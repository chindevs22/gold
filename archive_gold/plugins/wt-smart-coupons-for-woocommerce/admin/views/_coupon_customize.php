<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$admin_options = Wt_Smart_Coupon_Admin::get_options();
?>
<div class="wt-sc-tab-content" data-id="<?php echo esc_attr($target_id);?>">
    <div class="form-section">
        
        <div class="wt_section_title">
            <h2 style="margin-bottom:0px;"><?php _e('Coupon layouts','wt-smart-coupons-for-woocommerce') ?></h2>
        </div>
        <div class="coupon_styling_settings available_coupons">
            <div class="section-sub-title">
                <h4><?php _e('Available coupons','wt-smart-coupons-for-woocommerce') ?></h4>
            </div>
            <div style="float:left; width:50%;">
                <div class="form-item">
                    <label> <?php _e('Background color','wt-smart-coupons-for-woocommerce') ?> </label>
                    <div class="form-element">
                        <input name="wt_active_coupon_bg_color" id="wt_active_coupon_bg_color" type="text" value="<?php echo esc_attr($admin_options['wt_active_coupon_bg_color']); ?>" class="wt_colorpick" data-default-color="#2890a8"  />
                    </div>
                </div>

                <div class="form-item">
                    <label> <?php _e('Foreground color','wt-smart-coupons-for-woocommerce') ?> </label>
                    <div class="form-element">
                        <input name="wt_active_coupon_border_color" id="wt_active_coupon_border_color" type="text" value="<?php echo esc_attr($admin_options['wt_active_coupon_border_color']); ?>" class="wt_colorpick" data-default-color="#ffffff"  />
                    </div>
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="coupon_preview active_coupon_preview"></div>
            </div>

        </div> <!-- Available Coupons -->

        <div class="coupon_styling_settings used_coupons">
            <div class="section-sub-title">
                <h4><?php _e('Used coupons','wt-smart-coupons-for-woocommerce') ?></h4>
            </div>
            
            <div class="form-item">
                <?php 
                    $wt_display_used_coupons =  $admin_options['wt_display_used_coupons'];
                ?>
                <input type="checkbox" style="float:left; margin-top:3px; margin-right:10px;" id="wt_display_used_coupons" name="wt_display_used_coupons" <?php echo esc_attr($wt_display_used_coupons ? 'checked="checked"' : ''); ?>  ><label> <?php _e('Display used coupons in My account?','wt-smart-coupons-for-woocommerce'); ?></label>
            </div>
            <div style="float:left; width:50%;">
                <div class="form-item">
                    <label> <?php _e('Background color','wt-smart-coupons-for-woocommerce') ?> </label>
                    <div class="form-element">
                        <input name="wt_used_coupon_bg_color" id="wt_used_coupon_bg_color" type="text" value="<?php echo esc_attr($admin_options['wt_used_coupon_bg_color']); ?>" class="wt_colorpick" data-default-color="#eeeeee"  />
                    </div>

                </div>

                <div class="form-item">
                    <label> <?php _e('Foreground color','wt-smart-coupons-for-woocommerce') ?> </label>
                    <div class="form-element">
                        <input name="wt_used_coupon_border_color" id="wt_used_coupon_border_color" type="text" value="<?php echo esc_attr($admin_options['wt_used_coupon_border_color']); ?>" class="wt_colorpick" data-default-color="#000000"  />
                    </div>
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="coupon_preview used_coupon_preview"></div>
            </div>

        </div> <!-- Used Coupons -->


        <div class="coupon_styling_settings expired_coupons">
            <div class="section-sub-title">
                <h4><?php _e('Expired coupons','wt-smart-coupons-for-woocommerce') ?></h4>
            </div>

            <div class="form-item">
                <?php 
                    $wt_display_expired_coupons =  $admin_options['wt_display_expired_coupons']; 
                    $checked = '';
                    if( $wt_display_expired_coupons ) {
                        $checked = 'checked = checked';
                    }
                
                ?>
                <input type="checkbox" style="float:left; margin-top:3px; margin-right:10px;" id="wt_display_expired_coupons" name="wt_display_expired_coupons" <?php echo esc_attr($checked); ?> ><label> <?php _e('Display expired coupons in My account?','wt-smart-coupons-for-woocommerce'); ?></label>
            </div>
            <div style="float:left; width:50%;">
                <div class="form-item">
                    <label> <?php _e('Background color','wt-smart-coupons-for-woocommerce') ?> </label>
                    <div class="form-element">
                        <input name="wt_expired_coupon_bg_color" id="wt_expired_coupon_bg_color" type="text" value="<?php echo esc_attr($admin_options['wt_expired_coupon_bg_color']); ?>" class="wt_colorpick" data-default-color="#f3dfdf"  />
                    </div>

                </div>

                <div class="form-item">
                    <label> <?php _e('Foreground color','wt-smart-coupons-for-woocommerce') ?> </label>
                    <div class="form-element">
                        <input name="wt_expired_coupon_border_color" id="wt_expired_coupon_border_color" type="text" value="<?php echo esc_attr($admin_options['wt_expired_coupon_border_color']); ?>" class="wt_colorpick" data-default-color="#eccaca"  />
                    </div>
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="coupon_preview expired_coupon_preview"></div>
            </div>

        </div> <!-- Expired Coupons -->

        <?php do_action('wt_smart_coupon_after_coupon_settings_form'); ?>  
    </div>
    <?php 
    include "admin-settings-save-button.php";
    ?>
</div>