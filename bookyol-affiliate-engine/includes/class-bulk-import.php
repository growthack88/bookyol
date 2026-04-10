<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Bulk_Import {

    const NONCE_ACTION = 'bookyol_bulk_import_nonce';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_bookyol_bulk_import', array( $this, 'ajax_bulk_import' ) );
    }

    public function register_menu() {
        add_submenu_page(
            'edit.php?post_type=bookyol_book',
            __( 'Bulk Import', 'bookyol' ),
            __( 'Bulk Import', 'bookyol' ),
            'edit_posts',
            'bookyol-bulk-import',
            array( $this, 'render_page' )
        );
    }

    public function enqueue_assets( $hook ) {
        if ( $hook !== 'bookyol_book_page_bookyol-bulk-import' ) {
            return;
        }
        wp_enqueue_style(
            'bookyol-admin',
            BOOKYOL_URL . 'assets/css/bookyol-admin.css',
            array(),
            BOOKYOL_VERSION
        );
        wp_enqueue_script(
            'bookyol-bulk-import',
            BOOKYOL_URL . 'assets/js/bookyol-bulk-import.js',
            array(),
            BOOKYOL_VERSION,
            true
        );
        wp_localize_script( 'bookyol-bulk-import', 'BookYolBulk', array(
            'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
            'lookupNonce'  => wp_create_nonce( BookYol_Book_Lookup::NONCE_ACTION ),
            'importNonce'  => wp_create_nonce( self::NONCE_ACTION ),
            'i18n'         => array(
                'notFound'    => __( 'Not found', 'bookyol' ),
                'looking'     => __( 'Looking up…', 'bookyol' ),
                'importing'   => __( 'Importing…', 'bookyol' ),
                'noSelection' => __( 'Please select at least one book to import.', 'bookyol' ),
                'done'        => __( 'Done', 'bookyol' ),
            ),
        ) );
    }

    public function render_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Bulk Book Import', 'bookyol' ); ?></h1>
            <p><?php esc_html_e( 'Enter book titles or ISBNs, one per line (max 20). Searches Google Books first, then Open Library as fallback.', 'bookyol' ); ?></p>

            <textarea id="bookyol-bulk-input" rows="10" style="width:100%; max-width:700px; font-family:monospace;" placeholder="Atomic Habits&#10;9780735211292&#10;Deep Work&#10;The Psychology of Money"></textarea>

            <p>
                <button type="button" class="button button-primary" id="bookyol-bulk-lookup"><?php esc_html_e( 'Lookup All', 'bookyol' ); ?></button>
                <button type="button" class="button" id="bookyol-clear-cache" style="margin-left:8px;" onclick="
                    if(confirm('Clear all lookup cache? This allows re-searching ISBNs that previously failed.')){
                        fetch(BookYolBulk.ajaxUrl, {
                            method:'POST',
                            headers:{'Content-Type':'application/x-www-form-urlencoded'},
                            body:'action=bookyol_clear_lookup_cache&nonce='+BookYolBulk.lookupNonce
                        }).then(r=>r.json()).then(d=>{
                            alert(d.success ? '✅ Cache cleared! Try Lookup again.' : '❌ Error clearing cache.');
                        });
                    }
                "><?php esc_html_e( '🗑️ Clear Lookup Cache', 'bookyol' ); ?></button>
                <span id="bookyol-bulk-progress" style="margin-left:10px; color:#666;"></span>
            </p>

            <div style="margin:12px 0; padding:12px 16px; background:#f0f6ff; border-left:4px solid #4A90D9; border-radius:4px; font-size:13px; max-width:700px;">
                <strong>💡 Tips:</strong><br>
                • Use <strong>book titles</strong> instead of ISBNs for better results (e.g., "Atomic Habits" not "9780735211292")<br>
                • You can also search by <strong>"Title Author"</strong> (e.g., "Deep Work Cal Newport")<br>
                • If ISBN fails, the system tries Open Library automatically<br>
                • Click "Clear Lookup Cache" if books that should exist keep showing "Not found"
            </div>

            <div id="bookyol-bulk-results"></div>

            <p id="bookyol-bulk-import-row" style="display:none; margin-top:16px;">
                <button type="button" class="button button-primary" id="bookyol-bulk-import-btn"><?php esc_html_e( 'Import Selected', 'bookyol' ); ?></button>
                <span id="bookyol-bulk-summary" style="margin-left:10px;"></span>
            </p>
        </div>
        <?php
    }

    public function ajax_bulk_import() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
        }

        $books_raw = isset( $_POST['books'] ) ? wp_unslash( $_POST['books'] ) : '';
        if ( is_string( $books_raw ) ) {
            $books = json_decode( $books_raw, true );
        } else {
            $books = $books_raw;
        }

        if ( ! is_array( $books ) || empty( $books ) ) {
            wp_send_json_error( array( 'message' => 'No books provided' ) );
        }

        $imported = 0;
        $skipped  = 0;
        $failed   = 0;

        foreach ( $books as $book ) {
            if ( ! is_array( $book ) ) {
                $failed++;
                continue;
            }

            $title = isset( $book['title'] ) ? sanitize_text_field( $book['title'] ) : '';
            if ( empty( $title ) ) {
                $failed++;
                continue;
            }

            $isbn_13 = isset( $book['isbn_13'] ) ? sanitize_text_field( $book['isbn_13'] ) : '';
            $isbn_10 = isset( $book['isbn_10'] ) ? sanitize_text_field( $book['isbn_10'] ) : '';
            $isbn    = ! empty( $isbn_13 ) ? $isbn_13 : $isbn_10;

            if ( ! empty( $isbn ) && $this->isbn_exists( $isbn ) ) {
                $skipped++;
                continue;
            }

            // Also check by title to avoid duplicates
            if ( $this->title_exists( $title ) ) {
                $skipped++;
                continue;
            }

            $authors    = isset( $book['authors'] ) && is_array( $book['authors'] ) ? array_map( 'sanitize_text_field', $book['authors'] ) : array();
            $author_str = implode( ', ', $authors );

            $categories = isset( $book['categories'] ) && is_array( $book['categories'] ) ? array_map( 'sanitize_text_field', $book['categories'] ) : array();
            $best_for   = implode( ', ', $categories );

            $description = isset( $book['description'] ) ? wp_strip_all_tags( (string) $book['description'] ) : '';
            $excerpt     = mb_substr( $description, 0, 300 );

            $post_id = wp_insert_post( array(
                'post_type'    => 'bookyol_book',
                'post_status'  => 'draft',
                'post_title'   => $title,
                'post_content' => $description,
                'post_excerpt' => $excerpt,
            ), true );

            if ( is_wp_error( $post_id ) || ! $post_id ) {
                $failed++;
                continue;
            }

            update_post_meta( $post_id, '_bookyol_book_author', $author_str );
            update_post_meta( $post_id, '_bookyol_isbn', $isbn );
            update_post_meta( $post_id, '_bookyol_pages', isset( $book['pageCount'] ) ? absint( $book['pageCount'] ) : 0 );
            update_post_meta( $post_id, '_bookyol_best_for', $best_for );

            if ( ! empty( $book['thumbnail'] ) ) {
                update_post_meta( $post_id, '_bookyol_cover_url', esc_url_raw( $book['thumbnail'] ) );
            }

            $generator = new BookYol_Link_Generator();
            $generator->generate_and_save( $post_id, $isbn, $title, $author_str );

            $imported++;
        }

        wp_send_json_success( array(
            'imported'  => $imported,
            'skipped'   => $skipped,
            'failed'    => $failed,
            'list_url'  => admin_url( 'edit.php?post_type=bookyol_book' ),
        ) );
    }

    private function isbn_exists( $isbn ) {
        $query = new WP_Query( array(
            'post_type'      => 'bookyol_book',
            'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => array(
                array(
                    'key'   => '_bookyol_isbn',
                    'value' => $isbn,
                ),
            ),
        ) );
        return $query->have_posts();
    }

    private function title_exists( $title ) {
        $query = new WP_Query( array(
            'post_type'      => 'bookyol_book',
            'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'title'          => $title,
        ) );
        return $query->have_posts();
    }
}
