<?php
/**
 * Template: BookYol All Books Archive — v4.5.2
 * Forces full-width with inline styles to beat Astra.
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

<style>
/* Force full width — inline to guarantee override */
.bookyol-allbooks-wrap {
    width: 100vw !important;
    max-width: 100vw !important;
    margin-left: calc(-50vw + 50%) !important;
    box-sizing: border-box !important;
    font-family: 'DM Sans', -apple-system, sans-serif;
    background: #fff;
}
.bookyol-allbooks-wrap * { box-sizing: border-box; }

.bookyol-ab-hero {
    padding: 48px 24px;
    text-align: center;
    background: linear-gradient(135deg, rgba(124,92,252,0.08), #F3F0FF);
}
.bookyol-ab-hero-inner { max-width: 600px; margin: 0 auto; }
.bookyol-ab-hero h1 {
    font-family: 'Source Serif 4', Georgia, serif;
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 700;
    color: #1A1A1A;
    margin: 0 0 8px 0;
}
.bookyol-ab-hero p { font-size: 16px; color: #666; margin: 0 0 20px 0; }
.bookyol-ab-hero .emoji { font-size: 48px; display: block; margin-bottom: 12px; }

.bookyol-ab-search {
    display: flex;
    max-width: 420px;
    margin: 0 auto;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #EEEBE6;
    background: #fff;
}
.bookyol-ab-search input[type="text"] {
    flex: 1;
    border: none !important;
    padding: 12px 16px !important;
    font-size: 14px !important;
    outline: none !important;
    background: transparent !important;
    box-shadow: none !important;
    font-family: 'DM Sans', sans-serif !important;
    margin: 0 !important;
    min-height: auto !important;
    width: auto !important;
}
.bookyol-ab-search button {
    border: none !important;
    padding: 12px 24px !important;
    background: #7C5CFC !important;
    color: #fff !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif !important;
    min-height: auto !important;
}

/* Category pills */
.bookyol-ab-cats {
    border-bottom: 1px solid #EEEBE6;
    background: #FAFAFA;
    padding: 12px 0;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.bookyol-ab-cats::-webkit-scrollbar { display: none; }
.bookyol-ab-cats-inner {
    display: flex;
    gap: 8px;
    padding: 0 24px;
    max-width: 1200px;
    margin: 0 auto;
    white-space: nowrap;
}
.bookyol-ab-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 500;
    color: #666;
    background: #fff;
    border: 1px solid #EEEBE6;
    transition: all 0.2s;
    text-decoration: none !important;
    flex-shrink: 0;
}
.bookyol-ab-pill:hover { border-color: #999; color: #333; }
.bookyol-ab-pill.active { background: #7C5CFC !important; color: #fff !important; border-color: #7C5CFC !important; }
.bookyol-ab-pill .cnt { font-size: 11px; opacity: 0.6; }

/* Toolbar */
.bookyol-ab-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #EEEBE6;
}
.bookyol-ab-toolbar .results { font-size: 14px; color: #888; }
.bookyol-ab-toolbar .controls { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.bookyol-ab-toolbar .sort-wrap { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #888; }
.bookyol-ab-toolbar select {
    padding: 6px 12px;
    border: 1px solid #EEEBE6;
    border-radius: 6px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    color: #333;
    background: #fff;
    cursor: pointer;
}
.bookyol-ab-filter-search {
    display: flex;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #EEEBE6;
}
.bookyol-ab-filter-search input[type="text"] {
    border: none !important;
    padding: 6px 12px !important;
    font-size: 13px !important;
    outline: none !important;
    background: #fff !important;
    box-shadow: none !important;
    font-family: 'DM Sans', sans-serif !important;
    width: 180px !important;
    margin: 0 !important;
    min-height: auto !important;
}
.bookyol-ab-filter-search button {
    border: none !important;
    padding: 6px 14px !important;
    background: #7C5CFC !important;
    color: #fff !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    cursor: pointer;
    min-height: auto !important;
}

/* Book Grid */
.bookyol-ab-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
}
.bookyol-ab-book {
    display: block;
    text-decoration: none !important;
    color: #1A1A1A !important;
    transition: transform 0.3s;
}
.bookyol-ab-book:hover { transform: translateY(-6px); }
.bookyol-ab-book-cover {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 2/3;
    background: #F5F3EE;
    margin-bottom: 10px;
}
.bookyol-ab-book-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s;
}
.bookyol-ab-book:hover .bookyol-ab-book-cover img { transform: scale(1.04); }
.bookyol-ab-book-nocover {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 48px;
    background: #F0EDE8;
}
.bookyol-ab-book-rating {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0,0,0,0.7);
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    z-index: 2;
}
.bookyol-ab-book-rating .star { color: #FFD700; }
.bookyol-ab-book-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.3;
    margin-bottom: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.bookyol-ab-book-author { font-size: 12px; color: #999; }

/* Pagination */
.bookyol-ab-pagination { text-align: center; margin-top: 40px; }
.bookyol-ab-pagination a,
.bookyol-ab-pagination span {
    display: inline-block;
    padding: 8px 16px;
    margin: 0 3px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
}
.bookyol-ab-pagination a {
    color: #444 !important;
    border: 1px solid #EEEBE6;
    text-decoration: none !important;
}
.bookyol-ab-pagination a:hover { background: #7C5CFC; color: #fff !important; border-color: #7C5CFC; }
.bookyol-ab-pagination .current { background: #7C5CFC; color: #fff; }

.bookyol-ab-empty { text-align: center; padding: 60px 24px; color: #999; }
.bookyol-ab-empty h3 { color: #555; margin: 12px 0 4px; }

@media (max-width: 900px) { .bookyol-ab-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px) {
    .bookyol-ab-grid { grid-template-columns: repeat(2, 1fr); }
    .bookyol-ab-toolbar { flex-direction: column; align-items: flex-start; }
}
</style>

<div class="bookyol-allbooks-wrap">

    <!-- Hero -->
    <div class="bookyol-ab-hero">
        <div class="bookyol-ab-hero-inner">
            <span class="emoji">📚</span>
            <h1>All Books</h1>
            <p>Explore our complete library of <?php echo esc_html( $total ); ?> curated books.</p>
            <form class="bookyol-ab-search" method="get" action="<?php echo esc_url( $archive_url ); ?>">
                <input type="text" name="bookq" placeholder="Search books, authors..." value="<?php echo esc_attr( $search ); ?>" />
                <?php if ( $cat_filter ) : ?><input type="hidden" name="cat" value="<?php echo esc_attr( $cat_filter ); ?>" /><?php endif; ?>
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- Category Pills -->
    <?php if ( ! empty( $all_cats ) ) : ?>
    <div class="bookyol-ab-cats">
        <div class="bookyol-ab-cats-inner">
            <a href="<?php echo esc_url( $archive_url ); ?>" class="bookyol-ab-pill <?php echo empty( $cat_filter ) ? 'active' : ''; ?>">📚 All</a>
            <?php foreach ( $all_cats as $ac ) :
                $is_active = ( $ac->slug === $cat_filter );
                $pill_url  = add_query_arg( array( 'cat' => $ac->slug, 'sort' => $sort ), $archive_url );
            ?>
                <a href="<?php echo esc_url( $pill_url ); ?>" class="bookyol-ab-pill <?php echo $is_active ? 'active' : ''; ?>"><?php echo esc_html( $ac->name ); ?> <span class="cnt"><?php echo esc_html( $ac->count ); ?></span></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Content -->
    <div style="max-width:1200px;margin:0 auto;padding:32px 24px 64px;">

        <!-- Toolbar -->
        <div class="bookyol-ab-toolbar">
            <span class="results"><strong><?php echo esc_html( $total ); ?></strong> books<?php if ( $search ) echo ' for "' . esc_html( $search ) . '"'; ?></span>
            <div class="controls">
                <form class="bookyol-ab-filter-search" method="get" action="<?php echo esc_url( $archive_url ); ?>">
                    <input type="text" name="bookq" placeholder="Filter..." value="<?php echo esc_attr( $search ); ?>" />
                    <?php if ( $cat_filter ) : ?><input type="hidden" name="cat" value="<?php echo esc_attr( $cat_filter ); ?>" /><?php endif; ?>
                    <?php if ( $sort !== 'latest' ) : ?><input type="hidden" name="sort" value="<?php echo esc_attr( $sort ); ?>" /><?php endif; ?>
                    <button type="submit">Go</button>
                </form>
                <div class="sort-wrap">
                    <label>Sort:</label>
                    <select onchange="window.location.href=this.value;">
                        <?php
                        $sort_opts = array( 'latest' => 'Newest', 'title_asc' => 'A-Z', 'title_desc' => 'Z-A', 'rating' => 'Top Rated' );
                        foreach ( $sort_opts as $sk => $sl ) :
                            $u = add_query_arg( array( 'sort' => $sk, 'bookq' => $search, 'cat' => $cat_filter ), $archive_url );
                        ?>
                            <option value="<?php echo esc_url( $u ); ?>" <?php selected( $sort, $sk ); ?>><?php echo esc_html( $sl ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Book Grid -->
        <?php if ( $books_query->have_posts() ) : ?>
            <div class="bookyol-ab-grid">
                <?php while ( $books_query->have_posts() ) : $books_query->the_post();
                    $cover  = get_post_meta( get_the_ID(), '_bookyol_cover_url', true );
                    $author = get_post_meta( get_the_ID(), '_bookyol_book_author', true );
                    $rating = get_post_meta( get_the_ID(), '_bookyol_rating', true );
                ?>
                    <a href="<?php the_permalink(); ?>" class="bookyol-ab-book">
                        <div class="bookyol-ab-book-cover">
                            <?php if ( $rating ) : ?><span class="bookyol-ab-book-rating"><span class="star">★</span> <?php echo esc_html( $rating ); ?></span><?php endif; ?>
                            <?php if ( $cover ) : ?>
                                <img src="<?php echo esc_url( $cover ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                            <?php else : ?>
                                <div class="bookyol-ab-book-nocover">📚</div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="bookyol-ab-book-title"><?php the_title(); ?></div>
                            <?php if ( $author ) : ?><div class="bookyol-ab-book-author"><?php echo esc_html( $author ); ?></div><?php endif; ?>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
            <div class="bookyol-ab-pagination">
                <?php echo paginate_links( array( 'total' => $books_query->max_num_pages, 'current' => $paged, 'prev_text' => '&larr; Previous', 'next_text' => 'Next &rarr;' ) ); ?>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="bookyol-ab-empty">
                <span style="font-size:48px;">📭</span>
                <h3>No books found</h3>
                <p>Try a different search or category.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php get_footer(); ?>
