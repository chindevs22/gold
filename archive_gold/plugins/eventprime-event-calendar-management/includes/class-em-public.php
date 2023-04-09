<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Public script
 */
class EventM_Public {
	/**
	 * Instance
	 *
	 * @var Instance
	 */
	private static $instance = null;
	/**
	 * Initialization of class
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueues' ) );
		add_action( 'wp_head', array( $this, 'custom_styles' ), 100 );
	}

	/**
	 * Define instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Enqueue
	 */
	public function enqueues() {
		wp_register_script( 'em-angular', plugin_dir_url( __DIR__ ) . 'includes/js/angular.js', array( 'jquery' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'dir-pagination', plugin_dir_url( __DIR__ ) . 'includes/admin/template/js/dirPagination.js', array( 'em-angular' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-angular-module', plugin_dir_url( __DIR__ ) . 'includes/admin/template/js/em-module.js', array( 'em-angular', 'dir-pagination' ), EVENTPRIME_VERSION, false );
		wp_localize_script( 'em-angular-module', 'em_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		wp_register_script( 'em-booking-controller', plugin_dir_url( __DIR__ ) . 'includes/templates/js/em-booking-controller.js', array( 'em-angular-module' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-user-register-controller', plugin_dir_url( __DIR__ ) . 'includes/templates/js/em-register-controller.js', array( 'em-angular-module' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-timepicker', EM_BASE_URL . 'includes/admin/template/js/timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-event-submit-controller', plugin_dir_url( __DIR__ ) . 'includes/templates/js/em-event-submit-controller.js', array( 'em-angular-module', 'em-timepicker' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-public', plugin_dir_url( __DIR__ ) . 'includes/templates/js/em-public.js', array( 'jquery', 'jquery-ui-datepicker' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'moment', EM_BASE_URL . 'includes/templates/js/moment.min.js', array(), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-full-calendar', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/core/main.min.js', array(), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-full-interaction-calendar', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/interaction/main.min.js', array( 'em-full-calendar' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-full-daygrid-calendar', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/daygrid/main.min.js', array( 'em-full-calendar' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-full-list-calendar', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/list/main.min.js', array( 'em-full-calendar' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-full-calendar-locales', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/core/locales-all.min.js', array( 'em-full-calendar' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'em-full-calendar-moment', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/moment/main.js', array( 'em-full-calendar', 'moment' ), EVENTPRIME_VERSION, false );

		wp_register_script( 'em-calendar-util', EM_BASE_URL . 'includes/templates/js/em-calendar-util.js', array( 'em-full-calendar', 'em-full-interaction-calendar', 'em-full-daygrid-calendar', 'em-full-list-calendar', 'em-public', 'em-full-calendar-locales', 'moment', 'em-full-calendar-moment' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'jquery-colorbox', plugin_dir_url( __DIR__ ) . 'includes/templates/js/jquery.colorbox.js', array( 'jquery' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'font-awesome', plugin_dir_url( __DIR__ ) . 'includes/js/font_awesome.js', false, EVENTPRIME_VERSION, false );
		wp_register_script( 'em-google-map', plugin_dir_url( __DIR__ ) . 'includes/js/em-map.js', false, EVENTPRIME_VERSION, false );
		wp_register_script( 'em-ctabs', plugin_dir_url( __DIR__ ) . 'includes/templates/js/em_custom_tabs.js', false, EVENTPRIME_VERSION, false );
		wp_register_style( 'em_responsive_slider_style', plugin_dir_url( __DIR__ ) . 'includes/templates/css/responsiveslides.css', array(), EVENTPRIME_VERSION, false );
		wp_register_script( 'em_responsive_slider_js', plugin_dir_url( __DIR__ ) . 'includes/templates/js/responsiveslides.min.js', array(), EVENTPRIME_VERSION, false );

		wp_register_style( 'em-ctabs-css', plugin_dir_url( __DIR__ ) . 'includes/templates/css/em_custom_tabs.css', false, EVENTPRIME_VERSION, false );
		wp_register_style( 'jquery-ui-css', EM_BASE_URL . 'includes/templates/css/jquery-ui.css', array(), EVENTPRIME_VERSION, false );
		wp_register_style( 'em-public-css', plugin_dir_url( __DIR__ ) . '/includes/templates/css/em_public.css', array( 'jquery-ui-css' ), EVENTPRIME_VERSION, false );
		wp_register_style( 'em-colorbox-css', plugin_dir_url( __DIR__ ) . 'includes/templates/css/colorbox.css', array(), EVENTPRIME_VERSION, false );
		wp_register_style( 'em-full-calendar-css', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/core/main.min.css', array( 'em-public-css' ), EVENTPRIME_VERSION, false );
		wp_register_style( 'em-full-calendar-daygrid-css', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/daygrid/main.min.css', array(), EVENTPRIME_VERSION, false );
		wp_register_style( 'em-full-calendar-list-css', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/list/main.min.css', array(), EVENTPRIME_VERSION, false );

		wp_register_script( 'em-single-event', plugin_dir_url( __DIR__ ) . 'includes/templates/js/em-single-event.js', array( 'jquery' ), EVENTPRIME_VERSION, false );
		wp_register_script( 'fontawesome', plugin_dir_url( __DIR__ ) . 'includes/js/font_awesome.js', array( 'jquery' ), EVENTPRIME_VERSION, false );
		wp_enqueue_script( 'em-admin-jscolor', plugin_dir_url( __DIR__ ) . 'includes/admin/template/js/em-jscolor.js', false, EVENTPRIME_VERSION, false );
		wp_register_style( 'em-select2-css', plugin_dir_url( __DIR__ ) . 'includes/admin/template/css/select2.min.css', false, EVENTPRIME_VERSION, false );
		wp_register_script( 'em-select2', plugin_dir_url( __DIR__ ) . 'includes/admin/template/js/select2.min.js', array( 'jquery' ), EVENTPRIME_VERSION, false );

		wp_register_style( 'em-int-tel-input-css', plugin_dir_url( __DIR__ ) . 'includes/templates/css/intTell/intlTelInput.css', false, EVENTPRIME_VERSION, false );
		wp_register_script( 'em-int-tel-js', plugin_dir_url( __DIR__ ) . 'includes/templates/js/intTell/jquery.min.js', array(), EVENTPRIME_VERSION, true );
		wp_register_script( 'em-int-tel-input-js', plugin_dir_url( __DIR__ ) . 'includes/templates/js/intTell/intlTelInput.js', array(), EVENTPRIME_VERSION, true );
		wp_register_script( 'em-int-tel-input-min-js', plugin_dir_url( __DIR__ ) . 'includes/templates/js/intTell/intlTelInput.min.js', array(), EVENTPRIME_VERSION, true );
		wp_register_script( 'em-util-js', plugin_dir_url( __DIR__ ) . 'includes/templates/js/intTell/utils.js', array(), EVENTPRIME_VERSION, true );
		
		wp_localize_script( 'em-public', 'em_js_vars', em_global_js_strings() );
		wp_localize_script( 'em-booking-controller', 'em_booking_js_vars', em_booking_js_strings() );

		// register and localize datepicker language file.
		$site_local = get_locale();
		if ( strpos( $site_local, '_' ) !== false ) {
			$site_local = explode( '_', $site_local )[0];
			if ( file_exists( EM_BASE_DIR . 'includes/templates/js/datepicker-' . $site_local . '.js' ) ) {
				wp_register_script( 'datepicker-' . $site_local, plugin_dir_url( __DIR__ ) . 'includes/templates/js/datepicker-' . $site_local . '.js', array( 'jquery', 'jquery-ui-datepicker' ), EVENTPRIME_VERSION, false );
				wp_enqueue_script( 'datepicker-' . $site_local );
			} elseif ( file_exists( EM_BASE_DIR . 'includes/templates/js/datepicker-' . get_locale() . '.js' ) ) {
				wp_register_script( 'datepicker-' . get_locale(), plugin_dir_url( __DIR__ ) . 'includes/templates/js/datepicker-' . get_locale() . '.js', array( 'jquery', 'jquery-ui-datepicker' ), EVENTPRIME_VERSION, false );
				wp_enqueue_script( 'datepicker-' . get_locale() );
			}
		}
	}

	/**
	 * Custom Style
	 */
	public function custom_styles() {
		$custom_css = em_global_settings( 'custom_css' );
		if ( false !== $custom_css ) {
			echo '<style type="text/css">' . esc_attr( $custom_css ) . '</style>';
		}
	}

}
EventM_Public::get_instance();
