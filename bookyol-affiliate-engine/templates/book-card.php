<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * @var array $book
 * @var array $available
 */

$primary_slug = ! empty( $available ) ? $available[0] : '';
$secondary    = array_slice( $available, 1, 3 );
$rating       = (float) $book['rating'];
?>
<div class="bookyol-card">
    <div class="bookyol-card__cover">
        <?php if ( ! empty( $book['cover_url'] ) ) : ?>
            <img src="<?php echo esc_url( $book['cover_url'] ); ?>" alt="<?php echo esc_attr( $book['title'] ); ?>" loading="lazy" width="140">
        <?php endif; ?>
    </div>
    <div class="bookyol-card__info">
        <h3 class="bookyol-card__title"><?php echo esc_html( $book['title'] ); ?></h3>
        <?php if ( ! empty( $book['book_author'] ) ) : ?>
            <p class="bookyol-card__author"><?php echo esc_html__( 'by', 'bookyol' ) . ' ' . esc_html( $book['book_author'] ); ?></p>
        <?php endif; ?>

        <?php if ( $rating > 0 ) : ?>
            <div class="bookyol-card__rating" aria-label="<?php echo esc_attr( sprintf( __( '%s out of 5 stars', 'bookyol' ), $rating ) ); ?>">
                <?php echo esc_html( BookYol_Shortcodes::render_stars( $rating ) ); ?>
                <span><?php echo esc_html( $rating ); ?>/5</span>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $book['excerpt'] ) ) : ?>
            <p class="bookyol-card__excerpt"><?php echo esc_html( $book['excerpt'] ); ?></p>
        <?php endif; ?>

        <?php if ( $primary_slug ) : ?>
            <a href="<?php echo esc_url( BookYol_Shortcodes::redirect_url( $primary_slug, $book['slug'] ) ); ?>"
               class="bookyol-btn bookyol-btn--primary"
               target="_blank"
               rel="nofollow sponsored noopener">
                <?php echo esc_html( sprintf( __( 'Read on %s', 'bookyol' ), BookYol_Shortcodes::platform_display_name( $primary_slug ) ) ); ?> →
            </a>
        <?php endif; ?>

        <?php if ( ! empty( $secondary ) ) : ?>
            <div class="bookyol-card__alt-links">
                <?php esc_html_e( 'Also on:', 'bookyol' ); ?>
                <?php $i = 0; foreach ( $secondary as $slug ) : ?>
                    <?php if ( $i > 0 ) echo ' · '; ?>
                    <a href="<?php echo esc_url( BookYol_Shortcodes::redirect_url( $slug, $book['slug'] ) ); ?>" target="_blank" rel="nofollow sponsored noopener"><?php echo esc_html( BookYol_Shortcodes::platform_display_name( $slug ) ); ?></a>
                <?php $i++; endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
