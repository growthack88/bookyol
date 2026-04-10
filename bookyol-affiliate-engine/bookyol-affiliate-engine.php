<?php
/**
 * Plugin Name: BookYol Affiliate Engine
 * Description: Book affiliate link management with geo-routing, click tracking, and display shortcodes for BookYol.com
 * Version: 4.5.1
 * Author: Mahmoud Omar
 * Author URI: https://mahmoudomar.com
 * Text Domain: bookyol
 * Requires PHP: 7.4
 * Requires at least: 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BOOKYOL_VERSION', '4.5.1' );
define( 'BOOKYOL_PATH', plugin_dir_path( __FILE__ ) );
define( 'BOOKYOL_URL', plugin_dir_url( __FILE__ ) );
define( 'BOOKYOL_FILE', __FILE__ );

require_once BOOKYOL_PATH . 'includes/class-book-cpt.php';
require_once BOOKYOL_PATH . 'includes/class-meta-boxes.php';
require_once BOOKYOL_PATH . 'includes/class-geo-router.php';
require_once BOOKYOL_PATH . 'includes/class-click-tracker.php';
require_once BOOKYOL_PATH . 'includes/class-shortcodes.php';
require_once BOOKYOL_PATH . 'includes/class-redirect-handler.php';
require_once BOOKYOL_PATH . 'includes/class-link-generator.php';
require_once BOOKYOL_PATH . 'includes/class-book-lookup.php';
require_once BOOKYOL_PATH . 'includes/class-settings.php';
require_once BOOKYOL_PATH . 'includes/class-bulk-import.php';
require_once BOOKYOL_PATH . 'includes/class-homepage-helpers.php';
require_once BOOKYOL_PATH . 'includes/class-homepage-settings.php';
require_once BOOKYOL_PATH . 'includes/class-book-taxonomy.php';
require_once BOOKYOL_PATH . 'includes/class-category-mapper.php';

function bookyol_init() {
    new BookYol_Book_CPT();
    new BookYol_Meta_Boxes();
    new BookYol_Shortcodes();
    new BookYol_Redirect_Handler();
    new BookYol_Click_Tracker();
    new BookYol_Book_Lookup();
    new BookYol_Settings();
    new BookYol_Bulk_Import();
    new BookYol_Homepage_Settings();
    new BookYol_Book_Taxonomy();
    new BookYol_Category_Mapper();
}
add_action( 'plugins_loaded', 'bookyol_init' );

function bookyol_enqueue_frontend() {
    wp_enqueue_style(
        'bookyol-display',
        BOOKYOL_URL . 'assets/css/bookyol-display.css',
        array(),
        BOOKYOL_VERSION
    );

    if ( bookyol_is_homepage() ) {
        wp_enqueue_style(
            'bookyol-google-fonts',
            'https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600;700&display=swap',
            array(),
            null
        );
        wp_enqueue_style(
            'bookyol-homepage',
            BOOKYOL_URL . 'assets/css/bookyol-homepage.css',
            array( 'bookyol-google-fonts' ),
            BOOKYOL_VERSION
        );
    }

    if ( is_singular( 'bookyol_book' ) || is_tax( 'book_category' ) || is_search() || is_post_type_archive( 'bookyol_book' ) ) {
        wp_enqueue_style(
            'bookyol-google-fonts',
            'https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600;700&display=swap',
            array(),
            null
        );
    }

    if ( is_singular( 'post' ) ) {
        wp_enqueue_style(
            'bookyol-google-fonts',
            'https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600;700&display=swap',
            array(),
            null
        );
        wp_enqueue_style(
            'bookyol-blog',
            BOOKYOL_URL . 'assets/css/bookyol-blog.css',
            array( 'bookyol-google-fonts' ),
            BOOKYOL_VERSION
        );
        wp_enqueue_script(
            'bookyol-blog',
            BOOKYOL_URL . 'assets/js/bookyol-blog.js',
            array(),
            BOOKYOL_VERSION,
            true
        );
    }
}

add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $wp_admin_bar->add_node( array(
        'id'    => 'bookyol-homepage-settings',
        'title' => '📚 Edit Homepage',
        'href'  => admin_url( 'edit.php?post_type=bookyol_book&page=bookyol-homepage-settings' ),
        'meta'  => array( 'title' => 'BookYol Homepage Settings' ),
    ) );
}, 100 );

add_action( 'wp_footer', function () {
    if ( ! bookyol_is_homepage() || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $url = admin_url( 'edit.php?post_type=bookyol_book&page=bookyol-homepage-settings' );
    ?>
    <a href="<?php echo esc_url( $url ); ?>" style="position:fixed;bottom:20px;right:20px;z-index:9999;background:#7C5CFC;color:#fff;padding:12px 20px;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:600;box-shadow:0 4px 20px rgba(124,92,252,0.4);text-decoration:none;">⚙️ Edit Homepage Sections</a>
    <?php
} );
add_action( 'wp_enqueue_scripts', 'bookyol_enqueue_frontend' );

/* ==========================================================================
   Homepage Template Integration (Astra-compatible)
   ========================================================================== */

function bookyol_is_homepage() {
    return is_page() && get_page_template_slug() === 'bookyol-homepage';
}

add_filter( 'theme_page_templates', function ( $templates ) {
    $templates['bookyol-homepage'] = __( 'BookYol Homepage', 'bookyol' );
    return $templates;
} );

add_filter( 'template_include', function ( $template ) {
    if ( is_page() && get_page_template_slug() === 'bookyol-homepage' ) {
        $t = BOOKYOL_PATH . 'templates/page-homepage.php';
        if ( file_exists( $t ) ) return $t;
    }
    if ( is_tax( 'book_category' ) ) {
        $t = BOOKYOL_PATH . 'templates/archive-book-category.php';
        if ( file_exists( $t ) ) return $t;
    }
    if ( is_singular( 'bookyol_book' ) ) {
        $t = BOOKYOL_PATH . 'templates/single-book.php';
        if ( file_exists( $t ) ) return $t;
    }
    if ( is_singular( 'post' ) ) {
        $t = BOOKYOL_PATH . 'templates/single-post.php';
        if ( file_exists( $t ) ) return $t;
    }
    if ( is_search() ) {
        $t = BOOKYOL_PATH . 'templates/search-results.php';
        if ( file_exists( $t ) ) return $t;
    }
    if ( is_post_type_archive( 'bookyol_book' ) ) {
        $t = BOOKYOL_PATH . 'templates/archive-book.php';
        if ( file_exists( $t ) ) return $t;
    }
    return $template;
}, 999 );

// Astra compatibility filters.
add_filter( 'astra_the_title_enabled', function ( $enabled ) {
    if ( is_singular( 'bookyol_book' ) || is_tax( 'book_category' ) || is_singular( 'post' ) ) return false;
    if ( is_search() || is_post_type_archive( 'bookyol_book' ) ) return false;
    if ( bookyol_is_homepage() ) return false;
    return $enabled;
} );

add_filter( 'astra_page_layout', function ( $layout ) {
    if ( is_singular( 'bookyol_book' ) || is_tax( 'book_category' ) || is_singular( 'post' ) ) return 'no-sidebar';
    if ( is_search() || is_post_type_archive( 'bookyol_book' ) ) return 'no-sidebar';
    if ( bookyol_is_homepage() ) return 'no-sidebar';
    return $layout;
} );

add_filter( 'astra_get_content_layout', function ( $layout ) {
    if ( is_singular( 'bookyol_book' ) || is_tax( 'book_category' ) || is_singular( 'post' ) ) return 'page-builder';
    if ( is_search() || is_post_type_archive( 'bookyol_book' ) ) return 'page-builder';
    if ( bookyol_is_homepage() ) return 'page-builder';
    return $layout;
} );

/* ==========================================================================
   MASTER ASTRA RESET — v4.5.1
   One single wp_head action handles ALL BookYol page types.
   ========================================================================== */
add_action( 'wp_head', function () {
    $is_bookyol_page = (
        bookyol_is_homepage() ||
        is_singular( 'bookyol_book' ) ||
        is_tax( 'book_category' ) ||
        is_singular( 'post' ) ||
        is_search() ||
        is_post_type_archive( 'bookyol_book' )
    );

    if ( ! $is_bookyol_page ) {
        return;
    }
    ?>
    <style id="bookyol-astra-master-reset">
    /* ═══════════════════════════════════════════════════════
       BookYol Master Astra Override — v4.5.1
       Covers: homepage, single book, category archive,
               book archive (/books/), blog post, search
       ═══════════════════════════════════════════════════════ */

    /* ─── UNIVERSAL FULL-WIDTH RESET ─── */
    body.page-template-bookyol-homepage .site-content,
    body.page-template-bookyol-homepage .site-content > .ast-container,
    body.page-template-bookyol-homepage .ast-container,
    body.page-template-bookyol-homepage #primary,
    body.page-template-bookyol-homepage #primary > article,
    body.page-template-bookyol-homepage .entry-content,
    body.page-template-bookyol-homepage .post-inner,
    body.page-template-bookyol-homepage .ast-article-single,
    body.single-bookyol_book .site-content,
    body.single-bookyol_book .site-content > .ast-container,
    body.single-bookyol_book .ast-container,
    body.single-bookyol_book #primary,
    body.single-bookyol_book #primary > article,
    body.single-bookyol_book .ast-article-single,
    body.single-bookyol_book .post-inner,
    body.single-bookyol_book .entry-content,
    body.tax-book_category .site-content,
    body.tax-book_category .site-content > .ast-container,
    body.tax-book_category .ast-container,
    body.tax-book_category #primary,
    body.tax-book_category .ast-article-single,
    body.tax-book_category .entry-content,
    body.single-post .site-content,
    body.single-post .site-content > .ast-container,
    body.single-post .ast-container,
    body.single-post #primary,
    body.single-post #primary > article,
    body.single-post .ast-article-single,
    body.single-post .post-inner,
    body.single-post .entry-content,
    body.post-type-archive-bookyol_book .site-content,
    body.post-type-archive-bookyol_book .site-content > .ast-container,
    body.post-type-archive-bookyol_book .ast-container,
    body.post-type-archive-bookyol_book #primary,
    body.post-type-archive-bookyol_book #primary > article,
    body.post-type-archive-bookyol_book .ast-article-single,
    body.post-type-archive-bookyol_book .ast-article-post,
    body.post-type-archive-bookyol_book .post-inner,
    body.post-type-archive-bookyol_book .entry-content,
    body.post-type-archive-bookyol_book #content,
    body.search-results .site-content,
    body.search-results .site-content > .ast-container,
    body.search-results .ast-container,
    body.search-results #primary,
    body.search-results #primary > article,
    body.search-results .ast-article-single,
    body.search-results .ast-article-post,
    body.search-results .post-inner,
    body.search-results .entry-content {
        max-width: 100% !important;
        width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* ─── KILL SIDEBAR ON ALL BOOKYOL PAGES ─── */
    body.post-type-archive-bookyol_book #secondary,
    body.search-results #secondary,
    body.single-bookyol_book #secondary,
    body.tax-book_category #secondary {
        display: none !important;
    }
    body.post-type-archive-bookyol_book #primary,
    body.search-results #primary,
    body.single-bookyol_book #primary,
    body.tax-book_category #primary {
        float: none !important;
        width: 100% !important;
    }

    /* ─── KILL ASTRA SEPARATE-CONTAINER BOX STYLING ─── */
    body.post-type-archive-bookyol_book.ast-separate-container .ast-article-post,
    body.post-type-archive-bookyol_book.ast-separate-container .ast-article-single,
    body.search-results.ast-separate-container .ast-article-post,
    body.search-results.ast-separate-container .ast-article-single,
    body.single-bookyol_book.ast-separate-container .ast-article-single,
    body.tax-book_category.ast-separate-container .ast-article-post,
    body.tax-book_category.ast-separate-container .ast-article-single,
    body.single-post.ast-separate-container .ast-article-single {
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* ─── HIDE DEFAULT HEADERS/TITLES ─── */
    body.page-template-bookyol-homepage .entry-header,
    body.page-template-bookyol-homepage .entry-title,
    body.page-template-bookyol-homepage .page-title,
    body.single-bookyol_book .entry-header,
    body.single-bookyol_book .entry-title,
    body.tax-book_category .entry-header,
    body.tax-book_category .ast-archive-description,
    body.tax-book_category .ast-archive-title,
    body.tax-book_category .page-title,
    body.single-post .entry-header,
    body.single-post .entry-title,
    body.post-type-archive-bookyol_book .entry-header,
    body.post-type-archive-bookyol_book .page-header,
    body.post-type-archive-bookyol_book .ast-archive-description,
    body.post-type-archive-bookyol_book .ast-archive-title,
    body.search-results .entry-header,
    body.search-results .page-header {
        display: none !important;
    }

    /* ─── BREAK OUT PLUGIN CONTAINERS TO FULL VIEWPORT ─── */
    body.page-template-bookyol-homepage .entry-content > .bookyol-home,
    body.single-bookyol_book .entry-content > .bookyol-single,
    body.single-bookyol_book .bookyol-single,
    body.tax-book_category .entry-content > .bookyol-archive,
    body.tax-book_category .bookyol-archive,
    body.single-post .entry-content > .bookyol-blog,
    body.single-post .bookyol-blog,
    body.post-type-archive-bookyol_book .bookyol-archive,
    body.post-type-archive-bookyol_book .entry-content > .bookyol-archive,
    body.search-results .bookyol-search,
    body.search-results .entry-content > .bookyol-search {
        width: 100vw !important;
        position: relative !important;
        left: 50% !important;
        right: 50% !important;
        margin-left: -50vw !important;
        margin-right: -50vw !important;
        max-width: 100vw !important;
        box-sizing: border-box !important;
    }

    /* ─── HOMEPAGE SPECIFIC ─── */
    body.page-template-bookyol-homepage .site-content .ast-container {
        padding: 0 !important;
    }
    body.page-template-bookyol-homepage .bookyol-home h1,
    body.page-template-bookyol-homepage .bookyol-home h2,
    body.page-template-bookyol-homepage .bookyol-home h3,
    body.page-template-bookyol-homepage .bookyol-home p {
        margin-top: 0 !important;
    }

    /* ─── PREVENT HORIZONTAL SCROLLBAR ─── */
    body.post-type-archive-bookyol_book,
    body.search-results,
    body.single-bookyol_book,
    body.tax-book_category,
    body.single-post,
    body.page-template-bookyol-homepage {
        overflow-x: hidden !important;
    }
    </style>
    <?php
}, 5 ); // Priority 5 = runs early, before Astra's own styles

add_filter( 'body_class', function ( $classes ) {
    if ( bookyol_is_homepage() ) {
        $classes[] = 'page-template-bookyol-homepage';
    }
    return $classes;
} );

function bookyol_enqueue_admin( $hook ) {
    global $post_type;
    if ( ( $hook === 'post.php' || $hook === 'post-new.php' ) && $post_type === 'bookyol_book' ) {
        wp_enqueue_style(
            'bookyol-admin',
            BOOKYOL_URL . 'assets/css/bookyol-admin.css',
            array(),
            BOOKYOL_VERSION
        );
        wp_enqueue_media();
        wp_enqueue_script(
            'bookyol-admin-js',
            BOOKYOL_URL . 'assets/js/bookyol-geo.js',
            array(),
            BOOKYOL_VERSION,
            true
        );
        wp_enqueue_script(
            'bookyol-lookup',
            BOOKYOL_URL . 'assets/js/bookyol-lookup.js',
            array(),
            BOOKYOL_VERSION,
            true
        );
        wp_localize_script( 'bookyol-lookup', 'BookYolLookup', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( BookYol_Book_Lookup::NONCE_ACTION ),
        ) );
    }

    if ( $hook === 'bookyol_book_page_bookyol-homepage-settings' ) {
        wp_enqueue_style(
            'bookyol-homepage-admin',
            BOOKYOL_URL . 'assets/css/bookyol-homepage-admin.css',
            array(),
            BOOKYOL_VERSION
        );
        wp_enqueue_script(
            'bookyol-homepage-admin',
            BOOKYOL_URL . 'assets/js/bookyol-homepage-admin.js',
            array(),
            BOOKYOL_VERSION,
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'bookyol_enqueue_admin' );

function bookyol_activate() {
    require_once BOOKYOL_PATH . 'includes/class-book-cpt.php';
    require_once BOOKYOL_PATH . 'includes/class-click-tracker.php';
    require_once BOOKYOL_PATH . 'includes/class-redirect-handler.php';
    require_once BOOKYOL_PATH . 'includes/class-book-taxonomy.php';

    $cpt = new BookYol_Book_CPT();
    $cpt->register();

    $tax = new BookYol_Book_Taxonomy();
    $tax->register();

    BookYol_Click_Tracker::create_table();
    BookYol_Redirect_Handler::add_rewrite_rules();
    BookYol_Book_Taxonomy::create_default_terms();

    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bookyol_activate' );

function bookyol_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'bookyol_deactivate' );
