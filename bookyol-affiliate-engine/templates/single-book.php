<?php
/**
 * Template Name: BookYol Single Book
 * Single book display template — v3.3.0 (robust, error-safe).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure this is a valid book post.
if ( ! have_posts() ) {
    get_header();
    echo '<div class="bookyol-single__container" style="padding:60px 24px;text-align:center;"><h1>Book not found</h1></div>';
    get_footer();
    return;
}

the_post();
get_header();

// Get book data safely.
$book_id  = get_the_ID();
$title    = get_the_title();
$cover    = get_post_meta( $book_id, '_bookyol_cover_url', true );
if ( empty( $cover ) && has_post_thumbnail( $book_id ) ) {
    $cover = get_the_post_thumbnail_url( $book_id, 'large' );
}
$author   = get_post_meta( $book_id, '_bookyol_book_author', true );
$rating   = floatval( get_post_meta( $book_id, '_bookyol_rating', true ) );
$pages    = get_post_meta( $book_id, '_bookyol_pages', true );
$isbn     = get_post_meta( $book_id, '_bookyol_isbn', true );
$best_for = get_post_meta( $book_id, '_bookyol_best_for', true );

// Build star rating string (full, half, empty).
$full_stars  = $rating ? (int) floor( $rating ) : 0;
if ( $full_stars < 0 ) $full_stars = 0;
if ( $full_stars > 5 ) $full_stars = 5;
$half_star   = ( ( $rating - $full_stars ) >= 0.5 );
$empty_stars = 5 - $full_stars - ( $half_star ? 1 : 0 );
if ( $empty_stars < 0 ) $empty_stars = 0;
$stars_html  = str_repeat( '★', $full_stars ) . ( $half_star ? '★' : '' ) . str_repeat( '☆', $empty_stars );

// Get affiliate links safely.
$platform_config = array(
    'everand'   => array( 'name' => 'Everand',      'color' => '#7C5CFC', 'icon' => '📱', 'label' => 'Read Unlimited' ),
    'librofm'   => array( 'name' => 'Libro.fm',     'color' => '#FF6B6B', 'icon' => '🎧', 'label' => 'Listen Audiobook' ),
    'ebookscom' => array( 'name' => 'Ebooks.com',   'color' => '#4A90D9', 'icon' => '📖', 'label' => 'Buy Ebook' ),
    'bookshop'  => array( 'name' => 'Bookshop.org', 'color' => '#2ECC87', 'icon' => '📚', 'label' => 'Buy Physical' ),
    'kobo'      => array( 'name' => 'Kobo',         'color' => '#F5A623', 'icon' => '📕', 'label' => 'Read on Kobo' ),
    'jamalon'   => array( 'name' => 'Jamalon',      'color' => '#E84393', 'icon' => '🌍', 'label' => 'Buy on Jamalon' ),
);

$available_links = array();
foreach ( $platform_config as $slug => $info ) {
    $url = get_post_meta( $book_id, '_bookyol_link_' . $slug, true );
    if ( ! empty( $url ) && $url !== 'https://' && filter_var( $url, FILTER_VALIDATE_URL ) ) {
        $available_links[ $slug ] = array_merge( $info, array( 'url' => $url ) );
    }
}

// Geo routing with robust fallback.
$primary_platform    = null;
$secondary_platforms = array();

try {
    if ( class_exists( 'BookYol_Geo_Router' ) ) {
        $geo      = new BookYol_Geo_Router();
        $priority = method_exists( $geo, 'get_platform_priority' ) ? $geo->get_platform_priority() : array();
        if ( is_array( $priority ) && ! empty( $priority ) ) {
            foreach ( $priority as $p ) {
                if ( isset( $available_links[ $p ] ) ) {
                    if ( $primary_platform === null ) {
                        $primary_platform = $p;
                    } else {
                        $secondary_platforms[] = $p;
                    }
                }
            }
        }
    }
} catch ( \Throwable $e ) {
    // Silent fallback — will use the array_keys fallback below.
    $primary_platform    = null;
    $secondary_platforms = array();
}

// Fallback if geo routing didn't surface a primary.
if ( $primary_platform === null && ! empty( $available_links ) ) {
    $keys                = array_keys( $available_links );
    $primary_platform    = $keys[0];
    $secondary_platforms = array_slice( $keys, 1 );
}

// Get categories safely.
$book_terms = array();
if ( taxonomy_exists( 'book_category' ) ) {
    $maybe = wp_get_post_terms( $book_id, 'book_category' );
    if ( ! is_wp_error( $maybe ) && is_array( $maybe ) ) {
        $book_terms = $maybe;
    }
}

// Related books.
$related_books = array();
$related_args  = array(
    'post_type'      => 'bookyol_book',
    'posts_per_page' => 5,
    'post__not_in'   => array( $book_id ),
    'post_status'    => 'publish',
    'orderby'        => 'rand',
);
if ( ! empty( $book_terms ) ) {
    $term_ids = wp_list_pluck( $book_terms, 'term_id' );
    if ( ! empty( $term_ids ) ) {
        $related_args['tax_query'] = array(
            array( 'taxonomy' => 'book_category', 'field' => 'term_id', 'terms' => $term_ids ),
        );
    }
}
$related_books = get_posts( $related_args );

$book_slug = get_post_field( 'post_name', $book_id );
?>

<div class="bookyol-single">
    <div class="bookyol-single__container">

        <!-- Breadcrumb -->
        <nav class="bookyol-crumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'bookyol' ); ?></a>
            <span class="bookyol-crumb__sep">›</span>
            <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>"><?php esc_html_e( 'Books', 'bookyol' ); ?></a>
            <?php if ( ! empty( $book_terms ) ) : ?>
                <span class="bookyol-crumb__sep">›</span>
                <a href="<?php echo esc_url( get_term_link( $book_terms[0] ) ); ?>"><?php echo esc_html( $book_terms[0]->name ); ?></a>
            <?php endif; ?>
            <span class="bookyol-crumb__sep">›</span>
            <span class="bookyol-crumb__current"><?php echo esc_html( $title ); ?></span>
        </nav>

        <!-- Main Content Grid -->
        <div class="bookyol-single__grid">

            <!-- LEFT: Cover + Quick Info -->
            <div class="bookyol-single__left">
                <?php if ( $cover ) : ?>
                    <div class="bookyol-single__cover">
                        <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                    </div>
                <?php endif; ?>

                <div class="bookyol-single__meta-box">
                    <?php if ( $pages ) : ?>
                        <div class="bookyol-single__meta-row">
                            <span class="meta-label">📄 <?php esc_html_e( 'Pages', 'bookyol' ); ?></span>
                            <span class="meta-value"><?php echo esc_html( $pages ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( $isbn ) : ?>
                        <div class="bookyol-single__meta-row">
                            <span class="meta-label">🔢 <?php esc_html_e( 'ISBN', 'bookyol' ); ?></span>
                            <span class="meta-value"><?php echo esc_html( $isbn ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( $best_for ) : ?>
                        <div class="bookyol-single__meta-row">
                            <span class="meta-label">🎯 <?php esc_html_e( 'Best For', 'bookyol' ); ?></span>
                            <span class="meta-value"><?php echo esc_html( $best_for ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( $rating ) : ?>
                        <div class="bookyol-single__meta-row">
                            <span class="meta-label">⭐ <?php esc_html_e( 'Rating', 'bookyol' ); ?></span>
                            <span class="meta-value"><?php echo esc_html( $rating ); ?>/5</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT: Info + CTAs -->
            <div class="bookyol-single__right">

                <h1 class="bookyol-single__title"><?php echo esc_html( $title ); ?></h1>

                <?php if ( $author ) : ?>
                    <p class="bookyol-single__author"><?php esc_html_e( 'by', 'bookyol' ); ?> <strong><?php echo esc_html( $author ); ?></strong></p>
                <?php endif; ?>

                <?php if ( $rating ) : ?>
                    <div class="bookyol-single__stars">
                        <span class="stars-icons"><?php echo esc_html( $stars_html ); ?></span>
                        <span class="stars-num"><?php echo esc_html( $rating ); ?> <?php esc_html_e( 'out of 5', 'bookyol' ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $book_terms ) ) : ?>
                    <div class="bookyol-single__tags">
                        <?php foreach ( $book_terms as $bt ) : ?>
                            <a href="<?php echo esc_url( get_term_link( $bt ) ); ?>" class="bookyol-single__tag"><?php echo esc_html( $bt->name ); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="bookyol-single__desc">
                    <?php
                    if ( has_excerpt() ) {
                        echo '<p>' . wp_kses_post( get_the_excerpt() ) . '</p>';
                    }
                    the_content();
                    ?>
                </div>

                <?php if ( ! empty( $available_links ) ) : ?>
                    <div class="bookyol-single__cta-section">
                        <h3 class="bookyol-single__cta-heading"><?php esc_html_e( 'Get this book', 'bookyol' ); ?></h3>

                        <?php if ( $primary_platform && isset( $available_links[ $primary_platform ] ) ) :
                            $pdata = $available_links[ $primary_platform ];
                            ?>
                            <a href="<?php echo esc_url( home_url( '/go/' . $primary_platform . '/' . $book_slug . '/' ) ); ?>"
                               class="bookyol-single__cta-primary"
                               style="background: <?php echo esc_attr( $pdata['color'] ); ?>;"
                               target="_blank" rel="nofollow sponsored noopener">
                                <span class="cta-icon"><?php echo esc_html( $pdata['icon'] ); ?></span>
                                <span class="cta-text">
                                    <strong><?php echo esc_html( $pdata['label'] ); ?></strong>
                                    <small><?php esc_html_e( 'on', 'bookyol' ); ?> <?php echo esc_html( $pdata['name'] ); ?></small>
                                </span>
                                <span class="cta-arrow">→</span>
                            </a>
                        <?php endif; ?>

                        <?php if ( ! empty( $secondary_platforms ) ) : ?>
                            <div class="bookyol-single__cta-grid">
                                <?php foreach ( $secondary_platforms as $sp ) :
                                    if ( ! isset( $available_links[ $sp ] ) ) continue;
                                    $sdata = $available_links[ $sp ];
                                    ?>
                                    <a href="<?php echo esc_url( home_url( '/go/' . $sp . '/' . $book_slug . '/' ) ); ?>"
                                       class="bookyol-single__cta-secondary"
                                       target="_blank" rel="nofollow sponsored noopener">
                                        <span><?php echo esc_html( $sdata['icon'] ); ?></span>
                                        <span><?php echo esc_html( $sdata['name'] ); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <p class="bookyol-single__cta-note"><?php esc_html_e( 'Clicking these links supports BookYol at no extra cost to you.', 'bookyol' ); ?></p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <?php if ( ! empty( $related_books ) ) : ?>
            <div class="bookyol-single__related">
                <h2 class="bookyol-single__related-title"><?php esc_html_e( 'You might also like', 'bookyol' ); ?></h2>
                <div class="bookyol-books-grid" style="grid-template-columns: repeat(5, 1fr);">
                    <?php foreach ( $related_books as $rel ) :
                        $rc = get_post_meta( $rel->ID, '_bookyol_cover_url', true );
                        if ( empty( $rc ) && has_post_thumbnail( $rel->ID ) ) {
                            $rc = get_the_post_thumbnail_url( $rel->ID, 'medium' );
                        }
                        $ra = get_post_meta( $rel->ID, '_bookyol_book_author', true );
                        $rr = get_post_meta( $rel->ID, '_bookyol_rating', true );
                        ?>
                        <a href="<?php echo esc_url( get_permalink( $rel->ID ) ); ?>" class="bookyol-book-card">
                            <div class="bookyol-book-card__img">
                                <?php if ( $rr ) : ?>
                                    <span class="bookyol-book-card__rating"><span class="star">★</span> <?php echo esc_html( $rr ); ?></span>
                                <?php endif; ?>
                                <?php if ( $rc ) : ?>
                                    <img src="<?php echo esc_url( $rc ); ?>" alt="<?php echo esc_attr( $rel->post_title ); ?>" loading="lazy">
                                <?php endif; ?>
                            </div>
                            <div class="bookyol-book-card__info">
                                <div class="bookyol-book-card__title"><?php echo esc_html( $rel->post_title ); ?></div>
                                <?php if ( $ra ) : ?>
                                    <div class="bookyol-book-card__author"><?php echo esc_html( $ra ); ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>
