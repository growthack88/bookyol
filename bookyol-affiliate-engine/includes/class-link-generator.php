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

    public function generate_links( $isbn, $title, $author = '' ) {
        $ids   = self::get_ids();
        $links = array();

        if ( ! empty( $ids['bookshop_id'] ) && ! empty( $isbn ) ) {
            $links['bookshop'] = 'https://bookshop.org/a/' . rawurlencode( $ids['bookshop_id'] ) . '/' . rawurlencode( $isbn );
        }

        if ( ! empty( $ids['ebookscom_id'] ) && ! empty( $isbn ) ) {
            $links['ebookscom'] = 'https://www.ebooks.com/en-us/book/' . rawurlencode( $isbn ) . '/?aid=' . rawurlencode( $ids['ebookscom_id'] );
        }

        if ( ! empty( $ids['rakuten_id'] ) && ! empty( $isbn ) ) {
            $kobo_url       = 'https://www.kobo.com/us/en/search?query=' . rawurlencode( $isbn );
            $links['kobo']  = 'https://click.linksynergy.com/deeplink?id=' . rawurlencode( $ids['rakuten_id'] ) . '&mid=37217&murl=' . rawurlencode( $kobo_url );
        }

        if ( ! empty( $ids['awin_id'] ) && ! empty( $isbn ) ) {
            $libro_url        = 'https://libro.fm/audiobooks?searchterm=' . rawurlencode( $isbn );
            $links['librofm'] = 'https://www.awin1.com/cread.php?awinmid=25361&awinaffid=' . rawurlencode( $ids['awin_id'] ) . '&ued=' . rawurlencode( $libro_url );
        }

        if ( ! empty( $ids['everand_url'] ) ) {
            $links['everand'] = $ids['everand_url'];
        }

        if ( ! empty( $ids['jamalon_id'] ) && ! empty( $isbn ) ) {
            $links['jamalon'] = 'https://jamalon.com/en/catalogsearch/result?q=' . rawurlencode( $isbn ) . '&ref=' . rawurlencode( $ids['jamalon_id'] );
        }

        return $links;
    }

    public function generate_and_save( $post_id, $isbn, $title, $author = '' ) {
        $links = $this->generate_links( $isbn, $title, $author );

        foreach ( $links as $platform => $url ) {
            $meta_key = '_bookyol_link_' . $platform;
            $existing = get_post_meta( $post_id, $meta_key, true );
            if ( empty( $existing ) || $existing === 'https://' ) {
                update_post_meta( $post_id, $meta_key, esc_url_raw( $url ) );
            }
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
