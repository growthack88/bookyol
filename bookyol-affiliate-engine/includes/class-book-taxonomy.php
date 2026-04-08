<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Book_Taxonomy {

    const TAXONOMY = 'book_category';
    const VERSION  = '3.3.0';

    public function __construct() {
        add_action( 'init', array( $this, 'register' ), 5 );
        add_action( 'init', array( $this, 'maybe_create_terms' ), 20 );
    }

    public function register() {
        register_taxonomy( self::TAXONOMY, 'bookyol_book', array(
            'labels' => array(
                'name'              => __( 'Book Categories', 'bookyol' ),
                'singular_name'     => __( 'Book Category', 'bookyol' ),
                'search_items'      => __( 'Search Categories', 'bookyol' ),
                'all_items'         => __( 'All Categories', 'bookyol' ),
                'edit_item'         => __( 'Edit Category', 'bookyol' ),
                'update_item'       => __( 'Update Category', 'bookyol' ),
                'add_new_item'      => __( 'Add New Category', 'bookyol' ),
                'new_item_name'     => __( 'New Category Name', 'bookyol' ),
                'menu_name'         => __( 'Categories', 'bookyol' ),
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'books/category', 'with_front' => false ),
        ) );
    }

    /**
     * Runs on `init` after register(). Creates default terms if this version
     * hasn't seeded them yet, so updates bring new categories automatically.
     */
    public function maybe_create_terms() {
        $stored = get_option( 'bookyol_taxonomy_version', '0' );
        if ( version_compare( $stored, self::VERSION, '<' ) ) {
            self::create_default_terms();
            update_option( 'bookyol_taxonomy_version', self::VERSION );
        }
    }

    public static function create_default_terms() {
        if ( ! taxonomy_exists( self::TAXONOMY ) ) {
            return;
        }

        $defaults = array(
            'Business'      => 'business',
            'Psychology'    => 'psychology',
            'Self-Help'     => 'self-help',
            'Productivity'  => 'productivity',
            'Marketing'     => 'marketing',
            'Finance'       => 'finance',
            'Leadership'    => 'leadership',
            'Biographies'   => 'biographies',
            'Science'       => 'science',
            'Philosophy'    => 'philosophy',
            'History'       => 'history',
            'Creativity'    => 'creativity',
            'Fiction'       => 'fiction',
            'Thriller'      => 'thriller',
            'Sci-Fi'        => 'sci-fi',
            'Romance'       => 'romance',
            'Classic'       => 'classic',
            'Fantasy'       => 'fantasy',
            'Young Adult'   => 'young-adult',
            'Memoir'        => 'memoir',
            'Health'        => 'health',
            'Technology'    => 'technology',
            'Education'     => 'education',
            'Parenting'     => 'parenting',
            'Spirituality'  => 'spirituality',
            'Communication' => 'communication',
            'Economics'     => 'economics',
            'Design'        => 'design',
            'Humor'         => 'humor',
            'Poetry'        => 'poetry',
        );

        foreach ( $defaults as $name => $slug ) {
            if ( ! term_exists( $name, self::TAXONOMY ) ) {
                wp_insert_term( $name, self::TAXONOMY, array( 'slug' => $slug ) );
            }
        }
    }
}
