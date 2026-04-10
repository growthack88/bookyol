<?php
/**
 * Plugin Name: BookYol Affiliate Engine
 * Description: Book affiliate link management with geo-routing, click tracking, and display shortcodes for BookYol.com
 * Version: 4.5.0
 * Author: Mahmoud Omar
 * Author URI: https://mahmoudomar.com
 * Text Domain: bookyol
 * Requires PHP: 7.4
 * Requires at least: 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BOOKYOL_VERSION', '4.5.0' );
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

    // Load fonts on single book pages, category archives, search, and book archive.
    if ( is_singular( 'bookyol_book' ) || is_tax( 'book_category' ) || is_search() || is_post_type_archive( 'bookyol_book' ) ) {
        wp_enqueue_style(
            'bookyol-google-fonts',
            'https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600;700&display=swap',
            array(),
            null
        );
    }

    // v4.1.0: Rich blog post template assets.
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

// Admin bar link to Homepage Settings.
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

// Floating "Edit Homepage" button for admins on the homepage front-end.
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

// v4.0.1: Priority 999 so we always run after Astra and other plugins.
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
    // v4.1.0: Rich blog post template.
    if ( is_singular( 'post' ) ) {
        $t = BOOKYOL_PATH . 'templates/single-post.php';
        if ( file_exists( $t ) ) return $t;
    }
    // v4.5.0: Search results template.
    if ( is_search() ) {
        $t = BOOKYOL_PATH . 'templates/search-results.php';
        if ( file_exists( $t ) ) return $t;
    }
    // v4.5.0: All-books archive template.
    if ( is_post_type_archive( 'bookyol_book' ) ) {
        $t = BOOKYOL_PATH . 'templates/archive-book.php';
        if ( file_exists( $t ) ) return $t;
    }
    return $template;
}, 999 );

// Astra compatibility filters — cover homepage, single book, category archive, blog posts, search, and book archive.
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

add_action( 'wp_head', function () {
    if ( ! bookyol_is_homepage() ) {
        return;
    }
    ?>
    <style id="bookyol-astra-reset">
        body.page-template-bookyol-homepage .site-content,
        body.page-template-bookyol-homepage .site-content > .ast-container,
        body.page-template-bookyol-homepage .ast-container,
        body.page-template-bookyol-homepage #primary,
        body.page-template-bookyol-homepage #primary > article,
        body.page-template-bookyol-homepage .entry-content,
        body.page-template-bookyol-homepage .post-inner,
        body.page-template-bookyol-homepage .ast-article-single {
            max-width: 100% !important;
            width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        body.page-template-bookyol-homepage .entry-header,
        body.page-template-bookyol-homepage .ast-single-post .entry-header,
        body.page-template-bookyol-homepage .page-title,
        body.page-template-bookyol-homepage .entry-title {
            display: none !important;
        }
        body.page-template-bookyol-homepage .entry-content > .bookyol-home {
            width: 100vw !important;
            position: relative !important;
            left: 50% !important;
            right: 50% !important;
            margin-left: -50vw !important;
            margin-right: -50vw !important;
            max-width: 100vw !important;
        }
        body.page-template-bookyol-homepage .site-content .ast-container {
            padding: 0 !important;
        }
        body.page-template-bookyol-homepage .bookyol-home h1,
        body.page-template-bookyol-homepage .bookyol-home h2,
        body.page-template-bookyol-homepage .bookyol-home h3,
        body.page-template-bookyol-homepage .bookyol-home p {
            margin-top: 0 !important;
        }
    </style>
    <?php
} );

add_filter( 'body_class', function ( $classes ) {
    if ( bookyol_is_homepage() ) {
        $classes[] = 'page-template-bookyol-homepage';
    }
    return $classes;
} );

add_action( 'wp_head', function () {
    if ( ! is_singular( 'bookyol_book' ) && ! is_tax( 'book_category' ) ) {
        return;
    }
    ?>
    <style id="bookyol-single-reset">
        .single-bookyol_book .site-content,
        .single-bookyol_book .site-content > .ast-container,
        .single-bookyol_book .ast-container,
        .single-bookyol_book #primary,
        .single-bookyol_book #primary > article,
        .single-bookyol_book .ast-article-single,
        .single-bookyol_book .post-inner,
        .tax-book_category  .site-content,
        .tax-book_category  .site-content > .ast-container,
        .tax-book_category  .ast-container,
        .tax-book_category  #primary,
        .tax-book_category  .ast-archive-description,
        .tax-book_category  .ast-article-single {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        .single-bookyol_book .entry-header,
        .tax-book_category  .entry-header,
        .tax-book_category  .ast-archive-title,
        .tax-book_category  .ast-archive-description,
        .single-bookyol_book .entry-title,
        .single-bookyol_book .ast-single-post-order .entry-header,
        .tax-book_category  .page-title {
            display: none !important;
        }
        .single-bookyol_book .entry-content,
        .tax-book_category  .entry-content {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        .single-bookyol_book .entry-content > .bookyol-single,
        .tax-book_category  .entry-content > .bookyol-archive {
            width: 100vw;
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw !important;
            margin-right: -50vw !important;
            max-width: 100vw !important;
        }
    </style>
    <?php
} );

add_action( 'wp_head', function () {
    if ( ! is_singular( 'post' ) ) {
        return;
    }
    ?>
    <style id="bookyol-blog-reset">
        html body.single-post .site-content,
        html body.single-post .site-content > .ast-container,
        html body.single-post .ast-container,
        html body.single-post #primary,
        html body.single-post #primary > article,
        html body.single-post .ast-article-single,
        html body.single-post .post-inner,
        html body.single-post .entry-content {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        html body.single-post .entry-header,
        html body.single-post .entry-title,
        html body.single-post .ast-single-post-order .entry-header {
            display: none !important;
        }
        html body.single-post .entry-content > .bookyol-blog,
        html body.single-post .bookyol-blog {
            width: 100vw !important;
            position: relative !important;
            left: 50% !important;
            right: 50% !important;
            margin-left: -50vw !important;
            margin-right: -50vw !important;
            max-width: 100vw !important;
            box-sizing: border-box !important;
        }
    </style>
    <?php
} );

// v4.5.0: Astra container reset for search results and book archive pages.
add_action( 'wp_head', function () {
    if ( ! is_search() && ! is_post_type_archive( 'bookyol_book' ) ) {
        return;
    }
    ?>
    <style id="bookyol-search-archive-reset">
        /* v4.5.0: Full-width override for search + book archive */
        html body.search-results .site-content,
        html body.search-results .site-content > .ast-container,
        html body.search-results .ast-container,
        html body.search-results #primary,
        html body.search-results #primary > .ast-row,
        html body.search-results .ast-article-single,
        html body.search-results .post-inner,
        html body.search-results .entry-content,
        html body.post-type-archive-bookyol_book .site-content,
        html body.post-type-archive-bookyol_book .site-content > .ast-container,
        html body.post-type-archive-bookyol_book .ast-container,
        html body.post-type-archive-bookyol_book #primary,
        html body.post-type-archive-bookyol_book #primary > .ast-row,
        html body.post-type-archive-bookyol_book .ast-article-single,
        html body.post-type-archive-bookyol_book .post-inner,
        html body.post-type-archive-bookyol_book .entry-content,
        html body.post-type-archive-bookyol_book #content {
            max-width: 100% !important;
            width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        html body.search-results .entry-header,
        html body.search-results .page-header,
        html body.post-type-archive-bookyol_book .entry-header,
        html body.post-type-archive-bookyol_book .page-header,
        html body.post-type-archive-bookyol_book .ast-archive-description,
        html body.post-type-archive-bookyol_book .ast-archive-title {
            display: none !important;
        }
        /* Break .bookyol-archive out to viewport width */
        html body.post-type-archive-bookyol_book .bookyol-archive,
        html body.search-results .bookyol-search-page {
            width: 100vw !important;
            position: relative !important;
            left: 50% !important;
            right: 50% !important;
            margin-left: -50vw !important;
            margin-right: -50vw !important;
            max-width: 100vw !important;
            box-sizing: border-box !important;
        }
    </style>
    <?php
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
