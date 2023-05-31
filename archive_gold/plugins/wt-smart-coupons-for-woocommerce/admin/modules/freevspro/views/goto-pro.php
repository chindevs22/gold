<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<style>
/* hide default sidebar */
.wf_gopro_block{ display:none; }
.wt-sc-tab-container, .wt-sc-tab-head{ width:68%; }
.wt_smart_coupon_admin_form_right_box{ width:calc(32% - 25px); }

.wt_smcpn_gopro_block{ float: left; height:auto; padding-bottom:0px; width:100%; box-sizing:border-box; background:#fff;}
.wt_smcpn_gopro_block a:hover{ color:#fff; }
.wt_smcpn_upgrade_to_premium_hd_block{ float:left; width:100%; padding:25px; box-sizing:border-box; background:rgba(233, 245, 255, 0.67); }
.wt_smcpn_upgrade_to_premium_hd_block img{ float:left; width:40px; margin-right:10px; }
.wt_smcpn_upgrade_to_premium_hd_block h3{ float:left; width:calc(100% - 50px); color:#3A6E9B; margin:0px; display:flex; flex-direction:column; justify-content:center; height:40px; }
.wt_smcpn_upgrade_to_premium_desc{float:left; width:100%; padding:15px 25px; box-sizing:border-box;}
.wt_smcpn_upgrade_to_premium_desc_imgbox{float:left; width:65px; text-align:center;}
.wt_smcpn_upgrade_to_premium_desc_imgshadow{display:inline-block; width:42px; height:1px; background:radial-gradient(10.77% 90% at 50% 30%, #FFB800 0%, #F9CA49 100%); filter:blur(3.5px);}
.wt_smcpn_upgrade_to_premium_desc_txtbox{float:left; width:calc(100% - 100px); margin-left:35px; color:#737373; font-weight:300; font-size:14px; line-height:18px;}
.wt_smcpn_upgrade_to_premium_btn{ float:left; width:100%; text-align:center; font-weight:bold; padding:15px 0px; background:#1DA5F8; color:#fff; text-decoration:none; margin-top:15px;}
.wt_smcpn_upgrade_to_premium_btn img{ width:18px; margin-right:4px; }


.wt_smcpn_upgrade_to_premium{ float:left; width:100%; padding:15px 12px; box-sizing:border-box; background:#fff; border-radius:2px; margin-top:30px; border-left:solid 4px #36AF00; }
.wt_smcpn_upgrade_to_premium_ul{ list-style:none; margin-top:0px; margin-bottom:0px; }
.wt_smcpn_upgrade_to_premium_ul li{ margin-bottom:15px; }
.wt_smcpn_upgrade_to_premium_ul li:last-child{ margin-bottom:0px; }
.wt_smcpn_upgrade_to_premium_ul .icon_box{ float:left; width:21px; height:21px; text-align:center; border-radius:15px; background:#fff; margin-right:8px; box-shadow:2px 6px 6px #e8ebee; box-sizing:border-box; padding:3px; }
.wt_smcpn_upgrade_to_premium_ul .icon_box img{ width:15px; display:inline-block; }


.wt_smcpn_other_wt_plugins{ float:left; width:100%; margin-top:30px; }
.wt_smcpn_other_plugin_box{float:left; background:#fff; border-radius:2px; padding:0px; margin-bottom:15px; }
.wt_smcpn_other_plugin_hd_block{ float:left; width:100%; padding:25px; box-sizing:border-box; background:rgba(233, 245, 255, 0.67); }
.wt_smcpn_other_plugin_hd_block img{ float:left; width:40px; margin-right:10px; }
.wt_smcpn_other_plugin_hd_block h3{ float:left; width:calc(100% - 50px); color:#3A6E9B; margin:0px; display:flex; flex-direction:column; justify-content:center; height:40px; }
.wt_smcpn_other_plugin_con{float:left; text-align:left; width:100%; font-size:14px; font-weight:300; padding:25px 25px; box-sizing:border-box; }
.wt_smcpn_other_plugin_foot_install_btn{ float:left; width:100%; height:50px; line-height:45px; box-sizing:border-box; font-weight:500; font-size:14px; color:#1DA5F8; text-align:center; text-decoration:none; border-top:solid 1.5px #1DA5F8; }


.wt_smcpn_freevs_pro{  width:100%; margin:20px 0px; border-collapse:collapse; border-spacing:0px; }
.wt_smcpn_freevs_pro td{ border:solid 1px #e7eaef; text-align:left; vertical-align:top; padding:15px 20px; line-height:22px;}
.wt_smcpn_freevs_pro tr td:first-child{ background:#f8f9fa; vertical-align:middle; }
.wt_smcpn_freevs_pro tr td:not(:first-child){ padding-left:45px; }
.wt_sc_free_vs_pro_sub_info{ display:inline-block; margin-bottom:5px; margin-left:-22px; }
.wt_sc_free_vs_pro_feature_info{ margin-left:-22px; }
.wt_smcpn_freevs_pro tr td .dashicons{ margin-left:-23px; }
.wt_smcpn_freevs_pro tr:first-child td{ font-weight:bold; }

.wt_smcpn_upgrade_to_pro_bottom_btn{ background:#1DA5F8; border-radius:3px; color:#fff; text-decoration:none; font-size:14px; font-weight:500; float:right; padding:15px 35px; margin-left:10px; margin-top:5px; margin-bottom:20px;  }
.wt_smcpn_upgrade_to_pro_bottom_btn:hover{ color:#fff; }


.wt_smcpn_tab_container{ float:left; box-sizing:border-box; width:100%; height:auto; }
.wt_smcpn_settings_left{ width:100%; float:left; margin-bottom:5px; }
.wt_smcpn_settings_right{ width:100%; box-sizing:border-box; float:left;}


@media screen and (max-width:1210px) {
    .wt_smcpn_settings_left{ width:100%;}
    .wt_smcpn_settings_right{ padding-left:0px; width:100%;}
}

@media (max-width:1200px) {
  .wt-sc-tab-container, .wt-sc-tab-head{ width:65%; }
  .wt_smart_coupon_admin_form_right_box{ width:calc(35% - 25px); }
}

@media (max-width:768px) {
  .wt-sc-tab-head, .wt-sc-tab-container, .wt_smart_coupon_admin_form_right_box{ width:100%; }
  .wt_smart_coupon_admin_form_right_box{ margin:auto; margin-top:30px; }
}

html[dir="rtl"] .wt_smcpn_settings_left{ float:right; }
html[dir="rtl"] .wt_smcpn_settings_right{ float:left; padding-left:0px; padding-right:25px; }

</style>
<script type="text/javascript">
    function wt_sc_freevspro_sidebar_switch(href)
    {
        if('#wt-sc-freevspro' === href)
        {
            jQuery('.wt_smcpn_settings_right').show();
            jQuery('.wt_smart_coupon_admin_form_right_box').css({'border-top':'0px solid #fff'});
            jQuery('.wt_smart_coupon_setup_video, .wt_smart_coupon_pro_features').hide();

        }else
        {
            jQuery('.wt_smcpn_settings_right').hide();
            jQuery('.wt_smart_coupon_admin_form_right_box').css({'border-top':'1px solid #c3c4c7'});
            jQuery('.wt_smart_coupon_setup_video, .wt_smart_coupon_pro_features').show(); 
        }
    }
    jQuery(document).ready(function(){
        
        wt_sc_freevspro_sidebar_switch(jQuery('.wt-sc-tab-head .nav-tab.nav-tab-active').attr('href'));

        jQuery('.wt-sc-tab-head .nav-tab').on('click', function(){
            wt_sc_freevspro_sidebar_switch(jQuery(this).attr('href'));
        });
    });
</script>
<div class="wt_smcpn_settings_left">
    <div class="wt_smcpn_tab_container">
        <?php
        include plugin_dir_path( __FILE__ ).'comparison-table.php';
        ?>
    </div> 
</div>
<a class="wt_smcpn_upgrade_to_pro_bottom_btn" href="https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_comparison&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=<?php echo WEBTOFFEE_SMARTCOUPON_VERSION;?>" target="_blank">
    <img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>admin/images/pro_icon.svg"> <?php _e('Upgrade to Premium', 'wt-smart-coupons-for-woocommerce'); ?>
</a>