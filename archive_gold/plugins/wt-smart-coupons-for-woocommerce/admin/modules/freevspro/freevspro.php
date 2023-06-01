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
		    	'title'			=> __('Frequently Bought Together for WooCommerce', 'wt-smart-coupons-for-woocommerce'),
		    	'description'	=> __('Create and display frequently bought together suggestions automatically. Automatically generate frequently bought recommendations for your products.', 'wt-smart-coupons-for-woocommerce'),
		    	'icon'			=> 'fbt_plugin.png',
		    	'url'			=> 'https://www.webtoffee.com/product/woocommerce-frequently-bought-together/?utm_source=free_plugin_comparison_sidebar&utm_medium=smart_coupons_basic&utm_campaign=Frequently_Bought_Together',
			),
			array(
		    	'title'			=> __('Display Discounts for WooCommerce', 'wt-smart-coupons-for-woocommerce'),
		    	'description'	=> __('Maximize the visibility of your coupons by displaying them on product pages.', 'wt-smart-coupons-for-woocommerce'),
		    	'icon'			=> 'display-discounts.png',
		    	'url'			=> 'https://www.webtoffee.com/product/display-woocommerce-discounts/?utm_source=free_plugin_comparison_sidebar&utm_medium=smart_coupons_basic&utm_campaign=Display_Discounts',
			),
			array(
		    	'title'			=> __('WooCommerce Best Sellers', 'wt-smart-coupons-for-woocommerce'),
		    	'description'	=> __('Highlight the top-selling products in your store with labels, seals & sliders.', 'wt-smart-coupons-for-woocommerce'),
		    	'icon'			=> 'bestsellers_plugin.png',
		    	'url'			=> 'https://www.webtoffee.com/product/woocommerce-best-sellers/?utm_source=free_plugin_comparison_sidebar&utm_medium=smart_coupons_basic&utm_campaign=WooCommerce_Best_Sellers',
			),
			array(
		    	'title'			=> __('WooCommerce Product Feed & Sync Manager', 'wt-smart-coupons-for-woocommerce'),
		    	'description'	=> __('Make WooCommerce Product Feed Generation & Sync Easier', 'wt-smart-coupons-for-woocommerce'),
		    	'icon'			=> 'product-feed-sync.png',
		    	'url'			=> 'https://www.webtoffee.com/product/product-catalog-sync-for-facebook/?utm_source=free_plugin_comparison_sidebar&utm_medium=smart_coupons_basic&utm_campaign=WooCommerce_Product_Feed',
			),
		);

		shuffle($other_plugins_arr);

		$must_plugins_arr = array(
			array(
		    	'title'			=> __('WooCommerce Gift Cards', 'wt-smart-coupons-for-woocommerce'),
		    	'description'	=> __('Create purchasable gift cards with fixed or custom prices for your store.', 'wt-smart-coupons-for-woocommerce'),
		    	'icon'			=> 'giftcards_plugin.png',
		    	'url'			=> 'https://www.webtoffee.com/product/woocommerce-gift-cards/?utm_source=free_plugin_comparison_sidebar&utm_medium=smart_coupons_basic&utm_campaign=WooCommerce_Gift_Cards',
			),
			array(
		    	'title'			=> __('URL Coupons for WooCommerce', 'wt-smart-coupons-for-woocommerce'),
		    	'description'	=> __('Generate sharable URLs and QR codes for all discount coupons in your store.', 'wt-smart-coupons-for-woocommerce'),
		    	'icon'			=> 'url-coupons-plugin.png',
		    	'url'			=> 'https://www.webtoffee.com/product/url-coupons-for-woocommerce/?utm_source=free_plugin_comparison_sidebar&utm_medium=smart_coupons_basic&utm_campaign=URL_Coupons',
		    )
		);

		/* must plugins as first items */
		$other_plugins_arr = array_merge($must_plugins_arr, $other_plugins_arr);
		
		/* image location for the logos */
        $other_solutions_images_url = WT_SMARTCOUPON_MAIN_URL . 'admin/modules/other_solutions/assets/images';

		$plugin_count = 0;
		ob_start();
		
		foreach($other_plugins_arr as $plugin_data)
		{
			if(4 <= $plugin_count) //maximum 4 plugins
			{
				break;
			}
		
			?>
			<div class="wt_smcpn_other_plugin_box">
	            <div class="wt_smcpn_upgrade_to_premium_hd_block">
		    		<img src="<?php echo esc_attr($other_solutions_images_url.'/'.$plugin_data['icon']);?>">
		    		<h3><?php echo esc_html($plugin_data['title']);?></h3>
		    	</div>
	            <div class="wt_smcpn_other_plugin_con">
	                <?php echo wp_kses_post($plugin_data['description']);?>
	            </div>
	            <a href="<?php echo esc_attr($plugin_data['url']);?>" target="_blank" class="wt_smcpn_other_plugin_foot_install_btn"><?php _e('Get the plugin', 'wt-smart-coupons-for-woocommerce');?> → </a>
	        </div>
			<?php
			
			$plugin_count++;
		}
		
		$html = ob_get_clean();
		
		if("" !== $html)
		{
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
    	/* image location for the logos */
        $wt_sc_other_solutions_images = WT_SMARTCOUPON_MAIN_URL . 'admin/modules/other_solutions/assets/images';

    	?>
        <div class="wt_smcpn_settings_right">
		    <div class="wt_smcpn_gopro_block">		        
		    	<div class="wt_smcpn_upgrade_to_premium_hd_block">
		    		<img src="<?php echo esc_attr($wt_sc_other_solutions_images);?>/smart-coupons-plugin.png">
		    		<h3><?php _e('Smart Coupons for WooCommerce', 'wt-smart-coupons-for-woocommerce'); ?></h3>
		    	</div>
		    	<div class="wt_smcpn_upgrade_to_premium_desc">
		    		<div class="wt_smcpn_upgrade_to_premium_desc_imgbox">
		    			<img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>images/crown.svg" style="width:65px;">
		    			<span class="wt_smcpn_upgrade_to_premium_desc_imgshadow"></span>
		    		</div>
		    		<div class="wt_smcpn_upgrade_to_premium_desc_txtbox">
		    			<?php _e('Want additional coupon customizations? Get them with Smart Coupon premium!', 'wt-smart-coupons-for-woocommerce'); ?>
		    		</div>
		    	</div>
		    	<a class="wt_smcpn_upgrade_to_premium_btn" href="https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_comparison&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content=<?php echo WEBTOFFEE_SMARTCOUPON_VERSION;?>" target="_blank">
	                <img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>admin/images/pro_icon.svg"> <?php _e('Upgrade to Premium', 'wt-smart-coupons-for-woocommerce'); ?>
	            </a>
		    </div>

		        
	        <div class="wt_smcpn_upgrade_to_premium">
	            <ul class="wt_smcpn_upgrade_to_premium_ul">
	                <li>
	                    <div class="icon_box"><img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>images/money_back.svg"></div>
	                    <?php _e('30 Day Money Back Guarantee','wt-smart-coupons-for-woocommerce'); ?>
	                </li>
	                <li>
	                    <div class="icon_box"><img src="<?php echo esc_attr(WT_SMARTCOUPON_MAIN_URL);?>images/fast_support.svg"></div>
	                    <?php _e('Fast and 99% customer satisfaction','wt-smart-coupons-for-woocommerce'); ?>
	                </li>
	            </ul>            
	        </div>


	        <div class="wt_smcpn_other_wt_plugins">
	            <?php $this->wt_other_pluigns();?>
	        </div>
		      
		</div>
    	<?php
    }
}
Wt_Smart_Coupon_Freevspro::get_instance();