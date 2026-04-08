<?php
/**
 * Single Book Template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

while ( have_posts() ) :
    the_post();

    $book_id  = get_the_ID();
    $cover    = get_post_meta( $book_id, '_bookyol_cover_url', true );
    if ( empty( $cover ) && has_post_thumbnail( $book_id ) ) {
        $cover = get_the_post_thumbnail_url( $book_id, 'large' );
    }
    $author   = get_post_meta( $book_id, '_bookyol_book_author', true );
    $rating   = get_post_meta( $book_id, '_bookyol_rating', true );
    $pages    = get_post_meta( $book_id, '_bookyol_pages', true );
    $isbn     = get_post_meta( $book_id, '_bookyol_isbn', true );
    $best_for = get_post_meta( $book_id, '_bookyol_best_for', true );

    $platforms = array(
        'everand'   => array( 'name' => 'Everand',      'color' => '#7C5CFC', 'icon' => '📱' ),
        'librofm'   => array( 'name' => 'Libro.fm',     'color' => '#FF6B6B', 'icon' => '🎧' ),
        'ebookscom' => array( 'name' => 'Ebooks.com',   'color' => '#4A90D9', 'icon' => '📖' ),
        'bookshop'  => array( 'name' => 'Bookshop.org', 'color' => '#2ECC87', 'icon' => '📚' ),
        'kobo'      => array( 'name' => 'Kobo',         'color' => '#F5A623', 'icon' => '📕' ),
        'jamalon'   => array( 'name' => 'Jamalon',      'color' => '#E84393', 'icon' => '🌍' ),
    );

    $available_links = array();
    foreach ( $platforms as $slug => $info ) {
        $url = get_post_meta( $book_id, '_bookyol_link_' . $slug, true );
        if ( $url && $url !== 'https://' ) {
            $available_links[ $slug ] = array_merge( $info, array( 'url' => $url ) );
        }
    }

    // Geo-routed primary CTA.
    $geo      = new BookYol_Geo_Router();
    $priority = $geo->get_platform_priority();
    $primary_platform = null;
    foreach ( $priority as $p ) {
        if ( isset( $available_links[ $p ] ) ) {
            $primary_platform = $p;
            break;
        }
    }
    if ( ! $primary_platform && ! empty( $available_links ) ) {
        $primary_platform = array_key_first( $available_links );
    }

    $full_stars = $rating ? (int) floor( floatval( $rating ) ) : 0;
    if ( $full_stars < 0 ) $full_stars = 0;
    if ( $full_stars > 5 ) $full_stars = 5;
    $stars_html = str_repeat( '★', $full_stars ) . str_repeat( '☆', 5 - $full_stars );

    $book_terms = wp_get_post_terms( $book_id, 'book_category' );

    // Related books.
    $term_ids = wp_get_post_terms( $book_id, 'book_category', array( 'fields' => 'ids' ) );
    $related_args = array(
        'post_type'      => 'bookyol_book',
        'posts_per_page' => 5,
        'post__not_in'   => array( $book_id ),
        'post_status'    => 'publish',
    );
    if ( ! empty( $term_ids ) && ! is_wp_error( $term_ids ) ) {
        $related_args['tax_query'] = array(
            array( 'taxonomy' => 'book_category', 'field' => 'term_id', 'terms' => $term_ids ),
        );
    } else {
        $related_args['orderby'] = 'rand';
    }
    $related_books = get_posts( $related_args );

    $book_slug = get_post_field( 'post_name', $book_id );
    ?>

    <div class="bookyol-single">
        <div class="bookyol-container">

            <nav class="bookyol-breadcrumb">
                <a href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'Home', 'bookyol' ); ?></a>
                <span>›</span>
                <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>"><?php esc_html_e( 'Books', 'bookyol' ); ?></a>
                <?php if ( ! empty( $book_terms ) && ! is_wp_error( $book_terms ) ) : ?>
                    <span>›</span>
                    <a href="<?php echo esc_url( get_term_link( $book_terms[0] ) ); ?>"><?php echo esc_html( $book_terms[0]->name ); ?></a>
                <?php endif; ?>
                <span>›</span>
                <span class="current"><?php the_title(); ?></span>
            </nav>

            <div class="bookyol-single__main">

                <div class="bookyol-single__cover-col">
                    <?php if ( $cover ) : ?>
                        <div class="bookyol-single__cover">
                            <img src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>">
                        </div>
                    <?php endif; ?>

                    <div class="bookyol-single__quick-info">
                        <?php if ( $pages ) : ?>
                            <div class="bookyol-single__qi-item">
                                <span class="qi-label"><?php esc_html_e( 'Pages', 'bookyol' ); ?></span>
                                <span class="qi-value"><?php echo esc_html( $pages ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $isbn ) : ?>
                            <div class="bookyol-single__qi-item">
                                <span class="qi-label"><?php esc_html_e( 'ISBN', 'bookyol' ); ?></span>
                                <span class="qi-value"><?php echo esc_html( $isbn ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $best_for ) : ?>
                            <div class="bookyol-single__qi-item">
                                <span class="qi-label"><?php esc_html_e( 'Best For', 'bookyol' ); ?></span>
                                <span class="qi-value"><?php echo esc_html( $best_for ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bookyol-single__info-col">
                    <h1 class="bookyol-single__title"><?php the_title(); ?></h1>

                    <?php if ( $author ) : ?>
                        <p class="bookyol-single__author"><?php echo esc_html__( 'by', 'bookyol' ) . ' ' . esc_html( $author ); ?></p>
                    <?php endif; ?>

                    <?php if ( $rating ) : ?>
                        <div class="bookyol-single__rating">
                            <span class="stars"><?php echo esc_html( $stars_html ); ?></span>
                            <span class="score"><?php echo esc_html( $rating ); ?>/5</span>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $book_terms ) && ! is_wp_error( $book_terms ) ) : ?>
                        <div class="bookyol-single__tags">
                            <?php foreach ( $book_terms as $term ) : ?>
                                <a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="bookyol-single__tag"><?php echo esc_html( $term->name ); ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="bookyol-single__desc">
                        <?php
                        if ( has_excerpt() ) {
                            echo '<p>' . esc_html( get_the_excerpt() ) . '</p>';
                        }
                        the_content();
                        ?>
                    </div>

                    <?php if ( ! empty( $available_links ) ) : ?>
                        <div class="bookyol-single__actions">
                            <h3 class="bookyol-single__actions-title"><?php esc_html_e( 'Read this book on:', 'bookyol' ); ?></h3>

                            <?php if ( $primary_platform && isset( $available_links[ $primary_platform ] ) ) :
                                $pl = $available_links[ $primary_platform ]; ?>
                                <a href="<?php echo esc_url( home_url( '/go/' . $primary_platform . '/' . $book_slug . '/' ) ); ?>"
                                   class="bookyol-single__btn-primary"
                                   style="background: <?php echo esc_attr( $pl['color'] ); ?>;"
                                   target="_blank" rel="nofollow sponsored noopener">
                                    <?php echo esc_html( $pl['icon'] ); ?> <?php echo esc_html( sprintf( __( 'Read on %s', 'bookyol' ), $pl['name'] ) ); ?> →
                                </a>
                            <?php endif; ?>

                            <?php
                            $secondary = array_diff( array_keys( $available_links ), array( $primary_platform ) );
                            if ( ! empty( $secondary ) ) : ?>
                                <div class="bookyol-single__btn-grid">
                                    <?php foreach ( $secondary as $slug ) :
                                        $pl = $available_links[ $slug ]; ?>
                                        <a href="<?php echo esc_url( home_url( '/go/' . $slug . '/' . $book_slug . '/' ) ); ?>"
                                           class="bookyol-single__btn-secondary"
                                           target="_blank" rel="nofollow sponsored noopener">
                                            <?php echo esc_html( $pl['icon'] ); ?> <?php echo esc_html( $pl['name'] ); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ( ! empty( $related_books ) ) : ?>
                <div class="bookyol-single__related">
                    <h2 class="bookyol-section__title"><?php esc_html_e( 'You might also like', 'bookyol' ); ?></h2>
                    <div class="bookyol-books-grid">
                        <?php foreach ( $related_books as $rel ) :
                            $rel_cover  = get_post_meta( $rel->ID, '_bookyol_cover_url', true );
                            if ( empty( $rel_cover ) && has_post_thumbnail( $rel->ID ) ) {
                                $rel_cover = get_the_post_thumbnail_url( $rel->ID, 'medium' );
                            }
                            $rel_author = get_post_meta( $rel->ID, '_bookyol_book_author', true );
                            $rel_rating = get_post_meta( $rel->ID, '_bookyol_rating', true );
                            ?>
                            <a href="<?php echo esc_url( get_permalink( $rel->ID ) ); ?>" class="bookyol-book-card">
                                <div class="bookyol-book-card__img">
                                    <?php if ( $rel_rating ) : ?>
                                        <span class="bookyol-book-card__rating"><span class="star">★</span> <?php echo esc_html( $rel_rating ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $rel_cover ) : ?>
                                        <img src="<?php echo esc_url( $rel_cover ); ?>" alt="<?php echo esc_attr( $rel->post_title ); ?>" loading="lazy">
                                    <?php endif; ?>
                                </div>
                                <div class="bookyol-book-card__info">
                                    <div class="bookyol-book-card__title"><?php echo esc_html( $rel->post_title ); ?></div>
                                    <?php if ( $rel_author ) : ?>
                                        <div class="bookyol-book-card__author"><?php echo esc_html( $rel_author ); ?></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

<?php
endwhile;

get_footer();
