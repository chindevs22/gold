<?php
/**
 * Plugin Name: EventPrime – Modern Events Calendar, Bookings and Tickets
 * Plugin URI: http://eventprime.net
 * Description: Beginner-friendly Events Calendar plugin to create free as well as paid Events. Includes Event Types, Event Sites & Performers too.
 * Version: 2.7.8
 * Author: EventPrime
 * Text Domain: eventprime-event-calendar-management
 * Domain Path: /languages
 * Author URI: http://eventprime.net
 * Requires at least: 4.8
 * Tested up to: 6.1.1
 * Requires PHP: 5.6
 *
 * @package EventPrime
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Event_Magic' ) ) {
	/**
	 * EventPrime Plugin
	 *
	 * The main plugin handler class is responsiable for initializing the EventPrime.
	 *
	 * @since 1.0.0
	 */
	class Event_Magic {
		/**
		 * Version
		 *
		 * @var Version
		 */
		
		public $version = '2.7.8';

		/**
		 * Request type
		 *
		 * @var Request_Type
		 */
		public $request_type;
		/**
		 * Errors
		 *
		 * @var Errors
		 */
		public $errors = array();
		/**
		 * Extensions
		 *
		 * @var Extensions
		 */
		public $extensions = array();
		/**
		 * Instance
		 *
		 * @var Instance
		 */
		protected static $instance = null;

		/**
		 *
		 * Ensures only one instance of Event_Magic is loaded or can be loaded.
		 *
		 * @static
		 * @return Event_Magic - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->define_constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->init_hooks();
				add_action( 'plugins_loaded', array( self::$instance, 'plugin_loaded' ) );
			}
			return self::$instance;
		}

		/**
		 * Define plugin constants
		 */
		private function define_constants() {
			$this->define( 'EM_VENUE_TYPE_TAX', 'em_venue' );
			$this->define( 'EM_EVENT_POST_TYPE', 'em_event' );
			$this->define( 'EM_PERFORMER_POST_TYPE', 'em_performer' );
			$this->define( 'EM_BOOKING_POST_TYPE', 'em_booking' );
			$this->define( 'EM_EVENT_TYPE_TAX', 'em_event_type' );
			$this->define( 'EM_EVENT_VENUE_TAX', 'em_venue' );
			$this->define( 'EM_GLOBAL_SETTINGS', 'em_global_settings' );
			$this->define( 'EVENTPRIME_VERSION', $this->version );
			$this->define( 'EM_DB_VERSION', 'emagic_db_version' );
			$this->define( 'EM_PAGINATION_LIMIT', 10 );
			$this->define( 'EM_DEFAULT_CURRENCY', 'USD' );
			$this->define( 'EM_BASE_URL', plugin_dir_url( __FILE__ ) );
			$this->define( 'EM_BASE_FRONT_IMG_URL', plugin_dir_url( __FILE__ ) . 'includes/templates/images/' );
			$this->define( 'EM_BASE_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'EM_REQ_EXT_MCRYPT', 1 );
			$this->define( 'EM_REQ_EXT_CURL', 2 );
			$this->define('EM_EVENT_ORGANIZER_TAX', 'em_event_organizer');
		}

		/**
		 * Method to define all constant
		 *
		 * @param Name  $name Constant name.
		 *
		 * @param Value $value Constant value.
		 */
		public function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include plugin classes
		 */
		public function includes() {
			include_once EM_BASE_DIR . 'includes/class-em-factory.php';
			include_once EM_BASE_DIR . 'includes/class-em-constants.php';
			include_once EM_BASE_DIR . 'includes/class-em-raw-request.php';
			include_once EM_BASE_DIR . 'includes/class-do-action.php';
			include_once EM_BASE_DIR . 'includes/em-core-functions.php';
			include_once EM_BASE_DIR . 'includes/models/class-em-model.php';
			include_once EM_BASE_DIR . 'includes/class-em-user-meta.php';
			include_once EM_BASE_DIR . 'includes/class-em-install.php';

			// Dao.
			include_once EM_BASE_DIR . 'includes/dao/class-em-term.php';
			include_once EM_BASE_DIR . 'includes/dao/class-em-post.php';
			include_once EM_BASE_DIR . 'includes/dao/class-em-venue.php';
			include_once EM_BASE_DIR . 'includes/dao/class-em-performer.php';
			include_once EM_BASE_DIR . 'includes/dao/class-em-event-type.php';
			include_once EM_BASE_DIR . 'includes/dao/class-em-event.php';
			include_once EM_BASE_DIR . 'includes/dao/class-em-event.php';
			include_once EM_BASE_DIR . 'includes/dao/class-em-global-settings.php';
			include_once EM_BASE_DIR . 'includes/dao/class-em-booking.php';
			include_once EM_BASE_DIR . 'includes/dao/class-em-event-organizer.php';

			if ( is_admin() ) {
				include_once EM_BASE_DIR . 'includes/admin/class-em-admin.php';
			} else {
				include_once EM_BASE_DIR . 'includes/class-em-public.php';
			}

			// Models.
			include_once EM_BASE_DIR . 'includes/models/class-em-base-model.php';
			include_once EM_BASE_DIR . 'includes/models/class-em-array-model.php';
			include_once EM_BASE_DIR . 'includes/models/class-em-venue.php';
			include_once EM_BASE_DIR . 'includes/models/class-em-event.php';
			include_once EM_BASE_DIR . 'includes/models/class-em-performer.php';
			include_once EM_BASE_DIR . 'includes/models/class-em-event-type.php';
			include_once EM_BASE_DIR . 'includes/models/class-em-global-settings.php';
			include_once EM_BASE_DIR . 'includes/models/class-em-booking.php';
			include_once EM_BASE_DIR . 'includes/class-em-post-types.php';
			include_once EM_BASE_DIR . 'includes/class-em-shortcodes.php';
			include_once EM_BASE_DIR . 'includes/models/class-em-event-organizer.php';

			// Services.
			include_once EM_BASE_DIR . 'includes/service/class-em-payment.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-event.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-performer.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-venue.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-event-type.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-user.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-booking.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-booking.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-notification.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-print.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-setting.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-extensions.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-paypal.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-bulk-email.php';
			include_once EM_BASE_DIR . 'includes/service/class-em-event-organizer.php';

			// Widgets.
			include_once EM_BASE_DIR . 'includes/widgets/event_calendar.php';
			include_once EM_BASE_DIR . 'includes/widgets/venue_map.php';
			include_once EM_BASE_DIR . 'includes/widgets/event_slider.php';
			include_once EM_BASE_DIR . 'includes/widgets/event_countdown.php';
			include_once EM_BASE_DIR . 'includes/class-em-ajax.php';
			include_once EM_BASE_DIR . 'includes/widgets/featured_performer.php';
			include_once EM_BASE_DIR . 'includes/widgets/popular_performer.php';
			include_once EM_BASE_DIR . 'includes/widgets/featured_types.php';
			include_once EM_BASE_DIR . 'includes/widgets/popular_types.php';
			include_once EM_BASE_DIR . 'includes/widgets/featured_organizer.php';
			include_once EM_BASE_DIR . 'includes/widgets/popular_organizer.php';
			include_once EM_BASE_DIR . 'includes/widgets/featured_venues.php';
			include_once EM_BASE_DIR . 'includes/widgets/popular_venues.php';
		}

		/**
		 * Hooks initialization
		 */
		private function init_hooks() {
			if(!defined('EP_EM_EVENTS')) define('EP_EM_EVENTS', 555);
			add_action( 'init', 'em_check_event_status' ); // Automatically expiring events that are passed.
			add_action( 'init', 'em_delete_tmp_bookings' ); // Deleting temporary bookings.
			//add_action( 'init', 'em_redirect_event_posts' );
			add_action( 'init', array( EventM_Factory::get_service( 'EventM_Service' ), 'get_ical_file' ), 9999 ); // iCal file download.
			//add_action( 'init', array( EventM_Factory::get_service( 'EventM_Service' ), 'get_ical_file_email' ), 9999 );
			add_action( 'admin_notices', 'em_check_required_pages' );
			register_activation_hook( __FILE__, array( 'EventM_Install', 'install' ) );
			// add action on upgrade plugin.
			add_action( 'upgrader_process_complete', array( 'EventM_Install', 'event_magic_upgrader_process_complete' ), 10, 2 );
			add_action('init', array('EventM_Install', 'em_check_updated_data'));
		}

		/**
		 * Plugin text domain
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'eventprime-event-calendar-management', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Action after plugin loaded
		 */
		public function plugin_loaded() {
			do_action( 'event_magic_loaded' );
		}
	}

}

/**
 * Main instance of Event_Magic.
 *
 * @return Event_Magic
 */
function event_magic_instance() {
	return Event_Magic::instance();
}

event_magic_instance();
