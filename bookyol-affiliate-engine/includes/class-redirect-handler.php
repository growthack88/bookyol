<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Redirect_Handler {

    public function __construct() {
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'handle_redirect' ) );
    }

    public static function add_rewrite_rules() {
        add_rewrite_rule(
            '^go/([^/]+)/([^/]+)/?$',
            'index.php?bookyol_platform=$matches[1]&bookyol_bookslug=$matches[2]',
            'top'
        );
    }

    public function register_query_vars( $vars ) {
        $vars[] = 'bookyol_platform';
        $vars[] = 'bookyol_bookslug';
        return $vars;
    }

    public function handle_redirect() {
        $platform  = get_query_var( 'bookyol_platform' );
        $book_slug = get_query_var( 'bookyol_bookslug' );

        if ( empty( $platform ) || empty( $book_slug ) ) {
            return;
        }

        $platform  = sanitize_key( $platform );
        $book_slug = sanitize_title( $book_slug );

        $allowed = array_keys( BookYol_Shortcodes::$platform_names );
        if ( ! in_array( $platform, $allowed, true ) ) {
            wp_safe_redirect( home_url( '/?nolink=1' ), 302 );
            exit;
        }

        $book = get_page_by_path( $book_slug, OBJECT, 'bookyol_book' );
        if ( ! $book ) {
            wp_safe_redirect( home_url( '/?nolink=1' ), 302 );
            exit;
        }

        $url = get_post_meta( $book->ID, '_bookyol_link_' . $platform, true );
        if ( empty( $url ) ) {
            $permalink = get_permalink( $book->ID );
            wp_safe_redirect( add_query_arg( 'nolink', '1', $permalink ), 302 );
            exit;
        }

        $router  = new BookYol_Geo_Router();
        $cc      = $router->get_country_code();
        $referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';

        BookYol_Click_Tracker::log_click( $book->ID, $platform, $cc, $referer );

        wp_redirect( $url, 302 );
        exit;
    }
}
