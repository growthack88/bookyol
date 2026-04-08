<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Click_Tracker {

    public function __construct() {
        add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
    }

    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'bookyol_clicks';
    }

    public static function create_table() {
        global $wpdb;
        $table           = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            book_id BIGINT UNSIGNED NOT NULL,
            platform VARCHAR(50) NOT NULL,
            country_code VARCHAR(5) DEFAULT '',
            clicked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            referer_url VARCHAR(500) DEFAULT '',
            INDEX idx_book (book_id),
            INDEX idx_platform (platform),
            INDEX idx_date (clicked_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function log_click( $book_id, $platform, $country_code = '', $referer = '' ) {
        global $wpdb;
        $wpdb->insert(
            self::table_name(),
            array(
                'book_id'      => absint( $book_id ),
                'platform'     => substr( sanitize_text_field( $platform ), 0, 50 ),
                'country_code' => substr( sanitize_text_field( $country_code ), 0, 5 ),
                'clicked_at'   => current_time( 'mysql' ),
                'referer_url'  => substr( esc_url_raw( $referer ), 0, 500 ),
            ),
            array( '%d', '%s', '%s', '%s', '%s' )
        );
    }

    public function register_dashboard_widget() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        wp_add_dashboard_widget(
            'bookyol_clicks_widget',
            __( 'BookYol Clicks', 'bookyol' ),
            array( $this, 'render_dashboard_widget' )
        );
    }

    public function render_dashboard_widget() {
        global $wpdb;
        $table = self::table_name();

        $today_start = current_time( 'Y-m-d 00:00:00' );
        $week_start  = gmdate( 'Y-m-d 00:00:00', strtotime( '-6 days', current_time( 'timestamp' ) ) );
        $month_start = current_time( 'Y-m-01 00:00:00' );

        $today = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE clicked_at >= %s", $today_start ) );
        $week  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE clicked_at >= %s", $week_start ) );
        $month = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE clicked_at >= %s", $month_start ) );

        $top_books = $wpdb->get_results( $wpdb->prepare(
            "SELECT book_id, COUNT(*) AS cnt FROM $table WHERE clicked_at >= %s GROUP BY book_id ORDER BY cnt DESC LIMIT 5",
            $month_start
        ) );

        $by_platform = $wpdb->get_results( $wpdb->prepare(
            "SELECT platform, COUNT(*) AS cnt FROM $table WHERE clicked_at >= %s GROUP BY platform ORDER BY cnt DESC",
            $month_start
        ) );
        ?>
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="padding:6px 4px;"><strong><?php esc_html_e( 'Today', 'bookyol' ); ?></strong></td>
                <td style="padding:6px 4px; text-align:right;"><?php echo esc_html( number_format_i18n( $today ) ); ?></td>
            </tr>
            <tr>
                <td style="padding:6px 4px;"><strong><?php esc_html_e( 'This Week', 'bookyol' ); ?></strong></td>
                <td style="padding:6px 4px; text-align:right;"><?php echo esc_html( number_format_i18n( $week ) ); ?></td>
            </tr>
            <tr>
                <td style="padding:6px 4px;"><strong><?php esc_html_e( 'This Month', 'bookyol' ); ?></strong></td>
                <td style="padding:6px 4px; text-align:right;"><?php echo esc_html( number_format_i18n( $month ) ); ?></td>
            </tr>
        </table>

        <h4 style="margin-top:14px; margin-bottom:6px;"><?php esc_html_e( 'Top 5 Books (This Month)', 'bookyol' ); ?></h4>
        <?php if ( empty( $top_books ) ) : ?>
            <p style="color:#6b7280;"><?php esc_html_e( 'No clicks yet.', 'bookyol' ); ?></p>
        <?php else : ?>
            <table style="width:100%; border-collapse:collapse;">
                <?php foreach ( $top_books as $row ) :
                    $title = get_the_title( $row->book_id );
                    if ( ! $title ) $title = '#' . $row->book_id; ?>
                    <tr>
                        <td style="padding:4px 0;"><?php echo esc_html( $title ); ?></td>
                        <td style="padding:4px 0; text-align:right;"><?php echo esc_html( number_format_i18n( $row->cnt ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <h4 style="margin-top:14px; margin-bottom:6px;"><?php esc_html_e( 'By Platform (This Month)', 'bookyol' ); ?></h4>
        <?php if ( empty( $by_platform ) ) : ?>
            <p style="color:#6b7280;"><?php esc_html_e( 'No clicks yet.', 'bookyol' ); ?></p>
        <?php else : ?>
            <table style="width:100%; border-collapse:collapse;">
                <?php foreach ( $by_platform as $row ) : ?>
                    <tr>
                        <td style="padding:4px 0;"><?php echo esc_html( BookYol_Shortcodes::platform_display_name( $row->platform ) ); ?></td>
                        <td style="padding:4px 0; text-align:right;"><?php echo esc_html( number_format_i18n( $row->cnt ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif;
    }
}
