<?php
global $woocommerce;
global $post;
global $wpdb;
$field_type = isset($_REQUEST['field_type']) ? filter_var($_REQUEST['field_type']) : '';

if (isset($_REQUEST['section'])) {
	$section = filter_var($_REQUEST['section']);
} else {
	$section = 'billing';
}


	$fme_fields_result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'FmeCCFA_fields WHERE field_type!="" AND type = %s ORDER BY length(sort_order), sort_order', filter_var($section)));  

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



?>
<form method="post">
	<?php 
	$additionalLabelval = get_option('additionalfieldlabel');
	if ('' == $additionalLabelval) {
		$addlabel = 'Additional Fields';
	} else {
		$addlabel = $additionalLabelval;
	}
	if ('additional' == $section) {

		?>
		<span>
			<label><?php echo esc_html__('Additional Fields Label', 'conditional-checkout-fields-for-woocommerce'); ?></label><br/>
			<input type="text" name="additionalfieldlabel" id="additionalfieldlabel" value="<?php echo esc_attr($addlabel); ?>" style="margin-bottom: 10px;width: 25%">
		</span>
		<?php

	}

	?>
	<table id='fmeccfw_checkout_form_field' class="display fme_table_field" style="width:100%">
		<thead>
			<tr>        
				<th colspan="12">
					<button type="button" data-toggle="modal" onclick="fmeccfwOpenEditFieldForm('add','<?php echo isset($section) ? filter_var($section) : ''; ?>');" data-target="#fme_newfield_modal" class="button button-primary"><?php echo esc_html__('+ Add field', 'conditional-checkout-fields-for-woocommerce'); ?>
					</button>
			</th>
		</tr>
		<tr>   
			<th class="name"><?php echo esc_html__('Sort' , 'conditional-checkout-fields-for-woocommerce'); ?></th>
			<th class="name"><?php echo esc_html__('Label' , 'conditional-checkout-fields-for-woocommerce'); ?></th>
			<th class="name"><?php echo esc_html__('Name' , 'conditional-checkout-fields-for-woocommerce'); ?></th>
			<th class="status"><?php echo esc_html__('Type' , 'conditional-checkout-fields-for-woocommerce'); ?></th>
			<th class="name"><?php echo esc_html__('Default' , 'conditional-checkout-fields-for-woocommerce'); ?></th>   
			<th class="status"><?php echo esc_html__('Required' , 'conditional-checkout-fields-for-woocommerce'); ?></th>   
			<th class="status"><?php echo esc_html__('Enable' , 'conditional-checkout-fields-for-woocommerce'); ?></th> 
			<th class="status"><?php echo esc_html__('Action' , 'conditional-checkout-fields-for-woocommerce'); ?></th>   
		</tr>
	</thead>
	<tbody class="ui-sortable fme_cpffw_checkoutfields_data">
		<?php 
		$i = 0;
		foreach ($fme_fields_result as $key => $bfield) {
			?>
			<tr id="<?php echo 'fme_row' . filter_var($bfield->field_id); ?>" field_type="<?php echo esc_attr($bfield->type); ?>">
				<td class="sort ui-sortable-handle"><span><?php echo esc_attr($bfield->sort_order); ?></span></td>
				<td><?php echo esc_attr($bfield->field_label); ?></td>
				<td><?php echo esc_attr($bfield->field_name); ?></td>
				<td><?php echo esc_attr(ucfirst($bfield->field_type)); ?></td>
				<td>
					<?php 
					if ('default'==$bfield->field_mode) {
						echo esc_html__(' Yes', 'conditional-checkout-fields-for-woocommerce');
					} else {
						echo esc_html__('No', 'conditional-checkout-fields-for-woocommerce');
					}
					?>
				</td>
				<td>
					<span style="display:none;">
						<?php echo  esc_attr($bfield->is_required); ?>
					</span>
					<?php
					if ('1'==$bfield->is_required) {
						echo '<span class="dashicons dashicons-yes" title="Enable"></span>';  
					}
					?>
				</td>
				<td>
					<span style="display:none;">
						<?php echo  esc_attr($bfield->is_enable); ?>
					</span>
					<?php
					if ('1'==$bfield->is_enable) {
						echo '<span class="dashicons dashicons-yes" title="Enable"></span>';  
					}
					?>
				</td>
				<td class="td_edit action">
					<button type="button"  data-toggle="modal" data-target="#fme_newfield_modal" class="button action-btn f_edit_btn" onclick="fmeccfwOpenEditFieldForm(<?php echo esc_attr($bfield->field_id); ?>,'<?php echo filter_var($section); ?>')"><?php echo esc_html__('Edit', 'conditional-checkout-fields-for-woocommerce'); ?>
				</button>
				<?php 
				if ($bfield->type . '_additional' == $bfield->field_mode) {
					?>
					<button type="button" class="button button-danger fme_fdelete_btn" onclick="fmeccfw_Delete_fields(<?php echo esc_attr($bfield->field_id); ?>,'<?php echo filter_var($section); ?>')"><?php echo esc_html__('Delete', 'conditional-checkout-fields-for-woocommerce'); ?>
				</button>
					<?php
				}
				?>
		</td>
	</tr>
			<?php
		}
		?>
</tbody>
<tfoot>
	<tr>
		<th class="name"><?php echo esc_html__('Seq' , 'conditional-checkout-fields-for-woocommerce'); ?></th>
		<th class="name"><?php echo esc_html__('Name' , 'conditional-checkout-fields-for-woocommerce'); ?></th>
		<th class="status"><?php echo esc_html__('Type' , 'conditional-checkout-fields-for-woocommerce'); ?></th>
		<th class="status"><?php echo esc_html__('Placeholder' , 'conditional-checkout-fields-for-woocommerce'); ?></th>   
		<th class="status"><?php echo esc_html__('Required' , 'conditional-checkout-fields-for-woocommerce'); ?></th>   
		<th class="status"><?php echo esc_html__('Enable' , 'conditional-checkout-fields-for-woocommerce'); ?></th>  
		<th class="status"><?php echo esc_html__('Action' , 'conditional-checkout-fields-for-woocommerce'); ?></th>    
	</tr>
</tfoot>
</table>
</form>

<div class="modal fade" id="fme_newfield_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="exampleModalLabel">
					<?php
					// if ( isset($_REQUEST['section'] ) ) {
						echo filter_var(ucfirst(filter_var($section))) . ' Fields';
					// } 
					?>
				</h4>
			</div>
			<div class="modal-body">
				
			</div>
			<div class="modal-footer">
				<div id="fme_success_alert_save" class="updated inline" style="display: none;text-align:left;width: 40%;">
					<p><strong><?php echo esc_html__('Your settings have been saved.', 'conditional-checkout-fields-for-woocommerce'); ?></strong></p>
				</div>
				<button type="button" id="fme-ccfw-savefielddata" data-attr="" style="float: left;" class="button-primary" onclick="fme_save_form_field('<?php echo filter_var($section); ?>');"><?php echo esc_html__('Save Changes', 'conditional-checkout-fields-for-woocommerce'); ?></button>
				<button type="button" class="button-default" data-dismiss="modal"><?php echo esc_html__('Close', 'conditional-checkout-fields-for-woocommerce'); ?></button>
				
			</div>
		</div>
	</div>
</div>

