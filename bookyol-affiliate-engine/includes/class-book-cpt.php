<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookYol_Book_CPT {

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );
    }

    public function register() {
        $labels = array(
            'name'                  => __( 'Books', 'bookyol' ),
            'singular_name'         => __( 'Book', 'bookyol' ),
            'menu_name'             => __( 'Books', 'bookyol' ),
            'name_admin_bar'        => __( 'Book', 'bookyol' ),
            'add_new'               => __( 'Add New', 'bookyol' ),
            'add_new_item'          => __( 'Add New Book', 'bookyol' ),
            'new_item'              => __( 'New Book', 'bookyol' ),
            'edit_item'             => __( 'Edit Book', 'bookyol' ),
            'view_item'             => __( 'View Book', 'bookyol' ),
            'all_items'             => __( 'All Books', 'bookyol' ),
            'search_items'          => __( 'Search Books', 'bookyol' ),
            'not_found'             => __( 'No books found.', 'bookyol' ),
            'not_found_in_trash'    => __( 'No books found in Trash.', 'bookyol' ),
            'featured_image'        => __( 'Cover Image', 'bookyol' ),
            'set_featured_image'    => __( 'Set cover image', 'bookyol' ),
            'remove_featured_image' => __( 'Remove cover image', 'bookyol' ),
            'use_featured_image'    => __( 'Use as cover image', 'bookyol' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'books', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => 'books',
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-book-alt',
            'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'bookyol_book', $args );
    }
}
