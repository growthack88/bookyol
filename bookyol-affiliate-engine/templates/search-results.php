<?php
/**
 * Template: BookYol Search Results
 * v4.5.0 — Rich search results with book covers and cards
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$search_query = get_search_query();
$paged        = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;
?>
<div class="bookyol-search-page">

    <!-- Hero -->
    <div class="bookyol-search-page__hero">
        <div class="bookyol-search-page__hero-inner">
            <h1 class="bookyol-search-page__title">
                <?php
                if ( ! empty( $search_query ) ) {
                    /* translators: %s: search query */
                    printf( esc_html__( 'Results for "%s"', 'bookyol' ), esc_html( $search_query ) );
                } else {
                    esc_html_e( 'Search Books', 'bookyol' );
                }
                ?>
            </h1>
            <p class="bookyol-search-page__count">
                <?php
                /* translators: %d: number of results */
                printf( esc_html( _n( '%d result found', '%d results found', $wp_query->found_posts, 'bookyol' ) ), (int) $wp_query->found_posts );
                ?>
            </p>
            <form class="bookyol-search-page__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <input type="text" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search books, authors...', 'bookyol' ); ?>" />
                <input type="hidden" name="post_type" value="bookyol_book" />
                <button type="submit"><?php esc_html_e( 'Search', 'bookyol' ); ?></button>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="bookyol-search-page__container">
        <?php if ( have_posts() ) : ?>
            <div class="bookyol-search-page__grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    $post_type = get_post_type();
                    $is_book   = ( $post_type === 'bookyol_book' );

                    if ( $is_book ) :
                        $cover  = get_post_meta( get_the_ID(), '_bookyol_cover_url', true );
                        $author = get_post_meta( get_the_ID(), '_bookyol_book_author', true );
                        $rating = get_post_meta( get_the_ID(), '_bookyol_rating', true );
                        ?>
                        <a href="<?php the_permalink(); ?>" class="bookyol-search-page__card bookyol-search-page__card--book">
                            <div class="bookyol-search-page__card-cover">
                                <?php if ( $cover ) : ?>
                                    <img src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                <?php else : ?>
                                    <div class="bookyol-search-page__card-nocover">📚</div>
                                <?php endif; ?>
                                <?php if ( $rating ) : ?>
                                    <span class="bookyol-search-page__card-rating">★ <?php echo esc_html( $rating ); ?></span>
                                <?php endif; ?>
                                <span class="bookyol-search-page__card-badge"><?php esc_html_e( 'Book', 'bookyol' ); ?></span>
                            </div>
                            <div class="bookyol-search-page__card-info">
                                <h3><?php the_title(); ?></h3>
                                <?php if ( $author ) : ?>
                                    <p class="bookyol-search-page__card-author"><?php echo esc_html( 'by ' . $author ); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php else : ?>
                        <a href="<?php the_permalink(); ?>" class="bookyol-search-page__card bookyol-search-page__card--post">
                            <div class="bookyol-search-page__card-cover bookyol-search-page__card-cover--landscape">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium' ); ?>
                                <?php else : ?>
                                    <div class="bookyol-search-page__card-nocover">📝</div>
                                <?php endif; ?>
                                <span class="bookyol-search-page__card-badge"><?php esc_html_e( 'Article', 'bookyol' ); ?></span>
                            </div>
                            <div class="bookyol-search-page__card-info">
                                <h3><?php the_title(); ?></h3>
                                <p class="bookyol-search-page__card-date"><?php echo esc_html( get_the_date() ); ?></p>
                                <p class="bookyol-search-page__card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
                            </div>
                        </a>
                    <?php endif;
                endwhile;
                ?>
            </div>

            <div class="bookyol-search-page__pagination">
                <?php
                echo paginate_links( array(
                    'prev_text' => '&larr; ' . esc_html__( 'Previous', 'bookyol' ),
                    'next_text' => esc_html__( 'Next', 'bookyol' ) . ' &rarr;',
                ) );
                ?>
            </div>
        <?php else : ?>
            <div class="bookyol-search-page__empty">
                <span style="font-size:64px;">🔍</span>
                <h2><?php esc_html_e( 'No results found', 'bookyol' ); ?></h2>
                <p><?php esc_html_e( 'Try different keywords or browse our categories:', 'bookyol' ); ?></p>
                <?php
                if ( taxonomy_exists( 'book_category' ) ) :
                    $cats = get_terms( array( 'taxonomy' => 'book_category', 'hide_empty' => true, 'number' => 8 ) );
                    if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) :
                        ?>
                        <div class="bookyol-search-page__empty-cats">
                            <?php foreach ( $cats as $cat ) :
                                $link = get_term_link( $cat );
                                if ( is_wp_error( $link ) ) continue;
                                ?>
                                <a href="<?php echo esc_url( $link ); ?>" class="bookyol-search-page__empty-pill"><?php echo esc_html( $cat->name ); ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif;
                endif;
                ?>
            </div>
        <?php endif; ?>
    </div>

</div>
<?php
get_footer();
