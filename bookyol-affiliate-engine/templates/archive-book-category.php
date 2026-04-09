<?php
/**
 * Archive template for Book Categories — v4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$term      = get_queried_object();
$term_name = isset( $term->name ) ? $term->name : __( 'Books', 'bookyol' );
$term_slug = isset( $term->slug ) ? $term->slug : '';
$term_desc = ! empty( $term->description )
    ? $term->description
    : sprintf( __( 'Discover the best %s books, handpicked and reviewed by BookYol.', 'bookyol' ), strtolower( $term_name ) );
$term_count = isset( $term->count ) ? (int) $term->count : 0;

// Search within category.
$search_query = isset( $_GET['bookq'] ) ? sanitize_text_field( wp_unslash( $_GET['bookq'] ) ) : '';

// Sort.
$sort = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'latest';
$sort_options = array(
    'latest'     => __( 'Newest First', 'bookyol' ),
    'title_asc'  => __( 'Title A-Z', 'bookyol' ),
    'title_desc' => __( 'Title Z-A', 'bookyol' ),
    'rating'     => __( 'Highest Rated', 'bookyol' ),
);

$paged = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;

$query_args = array(
    'post_type'      => 'bookyol_book',
    'posts_per_page' => 20,
    'paged'          => $paged,
    'post_status'    => 'publish',
    'tax_query'      => array(
        array( 'taxonomy' => 'book_category', 'field' => 'slug', 'terms' => $term_slug ),
    ),
);

if ( ! empty( $search_query ) ) {
    $query_args['s'] = $search_query;
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
}

$books = new WP_Query( $query_args );

// All categories for the pills and sidebar.
$all_categories = array();
if ( taxonomy_exists( 'book_category' ) ) {
    $maybe = get_terms( array(
        'taxonomy'   => 'book_category',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ) );
    if ( ! is_wp_error( $maybe ) ) {
        $all_categories = $maybe;
    }
}

// Category accent colors [primary, tint].
$cat_colors = array(
    'business'     => array( '#4A90D9', '#EFF5FF' ),
    'psychology'   => array( '#7C5CFC', '#F3F0FF' ),
    'self-help'    => array( '#2ECC87', '#EDFFF6' ),
    'productivity' => array( '#F5A623', '#FFF8EC' ),
    'marketing'    => array( '#FF6B6B', '#FFF0F0' ),
    'finance'      => array( '#20B2AA', '#EEFCFB' ),
    'leadership'   => array( '#E84393', '#FFF0F7' ),
    'biographies'  => array( '#5352ED', '#F0F0FF' ),
    'fiction'      => array( '#667EEA', '#F0F0FF' ),
    'thriller'     => array( '#2D3436', '#F5F5F5' ),
    'sci-fi'       => array( '#00B894', '#EDFFF6' ),
    'romance'      => array( '#FD79A8', '#FFF0F7' ),
    'classic'      => array( '#6C5CE7', '#F3F0FF' ),
    'fantasy'      => array( '#A29BFE', '#F3F0FF' ),
    'science'      => array( '#0984E3', '#EFF5FF' ),
    'philosophy'   => array( '#8E24AA', '#F3E5F5' ),
    'history'      => array( '#795548', '#EFEBE9' ),
    'creativity'   => array( '#E65100', '#FFF3E0' ),
    'memoir'       => array( '#00897B', '#E0F2F1' ),
    'health'       => array( '#43A047', '#E8F5E9' ),
);

$accent    = isset( $cat_colors[ $term_slug ][0] ) ? $cat_colors[ $term_slug ][0] : '#7C5CFC';
$accent_bg = isset( $cat_colors[ $term_slug ][1] ) ? $cat_colors[ $term_slug ][1] : '#F3F0FF';

// Category emoji mapping.
$cat_emojis = array(
    'business' => '💼', 'psychology' => '🧠', 'self-help' => '🌱',
    'productivity' => '⚡', 'marketing' => '📈', 'finance' => '💰',
    'leadership' => '🎯', 'biographies' => '📖', 'fiction' => '📕',
    'thriller' => '🔍', 'sci-fi' => '🚀', 'romance' => '💘',
    'classic' => '📜', 'fantasy' => '🐉', 'science' => '🔬',
    'philosophy' => '💭', 'history' => '🏛️', 'creativity' => '🎨',
    'memoir' => '✍️', 'health' => '🏃',
);
$emoji = isset( $cat_emojis[ $term_slug ] ) ? $cat_emojis[ $term_slug ] : '📚';
?>

<div class="bookyol-archive">

    <!-- HERO BANNER -->
    <div class="bookyol-archive__hero" style="background: linear-gradient(135deg, <?php echo esc_attr( $accent ); ?>15, <?php echo esc_attr( $accent_bg ); ?>);">
        <div class="bookyol-archive__hero-inner">
            <span class="bookyol-archive__emoji"><?php echo esc_html( $emoji ); ?></span>
            <h1 class="bookyol-archive__title"><?php echo esc_html( $term_name ); ?> <?php esc_html_e( 'Books', 'bookyol' ); ?></h1>
            <p class="bookyol-archive__desc"><?php echo esc_html( $term_desc ); ?></p>
            <span class="bookyol-archive__count" style="color: <?php echo esc_attr( $accent ); ?>;"><?php echo esc_html( $term_count ); ?> <?php esc_html_e( 'books', 'bookyol' ); ?></span>

            <form class="bookyol-archive__search" method="get" action="<?php echo esc_url( get_term_link( $term ) ); ?>">
                <input type="text" name="bookq" placeholder="<?php printf( esc_attr__( 'Search in %s…', 'bookyol' ), esc_attr( $term_name ) ); ?>" value="<?php echo esc_attr( $search_query ); ?>" />
                <button type="submit" style="background: <?php echo esc_attr( $accent ); ?>;"><?php esc_html_e( 'Search', 'bookyol' ); ?></button>
            </form>
        </div>
    </div>

    <!-- CATEGORY PILLS -->
    <?php if ( ! empty( $all_categories ) ) : ?>
        <div class="bookyol-archive__cats">
            <div class="bookyol-archive__cats-inner">
                <a href="<?php echo esc_url( home_url( '/books/' ) ); ?>" class="bookyol-archive__cat-pill">📚 <?php esc_html_e( 'All Books', 'bookyol' ); ?></a>
                <?php foreach ( $all_categories as $cat ) :
                    $is_current = ( $cat->slug === $term_slug );
                    $ce         = isset( $cat_emojis[ $cat->slug ] ) ? $cat_emojis[ $cat->slug ] : '📖';
                    $pill_style = $is_current
                        ? 'background: ' . esc_attr( $accent ) . '; color: #fff; border-color: ' . esc_attr( $accent ) . ';'
                        : '';
                    ?>
                    <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
                       class="bookyol-archive__cat-pill <?php echo $is_current ? 'bookyol-archive__cat-pill--active' : ''; ?>"
                       <?php echo $pill_style ? 'style="' . $pill_style . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                        <?php echo esc_html( $ce . ' ' . $cat->name ); ?>
                        <span class="pill-count"><?php echo esc_html( $cat->count ); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- MAIN CONTENT -->
    <div class="bookyol-archive__main">
        <div class="bookyol-archive__content">

            <!-- Sort bar -->
            <div class="bookyol-archive__toolbar">
                <span class="bookyol-archive__results">
                    <?php if ( $search_query ) : ?>
                        <?php
                        printf(
                            /* translators: 1: search query, 2: category name */
                            esc_html__( 'Showing results for "%1$s" in %2$s', 'bookyol' ),
                            '<strong>' . esc_html( $search_query ) . '</strong>',
                            esc_html( $term_name )
                        );
                        ?>
                    <?php else : ?>
                        <?php echo esc_html( sprintf( _n( '%d book', '%d books', (int) $books->found_posts, 'bookyol' ), (int) $books->found_posts ) ); ?>
                    <?php endif; ?>
                </span>
                <div class="bookyol-archive__sort">
                    <label><?php esc_html_e( 'Sort by:', 'bookyol' ); ?></label>
                    <select onchange="window.location.href=this.value;">
                        <?php foreach ( $sort_options as $key => $label ) :
                            $url = add_query_arg( array( 'sort' => $key, 'bookq' => $search_query ), get_term_link( $term ) );
                            ?>
                            <option value="<?php echo esc_url( $url ); ?>" <?php selected( $sort, $key ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Book Grid -->
            <?php if ( $books->have_posts() ) : ?>
                <div class="bookyol-archive__grid">
                    <?php while ( $books->have_posts() ) :
                        $books->the_post();
                        $cover    = get_post_meta( get_the_ID(), '_bookyol_cover_url', true );
                        if ( empty( $cover ) && has_post_thumbnail( get_the_ID() ) ) {
                            $cover = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                        }
                        $author   = get_post_meta( get_the_ID(), '_bookyol_book_author', true );
                        $rating   = get_post_meta( get_the_ID(), '_bookyol_rating', true );
                        $best_for = get_post_meta( get_the_ID(), '_bookyol_best_for', true );
                        ?>
                        <a href="<?php the_permalink(); ?>" class="bookyol-archive__book">
                            <div class="bookyol-archive__book-cover">
                                <?php if ( $rating ) : ?>
                                    <span class="bookyol-archive__book-rating"><span class="star">★</span> <?php echo esc_html( $rating ); ?></span>
                                <?php endif; ?>
                                <?php if ( $cover ) : ?>
                                    <img src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                <?php else : ?>
                                    <div class="bookyol-archive__book-nocover"><?php echo esc_html( $emoji ); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="bookyol-archive__book-info">
                                <div class="bookyol-archive__book-title"><?php the_title(); ?></div>
                                <?php if ( $author ) : ?>
                                    <div class="bookyol-archive__book-author"><?php echo esc_html( $author ); ?></div>
                                <?php endif; ?>
                                <?php if ( $best_for ) : ?>
                                    <div class="bookyol-archive__book-for"><?php echo esc_html( $best_for ); ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <div class="bookyol-archive__pagination">
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
                <div class="bookyol-archive__empty">
                    <span style="font-size:48px;">📭</span>
                    <h3><?php esc_html_e( 'No books found', 'bookyol' ); ?></h3>
                    <p><?php esc_html_e( 'Try a different search or browse other categories.', 'bookyol' ); ?></p>
                </div>
            <?php endif; ?>

        </div>

        <!-- SIDEBAR -->
        <aside class="bookyol-archive__sidebar">

            <!-- Categories List -->
            <?php if ( ! empty( $all_categories ) ) : ?>
                <div class="bookyol-archive__sidebar-box">
                    <h3 class="bookyol-archive__sidebar-title"><?php esc_html_e( 'Categories', 'bookyol' ); ?></h3>
                    <ul class="bookyol-archive__sidebar-cats">
                        <?php foreach ( $all_categories as $cat ) :
                            $is_current = ( $cat->slug === $term_slug );
                            $ce         = isset( $cat_emojis[ $cat->slug ] ) ? $cat_emojis[ $cat->slug ] : '📖';
                            $link_style = $is_current ? 'color: ' . esc_attr( $accent ) . '; font-weight: 700;' : '';
                            ?>
                            <li class="<?php echo $is_current ? 'active' : ''; ?>">
                                <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
                                   <?php echo $link_style ? 'style="' . $link_style . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                                    <span><?php echo esc_html( $ce . ' ' . $cat->name ); ?></span>
                                    <span class="cat-count"><?php echo esc_html( $cat->count ); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Newsletter -->
            <div class="bookyol-archive__sidebar-box bookyol-archive__sidebar-newsletter" style="background: <?php echo esc_attr( $accent_bg ); ?>; border-color: <?php echo esc_attr( $accent ); ?>33;">
                <h3 class="bookyol-archive__sidebar-title">📬 <?php esc_html_e( 'Weekly Picks', 'bookyol' ); ?></h3>
                <p><?php printf( esc_html__( 'Get the best %s books delivered to your inbox every Tuesday.', 'bookyol' ), esc_html( strtolower( $term_name ) ) ); ?></p>
                <?php
                $hp_settings = function_exists( 'bookyol_get_homepage_settings' ) ? bookyol_get_homepage_settings() : array();
                $form_action = isset( $hp_settings['newsletter_form_action'] ) ? $hp_settings['newsletter_form_action'] : '';
                if ( $form_action ) : ?>
                    <form action="<?php echo esc_url( $form_action ); ?>" method="post">
                        <input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email', 'bookyol' ); ?>" required />
                        <button type="submit" style="background: <?php echo esc_attr( $accent ); ?>;"><?php esc_html_e( 'Subscribe', 'bookyol' ); ?></button>
                    </form>
                <?php else : ?>
                    <p style="font-size:12px;color:#999;"><?php esc_html_e( 'Newsletter coming soon!', 'bookyol' ); ?></p>
                <?php endif; ?>
            </div>

            <!-- Top Rated in category -->
            <?php
            $popular = get_posts( array(
                'post_type'      => 'bookyol_book',
                'posts_per_page' => 5,
                'post_status'    => 'publish',
                'tax_query'      => array(
                    array( 'taxonomy' => 'book_category', 'field' => 'slug', 'terms' => $term_slug ),
                ),
                'meta_key'       => '_bookyol_rating',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
            ) );
            if ( ! empty( $popular ) ) : ?>
                <div class="bookyol-archive__sidebar-box">
                    <h3 class="bookyol-archive__sidebar-title">⭐ <?php esc_html_e( 'Top Rated', 'bookyol' ); ?></h3>
                    <ul class="bookyol-archive__sidebar-popular">
                        <?php foreach ( $popular as $i => $pop ) :
                            $pc = get_post_meta( $pop->ID, '_bookyol_cover_url', true );
                            if ( empty( $pc ) && has_post_thumbnail( $pop->ID ) ) {
                                $pc = get_the_post_thumbnail_url( $pop->ID, 'thumbnail' );
                            }
                            $pa = get_post_meta( $pop->ID, '_bookyol_book_author', true );
                            $pr = get_post_meta( $pop->ID, '_bookyol_rating', true );
                            ?>
                            <li>
                                <a href="<?php echo esc_url( get_permalink( $pop->ID ) ); ?>">
                                    <span class="pop-rank"><?php echo esc_html( $i + 1 ); ?></span>
                                    <?php if ( $pc ) : ?>
                                        <img src="<?php echo esc_url( $pc ); ?>" alt="" width="40" height="60" />
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo esc_html( $pop->post_title ); ?></strong>
                                        <small><?php echo esc_html( $pa ); ?><?php if ( $pr ) echo ' · ★ ' . esc_html( $pr ); ?></small>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

        </aside>
    </div>

</div>

<?php get_footer(); ?>
