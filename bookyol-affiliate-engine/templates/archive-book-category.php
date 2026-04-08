<?php
/**
 * Archive template for Book Categories.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$term  = get_queried_object();
$paged = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;

$books = new WP_Query( array(
    'post_type'      => 'bookyol_book',
    'tax_query'      => array(
        array( 'taxonomy' => 'book_category', 'field' => 'slug', 'terms' => $term->slug ),
    ),
    'posts_per_page' => 20,
    'paged'          => $paged,
) );
?>
<div class="bookyol-archive">
    <div class="bookyol-container">
        <div class="bookyol-archive__header">
            <h1 class="bookyol-archive__title"><?php echo esc_html( sprintf( __( 'Best %s Books', 'bookyol' ), $term->name ) ); ?></h1>
            <p class="bookyol-archive__desc">
                <?php
                echo esc_html(
                    $term->description
                        ? $term->description
                        : sprintf( __( 'Discover the best %s books curated by BookYol.', 'bookyol' ), strtolower( $term->name ) )
                );
                ?>
            </p>
            <span class="bookyol-archive__count"><?php echo esc_html( sprintf( _n( '%d book', '%d books', (int) $books->found_posts, 'bookyol' ), (int) $books->found_posts ) ); ?></span>
        </div>

        <?php if ( $books->have_posts() ) : ?>
            <div class="bookyol-books-grid">
                <?php while ( $books->have_posts() ) :
                    $books->the_post();
                    $cover  = get_post_meta( get_the_ID(), '_bookyol_cover_url', true );
                    if ( empty( $cover ) && has_post_thumbnail( get_the_ID() ) ) {
                        $cover = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                    }
                    $author = get_post_meta( get_the_ID(), '_bookyol_book_author', true );
                    $rating = get_post_meta( get_the_ID(), '_bookyol_rating', true );
                    ?>
                    <a href="<?php the_permalink(); ?>" class="bookyol-book-card">
                        <div class="bookyol-book-card__img">
                            <?php if ( $rating ) : ?>
                                <span class="bookyol-book-card__rating"><span class="star">★</span> <?php echo esc_html( $rating ); ?></span>
                            <?php endif; ?>
                            <?php if ( $cover ) : ?>
                                <img src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="bookyol-book-card__info">
                            <div class="bookyol-book-card__title"><?php the_title(); ?></div>
                            <?php if ( $author ) : ?>
                                <div class="bookyol-book-card__author"><?php echo esc_html( $author ); ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

            <div class="bookyol-pagination">
                <?php
                echo paginate_links( array(
                    'total'     => $books->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => __( '← Previous', 'bookyol' ),
                    'next_text' => __( 'Next →', 'bookyol' ),
                ) );
                ?>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'No books found in this category yet.', 'bookyol' ); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php
get_footer();
