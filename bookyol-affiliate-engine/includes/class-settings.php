<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Settings {

    const NONCE_ACTION = 'bookyol_settings_nonce';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_bookyol_regenerate_all_links', array( $this, 'regenerate_all_links' ) );
    }

    public function register_menu() {
        add_submenu_page(
            'edit.php?post_type=bookyol_book',
            __( 'Affiliate Settings', 'bookyol' ),
            __( 'Affiliate Settings', 'bookyol' ),
            'manage_options',
            'bookyol-settings',
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'bookyol_settings_group',
            BookYol_Link_Generator::OPTION_KEY,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize' ),
                'default'           => BookYol_Link_Generator::default_ids(),
            )
        );
    }

    public function sanitize( $input ) {
        $defaults = BookYol_Link_Generator::default_ids();
        $out      = array();
        if ( ! is_array( $input ) ) {
            return $defaults;
        }
        foreach ( array_keys( $defaults ) as $key ) {
            if ( ! isset( $input[ $key ] ) ) {
                $out[ $key ] = '';
                continue;
            }
            $value = trim( wp_unslash( $input[ $key ] ) );
            if ( $key === 'everand_url' ) {
                $out[ $key ] = esc_url_raw( $value );
            } else {
                $out[ $key ] = sanitize_text_field( $value );
            }
        }
        return $out;
    }

    public function enqueue_assets( $hook ) {
        if ( $hook !== 'bookyol_book_page_bookyol-settings' ) {
            return;
        }
        wp_enqueue_style(
            'bookyol-admin',
            BOOKYOL_URL . 'assets/css/bookyol-admin.css',
            array(),
            BOOKYOL_VERSION
        );
        wp_enqueue_script(
            'bookyol-settings',
            BOOKYOL_URL . 'assets/js/bookyol-settings.js',
            array(),
            BOOKYOL_VERSION,
            true
        );
        wp_localize_script( 'bookyol-settings', 'BookYolSettings', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
            'i18n'    => array(
                'confirmRun'       => __( 'This will generate affiliate links for all books. Continue?', 'bookyol' ),
                'confirmOverwrite' => __( 'Overwrite existing links? (OK = yes, replace all; Cancel = only fill empty ones)', 'bookyol' ),
                'regenerating'     => __( 'Regenerating…', 'bookyol' ),
                'regenerateLabel'  => __( 'Regenerate All Links', 'bookyol' ),
                'failed'           => __( 'Request failed. Try again.', 'bookyol' ),
            ),
        ) );
    }

    private function get_platform_config() {
        return array(
            'bookshop_id' => array(
                'platform' => __( 'Bookshop.org', 'bookyol' ),
                'label'    => __( 'Affiliate ID', 'bookyol' ),
                'help'     => __( 'Login to bookshop.org/affiliates → your ID is in your dashboard URL or affiliate profile.', 'bookyol' ),
                'format'   => 'bookshop.org/a/{ID}/{ISBN}',
                'type'     => 'text',
            ),
            'ebookscom_id' => array(
                'platform' => __( 'Ebooks.com', 'bookyol' ),
                'label'    => __( 'Affiliate ID', 'bookyol' ),
                'help'     => __( 'Check your affiliate welcome email or dashboard.', 'bookyol' ),
                'format'   => 'ebooks.com/en-us/book/{ISBN}/?aid={ID}',
                'type'     => 'text',
            ),
            'rakuten_id' => array(
                'platform' => __( 'Kobo (via Rakuten)', 'bookyol' ),
                'label'    => __( 'Rakuten Site ID', 'bookyol' ),
                'help'     => __( 'Rakuten dashboard → Links → Site ID.', 'bookyol' ),
                'format'   => __( 'Rakuten deep link to kobo.com search for ISBN', 'bookyol' ),
                'type'     => 'text',
            ),
            'awin_id' => array(
                'platform' => __( 'Libro.fm (via Awin)', 'bookyol' ),
                'label'    => __( 'Awin Affiliate ID', 'bookyol' ),
                'help'     => __( 'Awin dashboard → top right shows your ID number.', 'bookyol' ),
                'format'   => __( 'Awin deep link to libro.fm with ISBN', 'bookyol' ),
                'type'     => 'text',
            ),
            'everand_url' => array(
                'platform' => __( 'Everand (via PartnerStack)', 'bookyol' ),
                'label'    => __( 'PartnerStack Referral URL', 'bookyol' ),
                'help'     => __( 'Paste your FULL referral link from PartnerStack dashboard. Example: https://everand.com/?utm_source=partnerstack&ref=xx. Everand uses ONE referral link, not per-book links.', 'bookyol' ),
                'format'   => __( 'Same referral URL used for all books', 'bookyol' ),
                'type'     => 'url',
            ),
            'jamalon_id' => array(
                'platform' => __( 'Jamalon (via ArabClicks)', 'bookyol' ),
                'label'    => __( 'ArabClicks Affiliate ID', 'bookyol' ),
                'help'     => __( 'ArabClicks dashboard → your publisher ID.', 'bookyol' ),
                'format'   => 'jamalon.com/en/catalogsearch/result?q={ISBN}&ref={ID}',
                'type'     => 'text',
            ),
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $ids    = BookYol_Link_Generator::get_ids();
        $config = $this->get_platform_config();
        ?>
        <div class="wrap bookyol-settings-wrap">
            <h1><?php esc_html_e( 'BookYol Affiliate Settings', 'bookyol' ); ?></h1>
            <p><?php esc_html_e( 'Enter your affiliate IDs below. Once saved, all new book imports will automatically generate tracked affiliate links. You only need to fill this in ONCE per platform.', 'bookyol' ); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields( 'bookyol_settings_group' ); ?>

                <div class="bookyol-settings-grid">
                    <?php foreach ( $config as $key => $row ) :
                        $value     = isset( $ids[ $key ] ) ? $ids[ $key ] : '';
                        $connected = ! empty( $value );
                        ?>
                        <div class="bookyol-settings-card">
                            <div class="bookyol-settings-card__header">
                                <h3><?php echo esc_html( $row['platform'] ); ?></h3>
                                <span class="bookyol-settings-status <?php echo $connected ? 'is-connected' : ''; ?>">
                                    <?php echo $connected
                                        ? '<span class="bookyol-status-dot bookyol-status-dot--active"></span> ' . esc_html__( 'Connected', 'bookyol' )
                                        : '<span class="bookyol-status-dot"></span> ' . esc_html__( 'Not configured', 'bookyol' );
                                    ?>
                                </span>
                            </div>
                            <label for="bookyol_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $row['label'] ); ?></label>
                            <input
                                type="<?php echo esc_attr( $row['type'] ); ?>"
                                id="bookyol_<?php echo esc_attr( $key ); ?>"
                                name="<?php echo esc_attr( BookYol_Link_Generator::OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]"
                                value="<?php echo esc_attr( $value ); ?>"
                                class="regular-text"
                                <?php echo $row['type'] === 'url' ? 'placeholder="https://"' : ''; ?> />
                            <p class="bookyol-settings-help"><strong><?php esc_html_e( 'How to find:', 'bookyol' ); ?></strong> <?php echo esc_html( $row['help'] ); ?></p>
                            <p class="bookyol-settings-format"><strong><?php esc_html_e( 'Generated format:', 'bookyol' ); ?></strong> <code><?php echo esc_html( $row['format'] ); ?></code></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php submit_button( __( 'Save Settings', 'bookyol' ) ); ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Bulk Actions', 'bookyol' ); ?></h2>
            <p><?php esc_html_e( 'Regenerate affiliate links for ALL existing books using current settings. Useful after you first configure your IDs or change them.', 'bookyol' ); ?></p>
            <p>
                <button type="button" id="bookyol-regenerate-btn" class="button button-secondary"><?php esc_html_e( 'Regenerate All Links', 'bookyol' ); ?></button>
                <span id="bookyol-regenerate-status" style="margin-left:10px;"></span>
            </p>
        </div>
        <?php
    }

    public function regenerate_all_links() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized', 'bookyol' ) ), 403 );
        }

        $overwrite = isset( $_POST['overwrite'] ) && $_POST['overwrite'] === 'true';

        $generator = new BookYol_Link_Generator();
        $result    = $generator->regenerate_all( $overwrite );

        wp_send_json_success( array(
            'message' => sprintf(
                /* translators: 1: updated count, 2: skipped count */
                __( '%1$d books updated. %2$d skipped.', 'bookyol' ),
                $result['updated'],
                $result['skipped']
            ),
            'updated' => $result['updated'],
            'skipped' => $result['skipped'],
        ) );
    }
}
