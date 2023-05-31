<?php
/**
 * Limit Max Discount
 *
 * @link       
 * @since 1.3.6   
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wt_Smart_Coupon_Limit_Max_Discount_Admin
{
    public $module_base='limit_max_discount';
    public $module_id='';
    public static $module_id_static='';
    private static $instance = null;
    public function __construct()
    {
        $this->module_id=Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;

        /**
         *  Maximum discount field on coupon add/edit page.
         */
        add_action('woocommerce_coupon_options_usage_limit',array($this, 'maximum_discount_field'), 10, 2);

        /**
         *  Toggle the visibility of `Max discount field`. (JS code)
         */
        add_action('admin_print_scripts', array($this, 'add_field_toggle_js'), 100);

        /**
         *  Save `Maximum discount amount` field value
         */
        add_action('woocommerce_coupon_options_save', array($this, 'save_maximum_discount_data'), 10, 2);


        add_filter('woocommerce_coupon_get_discount_amount', array($this, 'calculate_maximum_discount'), 20, 5 );

        /**
         * To correct the maximum discount
         * 
         * @since 1.4.5
         */
        add_action('woocommerce_after_calculate_totals', array($this, 'correct_maximum_discount'), 1000);

        /**
         * 
         *  Control percentage coupon maximum limit from backend and compatability for adding product wise discount via backend
         * 
         *  @since 1.4.5
         */
        add_action('woocommerce_order_after_calculate_totals', array($this, 'wt_woocommerce_order_after_calculate_totals'), 100, 2);

    }

    /**
     * Get Instance
     * @since 1.3.6
     */
    public static function get_instance()
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Smart_Coupon_Limit_Max_Discount_Admin();
        }
        return self::$instance;
    }

    /**
     * Add maximum discount field in coupon add/edit page
     * @since 1.3.6
     */
    public function maximum_discount_field($coupon_id, $coupon)
    {
        if($coupon->is_type('percent') || $coupon->is_type('fixed_product'))
        {
            $style = '';
        }else
        {
            $style = 'style="display:none"';
        }
    
        echo '<div id="wt_max_discount"  '.$style.'>';
        $max_discount =  get_post_meta($coupon_id, '_wt_max_discount', true );
        woocommerce_wp_text_input( array(
            'id'                => '_wt_max_discount',
            'label'             => __( 'Maximum discount value', 'wt-smart-coupons-for-woocommerce'),
            'placeholder'       => esc_attr__( 'Unlimited discount', 'wt-smart-coupons-for-woocommerce' ),
            'description'       => __( 'Use this option to set a cap on the discount value especially for percentage discounts. e.g, you may provide a 5 percentage discount coupon for a product but with a maximum discount upto $10.', 'wt-smart-coupons-for-woocommerce' ),
            'type'              => 'number',
            'desc_tip'          => true,
            'class'             => 'short',
            'custom_attributes' => array(
                'step'  => 1,
                'min'   => 0,
            ),
            'value' => ($max_discount ? $max_discount : ''),
        ) );
        echo '</div>';
    
    }
    
    /**
     * Add JS code block to toggle `Max discount amount` field.
     * @since 1.3.6
     */
    public function add_field_toggle_js()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                function wt_sc_toggle_max_discount_amount_field(elm)
                {
                    var type = elm.val();
                    if(type == 'percent' || type == 'fixed_product')
                    {
                        jQuery('#wt_max_discount').show();
                    }else
                    {
                        jQuery('#wt_max_discount').hide();
                    }
                }
                jQuery('#discount_type').on('change',function(){
                    wt_sc_toggle_max_discount_amount_field(jQuery(this));
                });
                wt_sc_toggle_max_discount_amount_field(jQuery('#discount_type'));
            });
        </script>
        <?php
    }

    /**
     * Save maximum discount meta.
     * @since 1.3.6
     */
    public function save_maximum_discount_data( $coupon_id, $coupon )
    { 
        update_post_meta($coupon_id, '_wt_max_discount', wc_format_decimal($_POST['_wt_max_discount']));  
    }

    /**
     * Calculate discounting amount
     * @since 1.3.6
     */
    public function calculate_maximum_discount( $discount, $discounting_amount, $cart_item, $single, $coupon )
    {

        if(!$coupon->is_type('percent') && ! $coupon->is_type('fixed_product') )
        {
            return ($discount);
        }

        $cart_discount_details = isset($cart_item['wt_discount_details'])? $cart_item['wt_discount_details'] : array();
        $max_discount = get_post_meta($coupon->get_id(), '_wt_max_discount', true);


        if(is_numeric($max_discount) && $max_discount>0 && !is_null($cart_item) && WC()->cart->subtotal_ex_tax)
        {
            $cart_item_qty = is_null($cart_item) ? 1 : $cart_item['quantity'];
            
            $sub_total_for_available_product = $this->get_allowed_prodcuts_from_cart($coupon);
            if(wc_prices_include_tax())
            {
                $product_price = wc_get_price_including_tax($cart_item['data']);
            } else {
                $product_price = wc_get_price_excluding_tax($cart_item['data']);
            }
            $discount_percent = ($product_price * $cart_item_qty) / ($sub_total_for_available_product);

            $_discount = ($max_discount * $discount_percent);
            $discount = min($_discount, $discount);
        }
    
        return ($discount);
    }

    /**
     * Get total price of allowed products for a coupon
     * @since 1.3.6
     */
    public function get_allowed_prodcuts_from_cart($coupon)
    {
        $cart = WC()->cart->get_cart() ;
        //$coupon = new WC_Coupon($coupon);
        $coupon_id      = $coupon->get_id();

        $sum_allowed_product = 0;
        // used to apply any reduction before calculating discount (will implement later ).
        $pre_discount_to_sub_total = apply_filters('wt_pre_applied_discount_into_sub_total', 0);

        foreach(  $cart as $cart_item_key => $cart_item  ) {
            $cart_item_qty = is_null( $cart_item ) ? 1 : $cart_item['quantity'];
            $_product = $cart_item['data'];                
            if( $coupon->is_valid_for_product( $_product )) {
                $allowed_products[] = $_product->get_id();
                if ( wc_prices_include_tax() ) {
                    $sum_allowed_product += wc_get_price_including_tax( $cart_item['data'] ) * $cart_item_qty;
                } else {
                    $sum_allowed_product += wc_get_price_excluding_tax( $cart_item['data'] ) * $cart_item_qty;
                }
            }
        }
        return $sum_allowed_product;
    }

   
    /**
     * 
     * To correct the maximum discount from the cart
     * 
     * @since 1.4.5  
     *  
     */
    public function correct_maximum_discount($cart)
    {
        if(is_null($cart))
        {
            $cart = WC()->cart;
        }

        if(version_compare(WC()->version, '3.1.2', '>'))
        {
            $cart_total = $cart->get_total('edit');
        }else
        {
            $cart_total = $cart->total;
        }

        if(empty($cart_total))
        {
            return;
        }

        $applied_coupons = WC()->cart->get_applied_coupons();
        if(empty($applied_coupons))
        {
            return;
        }

        $coupon_discount_totals=$this->get_coupon_discount_totals();
        foreach($applied_coupons as $coupon_code)
        {
            $coupon = new WC_Coupon($coupon_code);
            if(!$coupon || !(!$coupon->is_type('percent') || !$coupon->is_type('fixed_product')))
            {
                continue;
            }

            $max_discount = (int) get_post_meta($coupon->get_id(), '_wt_max_discount', true);
            if(!$max_discount)
            {
                continue;
            }

            if(!isset($coupon_discount_totals[$coupon_code]))
            {
                continue;
            }
            $current_discount_total=$coupon_discount_totals[$coupon_code];
            if($current_discount_total!==$max_discount) /* not equal */
            {
                $discount_diff=$current_discount_total-$max_discount;
                if(absint($discount_diff)>.1)
                {
                    continue;   
                }

                $coupon_discount_totals[$coupon_code]=$coupon_discount_totals[$coupon_code]-$discount_diff;
                $cart_total=$cart_total+$discount_diff;
            }
        }        

        $this->update_coupon_discount_total($cart, $coupon_discount_totals);
        
        if ( method_exists( $cart, 'set_total' ) ) {
            $cart->set_total( $cart_total );
        } else {
            $cart->total = $cart_total;
        }

    }

    /**
     *  Update cart object (Update coupon discount total)
     * 
     * @since 1.4.5  
     * 
     */
    public function update_coupon_discount_total($cart, $coupon_discount_totals)
    {       
        if(method_exists($cart, 'set_coupon_discount_totals')){
            $cart->set_coupon_discount_totals( $coupon_discount_totals );
        } else {
            $cart->coupon_discount_amounts = $coupon_discount_totals;
        }
    }
    /**
     *  Get Coupon Discount total from cart session.
     * 
     * @since 1.4.5  
     * 
     */
    public function get_coupon_discount_totals()
    {
        if ( method_exists( WC()->cart, 'get_coupon_discount_totals' ) ) {
            $coupon_discount_totals = WC()->cart->get_coupon_discount_totals();
        } else {
            $coupon_discount_totals = ( isset( WC()->cart->coupon_discount_amounts ) ? WC()->cart->coupon_discount_amounts : array() );
        }
    
        return $coupon_discount_totals;
    }


     /**
     * 
     *  Control the percentage coupon max limit when applying coupon via backend. [SC-387]
     * 
     *  Added compatibility when adding product wise discount via backend. [SC-716]
     * 
     *  @since 1.4.5  
     */
    function wt_woocommerce_order_after_calculate_totals($taxes, $order)
    {
        if (!is_admin()) {
            return;
        }
        if(!empty($order_coupons = $order->get_coupons())){
            $total_discount = 0;

            $order_discount_total = $order->get_discount_total();
            $order_discount_total_backup = $order_discount_total; //take a backup for comparing

            foreach($order_coupons as $key => $order_coupon) //loop through the order coupons
            {

                $coupon_code = $order_coupon->get_code();
                $coupon_id   = wc_get_coupon_id_by_code($coupon_code);

                if(0 === $coupon_id) //coupon not exists
                {
                     continue;
                }
                
                $coupon = new WC_Coupon($coupon_id); // to perform is percentage type check 
            
                if(!$coupon->is_type('percent')) //not a percentage coupon
                {
                    continue;
                }

                $max_discount = get_post_meta($coupon_id, '_wt_max_discount', true);
                $coupon_discount = $order_coupon->get_discount();

                if(!empty($max_discount) && $max_discount < $coupon_discount) //max restriction enabled.
                {
                    $order_discount_total -= ($coupon_discount - $max_discount); //deduct the extra calculated amount
                }
            }


            if( $order_discount_total_backup > $order_discount_total) // An extra amount is found. So need to update
            { 
                $order->set_discount_total($order_discount_total); //set order discount total
                $order_total = $order->get_total();
                $order->set_total($order_total + ($order_discount_total_backup - $order_discount_total)); //set order total

            }    

        }
    }
}
Wt_Smart_Coupon_Limit_Max_Discount_Admin::get_instance();