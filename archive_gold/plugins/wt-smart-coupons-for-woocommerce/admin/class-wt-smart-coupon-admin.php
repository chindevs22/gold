<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.webtoffee.com
 * @since      1.0.0
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wt_Smart_Coupon
 * @subpackage Wt_Smart_Coupon/admin
 * @author     WebToffee <info@webtoffee.com>
 */
if( ! class_exists('Wt_Smart_Coupon_Admin') ) {
    class Wt_Smart_Coupon_Admin {

        private $plugin_name;
        private $version;

        /**
         * module list, Module folder and main file must be same as that of module name
         * Please check the `register_modules` method for more details
         * @since 1.3.5
         */
        public static $modules=array(
            'url_coupon',
            'limit_max_discount',
            'coupon_shortcode',
            'giveaway_product',
            'coupon_restriction',
            'freevspro',
            'auto_coupon',
            'premium_upgrade',
            'other_solutions',
        );

        public static $existing_modules=array();

        private static $instance = null;
    
        public function __construct($plugin_name, $version) {
    
            $this->plugin_name = $plugin_name;
            $this->version = $version;
        }

        /**
         * Get Instance
         * @since 1.4.1
         */
        public static function get_instance($plugin_name, $version)
        {
            if(self::$instance==null)
            {
                self::$instance=new Wt_Smart_Coupon_Admin($plugin_name, $version);
            }

            return self::$instance;
        }

        /**
         * Admin settings right sidebar
         * @since 1.4.0
         */
        public static function admin_right_sidebar()
        {
            include WT_SMARTCOUPON_MAIN_PATH.'/admin/views/_admin_right_sidebar.php';
        }

        /**
         *  Setup video
         *  @since 1.4.0
         */
        public static function setup_video_sidebar()
        {
            include WT_SMARTCOUPON_MAIN_PATH.'/admin/views/_setup_video_sidebar.php';
        }

        /**
         *  Premium features
         *  @since 1.4.0
         */
        public static function premium_features_sidebar()
        {
            include WT_SMARTCOUPON_MAIN_PATH.'/admin/views/_premium_features_sidebar.php';
        }

        /**
         * Help links metabox html
         * @since 1.3.5
         */
        public function help_links_meta_box_html()
        {
            include WT_SMARTCOUPON_MAIN_PATH.'/admin/views/_help_links_meta_box.php';
        }


        /**
         * Help links metabox
         * @since 1.3.5
         */
        public function help_links_meta_box()
        {
            add_meta_box("wt-sc-help-links", __("Quick links", 'wt-smart-coupons-for-woocommerce'), array($this, "help_links_meta_box_html"), "shop_coupon", "side", "default", null);
        }

        /**
         * Upgrade to pro metabox html
         * @since 1.3.3
         */
        public function upgrade_to_pro_meta_box_html()
        {
           include WT_SMARTCOUPON_MAIN_PATH.'/admin/views/_upgrade_to_pro_metabox.php';
        }

        /**
         * Upgrade to pro metabox
         * @since 1.3.3
         */
        public function upgrade_to_pro_meta_box()
        {
            add_meta_box("wt-sc-upgrade-to-pro", __("Smart Coupons for WooCommerce", 'wt-smart-coupons-for-woocommerce'), array($this, "upgrade_to_pro_meta_box_html"), "shop_coupon", "side", "core", null);
        }
    
    
        /**
         * Save Custom meata fields added in coupon 
         * @since 1.0.0
         */
        public function process_shop_coupon_meta($post_id, $post) {
            if (!current_user_can('manage_woocommerce')) 
            {
                wp_die(__('You do not have sufficient permission to perform this operation', 'wt-smart-coupons-for-woocommerce'));
            }
            if (!empty($_POST['_wt_sc_shipping_methods'])) {
                $wt_sc_shipping_methods = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wt_sc_shipping_methods'],'text_arr');
                update_post_meta($post_id, '_wt_sc_shipping_methods', implode(',', $wt_sc_shipping_methods ) );
            } else {
                update_post_meta($post_id, '_wt_sc_shipping_methods', '');
            }
    
            if (!empty($_POST['_wt_sc_payment_methods'])) {
                $wt_sc_payment_methods = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wt_sc_payment_methods'],'text_arr');
                update_post_meta($post_id, '_wt_sc_payment_methods', implode(',', $wt_sc_payment_methods ));
            } else {
                update_post_meta($post_id, '_wt_sc_payment_methods', '' );
            }
    
            if (!empty($_POST['_wt_sc_user_roles'])) {
                $wt_sc_user_roles = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wt_sc_user_roles'],'text_arr');
                update_post_meta($post_id, '_wt_sc_user_roles', implode(',',$wt_sc_user_roles ) );
            } else {
                update_post_meta($post_id, '_wt_sc_user_roles', '');
            }

    
            if( isset($_POST['_wt_valid_for_number']) ) {
                $wt_valid_for_number = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wt_valid_for_number']);
                if($wt_valid_for_number != '') {
                    update_post_meta($post_id, '_wt_valid_for_number', $wt_valid_for_number );
                }
                if ( isset( $_POST['_wt_valid_for_type'] ) && '' != $_POST['_wt_valid_for_type']  ) {
                    $wt_valid_for_type = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wt_valid_for_type']);
                } else {
                    $wt_valid_for_type = 'days';
                }
                update_post_meta($post_id, '_wt_valid_for_type', $wt_valid_for_type );
    
            }
    
            if(isset($_POST['_wc_make_coupon_available']) && $_POST['_wc_make_coupon_available']!='' )
            {              
                $_wc_make_coupon_available = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['_wc_make_coupon_available'], 'text_arr');
                update_post_meta($post_id, '_wc_make_coupon_available', implode(',', $_wc_make_coupon_available));
            }else
            {
                update_post_meta($post_id, '_wc_make_coupon_available',  '');
            }
    
            
        }
    
        /**
         * Enqueue Admin styles.
         * @since 1.0.0
         * @since 1.3.5 Styles limited to WC pages and Smart coupon settings pages
         */
        public function enqueue_styles()
        {
            $screen    = get_current_screen();
            $screen_id = $screen ? $screen->id : '';
            
            if ( 
                (function_exists('wc_get_screen_ids') && in_array( $screen_id, wc_get_screen_ids())) || 
                (isset($_GET['page']) && ($_GET['page']==WT_SC_PLUGIN_NAME || strpos($_GET['page'], WT_SC_PLUGIN_NAME)===0))
            ) 
            {
                wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wt-smart-coupon-admin.css', array(), $this->version, 'all');
                wp_enqueue_style( 'wp-color-picker' );
            }
        }
        
        /**
         * Enqueue Admin Scripts.
         * @since 1.0.0
         * @since 1.3.5 Scripts limited to WC pages and Smart coupon settings pages
         */
        public function enqueue_scripts()
        {
            $screen    = get_current_screen();
            $screen_id = $screen ? $screen->id : '';
            
            if ( 
                (function_exists('wc_get_screen_ids') && in_array( $screen_id, wc_get_screen_ids())) || 
                (isset($_GET['page']) && ($_GET['page']==WT_SC_PLUGIN_NAME || strpos($_GET['page'], WT_SC_PLUGIN_NAME)===0))
            ) 
            {
                wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wt-smart-coupon-admin.js', array('jquery','wp-color-picker'), $this->version, false);               
                
                $script_parameters=array(
                    'msgs'=>array(
                        'settings_error'=>sprintf(__('Unable to update settings due to an internal error. %s To troubleshoot please click %s here. %s', 'wt-smart-coupons-for-woocommerce'), '<br />', '<a href="https://www.webtoffee.com/how-to-fix-the-unable-to-save-settings-issue/" target="_blank">', '</a>'),
                        'is_required'=>__("is required", 'wt-smart-coupons-for-woocommerce'),
                        'copied'=>__("Copied!", 'wt-smart-coupons-for-woocommerce'),
                        'error'=>__("Error", 'wt-smart-coupons-for-woocommerce'),
                        'loading'=>__("Loading...", 'wt-smart-coupons-for-woocommerce'),
                        'please_wait'=>__("Please wait...", 'wt-smart-coupons-for-woocommerce'),
                        'are_you_sure'=>__("Are you sure?", 'wt-smart-coupons-for-woocommerce'),
                        'are_you_sure_to_delete'=>__("Are you sure you want to delete?", 'wt-smart-coupons-for-woocommerce'),
                    )
                );
                
                $script_parameters['ajaxurl'] = admin_url( 'admin-ajax.php' );
                $script_parameters['nonce'] = wp_create_nonce( 'wt_smart_coupons_admin_nonce' );


                wp_localize_script($this->plugin_name,'WTSmartCouponAdminOBJ', $script_parameters );
            }
    
        }
    
        /**
         * Add tabs to the coupon option page.
         * @since 1.0.0
         */
        public function admin_coupon_options_tabs($tabs) {
    
            $tabs['wt_coupon_checkout_options'] = array(
                'label' => __('Checkout options', 'wt-smart-coupons-for-woocommerce'),
                'target' => 'webtoffee_coupondata_checkout1',
                'class' => 'webtoffee_coupondata_checkout1',
            );

            return $tabs;
        }
    
        /**
         * wt_coupon_checkout_options Page content
         * @since 1.0.0
         */
        public function admin_coupon_options_panels() {
    
            global $thepostid, $post;
            $thepostid = empty($thepostid) ? $post->ID : $thepostid;
            ?>
            <div id="webtoffee_coupondata_checkout1" class="panel woocommerce_options_panel">
            <?php
            do_action('webtoffee_coupon_metabox_checkout', $thepostid, $post);
            ?>
            </div>
    
            <?php
        }
    
        /**
         * Checkout tab form elements
         * @since 1.0.0
         * @since 1.4.3 Takeway payment gateway payment method is not listing in dropdown 
         */
        public function admin_coupon_metabox_checkout2($thepostid, $post) {
    
    
            $wc_help_icon_uri = WC()->plugin_url() . "/assets/images/help.png";
    
            $coupon_shipping_method_id_s = get_post_meta($thepostid, '_wt_sc_shipping_methods',true);
            if( '' !=  $coupon_shipping_method_id_s &&  !is_array( $coupon_shipping_method_id_s ) ) {
                $coupon_shipping_method_id_s = explode(',',$coupon_shipping_method_id_s);
            }
    
            // $coupon_shipping_method_ids = isset($coupon_shipping_method_id_s[0]) ? $coupon_shipping_method_id_s[0] : array();
            ?>
    
            <!-- Shipping methods -->
            <p class="form-field">
                <label for="_wt_sc_shipping_methods"><?php _e('Shipping methods', 'wt-smart-coupons-for-woocommerce'); ?></label>
                <select id="_wt_sc_shipping_methods" name="_wt_sc_shipping_methods[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e('Any shipping method', 'wt-smart-coupons-for-woocommerce'); ?>">
                    <?php
                    $shipping_methods = WC()->shipping->load_shipping_methods();
    
                    if (!empty($shipping_methods)) {
    
                        foreach ($shipping_methods as $shipping_method) {
    
                            if ( !empty( $coupon_shipping_method_id_s ) && in_array($shipping_method->id, $coupon_shipping_method_id_s)) {
                                echo '<option value="' . esc_attr($shipping_method->id) . '" selected>' . esc_html($shipping_method->method_title) . '</option>';
                            } else {
                                echo '<option value="' . esc_attr($shipping_method->id) . '">' . esc_html($shipping_method->method_title) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
               <?php echo wc_help_tip( __('Coupon will be applicable if any of these shipping methods are selected.', 'wt-smart-coupons-for-woocommerce') ); ?>
    
            </p>
    
            <!-- Payment methods -->
            <p class="form-field"><label for="_wt_sc_payment_methods"><?php _e('Payment methods', 'wt-smart-coupons-for-woocommerce'); ?></label>
    
                <select id="webtoffee_payment_methods" name="_wt_sc_payment_methods[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e('Any payment method', 'wt-smart-coupons-for-woocommerce'); ?>">
                    <?php
                    $coupon_payment_method_id_s = get_post_meta($thepostid, '_wt_sc_payment_methods',true);
                    if( '' !=  $coupon_payment_method_id_s && !is_array( $coupon_payment_method_id_s ) ) {
                        $coupon_payment_method_id_s = explode(',',$coupon_payment_method_id_s);
                    }
                    // $coupon_payment_method_ids = isset($coupon_payment_method_id_s[0]) ? $coupon_payment_method_id_s[0] : array();
    
                    $payment_methods = WC()->payment_gateways->payment_gateways();
    
                    if (!empty($payment_methods)) {
    
                        foreach ($payment_methods as $payment_method) {
    
                            if ('yes' === $payment_method->enabled || true === $payment_method->enabled) {
                                if ( !empty( $coupon_payment_method_id_s ) && in_array($payment_method->id, $coupon_payment_method_id_s)) {
                                    echo '<option value="' . esc_attr($payment_method->id) . '" selected>' . esc_html($payment_method->title) . '</option>';
                                } else {
                                    echo '<option value="' . esc_attr($payment_method->id) . '">' . esc_html($payment_method->title) . '</option>';
                                }
                            }
                        }
                    }
                    ?>
                </select>
                <?php echo wc_help_tip( __('Coupon will be applicable if any of these payment methods are selected.', 'wt-smart-coupons-for-woocommerce') ); ?>
            </p>
    
    
            <p class="form-field"><label for="_wt_sc_user_roles"><?php _e('Applicable Roles', 'wt-smart-coupons-for-woocommerce'); ?></label>
                <?php
                     $_wt_sc_user_roles_s = get_post_meta($thepostid, '_wt_sc_user_roles',true);
                     if( !is_array( $_wt_sc_user_roles_s ) &&  '' != $_wt_sc_user_roles_s  ) {
                         $_wt_sc_user_roles_s = explode(',',$_wt_sc_user_roles_s);
                     }
                     $available_roles = array_reverse(get_editable_roles());
    
                ?>
                <select id="_wt_sc_user_roles" name="_wt_sc_user_roles[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e('Any role', 'wt-smart-coupons-for-woocommerce'); ?>">
                    <?php
                    $available_roles = ( isset( $available_roles ) && is_array( $available_roles ) ) ? $available_roles : array();
                    foreach ($available_roles as $role_id => $role) {
                        $role_name = translate_user_role($role['name']);
    
                        echo '<option value="' . esc_attr($role_id) . '"'
                        . selected( !empty( $_wt_sc_user_roles_s ) && in_array($role_id, $_wt_sc_user_roles_s), true, false) . '>'
                        . esc_html($role_name) . '</option>';
                    }
                    ?>
                </select> 
                <?php echo wc_help_tip( __('Coupon will be applicable if customer belongs to any of these User Roles.', 'wt-smart-coupons-for-woocommerce') ); ?>
            </p>
    
            <?php
        }
        
    
        /**
         * Plugin action link.
         * @since 1.0.0
         * @since 1.3.9 Some links moved to plugin description section
         */
        public function add_plugin_links_wt_smartcoupon($links)
        {  
            $out=array(
                'settings' => '<a href="'.get_admin_url().'?page='.WT_SC_PLUGIN_NAME.'&tab=settings">'.esc_html__('Settings', 'wt-smart-coupons-for-woocommerce') .' </a>',
            );
            foreach($links as $link_key=>$link_html)
            {
                if($link_key==='deactivate')
                {
                    $out['deactivate'] = str_replace('<a', '<a class="smartcoupon-deactivate-link"', $link_html);
                }else
                {
                    $out[$link_key] = $link_html;
                }
            }
            $out['premium-upgrade'] = '<a target="_blank" href="https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_listing&utm_medium=smart_coupons_basic&utm_campaign=smart_coupons&utm_content='.WEBTOFFEE_SMARTCOUPON_VERSION.'" style="color: #3db634; font-weight: 500;">' . esc_html__('Premium Upgrade', 'wt-smart-coupons-for-woocommerce') . '</a>';
            $out['review']='<a target="_blank" href="https://wordpress.org/support/plugin/wt-smart-coupons-for-woocommerce/reviews/?rate=5#new-post">' . esc_html__('Review', 'wt-smart-coupons-for-woocommerce') . '</a>';
            return $out;
        }

        /**
         *  @since 1.3.9
         *  Links under plugin description section of plugins page
         */
        public function plugin_row_meta($links, $file)
        {
            if(WT_SMARTCOUPON_BASE_NAME !== $file)
            {
                return $links;
            }

            $links['documentation']='<a target="_blank" href="https://www.webtoffee.com/smart-coupons-for-woocommerce-userguide/">' . esc_html__('Docs', 'wt-smart-coupons-for-woocommerce') . '</a>';
            $links['support']='<a target="_blank" href="https://wordpress.org/support/plugin/wt-smart-coupons-for-woocommerce/">' . esc_html__('Support', 'wt-smart-coupons-for-woocommerce') . '</a>';
                     
            return $links;
        }


        /**
         * Add coupon visibility options in coupon general settings section
         * @since 1.3.7 Added option to show coupons in checkout page
         */
        function add_new_coupon_options( $coupon_id, $coupon )
        {
            $wc_make_coupon_available = get_post_meta($coupon_id , '_wc_make_coupon_available', true );
            $coupon_available_arr=($wc_make_coupon_available ? explode(',', $wc_make_coupon_available) : array()); 
            
            $coupon_availability_options = array(
                'my_account'    => __('My Account', 'wt-smart-coupons-for-woocommerce'),
                'checkout'      => __('Checkout', 'wt-smart-coupons-for-woocommerce'),
                'cart'          => __('Cart', 'wt-smart-coupons-for-woocommerce'),
            );
            ?>
            <p class="form-field">
                <label for="_wc_make_coupon_available"><?php _e('Display coupon in', 'wt-smart-coupons-for-woocommerce'); ?></label>
                <select id="_wc_make_coupon_available" name="_wc_make_coupon_available[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php _e('Please select', 'wt-smart-coupons-for-woocommerce'); ?>">
                    <?php
                    foreach($coupon_availability_options as $section => $name)
                    {
                        $selected =(in_array($section, $coupon_available_arr) ? 'selected = selected' : '');                       
                        echo '<option value="'.esc_attr($section).'" '.$selected.'>'.esc_html($name).'</option>';
                    }                  
                    ?>
                </select> 
                <?php echo wc_help_tip(__('Display coupon in the selected pages', 'wt-smart-coupons-for-woocommerce')); ?>
            </p>
            <?php
        }

    
        /**
         * Ajax action function for checking product type
         * @since 1.0.0
         */
    
        function check_product_type() {

            if ( check_ajax_referer( 'wt_smart_coupons_nonce', 'security' ) && current_user_can('manage_woocommerce')) {
                
                $product_id = isset( $_POST['product']) ? Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['product'],'int') : '';
                if( '' == $product_id  )  {
                    return false;
                }
                $product = wc_get_product( $product_id );
                echo $product->get_type( );
                die();
            }
        }
    
        /**
         * Get Smartcoupon Settings options
         * @since 1.0.1
         */
        public static function get_options() {
            $smart_coupon_options = apply_filters('wt_smart_coupo_default_options',array(
                'wt_active_coupon_bg_color'         => '#2890a8' ,
                'wt_active_coupon_border_color'     => '#ffffff' ,
    
                'wt_display_used_coupons'           => true,
                'wt_used_coupon_bg_color'           => '#eeeeee',
                'wt_used_coupon_border_color'       => '#000000',
    
                'wt_display_expired_coupons'        => true,
                'wt_expired_coupon_bg_color'        => '#f3dfdf',
                'wt_expired_coupon_border_color'    => '#eccaca',
    
            ));
            $smart_coupon_saved_option = get_option("wt_smart_coupon_options");
            
            if ( !empty($smart_coupon_saved_option) ) {
                foreach ( $smart_coupon_saved_option as $key => $option ) {
                    $smart_coupon_options[$key] = $option;
                }
            }
            update_option("wt_smart_coupon_options", $smart_coupon_options);
            return $smart_coupon_options;
        }
    
        /**
         * helper function for getting formatted price
         * @since 1.2.9
         */
        public static function get_formatted_price( $amount ) {
            $currency = get_woocommerce_currency_symbol();
            $currentcy_positon = get_option('woocommerce_currency_pos');
    
            switch( $currentcy_positon ) {
                case 'left' : 
                    return $currency.$amount;
                case 'left_space' : 
                    return $currency.' '.$amount;
                case 'right_space' : 
                    return $amount.' '.$currency;
                default  : 
                    return $amount.$currency;
            }
    
            
        }
    

        /**
         *  Register modules    
         *  @since 1.3.5     
         */
        public function register_modules()
        { 
            Wt_Smart_Coupon::register_modules(self::$modules, 'wt_sc_admin_modules', plugin_dir_path( __FILE__ ), self::$existing_modules);  
        }

        /**
         *  Check module enabled    
         *  @since 1.3.5     
         */
        public static function module_exists($module)
        {
            return in_array($module, self::$existing_modules);
        }

        /**
         *  @since 1.4.1
         *  Saving new coupon count
         */
        public function save_created_coupon_count($post_id, $post, $update)
        {
            if(!$update && 'shop_coupon' === $post->post_type && 'auto-draft' === $post->post_status)
            {
                $auto_draft = get_option('wt_sc_auto_draft_coupons', array());
                $auto_draft[$post_id] = 1;

                update_option('wt_sc_auto_draft_coupons', $auto_draft);
            }


            if('shop_coupon' === $post->post_type && 'auto-draft' !== $post->post_status)
            {
                $auto_draft = get_option('wt_sc_auto_draft_coupons', array());

                $coupons_created = (int) get_option('wt_sc_coupons_created', 0);

                $is_update_needed = false;

                if($update && isset($auto_draft[$post_id])) //auto draft item saving as shop coupon
                {
                    $coupons_created++;
                    $is_update_needed = true;
                    
                    unset($auto_draft[$post_id]);
                    update_option('wt_sc_auto_draft_coupons', $auto_draft);
                }

                if(!$update)
                {
                    $coupons_created++;
                    $is_update_needed = true;
                }          

                if($is_update_needed)
                {
                    update_option('wt_sc_coupons_created', $coupons_created);
                }
            }
        }
        

        /**
         *  Alter WP coupon search section to handle `coupons by email` search. 
         *  Search format - email:{email@example.com}
         *  
         *  @since 1.4.4
         */
        public function search_coupon_using_email($wp)
        {
            global $pagenow, $wpdb;
            
            if('edit.php' !== $pagenow || !isset($wp->query_vars['s']) || 'shop_coupon' !== $wp->query_vars['post_type'])
            {
                return;
            }
            
            $wp->query_vars['s'] = trim($wp->query_vars['s']);
            
            if('email:' === strtolower(substr($wp->query_vars['s'], 0, 6)))
            {
                $email = trim(substr($wp->query_vars['s'], 6));
                
                if(!$email)
                {
                    return;
                }

                $post_ids = $wpdb->get_col($wpdb->prepare("SELECT pm.post_id FROM {$wpdb->postmeta} AS pm LEFT JOIN {$wpdb->posts} AS p ON (p.ID = pm.post_id AND p.post_type = 'shop_coupon') WHERE pm.meta_key = 'customer_email' AND pm.meta_value LIKE %s", '%' . $wpdb->esc_like($email) . '%')); // WPCS: db call ok.
                
                if(empty($post_ids))
                {
                    return;
                } 
                
                unset($wp->query_vars['s'], $_REQUEST['s']); //prevent WP default search

                $wp->query_vars['post__in'] = $post_ids;
                $wp->query_vars['email'] = $email;
            }
        }


        /**
         * Registers menu options
         * Hooked into admin_menu
         *
         * @since    1.4.4
         */
        public function admin_menu()
        {
            $menus=array(
                array(
                    'menu',
                    __('General settings', 'wt-smart-coupons-for-woocommerce'),
                    __('Smart Coupons', 'wt-smart-coupons-for-woocommerce'),
                    'manage_woocommerce',
                    WT_SC_PLUGIN_NAME,
                    array($this, 'admin_settings_page'),
                    'dashicons-tag',
                    59
                ),
               array(
                    'submenu',
                    WT_SC_PLUGIN_NAME,
                    __('All coupons','wt-smart-coupons-for-woocommerce'),
                    __('All coupons','wt-smart-coupons-for-woocommerce'),
                    'edit_shop_coupons',
                    'edit.php?post_type=shop_coupon',
                ),
                array(
                    'submenu',
                    WT_SC_PLUGIN_NAME,
                    __('Add coupon','wt-smart-coupons-for-woocommerce'),
                    __('Add coupon','wt-smart-coupons-for-woocommerce'),
                    'edit_shop_coupons',
                    'post-new.php?post_type=shop_coupon',
                ),
                array(
                    'submenu',
                    WT_SC_PLUGIN_NAME,
                    __('General settings','wt-smart-coupons-for-woocommerce'),
                    __('General settings','wt-smart-coupons-for-woocommerce'),
                    'manage_woocommerce',
                    WT_SC_PLUGIN_NAME,
                    array($this, 'admin_settings_page'),
                ),
            );
            
            $menus=apply_filters('wt_sc_admin_menu', $menus);

            if(is_array($menus))
            {
                foreach($menus as $menu)
                {
                    if('submenu' === $menu[0])
                    {
                        if(isset($menu[6]))
                        {
                            add_submenu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5],$menu[6]);
                        }else{
                            add_submenu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5]);
                        }
                        
                    }else
                    {
                        add_menu_page($menu[1],$menu[2],$menu[3],$menu[4],$menu[5],$menu[6],$menu[7]);  
                    }
                }
            }

            if(function_exists('remove_submenu_page')){
                remove_submenu_page(WT_SC_PLUGIN_NAME, WT_SC_PLUGIN_NAME);
            }
        }

        /**
         * Admin settings page
         *
         * @since    1.4.4
         */
        public function admin_settings_page()
        {
            include WT_SMARTCOUPON_MAIN_PATH.'admin/views/general_settings.php';
        }

        /**
         * Generate tab head for settings page.
         * 
         * @since     1.4.4
         */
        public static function generate_settings_tabhead($title_arr, $type="plugin")
        {   
            $out_arr = apply_filters("wt_sc_".$type."_settings_tabhead", $title_arr);
            
            foreach($out_arr as $k => $v)
            {           
                if(is_array($v))
                {
                    $v = (isset($v[2]) ? $v[2] : '').$v[0].' '.(isset($v[1]) ? $v[1] : '');
                }
            ?>
                <a class="nav-tab" href="#<?php echo esc_attr($k);?>"><?php echo wp_kses_post($v); ?></a>
            <?php
            }
        }

        
        /**
        *   Save admin settings and module settings ajax hook
        *   
        *   @since 1.4.4  
        */
        public function save_settings()
        {
            $out=array(
                'status'=>false,
                'msg'=>__('Error', 'wt-smart-coupons-for-woocommerce'),
            );

            
            if(Wt_Smart_Coupon_Security_Helper::check_write_access('smart_coupons', 'wt_smart_coupons_admin_nonce')) 
            {

                $smart_coupon_options = Wt_Smart_Coupon_Admin::get_options();

                if( isset( $_POST['wt_active_coupon_bg_color'] ) && !empty( $_POST['wt_active_coupon_bg_color'] ) ) {
                    $wt_active_coupon_bg_color = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['wt_active_coupon_bg_color'],'hex');
                    $smart_coupon_options['wt_active_coupon_bg_color'] = $wt_active_coupon_bg_color;
                }
                if( isset( $_POST['wt_active_coupon_border_color'] ) && !empty( $_POST['wt_active_coupon_border_color'] ) ) {
                    $wt_active_coupon_border_color = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['wt_active_coupon_border_color'],'hex');
                    $smart_coupon_options['wt_active_coupon_border_color'] = $wt_active_coupon_border_color;
                }

                if( isset( $_POST['wt_display_used_coupons'] ) ) {
                    $wt_display_used_coupons = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['wt_display_used_coupons']);
                    
                    if( 'on' === $wt_display_used_coupons ) {
                        $smart_coupon_options['wt_display_used_coupons'] = true;
                    } else {
                        $smart_coupon_options['wt_display_used_coupons'] = false;
                    }
                } else {
                    $smart_coupon_options['wt_display_used_coupons'] = false;
                }
                if( isset( $_POST['wt_used_coupon_bg_color'] ) && !empty( $_POST['wt_used_coupon_bg_color'] ) ) {
                    $wt_used_coupon_bg_color = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['wt_used_coupon_bg_color'],'hex');
                    $smart_coupon_options['wt_used_coupon_bg_color'] = $wt_used_coupon_bg_color;
                }
                if( isset( $_POST['wt_used_coupon_border_color'] ) && !empty( $_POST['wt_used_coupon_border_color'] ) ) {
                    $wt_used_coupon_border_color = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['wt_used_coupon_border_color'],'hex');
                    $smart_coupon_options['wt_used_coupon_border_color'] = $wt_used_coupon_border_color;
                }
                if( isset( $_POST['wt_display_expired_coupons'] ) ) {
                    $wt_display_expired_coupons = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['wt_display_expired_coupons']);
                    
                    if( 'on' === $wt_display_expired_coupons ) {
                        $smart_coupon_options['wt_display_expired_coupons'] = true;
                    } else {
                        $smart_coupon_options['wt_display_expired_coupons'] = false;
                    }
                }
                else {
                    $smart_coupon_options['wt_display_expired_coupons'] = false;
                }
                if( isset( $_POST['wt_expired_coupon_bg_color'] ) && !empty( $_POST['wt_expired_coupon_bg_color'] ) ) {
                    $wt_expired_coupon_bg_color = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['wt_expired_coupon_bg_color'],'hex');
                    $smart_coupon_options['wt_expired_coupon_bg_color'] = $wt_expired_coupon_bg_color;
                }
                if( isset( $_POST['wt_expired_coupon_border_color'] ) && !empty( $_POST['wt_expired_coupon_border_color'] ) ) {
                    $wt_expired_coupon_border_color = Wt_Smart_Coupon_Security_Helper::sanitize_item($_POST['wt_expired_coupon_border_color'],'hex');
                    $smart_coupon_options['wt_expired_coupon_border_color'] = $wt_expired_coupon_border_color;
                }

                update_option("wt_smart_coupon_options", $smart_coupon_options);

                do_action('wt_smart_coupon_settings_updated');


                $out['status']=true;
                $out['msg']=__('Settings Updated', 'wt-smart-coupons-for-woocommerce');
            }
            echo json_encode($out);
            exit();
        }

        
        /**
         *  Envelope settings tab content with tab div.
         *  Relative path is not acceptable for view file
         *  
         *  @since 1.4.4
         */
        public static function envelope_settings_tabcontent($target_id, $view_file="", $html="", $view_params=array(), $need_submit_btn=0)
        {
            ?>
                <div class="wt-sc-tab-content" data-id="<?php echo esc_attr($target_id);?>">
                    <?php
                    if("" !== $view_file && file_exists($view_file))
                    {
                        include_once $view_file;
                    }else
                    {
                        echo wp_kses_post($html);
                    }
                    ?>
                    <?php 
                    if(1 === $need_submit_btn)
                    {
                        self::add_settings_footer();
                    }
                    ?>
                </div>
            <?php
        }

        
        /**
         * Smart coupon settings button on coupons page
         * 
         *  @since 1.4.4
         */
        public function coupon_page_settings_button()
        {
            global $current_screen;
            if('shop_coupon' !== $current_screen->post_type)
            {
                return;
            }
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($)
                {
                    jQuery('.page-title-action').after('<a href="<?php echo esc_attr(admin_url('admin.php?page='.WT_SC_PLUGIN_NAME));?>" class="page-title-action"><?php _e('Smart coupon settings', 'wt-smart-coupons-for-woocommerce');?></a>');
                });
            </script>
            <?php
        }
    }
}
