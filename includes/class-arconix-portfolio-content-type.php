<?php
/**
 * Defines and handles the Portfolio Post Type and Feature Taxonomy registration
 *
 * @since   1.4.0
 */
class Arconix_Portfolio_Content_Type {

    /**
     * Initialize the class and set its properties.
     *
     * @since   1.2.0
     * @version 1.4.0
     * @access  public
     */
    public function __construct() {
        register_activation_hook( __FILE__,         array( $this, 'activation' ) );
        register_deactivation_hook( __FILE__,       array( $this, 'deactivation' ) );

        add_action( 'init',                         array( $this, 'content_types' ) );
        add_action( 'dashboard_glance_items',       array( $this, 'at_a_glance' ) );

        add_filter( 'post_updated_messages',        array( $this, 'updated_messages' ) );
    }

    /**
     * Runs on Plugin Activation
     * Registers our Post Type and Taxonomy
     *
     * @since  1.2.0
     */
    public function activation() {
        $this->content_types();
        flush_rewrite_rules();

        // Get the administrator role
        $role = get_role( 'administrator' );

        // If the administrator role exists, add required capabilities for the plugin.
        if ( !empty( $role ) ) {
                $role->add_cap( 'manage_portfolio' );
                $role->add_cap( 'create_portfolio_items' );
                $role->add_cap( 'edit_portfolio_items' );
        }
    }

    /**
     * Runs on Plugin Deactivation
     *
     * @since  1.2.0
     */
    public function deactivation() {
        flush_rewrite_rules();
    }


    /**
     * Register the post_type and taxonomy
     *
     * @since 1.2.0
     */
    public function content_types() {
        $defaults = $this->defaults();
        register_post_type( $defaults['post_type']['slug'], $defaults['post_type']['args'] );
        register_taxonomy( $defaults['taxonomy']['slug'], $defaults['post_type']['slug'],  $defaults['taxonomy']['args'] );
    }

    /**
     * Define the defaults used in the registration of the post type and taxonomy
     *
     * @return  array $defaults
     *
     * @since   1.2.0
     * @version 1.4.0
     */
    public function defaults() {
        // Establishes plugin registration defaults for post type and taxonomy
        $defaults = array(
            'post_type' => array(
                'slug' => 'portfolio',
                'args' => array(
                    'labels' => array(
                        'name'                  => __( 'Portfolio',                             'arconix-portfolio' ),
                        'singular_name'         => __( 'Portfolio',                             'arconix-portfolio' ),
                        'add_new'               => __( 'Add New',                               'arconix-portfolio' ),
                        'add_new_item'          => __( 'Add New Portfolio Item',                'arconix-portfolio' ),
                        'edit'                  => __( 'Edit',                                  'arconix-portfolio' ),
                        'edit_item'             => __( 'Edit Portfolio Item',                   'arconix-portfolio' ),
                        'new_item'              => __( 'New Item',                              'arconix-portfolio' ),
                        'view'                  => __( 'View Portfolio',                        'arconix-portfolio' ),
                        'view_item'             => __( 'View Portfolio Item',                   'arconix-portfolio' ),
                        'search_items'          => __( 'Search Portfolio',                      'arconix-portfolio' ),
                        'not_found'             => __( 'No portfolio items found',              'arconix-portfolio' ),
                        'not_found_in_trash'    => __( 'No portfolio items found in Trash',     'arconix-portfolio' )
                    ),
                    'public'            => true,
                    'query_var'         => true,
                    'menu_position'     => 20,
                    'menu_icon'         => 'dashicons-portfolio',
                    'has_archive'       => false,
                    'supports'          => array( 'title', 'editor', 'thumbnail' ),
                    'rewrite'           => array( 'slug' => 'portfolio', 'with_front' => false ),
                    'capability_type'   => 'portfolio',
                    'map_meta_cap'      => true,
                    'capabilities'      => array(
                        // meta caps (don't assign these to roles)
                        'edit_post'                 => 'edit_portfolio_item',
			'read_post'                 => 'read_portfolio_item',
			'delete_post'               => 'delete_portfolio_item',

			// primitive/meta caps
			'create_posts'              => 'create_portfolio_items',

			// primitive caps used outside of map_meta_cap()
			'edit_posts'                => 'edit_portfolio_items',
			'edit_others_posts'         => 'manage_portfolio',
			'publish_posts'             => 'manage_portfolio',
			'read_private_posts'        => 'read',

			// primitive caps used inside of map_meta_cap()
			'read'                      => 'read',
			'delete_posts'              => 'manage_portfolio',
			'delete_private_posts'      => 'manage_portfolio',
			'delete_published_posts'    => 'manage_portfolio',
			'delete_others_posts'       => 'manage_portfolio',
			'edit_private_posts'        => 'edit_portfolio_items',
			'edit_published_posts'      => 'edit_portfolio_items'
                    )
                )
            ),
            'taxonomy' => array(
                'slug' => 'feature',
                'args' => array(
                    'labels' => array(
                        'name'                          => __( 'Features',                              'arconix-portfolio' ),
                        'singular_name'                 => __( 'Feature',                               'arconix-portfolio' ),
                        'search_items'                  => __( 'Search Features',                       'arconix-portfolio' ),
                        'popular_items'                 => __( 'Popular Features',                      'arconix-portfolio' ),
                        'all_items'                     => __( 'All Features',                          'arconix-portfolio' ),
                        'parent_item'                   => null,
                        'parent_item_colon'             => null,
                        'edit_item'                     => __( 'Edit Feature' ,                         'arconix-portfolio' ),
                        'update_item'                   => __( 'Update Feature',                        'arconix-portfolio' ),
                        'add_new_item'                  => __( 'Add New Feature',                       'arconix-portfolio' ),
                        'new_item_name'                 => __( 'New Feature Name',                      'arconix-portfolio' ),
                        'separate_items_with_commas'    => __( 'Separate features with commas',         'arconix-portfolio' ),
                        'add_or_remove_items'           => __( 'Add or remove features',                'arconix-portfolio' ),
                        'choose_from_most_used'         => __( 'Choose from the most used features',    'arconix-portfolio' ),
                        'menu_name'                     => __( 'Features',                              'arconix-portfolio' ),
                    ),
                    'hierarchical'              => false,
                    'show_ui'                   => true,
                    'show_admin_column'         => true,
                    'update_count_callback'     => '_update_post_term_count',
                    'query_var'                 => true,
                    'rewrite'                   => array( 'slug' => 'feature' )
                )
            )
        );

        return apply_filters( 'arconix_portfolio_defaults', $defaults );
    }

    /**
     * Correct messages when Portfolio post type is saved
     *
     * @since   0.9.0
     * @version 1.4.0
     * @global  stdObject    $post              WP Post object
     * @global  int          $post_ID           Post ID
     * @param   array        $messages          Existing array of messages
     * @return  array                           updated messages
     */
    public function updated_messages( $messages ) {
        global $post, $post_ID;
        $post_type = get_post_type( $post_ID );

        $obj = get_post_type_object( $post_type );
        $singular = $obj->labels->singular_name;

        $messages[$post_type] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf( __( $singular . ' updated. <a href="%s">View ' . strtolower( $singular ) . '</a>', 'arconix-portfolio' ), esc_url( get_permalink( $post_ID ) ) ),
            2  => __( 'Custom field updated.', 'arconix-portfolio' ),
            3  => __( 'Custom field deleted.', 'arconix-portfolio' ),
            4  => __( $singular . ' updated.', 'arconix-portfolio' ),
            5  => isset( $_GET['revision'] ) ? sprintf( __( $singular . ' restored to revision from %s', 'arconix-portfolio' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( __( $singular . ' published. <a href="%s">View ' . strtolower( $singular ) . '</a>', 'arconix-portfolio' ), esc_url( get_permalink( $post_ID ) ) ),
            7  => __( 'Page saved.' ),
            8  => sprintf( __( $singular . ' submitted. <a target="_blank" href="%s">Preview ' . strtolower( $singular ) . '</a>', 'arconix-portfolio' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
            9  => sprintf( __( $singular . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . strtolower( $singular ) . '</a>', 'arconix-portfolio' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
            10 => sprintf( __( $singular . ' draft updated. <a target="_blank" href="%s">Preview ' . strtolower( $singular ) . '</a>', 'arconix-portfolio' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
        );

        return $messages;
    }

    /**
     * Add the Portfolio post type and Feature taxonomy to the WP 3.8 "At a Glance" dashboard
     *
     * @since 1.4.0
     */
    public function at_a_glance() {
        $glancer = new Gamajo_Dashboard_Glancer;
        $glancer->add( 'portfolio' );
    }

}