<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Homepage_Settings {

    const OPTION_KEY = 'bookyol_homepage_settings';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function register_menu() {
        add_submenu_page(
            'edit.php?post_type=bookyol_book',
            __( 'Homepage Settings', 'bookyol' ),
            __( 'Homepage Settings', 'bookyol' ),
            'manage_options',
            'bookyol-homepage-settings',
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'bookyol_homepage_group',
            self::OPTION_KEY,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize' ),
                'default'           => array(),
            )
        );
    }

    public function sanitize( $input ) {
        if ( ! is_array( $input ) ) {
            return array();
        }

        $textareas = array(
            'format_digital_desc', 'format_audio_desc', 'format_physical_desc',
            'audio_desc', 'newsletter_subtitle', 'footer_description', 'hero_subtitle',
            'quote_text',
        );
        $urls = array(
            'format_digital_url', 'format_audio_url', 'format_physical_url',
            'audio_btn_url', 'newsletter_form_action',
        );
        $checkboxes = array( 'audio_show', 'newsletter_show', 'articles_show', 'cat_rows_show', 'top_rated_show', 'quote_show' );
        $ints       = array( 'hero_shelf_count', 'trending_count', 'new_count', 'articles_count', 'cat_rows_count', 'cat_rows_books_per', 'top_rated_count' );

        $out = array();
        foreach ( $input as $key => $value ) {
            if ( in_array( $key, $checkboxes, true ) ) {
                $out[ $key ] = ! empty( $value ) ? 1 : 0;
            } elseif ( in_array( $key, $ints, true ) ) {
                $out[ $key ] = absint( $value );
            } elseif ( in_array( $key, $urls, true ) ) {
                $out[ $key ] = esc_url_raw( wp_unslash( $value ) );
            } elseif ( in_array( $key, $textareas, true ) ) {
                $out[ $key ] = sanitize_textarea_field( wp_unslash( $value ) );
            } elseif ( $key === 'categories_json' || $key === 'collections_json' ) {
                $raw     = wp_unslash( $value );
                $decoded = json_decode( $raw, true );
                if ( ! is_array( $decoded ) ) {
                    $out[ $key ] = '';
                } else {
                    $clean = array();
                    foreach ( $decoded as $row ) {
                        if ( ! is_array( $row ) ) continue;
                        $item = array();
                        foreach ( $row as $f => $v ) {
                            if ( $f === 'url' ) {
                                $item[ $f ] = esc_url_raw( $v );
                            } else {
                                $item[ $f ] = sanitize_text_field( $v );
                            }
                        }
                        $clean[] = $item;
                    }
                    $out[ $key ] = wp_json_encode( $clean );
                }
            } else {
                $out[ $key ] = sanitize_text_field( wp_unslash( $value ) );
            }
        }

        // Ensure checkboxes default to 0 if unchecked (not present in POST).
        foreach ( $checkboxes as $cb ) {
            if ( ! isset( $out[ $cb ] ) ) {
                $out[ $cb ] = 0;
            }
        }

        return $out;
    }

    private function text( $key, $value, $placeholder = '' ) {
        printf(
            '<input type="text" name="%1$s[%2$s]" id="bookyol_%2$s" value="%3$s" class="regular-text" placeholder="%4$s" />',
            esc_attr( self::OPTION_KEY ),
            esc_attr( $key ),
            esc_attr( $value ),
            esc_attr( $placeholder )
        );
    }

    private function textarea( $key, $value ) {
        printf(
            '<textarea name="%1$s[%2$s]" id="bookyol_%2$s" rows="3" class="large-text">%3$s</textarea>',
            esc_attr( self::OPTION_KEY ),
            esc_attr( $key ),
            esc_textarea( $value )
        );
    }

    private function number( $key, $value ) {
        printf(
            '<input type="number" name="%1$s[%2$s]" id="bookyol_%2$s" value="%3$s" class="small-text" min="1" />',
            esc_attr( self::OPTION_KEY ),
            esc_attr( $key ),
            esc_attr( $value )
        );
    }

    private function select( $key, $value, $options ) {
        printf( '<select name="%1$s[%2$s]" id="bookyol_%2$s">', esc_attr( self::OPTION_KEY ), esc_attr( $key ) );
        foreach ( $options as $val => $label ) {
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $val ),
                selected( $value, $val, false ),
                esc_html( $label )
            );
        }
        echo '</select>';
    }

    private function checkbox( $key, $value, $label = '' ) {
        printf(
            '<label><input type="checkbox" name="%1$s[%2$s]" id="bookyol_%2$s" value="1" %3$s /> %4$s</label>',
            esc_attr( self::OPTION_KEY ),
            esc_attr( $key ),
            checked( $value, 1, false ),
            esc_html( $label )
        );
    }

    private function color( $key, $value ) {
        printf(
            '<input type="color" name="%1$s[%2$s]" id="bookyol_%2$s" value="%3$s" />',
            esc_attr( self::OPTION_KEY ),
            esc_attr( $key ),
            esc_attr( $value )
        );
    }

    private function field_row( $label, $callback ) {
        echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
        call_user_func( $callback );
        echo '</td></tr>';
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $s = bookyol_get_homepage_settings();

        $categories_json  = ! empty( $s['categories_json'] )  ? $s['categories_json']  : wp_json_encode( bookyol_default_categories() );
        $collections_json = ! empty( $s['collections_json'] ) ? $s['collections_json'] : wp_json_encode( bookyol_default_collections() );
        ?>
        <div class="wrap bookyol-homepage-admin">
            <h1><?php esc_html_e( 'BookYol Homepage Settings', 'bookyol' ); ?></h1>
            <p class="description"><?php esc_html_e( 'Configure all sections of your homepage. Assign the "BookYol Homepage" template to a page under Pages → Page Attributes → Template.', 'bookyol' ); ?></p>

            <nav class="nav-tab-wrapper bookyol-tabs">
                <a href="#hero"       class="nav-tab nav-tab-active" data-tab="hero"><?php esc_html_e( 'Hero', 'bookyol' ); ?></a>
                <a href="#formats"    class="nav-tab" data-tab="formats"><?php esc_html_e( 'Formats', 'bookyol' ); ?></a>
                <a href="#trending"   class="nav-tab" data-tab="trending"><?php esc_html_e( 'Trending', 'bookyol' ); ?></a>
                <a href="#catrows"    class="nav-tab" data-tab="catrows"><?php esc_html_e( 'Category Rows', 'bookyol' ); ?></a>
                <a href="#categories" class="nav-tab" data-tab="categories"><?php esc_html_e( 'Categories', 'bookyol' ); ?></a>
                <a href="#toprated"   class="nav-tab" data-tab="toprated"><?php esc_html_e( 'Highest Rated', 'bookyol' ); ?></a>
                <a href="#quote"      class="nav-tab" data-tab="quote"><?php esc_html_e( 'Quote', 'bookyol' ); ?></a>
                <a href="#audiobook"  class="nav-tab" data-tab="audiobook"><?php esc_html_e( 'Audiobook', 'bookyol' ); ?></a>
                <a href="#collections" class="nav-tab" data-tab="collections"><?php esc_html_e( 'Collections', 'bookyol' ); ?></a>
                <a href="#new"        class="nav-tab" data-tab="new"><?php esc_html_e( 'New This Week', 'bookyol' ); ?></a>
                <a href="#newsletter" class="nav-tab" data-tab="newsletter"><?php esc_html_e( 'Newsletter', 'bookyol' ); ?></a>
                <a href="#articles"   class="nav-tab" data-tab="articles"><?php esc_html_e( 'Articles', 'bookyol' ); ?></a>
                <a href="#footer"     class="nav-tab" data-tab="footer"><?php esc_html_e( 'Footer', 'bookyol' ); ?></a>
            </nav>

            <form method="post" action="options.php">
                <?php settings_fields( 'bookyol_homepage_group' ); ?>

                <?php $source_opts = array( 'latest' => 'Latest', 'featured' => 'Featured (IDs)', 'random' => 'Random' ); ?>
                <?php $trending_opts = $source_opts; $trending_opts['most_clicked'] = 'Most Clicked'; ?>

                <!-- HERO -->
                <div class="bookyol-tab-pane" data-pane="hero">
                    <h2><?php esc_html_e( 'Hero / Top Section', 'bookyol' ); ?></h2>
                    <table class="form-table">
                        <?php $this->field_row( 'Title', function() use ( $s ) { $this->text( 'hero_title', $s['hero_title'] ); } ); ?>
                        <?php $this->field_row( 'Highlight (colored part)', function() use ( $s ) { $this->text( 'hero_title_highlight', $s['hero_title_highlight'] ); } ); ?>
                        <?php $this->field_row( 'Subtitle', function() use ( $s ) { $this->textarea( 'hero_subtitle', $s['hero_subtitle'] ); } ); ?>
                        <?php $this->field_row( 'Shelf Source', function() use ( $s, $source_opts ) { $this->select( 'hero_shelf_source', $s['hero_shelf_source'], $source_opts ); } ); ?>
                        <?php $this->field_row( 'Shelf Book IDs (for Featured)', function() use ( $s ) { $this->text( 'hero_shelf_book_ids', $s['hero_shelf_book_ids'], '1,2,3' ); } ); ?>
                        <?php $this->field_row( 'Shelf Count', function() use ( $s ) { $this->number( 'hero_shelf_count', $s['hero_shelf_count'] ); } ); ?>
                    </table>
                </div>

                <!-- FORMATS -->
                <div class="bookyol-tab-pane" data-pane="formats" style="display:none;">
                    <h2><?php esc_html_e( 'Reading Formats', 'bookyol' ); ?></h2>
                    <h3><?php esc_html_e( 'Digital Card', 'bookyol' ); ?></h3>
                    <table class="form-table">
                        <?php $this->field_row( 'Title',     function() use ( $s ) { $this->text( 'format_digital_title', $s['format_digital_title'] ); } ); ?>
                        <?php $this->field_row( 'Description', function() use ( $s ) { $this->textarea( 'format_digital_desc', $s['format_digital_desc'] ); } ); ?>
                        <?php $this->field_row( 'Platforms', function() use ( $s ) { $this->text( 'format_digital_platforms', $s['format_digital_platforms'] ); } ); ?>
                        <?php $this->field_row( 'URL',       function() use ( $s ) { $this->text( 'format_digital_url', $s['format_digital_url'] ); } ); ?>
                    </table>
                    <h3><?php esc_html_e( 'Audio Card', 'bookyol' ); ?></h3>
                    <table class="form-table">
                        <?php $this->field_row( 'Title',     function() use ( $s ) { $this->text( 'format_audio_title', $s['format_audio_title'] ); } ); ?>
                        <?php $this->field_row( 'Description', function() use ( $s ) { $this->textarea( 'format_audio_desc', $s['format_audio_desc'] ); } ); ?>
                        <?php $this->field_row( 'Platforms', function() use ( $s ) { $this->text( 'format_audio_platforms', $s['format_audio_platforms'] ); } ); ?>
                        <?php $this->field_row( 'URL',       function() use ( $s ) { $this->text( 'format_audio_url', $s['format_audio_url'] ); } ); ?>
                    </table>
                    <h3><?php esc_html_e( 'Physical Card', 'bookyol' ); ?></h3>
                    <table class="form-table">
                        <?php $this->field_row( 'Title',     function() use ( $s ) { $this->text( 'format_physical_title', $s['format_physical_title'] ); } ); ?>
                        <?php $this->field_row( 'Description', function() use ( $s ) { $this->textarea( 'format_physical_desc', $s['format_physical_desc'] ); } ); ?>
                        <?php $this->field_row( 'Platforms', function() use ( $s ) { $this->text( 'format_physical_platforms', $s['format_physical_platforms'] ); } ); ?>
                        <?php $this->field_row( 'URL',       function() use ( $s ) { $this->text( 'format_physical_url', $s['format_physical_url'] ); } ); ?>
                    </table>
                </div>

                <!-- TRENDING -->
                <div class="bookyol-tab-pane" data-pane="trending" style="display:none;">
                    <h2><?php esc_html_e( 'Trending Now', 'bookyol' ); ?></h2>
                    <table class="form-table">
                        <?php $this->field_row( 'Title',    function() use ( $s ) { $this->text( 'trending_title', $s['trending_title'] ); } ); ?>
                        <?php $this->field_row( 'Source',   function() use ( $s, $trending_opts ) { $this->select( 'trending_source', $s['trending_source'], $trending_opts ); } ); ?>
                        <?php $this->field_row( 'Book IDs', function() use ( $s ) { $this->text( 'trending_book_ids', $s['trending_book_ids'], '1,2,3' ); } ); ?>
                        <?php $this->field_row( 'Count',    function() use ( $s ) { $this->number( 'trending_count', $s['trending_count'] ); } ); ?>
                        <?php $this->field_row( 'Accent Color', function() use ( $s ) { $this->color( 'trending_color', $s['trending_color'] ); } ); ?>
                    </table>
                </div>

                <!-- CATEGORY ROWS (v4.2.0) -->
                <div class="bookyol-tab-pane" data-pane="catrows" style="display:none;">
                    <h2><?php esc_html_e( 'Category Rows', 'bookyol' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Show dynamic book rows by category on the homepage. Each row pulls random books from one category.', 'bookyol' ); ?></p>
                    <table class="form-table">
                        <?php $this->field_row( 'Show Category Rows', function() use ( $s ) { $this->checkbox( 'cat_rows_show', $s['cat_rows_show'], __( 'Display category book rows on homepage', 'bookyol' ) ); } ); ?>
                        <?php $this->field_row( 'Source', function() use ( $s ) { $this->select( 'cat_rows_source', $s['cat_rows_source'], array( 'auto' => 'Auto (most populated categories)', 'manual' => 'Manual (specify slugs below)' ) ); } ); ?>
                        <?php $this->field_row( 'Number of Rows (Auto mode)', function() use ( $s ) { $this->number( 'cat_rows_count', $s['cat_rows_count'] ); } ); ?>
                        <?php $this->field_row( 'Books per Row', function() use ( $s ) { $this->number( 'cat_rows_books_per', $s['cat_rows_books_per'] ); } ); ?>
                        <?php $this->field_row( 'Manual Category Slugs', function() use ( $s ) { $this->text( 'cat_rows_slugs', $s['cat_rows_slugs'], 'fiction,business,psychology,self-help' ); } ); ?>
                    </table>
                </div>

                <!-- CATEGORIES -->
                <div class="bookyol-tab-pane" data-pane="categories" style="display:none;">
                    <h2><?php esc_html_e( 'Categories', 'bookyol' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Add, edit, or remove category pills. Color classes: biz, psy, self, prod, mkt, fin, lead, bio, sci, phil, his, cre', 'bookyol' ); ?></p>
                    <table class="widefat bookyol-repeater" id="bookyol-categories-repeater">
                        <thead>
                            <tr>
                                <th style="width:60px;"><?php esc_html_e( 'Icon', 'bookyol' ); ?></th>
                                <th><?php esc_html_e( 'Name', 'bookyol' ); ?></th>
                                <th><?php esc_html_e( 'URL', 'bookyol' ); ?></th>
                                <th style="width:120px;"><?php esc_html_e( 'Color', 'bookyol' ); ?></th>
                                <th style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <p><button type="button" class="button bookyol-repeater-add" data-target="bookyol-categories-repeater"><?php esc_html_e( '+ Add Category', 'bookyol' ); ?></button></p>
                    <textarea id="bookyol_categories_json" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[categories_json]" style="display:none;"><?php echo esc_textarea( $categories_json ); ?></textarea>
                </div>

                <!-- HIGHEST RATED (v4.2.0) -->
                <div class="bookyol-tab-pane" data-pane="toprated" style="display:none;">
                    <h2><?php esc_html_e( 'Highest Rated Books', 'bookyol' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Shows the top-rated books across all categories, sorted by rating descending.', 'bookyol' ); ?></p>
                    <table class="form-table">
                        <?php $this->field_row( 'Show Section', function() use ( $s ) { $this->checkbox( 'top_rated_show', $s['top_rated_show'], __( 'Display highest-rated section', 'bookyol' ) ); } ); ?>
                        <?php $this->field_row( 'Title',       function() use ( $s ) { $this->text( 'top_rated_title', $s['top_rated_title'] ); } ); ?>
                        <?php $this->field_row( 'Book Count',  function() use ( $s ) { $this->number( 'top_rated_count', $s['top_rated_count'] ); } ); ?>
                    </table>
                </div>

                <!-- QUOTE BANNER (v4.2.0) -->
                <div class="bookyol-tab-pane" data-pane="quote" style="display:none;">
                    <h2><?php esc_html_e( 'Quote Banner', 'bookyol' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'A visual break on the homepage with an inspirational quote about reading.', 'bookyol' ); ?></p>
                    <table class="form-table">
                        <?php $this->field_row( 'Show Quote',   function() use ( $s ) { $this->checkbox( 'quote_show', $s['quote_show'], __( 'Display the quote banner', 'bookyol' ) ); } ); ?>
                        <?php $this->field_row( 'Quote Text',   function() use ( $s ) { $this->textarea( 'quote_text', $s['quote_text'] ); } ); ?>
                        <?php $this->field_row( 'Quote Author', function() use ( $s ) { $this->text( 'quote_author', $s['quote_author'] ); } ); ?>
                    </table>
                </div>

                <!-- AUDIOBOOK -->
                <div class="bookyol-tab-pane" data-pane="audiobook" style="display:none;">
                    <h2><?php esc_html_e( 'Audiobook Spotlight', 'bookyol' ); ?></h2>
                    <table class="form-table">
                        <?php $this->field_row( 'Show Section', function() use ( $s ) { $this->checkbox( 'audio_show', $s['audio_show'], __( 'Display audiobook spotlight on homepage', 'bookyol' ) ); } ); ?>
                        <?php $this->field_row( 'Title',       function() use ( $s ) { $this->text( 'audio_title', $s['audio_title'] ); } ); ?>
                        <?php $this->field_row( 'Description', function() use ( $s ) { $this->textarea( 'audio_desc', $s['audio_desc'] ); } ); ?>
                        <?php $this->field_row( 'Button Text', function() use ( $s ) { $this->text( 'audio_btn_text', $s['audio_btn_text'] ); } ); ?>
                        <?php $this->field_row( 'Button URL',  function() use ( $s ) { $this->text( 'audio_btn_url', $s['audio_btn_url'] ); } ); ?>
                        <?php $this->field_row( 'Book IDs (3 covers)', function() use ( $s ) { $this->text( 'audio_book_ids', $s['audio_book_ids'], '1,2,3' ); } ); ?>
                    </table>
                </div>

                <!-- COLLECTIONS -->
                <div class="bookyol-tab-pane" data-pane="collections" style="display:none;">
                    <h2><?php esc_html_e( 'Collections', 'bookyol' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Gradient options: 1=purple, 2=pink, 3=cyan, 4=green, 5=sunset, 6=lavender', 'bookyol' ); ?></p>
                    <table class="widefat bookyol-repeater" id="bookyol-collections-repeater">
                        <thead>
                            <tr>
                                <th style="width:60px;"><?php esc_html_e( 'Emoji', 'bookyol' ); ?></th>
                                <th><?php esc_html_e( 'Title', 'bookyol' ); ?></th>
                                <th style="width:120px;"><?php esc_html_e( 'Count', 'bookyol' ); ?></th>
                                <th><?php esc_html_e( 'URL', 'bookyol' ); ?></th>
                                <th style="width:100px;"><?php esc_html_e( 'Gradient', 'bookyol' ); ?></th>
                                <th style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <p><button type="button" class="button bookyol-repeater-add" data-target="bookyol-collections-repeater"><?php esc_html_e( '+ Add Collection', 'bookyol' ); ?></button></p>
                    <textarea id="bookyol_collections_json" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[collections_json]" style="display:none;"><?php echo esc_textarea( $collections_json ); ?></textarea>
                </div>

                <!-- NEW THIS WEEK -->
                <div class="bookyol-tab-pane" data-pane="new" style="display:none;">
                    <h2><?php esc_html_e( 'New This Week', 'bookyol' ); ?></h2>
                    <table class="form-table">
                        <?php $this->field_row( 'Title',    function() use ( $s ) { $this->text( 'new_title', $s['new_title'] ); } ); ?>
                        <?php $this->field_row( 'Source',   function() use ( $s, $source_opts ) { $this->select( 'new_source', $s['new_source'], $source_opts ); } ); ?>
                        <?php $this->field_row( 'Book IDs', function() use ( $s ) { $this->text( 'new_book_ids', $s['new_book_ids'], '1,2,3' ); } ); ?>
                        <?php $this->field_row( 'Count',    function() use ( $s ) { $this->number( 'new_count', $s['new_count'] ); } ); ?>
                        <?php $this->field_row( 'Accent Color', function() use ( $s ) { $this->color( 'new_color', $s['new_color'] ); } ); ?>
                    </table>
                </div>

                <!-- NEWSLETTER -->
                <div class="bookyol-tab-pane" data-pane="newsletter" style="display:none;">
                    <h2><?php esc_html_e( 'Newsletter', 'bookyol' ); ?></h2>
                    <table class="form-table">
                        <?php $this->field_row( 'Show Section', function() use ( $s ) { $this->checkbox( 'newsletter_show', $s['newsletter_show'], __( 'Display newsletter section', 'bookyol' ) ); } ); ?>
                        <?php $this->field_row( 'Title',     function() use ( $s ) { $this->text( 'newsletter_title', $s['newsletter_title'] ); } ); ?>
                        <?php $this->field_row( 'Subtitle',  function() use ( $s ) { $this->textarea( 'newsletter_subtitle', $s['newsletter_subtitle'] ); } ); ?>
                        <?php $this->field_row( 'Button Text', function() use ( $s ) { $this->text( 'newsletter_btn_text', $s['newsletter_btn_text'] ); } ); ?>
                        <?php $this->field_row( 'Form Action URL', function() use ( $s ) { $this->text( 'newsletter_form_action', $s['newsletter_form_action'], 'ConvertKit/Mailchimp URL' ); } ); ?>
                        <?php $this->field_row( 'Note',      function() use ( $s ) { $this->text( 'newsletter_note', $s['newsletter_note'] ); } ); ?>
                    </table>
                </div>

                <!-- ARTICLES -->
                <div class="bookyol-tab-pane" data-pane="articles" style="display:none;">
                    <h2><?php esc_html_e( 'Blog Articles', 'bookyol' ); ?></h2>
                    <table class="form-table">
                        <?php $this->field_row( 'Show Section', function() use ( $s ) { $this->checkbox( 'articles_show', $s['articles_show'], __( 'Display articles section', 'bookyol' ) ); } ); ?>
                        <?php $this->field_row( 'Title',  function() use ( $s ) { $this->text( 'articles_title', $s['articles_title'] ); } ); ?>
                        <?php $this->field_row( 'Count',  function() use ( $s ) { $this->number( 'articles_count', $s['articles_count'] ); } ); ?>
                        <?php $this->field_row( 'Source', function() use ( $s ) { $this->select( 'articles_source', $s['articles_source'], array( 'posts' => 'Blog Posts', 'books' => 'Books' ) ); } ); ?>
                        <?php $this->field_row( 'Accent Color', function() use ( $s ) { $this->color( 'articles_color', $s['articles_color'] ); } ); ?>
                    </table>
                </div>

                <!-- FOOTER -->
                <div class="bookyol-tab-pane" data-pane="footer" style="display:none;">
                    <h2><?php esc_html_e( 'Footer', 'bookyol' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'These values are exposed in settings for future use. The current template uses your Astra theme footer via get_footer().', 'bookyol' ); ?></p>
                    <table class="form-table">
                        <?php $this->field_row( 'Description', function() use ( $s ) { $this->textarea( 'footer_description', $s['footer_description'] ); } ); ?>
                        <?php $this->field_row( 'Column 2 Title', function() use ( $s ) { $this->text( 'footer_col2_title', $s['footer_col2_title'] ); } ); ?>
                        <?php $this->field_row( 'Column 3 Title', function() use ( $s ) { $this->text( 'footer_col3_title', $s['footer_col3_title'] ); } ); ?>
                        <?php $this->field_row( 'Column 4 Title', function() use ( $s ) { $this->text( 'footer_col4_title', $s['footer_col4_title'] ); } ); ?>
                    </table>
                </div>

                <?php submit_button( __( 'Save All Settings', 'bookyol' ) ); ?>
            </form>
        </div>
        <?php
    }
}
