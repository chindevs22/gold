<?php
	// --------------------------------------------------------------------------------------------
	// CREATE USER SECTION
	// --------------------------------------------------------------------------------------------
	require_once 'helpers.php';
	function create_user_from_csv($userData) {
		global $userMGMLtoWP, $randomEmailCounter;

		//  Create array of User info from CSV data
		$wpdata['user_pass'] = "HariOm2022!";
		$wpdata['user_login'] = $userData['first_name'];
		$wpdata['first_name'] = $userData['first_name'];
		$wpdata['last_name'] = $userData['last_name'];
		$wpdata['display_name'] = $userData['first_name'];
        $wpdata['user_email'] = "chindevs" . $randomEmailCounter . "@gmail.com";
        $randomEmailCounter += 1;
		//  $wpdata['user_email'] = $userData['email'];

		if ( !username_exists($wpdata['user_login']) && !email_exists($wpdata['user_email']) ) {
			$user_id = wp_insert_user($wpdata);
			$wp_user = new WP_User($user_id);
			echo "USER: " . $userData['first_name'] . "<br>";
			echo "The MGML user id" . $userData['id'] . "<br>";
			echo "WordPRESS user ID" . $user_id . "<br>";
			$userMGMLtoWP[$userData['id']] = $user_id;
			$wp_user->set_role('subscriber');
			create_meta($userData, $user_id);
		}
		else {
			echo "NOT creating new user <br>";
			$user_id = wp_update_user($wpdata);
			$userMGMLtoWP[$userData['id']] = $user_id;
			create_meta($userData, $user_id);
		}
		echo "The user mgml to wp map: ";
	}

	function create_meta($userData, $user_id) {
		global $existingMetaMapping, $newMetaMapping;
		foreach ($existingMetaMapping as $key => $value) {
		   update_user_meta( $user_id, $key, $userData[$value] );
		}
		foreach ($newMetaMapping as $key => $value) {
		   add_user_meta( $user_id, $key, $userData[$value], true );
		}
	}

?>