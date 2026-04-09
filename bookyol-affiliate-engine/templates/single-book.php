<?php
/**
 * Template Name: BookYol Single Book
 * Single book display — v3.4.0 (bulletproof, no fatal errors)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! have_posts() ) {
    get_header();
    echo '<div style="max-width:800px;margin:60px auto;padding:24px;text-align:center;"><h1>Book not found</h1></div>';
    get_footer();
    return;
}

the_post();
get_header();

$book_id   = get_the_ID();
$title     = get_the_title();
$cover     = get_post_meta( $book_id, '_bookyol_cover_url', true );
$author    = get_post_meta( $book_id, '_bookyol_book_author', true );
$rating    = floatval( get_post_meta( $book_id, '_bookyol_rating', true ) );
$pages     = get_post_meta( $book_id, '_bookyol_pages', true );
$isbn      = get_post_meta( $book_id, '_bookyol_isbn', true );
$best_for  = get_post_meta( $book_id, '_bookyol_best_for', true );
$book_slug = get_post_field( 'post_name', $book_id );

// Stars
$full  = $rating ? min( (int) floor( $rating ), 5 ) : 0;
$empty = max( 5 - $full, 0 );
$stars = str_repeat( '★', $full ) . str_repeat( '☆', $empty );

// Affiliate links — simple array build
$platforms = array(
    'everand'   => array( 'Everand',      '#7C5CFC', '📱', 'Read Unlimited' ),
    'librofm'   => array( 'Libro.fm',     '#FF6B6B', '🎧', 'Listen Audiobook' ),
    'ebookscom' => array( 'Ebooks.com',   '#4A90D9', '📖', 'Buy Ebook' ),
    'bookshop'  => array( 'Bookshop.org', '#2ECC87', '📚', 'Buy Physical' ),
    'kobo'      => array( 'Kobo',         '#F5A623', '📕', 'Read on Kobo' ),
    'jamalon'   => array( 'Jamalon',      '#E84393', '🌍', 'Buy on Jamalon' ),
);

$links = array();
foreach ( $platforms as $key => $info ) {
    $u = get_post_meta( $book_id, '_bookyol_link_' . $key, true );
    if ( ! empty( $u ) && $u !== 'https://' ) {
        $links[ $key ] = $info;
    }
}

// Geo priority — simple fallback
$ordered_keys = array_keys( $links );
try {
    if ( class_exists( 'BookYol_Geo_Router' ) ) {
        $geo      = new BookYol_Geo_Router();
        $priority = $geo->get_platform_priority();
        $sorted   = array();
        foreach ( $priority as $p ) {
            if ( isset( $links[ $p ] ) ) {
                $sorted[] = $p;
            }
        }
        // Add any links not in priority
        foreach ( $ordered_keys as $k ) {
            if ( ! in_array( $k, $sorted, true ) ) {
                $sorted[] = $k;
            }
        }
        $ordered_keys = $sorted;
    }
} catch ( \Throwable $e ) {
    // keep $ordered_keys as-is
}

$primary_key = ! empty( $ordered_keys ) ? $ordered_keys[0] : null;
$secondary_keys = array_slice( $ordered_keys, 1 );

// Categories
$book_terms = array();
if ( taxonomy_exists( 'book_category' ) ) {
    $t = wp_get_post_terms( $book_id, 'book_category' );
    if ( ! is_wp_error( $t ) ) {
        $book_terms = $t;
    }
}

// Related books
$rel_args = array(
    'post_type'      => 'bookyol_book',
    'posts_per_page' => 5,
    'post__not_in'   => array( $book_id ),
    'post_status'    => 'publish',
    'orderby'        => 'rand',
);
if ( ! empty( $book_terms ) ) {
    $tids = wp_list_pluck( $book_terms, 'term_id' );
    $rel_args['tax_query'] = array(
        array( 'taxonomy' => 'book_category', 'field' => 'term_id', 'terms' => $tids ),
    );
}
$related = get_posts( $rel_args );

// Description
$desc = '';
if ( has_excerpt() ) {
    $desc .= '<p>' . wp_kses_post( get_the_excerpt() ) . '</p>';
}
ob_start();
the_content();
$desc .= ob_get_clean();
?>

<div class="bookyol-single">
<div class="bookyol-single__container">

<!-- Breadcrumb -->
<nav class="bookyol-crumb">
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
  <span class="bookyol-crumb__sep">›</span>
  <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>">Books</a>
  <?php if ( ! empty( $book_terms ) ) : ?>
    <span class="bookyol-crumb__sep">›</span>
    <a href="<?php echo esc_url( get_term_link( $book_terms[0] ) ); ?>"><?php echo esc_html( $book_terms[0]->name ); ?></a>
  <?php endif; ?>
  <span class="bookyol-crumb__sep">›</span>
  <span class="bookyol-crumb__current"><?php echo esc_html( $title ); ?></span>
</nav>

<!-- Grid -->
<div class="bookyol-single__grid">

  <!-- LEFT -->
  <div class="bookyol-single__left">
    <?php if ( $cover ) : ?>
      <div class="bookyol-single__cover">
        <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
      </div>
    <?php endif; ?>

    <div class="bookyol-single__meta-box">
      <?php if ( $pages ) : ?>
        <div class="bookyol-single__meta-row"><span class="meta-label">📄 Pages</span><span class="meta-value"><?php echo esc_html( $pages ); ?></span></div>
      <?php endif; ?>
      <?php if ( $isbn ) : ?>
        <div class="bookyol-single__meta-row"><span class="meta-label">🔢 ISBN</span><span class="meta-value"><?php echo esc_html( $isbn ); ?></span></div>
      <?php endif; ?>
      <?php if ( $best_for ) : ?>
        <div class="bookyol-single__meta-row"><span class="meta-label">🎯 Best For</span><span class="meta-value"><?php echo esc_html( $best_for ); ?></span></div>
      <?php endif; ?>
      <?php if ( $rating ) : ?>
        <div class="bookyol-single__meta-row"><span class="meta-label">⭐ Rating</span><span class="meta-value"><?php echo esc_html( $rating ); ?>/5</span></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="bookyol-single__right">

    <h1 class="bookyol-single__title"><?php echo esc_html( $title ); ?></h1>

    <?php if ( $author ) : ?>
      <p class="bookyol-single__author">by <strong><?php echo esc_html( $author ); ?></strong></p>
    <?php endif; ?>

    <?php if ( $rating ) : ?>
      <div class="bookyol-single__stars">
        <span class="stars-icons"><?php echo esc_html( $stars ); ?></span>
        <span class="stars-num"><?php echo esc_html( $rating ); ?> out of 5</span>
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
      <?php echo wp_kses_post( $desc ); ?>
    </div>

    <!-- ═══ GET THIS BOOK ═══ -->
    <?php if ( ! empty( $links ) ) : ?>
      <div class="bookyol-single__cta-section">
        <h3 class="bookyol-single__cta-heading">Get this book</h3>

        <?php if ( $primary_key ) :
          $pi = $links[ $primary_key ]; ?>
          <a href="<?php echo esc_url( home_url( '/go/' . $primary_key . '/' . $book_slug . '/' ) ); ?>"
             class="bookyol-single__cta-primary"
             style="background:<?php echo esc_attr( $pi[1] ); ?>;"
             target="_blank" rel="nofollow sponsored noopener">
            <span class="cta-icon"><?php echo $pi[2]; ?></span>
            <span class="cta-text"><strong><?php echo esc_html( $pi[3] ); ?></strong><small>on <?php echo esc_html( $pi[0] ); ?></small></span>
            <span class="cta-arrow">→</span>
          </a>
        <?php endif; ?>

        <?php if ( ! empty( $secondary_keys ) ) : ?>
          <div class="bookyol-single__cta-grid">
            <?php foreach ( $secondary_keys as $sk ) :
              if ( ! isset( $links[ $sk ] ) ) continue;
              $si = $links[ $sk ]; ?>
              <a href="<?php echo esc_url( home_url( '/go/' . $sk . '/' . $book_slug . '/' ) ); ?>"
                 class="bookyol-single__cta-secondary"
                 target="_blank" rel="nofollow sponsored noopener">
                <span><?php echo $si[2]; ?></span>
                <span><?php echo esc_html( $si[0] ); ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <p class="bookyol-single__cta-note">Clicking these links supports BookYol at no extra cost to you.</p>
      </div>
    <?php else : ?>
      <div class="bookyol-single__cta-section" style="text-align:center;color:#999;">
        <p>📚 This book will be available on multiple platforms soon.</p>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- RELATED BOOKS -->
<?php if ( ! empty( $related ) ) : ?>
  <div class="bookyol-single__related">
    <h2 class="bookyol-single__related-title">You might also like</h2>
    <div class="bookyol-books-grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:20px;">
      <?php foreach ( $related as $r ) :
        $rc = get_post_meta( $r->ID, '_bookyol_cover_url', true );
        $ra = get_post_meta( $r->ID, '_bookyol_book_author', true );
        $rr = get_post_meta( $r->ID, '_bookyol_rating', true );
      ?>
        <a href="<?php echo esc_url( get_permalink( $r->ID ) ); ?>" class="bookyol-book-card" style="display:block;text-decoration:none;">
          <div class="bookyol-book-card__img">
            <?php if ( $rr ) : ?>
              <span class="bookyol-book-card__rating"><span class="star">★</span> <?php echo esc_html( $rr ); ?></span>
            <?php endif; ?>
            <?php if ( $rc ) : ?>
              <img src="<?php echo esc_url( $rc ); ?>" alt="<?php echo esc_attr( $r->post_title ); ?>" loading="lazy" />
            <?php endif; ?>
          </div>
          <div class="bookyol-book-card__info">
            <div class="bookyol-book-card__title"><?php echo esc_html( $r->post_title ); ?></div>
            <div class="bookyol-book-card__author"><?php echo esc_html( $ra ); ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

</div>
</div>

<?php get_footer(); ?>
