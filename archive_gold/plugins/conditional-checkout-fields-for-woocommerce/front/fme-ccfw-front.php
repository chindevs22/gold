<?php

if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

if ( !class_exists( 'Fme_Ccffw_Front' ) ) { 

	class Fme_Ccffw_Front { 

		public function __construct() {


			add_action( 'wp_enqueue_scripts', array( $this, 'fme_front_scripts' ) );
			add_filter( 'woocommerce_form_field' , array($this,'fme_ccfw_remove_checkout_optional_text'), 10, 4 );
			add_filter( 'woocommerce_checkout_get_value' , array($this,'fme_ccfw_clear_checkout_fields') , 10, 2 );	
			add_action( 'wp_footer', array($this,'fme_ccfw_conditionally_hide_show_checkout_field'), 9999 );
			add_action( 'woocommerce_default_address_fields', array( $this, 'fme_ccfw_woocommerce_default_address_fields' ), 9999 );
			add_action( 'woocommerce_checkout_update_order_meta', array($this,'fme_ccfw_checkout_field_update_order_meta' ));
			add_filter( 'woocommerce_order_get_formatted_billing_address', array($this, 'fme_after_billing_order_data'), 10, 4);
			add_filter( 'woocommerce_order_get_formatted_shipping_address', array($this, 'fme_after_shipping_order_data'), 10, 34);
			add_action( 'woocommerce_thankyou', array($this, 'fme_after_additional_order_data'), 20 );
			add_action( 'woocommerce_view_order', array($this, 'fme_after_additional_order_data'), 20 );
			add_action( 'woocommerce_email_customer_details', array($this, 'fme_ext_add_email_order_meta'), 10, 4 );
			if ( 'off' == get_option('fme_ccfw_paypal_status')) {
				add_action( 'woocommerce_billing_fields', array( $this, 'fmeccfwaddCustomFieldsBillingFields' ), 9999 );
				add_action( 'woocommerce_shipping_fields', array( $this, 'fmeccfwaddCustomFieldsShippingFields' ), 9999 );
				add_action( 'woocommerce_checkout_fields', array( $this, 'fmeccfwaddCustomFieldsOrderFields' ), 9999 );
			} else {
				add_action('wp_head', array($this, 'render_checkout_page_fields'));
			}
			
			add_action( 'woocommerce_cart_calculate_fees', array($this, 'fme_ccfw_ext_woo_add_cart_fee' ));
			
		}
		public function render_checkout_page_fields() {
			add_action( 'woocommerce_billing_fields', array( $this, 'fmeccfwaddCustomFieldsBillingFields' ), 9999 );
			add_action( 'woocommerce_shipping_fields', array( $this, 'fmeccfwaddCustomFieldsShippingFields' ), 9999 );
			add_action( 'woocommerce_checkout_fields', array( $this, 'fmeccfwaddCustomFieldsOrderFields' ), 9999 );

		}
		
		public function fmeccfwaddCustomFieldsBillingFields( $fields ) {
			return $this->getCheckoutcustomFields( $fields, 'billing' );
		}

		public function fmeccfwaddCustomFieldsShippingFields( $fields ) {
			return $this->getCheckoutcustomFields( $fields, 'shipping' );
		}

		public function fmeccfwaddCustomFieldsOrderFields( $fields ) {
			return $this->getCheckoutcustomFields( $fields, 'additional' );
		}

		public function fme_get_fields_price() {
			global $wpdb;
			$fme_result = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE is_enable = 1');
			return $fme_result;
		}

		public function fme_ccfw_ext_woo_add_cart_fee( $cart ) {

			if ( ! $_REQUEST || ( is_admin() && ! is_ajax() ) ) {
				return;
			}

			$fme_billing_price_fields = $this->fme_get_fields_price();
			
			if (isset( $_REQUEST['post_data'] )) {
				parse_str(filter_var($_REQUEST['post_data']), $post_data );
			} else {
				$post_data = $_REQUEST;
			}

			$arr = array();
			if (!empty($fme_billing_price_fields)) {

				foreach ($fme_billing_price_fields as $key => $bprice_field) {
						
					if ( isset($post_data['fme_ccfw_price_' . $bprice_field->field_name]) && '' != $post_data['fme_ccfw_price_' . $bprice_field->field_name] ) {

						//haseeb changed
						$tax=false;
						if ('1'== $bprice_field->is_taxable) {
							$tax=true;
						}
						if ('file' == $bprice_field->field_type) {

							$option_price =  $post_data['fme_ccfw_price_' . $bprice_field->field_name];
							$option_text =  basename($post_data[$bprice_field->field_name]);
							if ( '' != $option_text && '' != $option_price ) {
								if (0 != $option_text || '0' != $option_text) {
									WC()->cart->add_fee( 
										wordwrap( __(
									 $bprice_field->field_label,
										'conditional-checkout-fields-for-woocommerce') . '(' . $option_text . ')', 15, "\n", true), $option_price , $tax
									);
								}	
							}

						} else if ('multiselect'==$bprice_field->field_type) {

							$option_text = $post_data['multiple_' . $bprice_field->field_name];
							if (!empty($option_text)) {
								$jsonData = stripslashes(html_entity_decode($option_text));
								$jsonData = json_decode($jsonData);
								if (!empty($jsonData)) {
									foreach ($jsonData as $key => $value1) {
										if ( '' != $value1 ) {
											$option_price =  $value1->value;
											if (!empty($option_price)) {
												if (0 != $value1->key || '0' != $value1->key) {
													WC()->cart->add_fee( 
														__(
															$bprice_field->field_label,
															'conditional-checkout-fields-for-woocommerce') . '(' . $value1->key . ')', $option_price, $tax );
												}
											}	
										}
									}
								}
							}
					
						} else {

							$option_price =  $post_data['fme_ccfw_price_' . $bprice_field->field_name];
							$option_text =  $post_data[$bprice_field->field_name];
							if ( '' != $option_text && '' != $option_price) {
								if (0 != $option_text || '0' != $option_text) {
									WC()->cart->add_fee( 
										__(
										$bprice_field->field_label,
										'conditional-checkout-fields-for-woocommerce') . '(' . $option_text . ')', $option_price, $tax
									);	
								
								}
							}
						}

					}	
				}
			}
		}

		public function fme_ext_add_email_order_meta( $order, $sent_to_admin, $plain_text, $email) {
			$order_id = $order->get_id();
			$array = array(__('billing', 'conditional-checkout-fields-for-woocommerce'), __('shipping', 'conditional-checkout-fields-for-woocommerce'), __('additional', 'conditional-checkout-fields-for-woocommerce'));
			$array_orignal = array('billing','shipping','additional');
			$output = '<table style="width:100%"><tbody><tr style="width:100%;display:inline-flex;">';
			$fme_index=0;
			foreach ($array as $key => $values) {
				$fme_order_fields_data = $this->getBillingemailFields($array_orignal[$key]);
				if ( 2 == $fme_index) {
					$output .= '</tr><tr>';
				}
				
				if (!empty($fme_order_fields_data)) {
					$fme_index++;
					$output .= "<td style='width:50%;padding: 12px;color: #636363;border: 1px solid #e5e5e5;'>";
					if (''!= $values) {
						$output .='<h2><strong>' . ucfirst($values) . __(' fields', 'conditional-checkout-fields-for-woocommerce' ) . '</strong></h2>';
					}	
					foreach ($fme_order_fields_data as $key => $value) {

						if ('file' == $value->field_type) {
							$billing_file_url = get_post_meta( $order_id, '_' . $value->field_name . 'fme' . $value->type . '', true );
							if (!empty($billing_file_url)) {
								$url = wp_upload_dir()['baseurl'] . '/' . $billing_file_url;
								
								$output .= '<strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ': </strong><a href=' . $url . ' target="_blank"><button>view file</button></a>';
								$output .='<br>';
							}
						} else if ('multiselect' == $value->field_type) { 
							$multiselect_value = get_post_meta( $order_id, '_multiple_' . $value->field_name . 'fme' . $value->type . '', true );
							if (!empty($multiselect_value)) {
								$jsonData = stripslashes(html_entity_decode($multiselect_value));
								$jsonData = json_decode($jsonData, true);
								$multiple_arr = array();
								foreach ($jsonData as $key => $multiple_) {
									array_push($multiple_arr , $multiple_['key']);
								}

								if (!empty($multiple_arr)) {
									$output .= '<strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ': </strong>' . implode(',', $multiple_arr);
									$output .='<br>';
								}
							}
						} else {
							$billing_value = get_post_meta( $order_id, '_' . $value->field_name . 'fme' . $value->type . '', true );
							if (!empty($billing_value)) {
								$output .= '<strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ': </strong>' . $billing_value;
								$output .='<br>';
							}
						}
					}
				}
				$output .= '</td>';
			}
			$output .= '</tr></tbody></table>';
			echo filter_var($output);

		}

		public function fme_ccfw_checkout_field_update_order_meta( $order_id ) {

			foreach ($_REQUEST as $key => $value) {
				$er = $this->fme_getBilling($key);
				if ('' != $er) {
					if ( ! empty( $_REQUEST[$key] ) && 'billing_additional' == $er->field_mode ) {

						if ( isset($_REQUEST['multiple_' . $key]) && 'multiselect' == $er->field_type && '0' != $value  ) {
							update_post_meta($order_id, '_multiple_' . $er->field_name . 'fmebilling', map_deep( wp_unslash( $_REQUEST['multiple_' . $key] ), 'sanitize_text_field' ) );
						} else {

							if (is_array($_REQUEST[$key])) {

								if (isset($_REQUEST[$key][0])) {

									update_post_meta( $order_id, '_' . $er->field_name . 'fmebilling', filter_var($_REQUEST[$key][0]));
								}
							} else {
								update_post_meta( $order_id, '_' . $er->field_name . 'fmebilling', filter_var($_REQUEST[$key]) );
							}
						}
					}
				}
			}

			foreach ($_REQUEST as $key => $value) {
				$er = $this->fme_getShipping($key);
				if ('' != $er) {
					if ( ! empty( $_REQUEST[$key] ) && 'shipping_additional' == $er->field_mode ) {

						if ( isset($_REQUEST['multiple_' . $key]) && 'multiselect' == $er->field_type && '0' != $value  ) {
							update_post_meta($order_id, '_multiple_' . $er->field_name . 'fmeshipping', map_deep( wp_unslash( $_REQUEST['multiple_' . $key] ), 'sanitize_text_field' ) );
						} else {

							if (is_array($_REQUEST[$key])) {

								if (isset($_REQUEST[$key][0])) {

									update_post_meta( $order_id, '_' . $er->field_name . 'fmeshipping', filter_var($_REQUEST[$key][0]));
								}
							} else {
								update_post_meta( $order_id, '_' . $er->field_name . 'fmeshipping', filter_var($_REQUEST[$key]) );
							}
						}
					}
				}
			}


			foreach ($_REQUEST as $key => $value) {
				$er = $this->fme_getAdditional($key);
				if ('' != $er) {
					if ( ! empty( $_REQUEST[$key] ) && 'additional_additional' == $er->field_mode ) {

						if ( isset($_REQUEST['multiple_' . $key]) && 'multiselect' == $er->field_type && '0' != $value  ) {
							update_post_meta($order_id, '_multiple_' . $er->field_name . 'fmeadditional', map_deep( wp_unslash( $_REQUEST['multiple_' . $key] ), 'sanitize_text_field' ) );
						} else {

							if (is_array($_REQUEST[$key])) {

								if (isset($_REQUEST[$key][0])) {

									update_post_meta( $order_id, '_' . $er->field_name . 'fmeadditional', filter_var($_REQUEST[$key][0]));
								}
							} else {
								update_post_meta( $order_id, '_' . $er->field_name . 'fmeadditional', filter_var($_REQUEST[$key]) );
							}
						}
					}
				}
			}
		}


		public function fme_getAdditional( $fme_name ) {
			global $wpdb;
			$fme_result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_name = %s order by sort_order asc', $fme_name));      
			return $fme_result;
		}

		public function fme_getShipping( $fme_name ) {
			global $wpdb;
			$fme_result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_name = %s order by sort_order asc', $fme_name));      
			return $fme_result;
		}

		public function getBillingemailFields( $type) {

			global $wpdb;
			$fme_result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_mode = %s and is_enable = 1 order by sort_order asc', $type . '_additional'));      
			return $fme_result;

		}
		public function fme_ccfw_getFields( $type) {

			global $wpdb;
			$fme_result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE type = %s AND field_mode = %s  order by sort_order asc', $type , $type . '_additional'));  
			return $fme_result; 

		}
		public function fme_after_billing_order_data( $fme_address, $raw_address, $order) {

			$fme_billing_fields = $this->fme_ccfw_getFields('billing');
			$order_id = $order->get_id();
			if ( is_wc_endpoint_url( 'order-received' ) || is_account_page()) {
				$this->fme_ccfw_get_order_data($fme_billing_fields, $order_id);
			} 
			return $fme_address;
		}

		public function fme_after_shipping_order_data( $fme_address, $raw_address, $order) {

			$fme_shipping_fields = $this->fme_ccfw_getFields('shipping');
			$order_id = $order->get_id();
			if ( is_wc_endpoint_url( 'order-received' ) || is_account_page() ) {
				$this->fme_ccfw_get_order_data($fme_shipping_fields, $order_id);
			} 
			return $fme_address;
		}

		
		public function fme_after_additional_order_data( $order_id ) {

			$fme_additional_fields = $this->fme_ccfw_getFields('additional');
			$additionalLabelval = get_option('additionalfieldlabel');
			if ('' == $additionalLabelval) {
				$addlabel = 'Additional Fields';
			} else {
				$addlabel = $additionalLabelval;
			}
			if (!empty($fme_additional_fields)) {
				echo '<h2>' . esc_html__(sanitize_text_field( $addlabel ), 'conditional-checkout-fields-for-woocommerce') . '</h2>';
			}
			if ( is_wc_endpoint_url( 'order-received' ) ) {
				$this->fme_ccfw_get_order_data($fme_additional_fields, $order_id);
			} else if (is_account_page()) {
				$this->fme_ccfw_get_order_data($fme_additional_fields, $order_id);
			}
		}

		public function fme_ccfw_get_order_data( $fme_order_fields, $order_id) {
			$output = '';
			$output .= '<p>';
			foreach ($fme_order_fields as $key => $value) {
				if ('multiselect' == $value->field_type) { 
					$multiselect_value = get_post_meta( $order_id, '_multiple_' . $value->field_name . 'fme' . $value->type . '', true );
					if (!empty($multiselect_value)) {
						$jsonData = stripslashes(html_entity_decode($multiselect_value));
						$jsonData = json_decode($jsonData, true);
						$multiple_arr = array();
						foreach ($jsonData as $key => $multiple_) {
							array_push($multiple_arr , $multiple_['key']);
						}

						if (!empty($multiple_arr)) {
							$output .= '<br><strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong> ' . implode(',', $multiple_arr);
						}
					}
				} else if ('file' == $value->field_type) {

					$billing_file_url =  get_post_meta( $order_id, '_' . $value->field_name . 'fme' . $value->type . '', true );
					$imgExts = array('gif', 'jpg', 'jpeg', 'png', 'tiff', 'tif');
					$url = wp_upload_dir()['baseurl'] . '/' . $billing_file_url;
					
					$url = str_replace(' ', '', $url);
					$urlExt = pathinfo($url, PATHINFO_EXTENSION);
					$urlExt = strtolower($urlExt);
					$selectfilename = basename(parse_url($url, PHP_URL_PATH));
					if (in_array($urlExt, $imgExts)) {
						if (!empty($billing_file_url)) {
							$output .= '<br><strong><p style="display:inline-block">' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong> <a href=' . $url . ' target="_blank" ><img class="fme_image_ccfw" src=' . $url . ' alt="image" title="Click to View"/ style="border: 1px solid #ddd;border-radius: 4px;padding: 5px;width: 150px;cursor:pointer;"></a><a href=' . $url . ' target="_blank" download=' . $selectfilename . '><button title="Download"/ class="btn" style="width: 100%;margin-top: 3%"><i class="fa fa-download"></i> Download</button></a></p>';
							?>
							<style type="text/css">
								.fme_image_ccfw:hover {
									box-shadow: 0 0 2px 1px rgba(0, 140, 186, 0.5);
									transform: scale(1.01);
								}
							</style>
							<?php
						}
					} else if ('pdf' == $urlExt) {

						if (!empty($billing_file_url)) {

							$output .= '<br><strong><p style="display:inline-block">' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong> <a href=' . $url . ' target="_blank" ><img class="fme_image_ccfw" src="https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg" alt="image" title="Click to View"/ style="border: 1px solid #ddd;border-radius: 4px;padding: 5px;width: 80px;cursor:pointer;"></a><a href=' . $url . ' target="_blank" download=' . $selectfilename . '><button title="Download"/ class="btn" style="width: 100%;margin-top: 3%"><i class="fa fa-download"></i> Download</button></a></p>';
						}

					} else {

						if (!empty($billing_file_url)) {
							$output .= '<br><strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong> <a href=' . $url . ' target="_blank" download=' . $selectfilename . '><button title="Download"/ class="btn"><i class="fa fa-download"></i> Download</button></a>';
						}
					}

				} else if ('color' == $value->field_type) {

					$fme_ccfw_color = get_post_meta( $order_id, '_' . $value->field_name . 'fme' . $value->type . '', true );
					if (!empty($fme_ccfw_color)) {
						$output .= '<br><strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong> <span class="color-picker"><label for="colorPicker"><input type="color" disabled value=' . $fme_ccfw_color . ' id="colorPicker"></label> ' . $fme_ccfw_color . '</span>';
					}
				} else if ('password' == $value->field_type) {

					$fme_ccfw_password = get_post_meta( $order_id, '_' . $value->field_name . 'fme' . $value->type . '' , true );
					if (!empty($fme_ccfw_password)) {
						$output .= '<br><strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong> <input type="password" disabled value=' . $fme_ccfw_password . ' id=' . $value->field_name . ' style="background-color:white;box-shadow:unset;"><span class="fa fa-fw fa-eye field-icon toggle-password" onclick="myFunction(' . "'" . $value->field_name . "'" . ');" style="cursor:pointer;"></span>';
						?>
						<script type="text/javascript">
							function myFunction(fmeviewpid) {
								var x = jQuery('#'+fmeviewpid);
								if (x.attr('type') === "password") {
									jQuery(x).attr('type','text');
									jQuery(x).next().css('color','lightgray');
								} else {
									jQuery(x).attr('type','password');
									jQuery(x).next().css('color','unset');
								}
							}
						</script>	
						<?php
					}

				} else {
					$fmeorder_value = get_post_meta( $order_id, '_' . $value->field_name . 'fme' . $value->type . '', true );
					if (!empty($fmeorder_value)) {
						$output .= '<br><strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ': </strong> ' . $fmeorder_value;
					}
				}
			}
			$output.= '</p>';
			echo filter_var($output);

		}


		public function fme_getBilling( $fme_name ) {
			global $wpdb;
			$fme_result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_name = %s', $fme_name));      
			return $fme_result;
		}


	
		public function fme_ccfw_clear_checkout_fields( $value, $input ) {
			//haseeb changed order_comments added in array
			$fme_default_text_fields = array( 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_phone', 'billing_country' ,'shipping_first_name', 'shipping_last_name','shipping_company', 'shiping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_country', 'order_comments');
			if ( ! in_array( $input, $fme_default_text_fields ) ) { 
				$value = ''; 
			}
			return $value;
		}


		public function fme_ccfw_woocommerce_default_address_fields( $fields ) {
			if ( is_checkout() || is_account_page() ) {
				foreach ( $fields as $key => $field ) {
					unset( $fields[ $key ]['priority'] );
				}
			}
			return $fields;
		}


		public function fme_ccfw_conditionally_hide_show_checkout_field() {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('.fme-ccfw_class_main').each(function(){
						call_it(this);
					});
					jQuery('.fme-ccfw_class_main').on('input change' , function() {			
						call_it(this);
						jQuery(document.body).trigger("update_checkout");	
					});
					jQuery('input[type="file"]').change(function() {
						call_it(this);
						jQuery(document.body).trigger("update_checkout");
					});

					function call_it(thiss){
						
						var current_inputted_elem_type=jQuery(thiss).attr('type');
						if (!current_inputted_elem_type) {
							if (jQuery(thiss).is('select')) {
								current_inputted_elem_type='select';
							}
						}
						
						if (jQuery(thiss).attr('type')=='radio') {
							var current_inputted_elem_val=jQuery('input[name='+jQuery(thiss).attr('name')+']:checked').val();
						} else if (jQuery(thiss).attr('type')=='checkbox'){
							var current_inputted_elem_val=jQuery(thiss).prop('checked');
						} else if(jQuery(thiss).is('select')){
							var current_inputted_elem_val=jQuery(thiss).val();
							if (''==current_inputted_elem_val[0]) {
								current_inputted_elem_val.shift();
							}
						}

						 else {
							var current_inputted_elem_val=jQuery(thiss).val();
							
						}
						
						var current_inputted_elem_id=jQuery(thiss).attr('id');
						if (jQuery(thiss).attr('type')=='radio'){
							current_inputted_elem_id=jQuery(thiss).attr('name');
						}
						if (jQuery(thiss).attr('type')=='file'){
							
							current_inputted_elem_id=current_inputted_elem_id.replace('fme_','');
							

						}
						jQuery('.fme-ccfw_class_main').each(function(){
							var all_rules_on_this_elem=jQuery(this).attr('data_check_values');
							

							if ('undefined' !== typeof(all_rules_on_this_elem)) {
								
								
								all_rules_on_this_elem=JSON.parse(all_rules_on_this_elem);
								
								for (var i = 0; i < all_rules_on_this_elem.length; i++) {
										
									for (var j = 0; j < all_rules_on_this_elem[i].length; j++) {
										

										if (all_rules_on_this_elem[i][j][1] == current_inputted_elem_id) {
											var checkcase = '';
											var condition_issss=all_rules_on_this_elem[i][j][2];
											if (condition_issss=='is_equal_to'){
												condition_issss='==';
											} else if (condition_issss=='is_not_equal_to'){
												condition_issss='!=';
											} else if (condition_issss=='is_checked'){
												condition_issss='checked';
											} else if (condition_issss=='is_not_empty'){
												condition_issss='!empty';
											} else if (condition_issss=='is_empty'){
												condition_issss='empty';
											}
											var fme_is_true = false;


											if ( condition_issss == 'checked' && current_inputted_elem_type == 'checkbox') {
												checkcase="'"+current_inputted_elem_val+"' " + condition_issss +" '"+all_rules_on_this_elem[i][j][3]+"'";
												
												if (jQuery('#'+all_rules_on_this_elem[i][j][1]).prop('checked')) {
													fme_is_true=true;
												}
											} else if ( condition_issss  == '!empty') {
												checkcase="'"+current_inputted_elem_val+"' " + condition_issss +" '"+all_rules_on_this_elem[i][j][3]+"'";
												
												if (current_inputted_elem_val) {
													fme_is_true=true;
												}
											} else if ( condition_issss  == 'empty') {
												checkcase="'"+current_inputted_elem_val+"' " + condition_issss +" '"+all_rules_on_this_elem[i][j][3]+"'";
												
												if (!current_inputted_elem_val) {
													fme_is_true=true;
												}
											} else if (condition_issss  == '==' || condition_issss  == '!=' ) {

												if (current_inputted_elem_val) {
													checkcase="'"+current_inputted_elem_val+"' " +condition_issss +" '"+all_rules_on_this_elem[i][j][3]+"'";
													fme_is_true=Function(`'use strict'; return (${checkcase})`)();
												}
											}
											
											if (fme_is_true){
												all_rules_on_this_elem[i][j][4]='T';
												jQuery(this).attr('data_check_values',JSON.stringify(all_rules_on_this_elem));
											} else {
												all_rules_on_this_elem[i][j][4]='F';
												jQuery(this).attr('data_check_values',JSON.stringify(all_rules_on_this_elem));
											}
										}
										
									}
								}
							}
						});

					
						jQuery('.fme-ccfw_class_main').each(function(){
							var all_rules_on_this_elem=jQuery(this).attr('data_check_values');

							if ('undefined' === typeof(all_rules_on_this_elem)) {
								return;
							}
							all_rules_on_this_elem=JSON.parse(all_rules_on_this_elem);
							for (var i = 0; i < all_rules_on_this_elem.length; i++) {
								var satisfied_count=0;
								var required_count=all_rules_on_this_elem[i].length;
								for (var j = 0; j < all_rules_on_this_elem[i].length; j++) {

									if (all_rules_on_this_elem[i][j][4]=='T') {
										satisfied_count++;
									}
								}
							
								if (satisfied_count>=required_count){

									if (all_rules_on_this_elem[i][0][0] == 'Show'){
										if (jQuery(this).attr('type')=='radio') {
											if(jQuery(this).attr('ischanged') == 'true') {
												jQuery('#'+jQuery(this).attr('name')+'_field').show();
												var radioname = jQuery(this).attr('name');
												var radios = jQuery("input:radio[name="+radioname+"]:first");
												radios.prop('checked', false);
												radios.hide();
												jQuery(this).attr('ischanged',false);
												jQuery(radios).trigger('change');	
											}
										} else {
											if (jQuery(this).is('select')) {
												if (jQuery(this).attr('ischanged') == 'true') {
													var selectoption = jQuery(this).attr('name');
													jQuery("#"+selectoption+" option:first").val('');
													jQuery("#"+selectoption).find('option:first').prop('selected',false);
													jQuery(this).attr('ischanged',false);	
												}

											} else if (jQuery(this).attr('type')=='checkbox') {
												
												if (jQuery(this).attr('ischanged') == 'true') {
													var checkbox = jQuery(this).attr('name');
													jQuery("#"+checkbox).prop('checked', false);
													jQuery(this).attr('ischanged',false);
													jQuery(this).change();
												}
												jQuery("#"+checkbox).prop('checked', false);		
											} else if (jQuery(this).attr('type')=='password' || jQuery(this).attr('type')=='tel') {

												if (jQuery(this).attr('ischanged') == 'true') {
													var val = jQuery(this).attr('fmeccfw-data-val-');
													jQuery(this).val(val);
				
													jQuery(this).attr('ischanged',false);
												}		

											} else if (jQuery(this).attr('typee')=='file') {

												if (jQuery(this).attr('ischanged') == 'true') {
													var input = jQuery(this).attr('name');
													jQuery('#'+input).val('0');
													jQuery('#fme_label_'+input).html('');
													jQuery('#fme_'+input).val('');
													jQuery(this).attr('ischanged',false);
												}
											}  else {

												if (jQuery(this).attr('ischanged') == 'true') {
													var val = jQuery(this).attr('fmeccfw-data-val-');
													jQuery(this).val(val);
													jQuery(this).attr('ischanged',false);
												}		
											}	
											
											jQuery('#'+jQuery(this).attr('id')+'_field').show();	
										}
									} else {
										if (jQuery(this).attr('type')=='radio') {
											if (jQuery(this).attr('ischanged') == 'false') {
												jQuery('#'+jQuery(this).attr('name')+'_field').hide();
												var radioname = jQuery(this).attr('name');
												var radios = jQuery("input:radio[name="+radioname+"]:first");
												radios.prop('checked', true);
												radios.hide();
												jQuery(this).attr('ischanged',true);
												jQuery(radios).trigger('change');
											}

										} else {
											if (jQuery(this).is('select')) {
												if (jQuery(this).attr('ischanged') == 'false') {
													var selectoption = jQuery(this).attr('name');
													jQuery("#"+selectoption+" option:first").val('0');
													jQuery("#"+selectoption).find('option:first').prop('selected',true);
													jQuery(this).attr('ischanged',true);

												}
												jQuery("#"+selectoption+" option:first").val('0');
												jQuery("#"+selectoption).find('option:first').prop('selected',true);
												jQuery('#'+selectoption).val('0'); // Select the option with a value of '1'
												jQuery('#'+selectoption).trigger('change'); 

											} else if (jQuery(this).attr('type')=='checkbox') {
										
												if (jQuery(this).attr('ischanged') == 'false') {
													var checkbox = jQuery(this).attr('name');
													jQuery("#"+checkbox).prop('checked', true);
													jQuery(this).attr('ischanged',true);
													jQuery(this).change();
												}		
												jQuery("#"+checkbox).attr('value','0');
												jQuery("#"+checkbox).val('0');										
											} else if (jQuery(this).attr('type')=='password' || jQuery(this).attr('type')=='tel') {
												if (jQuery(this).attr('ischanged') == 'false') {

													var val = jQuery(this).val();
													var val2 = jQuery(this).attr('fmeccfw-data-val-',val);

													var input = jQuery(this).attr('name');
													jQuery("#"+input).attr('value', '0');
													jQuery("#"+input).val('0');
													jQuery(this).attr('ischanged',true);
												}
											} else if (jQuery(this).attr('typee')=='file') {

												if (jQuery(this).attr('ischanged') == 'false') {

													var input = jQuery(this).attr('name');
													jQuery('#'+input).val('0');
													jQuery('#fme_label_'+input).html('');
													jQuery('#fme_'+input).val('');
													jQuery(this).attr('ischanged',true);
												}
											} else {
												if (jQuery(this).attr('ischanged') == 'false') {

													var val = jQuery(this).val();
													var val2 = jQuery(this).attr('fmeccfw-data-val-',val);

													var input = jQuery(this).attr('name');
													jQuery("#"+input).attr('value', '0');
													jQuery("#"+input).val('0');
													jQuery(this).attr('ischanged',true);
												}	
											}
											
											jQuery('#'+jQuery(this).attr('id')+'_field').hide();	
										}
									}
									break;

								} else {

									if (all_rules_on_this_elem[i][0][0] == 'Show'){
										if (jQuery(this).attr('type')=='radio') {
											if (jQuery(this).attr('ischanged') == 'false') {
												jQuery('#'+jQuery(this).attr('name')+'_field').hide();
												var radioname = jQuery(this).attr('name');
												var radios = jQuery("input:radio[name="+radioname+"]:first");
												radios.prop('checked', true);
												radios.hide();
												jQuery(this).attr('ischanged',true);
												jQuery(radios).trigger('change');
											}

										} else {
											if (jQuery(this).is('select')) {
												if (jQuery(this).attr('ischanged') == 'false') {
													var selectoption = jQuery(this).attr('name');
													jQuery("#"+selectoption+" option:first").val('0');
													jQuery("#"+selectoption).find('option:first').prop('selected',true);
													jQuery(this).attr('ischanged',true);
												}

												jQuery("#"+selectoption+" option:first").val('0');
												jQuery("#"+selectoption).find('option:first').prop('selected',true);
												jQuery('#'+selectoption).val('0'); // Select the option with a value of '1'
												jQuery('#'+selectoption).trigger('change'); 

											} else if (jQuery(this).attr('type')=='checkbox') {
												
												if (jQuery(this).attr('ischanged') == 'false') {
													var checkbox = jQuery(this).attr('name');
													jQuery("#"+checkbox).prop('checked', true);
													jQuery(this).attr('ischanged',true);
													jQuery("#"+checkbox).attr('value','0');
													jQuery("#"+checkbox).val('0');
													jQuery(this).change();
													
												}	
												jQuery("#"+checkbox).attr('value','0');
												jQuery("#"+checkbox).val('0');								
											} else if (jQuery(this).attr('type')=='password' || jQuery(this).attr('type')=='tel') {

												if (jQuery(this).attr('ischanged') == 'false') {

													var val = jQuery(this).val();
													var val2 = jQuery(this).attr('fmeccfw-data-val-',val);

													var input = jQuery(this).attr('name');
													jQuery("#"+input).attr('value','0');
													jQuery("#"+input).val('0');
													jQuery(this).attr('ischanged',true);					
												}		

											} else if (jQuery(this).attr('typee')=='file') {
												if (jQuery(this).attr('ischanged') == 'false') {

													var input = jQuery(this).attr('name');
													jQuery('#'+input).val('0');
													jQuery('#fme_label_'+input).html('');
													jQuery('#fme_'+input).val('');
													jQuery(this).attr('ischanged',true);
												}
												
											}  else {

												if (jQuery(this).attr('ischanged') == 'false') {
													
													var val = jQuery(this).val();
													var val2 = jQuery(this).attr('fmeccfw-data-val-',val);

													var input = jQuery(this).attr('name');
													jQuery("#"+input).attr('value','0');
													jQuery("#"+input).val('0');
													jQuery(this).attr('ischanged',true);					

												}

											}
											jQuery('#'+jQuery(this).attr('id')+'_field').hide();	
										}

									}else {
										if (jQuery(this).attr('type')=='radio') {
											if (jQuery(this).attr('ischanged') == 'true') {
												jQuery('#'+jQuery(this).attr('name')+'_field').show();
												var radioname = jQuery(this).attr('name');
												var radios = jQuery("input:radio[name="+radioname+"]:first");
												radios.prop('checked', false);
												radios.hide();
												jQuery(this).attr('ischanged',false);
												jQuery(radios).trigger('change');
											}	
										} else {
											if (jQuery(this).is('select')) {
												if (jQuery(this).attr('ischanged') == 'true') {
													var selectoption = jQuery(this).attr('name');
													jQuery("#"+selectoption+" option:first").val('');
													jQuery("#"+selectoption).find('option:first').prop('selected',false);
													jQuery(this).attr('ischanged',false);
												}
											} else if (jQuery(this).attr('type')=='checkbox') {
												
												if (jQuery(this).attr('ischanged') == 'true') {
													var checkbox = jQuery(this).attr('name');
													jQuery("#"+checkbox).prop('checked', false);
													jQuery(this).attr('ischanged',false);
													jQuery(this).change();
												}							
												jQuery("#"+checkbox).prop('checked', false);
											} else if (jQuery(this).attr('type')=='password' || jQuery(this).attr('type')=='tel') {

												if (jQuery(this).attr('ischanged') == 'true') {

													var val = jQuery(this).attr('fmeccfw-data-val-');
													jQuery(this).val(val);
													jQuery(this).attr('ischanged',false);
												}	
											} else if (jQuery(this).attr('typee')=='file') {
												if (jQuery(this).attr('ischanged') == 'true') {

													var input = jQuery(this).attr('name');
													jQuery('#'+input).val('');
													jQuery('#fme_label_'+input).html('');
													jQuery('#fme_'+input).val('');
													jQuery(this).attr('ischanged',false);
												}
											} else {
												if (jQuery(this).attr('ischanged') == 'true') {

													var val = jQuery(this).attr('fmeccfw-data-val-');
													jQuery(this).val(val);
													jQuery(this).attr('ischanged',false);
												}	
											}	
										
											jQuery('#'+jQuery(this).attr('id')+'_field').show();	
										}
									}
								}
							}
						});
					}
				});
			</script>
			<?php
		}


		public function fme_ccfw_remove_checkout_optional_text( $field, $key, $args, $value ) {

			if ( strpos( $field, '</label>' ) !== false && $args['required'] ) {
				$error = '<span class="error" style="display:none">';
				$error .= sprintf( __( $args['label'] . ' is a required field.', 'conditional-checkout-fields-for-woocommerce' ));
				$error .= '</span>';
				$field = substr_replace( $field, $error, strpos( $field, '</label>' ), 0);
			} 

			if ( is_checkout() && !is_wc_endpoint_url() ) {
				$optional = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'conditional-checkout-fields-for-woocommerce' ) . ')</span>';
				$field = str_replace( $optional, '', $field );
			}
			return $field;
		} 

		public function fme_front_scripts() {

			if (is_checkout()) {

				wp_enqueue_media();
				wp_enqueue_script('jquery');
				wp_enqueue_script('extCCFA-ui-script', 'https://code.jquery.com/ui/1.11.4/jquery-ui.js', false, 1.0);
				wp_enqueue_style('fme-ccfw-front', plugins_url( 'assets/css/fme-ccfw-style.css', __FILE__ ), false, 1.0);
				wp_enqueue_script('extCCFA_timepickrr_js', plugin_dir_url( __FILE__ ) . 'assets/js/wickedpicker.js', false , 1.0);
				wp_enqueue_script('extCCFA_fmain_js', plugin_dir_url( __FILE__ ) . 'assets/js/main.js', false , '1.3.1');
				wp_enqueue_style( 	'extCCFA-icon-css', plugins_url( 'assets/css/wickedpicker.css', __FILE__ ), false , 1.0);
				wp_enqueue_script( 	'extCCFA_color_spectrum_js', plugins_url( 'assets/js/color_spectrum.js', __FILE__ ), array('jquery'), 1.0);
				wp_enqueue_style( 	'extCCFA_color_spectrum_css', plugins_url( 'assets/css/color_spectrum.css', __FILE__ ), false , 1.0);
				
				$additionalLabelval = get_option('additionalfieldlabel');
				$fme_additional_fields = $this->fme_ccfw_getFields('additional');
				$additional_field_exist=0;
				if (array_search(1, array_column($fme_additional_fields, 'is_enable')) !== false) {
					$additional_field_exist=1;
	
				}
				if ('' == $additionalLabelval) {
					$addlabel = 'Additional Fields';
				} else {
					$addlabel = $additionalLabelval;
				}
				$fme_ccfw_data = array(
					'additionalLabelvalue' => $addlabel,
					'additional_field_exist' => $additional_field_exist
				);
				wp_localize_script('extCCFA_fmain_js', 'fme_ccfw_php_front_vars', $fme_ccfw_data);	
			}
		}

		
		public function getCheckoutcustomFields( $fme_billing_fields, $request_type = null ) {

			$fme_bfields = $this->fme_get_billing_fields($request_type);
			$fme_i = 0;
			$fme_user_role = wp_get_current_user()->roles;
			//haseeb changed order_comments added in array
			if ('billing'== $request_type) {
				$fme_default_text_fields = array( 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_phone', 'billing_country', 'order_comments' );
			} else if ('shipping'== $request_type) {
				$fme_default_text_fields = array( 'shipping_first_name', 'shipping_last_name','shipping_company', 'shiping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_country' );
			} else if ('additional'== $request_type) { //haseeb changed
				$fme_default_text_fields = array( 'order_comments' );
			} else {
				$fme_default_text_fields = array();	
			}

		
			foreach ($fme_bfields as $bfield) {
				
				if ('' != $bfield->cfield) {
					$fme_apply_conditional_field = unserialize($bfield->cfield); 					
				}	
				$fme_user_role_checked = $bfield->userrole_check;
				$fme_ischeckpc = $bfield->ischeckpc;

				if ( 'fme_display_user_role' == $fme_user_role_checked ||  'fme_hide_user_role' == $fme_user_role_checked) {

					$fme_roles= unserialize($bfield->specific_user_role);
					
					if (empty($fme_roles)) {

						$fme_valid= true;
					} else {

						if (!empty($fme_roles)) {
							$fme_roles= $fme_roles;
							if ( 'fme_display_user_role' == $fme_user_role_checked && empty( array_intersect($fme_roles, $fme_user_role))) {
								continue;
							} elseif ( 'fme_hide_user_role' == $fme_user_role_checked && !empty( array_intersect($fme_roles, $fme_user_role))) {
								
								if ('additional' == $request_type) {
									unset($fme_billing_fields['order'][$bfield->field_name]);
								} else {
									unset($fme_billing_fields[$bfield->field_name]);
								}
								continue;
							} else {
								$fme_valid = true;
							}	
						} else {
							$fme_valid = true;
						}
					}
				} else {

					if (!isset($fme_user_role_checked)) {
						
						$fme_valid = true;
					}
				}
				if ('on'==$fme_ischeckpc) {

					if ('' == $bfield->specific_pc) {
						$fme_valid= true;
					} else {

						if ('product'== $bfield->specific_pc) {

							$fme_selectedpro = unserialize($bfield->selected_pc);
							if ('' != $fme_selectedpro || !empty($fme_selectedpro)) {
								$fme_productcategory= $fme_selectedpro;
							} else {
								$fme_productcategory= '';
							}
							$fme_valid = false;
							$fme_cart_ids= array();
							foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
								$fme_product_id = $cart_item['product_id'];
								array_push($fme_cart_ids, $fme_product_id);
							}
							if (''==$fme_productcategory || empty($fme_selectedpro)) {
								$fme_valid = true;
							} else {
								if (!empty( array_intersect($fme_cart_ids, $fme_productcategory)) ) {
									$fme_valid = true;
								} 
							}
						} else if ('category' == $bfield->specific_pc) {

							$fme_selectedcat = unserialize($bfield->selected_pc);
							if ('' != $fme_selectedcat || !empty($fme_selectedcat)) {
								$fme_productcategory = $fme_selectedcat;
							} else {
								$fme_productcategory= '';
							}

							$fme_valid = false;
							$fme_cart_ids= array();
							foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
								$fme_product_id = $cart_item['product_id'];
								$terms = get_the_terms ($fme_product_id, 'product_cat' );

								foreach ($terms as $key => $term) {
									array_push($fme_cart_ids, $term->fme_parent_id);
									array_push($fme_cart_ids, $term->term_id);	

								}
								
							}
							if ('' == $fme_productcategory || empty($fme_productcategory)) {
								$fme_valid = true;
							} else {
								if (!empty( array_intersect($fme_cart_ids, $fme_productcategory)) ) {
									$fme_valid = true;
									
								} 
							}
						}
					}

				} else {
					$fme_valid = true;
				}			
						
				if (false == $fme_valid) {
					continue;
				}


				if (is_checkout()) {
					// If the country fields are disabled from the backend then the shipping fee calculation won't work properly, that's why the "shipping and billing country" clause added in if condition
					if (1 == $bfield->is_enable || 'billing_country' == $bfield->field_name || 'shipping_country' == $bfield->field_name) {
						
						if ('billing_country' == $bfield->field_name && 0 == $bfield->is_enable) {
							?>
							<style type="text/css">
								#billing_country_field{
									display: none !important;
								}
							</style>
							<?php
						} elseif ('shipping_country' == $bfield->field_name && 0 == $bfield->is_enable) {
							?>
							<style type="text/css">
								#shipping_country_field{
									display: none !important;
								}
							</style>

							<?php
							
						}

						if ( !empty( unserialize($bfield->cfield ) ) ) {
							?>
							<script type="text/javascript">
								jQuery(document).ready(function(){
									var thiss = jQuery('input[name="<?php echo esc_attr($bfield->field_name); ?>"]');
									if(!thiss.attr('type')) {
										thiss = jQuery('#<?php echo esc_attr($bfield->field_name); ?>');
									}
									jQuery(thiss).attr('data_check_values','<?php echo json_encode($fme_apply_conditional_field); ?>');
								});
							</script>
							<?php
						} else {
							?>
							<script type="text/javascript">
							jQuery(document).ready(function(){
								var thiss = jQuery('input[name="<?php echo esc_attr($bfield->field_name); ?>"]');
								
								if(!thiss.attr('type')) {
									thiss = jQuery('#<?php echo esc_attr($bfield->field_name); ?>');
								}
							});
						</script>
							<?php

						}
						
						?>
						<script type="text/javascript">
							jQuery(document).ready(function(){
								var thiss = jQuery('input[name="<?php echo esc_attr($bfield->field_name); ?>"]');
								if(!thiss.attr('type')) {
									thiss = jQuery('#<?php echo esc_attr($bfield->field_name); ?>');
								}
								jQuery(thiss).addClass('fme-ccfw_class_main');	
								jQuery('#<?php echo esc_attr($bfield->field_name); ?>').attr('ischanged',false);
							});
						</script>
						<?php
						if ('' != $bfield->field_price && 0 != $bfield->field_price) {
							$fme_currency = get_woocommerce_currency_symbol();
							$fprice = '(' . $fme_currency . $bfield->field_price . ')';
							?>
							<script type="text/javascript">
								jQuery(document).ready(function(){

									jQuery('#<?php echo esc_attr($bfield->field_name); ?>').after('<input type="hidden" name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>" value="">');
									jQuery('#<?php echo esc_attr($bfield->field_name); ?>').on('change',function() {
										var field = jQuery('input[name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>"]');
										field.attr('value',"<?php echo filter_var($bfield->field_price); ?>");
										jQuery('body').trigger('update_checkout');
									});
								});
							</script>	
							<?php

						} else {
							$fprice = '';
						}
						if ('default'== $bfield->field_mode) {

							if (in_array($bfield->field_name, $fme_default_text_fields)) {
								if ('additional' == $request_type) {

									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name]['label']= __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice;
									$fme_billing_fields[$type][$bfield->field_name]['class'] = explode(',', $bfield->is_class . ' ' . $bfield->field_id);
									$fme_billing_fields[$type][$bfield->field_name]['required'] 	= 0 == $bfield->is_required  ? 0 : 1;
									$fme_billing_fields[$type][$bfield->field_name]['placeholder'] = esc_html__($bfield->field_placeholder, 'conditional-checkout-fields-for-woocommerce');
									$fme_billing_fields[$type][$bfield->field_name]['priority'] = $bfield->sort_order * 10;
									$fme_billing_fields[$type][$bfield->field_name]['id'] =  $bfield->field_name;
								} else {
									$fme_billing_fields[$bfield->field_name]['label']= __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice;
									$fme_billing_fields[$bfield->field_name]['class'] = explode(',', $bfield->is_class . ' ' . $bfield->field_id);
									$fme_billing_fields[$bfield->field_name]['required'] 	= 0 == $bfield->is_required  ? 0 : 1;
									$fme_billing_fields[$bfield->field_name]['placeholder'] = esc_html__($bfield->field_placeholder, 'conditional-checkout-fields-for-woocommerce');
									$fme_billing_fields[$bfield->field_name]['priority'] = $bfield->sort_order * 10;
									$fme_billing_fields[$bfield->field_name]['id'] =  $bfield->field_name;
								}
							} 
						} else {
							if ('text'==$bfield->field_type || 'textarea'==$bfield->field_type || 'number'== $bfield->field_type 	||  'password'== $bfield->field_type || 'tel'== $bfield->field_type) {

								if ('additional' == $request_type) {

									$type = 'order';
									
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'placeholder'   => esc_html__($bfield->field_placeholder, 'conditional-checkout-fields-for-woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class'         => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'type'			=> $bfield->field_type,
										'priority'		=> $bfield->sort_order * 10,
									);
								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'placeholder'   => esc_html__($bfield->field_placeholder, 'conditional-checkout-fields-for-woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class'         => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'type'			=> $bfield->field_type,
										'priority'		=> $bfield->sort_order * 10
									);
								}

							} else if ('select'==$bfield->field_type ) {

								$selectboxopt = unserialize($bfield->options);
								$fme_sdata = [];
								$fme_sdata[''] = __('Choose an option', 'conditional-checkout-fields-for-woocommerce');	
								if (!empty($selectboxopt)) {	
									for ($i=0; $i < count($selectboxopt) ; $i++) { 
										$fme_sdata[$selectboxopt[$i][1]] = $selectboxopt[$i][0];
										?>
										<script type="text/javascript">
											jQuery(document).ready(function(){
												jQuery('#<?php echo esc_attr($bfield->field_name); ?>').on('change',function() {
													var optiontext = jQuery(this).val();
													if ('<?php echo filter_var($selectboxopt[$i][1]); ?>'== optiontext) {
														var field = jQuery('input[name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>"]');	
														field.attr('value','<?php echo filter_var($selectboxopt[$i][2]); ?>');
													} else if (optiontext=='') {
														jQuery('input[name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>"]').attr('value','')
													}
													jQuery('body').trigger('update_checkout');	
												});
											});
										</script>
										<?php
									}
								} 

								if ('additional' == $request_type) {
									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'type'      => 'select',
										'id' => $bfield->field_name,
										'label'=> __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'required' => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'     => true,
										'priority'		=> $bfield->sort_order * 10,
										'options'       => $fme_sdata
									);
								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'type'      => 'select',
										'id' => $bfield->field_name,
										'label'=> __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'required' => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'     => true,
										'priority'		=> $bfield->sort_order * 10,
										'options'       => $fme_sdata
									);
								}

								?>
								<script type="text/javascript">
									jQuery(document).ready(function(){
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>').after('<input type="hidden" name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>" value="">');
									});
								</script>
								<?php
								

							} else if ('multiselect' == $bfield->field_type ) {

								$fme_msopt = unserialize($bfield->options);
								
								$fme_msdata = [];
								$fme_msdatap = [];
								$fme_msdata[''] ='';	
								if (!empty($fme_msopt)) {	
									for ($i=0; $i < count($fme_msopt) ; $i++) { 
										$fme_msdata[$fme_msopt[$i][1]] = strtolower($fme_msopt[$i][0]);
										?>
										<script type="text/javascript">
											jQuery(document).ready(function(){
												var a =jQuery('select[name="<?php echo esc_attr($bfield->field_name); ?>"] option').each(function(){
													if (jQuery(this).val() !== "" && jQuery(this).val() == '<?php echo filter_var($fme_msopt[$i][1]); ?>') {
														jQuery(this).attr('data-attr','<?php echo filter_var($fme_msopt[$i][2]); ?>');
													}
												});	
											});
										</script>
										<?php
									}
								} 
								if ('additional' == $request_type) {

									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'type'      => 'select',
										'id' => $bfield->field_name,
										'label'=> __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'required' => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'     => true,
										'priority'		=> $bfield->sort_order * 10,
										'options'       => $fme_msdata,
									);

								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'type'      => 'select',
										'id' => $bfield->field_name,
										'label'=> __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'required' => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'     => true,
										'priority'		=> $bfield->sort_order * 10,
										'options'       => $fme_msdata
									);
								}
								?>
								<input type="hidden" name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>" value="">
								<input type="hidden"  name="multiple_<?php echo esc_attr($bfield->field_name); ?>" id="multiple_<?php echo esc_attr($bfield->field_name); ?>" value="0">
								<script type="text/javascript">
									jQuery(document).ready(function(){
										var a = 'select[name="<?php echo esc_attr($bfield->field_name); ?>"]',
										b = 'input[name="multiple_<?php echo esc_attr($bfield->field_name); ?>"]';
										var baths = [];
										jQuery(a).attr('multiple', 'multiple');
										jQuery(a).select2();
										jQuery(a).next().find('ul li:first').remove();
										jQuery(a).change( function(){
											var selectedOptions = [];
											var selectedOptionprice = [];
											jQuery('select[name="<?php echo esc_attr($bfield->field_name); ?>"] option:selected').each(function(){
												var optionprice = jQuery(this).attr('data-attr');
												if (optionprice== undefined || optionprice== 'undefined') {
													optionprice = '';
												}
												var optionvalue = jQuery(this).val();
												if ( optionprice || optionvalue ) {
													if(jQuery.trim(optionprice) || jQuery.trim(optionvalue)){
														selectedOptions.push({
															value: optionprice.trim(), 
															key:  optionvalue.trim()
														});
														selectedOptionprice.push(optionprice.trim());
													}
												}
											});
											if (selectedOptions!='') {
												jQuery('input[name="multiple_<?php echo esc_attr($bfield->field_name); ?>"]').val(JSON.stringify(selectedOptions));
												jQuery('input[name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>"]').val(selectedOptionprice.join(','));
											}
											if (jQuery('#<?php echo esc_attr($bfield->field_name); ?> option:selected').val()=='') {
												jQuery(this).next().find('ul li:first').remove();
											} 
											jQuery('body').trigger('update_checkout');	
										});
									});
								</script>
								<?php

							} else if ('radio' == $bfield->field_type ) {
								$radioopt = unserialize($bfield->options);
								$fme_radiodata = [];
								$fme_radiodata = ['0'=>''];
								if (!empty($radioopt)) {	
									for ($i=0; $i < count($radioopt) ; $i++) { 
										$fme_radiodata[$radioopt[$i][1]] = $radioopt[$i][0];
										?>
										<script type="text/javascript">
											jQuery(document).ready(function(){
												jQuery('input[name="<?php echo esc_attr($bfield->field_name); ?>"]').attr('ischanged',false);
												jQuery('input[name="<?php echo esc_attr($bfield->field_name); ?>"]').on('change',function() {
													var optiontext = jQuery(this).val();
													if ('<?php echo filter_var($radioopt[$i][1]); ?>'== optiontext) {
														var field = jQuery('input[name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>"]');	
														field.attr('value','<?php echo filter_var($radioopt[$i][2]); ?>');
														jQuery('body').trigger('update_checkout');	
													}
												});
											});

										</script>
										<?php
									}
								} 

								
								if ('additional' == $request_type) {
									
									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'woocommerce'),
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id), 
										'clear'         => true, 
										'id'         	=> $bfield->field_name,
										'type'			=> 'radio',
										'options'     	=> $fme_radiodata,
										'priority'		=> $bfield->sort_order * 10
									);
	 
								} else { 

									$fme_billing_fields[$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'woocommerce'),
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'         => true,
										'id'         	=> $bfield->field_name,
										'type'			=> 'radio',
										'options'     	=> $fme_radiodata,
										'priority'		=> $bfield->sort_order * 10
									);
								}
								
								?>
								<input type="hidden" name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>" value="">
								<script type="text/javascript">
									jQuery(document).ready(function(){
										var radiocss = jQuery("input[type='radio'][name=<?php echo esc_attr($bfield->field_name); ?>]").next();
										radiocss.css('display','inherit');
										radiocss.append("<br>")
										
										radiocss.css('margin','5px');
										var radios= jQuery("input[type='radio'][name=<?php echo esc_attr($bfield->field_name); ?>]:first");
										radios.hide();
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').css('border','1px solid #80808078');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').css('padding','12px');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').css('margin','revert');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').css('border-radius','5px');

									});
								</script>
								<?php
							} else if ('checkbox' == $bfield->field_type ) {


								if ('additional' == $request_type) {
									
									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'type'			=> 'checkbox',
										'priority'		=> $bfield->sort_order * 10
									);
								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'type'			=> 'checkbox',
										'priority'		=> $bfield->sort_order * 10
									);
								}
								?>
								<script type="text/javascript">
									jQuery(document).ready(function($) {
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>').val('0');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>').change(function(){
											
											if(this.checked) {
												if ('<?php echo esc_attr($bfield->field_placeholder); ?>'!=''){
												jQuery(this).val('<?php echo esc_attr($bfield->field_placeholder); ?>');
											} else {
												jQuery(this).val('<?php echo esc_attr($bfield->field_label); ?>');
											} 
												// jQuery(this).val('Yes');
											} else {
												jQuery(this).val('0');
												jQuery('input[name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>"]').val('');
											}
										});
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').css('border','1px solid #80808078');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').css('padding','12px');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').css('margin','revert');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').css('border-radius','5px');
									});
								</script>
								<?php		
							} else if ( 'date'== $bfield->field_type ) {
								if ('additional' == $request_type) {
									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'type'          => 'text',
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'id'         	=> $bfield->field_name,
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'label'         => __($bfield->field_label, 'woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'priority'		=> $bfield->sort_order * 10
									);
								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'type'          => 'text',
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'id'         	=> $bfield->field_name,
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'label'         => __($bfield->field_label, 'woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'priority'		=> $bfield->sort_order * 10
									);
								}
								

								?>
								<script type="text/javascript">
									//haseeb changed minDate: 0,
								
									jQuery(document).ready(function($) {
										
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>').click(function() {
											var mind='<?php echo esc_html__($bfield->is_min_date); ?>';
											if (mind!='0') {
												mind='';
											}
											jQuery('#<?php echo esc_attr($bfield->field_name); ?>').datepicker({
												showButtonPanel: false,
												dateFormat: 'dd-mm-yy',
												maxDate: '',
												minDate: mind, 
											}).datepicker('show');
										});
									});
								</script>
								<?php
							} else if ('time' == $bfield->field_type) {


								if ('additional' == $request_type) {
									
									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'input_class'   => array('timepick'),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'type'			=> 'text',
										'priority'		=> $bfield->sort_order * 10
									);
								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'input_class'   => array('timepick'),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'type'			=> 'text',
										'priority'		=> $bfield->sort_order * 10
									);
								}

								?>
								<script type="text/javascript">
									jQuery(document).ready(function($){
										jQuery('.timepick').wickedpicker({ 
											twentyFour: false, 
											title:'Timepicker', 
											showSeconds: true,
											clearable: false
										});
										jQuery('.timepick').val('');
									});
								</script>
								<?php
							} else if ('color' == $bfield->field_type) {

								if ('additional' == $request_type) {
									
									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'input_class'   => array('color_sepctrum'),
										'priority'		=> $bfield->sort_order * 10
									);
								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'input_class'   => array('color_sepctrum'),
										'priority'		=> $bfield->sort_order * 10
									);
								}

								?>
								<script>
									jQuery(document).ready(function($){
										jQuery(".color_sepctrum").spectrum({
											color: "#123222",
											preferredFormat: "hex",
										});
										jQuery('.color_sepctrum').next().css('width','100%');
										jQuery('.color_sepctrum').next().find('.sp-preview').css('width','93%');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>').on('change',function(){
											jQuery(this).show();
											jQuery(this).attr('readonly','readonly');
										});
									});
								</script>
								<?php 
							} else if ('heading' == $bfield->field_type) {

								if ('additional' == $request_type) {
									
									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'priority'=> $bfield->sort_order * 10,
										'type' => 'hidden',
										'label'=> __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce'),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'input_class' => array('fme_heading'),
										'id' => $bfield->field_name,
									);
								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'priority'=> $bfield->sort_order * 10,
										'type' => 'hidden',
										'label'=> __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce'),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'input_class' => array('fme_heading'),
										'id' => $bfield->field_name,
									);
								}

								
								?>
								<script type="text/javascript">
									jQuery(document).ready(function(){
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').find('label').html('<<?php echo filter_var($bfield->heading_type); ?>> <?php echo esc_html__($bfield->field_label, 'conditional-checkout-fields-for-woocommerce'); ?></<?php echo filter_var($bfield->heading_type); ?>>');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').find('label').children().css('font-weight','400');
									});
								</script>
								<?php
							} else if ('paragraph' == $bfield->field_type) {

								if ('additional' == $request_type) {
									
									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'priority'=> $bfield->sort_order * 10,
										'type' => 'hidden',
										'label'=> __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce'),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'input_class' => array('fme_heading'),
										'id' => $bfield->field_name,
									);
								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'priority'=> $bfield->sort_order * 10,
										'type' => 'hidden',
										'label'=> __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce'),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'input_class' => array('fme_heading'),
										'id' => $bfield->field_name,
									);
								}

								
								?>
								<script type="text/javascript">
									jQuery(document).ready(function(){
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').find('label').html('<p> <?php echo esc_html__($bfield->field_label, 'conditional-checkout-fields-for-woocommerce'); ?></p>');
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>_field').find('label').children().css('font-size','16px');
									});
								</script>
								<?php
							} else if ('file' == $bfield->field_type) {


								if ('additional' == $request_type) {
									
									$type = 'order';
									$fme_billing_fields[$type][$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'conditional-checkout-fields-for-woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'type' => 'hidden',
										'priority'		=> $bfield->sort_order * 10
									);

									if ( isset($bfield->field_file_size) && '' != $bfield->field_file_size ) {
										$fme_allowed_file_size = unserialize($bfield->field_file_size);
										$fme_type_file_type = $fme_allowed_file_size[1];
										$fme_file_maximum_size = $fme_allowed_file_size[0];
									}

								} else {

									$fme_billing_fields[$bfield->field_name] = array(
										'label'         => __($bfield->field_label, 'conditional-checkout-fields-for-woocommerce') . $fprice,
										'placeholder'   => __($bfield->field_placeholder, 'conditional-checkout-fields-for-woocommerce'),
										'required'      => ( 0 == $bfield->is_required ? false : true ),
										'class' => explode(',', $bfield->is_class . ' ' . $bfield->field_id),
										'clear'         => false,
										'id'         	=> $bfield->field_name,
										'type' => 'hidden',
										'priority'		=> $bfield->sort_order * 10,
									);
								}
								if ( isset($bfield->field_file_size) && '' != $bfield->field_file_size ) {
									$fme_allowed_file_size = unserialize($bfield->field_file_size);
									$fme_type_file_type = $fme_allowed_file_size[1];
									$fme_file_maximum_size = $fme_allowed_file_size[0];
								}
								?>

								<script type="text/javascript">
								 
									jQuery(document).ready(function(){
										jQuery("input[name=<?php echo esc_attr($bfield->field_name); ?>]").after('<input type="file" class="input-text" accept="<?php echo esc_attr($bfield->field_extensions); ?>" name="fme_<?php echo esc_attr($bfield->field_name); ?>" id="fme_<?php echo esc_attr($bfield->field_name); ?>" placeholder="<?php echo esc_attr($bfield->field_placeholder); ?>"><small id="fme_label_<?php echo esc_attr($bfield->field_name); ?>"></small>');
										jQuery("input[name=<?php echo esc_attr($bfield->field_name); ?>]").attr('typee','file');
										jQuery("input[name=fme_<?php echo esc_attr($bfield->field_name); ?>]").change(function(e){
											e.preventDefault();
											var file_data = jQuery(this).prop('files')[0];
											var filename = file_data.name;
											filename = filename.replace(/\s+/g, '_');
											var n = filename.lastIndexOf('/');
											var selectfilename = filename.substring(n + 1);
											var form_data = new FormData(); 
											var validExtensions = '<?php echo filter_var($bfield->field_extensions); ?>';
											form_data.append('fme<?php echo esc_attr($bfield->field_name); ?>', file_data);
											if ('<?php echo filter_var($fme_type_file_type); ?>' =='MB' && '' != '<?php echo filter_var($fme_file_maximum_size); ?>') {
												var fme_selected_file_size = file_data.size;
												var file_size = fme_selected_file_size / 1000000;
											} else if ('<?php echo filter_var($fme_type_file_type); ?>' =='KB' && '' != '<?php echo filter_var($fme_file_maximum_size); ?>') {
												var fme_selected_file_size = file_data.size;
												var file_size = fme_selected_file_size / 1000;
											} 
											if (file_size > '<?php echo filter_var($fme_file_maximum_size); ?>' && '' != '<?php echo filter_var($fme_file_maximum_size); ?>') {
												alert('File size must be Less than and equal to ' + '<?php echo filter_var($fme_file_maximum_size); ?>' + '<?php echo filter_var($fme_type_file_type); ?>');
												jQuery('input[name=fme_<?php echo esc_attr($bfield->field_name); ?>]').val('');
											} else {
												
												jQuery.ajax({
													url: "<?php echo filter_var(admin_url('/admin-ajax.php?action=fme_upload_pic&id=fme' . $bfield->field_name . '&ext=' . $bfield->field_extensions . '&security=' . wp_create_nonce('fme_upload_pic') . '')); ?>", 
													cache: false,
													contentType: false,
													processData: false,
													data: form_data,                         
													type: 'post',
													success: function(response){
														if (response=='invalid') {
															alert("Invalid file type Please choose only " + validExtensions + ' files');
														} else {
															if(response!=''){
																if ('' != '<?php echo esc_attr($bfield->field_price); ?>') {
																	jQuery('input[name="fme_ccfw_price_<?php echo esc_attr($bfield->field_name); ?>"]').attr('value', "<?php echo esc_attr($bfield->field_price); ?>");
																}
																jQuery('input[name=fme_<?php echo esc_attr($bfield->field_name); ?>]').prev().attr('value',filename);
																jQuery('#fme_label_<?php echo esc_attr($bfield->field_name); ?>').text(selectfilename + ':- File is selected');
																jQuery('body').trigger('update_checkout');	
															}
														}
													}
												});
											}
											
										});  
									});
								</script>
								<?php	
							}
						}

					} else {

						if (array_key_exists($bfield->field_name, $fme_billing_fields) !== false) {
							unset($fme_billing_fields[$bfield->field_name] );

						}
						//haseeb changed to hide order comments if disabled 
						if ('order_comments' == $bfield->field_name) {
							$type = 'order';
							unset($fme_billing_fields[$type][$bfield->field_name]);
						}
					}
				} 
			}
			 
			return $fme_billing_fields;

		}

		public function fme_get_billing_fields( $type) {
			global $wpdb;
			$fme_result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_type!="" AND type = %s ORDER BY length(sort_order), sort_order', $type));      
			return $fme_result;
		}

	}

	new Fme_Ccffw_Front();
}


?>
