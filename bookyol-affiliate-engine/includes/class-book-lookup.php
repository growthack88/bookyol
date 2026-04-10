<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Book_Lookup {

    const NONCE_ACTION = 'bookyol_lookup_nonce';

    public function __construct() {
        add_action( 'wp_ajax_bookyol_lookup_book', array( $this, 'lookup_book' ) );
        add_action( 'wp_ajax_bookyol_generate_links', array( $this, 'generate_links_ajax' ) );
        add_action( 'wp_ajax_bookyol_clear_lookup_cache', array( $this, 'clear_cache' ) );
    }

    public function clear_cache() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bookyol_lookup_%' OR option_name LIKE '_transient_timeout_bookyol_lookup_%'" );
        wp_send_json_success( array( 'message' => 'Lookup cache cleared.' ) );
    }

    public function generate_links_ajax() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
        }
        $isbn   = isset( $_POST['isbn'] ) ? sanitize_text_field( wp_unslash( $_POST['isbn'] ) ) : '';
        $title  = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $author = isset( $_POST['author'] ) ? sanitize_text_field( wp_unslash( $_POST['author'] ) ) : '';
        $generator = new BookYol_Link_Generator();
        $links     = $generator->generate_links( $isbn, $title, $author );
        wp_send_json_success( array( 'links' => $links ) );
    }

    public function lookup_book() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
        }
        $query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
        if ( empty( $query ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a search query' ) );
        }
        $results = self::search( $query );
        if ( is_wp_error( $results ) ) {
            wp_send_json_error( array( 'message' => $results->get_error_message() ) );
        }
        if ( empty( $results ) ) {
            wp_send_json_error( array( 'message' => 'No results found' ) );
        }
        wp_send_json_success( array( 'books' => $results ) );
    }

    /**
     * Search for a book — tries Google Books first, then Open Library as fallback.
     */
    public static function search( $query ) {
        $query = trim( $query );
        if ( empty( $query ) ) {
            return array();
        }

        $cache_key = 'bookyol_lookup_' . md5( $query );
        $cached    = get_transient( $cache_key );
        if ( is_array( $cached ) && ! empty( $cached ) ) {
            return $cached;
        }

        // Detect if query is an ISBN
        $stripped = preg_replace( '/[^0-9Xx]/', '', $query );
        $is_isbn  = ( strlen( $stripped ) === 10 || strlen( $stripped ) === 13 )
                    && preg_match( '/^[0-9]{9}[0-9Xx]$|^[0-9]{13}$/', $stripped );

        // Try Google Books first
        $results = self::search_google_books( $query, $stripped, $is_isbn );

        // If Google Books failed or returned nothing, try Open Library
        if ( empty( $results ) ) {
            $results = self::search_open_library( $query, $stripped, $is_isbn );
        }

        // Cache results (short cache for empty results so retries work faster)
        if ( ! empty( $results ) ) {
            set_transient( $cache_key, $results, DAY_IN_SECONDS );
        } else {
            set_transient( $cache_key, array(), 300 ); // Only 5 min cache for not-found
        }

        return $results;
    }

    /**
     * Google Books API search
     */
    private static function search_google_books( $query, $stripped, $is_isbn ) {
        if ( $is_isbn ) {
            $url = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . rawurlencode( $stripped ) . '&maxResults=5';
        } else {
            $url = 'https://www.googleapis.com/books/v1/volumes?q=' . rawurlencode( $query ) . '&maxResults=5&langRestrict=en';
        }

        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            return array();
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            return array();
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['items'] ) || ! is_array( $body['items'] ) ) {
            return array();
        }

        $results = array();
        foreach ( $body['items'] as $item ) {
            $vi = isset( $item['volumeInfo'] ) ? $item['volumeInfo'] : array();

            $isbn_13 = '';
            $isbn_10 = '';
            if ( ! empty( $vi['industryIdentifiers'] ) && is_array( $vi['industryIdentifiers'] ) ) {
                foreach ( $vi['industryIdentifiers'] as $id ) {
                    if ( isset( $id['type'], $id['identifier'] ) ) {
                        if ( $id['type'] === 'ISBN_13' ) {
                            $isbn_13 = $id['identifier'];
                        } elseif ( $id['type'] === 'ISBN_10' ) {
                            $isbn_10 = $id['identifier'];
                        }
                    }
                }
            }

            $images    = isset( $vi['imageLinks'] ) && is_array( $vi['imageLinks'] ) ? $vi['imageLinks'] : array();
            $thumbnail = '';
            foreach ( array( 'extraLarge', 'large', 'medium', 'small', 'thumbnail', 'smallThumbnail' ) as $k ) {
                if ( ! empty( $images[ $k ] ) ) {
                    $thumbnail = $images[ $k ];
                    break;
                }
            }
            $thumbnail = str_replace( 'http://', 'https://', $thumbnail );
            $thumbnail = str_replace( '&edge=curl', '', $thumbnail );

            $results[] = array(
                'title'         => isset( $vi['title'] ) ? (string) $vi['title'] : '',
                'subtitle'      => isset( $vi['subtitle'] ) ? (string) $vi['subtitle'] : '',
                'authors'       => isset( $vi['authors'] ) && is_array( $vi['authors'] ) ? array_values( $vi['authors'] ) : array(),
                'publisher'     => isset( $vi['publisher'] ) ? (string) $vi['publisher'] : '',
                'publishedDate' => isset( $vi['publishedDate'] ) ? (string) $vi['publishedDate'] : '',
                'description'   => isset( $vi['description'] ) ? wp_strip_all_tags( (string) $vi['description'] ) : '',
                'pageCount'     => isset( $vi['pageCount'] ) ? (int) $vi['pageCount'] : 0,
                'categories'    => isset( $vi['categories'] ) && is_array( $vi['categories'] ) ? array_values( $vi['categories'] ) : array(),
                'isbn_13'       => $isbn_13,
                'isbn_10'       => $isbn_10,
                'thumbnail'     => $thumbnail,
                'language'      => isset( $vi['language'] ) ? (string) $vi['language'] : 'en',
            );
        }

        return $results;
    }

    /**
     * Open Library API search (fallback)
     * Docs: https://openlibrary.org/dev/docs/api/books
     */
    private static function search_open_library( $query, $stripped, $is_isbn ) {
        if ( $is_isbn ) {
            // Try ISBN endpoint first
            $isbn_key = 'ISBN:' . $stripped;
            $url = 'https://openlibrary.org/api/books?bibkeys=' . rawurlencode( $isbn_key ) . '&format=json&jscmd=data';

            $response = wp_remote_get( $url, array( 'timeout' => 10 ) );
            if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
                // Fall through to search API
            } else {
                $body = json_decode( wp_remote_retrieve_body( $response ), true );
                if ( ! empty( $body[ $isbn_key ] ) ) {
                    $book = $body[ $isbn_key ];
                    return array( self::parse_open_library_book( $book, $stripped ) );
                }
            }

            // Try Open Library search by ISBN
            $url = 'https://openlibrary.org/search.json?isbn=' . rawurlencode( $stripped ) . '&limit=5';
        } else {
            // Search by title/author
            $url = 'https://openlibrary.org/search.json?q=' . rawurlencode( $query ) . '&limit=5&language=eng';
        }

        $response = wp_remote_get( $url, array( 'timeout' => 15 ) );
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return array();
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['docs'] ) || ! is_array( $body['docs'] ) ) {
            return array();
        }

        $results = array();
        foreach ( $body['docs'] as $doc ) {
            $title   = isset( $doc['title'] ) ? (string) $doc['title'] : '';
            if ( empty( $title ) ) continue;

            $authors = array();
            if ( ! empty( $doc['author_name'] ) && is_array( $doc['author_name'] ) ) {
                $authors = array_values( $doc['author_name'] );
            }

            $isbn_13 = '';
            $isbn_10 = '';
            if ( ! empty( $doc['isbn'] ) && is_array( $doc['isbn'] ) ) {
                foreach ( $doc['isbn'] as $i ) {
                    $clean = preg_replace( '/[^0-9Xx]/', '', $i );
                    if ( strlen( $clean ) === 13 && empty( $isbn_13 ) ) {
                        $isbn_13 = $clean;
                    } elseif ( strlen( $clean ) === 10 && empty( $isbn_10 ) ) {
                        $isbn_10 = $clean;
                    }
                }
            }

            // Cover image from Open Library
            $cover_id = ! empty( $doc['cover_i'] ) ? (int) $doc['cover_i'] : 0;
            $thumbnail = '';
            if ( $cover_id > 0 ) {
                $thumbnail = 'https://covers.openlibrary.org/b/id/' . $cover_id . '-L.jpg';
            } elseif ( ! empty( $isbn_13 ) ) {
                $thumbnail = 'https://covers.openlibrary.org/b/isbn/' . $isbn_13 . '-L.jpg';
            } elseif ( ! empty( $isbn_10 ) ) {
                $thumbnail = 'https://covers.openlibrary.org/b/isbn/' . $isbn_10 . '-L.jpg';
            }

            $categories = array();
            if ( ! empty( $doc['subject'] ) && is_array( $doc['subject'] ) ) {
                $categories = array_slice( $doc['subject'], 0, 3 );
            }

            $publisher = '';
            if ( ! empty( $doc['publisher'] ) && is_array( $doc['publisher'] ) ) {
                $publisher = $doc['publisher'][0];
            }

            $page_count = 0;
            if ( ! empty( $doc['number_of_pages_median'] ) ) {
                $page_count = (int) $doc['number_of_pages_median'];
            }

            $pub_year = '';
            if ( ! empty( $doc['first_publish_year'] ) ) {
                $pub_year = (string) $doc['first_publish_year'];
            }

            $results[] = array(
                'title'         => $title,
                'subtitle'      => isset( $doc['subtitle'] ) ? (string) $doc['subtitle'] : '',
                'authors'       => $authors,
                'publisher'     => $publisher,
                'publishedDate' => $pub_year,
                'description'   => '', // Open Library search doesn't return descriptions
                'pageCount'     => $page_count,
                'categories'    => $categories,
                'isbn_13'       => $isbn_13,
                'isbn_10'       => $isbn_10,
                'thumbnail'     => $thumbnail,
                'language'      => 'en',
            );
        }

        return $results;
    }

    /**
     * Parse a single book from Open Library Books API (bibkeys endpoint)
     */
    private static function parse_open_library_book( $book, $isbn ) {
        $title   = isset( $book['title'] ) ? (string) $book['title'] : '';
        $authors = array();
        if ( ! empty( $book['authors'] ) && is_array( $book['authors'] ) ) {
            foreach ( $book['authors'] as $a ) {
                if ( isset( $a['name'] ) ) {
                    $authors[] = (string) $a['name'];
                }
            }
        }

        $isbn_13 = strlen( $isbn ) === 13 ? $isbn : '';
        $isbn_10 = strlen( $isbn ) === 10 ? $isbn : '';

        // Try to get both ISBNs from identifiers
        if ( ! empty( $book['identifiers'] ) ) {
            if ( ! empty( $book['identifiers']['isbn_13'] ) ) {
                $isbn_13 = $book['identifiers']['isbn_13'][0];
            }
            if ( ! empty( $book['identifiers']['isbn_10'] ) ) {
                $isbn_10 = $book['identifiers']['isbn_10'][0];
            }
        }

        $thumbnail = '';
        if ( ! empty( $book['cover'] ) ) {
            $thumbnail = $book['cover']['large'] ?? $book['cover']['medium'] ?? $book['cover']['small'] ?? '';
        }
        if ( empty( $thumbnail ) && ! empty( $isbn ) ) {
            $thumbnail = 'https://covers.openlibrary.org/b/isbn/' . $isbn . '-L.jpg';
        }

        $categories = array();
        if ( ! empty( $book['subjects'] ) && is_array( $book['subjects'] ) ) {
            foreach ( array_slice( $book['subjects'], 0, 3 ) as $s ) {
                $categories[] = isset( $s['name'] ) ? (string) $s['name'] : (string) $s;
            }
        }

        $publisher = '';
        if ( ! empty( $book['publishers'] ) && is_array( $book['publishers'] ) ) {
            $p = $book['publishers'][0];
            $publisher = isset( $p['name'] ) ? (string) $p['name'] : (string) $p;
        }

        return array(
            'title'         => $title,
            'subtitle'      => isset( $book['subtitle'] ) ? (string) $book['subtitle'] : '',
            'authors'       => $authors,
            'publisher'     => $publisher,
            'publishedDate' => isset( $book['publish_date'] ) ? (string) $book['publish_date'] : '',
            'description'   => '', // Will be empty from bibkeys API
            'pageCount'     => isset( $book['number_of_pages'] ) ? (int) $book['number_of_pages'] : 0,
            'categories'    => $categories,
            'isbn_13'       => $isbn_13,
            'isbn_10'       => $isbn_10,
            'thumbnail'     => $thumbnail,
            'language'      => 'en',
        );
    }
}
