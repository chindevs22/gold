<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$pro_upgarde_features = array(
    __('Advanced BOGO Coupons', 'wt-smart-coupons-for-woocommerce'),
    __('Create and sell store credits coupons', 'wt-smart-coupons-for-woocommerce'),
    __('Email gift cards in beautiful templates', 'wt-smart-coupons-for-woocommerce'),
    __('Create and offer coupons based on customers’ purchase history Eg: First-order coupons, next-order, or nth order coupons.
    ', 'wt-smart-coupons-for-woocommerce'),
    __('Restrict coupons based on country', 'wt-smart-coupons-for-woocommerce'),
    __('Import coupons', 'wt-smart-coupons-for-woocommerce'),
    __('Bulk generate coupons', 'wt-smart-coupons-for-woocommerce'),
    __('Create signup & abandoned cart coupons', 'wt-smart-coupons-for-woocommerce'),
    __('Create combo coupons', 'wt-smart-coupons-for-woocommerce'),
    __('Coupon style customization', 'wt-smart-coupons-for-woocommerce'),
    __('Create and display count down discount sales banner', 'wt-smart-coupons-for-woocommerce'),
    __('Offer give away coupons', 'wt-smart-coupons-for-woocommerce'),
    __('Add coupon expiry in days', 'wt-smart-coupons-for-woocommerce'),
);

?>
<div class="wt_smcpn_upgrade_to_pro_bottom_banner">
    <div class="wt_smcpn_upgrade_to_pro_bottom_banner_hd">
        <?php _e('Upgrade to Smart Coupons for WooCommerce Premium to get hold of advanced features.', 'wt-smart-coupons-for-woocommerce');?>
    </div>
    <a class="wt_smcpn_upgrade_to_pro_bottom_banner_btn" href="https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_comparison&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=<?php echo WEBTOFFEE_SMARTCOUPON_VERSION;?>" target="_blank">
        <?php _e('UPGRADE TO PREMIUM', 'wt-smart-coupons-for-woocommerce'); ?>
    </a>
    <div class="wt_smcpn_upgrade_to_pro_bottom_banner_feature_list_main">
        <?php
            foreach($pro_upgarde_features as $pro_upgarde_feature)
            { 
                ?>
                <div class="wt_smcpn_upgrade_to_pro_bottom_banner_feature_list">
                    <?php echo $pro_upgarde_feature;?>
                </div>
                <?php
            }
        ?> 
    </div>
</div>