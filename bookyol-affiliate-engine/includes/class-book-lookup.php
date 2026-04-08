<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Book_Lookup {

    const NONCE_ACTION = 'bookyol_lookup_nonce';

    public function __construct() {
        add_action( 'wp_ajax_bookyol_lookup_book', array( $this, 'lookup_book' ) );
        add_action( 'wp_ajax_bookyol_generate_links', array( $this, 'generate_links_ajax' ) );
    }

    public function generate_links_ajax() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'bookyol' ) ), 403 );
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
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'bookyol' ) ), 403 );
        }

        $query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
        if ( empty( $query ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a search query', 'bookyol' ) ) );
        }

        $results = self::search( $query );

        if ( is_wp_error( $results ) ) {
            wp_send_json_error( array( 'message' => $results->get_error_message() ) );
        }

        if ( empty( $results ) ) {
            wp_send_json_error( array( 'message' => __( 'No results found', 'bookyol' ) ) );
        }

        wp_send_json_success( array( 'books' => $results ) );
    }

    public static function search( $query ) {
        $query = trim( $query );
        if ( empty( $query ) ) {
            return array();
        }

        $cache_key = 'bookyol_lookup_' . md5( $query );
        $cached    = get_transient( $cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        $stripped = preg_replace( '/[^0-9Xx]/', '', $query );
        $is_isbn  = ( strlen( $stripped ) === 10 || strlen( $stripped ) === 13 ) && preg_match( '/^[0-9]{9}[0-9Xx]$|^[0-9]{13}$/', $stripped );

        if ( $is_isbn ) {
            $url = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . rawurlencode( $stripped ) . '&maxResults=5';
        } else {
            $url = 'https://www.googleapis.com/books/v1/volumes?q=' . rawurlencode( $query ) . '&maxResults=5&langRestrict=en';
        }

        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'api_error', __( 'API error, try again', 'bookyol' ) );
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            return new WP_Error( 'api_error', __( 'API error, try again', 'bookyol' ) );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['items'] ) || ! is_array( $body['items'] ) ) {
            set_transient( $cache_key, array(), DAY_IN_SECONDS );
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

        set_transient( $cache_key, $results, DAY_IN_SECONDS );
        return $results;
    }
}
