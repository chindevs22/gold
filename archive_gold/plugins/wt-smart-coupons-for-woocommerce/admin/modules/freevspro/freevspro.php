<?php
/**
 * Free vs Pro Comparison
 *
 * @link       
 * @since 1.3.0    
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wt_Smart_Coupon_Freevspro
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='freevspro';
	private static $instance = null;

	public function __construct()
	{
		$this->module_id=$this->module_base;
		self::$module_id_static=$this->module_id;

		add_filter("wt_sc_plugin_settings_tabhead", array($this, 'settings_tabhead'),1);       
        add_filter("wt_sc_plugin_out_settings_form", array($this, 'out_settings_form'),1);
        add_action("wt_smart_coupon_admin_form_right_box", array($this, 'add_right_sidebar'),1);

	}

	
	/**
     * 	Get Instance
     * 
     * 	@since 1.4.4
     */
    public static function get_instance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new Wt_Smart_Coupon_Freevspro();
        }
        return self::$instance;
    }

	/**
	*	To show WT other free plugins
	*/
	public function wt_other_pluigns()
	{

		$other_plugins_arr=array(
		    array(
		    	'key'=>'cookie-law-info',
		    	'title'=>__('GDPR Cookie Consent (CCPA Ready)', 'wt-smart-coupons-for-woocommerce'),
		    	'description'=>__('This plugin will assist you in making your website GDPR (RGPD, DSVGO) compliant.', 'wt-smart-coupons-for-woocommerce'),
		    	'icon'=>'icon-256x256.png',
		    ),
		    array(
		    	'key'=>'users-customers-import-export-for-wp-woocommerce',
		    	'title'=>__('Import Export WordPress Users and WooCommerce Customers', 'wt-smart-coupons-for-woocommerce'),
		    	'description'=>__('This plugin allows you to import and export WordPress users and WooCommerce customers quickly and easily.', 'wt-smart-coupons-for-woocommerce'),
		    ),
		    array(
		    	'key'=>'order-xml-file-export-import-for-woocommerce',
		    	'title'=>__('Order XML File Export Import for WooCommerce', 'wt-smart-coupons-for-woocommerce'),
		    	'description'=>__('The Order XML File Export Import Plugin for WooCommerce will export your WooCommerce orders in XML format.', 'wt-smart-coupons-for-woocommerce'),
		    ),
		    array(
		    	'key'=>'wt-woocommerce-sequential-order-numbers',
		    	'title'=>__('Sequential Order Number for WooCommerce', 'wt-smart-coupons-for-woocommerce'),
		    	'description'=>__('Using this plugin, you will always get sequential order number for woocommerce.', 'wt-smart-coupons-for-woocommerce'),
		    	'file'=>'wt-advanced-order-number.php'
		    ),
		    array(
		    	'key'=>'wp-migration-duplicator',
		    	'title'=>__('WordPress Migration & Duplicator', 'wt-smart-coupons-for-woocommerce'),
		    	'description'=>__('This plugin exports your WordPress website media files, plugins and themes including the database with a single click.', 'wt-smart-coupons-for-woocommerce'),
		    	'icon'=>'icon-128x128.jpg',
		    ),
		    array(
		    	'key'=>'express-checkout-paypal-payment-gateway-for-woocommerce',
		    	'title'=>__('PayPal Express Checkout Payment Gateway for WooCommerce', 'wt-smart-coupons-for-woocommerce'),
		    	'description'=>__('With this plugin, your customer can use their credit cards or PayPal Money to make order from cart page itself.', 'wt-smart-coupons-for-woocommerce'),
		    	'icon'=>'icon-128x128.jpg',
		    ),
		);

		shuffle($other_plugins_arr);

		$must_plugins_arr=array(
			array(
		    	'key'=>'wt-woocommerce-related-products',
		    	'title'=>__('Related Products for WooCommerce', 'wt-smart-coupons-for-woocommerce'),
		    	'description'=>__('This plugin allows you to choose related products for a particular product.', 'wt-smart-coupons-for-woocommerce'),
		    	'file'=>'custom-related-products.php',
		    	'icon'=>'icon-256x256.png',
			),
			array(
		    	'key'=>'decorator-woocommerce-email-customizer',
		    	'title'=>__('Decorator – WooCommerce Email Customizer', 'wt-smart-coupons-for-woocommerce'),
		    	'description'=>__('Customize your WooCommerce emails now and stand out from the crowd!', 'wt-smart-coupons-for-woocommerce'),
		    	'file'=>'decorator.php'
		    )
		);

		/* must plugins as first items */
		$other_plugins_arr=array_merge($must_plugins_arr, $other_plugins_arr);
		
		$plugin_count=0;
		ob_start();
		foreach($other_plugins_arr as $plugin_data)
		{
			if($plugin_count>=4) //maximum 4 plugins
			{
				break;
			}
			$plugin_key=$plugin_data['key'];
			$plugin_file=WP_PLUGIN_DIR.'/'.$plugin_key.'/'.(isset($plugin_data['file']) ? $plugin_data['file'] : $plugin_key.'.php');
			if(!file_exists($plugin_file)) //plugin not installed
			{
				$plugin_count++;
				$plugin_title=$plugin_data['title'];
				$plugin_icon=isset($plugin_data['icon']) ? $plugin_data['icon'] : 'icon-128x128.png';
				?>
				<div class="wt_smcpn_other_plugin_box">
		            <div class="wt_smcpn_other_plugin_hd">
		                <?php echo $plugin_title;?>
		            </div>
		            <div class="wt_smcpn_other_plugin_con">
		                <?php echo $plugin_data['description'];?>
		            </div>
		            <div class="wt_smcpn_other_plugin_foot">
		                <a href="https://wordpress.org/plugins/<?php echo $plugin_key;?>/" target="_blank" class="wt_smcpn_other_plugin_foot_install_btn"><img src="<?php echo WT_SMARTCOUPON_MAIN_URL;?>images/download_icon.svg"> <?php _e('Download', 'wt-smart-coupons-for-woocommerce');?></a>
		            </div>
		        </div>
				<?php
			}
		}
		$html=ob_get_clean();
		if("" !== $html)
		{
		?>
			<div class="wt_smcpn_other_plugins_hd"><?php echo sprintf(__('OTHER %sFREE%s SOLUTIONS FROM WEBTOFFEE', 'wt-smart-coupons-for-woocommerce'), '<span style="color:#6abe45;">', '</span>');?></div>
		<?php
			echo $html;
		}
	}


	/**
     * 	Coupon banner tab content
     * 
     * 	@since 1.4.4
     * 
     */
    public function out_settings_form($args)
    {
        $view_file = plugin_dir_path( __FILE__ ).'views/goto-pro.php';

        $view_params=array();

        Wt_Smart_Coupon_Admin::envelope_settings_tabcontent('wt-sc-'.$this->module_base, $view_file, '', $view_params, 0);
    }

	/**
	 * 	Tab head for plugin settings page
     *  
     * 	@since 1.4.4
     *  
     */
    public function settings_tabhead($arr)
    {
        $added=0;
        $out_arr=array();
        foreach($arr as $k=>$v)
        {
            $out_arr[$k]=$v;
            if('wt-sc-help' === $k && 0 === $added) /* after help */
            {               
                $out_arr['wt-sc-'.$this->module_base]=__('Free vs. Pro', 'wt-smart-coupons-for-woocommerce');
                $added=1;
            }
        }
        if(0 === $added)
        {
            $out_arr['wt-sc-'.$this->module_base]=__('Free vs. Pro', 'wt-smart-coupons-for-woocommerce');
        }
        return $out_arr;
    }


    public function add_right_sidebar()
    {
    	?>
        <div class="wt_smcpn_settings_right">
		    <div class="wt_smcpn_gopro_block">
		        <div class="wt_smcpn_upgrade_to_premium_top_block">    
		            <img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>images/crown.svg" style="margin: 0 auto 20px auto; display:inline-block;">
		            <div class="wt_smcpn_upgrade_to_premium_top_block_head"><?php _e('Upgrade to premium', 'wt-smart-coupons-for-woocommerce');?></div>
		        </div>
		        <div class="wt_smcpn_upgrade_to_premium">
		            <ul class="wt_smcpn_upgrade_to_premium_ul">
		                <li>
		                    <div class="icon_box"><img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>images/money_back.svg"></div>
		                    <?php _e('30 day money back guarantee','wt-smart-coupons-for-woocommerce'); ?>
		                </li>
		                <li>
		                    <div class="icon_box"><img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>images/fast_support.svg"></div>
		                    <?php _e('Fast and superior support','wt-smart-coupons-for-woocommerce'); ?>
		                </li>
		                <li>
		                    
		                    <div class="icon_box"><img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>images/features_need.svg"></div>
		                    <?php _e('Features that every site needs','wt-smart-coupons-for-woocommerce'); ?>
		                </li>
		            </ul>
		            <a href="https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_comparison&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=<?php echo WEBTOFFEE_SMARTCOUPON_VERSION;?>" target="_blank" class="wt_smcpn_upgrade_to_premium_btn">
		                <img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>admin/images/pro_icon.svg"> <?php _e('UPGRADE TO PREMIUM', 'wt-smart-coupons-for-woocommerce'); ?>
		            </a>
		        </div>
		        <div class="wt_smcpn_other_wt_plugins">
		            <?php $this->wt_other_pluigns();?>
		        </div>
		    </div>  
		</div>
    	<?php
    }
}
Wt_Smart_Coupon_Freevspro::get_instance();