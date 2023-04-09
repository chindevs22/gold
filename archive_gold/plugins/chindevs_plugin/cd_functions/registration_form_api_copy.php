<?php
	require_once 'helpers.php';
	// create country_options on the form
	function create_country_options() {
		$form_options = get_option('stm_lms_form_builder_forms');

		$prof_form = $form_options[2];
		$fields = $prof_form['fields'];
		for ($x = 0; $x < count($fields); $x++) {
			$field = $fields[$x];
			if ($field['label'] == 'Country') {
				$field['choices'] = get_countries_new();
			}
			$fields[$x] = $field;
		}
		$form_options[2]['fields'] = $fields;
		update_option('stm_lms_form_builder_forms', $form_options);
	}

	// call the javascript
	function my_enqueue_script() {
		if (is_page('user-account')) {
			wp_enqueue_script('chindevs', get_template_directory_uri() . '/assets/js/chindevs.js', array('jquery'), '1.0', true);
		}
	}
	add_action('wp_enqueue_scripts', 'my_enqueue_script');

	function get_auth_token() {
	  $response = wp_remote_get("https://www.universal-tutorial.com/api/getaccesstoken", array(
		  'headers' => array(
				"Accept" => "application/json",
				"api-token" => "VY2ojFwRsuDagMzCTEfGciexCBZfAr6EmrBkMvjTvGN0cn9W0bForp5Cf69WcnRIk-c",
			   "user-email" => "anushagopal1234@gmail.com")
			)
		);
	  $token = json_decode( wp_remote_retrieve_body( $response ) );
	  return $token->auth_token;
	}

	function get_countries_new() {
		$token = get_auth_token();
		$response = wp_remote_get("https://www.universal-tutorial.com/api/countries/", array(
		  'headers' => array(
				"Authorization" => "Bearer {$token}",
			  "Accept" => "application/json")
			)
		);

		$countries = json_decode( wp_remote_retrieve_body( $response ) );
		// Get countries
		$country_names = array();
		foreach ( $countries as $country ) {
			array_push($country_names, $country->country_name);
		}
		return $country_names;
	}


	//populate states dropdown
	function get_states() {
	  $country = $_POST['country'];
	  $states = get_states_by_country_new($country);
	  $options = '';
	  foreach ($states as $state) {
		  $options .= '<option value="' . $state . '">' . $state . '</option>';
	  }
	  echo $options;
	  wp_die();
	}
	add_action('wp_ajax_get_states', 'get_states');
	add_action('wp_ajax_nopriv_get_states', 'get_states');

	function get_states_by_country_new($country_name) {
		$token = get_auth_token();
		$response = wp_remote_get("https://www.universal-tutorial.com/api/states/{$country_name}", array(
		  'headers' => array(
				"Authorization" => "Bearer {$token}",
			  "Accept" => "application/json")
			)
		);
		$states = json_decode( wp_remote_retrieve_body( $response ) );
		$state_names = array();
		foreach ( $states as $state ) {
			array_push($state_names, $state->state_name);
		}
		return $state_names;
	}
?>