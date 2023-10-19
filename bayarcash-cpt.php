<?php

defined( 'ABSPATH' ) || die();

// Register Custom Post Type
function create_bayarcash_account_cpt() {
    $labels = array(
        'name'                  => _x( 'Bayarcash Accounts For Gravity Forms', 'Post Type General Name', 'text_domain' ),
        'singular_name'         => _x( 'Bayarcash Account', 'Post Type Singular Name', 'text_domain' ),
        'menu_name'             => __( 'GF Bayarcash Accounts ', 'text_domain' ),
        'name_admin_bar'        => __( 'Bayarcash Account', 'text_domain' ),
        'archives'              => __( 'Bayarcash Account Archives', 'text_domain' ),
        'attributes'            => __( 'Bayarcash Account Attributes', 'text_domain' ),
        'parent_item_colon'     => __( 'Parent Bayarcash Account:', 'text_domain' ),
        'all_items'             => __( 'All Bayarcash Accounts', 'text_domain' ),
        'add_new_item'          => __( 'Add New Bayarcash Account', 'text_domain' ),
        'add_new'               => __( 'Add New', 'text_domain' ),
        'new_item'              => __( 'New Bayarcash Account', 'text_domain' ),
        'edit_item'             => __( 'Edit Bayarcash Account', 'text_domain' ),
        'update_item'           => __( 'Update Bayarcash Account', 'text_domain' ),
        'view_item'             => __( 'View Bayarcash Account', 'text_domain' ),
        'view_items'            => __( 'View Bayarcash Accounts', 'text_domain' ),
        'search_items'          => __( 'Search Bayarcash Account', 'text_domain' ),
        'not_found'             => __( 'Not found', 'text_domain' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
        'featured_image'        => __( 'Featured Image', 'text_domain' ),
        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
        'insert_into_item'      => __( 'Insert into Bayarcash Account', 'text_domain' ),
        'uploaded_to_this_item' => __( 'Uploaded to this Bayarcash Account', 'text_domain' ),
        'items_list'            => __( 'Bayarcash Accounts list', 'text_domain' ),
        'items_list_navigation' => __( 'Bayarcash Accounts list navigation', 'text_domain' ),
        'filter_items_list'     => __( 'Filter Bayarcash Accounts list', 'text_domain' ),
    );
    $args = array(
        'label'                 => __( 'Bayarcash Account', 'text_domain' ),
        'description'           => __( 'Bayarcash Account Description', 'text_domain' ),
        'labels'                => $labels,
        'supports'              => array( 'title' ),
        'taxonomies'            => array(),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'menu_icon'             => plugins_url("assets/logo.svg", __FILE__),
    );
    register_post_type( 'bayarcash_account', $args );
}
add_action( 'init', 'create_bayarcash_account_cpt', 0 );

// Add Custom Meta Box for Text Area Field

// Add Custom Meta Box for Text Area Fields
add_action( 'add_meta_boxes', 'add_bayarcash_account_metabox' );
function add_bayarcash_account_metabox() {
    add_meta_box(
        'bayarcash_account_text_area',
        __( 'BayarCash Form Settings', 'text_domain' ),
        'bayarcash_account_text_area_callback',
        'bayarcash_account'
    );
}

// Callback function for the custom meta box
function bayarcash_account_text_area_callback( $post ) {
    // Add your custom text area HTML here
    $pat_value = get_post_meta( $post->ID, '_pat_key', true );
    $postal_value = get_post_meta( $post->ID, '_postal_key', true );
    ?>
    <label for="pat_key"><?php _e( 'PAT Key:', 'text_domain' ); ?></label>
    <textarea id="pat_key" name="pat_key" style="width: 100%;" rows="5"><?php echo esc_textarea( $pat_value ); ?></textarea>

    <br>

    <label for="postal_key"><?php _e( 'Postal Key:', 'text_domain' ); ?></label>
    <input type="text" id="postal_key" name="postal_key" style="width: 100%;" value="<?php echo esc_attr( $postal_value ); ?>">

    <?php
}

// Save the custom text area values with trimmed spaces
add_action( 'save_post', 'save_bayarcash_account_custom_fields' );
function save_bayarcash_account_custom_fields( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['pat_key'] ) ) {
        update_post_meta( $post_id, '_pat_key', sanitize_text_field( trim( $_POST['pat_key'] ) ) );
    }

    if ( isset( $_POST['postal_key'] ) ) {
        update_post_meta( $post_id, '_postal_key', sanitize_text_field( trim( $_POST['postal_key'] ) ) );
    }
}
