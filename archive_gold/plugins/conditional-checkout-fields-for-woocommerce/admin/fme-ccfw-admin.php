<?php
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

if ( !class_exists( 'Fme_CCFW_Admin' ) ) { 

	class Fme_CCFW_Admin extends FME_CCFW_MAIN {
		
		public function __construct() {
			
			add_filter('woocommerce_settings_tabs_array', array($this, 'fme_ccfw_settings_tabs_array'), 50 );//admin	
			add_action( 'woocommerce_settings_fme_ccfw_tab', array($this, 'fme_ccfw_admin_settings' )); //admin
			add_action( 'admin_enqueue_scripts', array( $this, 'fme_fvg_admin_scripts' ) );

			add_action('wp_ajax_fme_ccfw_save_fielddata', array($this, 'fme_ccfw_save_fielddata'));

			add_action('wp_ajax_fme_ccfw_store_fielddata', array($this, 'fme_ccfw_store_fielddata'));
			
			add_action('wp_ajax_fme_ccfw_delete_fielddata', array($this, 'fme_ccfw_delete_fielddata'));

			add_action('wp_ajax_fme_ccfw_field_condtion', array($this, 'fme_ccfw_field_condtion'));

			add_action('wp_ajax_fme_ccfw_field_add_condtion', array($this, 'fme_ccfw_field_add_condtion'));

			add_action('wp_ajax_fme_ccfw_fieldsortorder', array($this, 'fme_ccfw_fieldsortorder'));

			add_action( 'woocommerce_admin_order_data_after_order_details' , array($this, 'fme_additional_order_data_in_admin' ));

			add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this,'edit_woocommerce_order_shipping_section'), 10, 1 );

			add_action( 'woocommerce_admin_order_data_after_billing_address', array($this,'edit_woocommerce_order_billing_section'), 10, 1 );

			// add_action( 'add_meta_boxes', array($this, 'extCCFA_get_additional_fields_data' ));

			add_action('wp_ajax_fme_upload_pic', array($this, 'fme_upload_pic'));
			add_action('wp_ajax_nopriv_fme_upload_pic', array($this, 'fme_upload_pic')); 

			add_action( 'save_post', array($this,'save_custom_code_after_order_details'), 10, 1 );

		}

		public function edit_woocommerce_order_billing_section( $order ) {
			$getadditionalFields = $this->getbillingFields();
			$this->custom_additional_fields($order, $getadditionalFields, '', 'billing');
		}

		public function edit_woocommerce_order_shipping_section( $order ) {
			$getadditionalFields = $this->getshippingFields();
			$this->custom_additional_fields($order, $getadditionalFields, '', 'shipping');
		}

		public function fme_additional_order_data_in_admin( $order ) {  

			$getadditionalFields = $this->getadditionalFields();
			$additionalLabelval = get_option('additionalfieldlabel');
			if (!empty($getadditionalFields)) {
				if ('' == $additionalLabelval) {
					$addlabel = 'Additional Fields';
				} else {
					$addlabel = $additionalLabelval;
				}
			} else {

				$addlabel = '';	
			}
			$this->custom_additional_fields($order, $getadditionalFields, $addlabel , 'additional');
		}

		public function custom_additional_fields( $order, $getFields, $fieldtype, $sectiontype ) {

			$fme_order_id = $order->get_id();
			if (!empty($getFields)) {
				?>
				<style type="text/css">
					input[type='radio'] {
						width: unset !important;
					}
					input[type='checkbox'] {
						width: unset !important;
					}
				</style>
				<?php 
				if ('additional'==$sectiontype && '' != $fieldtype) {

					?>
				<div class="order_data_column" style="width:100%;margin-top:5%;">
					<h4><?php esc_html_e($fieldtype, 'woocommerce' ); ?><a href="#" class="edit_address"><?php esc_html_e( 'Edit', 'woocommerce' ); ?></a></h4>
					<?php

				}
				?>

				<div class="address">

					<?php 
					foreach ($getFields as $bfield) {

						if ('heading' == $bfield->field_type || 'paragraph' == $bfield->field_type) { 
							continue;
						}

						if ('multiselect' == $bfield->field_type) { 
							$fmevalues = get_post_meta( $fme_order_id, '_multiple_' . $bfield->field_name . 'fme' . $bfield->type . '', true );
							if (!empty($fmevalues)) {
								$jsonData = stripslashes(html_entity_decode($fmevalues));
								$jsonData = json_decode($jsonData, true);
								$multiple_arr = array();
								foreach ($jsonData as $key => $multiple_) {
									array_push($multiple_arr , $multiple_['key']);
								}

								if (!empty($multiple_arr)) {
									$fmevalue = implode(',' , $multiple_arr);	
								}

								if ('' != $fmevalue) {
									echo '<p><strong>' . esc_html__( $bfield->field_label ) . ':</strong>' . filter_var($fmevalue) . '</p>';
								}
							}
						} if ('file' == $bfield->field_type) {

							$fmevalue = get_post_meta( $fme_order_id , '_' . $bfield->field_name . 'fme' . $bfield->type . '', true);

							if ('' != $fmevalue) {
								$url = wp_upload_dir()['baseurl'] . '/' . $fmevalue;
								$url = str_replace(' ', '', $url);
								echo '<p><strong>' . esc_html__( $bfield->field_label ) . ':</strong><a href="' . esc_url($url) . '" target="_blank"> View File </a></p>';
							}

						} else {
							$fmevalue = get_post_meta( $fme_order_id , '_' . $bfield->field_name . 'fme' . $bfield->type . '', true);

							if ('' != $fmevalue) {
								echo '<p><strong>' . esc_html__( $bfield->field_label ) . ':</strong>' . filter_var($fmevalue) . '</p>';
							}
						}

						
					}
					?>
				</div>
				<div class="edit_address">
					<?php 

					foreach ($getFields as $bfield) {

						if ('multiselect' == $bfield->field_type) { 
							$fmevalues = get_post_meta( $fme_order_id, '_multiple_' . $bfield->field_name . 'fme' . $bfield->type . '', true );
							if (!empty($fmevalues)) {
								$jsonData = stripslashes(html_entity_decode($fmevalues));
								$jsonData = json_decode($jsonData, true);
								$multiple_arr = array();
								foreach ($jsonData as $key => $multiple_) {
									array_push($multiple_arr , $multiple_['key']);
								}

								if (!empty($multiple_arr)) {
									$fmevalue = $multiple_arr;
								}
							}
						} else {
							$fmevalue = get_post_meta( $fme_order_id , '_' . $bfield->field_name . 'fme' . $bfield->type . '', true);
						}
						if ('' != $fmevalue) {	
							if ('text'==$bfield->field_type  || 'number'== $bfield->field_type 	||  'password'== $bfield->field_type || 'tel'== $bfield->field_type) {
								?>
								<p class="form-field _<?php echo esc_attr($bfield->field_name); ?>_field ">
									<label><?php echo esc_attr($bfield->field_label); ?></label>
									<input type="<?php echo esc_attr($bfield->field_type); ?>" style="width:max-content;"value="<?php echo filter_var($fmevalue); ?>" name="<?php echo esc_attr($bfield->field_name); ?>" id="<?php echo esc_attr($bfield->field_name); ?>" class="sort">
								</p>
								<?php
							} else if ('number' == $bfield->field_type) { 
								?>
								<p class="form-field __<?php echo esc_attr($bfield->field_name); ?>_field ">
									<label><?php echo esc_attr($bfield->field_label); ?></label>
									<input type="number" style="width:max-content;" value="<?php echo filter_var($fmevalue); ?>" name="<?php echo esc_attr($bfield->field_name); ?>" id="<?php echo esc_attr($bfield->field_name); ?>" class="sort">
								</p>
								<?php
							} else if ('textarea' == $bfield->field_type) { 
								?>
								<p class="form-field __<?php echo esc_attr($bfield->field_name); ?>_field ">
									<label><?php echo esc_attr($bfield->field_label); ?></label>
									<textarea style="width:max-content;" name="<?php echo esc_attr($bfield->field_name); ?>" id="	<?php echo esc_attr($bfield->field_name); ?>" class="sort"><?php echo filter_var($fmevalue); ?></textarea>
								</p>
								<?php
							} else if ('select' == $bfield->field_type) { 

								?>
								<p class="form-field __<?php echo esc_attr($bfield->field_name); ?>_field ">
									<label><?php echo esc_attr($bfield->field_label); ?></label>
									<select name="<?php echo esc_attr($bfield->field_name); ?>" id="<?php echo esc_attr($bfield->field_name); ?>" class="sort">
										<?php 
										$selectboxopt = unserialize($bfield->options);
										if (!empty($selectboxopt)) {
											$fme_sdata = [];	
											for ($i=0; $i < count($selectboxopt) ; $i++) { 
												$fme_sdata[$selectboxopt[$i][1]] = $selectboxopt[$i][0];	
											}
											if (!empty($fme_sdata)) {

												foreach ($fme_sdata as $key=>$optval) {
													$optionval = str_replace(' ', '', $key);
													echo '<option ' . selected($optionval, $fmevalue) . '  value=' . filter_var($optionval) . '>' . filter_var($optval) . '</option>';
												}
											}

										}
										?>
									</select>
								</p>
								<?php
							} else if ('multiselect' == $bfield->field_type) { 

								?>
								<p class="form-field __<?php echo esc_attr($bfield->field_name); ?>_field ">
									<label><?php echo esc_attr($bfield->field_label); ?></label>
									<select name="<?php echo esc_attr($bfield->field_name); ?>" id="<?php echo esc_attr($bfield->field_name); ?>" multiple>
										<?php 
										$selectboxopt = unserialize($bfield->options);
										if (!empty($selectboxopt)) {
											$fme_sdata = [];	
											for ($i=0; $i < count($selectboxopt) ; $i++) { 
												$fme_sdata[$selectboxopt[$i][1]] = $selectboxopt[$i][0];	
											}
											if (!empty($fme_sdata)) {
												$i = 0;
												foreach ($fme_sdata as $key=>$optval) {
													$optionval = str_replace(' ', '', $key);
													if (array_key_exists($i, $fmevalue)) {	
														echo '<option ' . selected($optionval, $fmevalue[$i]) . '  value=' . filter_var($optionval) . '>' . filter_var($optval) . '</option>';
														$i++;
													} else {
														echo '<option value=' . filter_var($optionval) . '>' . filter_var($optval) . '</option>';
													}
												}
											}

										}
										?>
									</select>
								</p>
								<input type="hidden"  name="multiple_<?php echo esc_attr($bfield->field_name); ?>" id="multiple_<?php echo esc_attr($bfield->field_name); ?>" value='<?php echo filter_var($fmevalues); ?>'>
								<script type="text/javascript">
									jQuery(document).ready(function(){
										var a = 'select[name="<?php echo esc_attr($bfield->field_name); ?>"]',
										b = 'input[name="multiple_<?php echo esc_attr($bfield->field_name); ?>"]';
										var baths = [];
										jQuery(a).attr('multiple', 'multiple');
										jQuery(a).select2();
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
										});
									});
								</script>
								<?php
							} else if ('radio' == $bfield->field_type ) {

								$radioopt = unserialize($bfield->options);
								$fme_radiodata = [];
								if (!empty($radioopt)) {	
									for ($i=0; $i < count($radioopt) ; $i++) { 
										$fme_radiodata[$radioopt[$i][1]] = $radioopt[$i][0];
									}
								}
								if (!empty($fme_radiodata)) {
									echo '<p class="form-field __' . esc_attr($bfield->field_name) . '_field">';
									echo '<label>' . esc_attr($bfield->field_label) . '</label>';
									foreach ($fme_radiodata as $key=> $value) {
										?>
										<span><?php echo esc_attr($value); ?></span>
										<input type="radio" value="<?php echo filter_var($key); ?>" <?php echo checked($fmevalue, $key); ?> name="<?php echo filter_var($bfield->field_name); ?>">
										<?php	
									}
									echo '</p>';

								} 
							} else if ('checkbox' == $bfield->field_type ) {

								echo '<p class="form-field __' . esc_attr($bfield->field_name) . '_field">';
								?>
								<span>
									<input type="checkbox" <?php echo checked($fmevalue, $bfield->field_label); ?> name="<?php echo filter_var($bfield->field_name); ?>">
									<?php echo esc_attr($bfield->field_label); ?>
								</span>
								<?php 
								echo '</p>';

							} else if ( 'date'== $bfield->field_type ) {

								echo '<p class="form-field __' . esc_attr($bfield->field_name) . '_field">';
								echo '<label>' . esc_attr($bfield->field_label) . '</label>';
								?>
								<input type="text" value="<?php echo filter_var($fmevalue); ?>" id="<?php echo filter_var($bfield->field_name); ?>" name="<?php echo filter_var($bfield->field_name); ?>">
								<script type="text/javascript">
									jQuery(document).ready(function($) {
										jQuery('#<?php echo esc_attr($bfield->field_name); ?>').click(function() {
											jQuery('#<?php echo esc_attr($bfield->field_name); ?>').datepicker({
												showButtonPanel: false,
												dateFormat: 'dd-mm-yy',
												maxDate: '',
												minDate: 0, 
											}).datepicker('show');
										});
									});
								</script>
								<?php 
								echo '</p>';
							} else if ('time' == $bfield->field_type) {

								echo '<p class="form-field __' . esc_attr($bfield->field_name) . '_field">';
								echo '<label>' . esc_attr($bfield->field_label) . '</label>';
								?>
								<input type="text" class="timepick1" value="<?php echo filter_var($fmevalue); ?>" id="<?php echo filter_var($bfield->field_name); ?>" name="<?php echo filter_var($bfield->field_name); ?>">
								<script type="text/javascript">
									jQuery(document).ready(function($){
										jQuery('.timepick1').wickedpicker({ 
											twentyFour: false, 
											title:'Timepicker', 
											showSeconds: true,
											clearable: false
										});
										// jQuery('.timepick1').val('');
									});
								</script>
								<?php 
								echo '</p>';

							} else if ('color' == $bfield->field_type) {

								echo '<p class="form-field __' . esc_attr($bfield->field_name) . '_field">';
								echo '<label>' . esc_attr($bfield->field_label) . '</label>';
								?>
								<input type="color" class="color_sepctrum" value="<?php echo filter_var($fmevalue); ?>" id="<?php echo filter_var($bfield->field_name); ?>" name="<?php echo filter_var($bfield->field_name); ?>">
								<?php 
								echo '</p>';

							} else if ('file' == $bfield->field_type) {

								if ( isset($bfield->field_file_size) && '' != $bfield->field_file_size ) {
									$fme_allowed_file_size = unserialize($bfield->field_file_size);
									$fme_type_file_type = $fme_allowed_file_size[1];
									$fme_file_maximum_size = $fme_allowed_file_size[0];
								}

								echo '<p class="form-field __' . esc_attr($bfield->field_name) . '_field">';
								echo '<label>' . esc_attr($bfield->field_label) . '</label>';

								$url = wp_upload_dir()['baseurl'] . '/' . $fmevalue;
								$url = str_replace(' ', '', $url);

								?>
								<a href="<?php echo esc_url($url); ?>" target="_blank"><img src="<?php echo esc_url($url); ?>" width="100"></a>
								<input type="file" accept="<?php echo esc_attr($bfield->field_extensions); ?>" class="input-text" id="fme_<?php echo filter_var($bfield->field_name); ?>" name="fme_<?php echo filter_var($bfield->field_name); ?>">

								<input type="hidden" class="input-hidden fme-ccfw_class_main"  id="<?php echo filter_var($bfield->field_name); ?>" value="<?php echo filter_var($fmevalue); ?>" name="<?php echo filter_var($bfield->field_name); ?>">
								

								<script type="text/javascript">
									jQuery(document).ready(function(){

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
												var urlpath = "<?php echo esc_url(wp_upload_dir()['baseurl']); ?>/";
												urlpath = urlpath+filename;
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
																jQuery('input[name=fme_<?php echo esc_attr($bfield->field_name); ?>]').next().attr('value',filename);
																jQuery('input[name=fme_<?php echo esc_attr($bfield->field_name); ?>]').prev().attr('href',urlpath);

																jQuery('input[name=fme_<?php echo esc_attr($bfield->field_name); ?>]').prev().find('img').attr('src',urlpath);

																jQuery('#fme_label_<?php echo esc_attr($bfield->field_name); ?>').text(selectfilename + ':- File is selected');	
															}
														}
													}
												});
											}

										});  

									});
								</script>
								<?php 
								echo '</p>';

							}
						}
					}
					?>
				</div>
				<?php 
				if ('additional' == $sectiontype ) {
					echo '</div>';
				}
			}

		}


		public function save_custom_code_after_order_details( $order_id ) {
			//haseeb changed
			if (get_post_type()!='shop_order') {
				return $order_id;	
			}
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $order_id;
			}

			$billing_fields = $this->getBillingFields();
			foreach ($billing_fields as $bfield) {

				if ('multiselect' == $bfield->field_type) { 
					
					$multivalue = isset($_REQUEST['multiple_' . $bfield->field_name]) ? map_deep( wp_unslash( $_REQUEST['multiple_' . $bfield->field_name] ), 'sanitize_text_field' ) : array();
					update_post_meta( $order_id, '_multiple_' . $bfield->field_name . 'fme' . $bfield->type . '', $multivalue);

				} else if ('checkbox' == $bfield->field_type) {

					$fmecrval =  isset($_REQUEST[$bfield->field_name]) ? filter_var($bfield->field_label) : '0'; 	
					update_post_meta( $order_id , '_' . $bfield->field_name . 'fme' . $bfield->type . '', $fmecrval);
				} else {
					update_post_meta( $order_id , '_' . $bfield->field_name . 'fme' . $bfield->type . '', filter_var($_REQUEST[$bfield->field_name]));
				}
			}

			$getadditionalFields = $this->getadditionalFields();
			foreach ($getadditionalFields as $bfield) {
				if ('multiselect' == $bfield->field_type) { 
					
					$multivalue = isset($_REQUEST['multiple_' . $bfield->field_name]) ? map_deep( wp_unslash( $_REQUEST['multiple_' . $bfield->field_name] ), 'sanitize_text_field' ) : array();
					update_post_meta( $order_id, '_multiple_' . $bfield->field_name . 'fme' . $bfield->type . '', $multivalue);

				} else if ('checkbox' == $bfield->field_type) {

					$fmecrval =  isset($_REQUEST[$bfield->field_name]) ? filter_var($bfield->field_label) : '0'; 	
					update_post_meta( $order_id , '_' . $bfield->field_name . 'fme' . $bfield->type . '', $fmecrval);
				} else {
					update_post_meta( $order_id , '_' . $bfield->field_name . 'fme' . $bfield->type . '', filter_var($_REQUEST[$bfield->field_name]));
				}
			}

			$getshippingFields = $this->getshippingFields();
			foreach ($getshippingFields as $bfield) {

				if ('multiselect' == $bfield->field_type) { 
					
					$multivalue = isset($_REQUEST['multiple_' . $bfield->field_name]) ? map_deep( wp_unslash( $_REQUEST['multiple_' . $bfield->field_name] ), 'sanitize_text_field' ) : array();
					update_post_meta( $order_id, '_multiple_' . $bfield->field_name . 'fme' . $bfield->type . '', $multivalue);

				} else if ('checkbox' == $bfield->field_type) {

					$fmecrval =  isset($_REQUEST[$bfield->field_name]) ? filter_var($bfield->field_label) : '0'; 	
					update_post_meta( $order_id , '_' . $bfield->field_name . 'fme' . $bfield->type . '', $fmecrval);
				} else {
					update_post_meta( $order_id , '_' . $bfield->field_name . 'fme' . $bfield->type . '', filter_var($_REQUEST[$bfield->field_name]));
				}
			}			
		}


		public function fme_upload_pic() {
			check_ajax_referer ('fme_upload_pic', 'security');
			$is_valid = '';
			$fmeid = ( isset( $_REQUEST['id'] ) ? filter_var($_REQUEST['id']) : false );
			$fmevalue = ( isset( $_REQUEST['ext'] ) ? filter_var($_REQUEST['ext']) : false );
			if ('' != $fmevalue) {
				$fmevalue = explode(',', $fmevalue);
			} else {
				$fmevalue = array();
			}

			if (!empty($fmevalue)) {
				if (isset($_FILES[$fmeid]['name']) || isset($_FILES[$fmeid]['type']) || isset($_FILES[$fmeid]['tmp_name']) || isset($_FILES[$fmeid]['error']) || isset($_FILES[$fmeid]['size'])) {
					$fmenamefile = preg_replace('/\s+/', '_', filter_var($_FILES[$fmeid]['name']));
					$file = array(
						'fmename'     => filter_var($fmenamefile),
						'fmetype'     => filter_var($_FILES[$fmeid]['type']),
						'fmetmp_name' => filter_var($_FILES[$fmeid]['tmp_name']),
						'fmeerror'    => filter_var($_FILES[$fmeid]['error']),
						'fmesize'     => filter_var($_FILES[$fmeid]['size'])
					);
					$array = list($filename, $fmeext) = explode('.', filter_var($fmenamefile));
					if (array_intersect($array, $fmevalue)) {
						if (move_uploaded_file($file['fmetmp_name'], wp_upload_dir()['basedir'] . '/' . $fmenamefile)) {
							echo filter_var($fmenamefile);
						}
					} else {
						if ('invalid' != $is_valid) {
							$is_valid = 'invalid';
						}
					}	
				}
				echo filter_var($is_valid);
			} else {

				if (isset($_FILES[$fmeid]['name']) || isset($_FILES[$fmeid]['type']) || isset($_FILES[$fmeid]['tmp_name']) || isset($_FILES[$fmeid]['error']) || isset($_FILES[$fmeid]['size'])) {

					$fmenamefile = preg_replace('/\s+/', '_', filter_var($_FILES[$fmeid]['name']));
					$file = array(
						'fmename'     => filter_var($fmenamefile),
						'fmetype'     => filter_var($_FILES[$fmeid]['type']),
						'fmetmp_name' => filter_var($_FILES[$fmeid]['tmp_name']),
						'fmeerror'    => filter_var($_FILES[$fmeid]['error']),
						'fmesize'     => filter_var($_FILES[$fmeid]['size'])
					);

					if (move_uploaded_file($file['fmetmp_name'], wp_upload_dir()['basedir'] . '/' . $fmenamefile)) {
						echo filter_var($fmenamefile);
					}
				}

			}
			wp_die();
		}


		public function fme_ccfw_field_add_condtion() {

			if (!current_user_can( 'manage_woocommerce' )) {
				wp_die();
			}
			check_ajax_referer ('fme_ccfw_admin_ajax_nonce', 'security');
			global $wpdb;
			global $woocommerce;
			$type = isset($_POST['type']) ? filter_var($_POST['type']) : '';
			require_once( FME_CCFW_PLUGIN_DIR . 'admin/view/fme-ccfw-add-condtion-html.php' );
			wp_die();

		}

		public function fme_ccfw_field_condtion() {
			if (!current_user_can( 'manage_woocommerce' )) {
				wp_die();
			}
			check_ajax_referer ('fme_ccfw_admin_ajax_nonce', 'security');
			global $wpdb;
			global $woocommerce;
			$type = isset($_POST['type']) ? filter_var($_POST['type']) : '';
			require_once( FME_CCFW_PLUGIN_DIR . 'admin/view/fme-ccfw-condtion-html.php' );
			wp_die();
		}


		public function getBillingFields() {
			global $wpdb;
			$fme_result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . "FmeCCFA_fields WHERE type = %s AND field_mode = 'billing_additional'ORDER BY length(sort_order), sort_order ", 'billing'));      
			return $fme_result;
		}

		public function getshippingFields() {
			global $wpdb;
			$fme_result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . "FmeCCFA_fields WHERE type = %s AND field_mode = 'shipping_additional' ORDER BY length(sort_order), sort_order ", 'shipping'));      
			return $fme_result;
		}

		public function getadditionalFields() {
			global $wpdb;
			$fme_result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . "FmeCCFA_fields WHERE type = %s AND field_mode = 'additional_additional' ORDER BY length(sort_order), sort_order", 'additional'));      
			return $fme_result;
		}

		public function extCCFA_get_additional_fields_data() {
			add_meta_box( 'mv_other_fields1', __('Conditional Checkout Fields Data', 'woocommerce'), array($this, 'ext_exta_billing_fields_admin_show') , 'shop_order', 'advanced' );
		}

		public function getFieldsdata( $type) {
			global $wpdb;
			$fme_result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_mode = %s', $type . '_additional'));      
			return $fme_result;
		}

		public function ext_exta_billing_fields_admin_show( $order, $feild ) {

			$order_id = get_the_id();
			$array = array('billing','shipping','additional');
			$output = '<table style="width:100%"><tbody><tr style="width:100%;display:inline-flex;">';
			$fme_index=0;
			foreach ($array as $key => $values) {

				$fme_order_fields_data = $this->getFieldsdata($values);
				if ( 2 == $fme_index) {
					$output .= '</tr><tr>';
				}
				
				if (!empty($fme_order_fields_data)) {
					$fme_index++;
					$output .= "<td style='width:50%;padding: 12px;color: #636363;border: 1px solid #e5e5e5;'>";
					if (''!= $values) {
						$output .='<h2><strong>' . ucfirst($values) . ' fields</strong></h2>';
					}
					foreach ($fme_order_fields_data as $key => $value) {

						if ('file' == $value->field_type) {
							$billing_file_url = get_post_meta( $order_id, '_' . $value->field_name . 'fme' . $value->type . '', true );
							$imgExts = array('gif', 'jpg', 'jpeg', 'png', 'tiff', 'tif');
							$url = wp_upload_dir()['baseurl'] . '/' . $billing_file_url;
							$url = str_replace(' ', '', $url);
							$urlExt = pathinfo($url, PATHINFO_EXTENSION);
							$urlExt = strtolower($urlExt);
							$selectfilename = basename(parse_url($url, PHP_URL_PATH));
							if (in_array($urlExt, $imgExts)) {
								if (!empty($billing_file_url)) {
									$output .= '<br><strong><p style="display:grid;">' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong> <a href=' . $url . ' target="_blank" ><img class="fme_image_ccfw" src=' . $url . ' alt="image" title="Click to View"/ style="border: 1px solid #ddd;border-radius: 4px;padding: 5px;width: 80px;cursor:pointer;"></a><a href=' . $url . ' target="_blank" download=' . $selectfilename . '><button type="button" title="Download"/ class="btn fme_orderdownloadbtn" style="border:0;border-radius: 0;background: none;background-color: #43454b;border-color: #43454b;color: #fff;cursor: pointer;padding: .6180469716em 1.41575em;text-decoration: none;font-weight: 600;text-shadow: none;display: inline-block;-webkit-appearance: none;word-break: break-all;"><i class="fa fa-download"></i> Download</button></a></p>';
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

									$output .= '<br><strong><p style="display: grid;">' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong> <a href=' . $url . ' target="_blank" ><img class="fme_image_ccfw" src="https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg" alt="image" title="Click to View"/ style="border: 1px solid #ddd;border-radius: 4px;padding: 5px;width: 80px;cursor:pointer;"></a><a href=' . $url . ' target="_blank" download=' . $selectfilename . '><button type="button" title="Download"/ class="btn fme_orderdownloadbtn" style="border:0;border-radius: 0;background: none;background-color: #43454b;border-color: #43454b;color: #fff;cursor: pointer;padding: .6180469716em 1.41575em;text-decoration: none;font-weight: 600;text-shadow: none;display: inline-block;-webkit-appearance: none;word-break: break-all;">Download</button></a></p>';
								}

							} else {

								if (!empty($billing_file_url)) {
									$output .= '<br><strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong> <a href=' . $url . ' target="_blank" download=' . $selectfilename . '><button type="button" title="Download"/ class="btn fme_orderdownloadbtn" style="border:0;border-radius: 0;background: none;background-color: #43454b;border-color: #43454b;color: #fff;cursor: pointer;padding: .6180469716em 1.41575em;text-decoration: none;font-weight: 600;text-shadow: none;display: inline-block;-webkit-appearance: none;word-break: break-all"> Download </button></a>';
								}
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
									$output .= '<strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong>' . implode(',', $multiple_arr);
									$output .='<br>';
								}
							}
						} else if ('password' == $value->field_type) { 

							$billing_value = get_post_meta( $order_id, '_' . $value->field_name . 'fme' . $value->type . '', true );
							if (!empty($billing_value)) {
								$output .= '<strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong><input type="password" value= ' . $billing_value . ' disabled style="color:black;border:unset;background:white;">';
								$output .='<br>';
							}
						} else {
							$billing_value = get_post_meta( $order_id, '_' . $value->field_name . 'fme' . $value->type . '', true );
							if (!empty($billing_value)) {
								$output .= '<strong>' . __( $value->field_label, 'conditional-checkout-fields-for-woocommerce' ) . ':</strong>' . $billing_value;
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


		public function fme_ccfw_admin_settings() {
			require_once( FME_CCFW_PLUGIN_DIR . 'admin/view/fme-ccfw-checkoutfield-settings.php' );
		}

		public function fme_ccfw_delete_fielddata() {
			if (!current_user_can( 'manage_woocommerce' )) {
				wp_die();
			}
			check_ajax_referer ('fme_ccfw_admin_ajax_nonce', 'security');
			global $wpdb;
			$delete_field_id = isset($_POST['id']) ? filter_var($_POST['id']) : '';
			$type = isset($_POST['type']) ? filter_var($_POST['type']) : '';
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_id = %d', $delete_field_id ) ); 
			wp_die();
			return true;
		}


		public function fme_ccfw_fieldsortorder() {
			if (!current_user_can( 'manage_woocommerce' )) {
				wp_die();
			}
			check_ajax_referer ('fme_ccfw_admin_ajax_nonce', 'security');
			global $wpdb;
			$field_id = isset($_POST['id']) ? filter_var($_POST['id']) : '';
			$type = isset($_POST['type']) ? filter_var($_POST['type']) : '';
			if ('additional' == $type) {
				$additionalfieldlabel = isset($_POST['additionalfieldlabel']) ? map_deep(wp_unslash($_POST['additionalfieldlabel']), 'sanitize_text_field') : '';
				update_option('additionalfieldlabel', $additionalfieldlabel);
			}

			$getsortorder = isset($_POST['getsortorder']) ? filter_var($_POST['getsortorder']) : '';
			$tablename = $wpdb->prefix . 'FmeCCFA_fields';
			$sql = $wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'FmeCCFA_fields SET sort_order = %d WHERE field_id = %d AND type = %s',
				$getsortorder,
				$field_id, 
				$type
			));
			echo filter_var('success');
			wp_die();
		}

		public function fme_ccfw_store_fielddata() {
			if (!current_user_can( 'manage_woocommerce' )) {
				wp_die();
			}
			check_ajax_referer ('fme_ccfw_admin_ajax_nonce', 'security');
			global $woocommerce;
			global $post;
			global $wpdb;
			$edit_id = isset($_POST['id']) ? filter_var($_POST['id']) : '';
			$type = isset($_POST['type']) ? filter_var($_POST['type']) : '';
			$fme_pqproduct = array(
				'post_status' => 'publish',
				'ignore_sticky_posts' => 1,
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
				'post_type' => 'product'
			);
			$fme_allProducts = get_posts($fme_pqproduct); 
			$fme_pqcategory = array(
				'taxonomy' => 'product_cat',

			);



			$fme_product_categories = get_terms($fme_pqcategory);
			global $wp_roles;
			$fme_roles = $wp_roles->get_names();

			if ('add' != $edit_id) {
				global $wpdb;
				$fme_result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_type!="" AND field_id = %d AND type = %s' , $edit_id, $type));  
				
				if ('on' == $fme_result[0]->userrole_check) {
					$fme_result[0]->userrole_check = 'fme_display_user_role';
				}
				if ('default' == $fme_result[0]->field_mode) {
					$fme_disable = 'disabled';
				}

			}

			?>
			<table class="table table-striped table-sm">
					<tbody>
						<tr>
							<td colspan="2">
								<div class="form-group">
									<label for="type"><?php echo esc_html__('Type', 'conditional-checkout-fields-for-woocommerce'); ?>
										<abbr class="required" title="required">*</abbr>
										<span class="woocommerce-tool-tips-fme" data-toggle='tooltip' title='Select Field Type'>?</span>
									</label>
								<select <?php echo esc_attr($fme_disable); ?> class="form-control" id="fme_cffw_field_type" onchange="fmec_ffw_fieldListner()" >
									<option value="text" 
									<?php 
									if (isset($fme_result) ) {
										selected('text', $fme_result[0]->field_type) ;} 
									?>
									><?php echo esc_html__('Text', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="textarea" 
									<?php 
									if (isset($fme_result) ) {
										selected('textarea', $fme_result[0]->field_type) ; } 
									?>
									><?php echo esc_html__('TextArea', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="number" 
									<?php 
									if (isset($fme_result) ) {
										selected('number', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Number', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="tel" 
									<?php 
									if (isset($fme_result) ) {
										selected('tel', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Telephone', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="password" 
									<?php 
									if (isset($fme_result) ) {
										selected('password', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Password', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="select" 
									<?php 
									if (isset($fme_result) ) {
										selected('select', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Select', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="multiselect" 
									<?php 
									if (isset($fme_result) ) {
										selected('multiselect', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('MultiSelect', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="checkbox" 
									<?php 
									if (isset($fme_result) ) {
										selected('checkbox', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Checkbox', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="radio" 
									<?php 
									if (isset($fme_result) ) {
										selected('radio', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Radio Button', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="date" 
									<?php 
									if (isset($fme_result) ) {
										selected('date', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Date Picker', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="time" 
									<?php 
									if (isset($fme_result) ) {
										selected('time', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Time Picker', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="color" 
									<?php 
									if (isset($fme_result) ) {
										selected('color', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Colo Picker', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="heading" 
									<?php 
									if (isset($fme_result) ) {
										selected('heading', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Heading', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="paragraph" 
									<?php 
									if (isset($fme_result) ) {
										selected('paragraph', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('Paragraph', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<option value="file" 
									<?php 
									if (isset($fme_result) ) {
										selected('file', $fme_result[0]->field_type); } 
									?>
									><?php echo esc_html__('File Upload', 'conditional-checkout-fields-for-woocommerce'); ?></option>
								</select>

								</div>
							</td>
							<td colspan="2">
								<div class="form-group">
									<label for="label"><?php echo esc_html__('Field Name', 'conditional-checkout-fields-for-woocommerce'); ?></label>
									<abbr class="required" title="required">*</abbr>
										<span class="woocommerce-tool-tips-fme" data-toggle='tooltip' title='Enter Field Name'>?</span>
									<input <?php echo esc_attr($fme_disable); ?> type="text" value="<?php echo esc_attr(isset($fme_result) ? $fme_result[0]->field_name:'') ; ?>" class="form-control" name="fme_ccfw_field_fieldname" id="fme_ccfw_field_fieldname" placeholder="Enter Field Name...">
									<span class="fmeerrorname" style="color:red;display:none;"><?php echo esc_html__('Field Name is required!'); ?></span>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="form-group">
									<label for="label"><?php echo esc_html__('Label', 'conditional-checkout-fields-for-woocommerce'); ?></label>
									<abbr class="required" title="required">*</abbr>
										<span class="woocommerce-tool-tips-fme" data-toggle='tooltip' title='Enter Field Label'>?</span>
									<input type="text" value="<?php echo esc_attr(isset($fme_result) ? $fme_result[0]->field_label:'') ; ?>"  class="form-control" id="fme_ccfw_field_label" placeholder="Enter Field label...">
									<span class="fmeerrorlabel" style="color:red;display:none;"><?php echo esc_html__('Field Label is required!'); ?></span>
								</div>
							</td>
							<td colspan="2">
								<div class="form-group">
									<label for="type"><?php echo esc_html__('Enable ', 'conditional-checkout-fields-for-woocommerce'); ?></label>
									<select class="form-control" id="fme_ccfw_field_ed">
										<option value="1" 
										<?php 
										if (isset($fme_result) ) {
											selected('1', $fme_result[0]->is_enable); } 
										?>
										><?php echo esc_html__('Enable', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option value="0" 
										<?php 
										if (isset($fme_result) ) {
											selected('0', $fme_result[0]->is_enable); } 
										?>
										><?php echo esc_html__('Disable', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									</select>
								</div>	
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="form-group">
									<?php
									//haseeb changed Placeholder in variable
									$label_text='Placeholder';
									$place_holder='Enter Placeholder...';
									if ('checkbox'==$fme_result[0]->field_type) {
										$label_text='Value when checked';
										$place_holder='Yes or No';
									}
									?>
									<label for="placeholder"><?php echo esc_html__($label_text, 'conditional-checkout-fields-for-woocommerce') ; ?></label>
									<input <?php echo esc_attr($fme_disable); ?> type="text" value="<?php echo esc_attr(isset($fme_result) ? $fme_result[0]->field_placeholder:'') ; ?>" class="form-control" id="fme_ccfw_field_placeholder" placeholder="<?php echo esc_html__($place_holder, 'conditional-checkout-fields-for-woocommerce'); ?>">
								</div>
							</td>
							<td>
								<div class="form-group">
									<?php 
									if ('default' == $fme_result[0]->field_mode) {
										?>

										<label for="type"><?php echo esc_html__('Field Class ', 'conditional-checkout-fields-for-woocommerce'); ?></label>
										<input <?php echo esc_attr($fme_disable); ?> type="text" name="fme_ccfw_field_class[]" class="form-control" id="fme_ccfw_field_class" value="<?php echo esc_attr(isset($fme_result) ? $fme_result[0]->is_class:'') ; ?>"
										<?php
									} else {

										?>

										<label for="type"><?php echo esc_html__('Field Class ', 'conditional-checkout-fields-for-woocommerce'); ?></label>
										<select class="form-control" name="fme_ccfw_field_class[]"  id="fme_ccfw_field_class">
											<option value="form-row-first" 
											<?php 
											if (isset($fme_result) ) {
												selected('form-row-first', $fme_result[0]->is_class); } 
											?>
											><?php echo esc_html__('form-row-first', 'conditional-checkout-fields-for-woocommerce'); ?></option>
											<option value="form-row-last" 
											<?php 
											if (isset($fme_result) ) {
												 selected('form-row-last', $fme_result[0]->is_class);} 
											?>
											><?php echo esc_html__('form-row-last', 'conditional-checkout-fields-for-woocommerce'); ?>
											</option>
											<option value="form-row-wide" 
											<?php 
											if (isset($fme_result) ) { 
												selected('form-row-wide', $fme_result[0]->is_class);} 
											?>
											><?php echo esc_html__('form-row-wide', 'conditional-checkout-fields-for-woocommerce'); ?>
											</option>
										</select>
										<?php
									}

									?>
									
								</div>	
							</td>
							<td class="fme-ccfw-pricefieldtd" id="fme-ccfw-pricefieldtd"
								<?php 
								if ('default' != $fme_result[0]->field_mode && 'select' == $fme_result[0]->field_type || 'multiselect' == $fme_result[0]->field_type || 'radio' == $fme_result[0]->field_type) {
									echo 'style=display:none';
								} else {
									echo 'style=display:block';
								}
								?>
								>
								<label for="placeholder"><?php echo esc_html__('Price', 'conditional-checkout-fields-for-woocommerce'); ?></label>
								<span class="woocommerce-tool-tips-fme" data-toggle='tooltip' title='Enter Field Price'>?</span>
								<input type="number" value="<?php echo esc_attr(isset($fme_result) ? $fme_result[0]->field_price:'') ; ?>" placeholder="Enter Price..." class="form-control" id="fme_ccfw_field_price">

							</td>
						</tr>
						<tr>
							<td colspan="3" id="fme_ccfw_field_extensions"
							<?php
							if ('default' != $fme_result[0]->field_mode && 'file' == $fme_result[0]->field_type) {
								echo 'style=visibility:inherit';
							} else {
								echo 'style=display:none';
							}
							?>
							>
								<div class="form-group">
									<label for="placeholder"><?php echo esc_html__('Field Extension', 'conditional-checkout-fields-for-woocommerce'); ?></label>
									<input type="text" value="<?php echo esc_attr(isset($fme_result) ? $fme_result[0]->field_extensions:'') ; ?>" class="form-control" id="fme_ccfw_field_extension" placeholder="Enter Field Extension (jpg,png,pdf,jpeg)">
								</div>
							</td>
							<td id="fme_ccfw_field_file_size"
							<?php
							if ('default' != $fme_result[0]->field_mode && 'file' == $fme_result[0]->field_type) {
								echo 'style=visibility:inherit';
							} else {
								echo 'style=display:none';
							}


							?>
							>


								<div class="form-group">
									<label for="placeholder"><?php echo esc_html__('File Size', 'conditional-checkout-fields-for-woocommerce'); ?></label><br>
									<?php 
									if (isset($fme_result) ) {
										$fme_allowed_file_size =  unserialize($fme_result[0]->field_file_size);
									}
									?>
									<select name="fme_ccfw_field_sizes_type" class="fme_ccfw_field_sizes_type" id="fme_ccfw_field_sizes">
										<option <?php selected('KB', $fme_allowed_file_size[1]); ?> value="KB"><?php echo esc_html__('KB', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option <?php selected('MB', $fme_allowed_file_size[1]); ?> value="MB"><?php echo esc_html__('MB', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									</select>
									<input type="text" value="<?php echo filter_var($fme_allowed_file_size[0]); ?>" placeholder="Enter File Size" class="form-control" id="fme_ccfw_field_sizes_val" style="width: 65%;display: inline;">
								</div>
							</td>
							<td colspan="2" id="fme_ccfw_field_heading_type"
							<?php

							if ('default' != $fme_result[0]->field_mode && 'paragraph' == $fme_result[0]->field_type || 'heading' == $fme_result[0]->field_type) {
								echo 'style=display:block';
							} else {
								echo 'style=display:none';
							}
							?>
							>
								<div class="form-group">
									<label for="placeholder"><?php echo esc_html__('Heading Type', 'conditional-checkout-fields-for-woocommerce'); ?></label>
									<select name="fme-ccfw-heading" id="fme-ccfw-heading-value">
										<option value="" 
										<?php 
										if (isset($fme_result) ) {
											selected('', $fme_result[0]->heading_type); } 
										?>
										><?php echo esc_html__('Please Select Heading', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option value="h1" 
										<?php 
										if (isset($fme_result) ) {
											selected('h1', $fme_result[0]->heading_type);} 
										?>
										><?php echo esc_html__('H1', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option value="h2" 
										<?php 
										if (isset($fme_result) ) {
											selected('h2', $fme_result[0]->heading_type);} 
										?>
										><?php echo esc_html__('H2', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option value="h3" 
										<?php 
										if (isset($fme_result) ) {
											selected('h3', $fme_result[0]->heading_type); } 
										?>
										><?php echo esc_html__('H3', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option value="h4" <?php selected('h4', $fme_result[0]->heading_type); ?>>
																				<?php 
																				if (isset($fme_result) ) {
																					echo esc_html__('H4', 'conditional-checkout-fields-for-woocommerce'); } 
																				?>
										</option>
										<option value="h5" 
										<?php 
										if (isset($fme_result) ) {
											selected('h5', $fme_result[0]->heading_type); } 
										?>
										><?php echo esc_html__('H5', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option value="h6" 
										<?php 
										if (isset($fme_result) ) {
											selected('h6', $fme_result[0]->heading_type);} 
										?>
										><?php echo esc_html__('H6', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option value="strong" 
										<?php 
										if (isset($fme_result) ) {
											selected('strong', $fme_result[0]->heading_type); } 
										?>
										><?php echo esc_html__('strong', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option value="p" 
										<?php 
										if (isset($fme_result) ) {
											selected('p', $fme_result[0]->heading_type);} 
										?>
										><?php echo esc_html__('paragraph', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="form-group">
									<div class="fme-ccfw-requireds"
									<?php
									if ('default' != $fme_result[0]->field_mode && 'paragraph' == $fme_result[0]->field_type || 'heading' == $fme_result[0]->field_type) {
										echo 'style=display:none';
									} else {
										echo 'style=display:block';
									}
									?>
									>
									<label for="placeholder"><?php echo esc_html__('Required', 'conditional-checkout-fields-for-woocommerce'); ?></label>
									<input type="checkbox" 
									<?php 
									if (isset($fme_result) ) {
										checked('1', $fme_result[0]->is_required); } 
									?>
									 value="1" class="form-control" id="fme_ccfw_field_required"> 
									<label for="placeholder"><?php echo esc_html__(' Taxable', 'conditional-checkout-fields-for-woocommerce'); ?></label>
									<input type="checkbox" 
									<?php 
									if (isset($fme_result) ) {
										checked('1', $fme_result[0]->is_taxable); } 
									?>
									 value="1" class="form-control" id="fme_ccfw_field_taxable">
									  <!-- haseeb changed date picker minimum -->
									<span class="fme-ccfw-min-date_chk_box"
									<?php
									if ('date' == $fme_result[0]->field_type ) {
										echo 'style=display:inline';
									} else {
										echo 'style=display:none';
									}
									?>

									>
									<label class="fme_ccfw_min_date_class" for="placeholder"><?php echo esc_html__('User can select date before current date', 'conditional-checkout-fields-for-woocommerce'); ?></label>
									<input type="checkbox" class="fme_ccfw_min_date_class"
									<?php 
									if (isset($fme_result) ) {

										checked('1', $fme_result[0]->is_min_date); } 
									?>
									 value="1" class="form-control" id="fme_ccfw_min_date">
									</span>
									</div>
									<label for="placeholder">
										<?php echo esc_html__('Display for Specific Product or Category', 'conditional-checkout-fields-for-woocommerce'); ?>
									</label>	
									<input type="checkbox" 
									<?php 
									if (isset($fme_result) ) {
										checked('on', $fme_result[0]->ischeckpc); } 
									?>
									 class="form-control" id="fme_ccfw_field_specfic_pc">
								</div>	
							</td>
						
							<td colspan="4">
								<div class="form-group" id="fme-ccfw-selectpc-val"
								<?php
								if ('on'==$fme_result[0]->ischeckpc && '' != $fme_result[0]->ischeckpc) {
									echo 'style=display:block';
								} else {
									echo 'style=display:none';
								}
								?>
								>
									<select class="form-control fme_ccfw_selectpc" id="fme-ccfw-fselectpc-type" name="fme_selectpc[]" style="max-width: unset;">
										<option 
										<?php 
										if (isset($fme_result) ) {
											selected('', $fme_result[0]->specific_pc); } 
										?>
										 value=""><?php echo esc_html__('Please Choose one', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option 
										<?php 
										if (isset($fme_result) ) {
											selected('product', $fme_result[0]->specific_pc); } 
										?>
										 value="product"><?php echo esc_html__('Product', 'conditional-checkout-fields-for-woocommerce'); ?></option>
										<option 
										<?php 
										if (isset($fme_result) ) {
											selected('category', $fme_result[0]->specific_pc); } 
										?>
										 value="category"><?php echo esc_html__('Category', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									</select>
								</div>	
							</td>
						</tr>
						<tr>
							<td colspan="4">
								<div class="form-group" id="product-addons"
								<?php
								if ( 'product'==$fme_result[0]->specific_pc && '' != $fme_result[0]->specific_pc) {
									echo 'style=display:block';
								} else {
									echo 'style=display:none';
								}
								?>
								>
									<label id="productlabel"><?php echo esc_html__('Choose Product', 'conditional-checkout-fields-for-woocommerce'); ?></label>&nbsp
									<?php if (!empty($fme_allProducts)) { ?>
										<script type="text/javascript">
											jQuery(document).ready(function(){
												jQuery('.fme-ccfw-products').select2();
												jQuery('.fme-ccfw-category').select2();
												jQuery('.fme-ccfw-user-roles').select2();
												jQuery('.select2').css('width','90%');
											});
										</script>
										<?php 
										if ('product'==$fme_result[0]->specific_pc) {

											if (empty(unserialize($fme_result[0]->selected_pc))) {
												$proArray = array();
											} else {
												$proArray = unserialize($fme_result[0]->selected_pc);
											} 
											$catArray = array();
										} else if ('category'==$fme_result[0]->specific_pc) {

											if (empty(unserialize($fme_result[0]->selected_pc))) {
												$catArray = array();
											} else {
												$catArray = unserialize($fme_result[0]->selected_pc);
											} 
											$proArray = array();
										} else {
											$proArray = array();
											$catArray = array();
										}
										?>
										<select class="fme-ccfw-products" id="fme-ccfw-fproducts" multiple="multiple" name="">
											<?php
											foreach ($fme_allProducts as $products) {		
												?>
												<option 
												<?php if (in_array($products->ID, $proArray)) { ?> 
															selected="selected" 
												<?php } ?> 
												value="<?php echo esc_attr($products->ID); ?>"><?php echo filter_var($products->post_title); ?></option>
												<?php
											}
											?>
										</select>
									<?php }; ?>	

								</div>	
								<div class="form-group" id="category-addons"
								<?php
								if ( 'category' == $fme_result[0]->specific_pc && '' != $fme_result[0]->specific_pc) {
									echo 'style=display:block';
								} else {
									echo 'style=display:none';
								}

								?>
								>
								<label id="productlabel"><?php echo esc_html__('Choose Category', 'conditional-checkout-fields-for-woocommerce'); ?></label>&nbsp
									<?php if (!empty($fme_product_categories)) { ?>	
										<select class="fme-ccfw-category" id="fme-ccfw-fcategory" multiple="multiple" name="">
											<?php
											foreach ($fme_product_categories as $category) {	
												?>
												<option
												<?php if (in_array($category->term_id, $catArray)) { ?> 
														selected="selected" 
												<?php } ?> 
												 value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_attr($category->name); ?></option>
												<?php
											}

											?>
										</select>
										<?php } ?>
								</div>			
							</td>
						</tr>
						<tr>
							<td>
								<div class="form-group fme_radio_buttons">
									<label for="placeholder"><?php echo esc_html__('Display for Specific User roles', 'conditional-checkout-fields-for-woocommerce'); ?></label>

									<input <?php checked('fme_display_user_role', $fme_result[0]->userrole_check); ?> type="radio" class="form-control" name="fme_field_specfic_userrole_radio" id="fme_field_specfic_userrole_radio" value="fme_display_user_role">
								</div>
								<div class="form-group fme_radio_buttons">
									<label for="placeholder"><?php echo esc_html__('Hide for  Specific User roles', 'conditional-checkout-fields-for-woocommerce'); ?></label>


									<input <?php checked('fme_hide_user_role', $fme_result[0]->userrole_check); ?> type="radio" class="form-control" name="fme_field_specfic_userrole_radio" id="fme_field_specfic_userrole_radio" value="fme_hide_user_role">
									
								</div>	
							</td>
							<td colspan="3">
								<div class="form-group" id="fme-ccfw-user-role"
																>
									<label id="userrolelabel"><?php echo esc_html__('Choose User Role(s)', 'conditional-checkout-fields-for-woocommerce'); ?></label>&nbsp
									<?php 
									if (empty(unserialize($fme_result[0]->specific_user_role))) {
										$fme_userrole_arr = array();
									} else {
										$fme_userrole_arr = unserialize($fme_result[0]->specific_user_role);
									} 
									if (!empty($fme_roles)) {
										?>
										<select class="fme-ccfw-user-roles" id="fme-ccfw-user-froles" multiple="multiple" name="">
											<?php
											foreach ($fme_roles as $key => $value) {
												?>
												<option 
												<?php if (in_array(strtolower($value), $fme_userrole_arr)) { ?> 
													selected="selected" 
												<?php } ?> 
												value="<?php echo filter_var(strtolower($value)); ?>">
													<?php echo filter_var($value); ?>
												</option>
												<?php
											}
											?>
										</select>
										<span style="display:block;"><i><?php echo esc_html__('Leave empty to diplay field for all user roles and ignore radio selection', 'conditional-checkout-fields-for-woocommerce'); ?></i></span>
										<?php
									}
									?>
								</div>
							</td>
						</tr>
						<tr id="fme_condition_fields_groupdata">
							<td colspan="6">
								<p><strong><?php echo esc_html__('Condition(s) to Meet:', 'conditional-checkout-fields-for-woocommerce'); ?></strong></p>
								<p><?php echo esc_html__('Relation Between Conditions is "AND" & Relations with Group is "OR"', 'conditional-checkout-fields-for-woocommerce'); ?></p>
								<button type="button" id="fme-ccfw-add_group_cond" class="btn btn-primary fme-ccfw-add_group_cond"><?php echo esc_html__('Add a Condition Group', 'conditional-checkout-fields-for-woocommerce'); ?></button>
							</td>			
						</tr>

						<?php 
						$Condition_field_array = unserialize($fme_result[0]->cfield);
						if (!empty($Condition_field_array)) {

							?>

							<?php
							foreach ($Condition_field_array as $key => $value) {

								?>
								<tr>
									<td colspan="6">
										<table class="table table-striped table-sm fme-ccfw-conditional_table_set" >
											<?php 

											foreach ($value as $key => $Condition_val) {
												
												?>
												<tr>
													<td>
														<div class="form-group">
															<label for="type"><?php echo esc_html__('Action', 'conditional-checkout-fields-for-woocommerce'); ?></label>
															<select class="form-control" id="fme-ccfw-faction" name="fmeshowif[]">
																<option <?php selected('', $Condition_val[0]); ?> value=""><?php echo esc_html__('Select', 'conditional-checkout-fields-for-woocommerce'); ?></option>
																<option <?php selected('Show', $Condition_val[0]); ?> value="Show"><?php echo esc_html__('Show', 'conditional-checkout-fields-for-woocommerce'); ?></option>
																<option <?php selected('Hide', $Condition_val[0]); ?> value="Hide"><?php echo esc_html__('Hide', 'conditional-checkout-fields-for-woocommerce'); ?></option>
															</select>
														</div>
													</td>
													<td colspan="1">
														<?php 


														// $fme_condition_fields = $wpdb->get_results( $wpdb->prepare( 'SELECT field_id, field_name  FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_type!="" AND type = %s ORDER BY length(sort_order), sort_order', $type)); 
														// haseeb changed 
														$fme_condition_fields = $wpdb->get_results( $wpdb->prepare( 'SELECT field_id, field_name  FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_type!="" ORDER BY field_id ASC' ) );
														?>
														<div class="form-group">
															<label for="type"><?php echo esc_html__('If value of', 'conditional-checkout-fields-for-woocommerce'); ?></label>
															<select class="form-control" id="fme-ccfw-fvalueof" name="fmecfield[]">
																<?php 
																if ( !empty($fme_condition_fields )) {
																	?>
																	<option <?php selected('', $Condition_val[1]); ?> value=""><?php echo esc_html__('Select', 'conditional-checkout-fields-for-woocommerce'); ?></option>
																	<?php
																	foreach ($fme_condition_fields as $key => $fmecondval) {
																		?>
																		<option <?php selected($fmecondval->field_name, $Condition_val[1]); ?>  value="<?php echo esc_attr($fmecondval->field_name); ?>"><?php echo esc_attr($fmecondval->field_name); ?></option>
																		<?php 
																	}
																} else {
																	?>
																	<option <?php selected('', $Condition_val[1]); ?> value=""><?php echo esc_html__('Select', 'conditional-checkout-fields-for-woocommerce'); ?></option>
																	<?php
																} 
																?>
															</select>
														</div>
													</td>
													<td>
														<div class="form-group">
															<label for="type"><?php echo esc_html__('Condition', 'conditional-checkout-fields-for-woocommerce'); ?></label>
															<select class="form-control" id="fme-ccfw-fcondition"  name="fmeccondition[]">
																<option <?php selected('', $Condition_val[2]); ?>  value=""><?php echo esc_html__('Select', 'conditional-checkout-fields-for-woocommerce'); ?></option>
																<option <?php selected('is_empty', $Condition_val[2]); ?>  value="is_empty"><?php echo esc_html__('is empty', 'conditional-checkout-fields-for-woocommerce'); ?></option>
																<option <?php selected('is_not_empty', $Condition_val[2]); ?>  value='is_not_empty'><?php echo esc_html__('is not empty', 'conditional-checkout-fields-for-woocommerce'); ?></option>
																<option <?php selected('is_equal_to', $Condition_val[2]); ?> value="is_equal_to"><?php echo esc_html__('is equal to ', 'conditional-checkout-fields-for-woocommerce'); ?></option>
																<option <?php selected('is_not_equal_to', $Condition_val[2]); ?> value="is_not_equal_to"><?php echo esc_html__('is not equal to', 'conditional-checkout-fields-for-woocommerce'); ?></option>
																<option <?php selected('is_checked', $Condition_val[2]); ?> value="is_checked"><?php echo esc_html__('is checked', 'conditional-checkout-fields-for-woocommerce'); ?></option>
															</select>
														</div>
													</td>
													<td>
														<div class="form-group showf">
															<label for="type"><?php echo esc_html__('value', 'conditional-checkout-fields-for-woocommerce'); ?></label>
															<input type="text" id="fme-ccfw-fconditionvalue" placeholder="Enter condition value" name="fmeccondition_value[]" class="form-control" value="<?php echo esc_attr( $Condition_val[3]); ?>">
														</div>
													</td>
													<td>
														<div class="form-group">
															<label for="type"><?php echo esc_html__('Action', 'conditional-checkout-fields-for-woocommerce'); ?></label><br>
															<button type="button" class="btn btn-danger fme-ccfw-remove-condtions-row" id="fme-ccfw-remove-condtions-row"><small>x</small></button>
														</div>
													</td>
												</tr>
												<?php	
											}

											?>
											<tr>
												<td colspan="6">
													<div class="form-group">
														<button type="button" class="btn btn-success fme-ccfw-add-condtions-ccfw" id="fme-ccfw-add-condtions-ccfw">Add Condtion</button>
														<button type="button" class="btn btn-danger fme-ccfw-remove-group" id="fme-ccfw-remove-group">Remove Group</button>
													</div>
												</td>
											</tr>
										</table>
									</td>
								</tr>

								<?php
							}
						}

						?>

						<tr id="fme-ccfw-optionbtn"
							<?php 
							if ('default' != $fme_result[0]->field_mode &&'select'==$fme_result[0]->field_type || 'multiselect' == $fme_result[0]->field_type || 'radio' == $fme_result[0]->field_type) {
								echo 'style=visibility:inherit';
							} else {
								echo 'style=display:none';
							}
							?>
							>
							<th colspan="4">
								<button id="fme-ccfw-btn-Add" type="button" class="btn btn-primary" data-toggle="tooltip" data-original-title="Add more controls"><?php echo esc_html__('Add Options', 'conditional-checkout-fields-for-woocommerce'); ?></button>
							</th>
						</tr>
						<tr class="fme-ccfw-option-fields" id="fme-ccfw-option-fields"
							<?php 
							if ('default' != $fme_result[0]->field_mode && 'select'==$fme_result[0]->field_type || 'multiselect' == $fme_result[0]->field_type || 'radio' == $fme_result[0]->field_type) {
								echo 'style=visibility:inherit';
							} else {
								echo 'style=visibility:hidden';
							}
							?>
							>
							<th><?php echo esc_html__('Option Name', 'conditional-checkout-fields-for-woocommerce'); ?></th>
							<th><?php echo esc_html__('Option Value', 'conditional-checkout-fields-for-woocommerce'); ?></th>
							<th><?php echo esc_html__('Option Price', 'conditional-checkout-fields-for-woocommerce'); ?></th>
							<th><?php echo esc_html__('Action', 'conditional-checkout-fields-for-woocommerce'); ?></th>
						</tr>
						<?php 
						if ( '' != $fme_result[0]->options && 'default' != $fme_result[0]->field_mode  && 'select'==$fme_result[0]->field_type || 'multiselect' == $fme_result[0]->field_type || 'radio' == $fme_result[0]->field_type) {
							
							$options = unserialize($fme_result[0]->options);
							foreach ($options as $key => $value) {
								?>
								<tr class="fme-ccfw-option-fields" id="fme-ccfw-option-fields"
									<?php 
									if ('select'==$fme_result[0]->field_type || 'multiselect' == $fme_result[0]->field_type || 'radio' == $fme_result[0]->field_type) {
										echo 'style=visibility:inherit';
									} else {
										echo 'style=visibility:hidden';
									}
									?>
									>
									<td>
										<input name="fme_ccfw_option_name[]" id="fme_ccfw_foption_name" placeholder="Enter option name" type="text" value = "<?php echo esc_attr($value[0]); ?>" class="form-control" />
									</td>
										<td>
											<input name= "fme_ccfw_option_value[]" id="fme_ccfw_foption_value"  placeholder="Enter option value" type="text" value = "<?php echo esc_attr($value[1]); ?>" class="form-control" />	
										</td>
										<td><input name= "fme_ccfw_option_price[]" id="fme_ccfw_foption_price" placeholder="Enter option price" type="text" value = "<?php echo esc_attr($value[2]); ?>" class="form-control" />
										</td>
										<td>
											<button type="button" class="btn btn-danger fme-ccfw-remove-option">Remove</button>
										</td>
									</tr>
									<?php
							}
						}	
						?>
					</tbody>
			</table>
			<?php
			wp_die();
		}



		public function fme_ccfw_save_fielddata() {

			if (!current_user_can( 'manage_woocommerce' )) {

				wp_die();
			}
			check_ajax_referer ('fme_ccfw_admin_ajax_nonce', 'security');
			global $wpdb;

			$fmecffw_type = isset($_POST['type']) ? filter_var($_POST['type']) : '';

			$fme_cffw_field_type = isset($_POST['fme_cffw_field_type']) ? filter_var($_POST['fme_cffw_field_type']) : '';
			$fme_ccfw_field_fieldname = isset($_POST['fme_ccfw_field_fieldname']) ? map_deep(wp_unslash($_POST['fme_ccfw_field_fieldname']), 'sanitize_text_field') : '';
			$fme_ccfw_field_label = isset($_POST['fme_ccfw_field_label']) ? map_deep(wp_unslash($_POST['fme_ccfw_field_label']), 'sanitize_text_field') : '';
			$fme_ccfw_field_ed = isset($_POST['fme_ccfw_field_ed']) ? filter_var($_POST['fme_ccfw_field_ed']) : '';
			$fme_ccfw_field_placeholder = isset($_POST['fme_ccfw_field_placeholder']) ? map_deep(wp_unslash($_POST['fme_ccfw_field_placeholder']), 'sanitize_text_field') : '';

			$fme_ccfw_field_class = isset($_POST['fme_ccfw_field_class']) ? filter_var($_POST['fme_ccfw_field_class']) : '';
			
			$fme_ccfw_field_price = isset($_POST['fme_ccfw_field_price']) ? map_deep(wp_unslash($_POST['fme_ccfw_field_price']), 'sanitize_text_field') : '';
			
			$fme_ccfw_field_required = isset($_POST['fme_ccfw_field_required']) ? filter_var($_POST['fme_ccfw_field_required']) : '';
			
			$fme_ccfw_field_taxable = isset($_POST['fme_ccfw_field_taxable']) ? filter_var($_POST['fme_ccfw_field_taxable']) : '';

			$fme_ccfw_min_date = isset($_POST['fme_ccfw_min_date']) ? filter_var($_POST['fme_ccfw_min_date']) : '';
			
			$fme_ccfw_field_specfic_pc_checked = isset($_POST['fme_ccfw_field_specfic_pc']) ? filter_var($_POST['fme_ccfw_field_specfic_pc']) : '';

			$fme_ccfw_fselectpc_type = isset($_POST['fme_ccfw_fselectpc_type']) ? filter_var($_POST['fme_ccfw_fselectpc_type']) : '';

			$fme_ccfw_fspc = isset($_POST['fme_ccfw_fspc']) ? array_map('filter_var', $_POST['fme_ccfw_fspc']) : array();

			$fme_field_specfic_userrole_checked = isset($_POST['fme_field_specfic_userrole']) ? filter_var($_POST['fme_field_specfic_userrole']) : '';

			$fme_field_specific_userrole = isset($_POST['fme_field_specific_userrole']) ? filter_var($_POST['fme_field_specific_userrole']) : '';

			$fme_ccfw_user_froles_val = isset($_POST['fme_ccfw_user_froles_val']) ? array_map('filter_var', $_POST['fme_ccfw_user_froles_val']) : array();

			$fme_ccfw_condtion_array = isset($_POST['fme_condition_arr']) ? map_deep( wp_unslash( $_POST['fme_condition_arr'] ), 'sanitize_text_field' ):'';

			$fme_ccfw_condtion_array_val = serialize($fme_ccfw_condtion_array);

			$fme_ccfw_foption_name = isset($_POST['fme_ccfw_foption_name']) ? array_map('filter_var', $_POST['fme_ccfw_foption_name']) : '';

			$fme_ccfw_foption_value = isset($_POST['fme_ccfw_foption_value']) ? array_map('filter_var', $_POST['fme_ccfw_foption_value']) : '';

			$fme_ccfw_foption_price = isset($_POST['fme_ccfw_foption_price']) ? array_map('filter_var', $_POST['fme_ccfw_foption_price']) : '';

			$fme_form_action = isset($_POST['fme_form_action']) ? filter_var($_POST['fme_form_action']) : '';

			$fme_auto_complete = 'given-name';

			$field_extensions = isset($_POST['fme_ccfw_field_extensions']) ? map_deep(wp_unslash($_POST['fme_ccfw_field_extensions']), 'sanitize_text_field') : '';


			$fme_ccfw_uploadsize_array = isset($_POST['fme_ccfw_uploadsize_array']) ? array_map('sanitize_text_field', wp_unslash($_POST['fme_ccfw_uploadsize_array'])) : '';

			$fme_ccfw_uploadsize_arrays = serialize($fme_ccfw_uploadsize_array);

			$field_headingtag = isset($_POST['fme_ccfw_field_heading_type']) ? strtolower(filter_var($_POST['fme_ccfw_field_heading_type'])) : '';

			$fme_sort_orders = $wpdb->get_results( $wpdb->prepare( 'SELECT Count(*) + 1 AS rowcount FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_type!="" AND type = %s' , $fmecffw_type)); 

			$fme_sort_order = $fme_sort_orders[0]->rowcount; 

			if ('' != $fme_ccfw_foption_name && '' != $fme_ccfw_foption_value) {

				$fme_array_foption = array();
				for ($i=0; $i < count($fme_ccfw_foption_name) ; $i++) { 
					$fme_array_option = array();
					array_push($fme_array_option, wp_unslash($fme_ccfw_foption_name[$i]));
					array_push($fme_array_option, wp_unslash($fme_ccfw_foption_value[$i]));
					array_push($fme_array_option, wp_unslash($fme_ccfw_foption_price[$i]));
					array_push($fme_array_foption, $fme_array_option);
				}
				$fme_array_foptions = serialize($fme_array_foption);
			} else {
				$fme_array_foptions = array();
			}

			if (empty($fme_ccfw_fspc)) {
				$fme_ccfw_fspc_val = $fme_ccfw_fspc;
			} else {
				$fme_ccfw_fspc_val = serialize($fme_ccfw_fspc);
			}

			if (empty($fme_ccfw_user_froles_val)) {
				$fme_ccfw_user_froles_value = $fme_ccfw_user_froles_val;
			} else {
				$fme_ccfw_user_froles_value = serialize($fme_ccfw_user_froles_val);
			}
			
			if ('add'==$fme_form_action) {

				if ('' != $fme_cffw_field_type && '' != $fmecffw_type && '' != $fme_ccfw_field_label) {

					$tablename = $wpdb->prefix . 'FmeCCFA_fields';
					$result = $wpdb->query($wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'FmeCCFA_fields (`field_name`, `field_label`, `field_placeholder`, `is_required`,`is_taxable`,`is_min_date`, `is_enable`, `is_class`, `sort_order`, `autocomplete`, `field_type`, `type`, `options`, `field_mode`, `field_extensions`, `field_file_size`, `field_price`, `cfield`, `specific_pc`, `ischeckpc`, `selected_pc`, `userrole_check`, `specific_user_role`,`heading_type`) values (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)',
						$fmecffw_type . '_' . preg_replace('/\s+/', '_', $fme_ccfw_field_fieldname),
						$fme_ccfw_field_label,
						$fme_ccfw_field_placeholder,
						$fme_ccfw_field_required,
						$fme_ccfw_field_taxable,
						$fme_ccfw_min_date,
						$fme_ccfw_field_ed,
						$fme_ccfw_field_class,
						$fme_sort_order,
						$fme_auto_complete,
						$fme_cffw_field_type,
						$fmecffw_type,
						$fme_array_foptions,
						$fmecffw_type . '_additional',
						$field_extensions,
						$fme_ccfw_uploadsize_arrays,
						$fme_ccfw_field_price,
						$fme_ccfw_condtion_array_val,
						$fme_ccfw_fselectpc_type,
						$fme_ccfw_field_specfic_pc_checked,
						$fme_ccfw_fspc_val,
						$fme_field_specific_userrole,
						$fme_ccfw_user_froles_value,
						$field_headingtag
					));
				}
			} else {

				$tablename = $wpdb->prefix . 'FmeCCFA_fields';
				$sql = $wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'FmeCCFA_fields SET field_name = %s, field_label = %s, field_placeholder = %s, is_required = %d, is_taxable = %d, is_min_date = %s, is_enable = %d, is_class = %s, autocomplete = %s, field_type = %s, type = %s, options = %s, field_extensions = %s, field_file_size = %s, field_price = %s, cfield = %s, specific_pc= %s, ischeckpc= %s, selected_pc = %s, userrole_check=%s, specific_user_role= %s, heading_type = %s WHERE field_id = %d',
					preg_replace('/\s+/', '_', $fme_ccfw_field_fieldname),
					$fme_ccfw_field_label,
					$fme_ccfw_field_placeholder,
					$fme_ccfw_field_required,
					$fme_ccfw_field_taxable,
					$fme_ccfw_min_date,
					$fme_ccfw_field_ed,
					$fme_ccfw_field_class,
					$fme_auto_complete,
					$fme_cffw_field_type,
					$fmecffw_type,
					$fme_array_foptions,
					$field_extensions,
					$fme_ccfw_uploadsize_arrays,
					$fme_ccfw_field_price,
					$fme_ccfw_condtion_array_val,
					$fme_ccfw_fselectpc_type,
					$fme_ccfw_field_specfic_pc_checked,
					$fme_ccfw_fspc_val,
					$fme_field_specific_userrole,
					$fme_ccfw_user_froles_value,
					$field_headingtag,
					$fme_form_action
				));
			}
			wp_die();
		}

		public function fme_ccfw_settings_tabs_array( $tabs ) {
			$tabs['fme_ccfw_tab'] = __('Conditional Checkout Fields', 'conditional-checkout-fields-for-woocommerce');
			return $tabs;
		}

		public function fme_fvg_admin_scripts() {	

			if (is_admin() && isset($_GET['tab']) &&  'fme_ccfw_tab' == $_GET['tab']) {

				wp_enqueue_script('jquery');
				wp_enqueue_style( 'bootstrap-min-css', plugins_url( 'assets/css/bootstrap.min.css', __FILE__ ), false , 1.0 );

				wp_enqueue_style( 'fme_ccfw_setting_css', plugins_url( 'assets/css/fme-fvg-admin.css', __FILE__ ), false , 1.0 );
				wp_enqueue_style( 'fme_ccfw_button_datatable_css', 'https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css', false , 1.0 );

				wp_enqueue_style( 'fme_ccfw_buttons_datatable_css', 'https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css', false , 1.0 );

				wp_enqueue_style( 'fme_ccfw_select_datatable_css', 'https://cdn.datatables.net/select/1.3.3/css/select.dataTables.min.css', false , 1.0 );


				wp_enqueue_style( 'fme_ccfw_reorder_datatable_css', 'https://cdn.datatables.net/rowreorder/1.2.7/css/rowReorder.dataTables.min.css', false , 1.0 );

				wp_enqueue_script( 'select2-min-js', plugins_url( 'assets/js/select2.min.js', __FILE__ ), false, 1.0 );
				wp_enqueue_style( 'select2-min-css', plugins_url( 'assets/css/select2.min.css', __FILE__ ), false , 1.0 );

				wp_enqueue_script( 'bootstrap-min-js', plugins_url( 'assets/js/bootstrap.min.js', __FILE__ ), false, 1.0 );

				wp_enqueue_script( 'fme_ccfw_setting_js', plugins_url( 'assets/js/fme-fvg-admin.js', __FILE__ ), false, '1.2.9' );

				wp_enqueue_script( 'fme_ccfw_datatables_js', 'https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js', false, 1.0 );
				wp_enqueue_script( 'fme_ccfw_datatables_js', 'https://code.jquery.com/jquery-3.5.1.js', false, 1.0 );

				wp_enqueue_script( 'fme_ccfw_reorder_datatables_js', 'https://cdn.datatables.net/rowreorder/1.2.7/js/dataTables.rowReorder.min.js', false, 1.0 );

				wp_enqueue_script('fme_ccfw_button_datatables_js', 'https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js', false, '1.0');

				wp_enqueue_script('fme_ccfw_jszip_datatables_js', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', false, '1.0');

				wp_enqueue_script('fme_ccfw_jszip_datatables_js', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', false, '1.0');

				wp_enqueue_script('fme_ccfw_pdfmake_datatables_js', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', false, '1.0');

				wp_enqueue_script('fme_ccfw_pdffontmake_datatables_js', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', false, '1.0');

				wp_enqueue_script('fme_ccfw_buttons_datatables_js', 'https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js', false, '1.0');

				wp_enqueue_script('fme_ccfw_printbuttons_datatables_js', 'https://cdn.datatables.net/buttons/1.7.0/js/buttons.print.min.js', false, '1.0');

				wp_enqueue_script('fme_ccfw_select_datatables_js', 'https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js', false, '1.0');

				$fme_ccfw_data = array(
					'admin_url' => admin_url('admin-ajax.php'),
					'admin_ajax_nonce' => wp_create_nonce('fme_ccfw_admin_ajax_nonce')
				);
				wp_localize_script('fme_ccfw_setting_js', 'fme_ccfw_php_vars', $fme_ccfw_data);
				wp_localize_script('fme_ccfw_setting_js', 'ajax_url_add_pq', array('ajax_url_add_pq_data' => admin_url('admin-ajax.php')));
			}
			if (is_admin()) {

				wp_enqueue_script('extCCFA_order_timepickrr_js', plugin_dir_url( __FILE__ ) . 'assets/js/wickedpicker.js', false , 1.0);
				wp_enqueue_style('extCCFA_order_timepickrr_css', plugin_dir_url( __FILE__ ) . 'assets/css/wickedpicker.css', false , 1.0);
			}
		}
	}

	new Fme_CCFW_Admin();
}
