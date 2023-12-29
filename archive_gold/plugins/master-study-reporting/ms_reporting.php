<?php

/**
 * Plugin Name: MasterStudy price based on currency
 * Description: Change currency and Amount as per visitor location. 
 * Version: 0.1
 * Author: WP Custom Solutions
 *
 * 
 */
defined('ABSPATH') || exit; // Exit if accessed directly.
require_once  "class.IP.php";
function get_sale_price( $post_id ) {
	return apply_filters( 'stm_lms_get_sale_price', get_post_meta( $post_id, 'sale_price', true ), $post_id );
}
/**
  * Makes all posts in the default category private.
  *
  * @see 'save_post'
  *
  * @param int $post_id The post being saved.
  */F
  function save_course_details($post_id, $post) {
    global $table_prefix, $wpdb;
	$tablename = 'wp_product_details';
	 //Get id and post title//
	 $course_details_one = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_type IN ('stm-courses') AND post_status ='publish'");

	if (!empty($course_details_one))
	$correct_course_id = array();
	foreach ($course_details_one as $course_id) {
		$correct_course_id[] = $course_id;

		$price     = get_post_meta($course_id->ID, 'price', true);
		if(empty($price)){F
			$price = 0;
		}
		else{
			$price = get_post_meta($course_id->ID, 'price', true);
		}

		$sale_price = get_sale_price($course_id->ID);
		if(empty($sale_price)){
			$sale_price = 0;
		}
		else{
			$sale_price = get_sale_price($course_id->ID);
		}


		$json_five = 199;
	    $json_six =  100;
	    $mycurrency_final = "INR";
	$sql = "INSERT INTO $tablename (product_id,product_name,product_indsale_price,product_indregular_price,product_usdsale_price, product_usregular_price,currency) VALUES ($course_id->ID,'$course_id->post_title', $sale_price, $price, $json_five, $json_six,'$mycurrency_final')";
	$wpdb->query($sql);
	if(!empty($json_five)){
	$sql2 ="UPDATE $tablename SET product_indregular_price = $price, product_indsale_price = $sale_price, product_name = '$course_id->post_title', product_usdsale_price = $json_six, product_usregular_price = $json_five WHERE product_id = $course_id->ID";
	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
       dbDelta($sql2);
	}
	    
	}
}
add_action( 'save_post', 'save_course_details', 10, 2 );
add_action( 'publish_post', 'save_course_details', 10, 2 );

function create_table()
{
    global $table_prefix, $wpdb; 
	$product_details = 'product_details';
    $wp_track_table = $table_prefix . "$product_details ";

    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) 
    {

           $sql = "CREATE TABLE $wp_track_table (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`product_id` int(11) DEFAULT NULL,
			`product_name` varchar(255) DEFAULT NULL,
			`product_indsale_price` varchar(255) DEFAULT NULL,
			`product_indregular_price` varchar(255) DEFAULT NULL,
			`product_usdsale_price` varchar(255) DEFAULT NULL,
			`product_usregular_price` varchar(255) DEFAULT NULL,
			`currency` varchar(255) DEFAULT NULL,
			UNIQUE KEY (`product_id`),
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;";
		  require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
		  dbDelta($sql);
    }
}
 register_activation_hook( __FILE__, 'create_table' );
add_action("admin_menu", "crfl_addmenu");
function crfl_addmenu()
{
	$settings_page  = add_menu_page('Page Settings', 'Enter Product Details', 'administrator', 'page-settings', 'enter_product_details');
	$page           = add_submenu_page('vvc-counter', 'Dashboard', 'Dashboard', 'administrator', 'vvc-counter', 'enter_product_details');
	$page           = add_submenu_page( 'page-settings', 'Enter Product Details', 'Enter Product Details', 'administrator',  'enter-product-details','enter_product_details');
}
function enter_product_details (){
	
	global $table_prefix, $wpdb;
	$tblname = 'wp_product_details';
  
     if (isset($_POST['pi'],$_POST['usp'],$_POST['urp'])){
		
     $product_id = (int)$_POST['pi'];
	 $usale_price = (int)$_POST['usp'];
	 $usregular_price = (int)$_POST['urp'];
	 $sql ="UPDATE wp_product_details SET product_usdsale_price = $usale_price, product_usregular_price = $usregular_price WHERE product_id = $product_id";
	  require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
       dbDelta($sql);
		if($product_id != null && $usale_price != null && $usregular_price != null ){
			echo "<p>Saved Product Details for Product Id= $product_id USD Sale Price= $usale_price USD Regular Price= $usregular_price.</p>" ;
		}
		else{
			echo"Try Again with correct data";
		}
		
  }
  $product_ids = "";
echo "<h2>Enter Product Id,USD sale price,USD regular price</h2>";
    echo "<p>Enter correct Product Id.</p>";
	
	echo '<form method="post">
	<table align="left" border="0" cellspacing="0" cellpadding="3">
	<tr><td style="font: message-box;">Product Id:</td><td><input type="number" name="pi" maxlength="30" required></td></tr>
	<tr><td style="font: message-box;">USD Sale Price:</td><td><input type="number" name="usp" maxlength="30" required></td></tr>
	<tr><td style="font: message-box;">USD Regular Price:</td><td><input type="number" name="urp" maxlength="30" required></td></tr>
  </br>
	<tr><td><input type="submit" value="Submit"></td></tr>
	
	</table>
    </form>';


}

function get_currency()
{
	
	global $visitor_currency;
	$ip = new user_ip();
	$price = $ip->getCurrency();
 
	return $price;
	
}
function get_course_id()
{
	global $wpdb;
	$course_details_one = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_type IN ('stm-courses') AND post_status ='publish'");

	if (!empty($course_details_one))
	foreach ($course_details_one as $course_id) {
		$course_id = $course_id->ID;
	}
	return $course_id;
	
}
add_action( 'lms', 'get_currency', 10 );
function get_currency_test()
{
	
	global $wpdb,$product,$tablename;
	$tablename = 'wp_product_details';
	$mycurrency_final = get_currency();
	$course_details_one = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_type IN ('stm-courses') AND post_status ='publish'");

	if (!empty($course_details_one))
	$correct_course_id = array();
	foreach ($course_details_one as $course_id) {
		$correct_course_id[] = $course_id;
		$course_id_correct = $course_id->ID;
	
	
	//get data//start
	$sql = $wpdb->get_results("SELECT product_id,product_indsale_price,product_indregular_price,product_usdsale_price,product_usregular_price FROM $tablename WHERE product_id = $course_id_correct GROUP BY product_id ");
	
	$mydata = array();

	foreach($sql as $row){ 
		$mydata[] = $row->product_id;
		
if ($mycurrency_final === "USD") {
			
//update wp_postmeta price//start
$sql3 = "UPDATE wp_postmeta SET meta_value = $row->product_usdsale_price WHERE post_id = $row->product_id  AND meta_key ='sale_price'";
//$wpdb->query($sql);
  require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
       dbDelta($sql3);
//update wp_postmeta price//end
} 
else
 {
//update wp_postmeta price//start
$sql4 = "UPDATE wp_postmeta SET meta_value = $row->product_indsale_price WHERE post_id = $row->product_id AND meta_key ='sale_price'";
//$wpdb->query($sql);
  require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
       dbDelta($sql4);
//update wp_postmeta price//end

}
if ($mycurrency_final === "USD") {
	//update wp_postmeta price//start
	$sql5 = "UPDATE wp_postmeta SET meta_value = $row->product_usregular_price WHERE post_id = $row->product_id  AND meta_key ='price'";
	//$wpdb->query($sql);
  require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
       dbDelta($sql5);
	//update wp_postmeta price//end
} else {
	//update wp_postmeta price//start
	$sql6 = "UPDATE wp_postmeta SET meta_value = $row->product_indregular_price WHERE post_id = $row->product_id AND meta_key ='price'";
	//$wpdb->query($sql);
  require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
       dbDelta($sql6);
	//update wp_postmeta price//end
}
}
}

}
add_action( 'lms', 'get_currency_test', 999 );
add_action( 'plugins_loaded', 'get_currency_test', 999 );
