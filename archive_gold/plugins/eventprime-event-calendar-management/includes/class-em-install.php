<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugin installation class
 */
class EventM_Install {
	/**
	 * Install on plugin activation
	 * 
	 * @param Network Wide $network_wide Multi Network.
	 */
	public static function install( $network_wide ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) {
			foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $blog_id ) {
				switch_to_blog( $blog_id );
				self::create_pages();
				self::default_settings();
				self::em_create_table();
				restore_current_blog();
				// set custom capabilities
				self::em_add_custom_capabilities();
			}
		} else {
			self::create_pages();
			self::default_settings();
			self::em_create_table();
			// set custom capabilities
			self::em_add_custom_capabilities();
		}
		add_option( 'event_magic_do_activation_redirect', true );
	}

	/**
	 * Method for create default pages
	 */
	private static function create_pages() {
		global $wpdb;
		$global_options = get_option( EM_GLOBAL_SETTINGS );

		$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_performers]%' AND POST_TYPE IN ('page','post')";
		$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
			$global_options['performers_page'] = $page_id;
		}else {
			// Creating performers page.
			$args = array(
				'post_content' => "[em_performers]",
				'post_title'   => 'Performers',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id = wp_insert_post( $args );
			if ( ! is_wp_error( $page_id ) ) {
				$global_options['performers_page'] = $page_id;
			}
		}

		$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_sites]%' AND POST_TYPE IN ('page','post')";
		$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
			$global_options['venues_page'] = $page_id;
		}else {
			// Creating Venues page.
			$args = array(
				'post_content' => "[em_sites]",
				'post_title'   => 'Event Sites & Locations',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id = wp_insert_post( $args );
			if ( ! is_wp_error( $page_id ) ) {
				$global_options['venues_page'] = $page_id;
			}
		}

		$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_events%' AND POST_TYPE IN ('page','post')";
		$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
			$global_options['events_page'] = $page_id;
		}else {
			// Creating Events page.
			$args = array(
				'post_content' => "[em_events]",
				'post_title'   => 'Events',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id = wp_insert_post( $args );
			if ( ! is_wp_error( $page_id ) ) {
				$global_options['events_page'] = $page_id;
			}
		}

		$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_booking]%' AND POST_TYPE IN ('page','post')";
		$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
			$global_options['booking_page'] = $page_id;
		}else {
			// Creating Events page.
			$args = array(
				'post_content' => '[em_booking]',
				'post_title'   => 'Booking',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id = wp_insert_post( $args );
			if ( ! is_wp_error( $page_id ) ) {
				$global_options['booking_page'] = $page_id;
			}
		}

		$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_profile]%' AND POST_TYPE IN ('page','post')";
		$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
			$global_options['profile_page'] = $page_id;
		}else {
			// Creating Events page.
			$args = array(
				'post_content' => "[em_profile]",
				'post_title'   => 'User Profile',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id = wp_insert_post( $args );
			if ( ! is_wp_error( $page_id ) ) {
				$global_options['profile_page'] = $page_id;
			}
		}

		$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_event_types]%' AND POST_TYPE IN ('page','post')";
		$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
			$global_options['event_types'] = $page_id;
		}else {
			// Creating Events page.
			$args = array(
				'post_content' => "[em_event_types]",
				'post_title'   => 'Event Types',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id = wp_insert_post( $args );
			if ( ! is_wp_error( $page_id ) ) {
				$global_options['event_types'] = $page_id;
			}
		}

		$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_event_submit_form]%' AND POST_TYPE IN ('page','post')";
		$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
			$global_options['event_submit_form'] = $page_id;
		}else {
			// Creating Events page.
			$args = array(
				'post_content' => "[em_event_submit_form]",
				'post_title'   => 'Submit Event',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id = wp_insert_post( $args );
			if ( ! is_wp_error( $page_id ) ) {
				$global_options['event_submit_form'] = $page_id;
			}
		}

		$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_booking_details]%' AND POST_TYPE IN ('page','post')";
		$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
			$global_options['booking_details_page'] = $page_id;
		}else {
			// Creating Booking Detail page.
			$args = array(
				'post_content' => "[em_booking_details]",
				'post_title'   => 'Booking Details',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id = wp_insert_post( $args );
			if ( ! is_wp_error( $page_id ) ) {
				$global_options['booking_details_page'] = $page_id;
			}
		}

		// Update settings.
		update_option( EM_GLOBAL_SETTINGS, $global_options );
	}

	/**
	 * Default set Global Settings
	 */
	private static function default_settings() {
		global $wp_rewrite;
		self::default_notifications();
		$instance = event_magic_instance();

		$global_options = get_option( EM_GLOBAL_SETTINGS );

		if ( ! isset( $global_options['payment_test_mode'] ) ) {
			$global_options['payment_test_mode'] = 1;
		}

		if ( ! isset( $global_options['currency'] ) ) {
			$global_options['currency'] = EM_DEFAULT_CURRENCY;
		}

		if ( ! isset( $global_options['event_tour'] ) ) {
			$global_options['event_tour'] = 0;
		}
		if ( ! isset( $global_options['is_visit_welcome_page'] ) ) {
			$global_options['is_visit_welcome_page'] = 0;
		}
		if ( ! isset( $global_options['dashboard_hide_past_events'] ) ) {
			$global_options['dashboard_hide_past_events'] = 0;
		}
		if ( ! isset( $global_options['disable_filter_options'] ) ) {
			$global_options['disable_filter_options'] = 0;
		}
		// Update settings.
		update_option( EM_GLOBAL_SETTINGS, $global_options );
		$wp_rewrite->flush_rules();

		// Update DB version.
		update_option( EM_DB_VERSION, $instance->version );
	}

	/**
	 * Default email notification
	 */
	private static function default_notifications() {
		$global_options = get_option( EM_GLOBAL_SETTINGS );

		$booking_pending_email = em_global_settings( 'booking_pending_email' );
		if ( empty( $booking_pending_email ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/pending.html';
			$global_options['booking_pending_email'] = ob_get_clean();
		}

		$booking_confirmed_email = em_global_settings( 'booking_confirmed_email' );
		if ( empty( $booking_confirmed_email ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/customer.html';
			$global_options['booking_confirmed_email'] = ob_get_clean();
		}

		$booking_cancelation_email = em_global_settings( 'booking_cancelation_email' );
		if ( empty( $booking_cancelation_email ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/cancellation.html';
			$global_options['booking_cancelation_email'] = ob_get_clean();
		}

		$reset_password_mail = em_global_settings( 'reset_password_mail' );
		if ( empty( $reset_password_mail ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/reset_user_password.html';
			$global_options['reset_password_mail'] = ob_get_clean();
		}

		$registration_email_content = em_global_settings( 'registration_email_content' );
		if ( empty( $registration_email_content ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/registration.html';
			$global_options['registration_email_content'] = ob_get_clean();
		}

		$booking_refund_email = em_global_settings( 'booking_refund_email' );
		if ( empty( $booking_refund_email ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/refund.html';
			$global_options['booking_refund_email'] = ob_get_clean();
		}

		$event_submitted_email = em_global_settings( 'event_submitted_email' );
		if ( empty( $event_submitted_email ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/event_submitted.html';
			$global_options['event_submitted_email'] = ob_get_clean();
		}

		$event_approved_email = em_global_settings( 'event_approved_email' );
		if ( empty( $event_approved_email ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/event_approved.html';
			$global_options['event_approved_email'] = ob_get_clean();
		}

		$admin_booking_confirmed_email = em_global_settings( 'admin_booking_confirmed_email' );
		if ( empty( $admin_booking_confirmed_email ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/admin_confirm.html';
			$global_options['admin_booking_confirmed_email'] = ob_get_clean();
		}
        
        $payment_confirmed_email = em_global_settings( 'payment_confirmed_email' );
		if ( empty( $payment_confirmed_email ) ) {
			ob_start();
			include EM_BASE_DIR . 'includes/mail/payment_confirmed.html';
			$global_options['payment_confirmed_email'] = ob_get_clean();
		}

		// Update settings.
		update_option( EM_GLOBAL_SETTINGS, $global_options );
	}

	/**
	 * Hook on update process
	 *
	 * @param Upgrader Object $upgrader_object Upgrader object.
	 *
	 * @param Options $options Option data.
	 */
	public static function event_magic_upgrader_process_complete( $upgrader_object, $options ) {
		$our_plugin = plugin_basename( __FILE__ );
		// If an update has taken place and the updated type is plugins and the plugins element exists.
		if ( 'update' === $options['action'] && 'plugin' === $options['type'] && isset( $options['plugins'] ) ) {
			// Iterate through the plugins being updated and check if ours is there.
			foreach ( $options['plugins'] as $plugin ) {
				if ( $plugin == $our_plugin ) {
					wp_redirect( admin_url( 'admin.php?page=event_magic' ) );
				}
			}
		}
	}

	public static function em_check_updated_data() {
        self::em_create_table();
        self::em_create_new_pages();
        self::em_check_organizer_data();
		self::em_update_notification_data();
		self::em_update_settings_value();
		self::em_add_custom_capabilities();
    }
	
	private static function em_create_table() {
        global $wpdb;
		if( version_compare( get_bloginfo('version'), '6.1')  < 0 ){
            require_once( ABSPATH . 'wp-includes/wp-db.php' );
		} else{
            require_once( ABSPATH . 'wp-includes/class-wpdb.php' );
		}
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix.'em_price_options';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `event_id` bigint(20) NOT NULL,
            `name` varchar(255) DEFAULT NULL,
            `description` longtext DEFAULT NULL,
            `start_date` datetime DEFAULT NULL,
            `end_date` datetime DEFAULT NULL,
            `price` varchar(50) DEFAULT NULL,
            `special_price` varchar(50) DEFAULT NULL,
            `capacity` integer(11) DEFAULT NULL,
            `is_default` tinyint(2) DEFAULT 0 NOT NULL,
            `is_event_price` tinyint(2) DEFAULT 0 NOT NULL,
            `icon` longtext DEFAULT NULL,
            `priority` integer(11) DEFAULT NULL,
            `capacity_progress_bar` tinyint(2) DEFAULT 0 NOT NULL,
            `status` tinyint(2) DEFAULT 1 NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
			`variation_color` varchar(20) DEFAULT NULL,
			`seat_data` longtext DEFAULT NULL,
            PRIMARY KEY (`id`)
            )$charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        dbDelta($sql);

        // add variation color column in variation table
        $db_name = $wpdb->dbname;
        $column_name = 'variation_color';
        $ep_set_variation_price = $wpdb->get_results($wpdb->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",$db_name, $table_name, $column_name ));
		if ( empty( $ep_set_variation_price ) ) {
			$add_color_column = "ALTER TABLE `{$table_name}` ADD `variation_color` VARCHAR(20) NULL DEFAULT NULL ";
    		$wpdb->query( $add_color_column );
    	}
    	$column_name = 'seat_data';
    	$add_seat_data_column = $wpdb->get_results($wpdb->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",$db_name, $table_name, $column_name ));
    	if ( empty( $add_seat_data_column ) ) {
    		$add_seat_data_column = "ALTER TABLE `{$table_name}` ADD `seat_data` Longtext NULL DEFAULT NULL ";
    		$wpdb->query( $add_seat_data_column );
		}
		$column_name = 'parent_price_option_id';
    	$add_parent_price_option_id = $wpdb->get_results($wpdb->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",$db_name, $table_name, $column_name ));
    	if ( empty( $add_parent_price_option_id ) ) {
    		$add_parent_price_option_id = "ALTER TABLE `{$table_name}` ADD `parent_price_option_id` integer(11) DEFAULT 0 NOT NULL ";
    		$wpdb->query( $add_parent_price_option_id );
		}
    }

    private static function em_create_new_pages() {
    	global $wpdb;
		$global_options = get_option( EM_GLOBAL_SETTINGS );
    	$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_booking_details]%' AND POST_TYPE IN ('page','post')";
		$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
			$global_options['booking_details_page'] = $page_id;
		}else {
			// Creating Booking Detail page.
			$args = array(
				'post_content' => "[em_booking_details]",
				'post_title'   => 'Booking Details',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			$page_id = wp_insert_post( $args );
			if ( ! is_wp_error( $page_id ) ) {
				$global_options['booking_details_page'] = $page_id;
			}
		}

		// organizer page
		$sql = "SELECT ID FROM $wpdb->posts WHERE POST_CONTENT like '%[em_event_organizers]%' AND POST_TYPE IN ('page','post')";
    	$page_id = $wpdb->get_var( $sql );
		if ( ! empty( $page_id ) ) {
        	$global_options['event_organizers'] = $page_id;
        } else {
        	// Creating Events Organizers page
        	$args = array(
        		'post_content' => '[em_event_organizers]',
            	'post_title'   => 'Event Organizers',
            	'post_status'  => 'publish',
            	'post_type'    => 'page',
            );
        	$page_id = wp_insert_post( $args );
        	if ( ! is_wp_error( $page_id ) ) {
            	$global_options['event_organizers'] = $page_id;
            }
        }

		// Update settings.
		update_option( EM_GLOBAL_SETTINGS, $global_options );
    }

    private static function em_check_organizer_data() {
    	// check for event data & organizer data
    	$event_post_args = array( 
        	'numberposts' => -1, // -1 is for all
        	'post_status' => 'any',
        	'post_type'	  => EM_EVENT_POST_TYPE,
        	'order' 	  => 'ASC', // or 'DESC'
        	'meta_query'  => array(
            	array(
                	'key'     => em_append_meta_key('organizer_name'),
                	'value'   => '',
                	'compare' => '!=',
            	)
        	),
     	);

    	$all_event_posts = get_posts($event_post_args);

    	$all_organizers_terms = get_terms(
    		array(
        		'taxonomy'   => EM_EVENT_ORGANIZER_TAX,
        		'hide_empty' => false,
    		)
    	);

    	if ( ! empty( $all_event_posts ) && empty( $all_organizers_terms ) ){
       		$event_service     = EventM_Factory::get_service( 'EventM_Service' );
       		$organizer_service = EventM_Factory::get_service( 'EventOrganizerM_Service' );
       		$organizer         = $organizer_service->load_edit_page();
    		foreach( $all_event_posts as $single_event_post ) {
        		$organizer->name = em_get_post_meta( $single_event_post->ID, 'organizer_name', true );
        		$organizer->organizer_phones = em_get_post_meta( $single_event_post->ID, 'organizer_phones', true );
        		$organizer->organizer_emails = em_get_post_meta( $single_event_post->ID, 'organizer_emails', true );
        		$organizer->organizer_websites = em_get_post_meta( $single_event_post->ID, 'organizer_websites', true );
        		$save_response = $organizer_service->save( $organizer );
        		if ( ! empty( $save_response ) ) { 
            		$term_id[0]  = $save_response->id;
            		$new_term_id = serialize($term_id);
            		em_update_post_meta( $single_event_post->ID, 'organizer', $new_term_id );
        		}
        	}
        }
    }

	private static function em_update_notification_data(){
		$global_options = get_option( EM_GLOBAL_SETTINGS );
		if(EVENTPRIME_VERSION <= '2.4.0'){
			$registration_email_content = em_global_settings( 'registration_email_content' );
			if ( !empty( $registration_email_content ) ) {
				ob_start();
				include EM_BASE_DIR . 'includes/mail/registration.html';
				$global_options['registration_email_content'] = ob_get_clean();
			}
		}
		if(EVENTPRIME_VERSION <= '2.7.0'){
			$booking_confirmed_email = em_global_settings( 'booking_confirmed_email' );
			$key = 'em_booking_confirmed_email_'.EVENTPRIME_VERSION;
			$check_if_key_exists = get_option($key);
			if ( !empty( $booking_confirmed_email ) && $check_if_key_exists != 1 ) {
				ob_start();
				include EM_BASE_DIR . 'includes/mail/customer.html';
				$global_options['booking_confirmed_email'] = ob_get_clean();
				add_option( $key , 1 );
			}
		}
		if(EVENTPRIME_VERSION < '2.7.0'){
			$admin_booking_confirmed_email = em_global_settings( 'admin_booking_confirmed_email' );
			if ( !empty( $admin_booking_confirmed_email ) ) {
				ob_start();
				include EM_BASE_DIR . 'includes/mail/admin_confirm.html';
				$global_options['admin_booking_confirmed_email'] = ob_get_clean();
			}
		}
        if(EVENTPRIME_VERSION <= '2.7.0'){
			$payment_confirmed_email = em_global_settings( 'payment_confirmed_email' );
			$key = 'em_payment_confirmed_email_'.EVENTPRIME_VERSION;
			$check_if_key_exists = get_option($key);
			if ( !empty( $payment_confirmed_email ) && $check_if_key_exists != 1 ) {
				ob_start();
				include EM_BASE_DIR . 'includes/mail/payment_confirmed.html';
				$global_options['payment_confirmed_email'] = ob_get_clean();
				add_option( $key , 1 );
			}
		}
       
		// Update settings.
		update_option( EM_GLOBAL_SETTINGS, $global_options );
	}

	private static function em_update_settings_value(){
		$global_options = get_option( EM_GLOBAL_SETTINGS );
		if ( ! isset( $global_options['show_qr_code_on_single_event'] ) ) {
			$global_options['show_qr_code_on_single_event'] = 1;
		}
		if ( ! isset( $global_options['show_qr_code_on_ticket'] ) ) {
			$global_options['show_qr_code_on_ticket'] = 1;
		}
       
		// Update settings.
		update_option( EM_GLOBAL_SETTINGS, $global_options );
	}

	/**
	 * Add custom user capabilities
	 */
	private static function em_add_custom_capabilities() {
		if( empty( get_option( 'em_ep_custom_cap' ) ) ) {
			$sections = array( 'events', 'event_types', 'event_sites', 'event_performers', 'event_organizers', 'bookings' );
			// common caps for users
			$ep_common_caps = ['create', 'view', 'edit', 'read_private'];
			self::ep_set_user_custom_cap( array( 'administrator', 'editor', 'author', 'contributor' ), $sections, $ep_common_caps );

			// main caps only for admin and editor
			$ep_main_caps = ['view_others', 'edit_others', 'delete', 'delete_others'];
			self::ep_set_user_custom_cap( array( 'administrator', 'editor' ), $sections, $ep_main_caps );

			// caps only for subscriber
			self::ep_set_user_custom_cap( array( 'subscriber' ), $sections, array( 'view', 'read_private' ) );

			// caps for the Global Settings
			self::ep_set_user_custom_cap( array( 'administrator', 'editor' ), array( 'global_settings' ), array( 'view', 'edit' ) );

			// caps for email attendees
			self::ep_set_user_custom_cap( array( 'administrator', 'editor' ), array( 'email_attendees' ), array( 'manage', 'send' ) );


			update_option( 'em_ep_custom_cap', 1 );
		}
	}

	private static function ep_set_user_custom_cap( $roles, $sections, $caps ){
		if( !empty( $roles) && !empty( $sections) && !empty( $caps) ) {
			global $wp_roles;
			foreach( $roles as $role ){
				foreach ($sections as $section) {
					foreach($caps as $cap){
						$user_cap = $cap.'_'.$section;
						$wp_roles->add_cap($role, $user_cap);
					}
				}
			}
		}
	}
}
