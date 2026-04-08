<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Meta_Boxes {

    private $platforms = array(
        'everand'   => 'Everand',
        'librofm'   => 'Libro.fm',
        'ebookscom' => 'Ebooks.com',
        'bookshop'  => 'Bookshop.org',
        'kobo'      => 'Kobo',
        'jamalon'   => 'Jamalon',
    );

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_bookyol_book', array( $this, 'save' ), 10, 2 );
    }

    public function add_meta_boxes() {
        add_meta_box(
            'bookyol_book_details',
            __( 'Book Details & Affiliate Links', 'bookyol' ),
            array( $this, 'render' ),
            'bookyol_book',
            'normal',
            'high'
        );
    }

    public function render( $post ) {
        wp_nonce_field( 'bookyol_save_meta', 'bookyol_meta_nonce' );
        ?>
        <div class="bookyol-lookup">
            <h4><?php esc_html_e( 'Quick Book Lookup', 'bookyol' ); ?></h4>
            <div class="bookyol-lookup__row">
                <input type="text" id="bookyol-lookup-query" placeholder="<?php esc_attr_e( 'Enter book title or ISBN…', 'bookyol' ); ?>" />
                <button type="button" id="bookyol-lookup-btn" class="button button-primary"><?php esc_html_e( 'Lookup', 'bookyol' ); ?></button>
            </div>
            <div id="bookyol-lookup-results" style="display:none;"></div>
            <div id="bookyol-lookup-status"></div>
        </div>
        <hr style="margin: 20px 0;">
        <?php

        $book_author = get_post_meta( $post->ID, '_bookyol_book_author', true );
        $rating      = get_post_meta( $post->ID, '_bookyol_rating', true );
        $cover_url   = get_post_meta( $post->ID, '_bookyol_cover_url', true );
        $isbn        = get_post_meta( $post->ID, '_bookyol_isbn', true );
        $pages       = get_post_meta( $post->ID, '_bookyol_pages', true );
        $best_for    = get_post_meta( $post->ID, '_bookyol_best_for', true );
        ?>
        <div class="bookyol-meta-section">
            <h4><?php esc_html_e( 'Book Details', 'bookyol' ); ?></h4>

            <div class="bookyol-meta-field">
                <label for="bookyol_book_author"><?php esc_html_e( 'Book Author', 'bookyol' ); ?></label>
                <input type="text" id="bookyol_book_author" name="_bookyol_book_author" value="<?php echo esc_attr( $book_author ); ?>" />
            </div>

            <div class="bookyol-meta-field">
                <label for="bookyol_rating"><?php esc_html_e( 'Rating (1-5)', 'bookyol' ); ?></label>
                <input type="number" id="bookyol_rating" name="_bookyol_rating" value="<?php echo esc_attr( $rating ); ?>" step="0.5" min="1" max="5" />
            </div>

            <div class="bookyol-meta-field">
                <label for="bookyol_cover_url"><?php esc_html_e( 'Cover Image', 'bookyol' ); ?></label>
                <input type="text" id="bookyol_cover_url" name="_bookyol_cover_url" value="<?php echo esc_attr( $cover_url ); ?>" />
                <button type="button" class="button" id="bookyol_cover_upload"><?php esc_html_e( 'Upload', 'bookyol' ); ?></button>
            </div>

            <div class="bookyol-meta-field">
                <label for="bookyol_isbn"><?php esc_html_e( 'ISBN', 'bookyol' ); ?></label>
                <input type="text" id="bookyol_isbn" name="_bookyol_isbn" value="<?php echo esc_attr( $isbn ); ?>" />
            </div>

            <div class="bookyol-meta-field">
                <label for="bookyol_pages"><?php esc_html_e( 'Page Count', 'bookyol' ); ?></label>
                <input type="number" id="bookyol_pages" name="_bookyol_pages" value="<?php echo esc_attr( $pages ); ?>" min="0" />
            </div>

            <div class="bookyol-meta-field">
                <label for="bookyol_best_for"><?php esc_html_e( 'Best For', 'bookyol' ); ?></label>
                <input type="text" id="bookyol_best_for" name="_bookyol_best_for" value="<?php echo esc_attr( $best_for ); ?>" placeholder="e.g. Entrepreneurs, Beginners" />
            </div>
        </div>

        <div class="bookyol-meta-section">
            <h4><?php esc_html_e( 'Affiliate Links', 'bookyol' ); ?></h4>
            <table class="bookyol-links-table">
                <thead>
                    <tr>
                        <th style="width:140px"><?php esc_html_e( 'Platform', 'bookyol' ); ?></th>
                        <th><?php esc_html_e( 'URL', 'bookyol' ); ?></th>
                        <th style="width:80px; text-align:center;"><?php esc_html_e( 'Status', 'bookyol' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $this->platforms as $slug => $name ) :
                    $meta_key = '_bookyol_link_' . $slug;
                    $value    = get_post_meta( $post->ID, $meta_key, true );
                    $active   = ! empty( $value );
                    ?>
                    <tr>
                        <td><label for="bookyol_link_<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></label></td>
                        <td><input type="url" id="bookyol_link_<?php echo esc_attr( $slug ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="https://" /></td>
                        <td style="text-align:center;">
                            <span class="bookyol-status-dot <?php echo $active ? 'bookyol-status-dot--active' : ''; ?>" aria-label="<?php echo $active ? esc_attr__( 'Active', 'bookyol' ) : esc_attr__( 'Empty', 'bookyol' ); ?>"></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function save( $post_id, $post ) {
        if ( ! isset( $_POST['bookyol_meta_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bookyol_meta_nonce'] ) ), 'bookyol_save_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $text_fields = array(
            '_bookyol_book_author',
            '_bookyol_isbn',
            '_bookyol_best_for',
        );
        foreach ( $text_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }

        if ( isset( $_POST['_bookyol_rating'] ) ) {
            $rating = floatval( wp_unslash( $_POST['_bookyol_rating'] ) );
            if ( $rating < 0 ) $rating = 0;
            if ( $rating > 5 ) $rating = 5;
            update_post_meta( $post_id, '_bookyol_rating', $rating );
        }

        if ( isset( $_POST['_bookyol_pages'] ) ) {
            update_post_meta( $post_id, '_bookyol_pages', absint( wp_unslash( $_POST['_bookyol_pages'] ) ) );
        }

        if ( isset( $_POST['_bookyol_cover_url'] ) ) {
            update_post_meta( $post_id, '_bookyol_cover_url', esc_url_raw( wp_unslash( $_POST['_bookyol_cover_url'] ) ) );
        }

        foreach ( array_keys( $this->platforms ) as $slug ) {
            $key = '_bookyol_link_' . $slug;
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, esc_url_raw( wp_unslash( $_POST[ $key ] ) ) );
            }
        }
    }
}
