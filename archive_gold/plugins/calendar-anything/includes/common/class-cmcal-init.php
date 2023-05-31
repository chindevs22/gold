<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Init {

    public function __construct() {

        // Add events
        add_action('init', array($this, 'register_settings'));
    }

    function register_settings() {
        $options = CMCAL()->setup_options;
        if (!empty($options)) {
            $post_types = $options["general_options"]["post_types"];
            if (in_array(CMCAL()->default_event_post_type, $post_types)) {
                $this->register_default_event_post_type();
                $this->register_default_event_taxonomy_category();
            }
            $this->register_calendar($post_types);
        }
    }

    function register_default_event_post_type() {

        // Create post type
        register_post_type(CMCAL()->default_event_post_type, array(
            'labels' => array(
                'name' => esc_html__('Calendar Events', 'calendar-anything'),
                'singular_name' => esc_html__('Event', 'calendar-anything'),
                'add_new' => esc_html__('New Event', 'calendar-anything'),
                'add_new_item' => esc_html__('Add New Event', 'calendar-anything'),
                'edit_item' => esc_html__('Edit Event', 'calendar-anything'),
                'new_item' => esc_html__('New Event', 'calendar-anything'),
                'view_item' => esc_html__('View Event', 'calendar-anything'),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'cmcal_events'),
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions'),
            'menu_position' => 7, // after calendar setup
            'menu_icon' => 'dashicons-calendar',
                )
        );
    }

    function register_default_event_taxonomy_category() {

        // Create taxonomies
        $labels = array(
            'name' => esc_html__('Event Categories', 'calendar-anything'),
            'singular_name' => esc_html__('Event Category', 'calendar-anything'),
            'search_items' => esc_html__('Search Event Categories', 'calendar-anything'),
            'all_items' => esc_html__('All Event Categories', 'calendar-anything'),
            'parent_item' => esc_html__('Parent Event Category', 'calendar-anything'),
            'parent_item_colon' => esc_html__('Parent Event Category:', 'calendar-anything'),
            'edit_item' => esc_html__('Edit Event Category', 'calendar-anything'),
            'update_item' => esc_html__('Update Event Category', 'calendar-anything'),
            'add_new_item' => esc_html__('Add New Event Category', 'calendar-anything'),
            'new_item_name' => esc_html__('New Event Category Name', 'calendar-anything'),
            'menu_name' => esc_html__('Event Categories', 'calendar-anything'),
        );

        register_taxonomy(CMCAL()->default_event_taxonomy_category, array(CMCAL()->default_event_post_type), array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true, 'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'cmcal-event-category'),
            'show_in_quick_edit' => true,
            'show_in_rest' => true,
        ));
    }

    function register_calendar($post_types) {

        // Create taxonomies
        $labels_cmcal_calendar = array(
            'name' => esc_html__('Calendars', 'calendar-anything'),
            'singular_name' => esc_html__('Calendar', 'calendar-anything'),
            'search_items' => esc_html__('Search Calendars', 'calendar-anything'),
            'all_items' => esc_html__('All Calendars', 'calendar-anything'),
            'edit_item' => esc_html__('Edit Calendar', 'calendar-anything'),
            'update_item' => esc_html__('Update Calendar', 'calendar-anything'),
            'add_new_item' => esc_html__('Add New Calendar', 'calendar-anything'),
            'new_item_name' => esc_html__('New Calendar Name', 'calendar-anything'),
            'menu_name' => esc_html__('Calendars', 'calendar-anything'),
        );

        register_taxonomy(CMCAL()->event_calendar, $post_types, array(
            'hierarchical' => true,
            'labels' => $labels_cmcal_calendar,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'cmcal-calendar'),
            'show_admin_column' => true,
            'show_in_quick_edit' => true,
            'show_in_rest' => true,
        ));
    }

}
