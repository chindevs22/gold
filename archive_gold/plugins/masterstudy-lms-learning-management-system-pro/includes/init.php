<?php
require_once STM_LMS_PRO_INCLUDES . '/licenses/freemius.php';
require_once STM_LMS_PRO_INCLUDES . '/licenses/appsumo.php';
require_once STM_LMS_PRO_INCLUDES . '/hooks/setup.php';
require_once STM_LMS_PRO_INCLUDES . '/classes/class-nonces.php';

function mslms_plus_verify() {
	if ( function_exists( 'mslms_fs' ) ) {
		return mslms_fs()->is__premium_only() && mslms_fs()->can_use_premium_code();
	} elseif ( function_exists( 'mslms_appsumo' ) ) {
		return mslms_appsumo()->is_activated();
	}

	return false;
}

function mslms_verify() {
	if ( function_exists( 'mslms_fs' ) || function_exists( 'mslms_appsumo' ) ) {
		return mslms_plus_verify();
	}

	return true;
}

if ( ! is_textdomain_loaded( 'masterstudy-lms-learning-management-system-pro' ) ) {
	load_plugin_textdomain(
		'masterstudy-lms-learning-management-system-pro',
		false,
		'masterstudy-lms-learning-management-system-pro/languages'
	);
}

if ( mslms_verify() ) {
	add_action( 'plugins_loaded', 'stm_lms_pro_init' );
	function stm_lms_pro_init() {
		if ( ! defined( 'STM_LMS_PATH' ) ) {
			require_once STM_LMS_PRO_INCLUDES . '/wizard/wizard.php';
		} else {
			require_once STM_LMS_PRO_INCLUDES . '/pro.php';

			if ( mslms_plus_verify() && file_exists( STM_LMS_PRO_INCLUDES . '/plus.php' ) ) {
				require_once STM_LMS_PRO_INCLUDES . '/plus.php';
			}
		}
	}
}

add_filter(
	'masterstudy_lms_plugin_addons',
	function ( $addons ) {
		return array_merge(
			$addons,
			array(
				new \MasterStudy\Lms\Pro\addons\assignments\Assignments(),
				new \MasterStudy\Lms\Pro\addons\sequential_drip_content\DripContent(),
				new \MasterStudy\Lms\Pro\addons\live_streams\LiveStreams(),
				new \MasterStudy\Lms\Pro\addons\prerequisite\Prerequisite(),
				new \MasterStudy\Lms\Pro\addons\scorm\Scorm(),
				new \MasterStudy\Lms\Pro\addons\shareware\Shareware(),
				new \MasterStudy\Lms\Pro\addons\zoom_conference\ZoomConference(),
			)
		);
	}
);
