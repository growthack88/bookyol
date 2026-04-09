<?php
/**
 * Blog Post Template — v4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! have_posts() ) {
    get_header();
    echo '<div style="max-width:800px;margin:60px auto;padding:24px;text-align:center;"><h1>Post not found</h1></div>';
    get_footer();
    return;
}

the_post();
get_header();

try {

    $post_id       = get_the_ID();
    $title         = get_the_title();
    $date          = get_the_date( 'F j, Y' );
    $author_name   = get_the_author();
    $author_bio    = get_the_author_meta( 'description' );
    $author_avatar = get_avatar_url( get_the_author_meta( 'ID' ), array( 'size' => 80 ) );
    $categories    = get_the_category();
    $tags          = get_the_tags();

    // Reading time calculation.
    $raw_content  = get_post_field( 'post_content', $post_id );
    $word_count   = str_word_count( wp_strip_all_tags( $raw_content ) );
    $reading_time = max( 1, (int) ceil( $word_count / 230 ) );

    // Featured image.
    $featured_img = get_the_post_thumbnail_url( $post_id, 'large' );

    // Related posts (same category).
    $cat_ids = wp_list_pluck( $categories, 'term_id' );
    $related_args = array(
        'post_type'      => 'post',
        'posts_per_page' => 3,
        'post__not_in'   => array( $post_id ),
        'post_status'    => 'publish',
    );
    if ( ! empty( $cat_ids ) ) {
        $related_args['category__in'] = $cat_ids;
    }
    $related = get_posts( $related_args );

    // Popular posts (most commented).
    $popular = get_posts( array(
        'post_type'      => 'post',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'orderby'        => 'comment_count',
        'order'          => 'DESC',
        'post__not_in'   => array( $post_id ),
    ) );

    // Random book recommendations.
    $recommended_books = get_posts( array(
        'post_type'      => 'bookyol_book',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'orderby'        => 'rand',
    ) );

    $share_url       = rawurlencode( get_permalink() );
    $share_title     = rawurlencode( $title );
    $permalink_plain = esc_url( get_permalink() );
    ?>

    <!-- Reading Progress Bar -->
    <div class="bookyol-blog__progress" id="bookyol-progress"></div>

    <article class="bookyol-blog">
    <div class="bookyol-blog__container">

        <!-- ARTICLE HEADER -->
        <header class="bookyol-blog__header">
            <?php if ( ! empty( $categories ) ) : ?>
                <div class="bookyol-blog__cats">
                    <?php foreach ( $categories as $cat ) : ?>
                        <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="bookyol-blog__cat"><?php echo esc_html( $cat->name ); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h1 class="bookyol-blog__title"><?php echo esc_html( $title ); ?></h1>

            <?php if ( has_excerpt() ) : ?>
                <p class="bookyol-blog__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
            <?php endif; ?>

            <div class="bookyol-blog__meta">
                <div class="bookyol-blog__meta-author">
                    <?php if ( $author_avatar ) : ?>
                        <img src="<?php echo esc_url( $author_avatar ); ?>" alt="<?php echo esc_attr( $author_name ); ?>" class="bookyol-blog__avatar" />
                    <?php endif; ?>
                    <div>
                        <span class="bookyol-blog__author-name"><?php echo esc_html( $author_name ); ?></span>
                        <span class="bookyol-blog__date"><?php echo esc_html( $date ); ?> · <?php echo esc_html( $reading_time ); ?> min read</span>
                    </div>
                </div>

                <div class="bookyol-blog__share">
                    <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="bookyol-blog__share-btn bookyol-blog__share-btn--x" title="Share on X" aria-label="Share on X">𝕏</a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="bookyol-blog__share-btn bookyol-blog__share-btn--fb" title="Share on Facebook" aria-label="Share on Facebook">f</a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="bookyol-blog__share-btn bookyol-blog__share-btn--li" title="Share on LinkedIn" aria-label="Share on LinkedIn">in</a>
                    <button type="button" class="bookyol-blog__share-btn bookyol-blog__share-btn--copy" data-copy-url="<?php echo $permalink_plain; ?>" title="Copy link" aria-label="Copy link">🔗</button>
                </div>
            </div>
        </header>

        <?php if ( $featured_img ) : ?>
            <div class="bookyol-blog__featured-img">
                <img src="<?php echo esc_url( $featured_img ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
            </div>
        <?php endif; ?>

        <!-- MAIN CONTENT AREA -->
        <div class="bookyol-blog__main">

            <!-- ARTICLE BODY -->
            <div class="bookyol-blog__body">

                <!-- Table of Contents (auto-generated by JS) -->
                <div class="bookyol-blog__toc" id="bookyol-toc" style="display:none;">
                    <div class="bookyol-blog__toc-header">
                        <span>📑 <?php esc_html_e( 'Table of Contents', 'bookyol' ); ?></span>
                        <button type="button" class="bookyol-blog__toc-toggle" aria-label="Toggle table of contents">−</button>
                    </div>
                    <nav class="bookyol-blog__toc-list" id="bookyol-toc-list"></nav>
                </div>

                <!-- Article Content -->
                <div class="bookyol-blog__content" id="bookyol-content">
                    <?php
                    // Isolated the_content() — buffered + try/catch so a filter fatal
                    // falls back to raw content instead of breaking the page.
                    $content_html = '';
                    try {
                        ob_start();
                        the_content();
                        $content_html = ob_get_clean();
                    } catch ( \Throwable $e ) {
                        if ( ob_get_level() > 0 ) {
                            @ob_end_clean();
                        }
                        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
                            error_log( 'BookYol single-post the_content() fatal on post ' . $post_id . ': ' . $e->getMessage() );
                        }
                        $content_html = wpautop( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
                    }
                    echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                </div>

                <?php if ( $tags ) : ?>
                    <div class="bookyol-blog__tags">
                        <?php foreach ( $tags as $tag ) : ?>
                            <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="bookyol-blog__tag">#<?php echo esc_html( $tag->name ); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="bookyol-blog__share-bottom">
                    <span><?php esc_html_e( 'Share this article:', 'bookyol' ); ?></span>
                    <div class="bookyol-blog__share">
                        <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="bookyol-blog__share-btn bookyol-blog__share-btn--x" aria-label="Share on X">𝕏</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="bookyol-blog__share-btn bookyol-blog__share-btn--fb" aria-label="Share on Facebook">f</a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="bookyol-blog__share-btn bookyol-blog__share-btn--li" aria-label="Share on LinkedIn">in</a>
                        <button type="button" class="bookyol-blog__share-btn bookyol-blog__share-btn--copy" data-copy-url="<?php echo $permalink_plain; ?>" aria-label="Copy link">🔗</button>
                    </div>
                </div>

                <div class="bookyol-blog__author-box">
                    <?php if ( $author_avatar ) : ?>
                        <img src="<?php echo esc_url( $author_avatar ); ?>" alt="<?php echo esc_attr( $author_name ); ?>" class="bookyol-blog__author-avatar" />
                    <?php endif; ?>
                    <div class="bookyol-blog__author-info">
                        <span class="bookyol-blog__author-label"><?php esc_html_e( 'Written by', 'bookyol' ); ?></span>
                        <strong class="bookyol-blog__author-name-lg"><?php echo esc_html( $author_name ); ?></strong>
                        <?php if ( $author_bio ) : ?>
                            <p class="bookyol-blog__author-bio"><?php echo esc_html( $author_bio ); ?></p>
                        <?php else : ?>
                            <p class="bookyol-blog__author-bio"><?php esc_html_e( 'Book lover, reader, and curator at BookYol. Helping you find your next great read.', 'bookyol' ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- SIDEBAR -->
            <aside class="bookyol-blog__sidebar">

                <div class="bookyol-blog__sidebar-box bookyol-blog__sidebar-newsletter">
                    <h3>📬 <?php esc_html_e( 'Weekly Book Picks', 'bookyol' ); ?></h3>
                    <p><?php esc_html_e( 'Get one great book recommendation every Tuesday.', 'bookyol' ); ?></p>
                    <?php
                    $hp = function_exists( 'bookyol_get_homepage_settings' ) ? bookyol_get_homepage_settings() : array();
                    $form_action = isset( $hp['newsletter_form_action'] ) ? $hp['newsletter_form_action'] : '';
                    if ( $form_action ) : ?>
                        <form action="<?php echo esc_url( $form_action ); ?>" method="post">
                            <input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email', 'bookyol' ); ?>" required />
                            <button type="submit"><?php esc_html_e( 'Subscribe', 'bookyol' ); ?></button>
                        </form>
                    <?php else : ?>
                        <p style="font-size:12px;color:#999;"><?php esc_html_e( 'Newsletter coming soon!', 'bookyol' ); ?></p>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $recommended_books ) ) : ?>
                    <div class="bookyol-blog__sidebar-box">
                        <h3>📚 <?php esc_html_e( 'Recommended Books', 'bookyol' ); ?></h3>
                        <ul class="bookyol-blog__sidebar-books">
                            <?php foreach ( $recommended_books as $rb ) :
                                $rc = get_post_meta( $rb->ID, '_bookyol_cover_url', true );
                                if ( empty( $rc ) && has_post_thumbnail( $rb->ID ) ) {
                                    $rc = get_the_post_thumbnail_url( $rb->ID, 'thumbnail' );
                                }
                                $ra = get_post_meta( $rb->ID, '_bookyol_book_author', true );
                                $rr = get_post_meta( $rb->ID, '_bookyol_rating', true );
                                ?>
                                <li>
                                    <a href="<?php echo esc_url( get_permalink( $rb->ID ) ); ?>">
                                        <?php if ( $rc ) : ?>
                                            <img src="<?php echo esc_url( $rc ); ?>" alt="" />
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo esc_html( $rb->post_title ); ?></strong>
                                            <small><?php echo esc_html( $ra ); ?><?php if ( $rr ) echo ' · ★ ' . esc_html( $rr ); ?></small>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $popular ) ) : ?>
                    <div class="bookyol-blog__sidebar-box">
                        <h3>🔥 <?php esc_html_e( 'Popular Articles', 'bookyol' ); ?></h3>
                        <ul class="bookyol-blog__sidebar-popular">
                            <?php foreach ( $popular as $i => $pp ) : ?>
                                <li>
                                    <a href="<?php echo esc_url( get_permalink( $pp->ID ) ); ?>">
                                        <span class="pop-num"><?php echo esc_html( $i + 1 ); ?></span>
                                        <div>
                                            <strong><?php echo esc_html( $pp->post_title ); ?></strong>
                                            <small><?php echo esc_html( get_the_date( 'M j, Y', $pp->ID ) ); ?></small>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="bookyol-blog__sidebar-box">
                    <h3>📂 <?php esc_html_e( 'Categories', 'bookyol' ); ?></h3>
                    <div class="bookyol-blog__sidebar-cat-pills">
                        <?php
                        $blog_cats = get_categories( array( 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
                        foreach ( $blog_cats as $bc ) : ?>
                            <a href="<?php echo esc_url( get_category_link( $bc->term_id ) ); ?>" class="bookyol-blog__sidebar-cat-pill">
                                <?php echo esc_html( $bc->name ); ?>
                                <span><?php echo esc_html( $bc->count ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </aside>
        </div>

        <!-- RELATED POSTS -->
        <?php if ( ! empty( $related ) ) : ?>
            <div class="bookyol-blog__related">
                <h2 class="bookyol-blog__related-title"><?php esc_html_e( 'Continue Reading', 'bookyol' ); ?></h2>
                <div class="bookyol-blog__related-grid">
                    <?php foreach ( $related as $rp ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $rp->ID ) ); ?>" class="bookyol-blog__related-card">
                            <div class="bookyol-blog__related-img">
                                <?php if ( has_post_thumbnail( $rp->ID ) ) : ?>
                                    <?php echo get_the_post_thumbnail( $rp->ID, 'medium' ); ?>
                                <?php else : ?>
                                    <div class="bookyol-blog__related-noimg">📝</div>
                                <?php endif; ?>
                            </div>
                            <div class="bookyol-blog__related-info">
                                <?php
                                $rp_cats = get_the_category( $rp->ID );
                                if ( ! empty( $rp_cats ) ) : ?>
                                    <span class="bookyol-blog__related-cat"><?php echo esc_html( $rp_cats[0]->name ); ?></span>
                                <?php endif; ?>
                                <h3><?php echo esc_html( $rp->post_title ); ?></h3>
                                <span class="bookyol-blog__related-date"><?php echo esc_html( get_the_date( 'M j, Y', $rp->ID ) ); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
    </article>

    <!-- Back to Top -->
    <button type="button" class="bookyol-blog__back-top" id="bookyol-back-top" title="Back to top" aria-label="Back to top">↑</button>

    <?php
} catch ( \Throwable $e ) {
    if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        error_log( 'BookYol single-post fatal: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
    }
    ?>
    <div style="max-width:800px;margin:60px auto;padding:24px;font-family:'DM Sans',sans-serif;">
        <h1 style="font-family:'Source Serif 4',Georgia,serif;"><?php the_title(); ?></h1>
        <?php echo wpautop( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) ); ?>
    </div>
    <?php
}

get_footer();
