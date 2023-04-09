<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * User Meta class
 */
class EventM_User_Meta {
	/**
	 * Instance
	 *
	 * @var Instance
	 */
	private static $instance = null;
	/**
	 * Class constructor
	 */
	private function __construct() {
		// add the field to user's own profile editing screen.
		add_filter(
			'user_contactmethods',
			array( $this, 'additional_contactmethods' )
		);

		// add the save action to user's own profile editing screen update.
		add_action(
			'personal_options_update',
			array( $this, 'usermeta_form_field_phone_update' )
		);

		// add the save action to user profile editing screen update.
		add_action(
			'edit_user_profile_update',
			array( $this, 'usermeta_form_field_phone_update' )
		);
	}
	/**
	 * Load instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Additional Method
	 *
	 * @param Array $array Attribute.
	 */
	public static function additional_contactmethods( $array ) {
		if ( ! isset( $array['phone'] ) ) {
			$array['phone'] = esc_html__( 'Phone', 'eventprime-event-calendar-management' );
		}
		return $array;
	}
	/**
	 * Update user meta
	 *
	 * @param User id $user_id User Id.
	 */
	public function usermeta_form_field_phone_update( $user_id ) {
		// check that the current user have the capability to edit the $user_id.
		if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['phone'] ) ) {
			return false;
		}

		// create/update user meta for the $user_id.
		return update_user_meta(
			$user_id,
			'phone',
			$_POST['phone']
		);
	}
}
EventM_User_Meta::get_instance();
