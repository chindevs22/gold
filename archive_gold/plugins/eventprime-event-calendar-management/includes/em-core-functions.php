<?php
if(!defined('ABSPATH')) exit;

/**
 * Esacaping request parameter
 * string paramete_key.
 *
 * @return string (Parameter Value)
 */
function event_m_get_param($param = null,$secure = false) {
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $null_return = null;

    if ($request !== null)
        $_POST = (array) $request;

    if ($param && isset($_POST[$param]) && is_array($_POST[$param])) {
        return $_POST[$param];
    }

    if ($param) {
        if ($secure)
            $value = (!empty($_POST[$param]) ? trim(esc_sql($_POST[$param])) : $null_return);
        else {
            $value = (!empty($_POST[$param]) ? trim(esc_sql($_POST[$param])) : (!empty($_GET[$param]) ? $_GET[$param] : $null_return ));
        }

        if( !empty($value) && !is_array($value) ){
           $value = stripslashes($value);
        }
        return $value;
    } else {
        $params = array();
        foreach ($_POST as $key => $param) {
            $params[trim(esc_sql($key))] = (!empty($_POST[$key]) ? trim(esc_sql($_POST[$key])) : $null_return );
        }
        if (!$secure) {
            foreach ($_GET as $key => $param) {
                $key = trim(esc_sql($key));
                if (!isset($params[$key])) { // if there is no key or it's a null value
                    $params[trim(esc_sql($key))] = (!empty($_GET[$key]) ? trim(esc_sql($_GET[$key])) : $null_return );
                }
            }
        }

        return stripslashes($params);
    }
}

function em_time($datetime) {
    if (empty($datetime))
        return;
    return strtotime($datetime);
}

function em_timestamp($str_date,$format='m/d/Y H:i'){
    $datepicker_format_arr = em_global_settings('datepicker_format');
    if(!empty($datepicker_format_arr)){
        $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
        $format = $datepicker_format_arr[1] . ' H:i';
    }
    $date=DateTime::createFromFormat($format,$str_date);
    if(empty($date))
        return false;
    return $date->getTimestamp();
}

function em_get_local_timestamp($timestamp = 0) {
    $stamp_diff = floatval(get_option('gmt_offset')) * 3600;
    if ($timestamp == 0)
        return time() + $stamp_diff;
    else
        return $timestamp + $stamp_diff;
}
/*
 * Return time in H:i format from timestamp
 */

function em_get_time($datetime) {
    return date('H:i', $datetime);
}

function em_showDateTime($datetime, $time = true, $format = null) {
    if (empty($format) || is_null($format))
        $format = get_option('date_format');

    if (empty($datetime))
        return;
    if ($time)
        $format .= ' H:i';
    return date($format, $datetime);
}

function em_get_user_ip(){
    if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost'){
        $localIP = getHostByName(getHostName());
    }
    else{
        $localIP = $_SERVER['REMOTE_ADDR'];
    }
    return $localIP;
}

function em_get_user_timezone(){
    $userIp = em_get_user_ip();
    $userTimezone = em_get_timezone_by_ip($userIp);
    if(empty($userTimezone)){
        $userTimezone = get_option( 'timezone_string' );
        if(empty($userTimezone)){
            $offset  = (float) get_option( 'gmt_offset' );
            $userTimezone = get_site_timezone_from_offset($offset);
        }
        if($userTimezone == 'UTC'){
            if($userIp >= '192.168.0.0' && $userIp <= '192.168.255.255'){
                $userTimezone = date_default_timezone_get();
            }
        }
    }
    if(!headers_sent()){
        setcookie("ep_user_timezone", $userTimezone, time() + (86400 * 30));
    }
    return $userTimezone;
}

function em_current_time_by_timezone(){
    $current_time = current_time('timestamp');
    if(isset($_COOKIE['ep_user_timezone'])){
        $userTimezone = $_COOKIE['ep_user_timezone'];
    }
    else{
        $userTimezone = em_get_user_timezone();
    }
    if(!empty($userTimezone)){
        if(!empty(get_option( 'timezone_string' )) && get_option( 'timezone_string' ) == $userTimezone){
            return $current_time;
        }
        $date2 = DateTime::createFromFormat("U", $current_time)->setTimeZone(new DateTimeZone($userTimezone))->format("Y-m-d H:i:s");
        $current_time = strtotime($date2);
    }
    return $current_time;
}

function em_get_timezone_by_ip($userIp){
    $timezone = '';
    /*$ipInfo = @file_get_contents('http://ip-api.com/php/' . $userIp);*/
    $ipInfo = @file_get_contents('http://ip-api.com/json/' . $userIp);
    $ipInfo = (array)json_decode( $ipInfo );
    if(!empty($ipInfo)){
        /*$ipInfo = unserialize($ipInfo);*/
        if(isset($ipInfo['timezone']) && !empty($ipInfo['timezone'])){
            $timezone = $ipInfo['timezone'];
            //setcookie("ep_user_timezone", $timezone);
        }
    }
    return $timezone;
}

/**
 * Function to get global settings data
 * Possible meta options: gmap_api_key
 */
function em_global_settings($meta = null) {
    // Load global setting array from options table
    $global_options = get_option(EM_GLOBAL_SETTINGS);
    // Check if option exists 
    if (!empty($global_options)) {
        if ($meta !== null) {
            if (array_key_exists($meta, $global_options)) {
                return $global_options[$meta];
            } else {
                // Option does not exists
                return false;
            }
        }
        return $global_options;
    }
    return false;
}

function em_check_event_status() {
    $current_ts = em_current_time_by_timezone();
    $event_service = EventM_Factory::get_service('EventM_Service');
    $event_service->update_past_event_status();
}

function em_delete_tmp_bookings() {
    $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
    $booking_service->remove_all_tmp_bookings();
}

function em_get_attached_posts($id, $tax_type) {
    $args = array(
        'post_type' => EM_EVENT_POST_TYPE,
        'numberposts' => -1,
        'post_status' => 'any',
        'tax_query' => array(
            array(
                'taxonomy' => $tax_type,
                'field' => 'term_id',
                'terms' => $id
            )
        )
    );

    $events = get_posts($args);
    return $events;
}

function em_get_post_meta($post_id, $key = '', $single = false, $numeric = false) {
    if (!empty($key))
        $key = "em_" . $key;

    return get_post_meta($post_id, $key, $single);
}

function em_update_post_meta($post_id, $meta_key, $meta_value) {
    $meta_key = "em_" . $meta_key;
    $result = update_post_meta($post_id, $meta_key, $meta_value);
    return $result;
}

function em_get_term_meta($term_id, $key = '', $single = false) {
    if (!empty($key)){
        $key = "em_" . $key;
        return get_term_meta($term_id, $key, $single);
    }
    return get_term_meta($term_id,$key, $single);    
    
    
}

function em_update_term_meta($term_id, $meta_key, $meta_value) {
    $meta_key = "em_" . $meta_key;
    update_term_meta($term_id, $meta_key, $meta_value);
}

function em_append_meta_key($key) {
    $keys = array();
    if (is_array($key)) {
        foreach ($key as $k) {
            $keys[] = "em_" . $k;
        }
        return $keys;
    } else
        return "em_" . $key;
}

function em_event_seating_capcity($event_id) {
    $capacity = (int) em_get_post_meta($event_id, 'seating_capacity', true);
    $event_service = EventM_Factory::get_service('EventM_Service');
    $event = $event_service->load_model_from_db($event_id);
    $venue = $event->venue;
    if (!empty($venue)){
        $type = em_get_term_meta($venue, 'type', true);
        if ( ! empty( $capacity ) && $type == "seats") {
            return $capacity;
        }
        if ($type == "standings"){
            return 0;
        }
        if (empty($venue)){
            return 0;
        }
        $capacity = (int) em_get_term_meta($venue, 'seating_capacity', true);
        return $capacity;
    }
    return 0;
}

function is_registration_magic_active() {
    if (defined("REGMAGIC_BASIC") || defined("REGMAGIC_GOLD"))
        return true;
    else
        return false;
}


// Returns true if both dates are equal
function em_compare_event_dates($event_id) {
    $start_date = em_showDateTime(em_get_post_meta($event_id, 'start_date', true), false);
    $end_date = em_showDateTime(em_get_post_meta($event_id, 'end_date', true), false);
    if(get_option('time_format') != 'm/d/Y') {
        if ($start_date == $end_date)
            return true;
        else
            return false;
    } else {
        if (strtotime($start_date) == strtotime($end_date))
            return true;
        else
            return false;
    }
}

function em_is_event_expired($event_id) {
    // Check event status
    $event = get_post($event_id);
    if ($event->post_status == "expired")
        return true;

    return false;
}

function em_set_mail_content_type_html($content_type) {
    $content_type = 'text/html';
    return $content_type;
}

function em_set_mail_from($original_email_address) {
    return get_option('admin_email');
}

function em_set_mail_from_name($original_from_address) {
    return get_option('blogname');
}

function em_rm_custom_data($user_id) {
    $current_user = get_user_by("ID", $user_id);
    $data = new stdClass();
    $data->is_user = true;
    $data->user = $current_user;
    $rm_Service = new RM_Services();
    $data->custom_fields = $rm_Service->get_custom_fields($current_user->user_email);

    if ($data->user->first_name) {
        ?>
        <div class="em-booking-row">
            <span class="em-booking-label"><?php echo RM_UI_Strings::get('FIELD_TYPE_FNAME'); ?>:</span>
            <span class="em-booking-detail"><?php echo $data->user->first_name; ?></span>
        </div>
        <?php
    }
    if ($data->user->last_name) {
        ?>

        <div class="em-booking-row">
            <span class="em-booking-label"><?php echo RM_UI_Strings::get('FIELD_TYPE_LNAME'); ?>:</span>
            <span class="em-booking-detail"><?php echo $data->user->last_name; ?></span>
        </div>
        <?php
    }
    if ($data->user->description) {
        ?>

        <div class="em-booking-row">
            <span class="em-booking-label"><?php echo RM_UI_Strings::get('LABEL_BIO'); ?>:</span>
            <span class="em-booking-detail"><?php echo $data->user->description; ?></span>
        </div>
        <?php
    }
    if ($data->user->user_email) {
        ?>

        <div class="em-booking-row">
            <span class="em-booking-label"><?php echo RM_UI_Strings::get('LABEL_EMAIL'); ?>:</span>
            <span class="em-booking-detail"><?php echo $data->user->user_email; ?></span>
        </div>
        <?php
    }
    if ($data->user->sec_email) {
        ?>

        <div class="em-booking-row">
            <span class="em-booking-label"><?php echo RM_UI_Strings::get('LABEL_SECEMAIL'); ?>:</span>
            <span class="em-booking-detail"><?php echo $data->user->sec_email; ?></span>
        </div>
        <?php
    }
    if ($data->user->phone) {
        ?>

        <div class="em-booking-row">
            <span class="em-booking-label"><?php echo RM_UI_Strings::get('FIELD_TYPE_PHONE'); ?>:</span>
            <span class="em-booking-detail"><?php echo $data->user->phone; ?></span>
        </div>
        <?php
    }
    if ($data->user->nickname) {
        ?>

        <div class="em-booking-row">
            <span class="em-booking-label"><?php echo RM_UI_Strings::get('FIELD_TYPE_NICKNAME'); ?>:</span>
            <span class="em-booking-detail"><?php echo $data->user->nickname; ?></span>
        </div>
        <?php
    }
    if ($data->user->user_url) {
        ?>

        <div class="em-booking-row">
            <span class="em-booking-label"><?php echo RM_UI_Strings::get('FIELD_TYPE_WEBSITE'); ?>:</span>
            <span class="em-booking-detail"><?php echo $data->user->user_url; ?></span>
        </div>
        <?php
    }

    if (is_array($data->custom_fields) || is_object($data->custom_fields))
        foreach ($data->custom_fields as $field_id => $sub) {
            $key = $sub->label;
            $meta = $sub->value;
            $sub_original = $sub;
            if (!isset($sub->type)) {
                $sub->type = '';
            }

            $meta = RM_Utilities::strip_slash_array(maybe_unserialize($meta));
            ?>
            <div class="em-booking-row">

                <span class="em-booking-label"><?php echo $key; ?></span>
                <span class="em-booking-detail">
            <?php
            if (is_array($meta) || is_object($meta)) {
                if (isset($meta['rm_field_type']) && $meta['rm_field_type'] == 'File') {
                    unset($meta['rm_field_type']);

                    foreach ($meta as $sub) {

                        $att_path = get_attached_file($sub);
                        $att_url = wp_get_attachment_url($sub);
                        ?>
                                <div class="rm-submission-attachment">
                                <?php echo wp_get_attachment_link($sub, 'thumbnail', false, true, false); ?>
                                    <div class="rm-submission-attachment-field"><?php echo basename($att_path); ?></div>
                                    <div class="rm-submission-attachment-field"><a href="<?php echo $att_url; ?>"><?php echo RM_UI_Strings::get('LABEL_DOWNLOAD'); ?></a></div>
                                </div>

                        <?php
                    }
                } elseif (isset($meta['rm_field_type']) && $meta['rm_field_type'] == 'Address') {
                    $sub = $meta['original'] . '<br/>';
                    if (count($meta) === 8) {
                        $sub .= '<b>Street Address</b> : ' . $meta['st_number'] . ', ' . $meta['st_route'] . '<br/>';
                        $sub .= '<b>City</b> : ' . $meta['city'] . '<br/>';
                        $sub .= '<b>State</b> : ' . $meta['state'] . '<br/>';
                        $sub .= '<b>Zip code</b> : ' . $meta['zip'] . '<br/>';
                        $sub .= '<b>Country</b> : ' . $meta['country'];
                    }
                    echo $sub;
                } elseif ($sub->type == 'Time') {
                    echo $meta['time'] . ", Timezone: " . $meta['timezone'];
                } else {
                    $sub = implode(', ', $meta);
                    echo $sub;
                }
            } else {
                if ($sub->type == 'Rating') {
                    echo RM_Utilities::enqueue_external_scripts('script_rm_rating', RM_BASE_URL . 'public/js/rating3/jquery.rateit.js');
                    echo '<div class="rateit" id="rateit5" data-rateit-min="0" data-rateit-max="5" data-rateit-value="' . $meta . '" data-rateit-ispreset="true" data-rateit-readonly="true"></div>';
                } else
                    echo $meta;
            }
            ?>
                </span>
            </div>
            <?php
        }
}

function em_datetime_diff($start, $end) {
    $start_date_time = new DateTime();
    $start_date_time->setTimestamp($start);
    $end_date_time = new DateTime();
    $end_date_time->setTimestamp($end);
    $interval = $end_date_time->diff($start_date_time);
    return $interval;
}

function em_get_mail_confirm_content($order_id) {
    $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
    $event_detail = $booking_service->get_event_by_booking($order_id);
    $data = (array) $event_detail;
    return $data;
}

function em_localize_map_info() {
    $gmap_api_key = em_global_settings('gmap_api_key');
    $local_objects = array();

    if ($gmap_api_key):
        $local_objects['gmap_uri'] = 'https://maps.googleapis.com/maps/api/js?key=' . $gmap_api_key . '&libraries=places';
    else:
        $local_objects['gmap_uri'] = false;
    endif;
    if ( !wp_script_is( 'em-google-map', 'registered' ) ) {
        wp_register_script( 'em-google-map', EM_BASE_URL . 'includes/js/em-map.js', false, EVENTPRIME_VERSION, false );
    }
    wp_localize_script('em-google-map', "em_map_info", $local_objects);
    wp_enqueue_script('em-google-map');
}

/*
 * Convert an associative array into key,label objects for dropdown fields
 */
function em_array_to_options($data = array()) {
    $options = array();
    foreach ($data as $key => $value) {
        $option = new stdClass();
        if (is_numeric($key))
            $option->key = $value;
        else
            $option->key = $key;

        $option->label = $value;
        $options[] = $option;
    }

    return $options;
}

function em_array_sort_by_date($a, $b) {
    return strtotime($a) > strtotime($b);
}

function get_payment_log_info($booking_id) {
    $payment_log = maybe_unserialize(em_get_post_meta($booking_id, 'payment_log', true));
    $currency_symbol = "";
    $currency_code = em_global_settings('currency');

    if (isset($payment_log['payment_gateway']) && ($payment_log['payment_gateway'] == 'paypal' )){
        $currency_code = $payment_log['mc_currency'];
    }
    elseif(isset($payment_log['payment_gateway']) && $payment_log['payment_gateway'] == 'stripe'){
        $currency_code = $payment_log['currency'];
    }

    if ($currency_code){
        $all_currency_symbols = EventM_Constants::get_currency_symbol();
        $currency_symbol = $all_currency_symbols[$currency_code];
    }
    else
    {
        $currency_symbol = EM_DEFAULT_CURRENCY;
    }
    return $currency_symbol;
}

function em_redirect_event_posts() {
    $postID = url_to_postid($_SERVER['REQUEST_URI']);
    $post = get_post($postID);
    $redirect_url = '';
    if (!empty($post) && $post->post_status != "trash") {
        $post_type = get_post_type($postID);
        if ($post_type == 'em_event') {
            $page_url = get_permalink(em_global_settings("events_page"));
            $redirect_url = add_query_arg("event", $postID, $page_url);
        } elseif ($post_type == 'em_performer') {
            $page_url = get_permalink(em_global_settings("performers_page"));
            $redirect_url = add_query_arg("performer", $postID, $page_url);
        } else
            return;

        wp_redirect($redirect_url);
        exit;
    }
}

function em_check_required_pages() {
    $notices = '';
    $pages = array(
        "events_page" => array("Event List", "[em_events"),
        "venues_page" => array("Site & Location", "[em_sites]"),
        "booking_page" => array("Booking", "[em_booking]"),
        "profile_page" => array("User Profile", "[em_profile"),
        "performers_page" => array("Performer List", "[em_performers]")
    );
    foreach ($pages as $key => $value) {
        $page_id = em_global_settings($key);
        $post = get_post($page_id);
        if(empty($post)){
            $notices .= '<p> For ' . $value[0] . ' use ' . $value[1] . ' shortcode</p>';
                continue;
        }
        $short_code_exists = strpos($post->post_content, $value[1]);
        if (empty($post) || $post->post_status == "trash" || $short_code_exists === false) {
            $notices .= '<p> For ' . $value[0] . ' use ' . $value[1] . ' shortcode</p>';
        }
    }

    if (!empty($notices)) 
    {
        echo '<div class="notice notice-success is-dismissible">EventPrime: It seems all the required pages are not configured.' . $notices .
        '<b>Note*: Once you have pasted all the shortcodes inside corresponding pages, you can configure the default pages in EventPrime Global Settings->Default Pages. </b>' .
        '</div>';
    }
}

function em_posts_order_by($orderby_statement) {
    return 'post_status DESC,' . $orderby_statement;
}

function em_add_editor($editor_id, $content = '') {
    wp_editor($content, $editor_id);
}

function em_currency_symbol() {
    $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
    $setting = $setting_service->load_model_from_db();
    $all_currency_symbols = EventM_Constants::get_currency_symbol();
    $currency_symbol = $all_currency_symbols[$setting->currency];
    return $currency_symbol;
}

function em_is_payment_gateway_enabled() {
    $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
    $setting = $setting_service->load_model_from_db();
    if ($setting->paypal_processor == 1 || (isset($setting->stripe_processor) && $setting->stripe_processor == 1) || (isset($setting->offline_processor) && $setting->offline_processor == 1)) {
        return true;
    } else {
        return false;
    }
}

function em_global_js_strings() {
    $site_local = get_locale();
    if(strpos($site_local, '_') !== false){
        $site_local = explode("_", $site_local)[0];
    }
    // datepicker format from global settings. Index 0 for js
    $datepicker_format_arr = em_global_settings('datepicker_format');
    return array(
        'date_format' => (!empty($datepicker_format_arr)) ? explode('&', em_global_settings('datepicker_format'))[0] : 'yy-mm-dd',
        'ajax_url' => admin_url('admin-ajax.php'),
        'site_language' => $site_local
    );
}

function em_is_user_admin() {
    if (current_user_can('manage_options')) {
        return true;
    }
    return false;
}

function em_manager_navs(){
    $performers_text = em_global_settings_button_title('Performers');
    $organizers_text = em_global_settings_button_title('Organizers');
    $manager_navs = array(
        array( 'key' => 'event_magic', 'label' => __('Event Manager','eventprime-event-calendar-management')),
        array( 'key' => 'em_event_types', 'label' => __('Event Type Manager','eventprime-event-calendar-management')),
        array( 'key' => 'em_venues', 'label' => __('Event Site Manager','eventprime-event-calendar-management')),
        array( 'key' => 'em_performers', 'label' => $performers_text . ' ' . __('Manager','eventprime-event-calendar-management')) ,
        array( 'key' => 'em_event_organizers', 'label' => $organizers_text . ' ' . __(' Manager','eventprime-event-calendar-management'))   
    );
    $manager_navs= apply_filters('event_magic_manager_navs',$manager_navs);
    return $manager_navs;
}

function em_dates_from_range($start, $end) 
{
    $array= array();    
    $interval = new DateInterval('P1D');
    
    $realEnd = new DateTime($end);
    $realEnd->add($interval);

    $period = new DatePeriod(
         new DateTime($start),
         $interval,
         $realEnd
    );

    foreach($period as $date) { 
        $array[] = $date->format('Y-m-d'); 
    }

    return $array;
}

function em_is_ssl(){
    return is_ssl();
}

function em_is_admin(){
    if(current_user_can('manage_options')){
        return true;
    }
    return false;
}

function em_get_converted_price_in_cent($price,$currency){
    if(em_is_price_conversion_req_for_stripe($currency))
        return $price*100;
    return $price;
}
    
function convert_fr($currency,$price){
    if(em_is_price_conversion_req_for_stripe($currency))
        return $price/100;
    return $price;
}
    
function em_is_price_conversion_req_for_stripe($currency){
    $currency= strtoupper($currency);
        switch($currency)
        {
            case 'BIF':
            case 'DJF':
            case 'JPY':
            case 'KRW':
            case 'PYG':
            case 'VND':
            case 'XAF':
            case 'XPF':
            case 'CLP':
            case 'GNF':
            case 'KMF':
            case 'MGA':
            case 'RWF':
            case 'VUV':
            case 'XOF':
                return false;
            default:
                return true;
        }
        return false;
}

function em_code_to_display_string($string = '') {
    if ($string !== '') {
        $string = str_replace('_', ' ', $string);
        $string = __(ucwords($string), 'eventprime-event-calendar-management');
    }
    return $string;
}

function em_get_admin_user_email() {
    $email = get_option('admin_email');
    $email = '<'.$email.'>';
    return $email;
}

function em_get_child_events($parent_id=-1) {
    $posts = get_posts(
        array(
            'post_parent' => $parent_id,
            'post_type' => EM_EVENT_POST_TYPE,
            'post_status' => 'any',
            'numberposts' => -1
        )
    );
    return $posts;
}

function em_get_calendar_locale() {
    $locale = get_locale();
    $locale = (empty($locale) || is_null($locale)) ? 'en' : $locale;
    
    if(strlen($locale)>5){
        $locale = substr($locale, 0, 5);
    }

    $locale = strtolower($locale);
    $locale = str_replace('_','-',$locale);

    if(in_array($locale,EventM_Constants::get_calendar_locales())){
        return $locale;
    } else {
        return substr($locale, 0, 2);;
    }
}

function em_get_site_domain() {
    $url = get_site_url();

    $url = str_replace('http://', '', $url);
    $url = str_replace('https://', '', $url);
    $url = str_replace('ftp://', '', $url);
    $url = str_replace('svn://', '', $url);
    $url = str_replace('www.', '', $url);

    $ex = explode('/', $url);
    $ex2 = explode('?', $ex[0]);

    return $ex2[0];
}

function event_m_get_buy_link() {
    $mgp = event_m_current_theme_aff_id();
    if(!empty($mgp))
        return 'https://metagauss.com/get-eventprime-for-wordpress/?mgp=' . $mgp;
    else
        return false;
}
    
function event_m_current_theme_aff_id() {
    $current_theme = event_m_current_theme_name();
    $set_of_themes = event_m_theme_list_obj();
    if(!empty($set_of_themes) && property_exists($set_of_themes,$current_theme))
        return intval($set_of_themes->$current_theme);
    else
        return false;
}
    
function event_m_current_theme_name() {
    $theme_obj = wp_get_theme();
    return $theme_obj->__get('title');
}
    
function event_m_theme_list_obj() {
    $file_path = EM_BASE_DIR . 'includes/lib/theme-list.json';
    $raw_json = file_get_contents($file_path);
    if($raw_json != false) {
        $raw_json = utf8_encode($raw_json);
        return json_decode($raw_json);
    } else {
        return false;
    }
}

function em_show_book_now_for_guest_users(){
    $em = event_magic_instance();
    if(in_array('guest-booking', $em->extensions)){
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings= $setting_service->load_model_from_db();
        if($global_settings->allow_guest_bookings){
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function em_older_posts_order_by($orderby_statement) {
    return 'post_status ASC,' . $orderby_statement;
}

function get_site_timezone_from_offset($offset){
    $offset = (string) $offset;
    $timezones = array(
        '-12' => 'Pacific/Auckland',
        '-11.5' => 'Pacific/Auckland', // Approx
        '-11' => 'Pacific/Apia',
        '-10.5' => 'Pacific/Apia', // Approx
        '-10' => 'Pacific/Honolulu',
        '-9.5' => 'Pacific/Honolulu', // Approx
        '-9' => 'America/Anchorage',
        '-8.5' => 'America/Anchorage', // Approx
        '-8' => 'America/Los_Angeles',
        '-7.5' => 'America/Los_Angeles', // Approx
        '-7' => 'America/Denver',
        '-6.5' => 'America/Denver', // Approx
        '-6' => 'America/Chicago',
        '-5.5' => 'America/Chicago', // Approx
        '-5' => 'America/New_York',
        '-4.5' => 'America/New_York', // Approx
        '-4' => 'America/Halifax',
        '-3.5' => 'America/Halifax', // Approx
        '-3' => 'America/Sao_Paulo',
        '-2.5' => 'America/Sao_Paulo', // Approx
        '-2' => 'America/Sao_Paulo',
        '-1.5' => 'Atlantic/Azores', // Approx
        '-1' => 'Atlantic/Azores',
        '-0.5' => 'UTC', // Approx
        '0' => 'UTC',
        '0.5' => 'UTC', // Approx
        '1' => 'Europe/Paris',
        '1.5' => 'Europe/Paris', // Approx
        '2' => 'Europe/Helsinki',
        '2.5' => 'Europe/Helsinki', // Approx
        '3' => 'Europe/Moscow',
        '3.5' => 'Europe/Moscow', // Approx
        '4' => 'Asia/Dubai',
        '4.5' => 'Asia/Tehran',
        '5' => 'Asia/Karachi',
        '5.5' => 'Asia/Kolkata',
        '5.75' => 'Asia/Katmandu',
        '6' => 'Asia/Yekaterinburg',
        '6.5' => 'Asia/Yekaterinburg', // Approx
        '7' => 'Asia/Krasnoyarsk',
        '7.5' => 'Asia/Krasnoyarsk', // Approx
        '8' => 'Asia/Shanghai',
        '8.5' => 'Asia/Shanghai', // Approx
        '8.75' => 'Asia/Tokyo', // Approx
        '9' => 'Asia/Tokyo',
        '9.5' => 'Asia/Tokyo', // Approx
        '10' => 'Australia/Melbourne',
        '10.5' => 'Australia/Adelaide',
        '11' => 'Australia/Melbourne', // Approx
        '11.5' => 'Pacific/Auckland', // Approx
        '12' => 'Pacific/Auckland',
        '12.75' => 'Pacific/Apia', // Approx
        '13' => 'Pacific/Apia',
        '13.75' => 'Pacific/Honolulu', // Approx
        '14' => 'Pacific/Honolulu',
    );
    $timezone = isset($timezones[$offset]) ? $timezones[$offset] : NULL;
    return $timezone;
}

function get_gmt_offset($timezome = NULL){
    if(trim($timezone) != '' and $timezone != 'global'){
        $UTC = new DateTimeZone('UTC');
        $TZ = new DateTimeZone($timezone);

        $gmt_offset_seconds = $TZ->getOffset((new DateTime('now', $UTC)));
        $gmt_offset = ($gmt_offset_seconds / HOUR_IN_SECONDS);
    }
    else $gmt_offset = get_option('gmt_offset');

    $minutes = $gmt_offset*60;
    $hour_minutes = sprintf("%02d", $minutes%60);

    // Convert the hour into two digits format
    $h = ($minutes-$hour_minutes)/60;
    $hours = sprintf("%02d", abs($h));

    // Add - sign to the first of hour if it's negative
    if($h < 0) $hours = '-'.$hours;

    return (substr($hours, 0, 1) == '-' ? '' : '+').$hours.':'.(((int) $hour_minutes < 0) ? abs($hour_minutes) : $hour_minutes);
}

function em_get_site_timezone(){
    $userTimezone = get_option( 'timezone_string' );
    if(empty($userTimezone)){
        $offset  = (float) get_option( 'gmt_offset' );
        $userTimezone = get_site_timezone_from_offset($offset);
    }
    if($userTimezone == 'UTC'){
        if($userIp >= '192.168.0.0' && $userIp <= '192.168.255.255'){
            $userTimezone = date_default_timezone_get();
        }
    }
    return $userTimezone;
}

function em_gmt_offset_seconds($date = NULL) {
    if($date) {
        $timezone = new DateTimeZone(em_get_user_timezone());
        // Convert to Date
        if(is_numeric($date)) $date = date('Y-m-d', $date);

        $target = new DateTime($date, $timezone);
        return $timezone->getOffset($target);
    }
    else
    {
        $gmt_offset = get_option('gmt_offset');
        $seconds = $gmt_offset * HOUR_IN_SECONDS;

        return (substr($gmt_offset, 0, 1) == '-' ? '' : '+').$seconds;
    }
}

function ep_all_exts(){
    $exts = array('Live Seating', 'Event Analytics', 'Event Sponsors', 'Stripe Payments', 'Offline Payments', 'Recurring Events', 'Attendees List', 'Coupon Codes', 'Guest Bookings', 'Event List Widgets', 'Admin Attendee Bookings', 'Event Wishlist', 'Event Comments', 'Event Automatic Discounts', 'Google Events Import Export', 'Events Import Export', 'EventPrime MailPoet', 'WooCommerce Integration', 'EventPrime Zoom Integration', 'Zapier Integration', 'EventPrime Invoices', 'Twilio Text Notifications');
    return $exts;
}

function em_get_more_extension_data($plugin_name){
    $data['is_activate'] = $data['is_installed'] = $data['url'] = '';
    $data['button'] = 'Download';
    $data['class_name'] = 'ep-install-now-btn';
    $em = event_magic_instance();
    $installed_plugins = get_plugins();
    $installed_plugin_file = array();
    $installed_plugin_url = array();
    if(!empty($installed_plugins)){
        foreach ($installed_plugins as $key => $value) {
            $exp = explode('/', $key);
            $installed_plugin_file[] = end($exp);
            $installed_plugin_url[] = $key;
        }
    }
    switch ($plugin_name) {
        case 'Live Seating':
            $data['url'] = 'https://eventprime.net/extensions/live-seating/';
            if(in_array('event-seating.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-seating.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Seating");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_venues');
            }
            $data['is_free'] = 0;
            $data['image'] = 'seating-integration-icon.png';
            $data['desc'] = "Add live seat selection on your events and provide seat based tickets to your event attendees. Set a seating arrangement for all your Event Sites with specific rows, columns, and walking aisles using EventPrime's very own Event Site Seating Builder.";
            break;
        case 'Event Analytics':
            $data['url'] = 'https://eventprime.net/extensions/event-analytics/';
            if(in_array('event-analytics.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-analytics.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Analytics");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_analytics');
            }
            $data['is_free'] = 1;
            $data['image'] = 'ep-analytics-icon.png';
            $data['desc'] = "Stay updated on all the Revenue and Bookings coming your way through EventPrime. The Event Analytics extension empowers you with data and graphs that you need to know how much your events are connecting with their audience.";
            break;
        case 'Event Sponsors':
            $data['url'] = 'https://eventprime.net/extensions/event-sponsors/';
            if(in_array('event-sponser.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-sponser.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Sponser");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=event_magic');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-sponser-icon.png';
            $data['desc'] = "Add Sponsor(s) to your events. Upload Sponsor logos and they will appear on the event page alongside all other details of the event.";
            break;
        case 'Stripe Payments':
            $data['url'] = 'https://eventprime.net/extensions/stripe-payments/';
            if(in_array('event-stripe.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-stripe.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Stripe");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-stripe-icon.png';
            $data['desc'] = "Start accepting Event Booking payments using the Stripe Payment Gateway. By integrating Stripe with EventPrime, event attendees can now pay with their credit cards while you receive the payment in your Stripe account.";
            break;
        case 'Offline Payments':
            $data['url'] = 'https://eventprime.net/extensions/offline-payments/';
            if(in_array('eventprime-offline.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('eventprime-offline.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Offline");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-offline-payment.png';
            $data['desc'] = "Don't want to use any online payment gateway to collect your event booking payments? Don't worry. With the Offline Payments extension, you can accept event bookings online while you collect booking payments from attendees offline.";
            break;
        case 'Recurring Events':
            $data['url'] = 'https://eventprime.net/extensions/recurring-events/';
            if(in_array('eventprime-recurring-events.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('eventprime-recurring-events.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EventPrime_Recurring_Events");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-recurring-events-icon.png';
            $data['desc'] = "Create events that recur by your specified numbers of days, weeks, months, or years. Make updates to all recurring events at once by updating the main event. Or make custom changes to individual recurring events, such as different performers, event sites, booking amount etc.";
            break;
        case 'Attendees List':
            $data['url'] = 'https://eventprime.net/extensions/attendees-list/';
            if(in_array('eventprime-attendees-list.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('eventprime-attendees-list.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Attendees_List");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-attendees-list-icon.png';
            $data['desc'] = "Display names of your Event Attendees on the Event page. Or within the new Attendees List widget.";
            break;
        case 'Coupon Codes':
            $data['url'] = 'https://eventprime.net/extensions/coupon-codes/';
            if(in_array('event-coupons.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-coupons.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Coupons");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_coupons');
            }
            $data['is_free'] = 0;
            $data['image'] = 'coupon-code-extension-icon.png';
            $data['desc'] = "Create and activate coupon codes for allowing Attendees for book for events at a discount. Set discount type and limits on coupon code usage, or deactivate at will.";
            break;
        case 'Guest Bookings':
            $data['url'] = 'https://eventprime.net/extensions/guest-bookings/';
            if(in_array('event-guest-booking.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-guest-booking.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Guest_Booking");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'event-guest-booking-icon.png';
            $data['desc'] = "Allow attendees to complete their event bookings without registering or logging in.";
            break;
        case 'Event List Widgets':
            $data['url'] = 'https://eventprime.net/extensions/event-list-widgets/';
            if(in_array('eventprime-more-widgets.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('eventprime-more-widgets.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_List_Widget");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'event-more-widget-icon.png';
            $data['desc'] = "Add 3 new Event Listing widgets to your website. These are the Popular Events list, Featured Events list, and Related Events list widgets.";
            break;
        case 'Admin Attendee Bookings':
            $data['url'] = 'https://eventprime.net/extensions/admin-attendee-bookings/';
            if(in_array('event-attendees-booking.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-attendees-booking.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Attendees_Booking");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_bookings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-manually-attendees-booking.png';
            $data['desc'] = "Admins can now create custom attendee bookings from the backend EventPrime dashboard.";
            break;
        case 'Event Wishlist':
            $data['url'] = 'https://eventprime.net/extensions/event-wishlist/';
            if(in_array('event-wishlist.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-wishlist.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Wishlist");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-save-events-icon.png';
            $data['desc'] = "Users can now wishlist events that they would like to attend and can see the list of all their wishlisted events on their frontend profiles.";
            break;
        case 'Event Comments':
            $data['url'] = 'https://eventprime.net/extensions/event-comments/';
            if(in_array('eventprime-event-comments.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('eventprime-event-comments.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Event_Comments");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-event-comment-icon.png';
            $data['desc'] = "Allow users to post comments on EventPrime events. Admins can manage these comments the same way as they manage WordPress comments.";
            break;
        case 'Event Automatic Discounts':
            $data['url'] = 'https://eventprime.net/extensions/automatic-discounts/';
            if(in_array('automatic-discounts.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('automatic-discounts.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Automatic_Discounts");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'event-early-bird-discount-icon.png';
            $data['desc'] = "Automatically display discounts on an event for a user based on Admin rules. With Automatic Discount Extension, you can create and activate discounts by setting rules (eligibility criteria) to offer the eligible users a discount on bookings. The discounts are automatically applied to the bookings.";
            break;
        case 'Google Events Import Export':
            $data['url'] = 'https://eventprime.net/extensions/google-import-export/';
            if(in_array('google-import-export.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('google-import-export.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Google_Import_Export_Events");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_google_import_export_events');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-google-ie.png';
            $data['desc'] = "Admin now import and export his Google Calendar events to and from EventPrime Calendar.";
            break;
        case 'Events Import Export':
            $data['url'] = 'https://eventprime.net/extensions/events-import-export/';
            if(in_array('events-import-export.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('events-import-export.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Events_Import_Export");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_file_import_export_events');
            }
            $data['is_free'] = 1;
            $data['image'] = 'ep-file-import-export-icon.png';
            $data['desc'] = "Import or export events in popular file formats like CSV, ICS, XML and JSON.";
            break;
        case 'EventPrime MailPoet':
            $data['url'] = 'https://eventprime.net/extensions/eventprime-mailpoet/';
            if(in_array('eventprime-mailpoet.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('eventprime-mailpoet.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_MailPoet");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_mailpoet');
            }
            $data['is_free'] = 1;
            $data['image'] = 'event-mailpoet-icon.png';
            $data['desc'] = "Connect and engage with your users by subscribing event attendees to MailPoet lists. Users can opt-in multiple newsletters during checkout and can also manage subscriptions in user account area.";
            break;
        case 'WooCommerce Integration':
            $data['url'] = 'https://eventprime.net/extensions/eventprime-woocommerce-integration/';
            if(in_array('woocommerce-integration.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('woocommerce-integration.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Woocommerce_Integration");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 1;
            $data['image'] = 'ep-woo-icon.png';
            $data['desc'] = "This extension allows you to add optional and/ or mandatory products to your events. You can define quantity or let users chose it themselves. Fully integrates with EventPrime checkout experience and WooCommerce order management.";
            break;
        case 'EventPrime Zoom Integration':
            $data['url'] = 'https://eventprime.net/extensions/eventprime-zoom-integration/';
            if(in_array('eventprime-zoom-meetings.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('eventprime-zoom-meetings.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Zoom_Meetings");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 1;
            $data['image'] = 'ep-zoom-icon.png';
            $data['desc'] = "This extension seamlessly creates virtual events to be conducted on Zoom through the EventPrime plugin. The extension provides easy linking of your website to that of Zoom. Commence and let the attendees join the event with a single click.";
            break;
        case 'Zapier Integration':
            $data['url'] = 'https://eventprime.net/extensions/eventprime-zapier-integration/';
            if(in_array('event-zapier.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-zapier.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Zapier_Integration");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 1;
            $data['image'] = 'ep-zapier-icon.png';
            $data['desc'] = "Extend the power of EventPrime using Zapier's powerful automation tools! Connect with over 3000 apps by building custom templates using EventPrime triggers.";
            break;
        case 'EventPrime Invoices':
            $data['url'] = 'https://eventprime.net/extensions/eventprime-invoices/';
            if(in_array('event-invoices.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('event-invoices.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_Event_Invoices");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_global_settings');
            }
            $data['is_free'] = 1;
            $data['image'] = 'ep-invoice-icon.png';
            $data['desc'] = "Allows fully customizable PDF invoices, complete with your company branding, to be generated and emailed with booking details to your users.";
            break;
        case 'Twilio Text Notifications':
            $data['url'] = 'https://eventprime.net/extensions/eventprime-twilio-text-notifications/';
            if(in_array('sms-integration.php', $installed_plugin_file)){
                $data['button'] = 'Activate';
                $data['class_name'] = 'ep-activate-now-btn';
                $file_key = array_search('sms-integration.php', $installed_plugin_file);
                $data['url'] = em_get_extension_activation_url($installed_plugin_url[$file_key]);
            }
            $data['is_activate'] = class_exists("EM_SMS_Integration");
            if($data['is_activate']){
                $data['button'] = 'Setting';
                $data['class_name'] = 'ep-option-now-btn';
                $data['url'] = admin_url('admin.php?page=em_sms_settings');
            }
            $data['is_free'] = 0;
            $data['image'] = 'ep-sms-integration-icon.png';
            $data['desc'] = "Keep your users engaged with text/ SMS notification system. Creating Twilio account is quick and easy. With this extension installed, you will be able to configure admin and user notifications separately, with personalized content.";
            break;
    }
    return $data;
}

function em_get_extension_activation_url($path){
    $plugin = $path;
    if (strpos($path, '/')) {
        $path = str_replace('/', '%2F', $path);
    }
    $activateUrl = sprintf(admin_url('plugins.php?action=activate&plugin=%s'), $path);    
    $activateUrl = wp_nonce_url($activateUrl, 'activate-plugin_' . $plugin);
    return $activateUrl;
}

function em_price_with_position($price, $currency_symbol = ''){
    if(empty($currency_symbol)){
        $currency_symbol = em_currency_symbol();
    }
    $currency_position = em_global_settings('currency_position');
    $price_with_curr_pos = $currency_symbol . $price;
    if($currency_position == 'before_space'){
        $price_with_curr_pos = $currency_symbol . ' '. $price;
    }
    if($currency_position == 'after'){
        $price_with_curr_pos = $price . $currency_symbol;
    }
    if($currency_position == 'after_space'){
        $price_with_curr_pos = $price . ' ' . $currency_symbol;
    }
    return $price_with_curr_pos;
}

function event_m_get_offer_data(){
    $url = "https://eventprime.net/ep-offers.json";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);  
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 3);     
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    $html = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($html,true);
    return $json;
}

function get_ep_table_name($param){
    global $wpdb;
    return $wpdb->prefix.$param;
}

function em_global_settings_date_format() {
    $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));    
    if(!empty($datepicker_format_arr[1])):
    return $datepicker_format_arr[1];
    endif; 
}

function em_global_settings_button_title($button_title, $text_domain = 'eventprime-event-calendar-management'){
    $button_titles = em_global_settings('button_titles');
    if(!empty($button_titles) && isset($button_titles->$button_title) && !empty($button_titles->$button_title)){
        return esc_html__( $button_titles->$button_title, $text_domain );  
    } else{
        return esc_html__( $button_title, $text_domain );     
    }
}

function is_multidate_event($event){
    if(is_numeric($event->start_date) && is_numeric($event->end_date)){
        $totalSecondsDiff = abs($event->start_date - $event->end_date);
        $totalDaysDiff = $totalSecondsDiff/60/60/24;
        if($totalDaysDiff > 1){
            return true;
        }
    }
    return false;
}

function em_booking_js_strings() {
    $booking_msg = array();
    $booking_msg['required_field'] = esc_html__('This is required field', 'eventprime-event-calendar-management');
    $booking_msg['invalid_email'] = esc_html__('Please enter valid email', 'eventprime-event-calendar-management');
    $booking_msg['invalid_phone'] = esc_html__('Please enter valid phone', 'eventprime-event-calendar-management');
    $booking_msg['required_name'] = esc_html__('Please enter name', 'eventprime-event-calendar-management');
    $booking_msg['required_email'] = esc_html__('Please enter email', 'eventprime-event-calendar-management');
    $booking_msg['required_phone'] = esc_html__('Please enter phone', 'eventprime-event-calendar-management');
    return array(
        'error_msg' => $booking_msg,
    );
}

function em_get_event_date( $event ) {
    if(!empty($event->all_day)){
        if(is_multidate_event($event)){
            $event_date = date_i18n(get_option('date_format'), $event->start_date) . ' - ' . date_i18n(get_option('date_format'), $event->end_date);
        }
        else{
            $event_date = date_i18n(get_option('date_format'),$event->start_date).' - '.__('ALL DAY','eventprime-event-calendar-management');
        }
    }
    else{
        $event_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
        $event_date .= ' - ';
        $event_date .= date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
    }
    return $event_date;
}

function em_get_single_event_page_url( $event, $global_settings ) {
    $url = '';
    if(absint($event->custom_link_enabled) == 1){
        $url = $event->custom_link;
        if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
            $url = add_query_arg('event_id', $event->id, get_permalink($global_settings->profile_page));
        }
    }
    else{
        $url = add_query_arg('event', $event->id, get_page_link($global_settings->events_page));
        $enable_seo_urls = em_global_settings('enable_seo_urls');
        if(!empty($enable_seo_urls)){
            $url = get_permalink($event->id);
        }
    }
    return $url;
}

function em_custom_type_page_url( $type ) {
    $enable_seo_urls = em_global_settings('enable_seo_urls');
    if ( $enable_seo_urls == 1 ) {
        $seo_urls = em_global_settings('seo_urls');
        $url = '';
        if($type == 'event'){
            $url = ( isset( $seo_urls->event_page_type_url ) && !empty( $seo_urls->event_page_type_url ) ) ? $seo_urls->event_page_type_url : 'event' ;
        }
        if($type == 'performer'){
            $url = ( isset( $seo_urls->performer_page_type_url ) && !empty( $seo_urls->performer_page_type_url ) ) ? $seo_urls->performer_page_type_url : 'performer' ;
        }
        if($type == 'organizer'){
            $url = ( isset( $seo_urls->organizer_page_type_url ) && !empty( $seo_urls->organizer_page_type_url ) ) ? $seo_urls->organizer_page_type_url : 'organizer' ;
        }
        if($type == 'venues'){
            $url = ( isset( $seo_urls->venues_page_type_url ) && !empty( $seo_urls->venues_page_type_url ) ) ? $seo_urls->venues_page_type_url : 'venues' ;
        }
        if($type == 'types'){
            $url = ( isset( $seo_urls->types_page_type_url ) && !empty( $seo_urls->types_page_type_url ) ) ? $seo_urls->types_page_type_url : 'types' ;
        }
        return $url;    
    }
    return $type;   
}

function em_check_context_user_capabilities( $user_can ) {
    if( ! empty($user_can) ) {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        foreach ( $user_can as $cap ) {
            if( current_user_can( $cap ) ) {
                return true;
            }
        }
        return false;
    }
    return false;
}

function ep_dynamic_social_sharing_fields(){
    $social_sharing_fields = array('facebook' => 'facebook', 'instagram' => 'instagram', 'linkedin' => 'linkedin', 'twitter' => 'twitter');
    return $social_sharing_fields;
}

function ep_check_column_size( $number = 3 ){
    switch($number){
        case 1 : $cols = 12;
        break;
        case 2 : $cols = 6;
        break;
        case 3 : $cols = 4;
        break;
        case 4 : $cols = 3;
        break;
        default: $cols = 4; 
    }
    return $cols;
}

function ep_listing_page_view_options(){
    $listing_page_view_options = array("card" => "Card View", "box" => "Box View", "list" => "List View");
    return $listing_page_view_options;
}

function ep_upcoming_event_view_options(){
    $upcoming_event_view_options = array("card" => "Card View", "list" => "List View", "mini-list" => "Simple list" );
    return $upcoming_event_view_options;
}

function ep_hex2rgba($color, $opacity = false) {
    $default = 'rgb(0,0,0)';
    if(empty($color))
        return $default; 
 
    if ($color[0] == '#' ) {
        $color = substr( $color, 1 );
    }
    if (strlen($color) == 6) {
        $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
    } elseif ( strlen( $color ) == 3 ) {
        $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
    } else {
        return $default;
    }
    $rgb =  array_map('hexdec', $hex);
    if($opacity){
        if(abs($opacity) == 1)
            $opacity = 1.0;
        $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
    } else {
        $output = 'rgb('.implode(",",$rgb).')';
    }

    return $output;
}

function ep_has_paid_ext(){
    $extensions = event_magic_instance()->extensions;
    $free_extension = array('em_mailpoet', 'analytics', 'file_import_export_events', 'woocommerce_integration', 'zoom-meetings', 'event_invoices', 'zapier-integration');
    $have_paid = array_diff($extensions, $free_extension);
    if(empty($have_paid)){
        return 0;
    }
    return 1;
}