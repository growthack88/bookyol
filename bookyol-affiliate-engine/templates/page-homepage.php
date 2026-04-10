<?php
/**
 * Template Name: BookYol Homepage
 * v4.2.0 — Expanded homepage with 16 sections and dynamic category rows
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$s = bookyol_get_homepage_settings();

// Categories/collections JSON.
$categories = array();
if ( ! empty( $s['categories_json'] ) ) {
    $decoded    = json_decode( $s['categories_json'], true );
    $categories = is_array( $decoded ) && ! empty( $decoded ) ? $decoded : bookyol_default_categories();
} else {
    $categories = bookyol_default_categories();
}

$collections = array();
if ( ! empty( $s['collections_json'] ) ) {
    $decoded     = json_decode( $s['collections_json'], true );
    $collections = is_array( $decoded ) && ! empty( $decoded ) ? $decoded : bookyol_default_collections();
} else {
    $collections = bookyol_default_collections();
}

/* ------------------------------------------------------------------
   HELPERS
   ------------------------------------------------------------------ */
$filter_with_cover = function ( $books ) {
    return array_values( array_filter( $books, function ( $book ) {
        return ! empty( get_post_meta( $book->ID, '_bookyol_cover_url', true ) );
    } ) );
};

/* ------------------------------------------------------------------
   HERO SHELF (24 books, cover-required)
   ------------------------------------------------------------------ */
$hero_books = bookyol_get_homepage_books(
    $s['hero_shelf_source'],
    $s['hero_shelf_book_ids'],
    max( 24, (int) $s['hero_shelf_count'] )
);
$hero_books = $filter_with_cover( $hero_books );

/* ------------------------------------------------------------------
   TRENDING (10)
   ------------------------------------------------------------------ */
$trending_count = max( 10, (int) $s['trending_count'] );
$trending_books = bookyol_get_homepage_books( $s['trending_source'], $s['trending_book_ids'], $trending_count );
$trending_books = $filter_with_cover( $trending_books );

/* ------------------------------------------------------------------
   NEW THIS WEEK (10)
   ------------------------------------------------------------------ */
$new_count  = max( 10, (int) $s['new_count'] );
$new_books  = bookyol_get_homepage_books( $s['new_source'], $s['new_book_ids'], $new_count );
$new_books  = $filter_with_cover( $new_books );

/* ------------------------------------------------------------------
   AUDIOBOOK SPOTLIGHT (3)
   ------------------------------------------------------------------ */
$audio_books = bookyol_get_homepage_books( ! empty( $s['audio_book_ids'] ) ? 'featured' : 'latest', $s['audio_book_ids'], 3 );
$audio_books = $filter_with_cover( $audio_books );

/* ------------------------------------------------------------------
   CATEGORY ROWS (v4.2.0)
   ------------------------------------------------------------------ */
$cat_rows = array();
if ( ! empty( $s['cat_rows_show'] ) && function_exists( 'bookyol_get_top_categories_with_books' ) ) {
    $cat_rows_count     = max( 1, (int) $s['cat_rows_count'] );
    $cat_rows_books_per = max( 3, (int) $s['cat_rows_books_per'] );
    if ( $s['cat_rows_source'] === 'manual' && ! empty( $s['cat_rows_slugs'] ) ) {
        $slugs    = array_filter( array_map( 'trim', explode( ',', $s['cat_rows_slugs'] ) ) );
        $cat_rows = bookyol_get_top_categories_with_books( $cat_rows_count, $cat_rows_books_per, $slugs );
    } else {
        $cat_rows = bookyol_get_top_categories_with_books( $cat_rows_count, $cat_rows_books_per );
    }
}

// Split category rows: first 2 after Trending, next 2 after Audiobook.
$cat_rows_top    = array_slice( $cat_rows, 0, 2 );
$cat_rows_bottom = array_slice( $cat_rows, 2 );

/* ------------------------------------------------------------------
   HIGHEST RATED (v4.2.0)
   ------------------------------------------------------------------ */
$top_rated_books = array();
if ( ! empty( $s['top_rated_show'] ) ) {
    $top_rated_books = get_posts( array(
        'post_type'      => 'bookyol_book',
        'posts_per_page' => max( 5, (int) $s['top_rated_count'] ),
        'post_status'    => 'publish',
        'meta_key'       => '_bookyol_rating',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    ) );
    $top_rated_books = $filter_with_cover( $top_rated_books );
}

/* ------------------------------------------------------------------
   Hero title with highlight
   ------------------------------------------------------------------ */
$hero_title_html = esc_html( $s['hero_title'] );
if ( ! empty( $s['hero_title_highlight'] ) ) {
    $highlight_esc   = esc_html( $s['hero_title_highlight'] );
    $hero_title_html = str_replace(
        $highlight_esc,
        '<em>' . $highlight_esc . '</em>',
        $hero_title_html
    );
}

/**
 * Reusable book-card renderer for the 5-column grids.
 */
$render_book_card = function ( $book, $extra_badge = '', $badge_class = '' ) {
    $cover = get_post_meta( $book->ID, '_bookyol_cover_url', true );
    if ( empty( $cover ) ) return;
    $author = get_post_meta( $book->ID, '_bookyol_book_author', true );
    $rating = get_post_meta( $book->ID, '_bookyol_rating', true );
    ?>
    <a href="<?php echo esc_url( get_permalink( $book->ID ) ); ?>" class="bookyol-book-card">
        <div class="bookyol-book-card__img">
            <?php if ( $extra_badge ) : ?>
                <span class="bookyol-book-card__badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $extra_badge ); ?></span>
            <?php endif; ?>
            <?php if ( $rating ) : ?>
                <span class="bookyol-book-card__rating"><span class="star">★</span> <?php echo esc_html( $rating ); ?></span>
            <?php endif; ?>
            <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $book->ID ) ); ?>" loading="lazy">
        </div>
        <div class="bookyol-book-card__info">
            <h3 class="bookyol-book-card__title"><?php echo esc_html( get_the_title( $book->ID ) ); ?></h3>
            <?php if ( $author ) : ?>
                <p class="bookyol-book-card__author"><?php echo esc_html( $author ); ?></p>
            <?php endif; ?>
        </div>
    </a>
    <?php
};
?>
<div class="bookyol-home">

    <!-- ══════ 1. HERO ══════ -->
    <section class="bookyol-hero">
        <div class="bookyol-container">
            <h1 class="bookyol-hero__title"><?php echo $hero_title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
            <p class="bookyol-hero__subtitle"><?php echo esc_html( $s['hero_subtitle'] ); ?></p>

            <form class="bookyol-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <input type="search" name="s" placeholder="<?php esc_attr_e( 'Search 500+ books…', 'bookyol' ); ?>" />
                <input type="hidden" name="post_type" value="bookyol_book" />
                <button type="submit"><?php esc_html_e( 'Search', 'bookyol' ); ?></button>
            </form>
        </div>

        <?php if ( ! empty( $hero_books ) ) : ?>
            <div class="bookyol-shelf-wrapper">
                <div class="bookyol-shelf" aria-label="<?php esc_attr_e( 'Featured books', 'bookyol' ); ?>">
                    <div class="bookyol-shelf__track">
                        <?php foreach ( $hero_books as $book ) :
                            $cover  = bookyol_book_cover_url( $book->ID );
                            $author = get_post_meta( $book->ID, '_bookyol_book_author', true );
                            ?>
                            <a href="<?php echo esc_url( get_permalink( $book->ID ) ); ?>" class="bookyol-shelf__item">
                                <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $book->ID ) ); ?>" loading="lazy" width="130" height="195">
                                <span class="bookyol-shelf__title"><?php echo esc_html( get_the_title( $book->ID ) ); ?></span>
                                <span class="bookyol-shelf__author"><?php echo esc_html( $author ); ?></span>
                            </a>
                        <?php endforeach; ?>
                        <?php // Duplicate for seamless infinite scroll. ?>
                        <?php foreach ( $hero_books as $book ) :
                            $cover  = bookyol_book_cover_url( $book->ID );
                            $author = get_post_meta( $book->ID, '_bookyol_book_author', true );
                            ?>
                            <a href="<?php echo esc_url( get_permalink( $book->ID ) ); ?>" class="bookyol-shelf__item" aria-hidden="true">
                                <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $book->ID ) ); ?>" loading="lazy" width="130" height="195">
                                <span class="bookyol-shelf__title"><?php echo esc_html( get_the_title( $book->ID ) ); ?></span>
                                <span class="bookyol-shelf__author"><?php echo esc_html( $author ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- ══════ 2. FORMAT CARDS ══════ -->
    <section class="bookyol-section">
        <div class="bookyol-container">
            <div class="bookyol-formats">
                <?php
                $formats = array(
                    array( 'slug' => 'digital',  'icon' => '📱' ),
                    array( 'slug' => 'audio',    'icon' => '🎧' ),
                    array( 'slug' => 'physical', 'icon' => '📚' ),
                );
                foreach ( $formats as $f ) :
                    $slug  = $f['slug'];
                    $title = $s[ 'format_' . $slug . '_title' ];
                    $desc  = $s[ 'format_' . $slug . '_desc' ];
                    $plats = $s[ 'format_' . $slug . '_platforms' ];
                    $url   = $s[ 'format_' . $slug . '_url' ];
                    $plat_items = array_filter( array_map( 'trim', explode( ',', $plats ) ) );
                    ?>
                    <a class="bookyol-format-card bookyol-format-card--<?php echo esc_attr( $slug ); ?>" href="<?php echo esc_url( $url ? $url : '#' ); ?>">
                        <span class="bookyol-format-card__arrow">→</span>
                        <span class="bookyol-format-card__icon"><?php echo esc_html( $f['icon'] ); ?></span>
                        <h3 class="bookyol-format-card__title"><?php echo esc_html( $title ); ?></h3>
                        <p class="bookyol-format-card__desc"><?php echo esc_html( $desc ); ?></p>
                        <?php if ( ! empty( $plat_items ) ) : ?>
                            <div class="bookyol-format-card__platforms">
                                <?php foreach ( $plat_items as $p ) : ?>
                                    <span class="bookyol-format-card__pill"><?php echo esc_html( $p ); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ══════ 3. TRENDING ══════ -->
    <?php if ( ! empty( $trending_books ) ) : ?>
        <section class="bookyol-section bookyol-section--gray">
            <div class="bookyol-container">
                <div class="bookyol-section__header">
                    <div class="bookyol-section__label">
                        <span class="bookyol-section__dot" style="background: <?php echo esc_attr( $s['trending_color'] ); ?>;"></span>
                        <h2 class="bookyol-section__title"><?php echo esc_html( $s['trending_title'] ); ?></h2>
                    </div>
                </div>
                <div class="bookyol-books-grid">
                    <?php foreach ( $trending_books as $i => $book ) :
                        $is_first = ( $i === 0 );
                        $is_new   = bookyol_is_new_book( $book->ID );
                        $badge    = $is_first ? '🔥 Trending' : ( $is_new ? 'New' : '' );
                        $badge_cl = $is_first ? 'bookyol-badge--trending' : ( $is_new ? 'bookyol-badge--new' : '' );
                        $render_book_card( $book, $badge, $badge_cl );
                    endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ══════ 4-5. CATEGORY ROWS (top 2) ══════ -->
    <?php foreach ( $cat_rows_top as $cat_row ) :
        $cat_term  = $cat_row['term'];
        $cat_emoji = $cat_row['emoji'];
        $cat_color = $cat_row['color'];
        $cat_books = $cat_row['books'];
        $cat_link  = get_term_link( $cat_term );
        if ( is_wp_error( $cat_link ) ) continue;
        ?>
        <section class="bookyol-section">
            <div class="bookyol-container">
                <div class="bookyol-section__header">
                    <div class="bookyol-section__label">
                        <span class="bookyol-section__dot" style="background: <?php echo esc_attr( $cat_color ); ?>;"></span>
                        <h2 class="bookyol-section__title"><?php echo esc_html( $cat_emoji . ' ' . $cat_term->name ); ?></h2>
                    </div>
                    <a href="<?php echo esc_url( $cat_link ); ?>" class="bookyol-section__link" style="color: <?php echo esc_attr( $cat_color ); ?>;">
                        <?php printf( esc_html__( 'See all %d books', 'bookyol' ), (int) $cat_term->count ); ?> →
                    </a>
                </div>
                <div class="bookyol-books-grid">
                    <?php foreach ( $cat_books as $cb ) { $render_book_card( $cb ); } ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>

    <!-- ══════ 6. EXPLORE BY CATEGORY (pills — dynamic from taxonomy) ══════ -->
    <?php
    $cat_pill_emojis = array(
        'business' => '💼', 'psychology' => '🧠', 'self-help' => '🌱',
        'productivity' => '⚡', 'marketing' => '📈', 'finance' => '💰',
        'leadership' => '🎯', 'biographies' => '📖', 'science' => '🔬',
        'philosophy' => '💭', 'history' => '🏛️', 'creativity' => '🎨',
        'fiction' => '📕', 'thriller' => '🔍', 'sci-fi' => '🚀',
        'romance' => '💘', 'classic' => '📜', 'fantasy' => '🐉',
        'memoir' => '✍️', 'health' => '🏃',
    );
    $cat_pill_colors = array(
        'business' => 'biz', 'psychology' => 'psy', 'self-help' => 'self',
        'productivity' => 'prod', 'marketing' => 'mkt', 'finance' => 'fin',
        'leadership' => 'lead', 'biographies' => 'bio', 'science' => 'sci',
        'philosophy' => 'phil', 'history' => 'his', 'creativity' => 'cre',
        'fiction' => 'bio', 'thriller' => 'his', 'sci-fi' => 'self',
        'romance' => 'lead', 'classic' => 'psy', 'fantasy' => 'psy',
    );
    $homepage_cats = array();
    if ( taxonomy_exists( 'book_category' ) ) {
        $maybe_cats = get_terms( array(
            'taxonomy'   => 'book_category',
            'hide_empty' => true,
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => 12,
        ) );
        if ( ! is_wp_error( $maybe_cats ) ) {
            $homepage_cats = $maybe_cats;
        }
    }
    if ( ! empty( $homepage_cats ) ) :
    ?>
        <section class="bookyol-section">
            <div class="bookyol-container">
                <div class="bookyol-section__header">
                    <div class="bookyol-section__label">
                        <h2 class="bookyol-section__title"><?php esc_html_e( 'Explore by Category', 'bookyol' ); ?></h2>
                    </div>
                </div>
                <div class="bookyol-categories">
                    <?php foreach ( $homepage_cats as $hc ) :
                        $link = get_term_link( $hc );
                        if ( is_wp_error( $link ) ) continue;
                        $emoji = isset( $cat_pill_emojis[ $hc->slug ] ) ? $cat_pill_emojis[ $hc->slug ] : '📖';
                        $color = isset( $cat_pill_colors[ $hc->slug ] ) ? $cat_pill_colors[ $hc->slug ] : 'biz';
                        ?>
                        <a class="bookyol-cat-pill bookyol-cat-pill--<?php echo esc_attr( $color ); ?>" href="<?php echo esc_url( $link ); ?>">
                            <span class="bookyol-cat-pill__icon"><?php echo esc_html( $emoji ); ?></span>
                            <span class="bookyol-cat-pill__name"><?php echo esc_html( $hc->name ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ══════ 7. HIGHEST RATED ══════ -->
    <?php if ( ! empty( $top_rated_books ) ) : ?>
        <section class="bookyol-section bookyol-section--gray">
            <div class="bookyol-container">
                <div class="bookyol-section__header">
                    <div class="bookyol-section__label">
                        <span class="bookyol-section__dot" style="background: #F5A623;"></span>
                        <h2 class="bookyol-section__title"><?php echo esc_html( $s['top_rated_title'] ); ?></h2>
                    </div>
                    <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="bookyol-section__link" style="color: #F5A623;"><?php esc_html_e( 'See all', 'bookyol' ); ?> →</a>
                </div>
                <div class="bookyol-books-grid">
                    <?php foreach ( $top_rated_books as $idx => $book ) :
                        $badge    = $idx === 0 ? '#1 Rated' : '';
                        $badge_cl = $idx === 0 ? 'bookyol-badge--pick' : '';
                        $render_book_card( $book, $badge, $badge_cl );
                    endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ══════ 8. QUOTE BANNER ══════ -->
    <?php if ( ! empty( $s['quote_show'] ) && ! empty( $s['quote_text'] ) ) : ?>
        <section class="bookyol-section" style="padding: 32px 0;">
            <div class="bookyol-container">
                <div class="bookyol-quote-banner">
                    <blockquote>
                        &ldquo;<?php echo esc_html( $s['quote_text'] ); ?>&rdquo;
                    </blockquote>
                    <?php if ( ! empty( $s['quote_author'] ) ) : ?>
                        <cite>— <?php echo esc_html( $s['quote_author'] ); ?></cite>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ══════ 9. AUDIOBOOK SPOTLIGHT ══════ -->
    <?php if ( ! empty( $s['audio_show'] ) ) : ?>
        <section class="bookyol-section bookyol-section--gray">
            <div class="bookyol-container">
                <div class="bookyol-audio-banner">
                    <div class="bookyol-audio-banner__content">
                        <span class="bookyol-audio-banner__tag"><?php esc_html_e( 'Audiobook Spotlight', 'bookyol' ); ?></span>
                        <h2 class="bookyol-audio-banner__title"><?php echo esc_html( $s['audio_title'] ); ?></h2>
                        <p class="bookyol-audio-banner__desc"><?php echo esc_html( $s['audio_desc'] ); ?></p>
                        <a class="bookyol-audio-banner__btn" href="<?php echo esc_url( $s['audio_btn_url'] ? $s['audio_btn_url'] : '#' ); ?>"><?php echo esc_html( $s['audio_btn_text'] ); ?></a>
                    </div>
                    <?php if ( ! empty( $audio_books ) ) : ?>
                        <div class="bookyol-audio-banner__books">
                            <?php foreach ( array_slice( $audio_books, 0, 3 ) as $book ) :
                                $cover = bookyol_book_cover_url( $book->ID );
                                ?>
                                <a href="<?php echo esc_url( get_permalink( $book->ID ) ); ?>">
                                    <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $book->ID ) ); ?>" loading="lazy">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ══════ 10-11. CATEGORY ROWS (bottom 2) ══════ -->
    <?php foreach ( $cat_rows_bottom as $cat_row ) :
        $cat_term  = $cat_row['term'];
        $cat_emoji = $cat_row['emoji'];
        $cat_color = $cat_row['color'];
        $cat_books = $cat_row['books'];
        $cat_link  = get_term_link( $cat_term );
        if ( is_wp_error( $cat_link ) ) continue;
        ?>
        <section class="bookyol-section">
            <div class="bookyol-container">
                <div class="bookyol-section__header">
                    <div class="bookyol-section__label">
                        <span class="bookyol-section__dot" style="background: <?php echo esc_attr( $cat_color ); ?>;"></span>
                        <h2 class="bookyol-section__title"><?php echo esc_html( $cat_emoji . ' ' . $cat_term->name ); ?></h2>
                    </div>
                    <a href="<?php echo esc_url( $cat_link ); ?>" class="bookyol-section__link" style="color: <?php echo esc_attr( $cat_color ); ?>;">
                        <?php printf( esc_html__( 'See all %d books', 'bookyol' ), (int) $cat_term->count ); ?> →
                    </a>
                </div>
                <div class="bookyol-books-grid">
                    <?php foreach ( $cat_books as $cb ) { $render_book_card( $cb ); } ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>

    <!-- ══════ 12. COLLECTIONS ══════ -->
    <?php if ( ! empty( $collections ) ) : ?>
        <section class="bookyol-section bookyol-section--gray">
            <div class="bookyol-container">
                <div class="bookyol-section__header">
                    <div class="bookyol-section__label">
                        <h2 class="bookyol-section__title"><?php esc_html_e( 'Curated Collections', 'bookyol' ); ?></h2>
                    </div>
                </div>
                <div class="bookyol-collections-grid">
                    <?php foreach ( $collections as $col ) :
                        if ( empty( $col['title'] ) ) continue;
                        $grad  = isset( $col['gradient'] ) && $col['gradient'] ? $col['gradient'] : '1';
                        $url   = isset( $col['url'] ) ? $col['url'] : '#';
                        $emoji = isset( $col['emoji'] ) ? $col['emoji'] : '📚';
                        $count = isset( $col['count'] ) ? $col['count'] : '';
                        ?>
                        <a class="bookyol-collection-card bookyol-collection--<?php echo esc_attr( $grad ); ?>" href="<?php echo esc_url( $url ); ?>">
                            <span class="bookyol-collection-card__arrow">→</span>
                            <div class="bookyol-collection-card__emoji"><?php echo esc_html( $emoji ); ?></div>
                            <h3 class="bookyol-collection-card__title"><?php echo esc_html( $col['title'] ); ?></h3>
                            <?php if ( $count ) : ?>
                                <p class="bookyol-collection-card__count"><?php echo esc_html( $count ); ?></p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ══════ 13. NEW THIS WEEK ══════ -->
    <?php if ( ! empty( $new_books ) ) : ?>
        <section class="bookyol-section">
            <div class="bookyol-container">
                <div class="bookyol-section__header">
                    <div class="bookyol-section__label">
                        <span class="bookyol-section__dot" style="background: <?php echo esc_attr( $s['new_color'] ); ?>;"></span>
                        <h2 class="bookyol-section__title"><?php echo esc_html( $s['new_title'] ); ?></h2>
                    </div>
                </div>
                <div class="bookyol-books-grid">
                    <?php foreach ( $new_books as $book ) {
                        $render_book_card( $book, 'New', 'bookyol-badge--new' );
                    } ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ══════ 14. NEWSLETTER ══════ -->
    <?php if ( ! empty( $s['newsletter_show'] ) ) :
        $nl_action = isset( $s['newsletter_form_action'] ) ? $s['newsletter_form_action'] : '';
        ?>
        <section class="bookyol-section bookyol-section--gray">
            <div class="bookyol-container">
                <div class="bookyol-newsletter">
                    <h2 class="bookyol-newsletter__title"><?php echo esc_html( $s['newsletter_title'] ); ?></h2>
                    <p class="bookyol-newsletter__subtitle"><?php echo esc_html( $s['newsletter_subtitle'] ); ?></p>
                    <div class="bookyol-newsletter__form">
                        <?php if ( ! empty( $nl_action ) ) : ?>
                            <form action="<?php echo esc_url( $nl_action ); ?>" method="post">
                                <input type="email" name="email_address" placeholder="<?php esc_attr_e( 'Enter your email address', 'bookyol' ); ?>" required />
                                <button type="submit"><?php echo esc_html( $s['newsletter_btn_text'] ); ?></button>
                            </form>
                        <?php else : ?>
                            <form onsubmit="event.preventDefault(); alert('Newsletter form not configured yet. Go to Books → Homepage Settings → Newsletter tab to add your form URL.');">
                                <input type="email" placeholder="<?php esc_attr_e( 'Enter your email address', 'bookyol' ); ?>" required />
                                <button type="submit"><?php echo esc_html( $s['newsletter_btn_text'] ); ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <p class="bookyol-newsletter__note"><?php echo esc_html( $s['newsletter_note'] ); ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ══════ 15. ARTICLES ══════ -->
    <?php if ( ! empty( $s['articles_show'] ) ) :
        $articles = get_posts( array(
            'post_type'      => $s['articles_source'] === 'books' ? 'bookyol_book' : 'post',
            'posts_per_page' => intval( $s['articles_count'] ),
            'post_status'    => 'publish',
        ) );
        if ( ! empty( $articles ) ) :
            $bg_variants = array( 'blue', 'coral', 'violet', 'green', 'amber' );
            ?>
            <section class="bookyol-section">
                <div class="bookyol-container">
                    <div class="bookyol-section__header">
                        <div class="bookyol-section__label">
                            <span class="bookyol-section__dot" style="background: <?php echo esc_attr( $s['articles_color'] ); ?>;"></span>
                            <h2 class="bookyol-section__title"><?php echo esc_html( $s['articles_title'] ); ?></h2>
                        </div>
                    </div>
                    <div class="bookyol-articles-grid">
                        <?php foreach ( $articles as $i => $article ) :
                            $thumb = has_post_thumbnail( $article->ID ) ? get_the_post_thumbnail_url( $article->ID, 'medium_large' ) : '';
                            if ( ! $thumb && $s['articles_source'] === 'books' ) {
                                $thumb = bookyol_book_cover_url( $article->ID );
                            }
                            $excerpt = $article->post_excerpt ? $article->post_excerpt : wp_trim_words( $article->post_content, 25 );
                            $bg      = $bg_variants[ $i % count( $bg_variants ) ];
                            ?>
                            <a class="bookyol-article-card" href="<?php echo esc_url( get_permalink( $article->ID ) ); ?>">
                                <div class="bookyol-article-card__img bookyol-article-card__img--<?php echo esc_attr( $bg ); ?>">
                                    <?php if ( $thumb ) : ?>
                                        <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $article->ID ) ); ?>" loading="lazy">
                                    <?php else : ?>
                                        <span>📝</span>
                                    <?php endif; ?>
                                </div>
                                <div class="bookyol-article-card__body">
                                    <span class="bookyol-article-card__tag" style="color: <?php echo esc_attr( $s['articles_color'] ); ?>;"><?php echo esc_html( get_the_date( '', $article->ID ) ); ?></span>
                                    <h3 class="bookyol-article-card__title"><?php echo esc_html( get_the_title( $article->ID ) ); ?></h3>
                                    <p class="bookyol-article-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

</div>
<?php
get_footer();
