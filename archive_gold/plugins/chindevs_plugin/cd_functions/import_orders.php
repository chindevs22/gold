<?php

    require_once 'helpers.php';

    function create_product_orders($orderData) {

        // Create Order Post

		$wpdata['post_title'] = "Order - " . $orderData['created_at'];
		$wpdata['post_status'] ='wc-completed';
		$wpdata['post_type'] = 'shop_order';
		$order_post_id = wp_insert_post( $wpdata );

        // Post Order Meta
        $wp_user_id = get_user_id('mgml_user_id', $orderData['user_id'];

        // create all easily mappable fields from global mapping
        create_order_meta($orderData, $order_post_id);

        update_post_meta($order_post_id, '_completed_date', $orderData['updated_at']);
        update_post_meta($order_post_id, '_paid_date', $orderData['updated_at']);
        update_post_meta($order_post_id, '_order_total', $orderData['paidAmount']);
        update_post_meta($order_post_id, '_order_stock_reduced', 'yes');
        update_post_meta($order_post_id, '_prices_include_tax', 'no');
        update_post_meta($order_post_id, '_new_order_email_sent', 'yes');
        update_post_meta($order_post_id, '_recorded_coupon_usage_counts', 'yes');
        update_post_meta($order_post_id, '_recorded_sales', 'yes');
        update_post_meta($order_post_id, '_download_permissions_granted', 'yes');
        update_post_meta($order_post_id, '_customer_user', $wp_user_id);

        //Billing Info
        $billing_name = $orderdata['billing_name']
        $nameArr = explode(' ', $billing_name);
        $firstName = array_shift($nameArr);
        $lastName = join(" ", $nameArr);
        update_post_meta($order_post_id, '_billing_first_name', $firstName);
        update_post_meta($order_post_id, '_billing_last_name', $lastName);
        //Billing Address Index

        $product_ids = $orderData['product_ids'];
        $p_ids = create_array_from_string($product_ids, ",");

        // For Each Product purchased in the order update wp_stm_lms_order_items table
        $productData = json_decode($orderData['cart_info'], true);
        $productInfo = array();
        foreach($productData['products'] as $product) {
            $dataArr['quantity'] = $product['qty'];
        	if ($product['discount'] == 1) {
            	$dataArr['price'] = $product['discounted_price'];
            } else {
            	$dataArr['price'] = $product['price'];
            }
        	$productInfo[$product['id']] = $dataArr;
        }

        $shipTaxData = json_decode($orderData['shipping_packaging_charge_info'], true);
        $totalShipCharges = $shipTaxData['totalShippingPackageCharges'];
        $itemsString = '';

        $table_name = 'wp_stm_lms_order_items'
        foreach($p_ids as $p_id) {
            $wp_product_id = get_from_post('product', 'mgml_product_id', $p_id);
            $wpdb->insert($table_name, array(
                'order_id' => $order_post_id,
                'object_id' => $wp_product_id,
                'quantity' => $productInfo[$p_id]['quantity'],
                'price' => $productInfo[$p_id]['price'],
            ));
        }

        // For Each Product purchased in the order update wp_woocommerce_order_items table
        $wc_order_items_table = 'wp_woocommerce_order_items';
        $wc_order_items_meta_table = 'wp_woocommerce_order_itemmeta';

        foreach($p_ids as $p_id) {
            $wp_product_id = get_from_post('product', 'mgml_product_id', $p_id);
            $title = get_the_title( $wp_product_id )
            $itemsString .= $title . ' &times; ' . $productInfo[$p_id]['quantity'] . ', ';
            $wpdb->insert($wc_order_items_table, array(
                'order_item_name' =>  $title,
                'order_item_type' => 'line_item',
                'order_id' => $order_post_id,
            ));
            $order_item_id = $wpdb->insert_id;
            wc_update_order_item_meta($order_item_id, '_product_id', $wp_product_id);
            wc_update_order_item_meta($order_item_id, '_qty', $productInfo[$p_id]['quantity']);
            wc_update_order_item_meta($order_item_id, '_line_subtotal', $productInfo[$p_id]['price'] * $productInfo[$p_id]['quantity']);
            wc_update_order_item_meta($order_item_id, '_line_total', $productInfo[$p_id]['price'] * $productInfo[$p_id]['quantity']);
            wc_update_order_item_meta($order_item_id, '_line_tax', 0);
            wc_update_order_item_meta($order_item_id, '_reduced_stock', $productInfo[$p_id]['quantity']);
        }

        if ($totalShipCharges > 0) {
            // Insert Flat Rate if Shipping
            $wpdb->insert($wc_order_items_table, array(
                'order_item_name' =>  'Flat rate',
                'order_item_type' => 'shipping',
                'order_id' => $order_post_id,
            ));
            $order_meta_id = $wpdb->insert_id;
            wc_update_order_item_meta($order_meta_id, 'method_id', 'flat_rate');
            wc_update_order_item_meta($order_meta_id, 'instance_id', '1');
            wc_update_order_item_meta($order_meta_id, 'cost', $totalShipCharges);
            wc_update_order_item_meta($order_meta_id, 'Items', $itemsString);
        }

        // For Each Product purchased in the order update wp_wc_customer_lookup table
        $customer_lookup_table = 'wp_wc_customer_lookup';
        global $wpdb;
        $user = get_user_by("id", $wp_user_id);
        get_user_meta( $wp_user_id, 'first_name', true )

        //Does unique customer already exist
        $customer = $wpdb->get_row(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "SELECT * FROM {$customer_lookup_table} WHERE user_id = ( %d )",
                $wp_user_id
            ),
            ARRAY_A
        );
        if (!isset($customer)){ //Create Customore in WC customer table
            $wpdb->insert($customer_lookup_table, array(
                'user_id' =>  $wp_user_id,
                'username' => $user->user_nicename,
                'first_name' => get_user_meta( $wp_user_id, 'first_name', true ),
                'last_name' => get_user_meta( $wp_user_id, 'last_name', true ),
                'email' => $user->user_email,
                'date_registered' => $user->user_registered,
                'country' => get_user_meta( $wp_user_id, 'cigbecl89n', true ),
                'postcode' => get_user_meta( $wp_user_id, 'rgcxegzsmy', true ),
                'city' => get_user_meta( $wp_user_id, '0hrgnga1qhp5', true ),
                'state' => get_user_meta( $wp_user_id, 'ijl5c9zv6lp', true ),
            ));
            $customer_id = $wpdb->insert_id;
        } else {
            $customer_id = $customer['customer_id'];
        }

    }


    function create_order_meta($orderData, $order_post_id) {
        global $orderMetaMapping;
        foreach ($orderMetaMapping as $key => $value) {
           update_post_meta( $order_post_id, $key, $orderData[$value] );
        }
    }

?>