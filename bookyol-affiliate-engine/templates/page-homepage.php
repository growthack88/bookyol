<?php
/**
 * Template Name: BookYol Homepage
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$s = bookyol_get_homepage_settings();

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

$hero_books     = bookyol_get_homepage_books( $s['hero_shelf_source'], $s['hero_shelf_book_ids'], $s['hero_shelf_count'] );
$trending_books = bookyol_get_homepage_books( $s['trending_source'], $s['trending_book_ids'], $s['trending_count'] );
$new_books      = bookyol_get_homepage_books( $s['new_source'], $s['new_book_ids'], $s['new_count'] );
$audio_books    = bookyol_get_homepage_books( ! empty( $s['audio_book_ids'] ) ? 'featured' : 'latest', $s['audio_book_ids'], 3 );

$hero_title_html = esc_html( $s['hero_title'] );
if ( ! empty( $s['hero_title_highlight'] ) ) {
    $highlight_esc = esc_html( $s['hero_title_highlight'] );
    $hero_title_html = str_replace(
        $highlight_esc,
        '<em>' . $highlight_esc . '</em>',
        $hero_title_html
    );
}
?>
<div class="bookyol-home">

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
            <div class="bookyol-shelf">
                <?php foreach ( $hero_books as $book ) :
                    $cover  = bookyol_book_cover_url( $book->ID );
                    if ( ! $cover ) continue;
                    $author = get_post_meta( $book->ID, '_bookyol_book_author', true );
                    ?>
                    <a class="bookyol-shelf__item" href="<?php echo esc_url( get_permalink( $book->ID ) ); ?>">
                        <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $book->ID ) ); ?>" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

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
                        $cover  = bookyol_book_cover_url( $book->ID );
                        $author = get_post_meta( $book->ID, '_bookyol_book_author', true );
                        $rating = get_post_meta( $book->ID, '_bookyol_rating', true );
                        $is_first = ( $i === 0 );
                        $is_new   = bookyol_is_new_book( $book->ID );
                        ?>
                        <a class="bookyol-book-card" href="<?php echo esc_url( get_permalink( $book->ID ) ); ?>">
                            <div class="bookyol-book-card__img">
                                <?php if ( $cover ) : ?>
                                    <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $book->ID ) ); ?>" loading="lazy">
                                <?php endif; ?>
                                <?php if ( $is_first ) : ?>
                                    <span class="bookyol-book-card__badge bookyol-badge--trending"><?php esc_html_e( '🔥 Trending', 'bookyol' ); ?></span>
                                <?php elseif ( $is_new ) : ?>
                                    <span class="bookyol-book-card__badge bookyol-badge--new"><?php esc_html_e( 'New', 'bookyol' ); ?></span>
                                <?php endif; ?>
                                <?php if ( $rating ) : ?>
                                    <span class="bookyol-book-card__rating"><span class="star">★</span> <?php echo esc_html( $rating ); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="bookyol-book-card__info">
                                <h3 class="bookyol-book-card__title"><?php echo esc_html( get_the_title( $book->ID ) ); ?></h3>
                                <?php if ( $author ) : ?>
                                    <p class="bookyol-book-card__author"><?php echo esc_html( $author ); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( ! empty( $categories ) ) : ?>
        <section class="bookyol-section">
            <div class="bookyol-container">
                <div class="bookyol-section__header">
                    <div class="bookyol-section__label">
                        <h2 class="bookyol-section__title"><?php esc_html_e( 'Explore by Category', 'bookyol' ); ?></h2>
                    </div>
                </div>
                <div class="bookyol-categories">
                    <?php foreach ( $categories as $cat ) :
                        if ( empty( $cat['name'] ) ) continue;
                        $class = isset( $cat['color_class'] ) && $cat['color_class'] ? $cat['color_class'] : 'biz';
                        $url   = isset( $cat['url'] ) ? $cat['url'] : '#';
                        $icon  = isset( $cat['icon'] ) ? $cat['icon'] : '';
                        ?>
                        <a class="bookyol-cat-pill bookyol-cat-pill--<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $url ); ?>">
                            <?php if ( $icon ) : ?>
                                <span class="bookyol-cat-pill__icon"><?php echo esc_html( $icon ); ?></span>
                            <?php endif; ?>
                            <span class="bookyol-cat-pill__name"><?php echo esc_html( $cat['name'] ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

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
                                if ( ! $cover ) continue;
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

    <?php if ( ! empty( $collections ) ) : ?>
        <section class="bookyol-section">
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

    <?php if ( ! empty( $new_books ) ) : ?>
        <section class="bookyol-section bookyol-section--gray">
            <div class="bookyol-container">
                <div class="bookyol-section__header">
                    <div class="bookyol-section__label">
                        <span class="bookyol-section__dot" style="background: <?php echo esc_attr( $s['new_color'] ); ?>;"></span>
                        <h2 class="bookyol-section__title"><?php echo esc_html( $s['new_title'] ); ?></h2>
                    </div>
                </div>
                <div class="bookyol-books-grid">
                    <?php foreach ( $new_books as $book ) :
                        $cover  = bookyol_book_cover_url( $book->ID );
                        $author = get_post_meta( $book->ID, '_bookyol_book_author', true );
                        $rating = get_post_meta( $book->ID, '_bookyol_rating', true );
                        ?>
                        <a class="bookyol-book-card" href="<?php echo esc_url( get_permalink( $book->ID ) ); ?>">
                            <div class="bookyol-book-card__img">
                                <?php if ( $cover ) : ?>
                                    <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title( $book->ID ) ); ?>" loading="lazy">
                                <?php endif; ?>
                                <span class="bookyol-book-card__badge bookyol-badge--new" style="background: <?php echo esc_attr( $s['new_color'] ); ?>;"><?php esc_html_e( 'New', 'bookyol' ); ?></span>
                                <?php if ( $rating ) : ?>
                                    <span class="bookyol-book-card__rating"><span class="star">★</span> <?php echo esc_html( $rating ); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="bookyol-book-card__info">
                                <h3 class="bookyol-book-card__title"><?php echo esc_html( get_the_title( $book->ID ) ); ?></h3>
                                <?php if ( $author ) : ?>
                                    <p class="bookyol-book-card__author"><?php echo esc_html( $author ); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( ! empty( $s['newsletter_show'] ) ) : ?>
        <section class="bookyol-section">
            <div class="bookyol-container">
                <div class="bookyol-newsletter">
                    <h2 class="bookyol-newsletter__title"><?php echo esc_html( $s['newsletter_title'] ); ?></h2>
                    <p class="bookyol-newsletter__subtitle"><?php echo esc_html( $s['newsletter_subtitle'] ); ?></p>
                    <?php if ( ! empty( $s['newsletter_form_action'] ) ) : ?>
                        <form class="bookyol-newsletter__form" action="<?php echo esc_url( $s['newsletter_form_action'] ); ?>" method="post">
                            <input type="email" name="email" placeholder="<?php esc_attr_e( 'your@email.com', 'bookyol' ); ?>" required />
                            <button type="submit"><?php echo esc_html( $s['newsletter_btn_text'] ); ?></button>
                        </form>
                    <?php endif; ?>
                    <p class="bookyol-newsletter__note"><?php echo esc_html( $s['newsletter_note'] ); ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( ! empty( $s['articles_show'] ) ) :
        $articles = get_posts( array(
            'post_type'      => $s['articles_source'] === 'books' ? 'bookyol_book' : 'post',
            'posts_per_page' => intval( $s['articles_count'] ),
            'post_status'    => 'publish',
        ) );
        if ( ! empty( $articles ) ) :
            $bg_variants = array( 'blue', 'coral', 'violet', 'green', 'amber' );
            ?>
        <section class="bookyol-section bookyol-section--gray">
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
