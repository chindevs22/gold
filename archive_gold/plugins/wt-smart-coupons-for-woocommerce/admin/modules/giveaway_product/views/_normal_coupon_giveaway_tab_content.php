<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="options_group wt_sc_normal_coupon_giveaway_tab_content">
    <p class="form-field"><label><?php _e('Free Product', 'wt-smart-coupons-for-woocommerce' ); ?></label>
        <select class="wc-product-search" style="width: 50%;" name="_wt_free_product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product...', 'wt-smart-coupons-for-woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations_without_parent"  data-allow_clear="true">
            <?php
            if(!empty($free_product_id_arr))
            {
                foreach($free_product_id_arr as $product_id)
                {
                    $product = wc_get_product($product_id);
                    if(is_object($product))
                    {
                        echo '<option value="'.esc_attr($product_id).'"'.selected(true, true, false).'>'.wp_kses_post($product->get_formatted_name()) . '</option>';
                    }
                }
            }                   
            ?>
        </select><?php echo wc_help_tip( __("A single quantity of the specified free product is added to the customer's cart when the coupon is applied. However, the corresponding tax and shipping charges are not exempted.", 'wt-smart-coupons-for-woocommerce' ) ); ?>
    </p>
</div>