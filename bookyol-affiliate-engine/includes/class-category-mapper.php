<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Category_Mapper {

    const NONCE_ACTION = 'bookyol_map_categories';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'wp_ajax_bookyol_map_categories', array( $this, 'handle_mapping' ) );
        add_action( 'wp_ajax_bookyol_auto_categorize', array( $this, 'auto_categorize' ) );
    }

    public function add_menu_page() {
        add_submenu_page(
            'edit.php?post_type=bookyol_book',
            __( 'Map Categories', 'bookyol' ),
            __( 'Map Categories', 'bookyol' ),
            'manage_options',
            'bookyol-map-categories',
            array( $this, 'render_page' )
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $books = get_posts( array(
            'post_type'      => 'bookyol_book',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft' ),
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        $categories = array();
        if ( taxonomy_exists( 'book_category' ) ) {
            $maybe = get_terms( array(
                'taxonomy'   => 'book_category',
                'hide_empty' => false,
                'orderby'    => 'name',
            ) );
            if ( ! is_wp_error( $maybe ) ) {
                $categories = $maybe;
            }
        }

        $nonce = wp_create_nonce( self::NONCE_ACTION );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Map Books to Categories', 'bookyol' ); ?></h1>
            <p class="description"><?php esc_html_e( 'Assign categories to your books. You can manually select categories or use auto-categorize to let the system guess based on book metadata.', 'bookyol' ); ?></p>

            <div style="margin: 16px 0;">
                <button type="button" id="bookyol-auto-cat-btn" class="button button-primary" style="margin-right: 10px;">
                    🤖 <?php esc_html_e( 'Auto-Categorize All Uncategorized Books', 'bookyol' ); ?>
                </button>
                <button type="button" id="bookyol-save-cats-btn" class="button button-secondary">
                    💾 <?php esc_html_e( 'Save All Changes', 'bookyol' ); ?>
                </button>
                <span id="bookyol-cat-status" style="margin-left: 12px; font-style: italic; color: #666;"></span>
            </div>

            <?php if ( empty( $books ) ) : ?>
                <p><em><?php esc_html_e( 'No books found. Add some books first.', 'bookyol' ); ?></em></p>
            <?php elseif ( empty( $categories ) ) : ?>
                <p><em><?php esc_html_e( 'No categories found. The book_category taxonomy has no terms yet. Deactivate and reactivate the plugin to create default terms.', 'bookyol' ); ?></em></p>
            <?php else : ?>

            <table class="wp-list-table widefat fixed striped" id="bookyol-cat-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="bookyol-select-all"></th>
                        <th style="width: 60px;"><?php esc_html_e( 'Cover', 'bookyol' ); ?></th>
                        <th style="width: 250px;"><?php esc_html_e( 'Book Title', 'bookyol' ); ?></th>
                        <th style="width: 150px;"><?php esc_html_e( 'Author', 'bookyol' ); ?></th>
                        <th style="width: 120px;"><?php esc_html_e( 'Best For', 'bookyol' ); ?></th>
                        <th><?php esc_html_e( 'Categories (check all that apply)', 'bookyol' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $books as $book ) :
                        $cover    = get_post_meta( $book->ID, '_bookyol_cover_url', true );
                        $author   = get_post_meta( $book->ID, '_bookyol_book_author', true );
                        $best_for = get_post_meta( $book->ID, '_bookyol_best_for', true );
                        $assigned = wp_get_post_terms( $book->ID, 'book_category', array( 'fields' => 'ids' ) );
                        if ( is_wp_error( $assigned ) ) $assigned = array();
                        ?>
                        <tr data-book-id="<?php echo esc_attr( $book->ID ); ?>">
                            <td><input type="checkbox" class="bookyol-book-select" value="<?php echo esc_attr( $book->ID ); ?>"></td>
                            <td>
                                <?php if ( $cover ) : ?>
                                    <img src="<?php echo esc_url( $cover ); ?>" style="width:40px;height:60px;object-fit:cover;border-radius:3px;" alt="">
                                <?php else : ?>
                                    <span style="color:#ccc;">📕</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo esc_html( $book->post_title ); ?></strong></td>
                            <td><?php echo esc_html( $author ); ?></td>
                            <td><small><?php echo esc_html( $best_for ); ?></small></td>
                            <td class="bookyol-cat-checkboxes">
                                <?php foreach ( $categories as $cat ) : ?>
                                    <label style="display:inline-block;margin:2px 8px 2px 0;font-size:12px;white-space:nowrap;">
                                        <input type="checkbox"
                                               name="cats[<?php echo esc_attr( $book->ID ); ?>][]"
                                               value="<?php echo esc_attr( $cat->term_id ); ?>"
                                               <?php checked( in_array( $cat->term_id, $assigned, true ) ); ?>>
                                        <?php echo esc_html( $cat->name ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 16px;">
                <button type="button" id="bookyol-save-cats-btn-bottom" class="button button-primary">
                    💾 <?php esc_html_e( 'Save All Changes', 'bookyol' ); ?>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <script>
        (function() {
            var ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
            var nonce   = '<?php echo esc_js( $nonce ); ?>';

            var selectAll = document.getElementById('bookyol-select-all');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    var boxes = document.querySelectorAll('.bookyol-book-select');
                    for (var i = 0; i < boxes.length; i++) { boxes[i].checked = selectAll.checked; }
                });
            }

            function saveCategories() {
                var status = document.getElementById('bookyol-cat-status');
                status.textContent = 'Saving…';
                status.style.color = '#666';

                var mappings = {};
                var rows = document.querySelectorAll('#bookyol-cat-table tbody tr');
                for (var i = 0; i < rows.length; i++) {
                    var bookId = rows[i].getAttribute('data-book-id');
                    var checked = rows[i].querySelectorAll('.bookyol-cat-checkboxes input:checked');
                    var ids = [];
                    for (var j = 0; j < checked.length; j++) { ids.push(parseInt(checked[j].value, 10)); }
                    mappings[bookId] = ids;
                }

                var body = new URLSearchParams();
                body.append('action', 'bookyol_map_categories');
                body.append('nonce', nonce);
                body.append('mappings', JSON.stringify(mappings));

                fetch(ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (data && data.success) {
                        status.textContent = '✅ ' + data.data.message;
                        status.style.color = '#2ECC87';
                    } else {
                        status.textContent = '❌ Error saving';
                        status.style.color = '#FF6B6B';
                    }
                }).catch(function() {
                    status.textContent = '❌ Network error';
                    status.style.color = '#FF6B6B';
                });
            }

            var btnTop = document.getElementById('bookyol-save-cats-btn');
            var btnBot = document.getElementById('bookyol-save-cats-btn-bottom');
            if (btnTop) btnTop.addEventListener('click', saveCategories);
            if (btnBot) btnBot.addEventListener('click', saveCategories);

            var autoBtn = document.getElementById('bookyol-auto-cat-btn');
            if (autoBtn) {
                autoBtn.addEventListener('click', function() {
                    if (!confirm('This will auto-assign categories to books that have no categories. Books with existing categories will not be changed. Continue?')) return;

                    var status = document.getElementById('bookyol-cat-status');
                    status.textContent = 'Auto-categorizing…';
                    status.style.color = '#666';
                    autoBtn.disabled = true;

                    var body = new URLSearchParams();
                    body.append('action', 'bookyol_auto_categorize');
                    body.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString()
                    }).then(function(r) { return r.json(); }).then(function(data) {
                        autoBtn.disabled = false;
                        if (data && data.success) {
                            status.textContent = '✅ ' + data.data.message;
                            status.style.color = '#2ECC87';
                            setTimeout(function() { location.reload(); }, 1500);
                        } else {
                            status.textContent = '❌ Auto-categorize failed';
                            status.style.color = '#FF6B6B';
                        }
                    }).catch(function() {
                        autoBtn.disabled = false;
                        status.textContent = '❌ Network error';
                        status.style.color = '#FF6B6B';
                    });
                });
            }
        })();
        </script>
        <?php
    }

    public function handle_mapping() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized', 'bookyol' ) ), 403 );
        }

        $raw      = isset( $_POST['mappings'] ) ? wp_unslash( $_POST['mappings'] ) : '';
        $mappings = json_decode( $raw, true );
        if ( ! is_array( $mappings ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data', 'bookyol' ) ) );
        }

        $count = 0;
        foreach ( $mappings as $book_id => $term_ids ) {
            $book_id  = intval( $book_id );
            $term_ids = array_map( 'intval', (array) $term_ids );
            wp_set_post_terms( $book_id, $term_ids, 'book_category' );
            $count++;
        }

        wp_send_json_success( array(
            'message' => sprintf( _n( '%d book updated.', '%d books updated.', $count, 'bookyol' ), $count ),
        ) );
    }

    public function auto_categorize() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized', 'bookyol' ) ), 403 );
        }

        $keyword_map = array(
            'fiction'      => array( 'fiction', 'novel', 'story', 'stories', 'literary' ),
            'business'     => array( 'business', 'startup', 'entrepreneur', 'company', 'management', 'strategy', 'corporate', 'commerce', 'enterprise' ),
            'psychology'   => array( 'psychology', 'mind', 'brain', 'behavior', 'cognitive', 'mental', 'thinking', 'emotional', 'habits', 'decision' ),
            'self-help'    => array( 'self-help', 'self help', 'personal development', 'motivation', 'mindset', 'happiness', 'wellness', 'growth', 'improvement' ),
            'productivity' => array( 'productivity', 'time management', 'focus', 'efficiency', 'performance', 'routine', 'systems' ),
            'marketing'    => array( 'marketing', 'branding', 'advertising', 'sales', 'copywriting', 'content', 'social media', 'growth hacking', 'influence', 'persuasion' ),
            'finance'      => array( 'finance', 'money', 'investing', 'wealth', 'financial', 'economics', 'stock', 'rich', 'debt', 'budget', 'retirement' ),
            'leadership'   => array( 'leadership', 'leader', 'team', 'culture', 'inspire', 'vision', 'executive', 'ceo' ),
            'biographies'  => array( 'biography', 'autobiography', 'memoir', 'life story', 'portrait' ),
            'science'      => array( 'science', 'physics', 'biology', 'chemistry', 'research', 'data', 'technology', 'evolution', 'space', 'universe' ),
            'philosophy'   => array( 'philosophy', 'stoic', 'wisdom', 'meaning', 'ethics', 'morality', 'existence', 'consciousness', 'meditations' ),
            'history'      => array( 'history', 'historical', 'war', 'ancient', 'civilization', 'century', 'empire', 'revolution' ),
            'creativity'   => array( 'creativity', 'creative', 'art', 'design', 'innovation', 'imagination', 'artist' ),
            'classic'      => array( 'classic', 'literary classic', 'timeless' ),
            'sci-fi'       => array( 'sci-fi', 'science fiction', 'dystopia', 'future', 'robot', 'alien' ),
            'thriller'     => array( 'thriller', 'suspense', 'crime', 'detective', 'murder', 'mystery' ),
            'fantasy'      => array( 'fantasy', 'magic', 'wizard', 'dragon', 'quest', 'realm' ),
            'romance'      => array( 'romance', 'love', 'relationship', 'heart', 'passion' ),
            'health'       => array( 'health', 'fitness', 'nutrition', 'diet', 'exercise', 'wellbeing' ),
            'communication' => array( 'communication', 'conversation', 'speaking', 'negotiation', 'listening' ),
            'economics'    => array( 'economics', 'economy', 'macro', 'microeconomics', 'game theory' ),
            'parenting'    => array( 'parenting', 'children', 'parent', 'family', 'raising' ),
            'spirituality' => array( 'spiritual', 'meditation', 'buddhism', 'mindfulness', 'soul', 'awakening' ),
        );

        $books = get_posts( array(
            'post_type'      => 'bookyol_book',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft' ),
        ) );

        $updated = 0;
        $skipped = 0;

        foreach ( $books as $book ) {
            $existing = wp_get_post_terms( $book->ID, 'book_category', array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $existing ) && ! empty( $existing ) ) {
                $skipped++;
                continue;
            }

            $title    = strtolower( $book->post_title );
            $best_for = strtolower( (string) get_post_meta( $book->ID, '_bookyol_best_for', true ) );
            $excerpt  = strtolower( (string) $book->post_excerpt );
            $content  = strtolower( wp_strip_all_tags( (string) $book->post_content ) );
            $searchable = $title . ' ' . $best_for . ' ' . $excerpt . ' ' . $content;

            $matched_cats = array();

            foreach ( $keyword_map as $cat_slug => $keywords ) {
                foreach ( $keywords as $kw ) {
                    if ( stripos( $searchable, $kw ) !== false ) {
                        $term = get_term_by( 'slug', $cat_slug, 'book_category' );
                        if ( $term && ! in_array( $term->term_id, $matched_cats, true ) ) {
                            $matched_cats[] = $term->term_id;
                        }
                        break;
                    }
                }
            }

            if ( ! empty( $matched_cats ) ) {
                wp_set_post_terms( $book->ID, $matched_cats, 'book_category' );
                $updated++;
            } else {
                $skipped++;
            }
        }

        wp_send_json_success( array(
            'message' => sprintf(
                __( '%1$d books categorized. %2$d skipped (already categorized or no match).', 'bookyol' ),
                $updated,
                $skipped
            ),
        ) );
    }
}
