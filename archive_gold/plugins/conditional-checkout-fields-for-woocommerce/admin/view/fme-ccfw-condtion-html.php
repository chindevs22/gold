<?php 
if (isset($_REQUEST['section'])) {
	$section = filter_var($_REQUEST['section']);
} else {
	$section = 'billing';
}
?>
<tr>
	<td colspan="6">
		<table class="table table-striped table-sm fme-ccfw-conditional_table_set" >
			<tbody>
				<tr class="fme_condition_fields_addcondtion"></tr>
				<tr>
					<td>
						<div class="form-group">
							<label for="type"><?php echo esc_html__('Action', 'conditional-checkout-fields-for-woocommerce'); ?></label>
							<select class="form-control" id="fme-ccfw-faction" name="fmeshowif[]">
								<option <?php selected('', $fme_result[0]->showif); ?> value=""><?php echo esc_html__('Select', 'conditional-checkout-fields-for-woocommerce'); ?></option>
								<option <?php selected('Show', $fme_result[0]->showif); ?> value="Show"><?php echo esc_html__('Show', 'conditional-checkout-fields-for-woocommerce'); ?></option>
								<option <?php selected('Hide', $fme_result[0]->showif); ?> value="Hide"><?php echo esc_html__('Hide', 'conditional-checkout-fields-for-woocommerce'); ?></option>
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
								if ( !empty( $fme_condition_fields ) ) {
									?>
									<option <?php selected('', $fme_result[0]->cfield); ?> value=""><?php echo esc_html__('Select', 'conditional-checkout-fields-for-woocommerce'); ?></option>
									<?php
									foreach ($fme_condition_fields as $key => $fmecondval) {
										?>
										<option <?php selected($fmecondval->field_name, $fme_result[0]->cfield); ?>  value="<?php echo esc_attr($fmecondval->field_name); ?>"><?php echo esc_attr($fmecondval->field_name); ?></option>
										<?php 
									}
								} else {
									?>
									<option <?php selected('', $fme_result[0]->cfield); ?> value=""><?php echo esc_html__('Select', 'conditional-checkout-fields-for-woocommerce'); ?></option>
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
								<option <?php selected('', $fme_result[0]->ccondition); ?>  value=""><?php echo esc_html__('Select', 'conditional-checkout-fields-for-woocommerce'); ?></option>
								<option <?php selected('is_empty', $fme_result[0]->ccondition); ?>  value="is_empty"><?php echo esc_html__('is empty', 'conditional-checkout-fields-for-woocommerce'); ?></option>
								<option <?php selected('is_not_empty', $fme_result[0]->ccondition); ?>  value="is_not_empty"><?php echo esc_html__('is not empty', 'conditional-checkout-fields-for-woocommerce'); ?></option>
								<option <?php selected('is_equal_to', $fme_result[0]->ccondition); ?> value="is_equal_to"><?php echo esc_html__('is equal to ', 'conditional-checkout-fields-for-woocommerce'); ?></option>
								<option <?php selected('is_not_equal_to', $fme_result[0]->ccondition); ?> value="is_not_equal_to"><?php echo esc_html__('is not equal to', 'conditional-checkout-fields-for-woocommerce'); ?></option>
								<option <?php selected('is_checked', $fme_result[0]->ccondition); ?> value="is_checked"><?php echo esc_html__('is checked', 'conditional-checkout-fields-for-woocommerce'); ?></option>
							</select>
						</div>
					</td>
					<td>
						<div class="form-group showf">
							<label for="type"><?php echo esc_html__('value', 'conditional-checkout-fields-for-woocommerce'); ?></label>
							<input type="text" id="fme-ccfw-fconditionvalue" placeholder="Enter condition value" name="fmeccondition_value[]" class="form-control" value="<?php echo esc_attr($fme_result[0]->ccondition_value); ?>">
						</div>
					</td>
					<td>
						<div class="form-group">
							<label for="type"><?php echo esc_html__('Action', 'conditional-checkout-fields-for-woocommerce'); ?></label><br>
							<button type="button" class="btn btn-danger fme-ccfw-remove-condtions-row" id="fme-ccfw-remove-condtions-row"><small>x</small></button>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="6">
						<div class="form-group">
							<button type="button" data-section="<?php echo filter_var($section); ?>" class="btn btn-success fme-ccfw-add-condtions-ccfw" id="fme-ccfw-add-condtions-ccfw"><?php echo esc_html__('Add Condition', 'conditional-checkout-fields-for-woocommerce'); ?></button>
							<button type="button" class="btn btn-danger fme-ccfw-remove-group" id="fme-ccfw-remove-group"><?php echo esc_html__('Remove Group', 'conditional-checkout-fields-for-woocommerce'); ?></button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</td>
</tr>
