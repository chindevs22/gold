<?php
	// product is a post
	require_once 'helpers.php';
	function create_publications_from_csv($productData) {

        error_log(print_r($productData, true));
		$wpdata['post_title'] = $productData['title'];
		$wpdata['post_excerpt'] = html_entity_decode($productData['description']);
		$wpdata['post_content']  = html_entity_decode($productData['description']);
		$wpdata['post_status'] ='publish';
		$wpdata['post_type'] = 'product';

		$product_post_id = wp_insert_post( $wpdata );
        error_log("product id created " . $product_post_id);

		// Product Metadata

		// Prices
		$usd_price =  $productData['usd_price']; //need to handle rupees
		$inr_price =  $productData['inr_price'];
		update_post_meta($product_post_id, '_regular_price', $usd_price);
		update_post_meta($product_post_id, '_regular_price_wmcp', '{"INR": "' . $inr_price . '"}'); // INR price
		update_post_meta($product_post_id, '_price', $usd_price);
		if ($productData['discount_flag'] == 1) {
			$percent_off = $usd_price * $productData['discounted_price']/100;
			$sale_price = round($usd_price - $percent_off);

			$inr_percent_off = $inr_price * $productData['discounted_price']/100;
			$inr_sale_price = round($inr_price - $inr_percent_off);

			update_post_meta($product_post_id, '_sale_price', $sale_price);
			update_post_meta($product_post_id, '_sale_price_wmcp',  '{"INR": "' . $inr_sale_price .'"}');
 			update_post_meta($product_post_id, '_price', $sale_price);
		}

        update_post_meta($product_post_id, 'mgml_product_id', $productData['id']);
		update_post_meta($product_post_id, '_visibility', 'visible');
		update_post_meta($product_post_id, '_stock_status', 'instock');
		update_post_meta($product_post_id, '_stock',  $productData['quantity']);
		update_post_meta($product_post_id, '_manage_stock', 'yes');
		update_post_meta($product_post_id, '_featured', $productData['featured_product'] == 1 ? 'yes' : 'no');

		update_post_meta($product_post_id, '_weight', $productData['weight']/1000);

		// set the product image - TBD once we have an image

		// set the product category -----------------------------------------------------------------

        // Handle Category -----------------------------------------------------------------

        $taxonomy = 'product_cat';
        $parent_cat_name =  $productData['parent_category_name'];
        $parentTerm = get_term_by( 'name', $parent_cat_name , $taxonomy );

        // Create or Find Parent Category ID
        if ( $parentTerm ) { // Parent Term Exists
            $parent_category_id = $parentTerm->term_id;
            echo "The ID of the term " . $parent_cat_name. " is: " . $parent_category_id;
        } else { // Create the Parent Term
            $new_term = wp_insert_term($parent_cat_name, $taxonomy, array('parent'=> 0));
            $parent_category_id = intval($new_term['term_id']);
            echo "The resulting id: " . $parent_category_id;
            echo "Created category " . $parent_cat_name;
            error_log("Created category " . $parent_cat_name);
        }

        // Create or Find Sub Category ID
        $sub_cat_name =  $productData['sub_category_name'];
        $subCatTerm = get_term_by( 'name', $sub_cat_name , $taxonomy );
        if ( $subCatTerm ) { // if Sub Category Exists already
             $sub_category_id = $subCatTerm->term_id;
             echo "The ID of the term " . $sub_cat_name . " is: " . $sub_category_id;
        } else {
            $new_sub_term = wp_insert_term($sub_cat_name, $taxonomy, array('parent'=> $parent_category_id));
            $sub_category_id = intval($new_sub_term['term_id']);
            echo "Created sub category " . $sub_cat_name;
            error_log("Created sub category " . $sub_cat_name);
        }
        wp_set_object_terms($product_post_id, $sub_category_id,  $taxonomy, $append = true );

// 		// Set the the product attributes for language and author -----------------------------------------

		$extra_attributes = json_decode($productData['variable_filed'], true);
		$count = 0;
		$outer_arr = array();
		$terms_array = array(); // add each term (Author: Swami Ji) to the array

		foreach($extra_attributes as $attr) {

			$key = trim($attr['key'], " \x3A"); // strip whitespace and colon from key
			$value = trim($attr['value'], " \x3A");
			$formattedAttr = "" . $key . ": " . $value;

			array_push($terms_array, $formattedAttr); // build tags

			$data_arr = build_attr_array($attr, $count);
			$count += 1;
			$outer_arr[strtolower($key)] = $data_arr;
		}
		// add language attribute from language col
		// ensure new_language exists in CSV
		if(!array_key_exists('language', $outer_arr)) {
			$attr = array("key" => "Language", "value" => $productData['new_language']);
			array_push($terms_array, "" . $attr['key'] . ": " . $attr['value']);
			$data_arr = build_attr_array($attr, $count);
			$outer_arr[strtolower($attr['key'])] = $data_arr;
		}
		update_post_meta($product_post_id, '_product_attributes', $outer_arr);

		wp_set_object_terms($product_post_id,  $terms_array, 'product_tag', $append = true );
	}


?>