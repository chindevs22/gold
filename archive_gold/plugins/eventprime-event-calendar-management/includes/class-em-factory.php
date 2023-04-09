<?php
/**
 * Factory class for load services
 */
class EventM_Factory {
	/**
	 * Get service name and return class instance
	 *
	 * @param Type $type Service name.
	 *
	 * @throws \Exception Not found.
	 */
	public static function get_service( $type ) {
		if ( class_exists( $type ) ) {
			return $type::get_instance();
		} else {
			throw new Exception( 'Class not found.' );
		}
	}
}
