<?php
/*
 * Plugin Name:       Conditional Checkout Fields for WooCommerce
 * Description:       Customize the checkout page by adding new and conditional based fields in Billing, Shipping, and Additional section.
 * Version:           1.3.1
 * Author:       FME Addons         
 * Text Domain:   conditional-checkout-fields-for-woocommerce
 * Domain Path: /languages

 * Woo: 6199451:b1d9f33593c518d8418a7c0190b4e2c8
*/

if ( ! defined( 'WPINC' ) ) {
	wp_die();
}
 
/**
 * Check if WooCommerce is active
 * if wooCommerce is not active ext Tabs module will not work.
 * 
 * @since 4.4.0
 **/
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {

	/**
	* Check WooCommerce is installed and active
	*
	* This function will check that woocommerce is installed and active
	* and returns true or false
	*
	* @return true or false
	*/
	function fme_fv_admin_notice() {

		// Deactivate the plugin
		deactivate_plugins(__FILE__);

		$allowed_tags = array(
			'a' => array(
				'is_class' => array(),
				'href'  => array(),
				'rel'   => array(),
				'title' => array(),
			),
			'abbr' => array(
				'title' => array(),
			),
			'b' => array(),
			'blockquote' => array(
				'cite'  => array(),
			),
			'cite' => array(
				'title' => array(),
			),
			'code' => array(),
			'del' => array(
				'datetime' => array(),
				'title' => array(),
			),
			'dd' => array(),
			'div' => array(
				'is_class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'dl' => array(),
			'dt' => array(),
			'em' => array(),
			'h1' => array(),
			'h2' => array(),
			'h3' => array(),
			'h4' => array(),
			'h5' => array(),
			'h6' => array(),
			'i' => array(),
			'img' => array(
				'alt'    => array(),
				'is_class'  => array(),
				'height' => array(),
				'src'    => array(),
				'is_class'  => array(),
			),
			'li' => array(
				'is_class' => array(),
			),
			'ol' => array(
				'is_class' => array(),
			),
			'p' => array(
				'is_class' => array(),
			),
			'q' => array(
				'cite' => array(),
				'title' => array(),
			),
			'span' => array(
				'is_class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'strike' => array(),
			'strong' => array(),
			'ul' => array(
				'is_class' => array(),
			),
		);

		$wooextmm_message = '<div id="message" is_class="error">
		<p><strong> Add Featured Videos in Product Gallery for WooCommerce Plugin is inactive.</strong> The <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce plugin</a> must be active for this plugin to work. Please install &amp; activate WooCommerce »</p></div>';

		echo wp_kses(__($wooextmm_message, 'fme-fv'), $allowed_tags);

	}
	add_action('admin_notices', 'fme_fv_admin_notice');
}



if ( !class_exists( 'FME_CCFW_MAIN' ) ) {

	class FME_CCFW_MAIN {
		
		public function __construct() {
			$this->FME_CCFW_module_constants();
			add_action( 'init', array( $this, 'fme_ccfw_load_text_domain' ) );
			add_action('init', array($this, 'check_paypal_availability'));
			//haseeb changed fme_ccfw_deactivate
			add_action( 'upgrader_process_complete', array( $this, 'fme_ccfw_deactivate' ), 10, 2 );
			if (is_admin()) {

				register_activation_hook( __FILE__, array( $this, 'fme_install_module' ) );
				// add_filter( 'upgrader_pre_install', 'deactivate_plugin_before_upgrade_callback', 10, 2 );
				
				require_once( FME_CCFW_PLUGIN_DIR . 'admin/fme-ccfw-admin.php' );
			} else {
				require_once( FME_CCFW_PLUGIN_DIR . 'front/fme-ccfw-front.php' );
			}
			
		}
		public function check_paypal_availability() {
			if ( class_exists( 'WC_Payment_Gateways' ) ) {
				$gateways = new WC_Payment_Gateways();

				$active_gateways = $gateways->get_available_payment_gateways();
				$fme_ccfw_paypal_status = false;
				foreach ( $active_gateways as $gateway ) {

					if ('ppcp-gateway' == $gateway->id) {
						
						$fme_ccfw_paypal_status=true;
						break;
					}
				}

				if ( $fme_ccfw_paypal_status ) {
					update_option('fme_ccfw_paypal_status', 'on');

				} else {
					update_option('fme_ccfw_paypal_status', 'off');
				}
			}
		}
		public function fme_ccfw_load_text_domain() {
			load_plugin_textdomain('conditional-checkout-fields-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages/');
		}

		public function fme_install_module() {
			
			
			
			$this->fme_module_tables();
			$this->fme_create_module_data();
		}

		private function fme_module_tables() {
			global $wpdb;
			$wpdb->FmeCCFA_fields = $wpdb->prefix . 'FmeCCFA_fields';
		}

		public function fme_create_module_data() {
			global $wpdb;
			$this->fme_create_tables();
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->FmeCCFA_fields'" ) == $wpdb->FmeCCFA_fields ) {
				$result = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields' );
				if (0 == $wpdb->num_rows ) {
					$this->fme_set_module_default_data();
				} else {
						// replaced "on" value with "fme_display_user_role" in 1.2.3
						$wpdb->query($wpdb->prepare( "UPDATE $wpdb->FmeCCFA_fields SET userrole_check=%s WHERE userrole_check=%s", 'fme_display_user_role', 'on'));	

				}
				//haseeb changed
				$plugin_data=get_plugin_data(FME_CCFW_PLUGIN_DIR . 'conditional-checkout-fields-for-woocommerce.php');
				$fme_version=$plugin_data['Version'];
				if ( 0!=$wpdb->num_rows && '1.1.9'==$fme_version && isset($result) ) {
					global $wpdb;
					//haseeb changed Addtional Order Notes
					$result2 = $wpdb->get_results(  'SELECT field_id, field_name  FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_name ="order_comments" ') ;

					if (isset($result2) && empty($result2) ) {
						// $result2 = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields' );
						
						$wpdb->query($wpdb->prepare( "
							INSERT INTO $wpdb->FmeCCFA_fields
							(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
							VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
							", 
							'order_comments',
							'Order notes',
							'Notes about your order, e.g. special notes for delivery.',
							'0',
							'1',
							'form-row-wide',
							'1',
							'order-comments',
							'textarea',
							'additional',
							'default'
						) );
					}
				}
			}
		}

		public function fme_create_tables() {
			global $wpdb;
			$charset_collate = '';
			if ( !empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( !empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";		
			}
			

			// if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->FmeCCFA_fields'" ) != $wpdb->FmeCCFA_fields ) {
			$sql = 'CREATE TABLE ' . $wpdb->FmeCCFA_fields . " (
			field_id int(25) NOT NULL auto_increment,
			field_name varchar(255) NULL,
			field_label longtext NULL,
			field_placeholder varchar(255) NULL,
			is_required int(25) NOT NULL,
			is_taxable int(25) NOT NULL,
			is_min_date int(25) NULL,
			is_enable int(25) NOT NULL,
			is_class varchar(255) NULL,
			sort_order int(25) NOT NULL,
			autocomplete varchar(255) NULL,
			field_type varchar(255) NULL,
			type varchar(255) NULL,
			options longtext NULL,
			field_mode varchar(255) NULL,
			field_extensions varchar(255) NULL,
			field_price varchar(255) NULL,
			cfield longtext NULL,
			specific_pc varchar(5000) NULL,
			ischeckpc varchar(255) NULL,
			selected_pc text NULL,
			userrole_check varchar(255) NULL,
			specific_user_role varchar(5000) NULL,
			heading_type varchar(255) NULL,
			field_file_size varchar(255) NULL,
			PRIMARY KEY (field_id)
		) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			// }
		}

		public function fme_set_module_default_data() {
			global $wpdb;

			// Insert billing first name
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
			(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_first_name',
				'First Name',
				'First Name',
				'1',
				'1',
				'form-row-first',
				'1',
				'given-name',
				'text',
				'billing',
				'default'
			) );

					// Insert billing last name
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete ,field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_last_name',
				'Last Name',
				'Last Name',
				'1',
				'1',
				'form-row-last',
				'2',
				'family-name',
				'text',
				'billing',
				'default'
			) );

			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete,  field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_company',
				'Company Name',
				'Company Name',
				'0',
				'1',
				'form-row-wide',
				'3',
				'organization',
				'text',
				'billing',
				'default'
			) );

					// Insert billing country
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_country',
				'Country / Region',
				'Country',
				'1',
				'1',
				'form-row-wide address-field update_totals_on_change',
				'4',
				'country',
				'select',
				'billing',
				'default'
			) );


				// Insert billing address1
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_address_1',
				'Street address',
				'House number and street name',
				'1',
				'1',
				'form-row-wide address-field',
				'5',
				'address-line1',
				'text',
				'billing',
				'default'
			) );


					// Insert billing address2
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s,%s)
				", 
				'billing_address_2',
				'Address 2',
				'Apartment, suite, unit, etc.',
				'0',
				'1',
				'form-row-wide address-field',
				'6',
				'address-line2',
				'text',
				'billing',
				'default'
			) );


					// Insert billing city
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_city',
				'Town / City',
				'Town / City',
				'1',
				'1',
				'address-field form-row-wide ',
				'7',
				'address-level2',
				'text',
				'billing',
				'default'
			) );

					// Insert billing state
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_state',
				'State / County',
				'Select an option...',
				'1',
				'1',
				'address-field form-row-wide ',
				'8',
				'address-level1',
				'select',
				'billing',
				'default'
			) );

						// Insert billing postcode
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_postcode',
				'Postcode / Zip',
				'Postcode / Zip',
				'1',
				'1',
				'address-field form-row-wide',
				'9',
				'postal-code',
				'text',
				'billing',
				'default'
			) );


					// Insert billing phone number
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_phone',
				'Phone',
				'Phone',
				'1',
				'1',
				'form-row-wide',
				'10',
				'tel',
				'tel',
				'billing',
				'default'
			) );

					// Insert billing email
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'billing_email',
				'Email Address',
				'Email Address',
				'1',
				'1',
				'form-row-wide',
				'11',
				'email username',
				'email',
				'billing',
				'default'
			) );

					// Insert shipping first name
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'shipping_first_name',
				'First Name',
				'First Name',
				'1',
				'1',
				'form-row-first',
				'1',
				'given-name',
				'text',
				'shipping',
				'default'
			) );

					// Insert shipping last name
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete,  field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'shipping_last_name',
				'Last Name',
				'Last Name',
				'1',
				'1',
				'form-row-last',
				'2',
				'family-name',
				'text',
				'shipping',
				'default'
			) );

					// Insert shipping company
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'shipping_company',
				'Company Name',
				'Company Name',
				'0',
				'1',
				'form-row-wide',
				'3',
				'organization',
				'text',
				'shipping',
				'default'
			) );


					// Insert shipping country
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'shipping_country',
				'Country / Region',
				'Country',
				'1',
				'1',
				'form-row-wide address-field update_totals_on_change',
				'4',
				'country',
				'select',
				'shipping',
				'default'
			) );


				// Insert shipping address1
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'shipping_address_1',
				'Street address',
				'House number and street name',
				'1',
				'1',
				'form-row-wide address-field',
				'5',
				'address-line1',
				'text',
				'shipping',
				'default'
			) );


					// Insert shipping address2
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s,%s)
				", 
				'shipping_address_2',
				'Address 2',
				'Apartment, suite, unit, etc.',
				'0',
				'1',
				'form-row-wide address-field',
				'6',
				'address-line2',
				'text',
				'shipping',
				'default'
			) );


					// Insert shipping city
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'shipping_city',
				'Town / City',
				'Town / City',
				'1',
				'1',
				'address-field form-row-wide ',
				'7',
				'address-level2',
				'text',
				'shipping',
				'default'
			) );

					// Insert shipping state
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'shipping_state',
				'State / County',
				'Select and option...',
				'1',
				'1',
				'address-field form-row-wide ',
				'8',
				'address-level1',
				'select',
				'shipping',
				'default'
			) );

						// Insert shipping postcode
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'shipping_postcode',
				'Postcode / Zip',
				'Postcode / Zip',
				'1',
				'1',
				'address-field form-row-wide',
				'9',
				'postal-code',
				'text',
				'shipping',
				'default'
			) );

				//haseeb changed Addtional Order Notes
			$wpdb->query($wpdb->prepare( "
				INSERT INTO $wpdb->FmeCCFA_fields
				(field_name, field_label, field_placeholder, is_required, is_enable, is_class, sort_order, autocomplete, field_type, type, field_mode)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
				", 
				'order_comments',
				'Order notes',
				'Notes about your order, e.g. special notes for delivery.',
				'0',
				'1',
				'form-row-wide',
				'1',
				'order-comments',
				'textarea',
				'additional',
				'default'
			) );

		}

		public function FME_CCFW_module_constants() {

			if ( !defined( 'FME_CCFW_URL' ) ) {
				define( 'FME_CCFW_URL', plugin_dir_url( __FILE__ ) );
			}

			if ( !defined( 'FME_CCFW_BASENAME' ) ) {
				define( 'FME_CCFW_BASENAME', plugin_basename( __FILE__ ) );
			}

			if ( ! defined( 'FME_CCFW_PLUGIN_DIR' ) ) {
				define( 'FME_CCFW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}
		}


		public function fme_ccfw_deactivate( $upgrader_object, $options ) {
			// The path to our plugin's main file
			$our_plugin = plugin_basename( __FILE__ );
			// If an update has taken place and the updated type is plugins and the plugins element exists
			if ( 'update' == $options['action'] &&  'plugin' == $options['type'] && isset( $options['plugins'] ) ) {
					
				// Iterate through the plugins being updated and check if ours is there
				foreach ( $options['plugins'] as $plugin ) {
					if ( $plugin == $our_plugin ) {
						

						$plugin_data=get_plugin_data(FME_CCFW_PLUGIN_DIR . 'conditional-checkout-fields-for-woocommerce.php');
						$fme_version=$plugin_data['Version'];
						
						$obj = new FME_CCFW_MAIN();
						$obj->fme_install_module();
					}
				}
			}
		}


	} 

	new FME_CCFW_MAIN();
}
