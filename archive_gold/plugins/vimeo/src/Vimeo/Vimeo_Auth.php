<?php
namespace Tribe\Vimeo_WP\Vimeo;

use Tribe\Vimeo_WP\Settings\Settings;

class Vimeo_Auth {
	const APP_ID            = '220150';
	const CLIENT_ID         = '0d55495014a9459aa0c532e629435c0ab12614ad';
	const DISCONNECT_QUERY  = 'disconnect_vimeo_user';
	const ENDPOINT_AUTH     = '/oauth/authorize';
	const ENDPOINT_EXTENDED = '/oauth/extend';
	const ENDPOINT_ROOT     = 'https://api.vimeo.com';
	const REDIRECT_URL      = 'https://vimeo.com/wordpress/auth/token';
	const VIMEO_NONCE       = 'vimeo_disconnect_nonce';

	public function has_access_token() {
		$access_token = get_option( Settings::ACCESS_TOKEN, '' );
		if ( $access_token ) {
			return true;
		}
		return false;
	}

	/**
	 * Removes the connected Vimeo Account.
	 *
	 * @return void
	 */
	public function disconnect_vimeo_account() {
		if ( isset( $_POST[ self::DISCONNECT_QUERY ] ) ) {
			if ( 'true' === $_POST[ self::DISCONNECT_QUERY ] ) {
				if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
					add_settings_error( Settings::USER_TOKEN, 'error', __( 'Error disconnecting account, please try again.', 'vimeo-for-wordpress' ), 'error' );
				} else {
					if ( wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ), self::VIMEO_NONCE ) ) {
						delete_option( Settings::USER_TOKEN );
						delete_option( Settings::ACCESS_TOKEN );
						add_settings_error( Settings::USER_TOKEN, 'success', __( 'Your account has been disconnected successfully.', 'vimeo-for-wordpress' ), 'success' );
					} else {
						add_settings_error( Settings::USER_TOKEN, 'error', __(  'Error disconnecting account, please try again.', 'vimeo-for-wordpress' ), 'error' );
					}
				}
			}
		}
	}

	/**
	 * Returns the connect URL to start the OAuth flow.
	 *
	 * @return string
	 */
	public function get_connect_url() {
		$query = [
			'response_type' => 'token',
			'client_id'     => self::CLIENT_ID,
			'redirect_uri'  => self::REDIRECT_URL,
			// We separate the scope with a space, that it will be
			// replace by + sign by http_build_query following the RFC1738
			'scope'         => implode( ' ', [ 'public', 'private', 'video_files', 'upload', 'edit', 'delete' ] ),
			'state'         => base64_encode( openssl_random_pseudo_bytes( 30 ) ),
		];
		$url   = self::ENDPOINT_ROOT . self::ENDPOINT_AUTH . '?' . http_build_query( $query );
		return $url;
	}

	/**
	 * Makes vimeo request.
	 *
	 * @param string $endpoint The endpoint for the Vimeo API.
	 * @param string $token_override The token to use for making the call.
	 * @return array|WP_Error
	 */
	private function make_request( $endpoint, $token_override = '' ) {
		$access_token = get_option( Settings::ACCESS_TOKEN, '' );
		if ( $token_override ) {
			$access_token = $token_override;
		}
		$response = wp_remote_get(
			self::ENDPOINT_ROOT . $endpoint,
			[
				'headers' => [
					'Authorization' => 'bearer ' . $access_token,
				],
			]
		);
		return $response;
	}

	/**
	 * Validates the User Token, and stores long-lived token.
	 *
	 * @param string $user_token The user_token to test.
	 * @return bool
	 */
	public function validate_key( $user_token ) {
		$response = wp_remote_post(
			self::ENDPOINT_ROOT . self::ENDPOINT_EXTENDED,
			[
				'headers' => [
					'Authorization' => 'bearer ' . $user_token,
				],
			]
		);
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$response_body = wp_remote_retrieve_body( $response );
			if ( $response_body ) {
				$body         = json_decode( $response_body, true );
				$access_token = isset( $body['access_token'] ) ? $body['access_token'] : false;
				if ( $access_token ) {
					update_option( Settings::ACCESS_TOKEN, $access_token );
					return true;
				}
			}
		}
		return false;
	}


	public function get_user_account() {
		$response = $this->make_request( '/me' );
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			return json_decode( wp_remote_retrieve_body( $response ), true );
		}
		return '';
	}
}
