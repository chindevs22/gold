<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<style>
/* hide default sidebar */
.wf_gopro_block{ display:none; }

.wt_smcpn_gopro_block{ background:#fff; float: left; height:auto; padding:15px; padding-bottom:8px; width:100%; box-sizing:border-box;}
.wt_smcpn_gopro_block a:hover{ color:#fff; }
.wt_smcpn_upgrade_to_premium_top_block{ text-align:center; padding:20px 0px 30px 0px; }
.wt_smcpn_upgrade_to_premium_top_block_head{ font-size:20px; font-weight:bold; }
.wt_smcpn_upgrade_to_premium{ background:#F8F9FA; border-radius:5px; padding:30px 20px; }
.wt_smcpn_upgrade_to_premium_ul{ list-style:none; margin-top:0px; margin-bottom:30px; }
.wt_smcpn_upgrade_to_premium_ul li{ margin-bottom:15px; }
.wt_smcpn_upgrade_to_premium_ul .icon_box{ float:left; width:21px; height:21px; text-align:center; border-radius:15px; background:#fff; margin-right:8px; box-shadow:2px 6px 6px #e8ebee; box-sizing:border-box; padding:3px; }
.wt_smcpn_upgrade_to_premium_ul .icon_box img{ width:15px; display:inline-block; }
.wt_smcpn_upgrade_to_premium_btn{ width:100%; display:block; text-align:center; font-weight:bold; padding:10px 0px; border-radius:5px; background:#6abe45; color:#fff; text-transform:uppercase; text-decoration:none; margin-top:25px;}
.wt_smcpn_upgrade_to_premium_btn img{ width:18px; margin-right:4px; }
.wt_smcpn_other_plugins_hd{ text-align:center; margin:10px 0px; margin-top:15px; padding:15px 0px; font-size:12px; font-weight:600; }

.wt_smcpn_other_plugin_box{float:left; background:#f8f9fa; border-radius:5px; padding:25px 0px; margin-bottom:15px; }
.wt_smcpn_other_plugin_hd{float:left; text-align:left; width:100%; height:auto; font-weight:500; font-size:13px; padding:0px 25px; box-sizing:border-box; }
.wt_smcpn_other_plugin_con{float:left; text-align:left; width:100%; font-size:11px; padding:8px 25px; box-sizing:border-box; }
.wt_smcpn_other_plugin_foot{float:left; text-align:center; width:100%; padding:9px 25px 0px 25px; box-sizing:border-box; }
.wt_smcpn_other_plugin_hd img{ float:left; width:50px; height:50px; margin-right:5px; border-radius:5px;}
.wt_smcpn_other_plugin_foot_install_btn{ display:inline-block; padding:10px 45px; font-weight:bold; font-size:11px; border-radius:5px; color:#fff; text-align:center; text-transform:uppercase; background:#2D9FFF; text-decoration:none; }
.wt_smcpn_other_plugin_foot_install_btn img{ width:17px; margin-right:7px; }
.wt_smcpn_other_plugin_foot_not_installed{ float:left; padding:5px 15px; font-size:10px; color:#989ca0; text-align:center; }

.wt_smcpn_freevs_pro{  width:100%; margin:20px 0px; border-collapse:collapse; border-spacing:0px; }
.wt_smcpn_freevs_pro td{ border:solid 1px #e7eaef; text-align:left; vertical-align:top; padding:15px 20px; line-height:22px;}
.wt_smcpn_freevs_pro tr td:first-child{ background:#f8f9fa; vertical-align:middle; }
.wt_smcpn_freevs_pro tr td:not(:first-child){ padding-left:45px; }
.wt_sc_free_vs_pro_sub_info{ display:inline-block; margin-bottom:5px; margin-left:-22px; }
.wt_sc_free_vs_pro_feature_info{ margin-left:-22px; }
.wt_smcpn_freevs_pro tr td .dashicons{ margin-left:-23px; }
.wt_smcpn_freevs_pro tr:first-child td{ font-weight:bold; }

.wt_smcpn_upgrade_to_pro_bottom_banner{ float:left; width:100%; margin:20px 0px; box-sizing:border-box; padding:35px; color:#ffffff; height:auto; background:#35678b; }
.wt_smcpn_upgrade_to_pro_bottom_banner_hd{ float:left; width:60%; border-left:solid 5px #feb439; font-size:20px; font-weight:bold; padding-left:10px; line-height:28px; margin-top:10px;}
.wt_smcpn_upgrade_to_pro_bottom_banner_btn{ background:#0cc572; border-radius:5px; color:#fff; text-decoration:none; font-size:16px; font-weight:bold; float:left; padding:20px 15px; margin-left:10px; margin-top:10px; }
.wt_smcpn_upgrade_to_pro_bottom_banner_btn:hover{ color:#fff; }
.wt_smcpn_upgrade_to_pro_bottom_banner_feature_list_main{ float:left; width:100%; margin-top:30px; }
.wt_smcpn_upgrade_to_pro_bottom_banner_feature_list{ float:left; box-sizing:border-box; width:31%; margin-right:2%; padding:3px 0px 3px 20px; font-size:11px; color:#fff; background:url(<?php echo plugin_dir_url(dirname(__FILE__));?>assets/images/tick_icon.png) no-repeat left 5px; }
@media screen and (max-width:768px) {
  .wt_smcpn_upgrade_to_pro_bottom_banner_feature_list{ width:100%; margin:auto; }
}


.wt_smcpn_tab_container{ float:left; box-sizing:border-box; width:100%; height:auto; }
.wt_smcpn_settings_left{ width:100%; float:left; margin-bottom:25px; }
.wt_smcpn_settings_right{ width:100%; box-sizing:border-box; float:left;}
@media screen and (max-width:1210px) {
    .wt_smcpn_settings_left{ width:100%;}
    .wt_smcpn_settings_right{ padding-left:0px; width:100%;}
}

html[dir="rtl"] .wt_smcpn_settings_left{ float:right; }
html[dir="rtl"] .wt_smcpn_settings_right{ float:left; padding-left:0px; padding-right:25px; }

</style>
<script type="text/javascript">
    function wt_sc_freevspro_sidebar_switch(href)
    {
        if('#wt-sc-freevspro' === href)
        {
            jQuery('.wt_smcpn_gopro_block').show();
            jQuery('.wt_smart_coupon_setup_video, .wt_smart_coupon_pro_features').hide();

        }else
        {
            jQuery('.wt_smcpn_gopro_block').hide();
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
<?php
include plugin_dir_path( __FILE__ ).'bottom-banner.php';
?>

