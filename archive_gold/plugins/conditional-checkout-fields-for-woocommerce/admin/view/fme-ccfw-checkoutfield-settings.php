<?php
$selected_bsection = isset($_REQUEST['section']) ? filter_var($_REQUEST['section']) : '';
$selected_ssection = isset($_REQUEST['section']) ? filter_var($_REQUEST['section']) : '';
$selected_asectional = isset($_REQUEST['section']) ? filter_var($_REQUEST['section']) : '';

if (isset($_REQUEST['section']) && 'billing' == $_REQUEST['section']) {
	$selected_bsection = 'current';
} 


if (!isset($_REQUEST['section'])) {
	$selected_bsection = 'current';
}

if (isset($_REQUEST['section']) && 'shipping' == $_REQUEST['section']) {
	$selected_ssection = 'current';
} 
if (isset($_REQUEST['section']) && 'additional' == $_REQUEST['section']) {
	$selected_asectional = 'current';
}

?>
<section class="home-content-top">
	<div class="container-fluid">    
		<div class="clearfix"></div>
		<ul class="subsubsub">
			<li>
				<a href="<?php echo filter_var(admin_url()) . 'admin.php?page=wc-settings&tab=fme_ccfw_tab&section=billing'; ?>" class="<?php echo filter_var($selected_bsection); ?>"><?php echo esc_html__('Billing Fields', 'conditional-checkout-fields-for-woocommerce'); ?></a> | 
			</li>
			<li><a href="<?php echo filter_var(admin_url()) . 'admin.php?page=wc-settings&tab=fme_ccfw_tab&section=shipping'; ?>" class="<?php echo filter_var($selected_ssection); ?>"><?php echo esc_html__('Shipping Fields', 'conditional-checkout-fields-for-woocommerce'); ?></a> | 
			</li>
			<li><a href="<?php echo filter_var(admin_url()) . 'admin.php?page=wc-settings&tab=fme_ccfw_tab&section=additional'; ?>" class="<?php echo filter_var($selected_asectional); ?>"><?php echo esc_html__('Additional Fields', 'conditional-checkout-fields-for-woocommerce'); ?></a>  
			</li>
		</ul><br>
		<div id="fme_success_alert" class="updated inline" style="display: none;">
			<p><strong> <?php echo esc_html('Your settings have been saved.', 'conditional-checkout-fields-for-woocommerce'); ?></strong></p>
		</div>

		<div class="tab-content fmemargin-tops">
			<div class="tab-pane active fade in" id="fmetab1">
				<div class="fmesection col-md-12">
					<!-- Tab panes -->
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane active" id="fme_ccfw_billing_fields">
							<?php require_once( FME_CCFW_PLUGIN_DIR . 'admin/checkout/fme-ccfw-checkout-fields.php' ); ?>
						</div>
					</div>

				</div>
			</div>
		</div>

	</div>
</section>

<input type="button" name="fme_save_fields" id="fme-cpffw_save_fieldsettings" style="margin-left: 1%;
margin-top: 28px;"  class="button-primary" value="Save changes">
