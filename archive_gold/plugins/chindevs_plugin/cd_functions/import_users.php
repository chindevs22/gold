<?php
	// --------------------------------------------------------------------------------------------
	// CREATE USER SECTION
	// --------------------------------------------------------------------------------------------
	require_once 'helpers.php';
	function create_user_from_csv($userData) {
		global $userMGMLtoWP, $randomEmailCounter;

		if(!is_email($userData['email'])) {
			error_log("User doesn't have a valid email so was not created in our system");
			error_log(print_r($userData,true));
//			return;
		}

		//  Create array of User info from CSV data
		$wpdata['user_pass'] = "NewPassword!";
		//Cant use trim, need to pregreplace
		$wpdata['user_login'] = $userData['email'];
		$wpdata['first_name'] = $userData['first_name'];
		$wpdata['last_name'] = $userData['last_name'];
		$wpdata['display_name'] = $userData['first_name'];
        $wpdata['user_email'] = "chindevsRound1" . $randomEmailCounter . "@gmail.com";
        $randomEmailCounter += 1;

		//  $wpdata['user_email'] = $userData['email']; TODO: Add back the actual email

		// Create User
		if (!username_exists($wpdata['user_login']) && !email_exists($wpdata['user_email'])) {
			$user_id = wp_insert_user($wpdata);

			// if error creating the user
			if ( is_wp_error($user_id) ) {

				$error_message = $user_id->get_error_message();
				error_log("Didn't create user because of an error: " . $error_message);
				return;
			}

			$wp_user = new WP_User($user_id);

			error_log("USER: " . $userData['first_name']);
			error_log("The MGML user id" . $userData['id']);
			error_log("WordPRESS user ID" . $user_id);

// 			$userMGMLtoWP[$userData['id']] = $user_id;
			$wp_user->set_role('subscriber');
			create_meta($userData, $user_id, $userData['id']);

            // Send Password Reset Notification for user
            wp_new_user_notification($user_id, null, 'both');
		} else {
			// user already exists
			error_log("Updating existing user <br>");
			$user = get_user_by('login', $wpdata['user_login']);
			if ($user) {
				$wpdata['ID'] = $user->ID;
				$user_id = wp_update_user($wpdata);
// 				$userMGMLtoWP[$userData['id']] = $user_id;
				create_meta($userData, $user_id, $userData['id']);
			} else {
				error_log("ERROR: Could not find existing user <br>");
			}
		}
	}

	function create_meta($userData, $user_id, $mgml_id) {
		global $existingMetaMapping, $newMetaMapping;
		update_user_meta($user_id, 'mgml_user_id', $mgml_id);

		foreach ($existingMetaMapping as $key => $value) {
		   update_user_meta( $user_id, $key, $userData[$value] );
		}
		foreach ($newMetaMapping as $key => $value) {
		   add_user_meta( $user_id, $key, $userData[$value], true );
		}
	}

?>