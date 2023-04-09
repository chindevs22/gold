<?php
class EventM_Paypal_Service extends EventM_Payment_Service {
    
    private static $instance = null;
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    public function __construct() {
        parent::__construct("paypal");
        $this->actions();
    }

    public function actions(){
        add_action('event_magic_front_payment_processors',array($this,'show_on_front'),10,1);
        add_action('event_magic_front_payment_forms',array($this,'paypal_form'));
        add_action('wp_ajax_event_magic_pp_ipn',array($this,'paypal_ipn'));
        add_action('wp_ajax_nopriv_event_magic_pp_ipn',array($this,'paypal_ipn'));
        add_action('wp_ajax_event_magic_pp_sbpr', array($this, 'update_mp_booking_info'));
        add_action('wp_ajax_nopriv_event_magic_pp_sbpr',array($this,'update_mp_booking_info'));
    }
    
    public function paypal_ipn(){
        if ($this->verify_ipn()) {
            $this->update_booking_info();
        }
    }
    
    public function verify_ipn() {
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $data = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2)
                $data[$keyval[0]] = urldecode($keyval[1]);
        }
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }

        foreach ($data as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        // Step 2: POST IPN data back to PayPal to validate
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $settings = $gs_service->load_model_from_db();
        $url = !empty($settings->payment_test_mode) ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        if (!($res = curl_exec($ch))) {
            curl_close($ch);
            exit;
        }
        curl_close($ch);

        // inspect IPN validation result and act accordingly
        if (strcmp($res, "VERIFIED") == 0) {
            return true;
        } else if (strcmp($res, "INVALID") == 0) {
            
        }
        return false;
    }

    public function update_booking_info() {
        $booking_service= EventM_Factory::get_service('EventM_Booking_Service');
        $ids = event_m_get_param('custom');
        $booking_ids = explode(',', $ids);
        foreach ($booking_ids as $booking_id) {
            $booking = $booking_service->load_model_from_db($booking_id);
            if (empty($booking->id))
                continue;
            $data = $_POST;
            $data['payment_gateway'] = 'paypal';
            if (strtolower($data['payment_status']) == "refunded") {
                if (isset($booking->payment_log['refund_log'])){
                    $booking->payment_log['refund_log'][] = $data;
                }  
                else{
                     $booking->payment_log['refund_log'] = array($data);
                }
                em_update_post_meta($booking->id, 'payment_log',$booking->payment_log);
                continue;
            } 
            else
            {
                $booking_service->confirm_booking($booking->id,$data);
            }    
        }
    }

    public function refund($booking, $info = array()) {
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $booking_service= EventM_Factory::get_service('EventM_Booking_Service');
        $order_info = $booking->order_info;
        $pp_log = $booking->payment_log;
        
        if ($booking->status == "refunded" || strtolower($pp_log['payment_status']) == "refunded")
            return false;
        
        if (empty($booking->id) || empty($pp_log))
            return false;
        
        $methodName_ = 'RefundTransaction';
        // Set request-specific fields.
        $transactionID = urlencode($pp_log['txn_id']);
        $refundType = urlencode('Full');  // or 'Partial'
        $amount = $booking_service->get_final_price($booking->id);
        if ($pp_log['mc_gross'] > $amount) {
            $refundType = urlencode('Partial');
            $memo = "Partial Refund";
        }

        $settings = $gs_service->load_model_from_db();
        $currencyID = urlencode($settings->currency);   // or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
        // Add request-specific fields to the request string.
        $nvpStr = "&TRANSACTIONID=$transactionID&REFUNDTYPE=$refundType&CURRENCYCODE=$currencyID";

        if (isset($memo)) {
            $nvpStr .= "&NOTE=$memo";
        }

        if (strcasecmp($refundType, 'Partial') == 0) {
            if (!isset($amount)) {
                exit('Partial Refund Amount is not specified.');
            } else {
                $nvpStr = $nvpStr . "&AMT=$amount";
            }

            if (!isset($memo)) {
                exit('Partial Refund Memo is not specified.');
            }
        }

        /**
         * Send HTTP POST Request
         *
         * @param     string     The API method name
         * @param     string     The POST Message fields in &name=value pair format
         * @return     array     Parsed HTTP Response body
         */
        // Set up your API credentials, PayPal end point, and API version.
        $API_UserName = urlencode($settings->paypal_api_username);
        $API_Password = urlencode($settings->paypal_api_password);
        $API_Signature = urlencode($settings->paypal_api_sig);
        
        if (empty($API_UserName) || empty($API_Password) || empty($API_Signature))
            return false;

        $sandbox = $settings->payment_test_mode;
        if ($sandbox == 1)
            $API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
        else
            $API_Endpoint = "https://api-3t.paypal.com/nvp";

        $version = urlencode('119');

        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // Set the API operation, version, and API signature in the request.
        $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr";

        // Set the request as a POST FIELD for curl.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        // Get response from the server.
        $httpResponse = curl_exec($ch);
        
        if (!$httpResponse) {
            exit("$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
        }

        // Extract the response details.
        $httpResponseAr = explode("&", $httpResponse);

        $httpParsedResponseAr = array();
        foreach ($httpResponseAr as $i => $value) {
            $tmpAr = explode("=", $value);
            if (sizeof($tmpAr) > 1) {
                $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
            exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
        }
        return $httpParsedResponseAr;
    }

    public function cancel($order_id) {
        
    }

    public function charge($info = array()) {
        return null;
    }

    public function show_on_front(){
        $settings_service= EventM_Factory::get_service('EventM_Setting_Service');
        $gs= $settings_service->load_model_from_db();
        if(empty($gs->paypal_processor))
            return;
    ?>
    <?php if( $gs->modern_paypal == 1 && isset( $gs->paypal_client_id ) && !empty( $gs->paypal_client_id ) ){
        $currency_code = ! empty( em_global_settings('currency') ) ? em_global_settings('currency') : 'USD'; ?>
        <div  class="difl kf-payment-mode-select" ng-show="price > 0 && bookable && payment_processors.hasOwnProperty('paypal')">
            <input ng-hide="true" type="radio" name="paypal" value="paypal" ng-model="data.payment_processor" />
            <label ng-disabled="requestInProgress" class="ep-payment-button ep-payment-checkout-btn-wrap ep-payment-checkout-paypal" ng-click="proceedToModernPaypal();data.payment_processor='paypal';"><?php echo em_global_settings_button_title('PayPal'); ?> <span><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="124px" height="33px" viewBox="0 0 124 33" enable-background="new 0 0 124 33" xml:space="preserve">
<path fill="#253B80" d="M46.211,6.749h-6.839c-0.468,0-0.866,0.34-0.939,0.802l-2.766,17.537c-0.055,0.346,0.213,0.658,0.564,0.658
	h3.265c0.468,0,0.866-0.34,0.939-0.803l0.746-4.73c0.072-0.463,0.471-0.803,0.938-0.803h2.165c4.505,0,7.105-2.18,7.784-6.5
	c0.306-1.89,0.013-3.375-0.872-4.415C50.224,7.353,48.5,6.749,46.211,6.749z M47,13.154c-0.374,2.454-2.249,2.454-4.062,2.454
	h-1.032l0.724-4.583c0.043-0.277,0.283-0.481,0.563-0.481h0.473c1.235,0,2.4,0,3.002,0.704C47.027,11.668,47.137,12.292,47,13.154z"
	/>
<path fill="#253B80" d="M66.654,13.075h-3.275c-0.279,0-0.52,0.204-0.563,0.481l-0.145,0.916l-0.229-0.332
	c-0.709-1.029-2.29-1.373-3.868-1.373c-3.619,0-6.71,2.741-7.312,6.586c-0.313,1.918,0.132,3.752,1.22,5.031
	c0.998,1.176,2.426,1.666,4.125,1.666c2.916,0,4.533-1.875,4.533-1.875l-0.146,0.91c-0.055,0.348,0.213,0.66,0.562,0.66h2.95
	c0.469,0,0.865-0.34,0.939-0.803l1.77-11.209C67.271,13.388,67.004,13.075,66.654,13.075z M62.089,19.449
	c-0.316,1.871-1.801,3.127-3.695,3.127c-0.951,0-1.711-0.305-2.199-0.883c-0.484-0.574-0.668-1.391-0.514-2.301
	c0.295-1.855,1.805-3.152,3.67-3.152c0.93,0,1.686,0.309,2.184,0.892C62.034,17.721,62.232,18.543,62.089,19.449z"/>
<path fill="#253B80" d="M84.096,13.075h-3.291c-0.314,0-0.609,0.156-0.787,0.417l-4.539,6.686l-1.924-6.425
	c-0.121-0.402-0.492-0.678-0.912-0.678h-3.234c-0.393,0-0.666,0.384-0.541,0.754l3.625,10.638l-3.408,4.811
	c-0.268,0.379,0.002,0.9,0.465,0.9h3.287c0.312,0,0.604-0.152,0.781-0.408L84.564,13.97C84.826,13.592,84.557,13.075,84.096,13.075z
	"/>
<path fill="#179BD7" d="M94.992,6.749h-6.84c-0.467,0-0.865,0.34-0.938,0.802l-2.766,17.537c-0.055,0.346,0.213,0.658,0.562,0.658
	h3.51c0.326,0,0.605-0.238,0.656-0.562l0.785-4.971c0.072-0.463,0.471-0.803,0.938-0.803h2.164c4.506,0,7.105-2.18,7.785-6.5
	c0.307-1.89,0.012-3.375-0.873-4.415C99.004,7.353,97.281,6.749,94.992,6.749z M95.781,13.154c-0.373,2.454-2.248,2.454-4.062,2.454
	h-1.031l0.725-4.583c0.043-0.277,0.281-0.481,0.562-0.481h0.473c1.234,0,2.4,0,3.002,0.704
	C95.809,11.668,95.918,12.292,95.781,13.154z"/>
<path fill="#179BD7" d="M115.434,13.075h-3.273c-0.281,0-0.52,0.204-0.562,0.481l-0.145,0.916l-0.23-0.332
	c-0.709-1.029-2.289-1.373-3.867-1.373c-3.619,0-6.709,2.741-7.311,6.586c-0.312,1.918,0.131,3.752,1.219,5.031
	c1,1.176,2.426,1.666,4.125,1.666c2.916,0,4.533-1.875,4.533-1.875l-0.146,0.91c-0.055,0.348,0.213,0.66,0.564,0.66h2.949
	c0.467,0,0.865-0.34,0.938-0.803l1.771-11.209C116.053,13.388,115.785,13.075,115.434,13.075z M110.869,19.449
	c-0.314,1.871-1.801,3.127-3.695,3.127c-0.949,0-1.711-0.305-2.199-0.883c-0.484-0.574-0.666-1.391-0.514-2.301
	c0.297-1.855,1.805-3.152,3.67-3.152c0.93,0,1.686,0.309,2.184,0.892C110.816,17.721,111.014,18.543,110.869,19.449z"/>
<path fill="#179BD7" d="M119.295,7.23l-2.807,17.858c-0.055,0.346,0.213,0.658,0.562,0.658h2.822c0.469,0,0.867-0.34,0.939-0.803
	l2.768-17.536c0.055-0.346-0.213-0.659-0.562-0.659h-3.16C119.578,6.749,119.338,6.953,119.295,7.23z"/>
<path fill="#253B80" d="M7.266,29.154l0.523-3.322l-1.165-0.027H1.061L4.927,1.292C4.939,1.218,4.978,1.149,5.035,1.1
	c0.057-0.049,0.13-0.076,0.206-0.076h9.38c3.114,0,5.263,0.648,6.385,1.927c0.526,0.6,0.861,1.227,1.023,1.917
	c0.17,0.724,0.173,1.589,0.007,2.644l-0.012,0.077v0.676l0.526,0.298c0.443,0.235,0.795,0.504,1.065,0.812
	c0.45,0.513,0.741,1.165,0.864,1.938c0.127,0.795,0.085,1.741-0.123,2.812c-0.24,1.232-0.628,2.305-1.152,3.183
	c-0.482,0.809-1.096,1.48-1.825,2c-0.696,0.494-1.523,0.869-2.458,1.109c-0.906,0.236-1.939,0.355-3.072,0.355h-0.73
	c-0.522,0-1.029,0.188-1.427,0.525c-0.399,0.344-0.663,0.814-0.744,1.328l-0.055,0.299l-0.924,5.855l-0.042,0.215
	c-0.011,0.068-0.03,0.102-0.058,0.125c-0.025,0.021-0.061,0.035-0.096,0.035H7.266z"/>
<path fill="#179BD7" d="M23.048,7.667L23.048,7.667L23.048,7.667c-0.028,0.179-0.06,0.362-0.096,0.55
	c-1.237,6.351-5.469,8.545-10.874,8.545H9.326c-0.661,0-1.218,0.48-1.321,1.132l0,0l0,0L6.596,26.83l-0.399,2.533
	c-0.067,0.428,0.263,0.814,0.695,0.814h4.881c0.578,0,1.069-0.42,1.16-0.99l0.048-0.248l0.919-5.832l0.059-0.32
	c0.09-0.572,0.582-0.992,1.16-0.992h0.73c4.729,0,8.431-1.92,9.513-7.476c0.452-2.321,0.218-4.259-0.978-5.622
	C24.022,8.286,23.573,7.945,23.048,7.667z"/>
<path fill="#222D65" d="M21.754,7.151c-0.189-0.055-0.384-0.105-0.584-0.15c-0.201-0.044-0.407-0.083-0.619-0.117
	c-0.742-0.12-1.555-0.177-2.426-0.177h-7.352c-0.181,0-0.353,0.041-0.507,0.115C9.927,6.985,9.675,7.306,9.614,7.699L8.05,17.605
	l-0.045,0.289c0.103-0.652,0.66-1.132,1.321-1.132h2.752c5.405,0,9.637-2.195,10.874-8.545c0.037-0.188,0.068-0.371,0.096-0.55
	c-0.313-0.166-0.652-0.308-1.017-0.429C21.941,7.208,21.848,7.179,21.754,7.151z"/>
<path fill="#253B80" d="M9.614,7.699c0.061-0.393,0.313-0.714,0.652-0.876c0.155-0.074,0.326-0.115,0.507-0.115h7.352
	c0.871,0,1.684,0.057,2.426,0.177c0.212,0.034,0.418,0.073,0.619,0.117c0.2,0.045,0.395,0.095,0.584,0.15
	c0.094,0.028,0.187,0.057,0.278,0.086c0.365,0.121,0.704,0.264,1.017,0.429c0.368-2.347-0.003-3.945-1.272-5.392
	C20.378,0.682,17.853,0,14.622,0h-9.38c-0.66,0-1.223,0.48-1.325,1.133L0.01,25.898c-0.077,0.49,0.301,0.932,0.795,0.932h5.791
	l1.454-9.225L9.614,7.699z"/>
</svg>
</span></label>
        </div>
        <!-- smart paypal button -->
        <script src="https://www.paypal.com/sdk/js?currency=<?php echo $currency_code; ?>&client-id=<?php echo $gs->paypal_client_id;?>&vault=true"></script>
    <?php
        }else{?>
        <div  class="difl kf-payment-mode-select" ng-show="price > 0 && bookable && payment_processors.hasOwnProperty('paypal')">
            <input type="radio" name="paypal" value="paypal" ng-model="data.payment_processor" /><label class="ep-payment-button ep-payment-checkout-btn-wrap ep-payment-checkout-paypal" ng-click="data.payment_processor='paypal'"><?php echo __("Paypal", 'eventprime-event-calendar-management'); ?><span><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="124px" height="33px" viewBox="0 0 124 33" enable-background="new 0 0 124 33" xml:space="preserve">
<path fill="#253B80" d="M46.211,6.749h-6.839c-0.468,0-0.866,0.34-0.939,0.802l-2.766,17.537c-0.055,0.346,0.213,0.658,0.564,0.658
	h3.265c0.468,0,0.866-0.34,0.939-0.803l0.746-4.73c0.072-0.463,0.471-0.803,0.938-0.803h2.165c4.505,0,7.105-2.18,7.784-6.5
	c0.306-1.89,0.013-3.375-0.872-4.415C50.224,7.353,48.5,6.749,46.211,6.749z M47,13.154c-0.374,2.454-2.249,2.454-4.062,2.454
	h-1.032l0.724-4.583c0.043-0.277,0.283-0.481,0.563-0.481h0.473c1.235,0,2.4,0,3.002,0.704C47.027,11.668,47.137,12.292,47,13.154z"
	/>
<path fill="#253B80" d="M66.654,13.075h-3.275c-0.279,0-0.52,0.204-0.563,0.481l-0.145,0.916l-0.229-0.332
	c-0.709-1.029-2.29-1.373-3.868-1.373c-3.619,0-6.71,2.741-7.312,6.586c-0.313,1.918,0.132,3.752,1.22,5.031
	c0.998,1.176,2.426,1.666,4.125,1.666c2.916,0,4.533-1.875,4.533-1.875l-0.146,0.91c-0.055,0.348,0.213,0.66,0.562,0.66h2.95
	c0.469,0,0.865-0.34,0.939-0.803l1.77-11.209C67.271,13.388,67.004,13.075,66.654,13.075z M62.089,19.449
	c-0.316,1.871-1.801,3.127-3.695,3.127c-0.951,0-1.711-0.305-2.199-0.883c-0.484-0.574-0.668-1.391-0.514-2.301
	c0.295-1.855,1.805-3.152,3.67-3.152c0.93,0,1.686,0.309,2.184,0.892C62.034,17.721,62.232,18.543,62.089,19.449z"/>
<path fill="#253B80" d="M84.096,13.075h-3.291c-0.314,0-0.609,0.156-0.787,0.417l-4.539,6.686l-1.924-6.425
	c-0.121-0.402-0.492-0.678-0.912-0.678h-3.234c-0.393,0-0.666,0.384-0.541,0.754l3.625,10.638l-3.408,4.811
	c-0.268,0.379,0.002,0.9,0.465,0.9h3.287c0.312,0,0.604-0.152,0.781-0.408L84.564,13.97C84.826,13.592,84.557,13.075,84.096,13.075z
	"/>
<path fill="#179BD7" d="M94.992,6.749h-6.84c-0.467,0-0.865,0.34-0.938,0.802l-2.766,17.537c-0.055,0.346,0.213,0.658,0.562,0.658
	h3.51c0.326,0,0.605-0.238,0.656-0.562l0.785-4.971c0.072-0.463,0.471-0.803,0.938-0.803h2.164c4.506,0,7.105-2.18,7.785-6.5
	c0.307-1.89,0.012-3.375-0.873-4.415C99.004,7.353,97.281,6.749,94.992,6.749z M95.781,13.154c-0.373,2.454-2.248,2.454-4.062,2.454
	h-1.031l0.725-4.583c0.043-0.277,0.281-0.481,0.562-0.481h0.473c1.234,0,2.4,0,3.002,0.704
	C95.809,11.668,95.918,12.292,95.781,13.154z"/>
<path fill="#179BD7" d="M115.434,13.075h-3.273c-0.281,0-0.52,0.204-0.562,0.481l-0.145,0.916l-0.23-0.332
	c-0.709-1.029-2.289-1.373-3.867-1.373c-3.619,0-6.709,2.741-7.311,6.586c-0.312,1.918,0.131,3.752,1.219,5.031
	c1,1.176,2.426,1.666,4.125,1.666c2.916,0,4.533-1.875,4.533-1.875l-0.146,0.91c-0.055,0.348,0.213,0.66,0.564,0.66h2.949
	c0.467,0,0.865-0.34,0.938-0.803l1.771-11.209C116.053,13.388,115.785,13.075,115.434,13.075z M110.869,19.449
	c-0.314,1.871-1.801,3.127-3.695,3.127c-0.949,0-1.711-0.305-2.199-0.883c-0.484-0.574-0.666-1.391-0.514-2.301
	c0.297-1.855,1.805-3.152,3.67-3.152c0.93,0,1.686,0.309,2.184,0.892C110.816,17.721,111.014,18.543,110.869,19.449z"/>
<path fill="#179BD7" d="M119.295,7.23l-2.807,17.858c-0.055,0.346,0.213,0.658,0.562,0.658h2.822c0.469,0,0.867-0.34,0.939-0.803
	l2.768-17.536c0.055-0.346-0.213-0.659-0.562-0.659h-3.16C119.578,6.749,119.338,6.953,119.295,7.23z"/>
<path fill="#253B80" d="M7.266,29.154l0.523-3.322l-1.165-0.027H1.061L4.927,1.292C4.939,1.218,4.978,1.149,5.035,1.1
	c0.057-0.049,0.13-0.076,0.206-0.076h9.38c3.114,0,5.263,0.648,6.385,1.927c0.526,0.6,0.861,1.227,1.023,1.917
	c0.17,0.724,0.173,1.589,0.007,2.644l-0.012,0.077v0.676l0.526,0.298c0.443,0.235,0.795,0.504,1.065,0.812
	c0.45,0.513,0.741,1.165,0.864,1.938c0.127,0.795,0.085,1.741-0.123,2.812c-0.24,1.232-0.628,2.305-1.152,3.183
	c-0.482,0.809-1.096,1.48-1.825,2c-0.696,0.494-1.523,0.869-2.458,1.109c-0.906,0.236-1.939,0.355-3.072,0.355h-0.73
	c-0.522,0-1.029,0.188-1.427,0.525c-0.399,0.344-0.663,0.814-0.744,1.328l-0.055,0.299l-0.924,5.855l-0.042,0.215
	c-0.011,0.068-0.03,0.102-0.058,0.125c-0.025,0.021-0.061,0.035-0.096,0.035H7.266z"/>
<path fill="#179BD7" d="M23.048,7.667L23.048,7.667L23.048,7.667c-0.028,0.179-0.06,0.362-0.096,0.55
	c-1.237,6.351-5.469,8.545-10.874,8.545H9.326c-0.661,0-1.218,0.48-1.321,1.132l0,0l0,0L6.596,26.83l-0.399,2.533
	c-0.067,0.428,0.263,0.814,0.695,0.814h4.881c0.578,0,1.069-0.42,1.16-0.99l0.048-0.248l0.919-5.832l0.059-0.32
	c0.09-0.572,0.582-0.992,1.16-0.992h0.73c4.729,0,8.431-1.92,9.513-7.476c0.452-2.321,0.218-4.259-0.978-5.622
	C24.022,8.286,23.573,7.945,23.048,7.667z"/>
<path fill="#222D65" d="M21.754,7.151c-0.189-0.055-0.384-0.105-0.584-0.15c-0.201-0.044-0.407-0.083-0.619-0.117
	c-0.742-0.12-1.555-0.177-2.426-0.177h-7.352c-0.181,0-0.353,0.041-0.507,0.115C9.927,6.985,9.675,7.306,9.614,7.699L8.05,17.605
	l-0.045,0.289c0.103-0.652,0.66-1.132,1.321-1.132h2.752c5.405,0,9.637-2.195,10.874-8.545c0.037-0.188,0.068-0.371,0.096-0.55
	c-0.313-0.166-0.652-0.308-1.017-0.429C21.941,7.208,21.848,7.179,21.754,7.151z"/>
<path fill="#253B80" d="M9.614,7.699c0.061-0.393,0.313-0.714,0.652-0.876c0.155-0.074,0.326-0.115,0.507-0.115h7.352
	c0.871,0,1.684,0.057,2.426,0.177c0.212,0.034,0.418,0.073,0.619,0.117c0.2,0.045,0.395,0.095,0.584,0.15
	c0.094,0.028,0.187,0.057,0.278,0.086c0.365,0.121,0.704,0.264,1.017,0.429c0.368-2.347-0.003-3.945-1.272-5.392
	C20.378,0.682,17.853,0,14.622,0h-9.38c-0.66,0-1.223,0.48-1.325,1.133L0.01,25.898c-0.077,0.49,0.301,0.932,0.795,0.932h5.791
	l1.454-9.225L9.614,7.699z"/>
</svg>
</span></label>
        </div>
        <?php
        }   
    }
    
    public function paypal_form($event){
        $settings_service= EventM_Factory::get_service('EventM_Setting_Service');
        $gs= $settings_service->load_model_from_db(); 
        if(empty($gs->paypal_processor)){
            return;
        }
        $url = !empty($gs->payment_test_mode) ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';
        $notify_url = add_query_arg(array('action' => 'event_magic_pp_ipn'),admin_url('admin-ajax.php'));
        /* $return_url = add_query_arg(array('em_bookings' => "{{order_ids}}"),get_permalink($gs->profile_page)); */
        if(!is_user_logged_in()){
            $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
            $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
            $gs = $gs_service->load_model_from_db();
            if(!empty($showBookNowForGuestUsers)){
                $return_url = add_query_arg(array('id' => "{{order_ids}}", 'is_guest' => 1 ),get_permalink(em_global_settings('booking_details_page')));
            }
        }else{
            $return_url = add_query_arg(array('em_bookings' => "{{order_ids}}"),get_permalink($gs->profile_page));
        }
        $user= wp_get_current_user();
        ?>
        <form ng-show="data.pp.show_paypal" method="post" name="emPaypalForm" action= <?php echo $url; ?>>
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?php echo $gs->paypal_email; ?>">
            <input type="hidden" name="item_name" value="<?php echo $event->name; ?>">
            <input type="hidden" name="item_number" value="{{item_numbers}}">
            <input type="hidden" name="amount" value="{{price}}">
            <input type="hidden" name="first_name" value="<?php echo $user->display_name; ?>">
            <input type="hidden" name="email" value="<?php echo $user->user_email; ?>">
            <input type="hidden" name="custom" value="{{order_ids}}">
            <INPUT TYPE="hidden" NAME="return" value="<?php echo $return_url; ?>">
            <INPUT TYPE="hidden" NAME="currency_code" value="<?php echo $gs->currency; ?>">
            <input type="hidden" name="bn" value="CMSHelp_SP">
            <INPUT TYPE="hidden" NAME="notify_url" value="<?php echo $notify_url; ?>">
            <input type="hidden" name="coupon_code" value="{{couponCode}}" />
            <input type="hidden" name="coupon_amount" value="{{order.coupon_amount}}" />
            <input type="hidden" name="coupon_type" value="{{order.coupon_type}}" />
            <input type="hidden" name="coupon_discount" value="{{order.coupon_discount}}" />
        </form>
    <?php    
    }

    public function update_mp_booking_info() {
        $booking_service= EventM_Factory::get_service('EventM_Booking_Service');
        $settings_service= EventM_Factory::get_service('EventM_Setting_Service');
        $gs= $settings_service->load_model_from_db();
        $orderData = file_get_contents('php://input');
        $data = json_decode($orderData);
        $booking_id = (int)$data->purchase_units[0]->custom_id;
       
        $data->payment_gateway = 'paypal';
        $data->payment_status = strtolower($data->status);
        $data->total_amount =   $data->purchase_units[0]->amount->value;
        $booking = $booking_service->load_model_from_db($booking_id);
        if (empty($booking->id)){ return false; }

        if( $data->payment_status == 'refunded' ){
            if (isset($booking->payment_log['refund_log'])){
                $booking->payment_log['refund_log'][] = (array)$data;
            }  
            else{
                 $booking->payment_log['refund_log'] = array((array)$data);
            }
            em_update_post_meta($booking->id, 'payment_log',$booking->payment_log);

        }else{
            $booking_service->confirm_booking( $booking_id, (array)$data );
            /* $return_url = add_query_arg(array('em_bookings' => $booking_id),get_permalink($gs->profile_page)); */
            if(!is_user_logged_in()){
                $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
                $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
                $gs = $gs_service->load_model_from_db();
                if(!empty($showBookNowForGuestUsers)){
                    $return_url = add_query_arg(array('id' => $booking_id, 'is_guest' => 1 ),get_permalink(em_global_settings('booking_details_page')));
                }
            }else{
                $return_url = add_query_arg(array('em_bookings' => $booking_id),get_permalink($gs->profile_page));
            }
            $response = array( 'status'=>'success','url'=> $return_url);
            wp_send_json_success($response);
        }
    }
}
EventM_Paypal_Service::get_instance();