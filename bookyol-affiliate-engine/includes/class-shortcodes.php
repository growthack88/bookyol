<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Shortcodes {

    public static $platform_names = array(
        'everand'   => 'Everand',
        'librofm'   => 'Libro.fm',
        'ebookscom' => 'Ebooks.com',
        'bookshop'  => 'Bookshop.org',
        'kobo'      => 'Kobo',
        'jamalon'   => 'Jamalon',
    );

    public function __construct() {
        add_shortcode( 'bookyol_book', array( $this, 'render_book' ) );
        add_shortcode( 'bookyol_compare', array( $this, 'render_compare' ) );
    }

    public static function get_book_data( $book_id ) {
        $post = get_post( $book_id );
        if ( ! $post || $post->post_type !== 'bookyol_book' ) {
            return null;
        }

        $cover = get_post_meta( $book_id, '_bookyol_cover_url', true );
        if ( empty( $cover ) && has_post_thumbnail( $book_id ) ) {
            $cover = get_the_post_thumbnail_url( $book_id, 'medium' );
        }

        $data = array(
            'id'          => $book_id,
            'title'       => get_the_title( $post ),
            'slug'        => $post->post_name,
            'excerpt'     => $post->post_excerpt,
            'book_author' => get_post_meta( $book_id, '_bookyol_book_author', true ),
            'rating'      => (float) get_post_meta( $book_id, '_bookyol_rating', true ),
            'cover_url'   => $cover,
            'isbn'        => get_post_meta( $book_id, '_bookyol_isbn', true ),
            'pages'       => get_post_meta( $book_id, '_bookyol_pages', true ),
            'best_for'    => get_post_meta( $book_id, '_bookyol_best_for', true ),
            'links'       => array(),
        );

        foreach ( array_keys( self::$platform_names ) as $slug ) {
            $url = get_post_meta( $book_id, '_bookyol_link_' . $slug, true );
            if ( ! empty( $url ) ) {
                $data['links'][ $slug ] = $url;
            }
        }

        return $data;
    }

    public static function get_available_platforms( $book_data ) {
        $router   = new BookYol_Geo_Router();
        $priority = $router->get_platform_priority();
        $result   = array();
        foreach ( $priority as $slug ) {
            if ( isset( $book_data['links'][ $slug ] ) ) {
                $result[] = $slug;
            }
        }
        foreach ( $book_data['links'] as $slug => $url ) {
            if ( ! in_array( $slug, $result, true ) ) {
                $result[] = $slug;
            }
        }
        return $result;
    }

    public static function render_stars( $rating ) {
        $rating   = max( 0, min( 5, (float) $rating ) );
        $filled   = (int) floor( $rating );
        $empty    = 5 - $filled;
        $out      = str_repeat( '★', $filled ) . str_repeat( '☆', $empty );
        return $out;
    }

    public static function platform_display_name( $slug ) {
        return isset( self::$platform_names[ $slug ] ) ? self::$platform_names[ $slug ] : ucfirst( $slug );
    }

    public static function redirect_url( $platform, $slug ) {
        return home_url( '/go/' . rawurlencode( $platform ) . '/' . rawurlencode( $slug ) . '/' );
    }

    public function render_book( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'bookyol_book' );
        $book = self::get_book_data( absint( $atts['id'] ) );
        if ( ! $book ) {
            return '';
        }

        $available = self::get_available_platforms( $book );

        ob_start();
        include BOOKYOL_PATH . 'templates/book-card.php';
        return ob_get_clean();
    }

    public function render_compare( $atts ) {
        $atts = shortcode_atts( array( 'ids' => '' ), $atts, 'bookyol_compare' );
        $ids  = array_filter( array_map( 'absint', explode( ',', $atts['ids'] ) ) );
        if ( empty( $ids ) ) {
            return '';
        }

        $books = array();
        foreach ( $ids as $id ) {
            $data = self::get_book_data( $id );
            if ( $data ) {
                $data['available'] = self::get_available_platforms( $data );
                $books[]           = $data;
            }
        }
        if ( empty( $books ) ) {
            return '';
        }

        ob_start();
        include BOOKYOL_PATH . 'templates/comparison-table.php';
        return ob_get_clean();
    }
}
