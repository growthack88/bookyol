<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bookyol_get_homepage_settings() {
    $defaults = array(
        // Hero
        'hero_title'            => 'Find your next favorite book',
        'hero_title_highlight'  => 'favorite book',
        'hero_subtitle'         => '500+ curated books. 6 platforms. Ebook, audiobook, or print — your choice.',
        'hero_shelf_source'     => 'random',
        'hero_shelf_book_ids'   => '',
        'hero_shelf_count'      => 24,

        // Formats
        'format_digital_title'     => 'Read Digital',
        'format_digital_desc'      => 'Instant access to ebooks on any device. Read on the go, highlight, and sync across platforms.',
        'format_digital_platforms' => 'Everand, Ebooks.com, Kobo',
        'format_digital_url'       => '',
        'format_audio_title'       => 'Listen Audio',
        'format_audio_desc'        => 'Turn commutes into classrooms. Professional narration that brings every book to life.',
        'format_audio_platforms'   => 'Libro.fm, Everand',
        'format_audio_url'         => '',
        'format_physical_title'    => 'Buy Physical',
        'format_physical_desc'     => 'Nothing beats a real book. Support independent bookstores with every purchase.',
        'format_physical_platforms' => 'Bookshop.org, Kobo',
        'format_physical_url'      => '',

        // Trending
        'trending_title'     => 'Trending Now',
        'trending_source'    => 'random',
        'trending_book_ids'  => '',
        'trending_count'     => 10,
        'trending_color'     => '#FF6B6B',

        // Categories
        'categories_json'    => '',

        // Audiobook Spotlight
        'audio_show'     => 1,
        'audio_title'    => 'Turn every commute into a masterclass',
        'audio_desc'     => 'Discover professionally narrated audiobooks. Listen while you drive, walk, cook, or work out. Support indie bookstores with every listen.',
        'audio_btn_text' => '🎧 Browse Audiobooks →',
        'audio_btn_url'  => '',
        'audio_book_ids' => '',

        // Collections
        'collections_json' => '',

        // New This Week
        'new_title'     => 'New This Week',
        'new_source'    => 'latest',
        'new_book_ids'  => '',
        'new_count'     => 10,
        'new_color'     => '#2ECC87',

        // Newsletter
        'newsletter_show'        => 1,
        'newsletter_title'       => '📬 One great book, every Tuesday.',
        'newsletter_subtitle'    => 'Join a growing community of readers getting curated picks delivered to their inbox.',
        'newsletter_btn_text'    => 'Subscribe Free',
        'newsletter_form_action' => '',
        'newsletter_note'        => 'No spam · Unsubscribe anytime · Join 2,000+ readers',

        // v4.2.0: Category Rows
        'cat_rows_show'       => 1,
        'cat_rows_count'      => 4,
        'cat_rows_books_per'  => 10,
        'cat_rows_source'     => 'auto',
        'cat_rows_slugs'      => 'fiction,business,psychology,self-help',

        // v4.2.0: Highest Rated
        'top_rated_show'  => 1,
        'top_rated_title' => '⭐ Highest Rated',
        'top_rated_count' => 10,

        // v4.2.0: Quote Banner
        'quote_show'   => 1,
        'quote_text'   => 'A reader lives a thousand lives before he dies. The man who never reads lives only one.',
        'quote_author' => 'George R.R. Martin',

        // Articles
        'articles_show'   => 1,
        'articles_title'  => 'Latest from the Blog',
        'articles_count'  => 3,
        'articles_source' => 'posts',
        'articles_color'  => '#4A90D9',

        // Footer
        'footer_description' => 'Your gateway to knowledge. Find your next great read across every platform — digital, audio, or print.',
        'footer_col2_title'  => 'Explore',
        'footer_col3_title'  => 'Topics',
        'footer_col4_title'  => 'BookYol',
    );

    $saved = get_option( 'bookyol_homepage_settings', array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }
    return wp_parse_args( $saved, $defaults );
}

function bookyol_get_homepage_books( $source, $ids_string, $count = 5 ) {
    $args = array(
        'post_type'      => 'bookyol_book',
        'posts_per_page' => intval( $count ),
        'post_status'    => 'publish',
    );

    if ( $source === 'featured' && ! empty( $ids_string ) ) {
        $ids = array_filter( array_map( 'intval', explode( ',', $ids_string ) ) );
        if ( ! empty( $ids ) ) {
            $args['post__in'] = $ids;
            $args['orderby']  = 'post__in';
        }
    } elseif ( $source === 'random' ) {
        $args['orderby'] = 'rand';
    } elseif ( $source === 'most_clicked' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'bookyol_clicks';
        $rows  = $wpdb->get_results( $wpdb->prepare(
            "SELECT book_id, COUNT(*) AS cnt FROM $table GROUP BY book_id ORDER BY cnt DESC LIMIT %d",
            intval( $count )
        ) );
        if ( $rows ) {
            $ids = array_map( function ( $r ) { return (int) $r->book_id; }, $rows );
            if ( ! empty( $ids ) ) {
                $args['post__in'] = $ids;
                $args['orderby']  = 'post__in';
            }
        }
    } else {
        $args['orderby'] = 'date';
        $args['order']   = 'DESC';
    }

    return get_posts( $args );
}

function bookyol_default_categories() {
    return array(
        array( 'icon' => '💼', 'name' => 'Business',     'url' => '/books/category/business/',     'color_class' => 'biz' ),
        array( 'icon' => '🧠', 'name' => 'Psychology',   'url' => '/books/category/psychology/',   'color_class' => 'psy' ),
        array( 'icon' => '🌱', 'name' => 'Self-Help',    'url' => '/books/category/self-help/',    'color_class' => 'self' ),
        array( 'icon' => '⚡', 'name' => 'Productivity', 'url' => '/books/category/productivity/', 'color_class' => 'prod' ),
        array( 'icon' => '📈', 'name' => 'Marketing',    'url' => '/books/category/marketing/',    'color_class' => 'mkt' ),
        array( 'icon' => '💰', 'name' => 'Finance',      'url' => '/books/category/finance/',      'color_class' => 'fin' ),
        array( 'icon' => '🎯', 'name' => 'Leadership',   'url' => '/books/category/leadership/',   'color_class' => 'lead' ),
        array( 'icon' => '📖', 'name' => 'Biographies',  'url' => '/books/category/biographies/',  'color_class' => 'bio' ),
        array( 'icon' => '🔬', 'name' => 'Science',      'url' => '/books/category/science/',      'color_class' => 'sci' ),
        array( 'icon' => '💭', 'name' => 'Philosophy',   'url' => '/books/category/philosophy/',   'color_class' => 'phil' ),
        array( 'icon' => '🏛️', 'name' => 'History',     'url' => '/books/category/history/',      'color_class' => 'his' ),
        array( 'icon' => '🎨', 'name' => 'Creativity',   'url' => '/books/category/creativity/',   'color_class' => 'cre' ),
    );
}

function bookyol_default_collections() {
    return array(
        array( 'emoji' => '🚀', 'title' => 'Startup Essentials',     'count' => '24 books', 'url' => '/books/category/business/',   'gradient' => '1' ),
        array( 'emoji' => '🧘', 'title' => 'Mindset & Growth',       'count' => '31 books', 'url' => '/books/category/self-help/',  'gradient' => '2' ),
        array( 'emoji' => '💡', 'title' => 'Creative Thinking',      'count' => '18 books', 'url' => '/books/category/creativity/', 'gradient' => '3' ),
        array( 'emoji' => '💸', 'title' => 'Money & Investing',      'count' => '22 books', 'url' => '/books/category/finance/',    'gradient' => '4' ),
        array( 'emoji' => '🧠', 'title' => 'Psychology Deep Dives',  'count' => '27 books', 'url' => '/books/category/psychology/', 'gradient' => '5' ),
        array( 'emoji' => '📊', 'title' => 'Data-Driven Marketing',  'count' => '15 books', 'url' => '/books/category/marketing/',  'gradient' => '6' ),
    );
}

function bookyol_render_star_rating( $rating ) {
    $full = (int) floor( floatval( $rating ) );
    if ( $full < 0 ) $full = 0;
    if ( $full > 5 ) $full = 5;
    return str_repeat( '★', $full ) . str_repeat( '☆', 5 - $full );
}

function bookyol_book_cover_url( $post_id ) {
    $url = get_post_meta( $post_id, '_bookyol_cover_url', true );
    if ( empty( $url ) && has_post_thumbnail( $post_id ) ) {
        $url = get_the_post_thumbnail_url( $post_id, 'medium' );
    }
    return $url;
}

function bookyol_is_new_book( $post_id, $days = 7 ) {
    $post_date = get_the_date( 'U', $post_id );
    if ( ! $post_date ) return false;
    return ( time() - (int) $post_date ) <= ( $days * DAY_IN_SECONDS );
}

/**
 * v4.2.0: Get published books for a given category slug, filtered to books that
 * have a cover image set.
 */
function bookyol_get_books_by_category( $category_slug, $count = 10 ) {
    if ( ! taxonomy_exists( 'book_category' ) || empty( $category_slug ) ) {
        return array();
    }

    $term = get_term_by( 'slug', $category_slug, 'book_category' );
    if ( ! $term || is_wp_error( $term ) ) {
        return array();
    }

    $books = get_posts( array(
        'post_type'      => 'bookyol_book',
        'posts_per_page' => (int) $count,
        'post_status'    => 'publish',
        'orderby'        => 'rand',
        'tax_query'      => array(
            array( 'taxonomy' => 'book_category', 'field' => 'slug', 'terms' => $category_slug ),
        ),
    ) );

    // Filter out books without covers so the grid doesn't have gaps.
    return array_values( array_filter( $books, function ( $book ) {
        return ! empty( get_post_meta( $book->ID, '_bookyol_cover_url', true ) );
    } ) );
}

/**
 * v4.2.0: Return the top N most populated book_category terms along with a
 * batch of random books and their emoji + accent color, for rendering
 * category-specific book rows on the homepage.
 */
function bookyol_get_top_categories_with_books( $count = 4, $books_per = 10, $explicit_slugs = array() ) {
    if ( ! taxonomy_exists( 'book_category' ) ) {
        return array();
    }

    $cat_emojis = array(
        'business' => '💼', 'psychology' => '🧠', 'self-help' => '🌱',
        'productivity' => '⚡', 'marketing' => '📈', 'finance' => '💰',
        'leadership' => '🎯', 'biographies' => '📖', 'fiction' => '📕',
        'thriller' => '🔍', 'sci-fi' => '🚀', 'romance' => '💘',
        'classic' => '📜', 'fantasy' => '🐉', 'science' => '🔬',
        'philosophy' => '💭', 'history' => '🏛️', 'creativity' => '🎨',
        'memoir' => '✍️', 'health' => '🏃',
    );

    $cat_colors = array(
        'business' => '#4A90D9', 'psychology' => '#7C5CFC', 'self-help' => '#2ECC87',
        'productivity' => '#F5A623', 'marketing' => '#FF6B6B', 'finance' => '#20B2AA',
        'leadership' => '#E84393', 'biographies' => '#5352ED', 'fiction' => '#667EEA',
        'thriller' => '#2D3436', 'sci-fi' => '#00B894', 'romance' => '#FD79A8',
        'classic' => '#6C5CE7', 'fantasy' => '#A29BFE', 'science' => '#0984E3',
        'philosophy' => '#8E24AA', 'history' => '#795548', 'creativity' => '#E65100',
    );

    $terms = array();
    if ( ! empty( $explicit_slugs ) && is_array( $explicit_slugs ) ) {
        foreach ( $explicit_slugs as $slug ) {
            $t = get_term_by( 'slug', trim( $slug ), 'book_category' );
            if ( $t && ! is_wp_error( $t ) ) {
                $terms[] = $t;
            }
        }
    } else {
        $maybe = get_terms( array(
            'taxonomy'   => 'book_category',
            'hide_empty' => true,
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => (int) $count,
        ) );
        if ( ! is_wp_error( $maybe ) ) {
            $terms = $maybe;
        }
    }

    $result = array();
    foreach ( $terms as $term ) {
        $books = bookyol_get_books_by_category( $term->slug, $books_per );
        // Only include categories that have enough books to fill a row.
        if ( count( $books ) >= 3 ) {
            $result[] = array(
                'term'  => $term,
                'emoji' => isset( $cat_emojis[ $term->slug ] ) ? $cat_emojis[ $term->slug ] : '📚',
                'color' => isset( $cat_colors[ $term->slug ] ) ? $cat_colors[ $term->slug ] : '#7C5CFC',
                'books' => $books,
            );
        }
    }

    return $result;
}
