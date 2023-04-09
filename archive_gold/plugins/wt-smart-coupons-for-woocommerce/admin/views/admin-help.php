<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 *  @since 1.4.4
 */
?>
<div class="wt-sc-tab-content" data-id="<?php echo esc_attr($target_id);?>">
    <h3>
        <?php _e('Help links', 'wt-smart-coupons-for-woocommerce'); ?>
    </h3> 

    <?php $admin_img_path = WT_SMARTCOUPON_MAIN_URL.'admin/images/'; ?>
    <ul class="wt-smartcoupon-help-links">
        <li>
            <img src="<?php echo esc_attr($admin_img_path);?>documentation.png">
            <h3><?php _e('Documentation', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            <p><?php _e('Refer to our documentation to set up and get started', 'wt-smart-coupons-for-woocommerce'); ?></p>
            <a target="_blank" href="https://www.webtoffee.com/smart-coupons-for-woocommerce-userguide/" class="button button-primary">
                <?php _e('Documentation', 'wt-smart-coupons-for-woocommerce'); ?>        
            </a>
        </li>
        <li>
            <img src="<?php echo esc_attr($admin_img_path);?>support.png">
            <h3><?php _e('Help and Support', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            <p><?php _e('We would love to help you on any queries or issues.', 'wt-smart-coupons-for-woocommerce'); ?></p>
            <a target="_blank" href="https://www.webtoffee.com/support/" class="button button-primary">
                <?php _e('Contact Us', 'wt-smart-coupons-for-woocommerce'); ?>
            </a>
        </li>               
    </ul>
</div>