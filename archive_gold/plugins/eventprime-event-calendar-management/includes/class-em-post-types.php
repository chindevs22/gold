<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class to create initial post type, taxonomy and status
 */
class EventM_Post_types {
	/**
	 * Init action
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
		if(!defined('EP_EM_EVENTS')) define('EP_EM_EVENTS', 555);
	}

	/**
	 * Register plugin's default taxonomies
	 */
	public static function register_taxonomies() {
		if ( taxonomy_exists( 'em_event_type' ) ) {
			return;
		}

		register_taxonomy(
			'em_event_type',
			'em_event',
			array(
				'hierarchical' => true,
				'label'        => __( 'Event Types', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'labels'       => array(
				'name'              => __( 'Event Types', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'singular_name'     => __( 'Event Type', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'search_items'      => __( 'Search Event Types', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'all_items'         => __( 'All Event Types', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'parent_item'       => __( 'Parent Event Type', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'parent_item_colon' => __( 'Parent Event Type:', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'edit_item'         => __( 'Edit Event Type', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'update_item'       => __( 'Update Event Type', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'add_new_item'      => __( 'Add New Event Type', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'new_item_name'     => __( 'New Event Type', __( 'Events', 'eventprime-event-calendar-management ' ) ),
			),
			'show_ui'      => false,
			'query_var'    => true,
			'capabilities' => array(
				'manage_terms' => 'manage_event_magic_terms',
				'edit_terms'   => 'edit_event_magic_terms',
				'delete_terms' => 'delete_event_magic_terms',
				'assign_terms' => 'assign_event_magic_terms',
			),
			'show_in_nav_menus' => false,
			'rewrite' => array( 'slug' => em_custom_type_page_url('types'), 'ep_mask' => EP_EM_EVENTS, 'with_front' => true ),
			)
		);

		register_taxonomy(
			'em_venue',
			'em_event',
			array(
				'hierarchical' => true,
				'label'        => __( 'Venues', __( 'Venues', 'eventprime-event-calendar-management' ) ),
				'labels'       => array(
				'name'              => __( 'Venues', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'singular_name'     => __( 'Venue', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'menu_name'         => __( 'Venue', 'Admin menu name', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'search_items'      => __( 'Search Venues', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'all_items'         => __( 'All Venues', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'parent_item'       => __( 'Parent Venue', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'parent_item_colon' => __( 'Parent Venue:', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'edit_item'         => __( 'Edit Venue', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'update_item'       => __( 'Update Venue', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'add_new_item'      => __( 'Add New Venue', __( 'Events', 'eventprime-event-calendar-management' ) ),
				'new_item_name'     => __( 'New Venue', __( 'Events', 'eventprime-event-calendar-management' ) )
			),
			'show_ui'      => false,
			'query_var'    => true,
			'rewrite' => array( 'slug' => em_custom_type_page_url('venues'), 'ep_mask' => EP_EM_EVENTS, 'with_front' => true ),
			'capabilities' => array(
				'manage_terms' => 'manage_event_magic_terms',
				'edit_terms'   => 'edit_event_magic_terms',
				'delete_terms' => 'delete_event_magic_terms',
				'assign_terms' => 'assign_event_magic_terms',
			),
			'show_in_nav_menus' => true,
			)
		);

		register_taxonomy('em_event_organizer','em_event',
			array(
	            'hierarchical' => true,
	            'label' => __('Event Organizers', __('Events', 'eventprime-event-calendar-management')),
	            'labels' => array(
	                'name' => __('Event Organizers', __('Events', 'eventprime-event-calendar-management')),
	                'singular_name' => __('Event Type', __('Events', 'eventprime-event-calendar-management')),
	                'search_items' => __('Search Event Organizers', __('Events', 'eventprime-event-calendar-management')),
	                'all_items' => __('All Event Organizers', __('Events', 'eventprime-event-calendar-management')),
	                'parent_item' => __('Parent Event Organizer', __('Events', 'eventprime-event-calendar-management')),
	                'parent_item_colon' => __('Parent Event Organizer:', __('Events', 'eventprime-event-calendar-management')),
	                'edit_item' => __('Edit Event Organizer', __('Events', 'eventprime-event-calendar-management')),
	                'update_item' => __('Update Event Organizer', __('Events', 'eventprime-event-calendar-management')),
	                'add_new_item' => __('Add New Event Organizer', __('Events', 'eventprime-event-calendar-management')),
	                'new_item_name' => __('New Event Organizer', __('Events', 'eventprime-event-calendar-management'))
	            ),
	            'show_ui' => false,
	            'query_var' => true,
	            'capabilities' => array(
	                'manage_terms' => 'manage_event_magic_terms',
	                'edit_terms' => 'edit_event_magic_terms',
	                'delete_terms' => 'delete_event_magic_terms',
	                'assign_terms' => 'assign_event_magic_terms',
	            ),
	            'show_in_nav_menus' => false,
	            'rewrite' => array( 'slug' => em_custom_type_page_url('organizer'), 'ep_mask' => EP_EM_EVENTS, 'with_front' => true ),
            )
        );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( post_type_exists( 'em_event' ) ) {
			return;
		}

		register_post_type('em_event', array(
			'labels' => array(
				'name' => __('Events', 'eventprime-event-calendar-management'),
				'singular_name' => __('Event', 'eventprime-event-calendar-management'),
				'add_new' => __('Add Event', 'eventprime-event-calendar-management'),
				'add_new_item' => __('Add New Event', 'eventprime-event-calendar-management'),
				'edit' => __('Edit', 'eventprime-event-calendar-management'),
				'edit_item' => __('Edit Event', 'eventprime-event-calendar-management'),
				'new_item' => __('New Event', 'eventprime-event-calendar-management'),
				'view' => __('View Event', 'eventprime-event-calendar-management'),
				'view_item' => __('View Event', 'eventprime-event-calendar-management'),
				'not_found' => __('No Events found', 'eventprime-event-calendar-management'),
				'not_found_in_trash' => __('No Events found in trash', 'eventprime-event-calendar-management'),
				'featured_image' => __('Event Image', 'eventprime-event-calendar-management'),
				'set_featured_image' => __('Set event image', 'eventprime-event-calendar-management'),
				'remove_featured_image' => __('Remove event image', 'eventprime-event-calendar-management'),
				'use_featured_image' => __('Use as event image', 'eventprime-event-calendar-management'),
			),
			'description' => __('Here you can add new events.', 'eventprime-event-calendar-management'),
			'public' => true,
			'show_ui' => false,
			'has_archive' => true,
			'map_meta_cap' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'hierarchical' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => em_custom_type_page_url('event'), 'ep_mask' => EP_EM_EVENTS, 'with_front' => true ),
			'capability_type' => 'post',
			'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes', 'publicize', 'wpcom-markdown', 'comments'),
			'show_in_nav_menus' => true
				)
		);


		register_post_type('em_ticket', array(
			'labels' => array(
				'name' => __('Tickets', 'eventprime-event-calendar-management'),
				'singular_name' => __('Ticket', 'eventprime-event-calendar-management'),
				'add_new' => __('Add Ticket', 'eventprime-event-calendar-management'),
				'add_new_item' => __('Add New Ticket', 'eventprime-event-calendar-management'),
				'edit' => __('Edit', 'eventprime-event-calendar-management'),
				'edit_item' => __('Edit Ticket', 'eventprime-event-calendar-management'),
				'new_item' => __('New Ticket', 'eventprime-event-calendar-management'),
				'view' => __('View Ticket', 'eventprime-event-calendar-management'),
				'view_item' => __('View Ticket', 'eventprime-event-calendar-management'),
				'not_found' => __('No Tickets found', 'eventprime-event-calendar-management'),
				'not_found_in_trash' => __('No Tickets found in trash', 'eventprime-event-calendar-management'),
				'featured_image' => __('Ticket Image', 'eventprime-event-calendar-management'),
				'set_featured_image' => __('Set ticket image', 'eventprime-event-calendar-management'),
				'remove_featured_image' => __('Remove ticket image', 'eventprime-event-calendar-management'),
				'use_featured_image' => __('Use as ticket image', 'eventprime-event-calendar-management'),
			),
			'description' => __('Here you can add new events.', 'eventprime-event-calendar-management'),
			'public' => true,
			'show_ui' => false,
			'map_meta_cap' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'hierarchical' => false, // Restrict WP to loads all records!
			'query_var' => true,
			'capability_type' => 'event_magic',
			'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes', 'publicize', 'wpcom-markdown'),
			'show_in_nav_menus' => false
				)
		);

		register_post_type('em_booking', array(
			'labels' => array(
				'name' => __('Bookings', 'eventprime-event-calendar-management'),
				'singular_name' => __('Booking', 'eventprime-event-calendar-management'),
				'add_new' => __('Add Booking', 'eventprime-event-calendar-management'),
				'add_new_item' => __('Add New Booking', 'eventprime-event-calendar-management'),
				'edit' => __('Edit', 'eventprime-event-calendar-management'),
				'edit_item' => __('Edit Booking', 'eventprime-event-calendar-management'),
				'new_item' => __('New Booking', 'eventprime-event-calendar-management'),
				'view' => __('View Booking', 'eventprime-event-calendar-management'),
				'view_item' => __('View Booking', 'eventprime-event-calendar-management'),
				'not_found' => __('No Booking found', 'eventprime-event-calendar-management'),
				'not_found_in_trash' => __('No Booking found in trash', 'eventprime-event-calendar-management'),
				'featured_image' => __('Booking Image', 'eventprime-event-calendar-management'),
				'set_featured_image' => __('Set Booking image', 'eventprime-event-calendar-management'),
				'remove_featured_image' => __('Remove Booking image', 'eventprime-event-calendar-management'),
				'use_featured_image' => __('Use as Booking image', 'eventprime-event-calendar-management'),
			),
			'description' => __('Here you can add new bookings.', 'eventprime-event-calendar-management'),
			'public' => true,
			'show_ui' => false,
			'map_meta_cap' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'hierarchical' => false, // Restrict WP to loads all records!
			'query_var' => true,
			'capability_type' => 'event_magic',
			'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes', 'publicize', 'wpcom-markdown'),
			'show_in_nav_menus' => false
				)
		);

		register_post_type('em_performer', array(
			'labels' => array(
				'name' => __('Performers', 'eventprime-event-calendar-management'),
				'singular_name' => __('Performer', 'eventprime-event-calendar-management'),
				'add_new' => __('Add Performer', 'eventprime-event-calendar-management'),
				'add_new_item' => __('Add New Performer', 'eventprime-event-calendar-management'),
				'edit' => __('Edit', 'eventprime-event-calendar-management'),
				'edit_item' => __('Edit Performer', 'eventprime-event-calendar-management'),
				'new_item' => __('New Performer', 'eventprime-event-calendar-management'),
				'view' => __('View Performer', 'eventprime-event-calendar-management'),
				'view_item' => __('View Performer', 'eventprime-event-calendar-management'),
				'not_found' => __('No Performer found', 'eventprime-event-calendar-management'),
				'not_found_in_trash' => __('No Performer found in trash', 'eventprime-event-calendar-management'),
				'featured_image' => __('Performer Image', 'eventprime-event-calendar-management'),
				'set_featured_image' => __('Set Performer image', 'eventprime-event-calendar-management'),
				'remove_featured_image' => __('Remove Performer image', 'eventprime-event-calendar-management'),
				'use_featured_image' => __('Use as Performer image', 'eventprime-event-calendar-management'),
			),
			'description' => __('Here you can add new rerformers.', 'eventprime-event-calendar-management'),
			'public' => true,
			'show_ui' => false,
			'map_meta_cap' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'rewrite' => array( 'slug' => em_custom_type_page_url('performer'), 'ep_mask' => EP_EM_EVENTS, 'with_front' => true ),
			'hierarchical' => false, // Restrict WP to loads all records!
			'query_var' => true,
			'capability_type' => 'event_magic',
			'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes', 'publicize', 'wpcom-markdown'),
			'show_in_nav_menus' => true
				)
		);
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	/**
	 * Register our custom post statuses, used for event status.
	 */
	public static function register_post_status() {
		register_post_status('emexpired', array(
			'label' => __('EM Expired', 'Event status', 'eventprime-event-calendar-management'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('EM Expired <span class="count">(%s)</span>', 'EM Expired <span class="count">(%s)</span>', 'eventprime-event-calendar-management')
		));

		register_post_status('expired', array(
			'label' => __('Expired', 'Event status', 'eventprime-event-calendar-management'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'eventprime-event-calendar-management')
		));

		register_post_status('cancelled', array(
			'label' => _x('Cancelled', 'Event status', 'eventprime-event-calendar-management'),
			'public' => false,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'eventprime-event-calendar-management')
		));

		register_post_status('pending', array(
			'label' => _x('Pending', 'Event status', 'eventprime-event-calendar-management'),
			'public' => false,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'eventprime-event-calendar-management')
		));

		register_post_status('refunded', array(
			'label' => _x('Refunded', 'Event status', 'eventprime-event-calendar-management'),
			'public' => false,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'eventprime-event-calendar-management')
		));
		
		register_post_status('completed', array(
			'label' => _x('Completed', 'Event status', 'eventprime-event-calendar-management'),
			'public' => false,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'eventprime-event-calendar-management')
		));
	}

}

EventM_Post_types::init();
