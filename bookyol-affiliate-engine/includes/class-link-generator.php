<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Link_Generator {

    const OPTION_KEY = 'bookyol_affiliate_ids';

    public static function default_ids() {
        return array(
            'bookshop_id'  => '',
            'ebookscom_id' => '',
            'rakuten_id'   => '',
            'awin_id'      => '',
            'everand_url'  => '',
            'jamalon_id'   => '',
        );
    }

    public static function get_ids() {
        $stored = get_option( self::OPTION_KEY, array() );
        return wp_parse_args( is_array( $stored ) ? $stored : array(), self::default_ids() );
    }

    /**
     * Generate all affiliate links for a book.
     *
     * @param string $isbn   ISBN-13 or ISBN-10
     * @param string $title  Book title
     * @param string $author Book author
     * @return array         ['platform_slug' => 'full_affiliate_url', ...]
     */
    public function generate_links( $isbn, $title, $author = '' ) {
        $ids   = self::get_ids();
        $links = array();

        // Bookshop.org — Primary platform. ISBN preferred, title-based fallback.
        if ( ! empty( $ids['bookshop_id'] ) ) {
            if ( ! empty( $isbn ) ) {
                $clean_isbn = preg_replace( '/[^0-9]/', '', $isbn );
                $links['bookshop'] = 'https://bookshop.org/a/' . rawurlencode( $ids['bookshop_id'] ) . '/' . $clean_isbn;
            } elseif ( ! empty( $title ) ) {
                $search = $title;
                if ( ! empty( $author ) ) {
                    $search .= ' ' . $author;
                }
                $links['bookshop'] = 'https://bookshop.org/shop/bookyol?searchterm=' . rawurlencode( $search );
            }
        }

        // Ebooks.com — Book page with affiliate parameter.
        if ( ! empty( $ids['ebookscom_id'] ) && ! empty( $isbn ) ) {
            $links['ebookscom'] = 'https://www.ebooks.com/en-us/book/' . rawurlencode( $isbn ) . '/?aid=' . rawurlencode( $ids['ebookscom_id'] );
        }

        // Kobo via Rakuten — deep link through Rakuten redirect.
        if ( ! empty( $ids['rakuten_id'] ) && ! empty( $isbn ) ) {
            $kobo_url       = 'https://www.kobo.com/us/en/search?query=' . rawurlencode( $isbn );
            $links['kobo']  = 'https://click.linksynergy.com/deeplink?id=' . rawurlencode( $ids['rakuten_id'] ) . '&mid=37217&murl=' . rawurlencode( $kobo_url );
        }

        // Libro.fm via Awin — deep link through Awin redirect.
        if ( ! empty( $ids['awin_id'] ) && ! empty( $isbn ) ) {
            $libro_url        = 'https://libro.fm/audiobooks?searchterm=' . rawurlencode( $isbn );
            $links['librofm'] = 'https://www.awin1.com/cread.php?awinmid=25361&awinaffid=' . rawurlencode( $ids['awin_id'] ) . '&ued=' . rawurlencode( $libro_url );
        }

        // Everand — single referral URL (not per-book).
        if ( ! empty( $ids['everand_url'] ) ) {
            $links['everand'] = $ids['everand_url'];
        }

        // Jamalon via ArabClicks.
        if ( ! empty( $ids['jamalon_id'] ) && ! empty( $isbn ) ) {
            $links['jamalon'] = 'https://jamalon.com/en/catalogsearch/result?q=' . rawurlencode( $isbn ) . '&ref=' . rawurlencode( $ids['jamalon_id'] );
        }

        return $links;
    }

    /**
     * Generate and save links.
     *
     * @param int    $post_id
     * @param string $isbn
     * @param string $title
     * @param string $author
     * @return array
     */
    public function generate_and_save( $post_id, $isbn, $title, $author = '' ) {
        $links = $this->generate_links( $isbn, $title, $author );

        foreach ( $links as $platform => $url ) {
            $meta_key = '_bookyol_link_' . $platform;
            // Always update — overwrite empty, "https://", or stale links.
            update_post_meta( $post_id, $meta_key, esc_url_raw( $url ) );
        }

        return $links;
    }

    public function regenerate_all( $overwrite = false ) {
        $books = get_posts( array(
            'post_type'      => 'bookyol_book',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
            'fields'         => 'ids',
        ) );

        $updated = 0;
        $skipped = 0;
        $platforms = array( 'everand', 'librofm', 'ebookscom', 'bookshop', 'kobo', 'jamalon' );

        foreach ( $books as $post_id ) {
            $isbn   = get_post_meta( $post_id, '_bookyol_isbn', true );
            $title  = get_the_title( $post_id );
            $author = get_post_meta( $post_id, '_bookyol_book_author', true );

            if ( empty( $isbn ) && empty( $title ) ) {
                $skipped++;
                continue;
            }

            if ( $overwrite ) {
                foreach ( $platforms as $p ) {
                    delete_post_meta( $post_id, '_bookyol_link_' . $p );
                }
            }

            $links = $this->generate_and_save( $post_id, $isbn, $title, $author );
            if ( ! empty( $links ) ) {
                $updated++;
            } else {
                $skipped++;
            }
        }

        return array( 'updated' => $updated, 'skipped' => $skipped );
    }
}
