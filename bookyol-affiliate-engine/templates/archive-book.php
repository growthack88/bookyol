<?php
/**
 * Template: BookYol All Books Archive
 * v4.5.0 — Rich archive with covers, search, sort, category filter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$paged      = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;
$search     = isset( $_GET['bookq'] ) ? sanitize_text_field( wp_unslash( $_GET['bookq'] ) ) : '';
$sort       = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'latest';
$cat_filter = isset( $_GET['cat'] ) ? sanitize_text_field( wp_unslash( $_GET['cat'] ) ) : '';

$query_args = array(
    'post_type'      => 'bookyol_book',
    'posts_per_page' => 24,
    'paged'          => $paged,
    'post_status'    => 'publish',
);

if ( ! empty( $search ) ) {
    $query_args['s'] = $search;
}

if ( ! empty( $cat_filter ) && taxonomy_exists( 'book_category' ) ) {
    $query_args['tax_query'] = array(
        array( 'taxonomy' => 'book_category', 'field' => 'slug', 'terms' => $cat_filter ),
    );
}

switch ( $sort ) {
    case 'title_asc':
        $query_args['orderby'] = 'title';
        $query_args['order']   = 'ASC';
        break;
    case 'title_desc':
        $query_args['orderby'] = 'title';
        $query_args['order']   = 'DESC';
        break;
    case 'rating':
        $query_args['meta_key'] = '_bookyol_rating';
        $query_args['orderby']  = 'meta_value_num';
        $query_args['order']    = 'DESC';
        break;
    default:
        $query_args['orderby'] = 'date';
        $query_args['order']   = 'DESC';
        break;
}

$books_query = new WP_Query( $query_args );
$total       = $books_query->found_posts;

$all_cats = array();
if ( taxonomy_exists( 'book_category' ) ) {
    $maybe = get_terms( array(
        'taxonomy'   => 'book_category',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ) );
    if ( ! is_wp_error( $maybe ) ) {
        $all_cats = $maybe;
    }
}

$archive_url = get_post_type_archive_link( 'bookyol_book' );
?>
<div class="bookyol-archive">

    <!-- Hero -->
    <div class="bookyol-archive__hero" style="background: linear-gradient(135deg, rgba(124,92,252,0.08), #F3F0FF);">
        <div class="bookyol-archive__hero-inner">
            <span class="bookyol-archive__emoji">📚</span>
            <h1 class="bookyol-archive__title"><?php esc_html_e( 'All Books', 'bookyol' ); ?></h1>
            <p class="bookyol-archive__desc">
                <?php
                /* translators: %d: total number of books */
                printf( esc_html__( 'Explore our complete library of %d curated books.', 'bookyol' ), (int) $total );
                ?>
            </p>
            <form class="bookyol-archive__search" method="get" action="<?php echo esc_url( $archive_url ); ?>">
                <input type="text" name="bookq" placeholder="<?php esc_attr_e( 'Search books...', 'bookyol' ); ?>" value="<?php echo esc_attr( $search ); ?>" />
                <?php if ( $cat_filter ) : ?>
                    <input type="hidden" name="cat" value="<?php echo esc_attr( $cat_filter ); ?>" />
                <?php endif; ?>
                <button type="submit" style="background:#7C5CFC;"><?php esc_html_e( 'Search', 'bookyol' ); ?></button>
            </form>
        </div>
    </div>

    <!-- Category pills -->
    <?php if ( ! empty( $all_cats ) ) : ?>
        <div class="bookyol-archive__cats">
            <div class="bookyol-archive__cats-inner">
                <a href="<?php echo esc_url( $archive_url ); ?>" class="bookyol-archive__cat-pill <?php echo empty( $cat_filter ) ? 'bookyol-archive__cat-pill--active' : ''; ?>" <?php if ( empty( $cat_filter ) ) echo 'style="background:#7C5CFC;color:#fff;border-color:#7C5CFC;"'; ?>>📚 <?php esc_html_e( 'All', 'bookyol' ); ?></a>
                <?php foreach ( $all_cats as $ac ) :
                    $is_active = ( $ac->slug === $cat_filter );
                    $pill_url  = add_query_arg( array( 'cat' => $ac->slug, 'sort' => $sort ), $archive_url );
                    ?>
                    <a href="<?php echo esc_url( $pill_url ); ?>" class="bookyol-archive__cat-pill <?php echo $is_active ? 'bookyol-archive__cat-pill--active' : ''; ?>" <?php if ( $is_active ) echo 'style="background:#7C5CFC;color:#fff;border-color:#7C5CFC;"'; ?>>
                        <?php echo esc_html( $ac->name ); ?>
                        <span class="pill-count"><?php echo esc_html( $ac->count ); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="bookyol-archive__main" style="grid-template-columns:1fr;">
        <div class="bookyol-archive__content">

            <div class="bookyol-archive__toolbar">
                <span class="bookyol-archive__results">
                    <strong><?php echo esc_html( $total ); ?></strong> <?php esc_html_e( 'books', 'bookyol' ); ?>
                    <?php if ( $search ) : ?>
                        <?php printf( esc_html__( 'for "%s"', 'bookyol' ), esc_html( $search ) ); ?>
                    <?php endif; ?>
                </span>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <form class="bookyol-archive__search" method="get" action="<?php echo esc_url( $archive_url ); ?>" style="margin:0;max-width:260px;">
                        <input type="text" name="bookq" placeholder="<?php esc_attr_e( 'Filter books...', 'bookyol' ); ?>" value="<?php echo esc_attr( $search ); ?>" />
                        <?php if ( $cat_filter ) : ?><input type="hidden" name="cat" value="<?php echo esc_attr( $cat_filter ); ?>" /><?php endif; ?>
                        <?php if ( $sort !== 'latest' ) : ?><input type="hidden" name="sort" value="<?php echo esc_attr( $sort ); ?>" /><?php endif; ?>
                        <button type="submit" style="background:#7C5CFC;"><?php esc_html_e( 'Go', 'bookyol' ); ?></button>
                    </form>
                    <div class="bookyol-archive__sort">
                        <label><?php esc_html_e( 'Sort:', 'bookyol' ); ?></label>
                        <select onchange="window.location.href=this.value;">
                            <?php
                            $sort_opts = array(
                                'latest'     => __( 'Newest', 'bookyol' ),
                                'title_asc'  => __( 'A-Z', 'bookyol' ),
                                'title_desc' => __( 'Z-A', 'bookyol' ),
                                'rating'     => __( 'Top Rated', 'bookyol' ),
                            );
                            foreach ( $sort_opts as $sk => $sl ) :
                                $u = add_query_arg( array( 'sort' => $sk, 'bookq' => $search, 'cat' => $cat_filter ), $archive_url );
                                ?>
                                <option value="<?php echo esc_url( $u ); ?>" <?php selected( $sort, $sk ); ?>><?php echo esc_html( $sl ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <?php if ( $books_query->have_posts() ) : ?>
                <div class="bookyol-archive__grid" style="grid-template-columns:repeat(5,1fr);">
                    <?php
                    while ( $books_query->have_posts() ) :
                        $books_query->the_post();
                        $cover  = get_post_meta( get_the_ID(), '_bookyol_cover_url', true );
                        $author = get_post_meta( get_the_ID(), '_bookyol_book_author', true );
                        $rating = get_post_meta( get_the_ID(), '_bookyol_rating', true );
                        ?>
                        <a href="<?php the_permalink(); ?>" class="bookyol-archive__book">
                            <div class="bookyol-archive__book-cover">
                                <?php if ( $rating ) : ?>
                                    <span class="bookyol-archive__book-rating"><span class="star">★</span> <?php echo esc_html( $rating ); ?></span>
                                <?php endif; ?>
                                <?php if ( $cover ) : ?>
                                    <img src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                <?php else : ?>
                                    <div class="bookyol-archive__book-nocover">📚</div>
                                <?php endif; ?>
                            </div>
                            <div class="bookyol-archive__book-info">
                                <div class="bookyol-archive__book-title"><?php the_title(); ?></div>
                                <?php if ( $author ) : ?>
                                    <div class="bookyol-archive__book-author"><?php echo esc_html( $author ); ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>

                <div class="bookyol-archive__pagination">
                    <?php
                    echo paginate_links( array(
                        'total'     => $books_query->max_num_pages,
                        'current'   => $paged,
                        'prev_text' => '&larr; ' . esc_html__( 'Previous', 'bookyol' ),
                        'next_text' => esc_html__( 'Next', 'bookyol' ) . ' &rarr;',
                    ) );
                    ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="bookyol-archive__empty">
                    <span style="font-size:48px;">📭</span>
                    <h3><?php esc_html_e( 'No books found', 'bookyol' ); ?></h3>
                    <p><?php esc_html_e( 'Try a different search or category.', 'bookyol' ); ?></p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>
<?php
get_footer();
