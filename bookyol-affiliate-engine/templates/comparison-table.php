<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * @var array $books
 */
?>
<div class="bookyol-compare-wrapper">
    <table class="bookyol-compare">
        <thead>
            <tr>
                <th></th>
                <?php foreach ( $books as $b ) : ?>
                    <th><?php echo esc_html( $b['title'] ); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr class="bookyol-compare__covers">
                <td><?php esc_html_e( 'Cover', 'bookyol' ); ?></td>
                <?php foreach ( $books as $b ) : ?>
                    <td>
                        <?php if ( ! empty( $b['cover_url'] ) ) : ?>
                            <img src="<?php echo esc_url( $b['cover_url'] ); ?>" alt="<?php echo esc_attr( $b['title'] ); ?>" width="100" loading="lazy">
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Author', 'bookyol' ); ?></td>
                <?php foreach ( $books as $b ) : ?>
                    <td><?php echo esc_html( $b['book_author'] ); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Rating', 'bookyol' ); ?></td>
                <?php foreach ( $books as $b ) : ?>
                    <td>
                        <?php if ( (float) $b['rating'] > 0 ) : ?>
                            <span style="color:#f59e0b;"><?php echo esc_html( BookYol_Shortcodes::render_stars( $b['rating'] ) ); ?></span>
                            <?php echo esc_html( $b['rating'] ); ?>/5
                        <?php else : ?>
                            —
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Best For', 'bookyol' ); ?></td>
                <?php foreach ( $books as $b ) : ?>
                    <td><?php echo esc_html( $b['best_for'] ? $b['best_for'] : '—' ); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Pages', 'bookyol' ); ?></td>
                <?php foreach ( $books as $b ) : ?>
                    <td><?php echo esc_html( $b['pages'] ? $b['pages'] : '—' ); ?></td>
                <?php endforeach; ?>
            </tr>
            <tr class="bookyol-compare__cta">
                <td><?php esc_html_e( 'Read', 'bookyol' ); ?></td>
                <?php foreach ( $books as $b ) :
                    $primary = ! empty( $b['available'] ) ? $b['available'][0] : ''; ?>
                    <td>
                        <?php if ( $primary ) : ?>
                            <a href="<?php echo esc_url( BookYol_Shortcodes::redirect_url( $primary, $b['slug'] ) ); ?>"
                               class="bookyol-btn bookyol-btn--small"
                               target="_blank"
                               rel="nofollow sponsored noopener">
                                <?php echo esc_html( sprintf( __( 'Read on %s', 'bookyol' ), BookYol_Shortcodes::platform_display_name( $primary ) ) ); ?> →
                            </a>
                        <?php else : ?>
                            —
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div>
