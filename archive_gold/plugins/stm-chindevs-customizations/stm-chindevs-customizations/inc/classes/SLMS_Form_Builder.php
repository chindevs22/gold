<?php

class SLMS_Form_Builder extends STM_LMS_Form_Builder {

    public function __construct() {
//        remove_action( 'show_user_profile', array( 'STM_LMS_Form_Builder', 'display_fields_in_profile'), 20 );
//        remove_action( 'edit_user_profile', array( 'STM_LMS_Form_Builder', 'display_fields_in_profile'), 20 );

        remove_all_actions('show_user_profile');
        remove_all_actions('edit_user_profile');

        add_action( 'show_user_profile', array( $this, 'display_fields_in_profile' ), 20 );
        add_action( 'edit_user_profile', array( $this, 'display_fields_in_profile' ), 20 );
    }

    public function display_fields_in_profile( $user ) {
        require_once SLMS_PATH . '/addons/form_builder/templates/admin_profile_fields.php';
    }

}

new SLMS_Form_Builder();
