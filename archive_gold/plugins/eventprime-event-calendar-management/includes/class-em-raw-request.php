<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Load request
 */
class EventM_Raw_Request {
	/**
	 * Instance
	 *
	 * @var Instance
	 */
	private static $instance = null;

	/**
	 * Get class instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Map request to model
	 */
	public function map_request_to_model( $type = null ) {
		if ( ! class_exists( $type ) ) {
			return null;
		}

		$model = new $type();
		$data  = $this->get_data();
		if ( ! empty( $data ) && is_array( $data ) ) {
			foreach ( $data as $key => $val ) {
				$method = 'set_' . $key;
				if ( method_exists( $model, $method ) ) {
					$model->$method( $val );
				}
				else if ( property_exists( $model, $key ) ) {
					$model->{$key} = $val;
				}
			}
		}
		return $model;
	}

	/**
	 * Get and return param
	 *
	 * @param Param $param Params.
	 *
	 * @param Secure $secure Is secure.
	 */
	public function get_param( $param = null, $secure = false ) {
		$request = $this->get_data();
		$null_return = null;

		if ( null !== $request ) {
			$_POST = (array) $request;
		}

		if ( $param && isset( $_POST[ $param ] ) && is_array( $_POST[ $param ] ) ) {
			return $_POST[ $param ];
		}

		if ( $param ) {
			if ( $secure ) {
				$value = ( ! empty( $_POST[ $param ] ) ? trim( esc_sql( $_POST[ $param ] ) ) : $null_return );
			} else {
				$value = ( ! empty( $_POST[ $param ] ) ? trim( esc_sql( $_POST[ $param ] ) ) : ( ! empty( $_GET[ $param ] ) ? trim( esc_sql( $_GET[ $param ] ) ) : $null_return ) );
			}
			return stripslashes( $value );
		} else {
			$params = array();
			foreach ( $_POST as $key => $param ) {
				$params[ trim( esc_sql( $key ) ) ] = ( ! empty( $_POST[ $key ] ) ? trim( esc_sql( $_POST[ $key ] ) ) : $null_return );
			}
			if ( ! $secure ) {
				foreach ( $_GET as $key => $param ) {
					$key = trim( esc_sql( $key ) );
					if ( ! isset( $params[ $key ] ) ) { // if there is no key or it's a null value.
						$params[ trim( esc_sql( $key ) ) ] = ( ! empty( $_GET[ $key ] ) ? trim( esc_sql( $_GET[ $key ] ) ) : $null_return );
					}
				}
			}

			return stripslashes( $params );
		}
	}

	/**
	 * Get input data
	 */
	public function get_data() {
		$postdata = file_get_contents( "php://input" );

		$request = json_decode( $postdata );
		return (array) $request;
	}
}
