<?php
/**
 * Premium Upgrade Page Content
 *
 * @link       
 * @since 1.4.4   
 *
 * @package  Wt_Smart_Coupon  
 */

// If this file is called directly, abort.
if (!defined('WPINC'))
{
    die;
}
?>
<style type="text/css">
.wt_sc_upgrade_to_pro{ float:left; width:95%; margin:20px 2.5%; height:auto; padding:40px; box-sizing:border-box; background:#fff; border-radius:4px; }  

.wt_sc_upgrade_to_pro_left{ float:left; width:54%; }
.wt_sc_pro_plugin_logo{ float:left; width:65px; } 
.wt_sc_pro_plugin_title_box{ float:left; margin-left:20px; margin-top:2px; width:calc(100% - 85px); } 
.wt_sc_pro_plugin_title{ float:left; margin-top:0px; width:100%; font-size:20px; } 
.wt_sc_pro_plugin_rating{ float:left; margin-top:0px; width:100%; font-size:18px; } 
.wt_sc_pro_plugin_desc{ float:left; width:100%; font-size:16px; font-weight:200; margin-top:25px; line-height:26px; } 
.wt_sc_pro_plugin_features{float:left; width:100%; margin-top:25px; }
.wt_sc_pro_plugin_features ul{ list-style:none; margin:0px; }
.wt_sc_pro_plugin_features ul li{ float:left; font-size:14px; padding-bottom:10px; padding-right:15px;}
.wt_sc_pro_plugin_features ul li .dashicons{ color:#6abe45; margin-right:2px; }
.wt_sc_upgrade_to_pro_btn{ display:inline-block; padding:13px 20px; color:#fff; background:linear-gradient(109.71deg, #2A27E8 13.44%, #3277FD 76.74%); border-radius:6px; text-decoration:none; font-size:14px; margin-top:20px; }
.wt_sc_upgrade_to_pro_btn:hover{ color:#fff; text-decoration:none; background:linear-gradient(20deg, #2A27E8 13.44%, #3277FD 76.74%); }

.wt_sc_upgrade_to_pro_right{ float:left; width:46%; }
.wt_sc_upgrade_to_pro_setup_video_box{ float:left; width:100%; box-sizing:border-box; padding:20px; background:#fff; box-shadow:0px 4px 28px rgba(227, 224, 249, 0.48); border-radius:2px; min-height:300px; text-align:center; }
.wt_sc_upgrade_to_pro_setup_video_box h4{ font-size:14px; margin-top:0px; }

.wt_sc_pro_advantage_box{ float:left; display:flex; flex-wrap:wrap; gap:0px 3%; align-content:space-between; width:95%; margin:20px 2.5%; margin-top:50px; box-sizing:border-box; background:#fff; padding:15px 40px; border-radius:4px; }
.wt_sc_pro_advantage_items{ width:30%; height:auto; padding:0px; }
.wt_sc_pro_advantage_items:first-child{ padding-left:0px; }
.wt_sc_pro_advantage_img_box{ float:left; width:50px; height:50px; border-radius:45px; background:#c9e4ff; text-align:center; }
.wt_sc_pro_advantage_img_box img{ display:inline-block; }
.wt_sc_pro_advantage_text_box{ float:left; width:calc(100% - 50px); margin-top:17px; font-weight:500; font-size:15px; box-sizing:border-box; padding-left:15px; border-right:solid 1px #dadada; }
.wt_sc_pro_advantage_items:last-child .wt_sc_pro_advantage_text_box{ border-right:none; }

.wt_sc_other_addons{float:left; height:auto; width:95%; margin:20px 2.5%; margin-top:30px;}
.wt_sc_other_addons_title{float:left; width:100%; text-align:center; font-size:20px;}
.wt_sc_other_addons_container{ float:left; height:auto; width:100%; display:flex; justify-content:space-between; flex-wrap:wrap; margin-top:10px; }

.wt_sc_other_addons_box{ width:30%; box-sizing:border-box; padding:20px; min-width:363px; background:#fff; border-radius:4px; position:relative; margin-top:15px; }
.wt_sc_other_addons_video_title{ width:100%; float:left; text-align:center; font-size:14px; margin-top:0px; }
.wt_sc_other_addons_video_box{ width:100%; float:left; height:200px; }
.wt_sc_other_addons_title_box{ width:100%; float:left; height:auto; margin-top:30px; }
.wt_sc_other_addons_title_box img{ float:left; width:55px; margin-right:15px;  }
.wt_sc_other_addons_title_box h3{ float:left; width:calc(100% - 70px); line-height:22px; margin-top:5px; font-size:18px;  }
.wt_sc_other_addons_desc{ width:100%; float:left; height:auto; margin-top:15px; font-size:14px; line-height:22px; }

.wt_sc_other_addons_features{width:100%; float:left; height:auto; margin-top:15px; margin-bottom:60px;}
.wt_sc_other_addons_features ul{ list-style:none; margin:0px; }
.wt_sc_other_addons_features li{ float:left; width:calc(100% - 23px); margin-left:23px; box-sizing:border-box; padding-left:23px; padding:7px 0px; }
.wt_sc_other_addons_features li .dashicons{ margin-left:-23px; float:left; color:#6abe45; }

.wt_sc_other_addons_visit_btn_box{ position:absolute; bottom:20px; left:7%; float:left; height:auto; width:86%; margin-top:30px; }
.wt_sc_other_addons_visit_btn{ float:left; height:auto; width:100%; box-sizing:border-box; height:40px; line-height:40px; border-radius:6px; border:solid 1px #2D3BEE; color:#2D3BEE; text-decoration:none; text-align:center; font-size:15px; }
.wt_sc_other_addons_visit_btn:hover{ color:#2D3BEE; text-decoration:none; }

@media only screen and (max-width:960px) {
    .wt_sc_upgrade_to_pro_left{  margin-bottom:60px; }
    .wt_sc_upgrade_to_pro_left, .wt_sc_upgrade_to_pro_right{ float:left; width:100%; }
    .wt_sc_pro_advantage_text_box{ padding-right:5px; margin-top:5px;}
    .wt_sc_other_addons_box{ width:45%; }
}
@media only screen and (max-width:600px) {
   .wt_sc_pro_advantage_items{ width:100%; margin-bottom:10px;}
   .wt_sc_pro_advantage_text_box{ border-right:none; } 
   .wt_sc_other_addons_box{ width:100%; min-width:0; }
}
</style>

<!-- Smart Coupon Premium -->
<div class="wt_sc_upgrade_to_pro">
    <div class="wt_sc_upgrade_to_pro_left">
        <img src="<?php echo esc_attr($module_img_path);?>plugin_pro_icon.svg" class="wt_sc_pro_plugin_logo">
        <div class="wt_sc_pro_plugin_title_box">
            <h3 class="wt_sc_pro_plugin_title"><?php _e('Smart Coupon for Woocommerce', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            <div class="wt_sc_pro_plugin_rating">⭐️ ⭐️ ⭐️ ⭐️ ⭐️ 4.8</div>
        </div>
        <div class="wt_sc_pro_plugin_desc">
            <?php _e('Create coupons to offer discounts and free products to your customers with Smart Coupons for WooCommerce.', 'wt-smart-coupons-for-woocommerce'); ?>
        </div>
        <div class="wt_sc_pro_plugin_features">
            <ul>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'BOGO Coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Offer store credits','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Store credits','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Give away products','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Signup coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Count down sales banner','wt-smart-coupons-for-woocommerce'); ?></li>
                <li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Import export coupons','wt-smart-coupons-for-woocommerce'); ?></li>
            </ul>
        </div>
        <a href="https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons" class="wt_sc_upgrade_to_pro_btn"><img src="<?php echo esc_attr($admin_img_path);?>pro_icon.svg"> <?php _e('Upgrade to Premium', 'wt-smart-coupons-for-woocommerce'); ?></a>
    </div>
    <div class="wt_sc_upgrade_to_pro_right">
        <div class="wt_sc_upgrade_to_pro_setup_video_box">
            <h4><?php _e('Watch setup video', 'wt-smart-coupons-for-woocommerce'); ?></h4> 
            <iframe src="//www.youtube.com/embed/IY4cmdUBw4A?rel=0" allowfullscreen="allowfullscreen" style="width:100%; min-height:240px;" frameborder="0" align="middle"></iframe>
        </div> 
    </div> 
</div>

<!-- Features -->
<div class="wt_sc_pro_advantage_box">
   <div class="wt_sc_pro_advantage_items">
        <div class="wt_sc_pro_advantage_img_box">
            <img src="<?php echo esc_attr($module_img_path);?>money_back.svg" style="width:10px; margin-top:17px;" alt=""  />
        </div>
        <div class="wt_sc_pro_advantage_text_box"><?php _e('30 Day Money Back Guarantee', 'wt-smart-coupons-for-woocommerce'); ?></div>
   </div> 
   <div class="wt_sc_pro_advantage_items">
        <div class="wt_sc_pro_advantage_img_box">
            <img src="<?php echo esc_attr($module_img_path);?>fast_support.svg" style="width:20px; margin-top:15px;" alt=""  />
        </div>
        <div class="wt_sc_pro_advantage_text_box"><?php _e('Fast and Priority Support', 'wt-smart-coupons-for-woocommerce'); ?></div>
   </div> 
   <div class="wt_sc_pro_advantage_items">
        <div class="wt_sc_pro_advantage_img_box" style="background:#e0f1d8;">
            <img src="<?php echo esc_attr($module_img_path);?>satisfaction.svg" style="width:26px; margin-top:16px;" alt=""  />
        </div>
        <div class="wt_sc_pro_advantage_text_box"><?php _e('97% Satisfaction rating', 'wt-smart-coupons-for-woocommerce'); ?></div>
   </div> 
</div>

<div class="wt_sc_other_addons">
    <h3 class="wt_sc_other_addons_title"><?php _e('Multiply your sales with these Smart Coupons addons', 'wt-smart-coupons-for-woocommerce'); ?></h3>
    
    <!-- Add on plugin details -->
    <div class="wt_sc_other_addons_container">
        
        <!-- Gift card Plugin -->
        <div class="wt_sc_other_addons_box">
            <h4 class="wt_sc_other_addons_video_title"><?php _e('Watch setup video', 'wt-smart-coupons-for-woocommerce'); ?></h4>
            <div class="wt_sc_other_addons_video_box">
                <iframe src="//www.youtube.com/embed/bKmGBG9U1uY?rel=0" allowfullscreen="allowfullscreen" style="width:100%; min-height:200px;" frameborder="0" align="middle"></iframe>
            </div>
            <div class="wt_sc_other_addons_title_box">
                <img src="<?php echo esc_attr($module_img_path);?>gift_cards_icon.svg">
                <h3><?php _e('WooCommerce Gift Cards', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            </div>
            <div class="wt_sc_other_addons_desc"><?php _e('Create & manage gift cards for your store.', 'wt-smart-coupons-for-woocommerce'); ?></div>
            
            <div class="wt_sc_other_addons_features">
                <ul>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Create gift cards products','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Email gift cards to customers','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('20+ predefined gift card templates','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Category-wise template listing','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Upload custom gift card templates','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Generate gift cards based on order status','wt-smart-coupons-for-woocommerce'); ?></li>
                </ul>
            </div>

            <div class="wt_sc_other_addons_visit_btn_box">
                <a class="wt_sc_other_addons_visit_btn" href="https://www.webtoffee.com/product/woocommerce-gift-cards/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=WooCommerce_Gift_Cards"><?php _e('Visit plugin page', 'wt-smart-coupons-for-woocommerce'); ?> → </a>
            </div>
        </div>

        <!-- URL Plugin -->
        <div class="wt_sc_other_addons_box">
            <h4 class="wt_sc_other_addons_video_title"><?php _e('Watch setup video', 'wt-smart-coupons-for-woocommerce'); ?></h4>
            <div class="wt_sc_other_addons_video_box">
                <iframe src="//www.youtube.com/embed/80JyXvalx6E?rel=0" allowfullscreen="allowfullscreen" style="width:100%; min-height:200px;" frameborder="0" align="middle"></iframe>
            </div>
            <div class="wt_sc_other_addons_title_box">
                <img src="<?php echo esc_attr($module_img_path);?>url_coupon_icon.svg">
                <h3><?php _e('URL Coupons for WooCommerce', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            </div>
            <div class="wt_sc_other_addons_desc"><?php _e('Get sharable URLs and QR codes for your coupons!', 'wt-smart-coupons-for-woocommerce'); ?></div>
            
            <div class="wt_sc_other_addons_features">
                <ul>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Create unique URLs for coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Generate QR codes for coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Auto-apply coupons on click','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Automatically add products to the cart','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Redirection users to specific pages','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Simple to use and easy to understand','wt-smart-coupons-for-woocommerce'); ?></li>
                </ul>
            </div>

            <div class="wt_sc_other_addons_visit_btn_box">
                <a class="wt_sc_other_addons_visit_btn" href="https://www.webtoffee.com/product/url-coupons-for-woocommerce/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=URL_Coupons"><?php _e('Visit plugin page', 'wt-smart-coupons-for-woocommerce'); ?> → </a>
            </div>
        </div> 

        <!-- Display Discount Plugin -->
        <div class="wt_sc_other_addons_box">
            <h4 class="wt_sc_other_addons_video_title"><?php _e('Watch setup video', 'wt-smart-coupons-for-woocommerce'); ?></h4>
            <div class="wt_sc_other_addons_video_box">
                <iframe src="//www.youtube.com/embed/yJKUjqzdKUk?rel=0" allowfullscreen="allowfullscreen" style="width:100%; min-height:200px;" frameborder="0" align="middle"></iframe>
            </div>
            <div class="wt_sc_other_addons_title_box">
                <img src="<?php echo esc_attr($module_img_path);?>display_discounts_icon.svg">
                <h3><?php _e('Display Discounts for WooCommerce', 'wt-smart-coupons-for-woocommerce'); ?></h3>
            </div>
            <div class="wt_sc_other_addons_desc"><?php _e('The best way to market your coupons in-house!', 'wt-smart-coupons-for-woocommerce'); ?></div>
            
            <div class="wt_sc_other_addons_features">
                <ul>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'List discounts on WooCommerce product pages','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Add countdown timers to time-limited coupons','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Multiple coupon display template','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Show restriction info within the coupon','wt-smart-coupons-for-woocommerce'); ?></li>
                    <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Limit the number of coupons to display','wt-smart-coupons-for-woocommerce'); ?></li>
                </ul>
            </div>

            <div class="wt_sc_other_addons_visit_btn_box">
                <a class="wt_sc_other_addons_visit_btn" href="https://www.webtoffee.com/product/display-woocommerce-discounts/?utm_source=free_plugin_premium_upgrade_page&utm_medium=smart_coupons_basic&utm_campaign=Display_Discounts"><?php _e('Visit plugin page', 'wt-smart-coupons-for-woocommerce'); ?> → </a>
            </div>
        </div>
    </div>
</div>