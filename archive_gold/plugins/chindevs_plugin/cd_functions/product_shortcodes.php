<?php

function get_product_description_shortcode( $product_id ) {
 	if (!$product_id) {
		return "No Product ID attribute for Product Description";
	}
    $product = wc_get_product( $product_id );

    if ( $product ) {
        return  $product->get_description();
    }
}

function get_product_reviews( $product_id ) {

	$args = array(
	   'status' => 'approve',
	   'post_id' => $product_id,
	   'type' => 'review',
	);

	$comments_query = new WP_Comment_Query;
	$comments = $comments_query->query( $args );
	$review = '';
	if ( $comments ) {
	   foreach ( $comments as $comment ) {
		  $review .= '<p>' . $comment->comment_content . '</p>'; // Output the review content
		  $review .= '<p>By: ' . $comment->comment_author . '</p>'; // Output the author of the review
		  $review .= '<p>Date: ' . $comment->comment_date . '</p>'; // Output the date of the review
	   }
	} else {
	   $review = 'No reviews found for this product.';
	}
	return $review;
}

function get_product_free_video_shortcode( $product_id ) {
	if (!$product_id) {
		return "No Product ID attribute for Free Video";
	}
    $product = wc_get_product( $product_id );

	$video_link = get_post_meta($product_id, 'video', true);

	$video_data = do_shortcode("[embedyt]".$video_link."[/embedyt]");
    if ( $product ) {
        return $video_data;
    }
}

function get_product_name_shortcode( $product_id ) {
    if (!$product_id) {
		return "No Product ID attribute for Product Name";
	}
    $product = wc_get_product( $product_id );


    if ( $product ) {
			return '<div class="name">' . $product->get_title() . '</div>';
//         return $product->get_title();
    }
}

function get_product_shortdesc_shortcode( $product_id ) {
	if (!$product_id) {
		return "No Product ID attribute for Product Short Description";
	}

    $product = wc_get_product( $product_id );
    if ( $product ) {
        return $product->get_short_description();
    }
}

function create_elementor_template($atts) {
	$atts = shortcode_atts(array(
		'template_id' => '',
		'product_id' => '',
	), $atts);

	$template_id = $atts['template_id'];
	$product_id = $atts['product_id'];

	if (!$template_id) {
		return '';
	}

	// Get the Elementor template content
	$template_content = \Elementor\Plugin::$instance->frontend->get_builder_content($template_id);
	$template_content = do_shortcode( $template_content ); // a b

	// Process shortcodes inside the template content
	$description_content = get_product_description_shortcode($product_id);

	$new_content = preg_replace_callback('/\[get_product_description\s*.*?\]/', function($matches)  use ( $description_content ) {
		return $description_content;
	}, $template_content);

	$video_content =  get_product_free_video_shortcode($product_id);
	$new_content = preg_replace_callback('/\[get_product_free_video\s*.*?\]/', function($matches)  use ( $video_content ) {
		return $video_content;
	}, $new_content);

	$product_content =  do_shortcode('[product id="' . $product_id . '" class="my-product"]');
	$new_content = preg_replace_callback('/\[the_product\s*.*?\]/', function($matches)  use ( $product_content ) {
		return $product_content;
	}, $new_content);

	$reviews_content = get_product_reviews($product_id);
	$new_content = preg_replace_callback('/\[the_reviews\s*.*?\]/', function($matches)  use ( $reviews_content ) {
		return $reviews_content;
	}, $new_content);

	return $new_content;
}

add_shortcode('elementor_template', 'create_elementor_template');
?>