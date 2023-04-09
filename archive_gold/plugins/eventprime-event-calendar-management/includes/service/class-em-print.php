<?php
if(!defined('ABSPATH')) exit;
class EventM_Print { 
    public static function front_ticket($booking,$seat_no="")
    {   
        $booking_service= EventM_Factory::get_service('EventM_Booking_Service');
        $event_service= EventM_Factory::get_service('EventM_Service');
        $event_type_service= EventM_Factory::get_service('EventTypeM_Service');
        $tpl_location= plugin_dir_path( __DIR__ ).'print/ticket.php';
        $data= array();
        $event= $event_service->load_model_from_db($booking->event);
        $event_type= $event_type_service->load_model_from_db($event->event_type);
        $interval= em_datetime_diff($event->start_date,$event->end_date);
        if(em_compare_event_dates($event->id)){
            $data['date_time'] =  date(get_option('date_format').' '.get_option('time_format'),$event->start_date);
            $hours=''; $minutes='';
            if($interval->h)
                $hours= '<span class="em_time">'.$interval->h.'</span> hours ';
            if($interval->i)
                $minutes= '<span class="em_time"> '.$interval->i.'</span> minutes';
            $data['duration'] =  '<span> Duration: '.$hours.$minutes.'</span> ';
        }
        else
        {
             $data['date_time'] =  date(get_option('date_format').' '.get_option('time_format'),$event->end_date);
             $data['duration'] = 'Duration: '.$interval->days.' day(s) '; 
        }
        // Venue information
        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
        $venue_id = $event->venue;
        $venue= $venue_service->load_model_from_db($venue_id);
        $data['booking_id'] = $booking->id;
        $data['event_title']= $event->name;
        $data['organiser'] = $venue->seating_organizer;
        $data['age_group']='';
        $data['audience_note']='';
        if(!empty($event_type->id)){
            if($event_type->age_group == "parental_guidance"){
                $data['age_group'] = __('All ages but parental guidance','eventprime-event-calendar-management');
            }
            else if($event_type->age_group == 'custom_group'){
                $data['age_group']= !empty($event_type->custom_group) ? str_replace('-',' to ',$event_type->custom_group) : __('Not Specified. Contact organizer for details.','eventprime-event-calendar-management');
            }
            else 
            {
                    $data['age_group'] =  $event_type->age_group;
            }
            $data['audience_note']= $event_type->description;
        }

        $thumbnail=get_the_post_thumbnail_url($event->id,'post-thumbnail');
        $data['thumbnail']= empty($thumbnail) ? '' : $thumbnail;        
        
        if(!empty($venue->id)){
            $data['venue_address']= $venue->address;
            $data['venue_name']=$venue->name;
        }
        
        $currency_symbol= $booking->order_info['currency']; 
        $ticket_price = $booking_service->get_price_for_print($booking->id, $seat_no);
        $data['price_option_name'] = '';
        if(isset($booking->payment_log['multi_price_option_data']) && !empty($booking->payment_log['multi_price_option_data']->name)){
            $data['price_option_name'] = '(' . $booking->payment_log['multi_price_option_data']->name . ')';
        }
        if (empty($ticket_price)){
            $data['pay_status'] = "";
            $data['ticket_price'] = __('Free','eventprime-event-calendar-management');
            $data['currency_symbol'] = "";
            $data['ticket_price_dec'] = "";
        }
        else
        {
            $data['pay_status'] = __('PAID','eventprime-event-calendar-management');
            $data['currency_symbol'] = $currency_symbol;
            $whole = $decimal = '';
            if(strpos($ticket_price, '.') == true) {
                list($whole, $decimal) = explode('.', $ticket_price);
            }
            else{
                $whole = $ticket_price;
            }
            $data['ticket_price'] = $whole;
            $data['ticket_price_dec'] = (empty($decimal)) ? '.00' : '.' . $decimal;    
        }
        
        if($venue->type=='seats') 
        {   $data['seat_type'] = __('Seat No.','eventprime-event-calendar-management');
            $data['seat_no']= $seat_no;
        }
        else
        {   
            $data['seat_type'] = __('No. of Person(s)','eventprime-event-calendar-management');
            $data['seat_no']= (int) $booking->order_info['quantity'];
        }
        
        $data= apply_filters('event_magic_data_before_print',$data,$event,$booking);
        if(file_exists($tpl_location))
        {
            ob_start();
            include($tpl_location);
            $html= ob_get_clean();
        }
       
        return $html;
    }
    
    public static function print_html($html,$args=array()){ 
        if (!class_exists('TCPDF'))
            require_once plugin_dir_path(dirname(__FILE__)) . 'lib/tcpdf_min/tcpdf.php';

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        if(!empty($args['title'])){
            $pdf->SetTitle($args['title']);
        }
        
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $font=  isset($args['font']) ? $args['font'] : 'courier';
        $pdf->SetFont($font, '', 10);

        // add a page
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $name= isset($args['name']) ? $args['name'] : '';
        $pdf->Output("booking'-'$name.pdf", 'D');
    }
    
    public static function details($booking) {
        $booking_service= EventM_Factory::get_service('EventM_Booking_Service');
        $html = "";
        $header_data = array('logo' => null, 'header_text' => null, 'title' => '');
        $header_data = wp_parse_args($header_data, array('logo' => null, 'header_text' => null, 'title' => ''));
        if (!class_exists('TCPDF'))
            require_once plugin_dir_path(dirname(__FILE__)) . 'lib/tcpdf_min/tcpdf.php';

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('EventPrime');
        $pdf->SetTitle('Event Booking');
        $pdf->SetSubject('PDF for Submission');
        $pdf->SetKeywords('submission,pdf,print');
        $pdf->SetHeaderData($header_data['logo'], 10, $header_data['title'], $header_data['header_text']);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $pdf->SetFont('courier', '', 10);

        // add a page
        $pdf->AddPage();

        $data = array();
        $event_service= EventM_Factory::get_service('EventM_Service');
        $event_type_service = EventM_Factory::get_service('EventTypeM_Service');
        $event = $event_service->load_model_from_db($booking->event); 
        if (empty($event->id))
            die(__('Event does not exist in database.','eventprime-event-calendar-management'));
        
        $data['booking_id'] = $booking->id;
        $data['event_title'] = $event->name;
        $status = EventM_Constants::$status[$booking->status];
        $data['status']= $status;
        $data['venue_name'] = $data['venue_address'] = '';
        if(!empty($event->venue)){
            $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
            $venue= $venue_service->load_model_from_db($event->venue);
            if(!empty($venue->id)){
                $data['venue_name'] = $venue->name;
                $data['venue_address'] = empty($venue->address) ? __('Venue address is not defined.','eventprime-event-calendar-management') : $venue->address;
            }
        }
        $order_info = $booking->order_info;
        $item_price = $order_info['item_price'];
        $data['item_price_text'] = 'Price per Seat';
        if(isset($order_info['order_item_data'])){
            $data['item_price_text'] = 'Sub Total';
            if(isset($order_info['seat_sequences']) && !empty($order_info['seat_sequences'])){
                foreach($order_info['order_item_data'] as $order_item_data){
                    $item_price += $order_item_data->sub_total;
                }
            } else{
                foreach($order_info['order_item_data'] as $order_item_data){
                    $item_price = $order_item_data->price;
                    break;
                }
            }
        }
        $data['item_price'] = empty($item_price) ? __('Free','eventprime-event-calendar-management') : em_price_with_position($item_price, $order_info['currency']);
        $data['final_price'] = $booking_service->get_final_price($booking->id);
        $data['quantity'] = (int) $order_info['quantity'];
        $data['discount'] = $order_info['discount'];
        $currency_symbol = $order_info['currency'];

        if (!empty($order_info['seat_sequences'])) {
            $data['seat_sequences'] = $seat_sequences = implode(',', $order_info['seat_sequences']);
        } else {
            $data['seat_no'] = absint($order_info['quantity']);
        }
        if(isset($order_info['is_custom_booking_field']) && !empty($order_info['is_custom_booking_field'])){
            $attendee_names = $booking->attendee_names;
            foreach($booking->attendee_names as $attendees){
                $attData = array();
                $attendee_name_html .= '<tr>';
                    $attendee_name_html .= '<td style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">';
                        foreach($attendees as $label => $value){
                           $attendee_name_html .= '<span>'. $label .' : '. $value .' </span><br/>';
                        }
                    $attendee_name_html .= '</td>';
                $attendee_name_html .= '</tr>';
            }
            $data['attendee_name'] = $attendee_name_html;
        }
        else{
            $data['attendee_name'] = implode(',', $booking->attendee_names);
        }
        $data['event_type_name'] = '';
        if(!empty($event->event_type)){
            $event_type = $event_type_service->load_model_from_db($event->event_type);
            if(!empty($event_type)){
                $data['event_type_name'] = $event_type->name;
            }
        }
        if (isset($data['seat_sequences'])) {
            ob_start();
            $tpl_location = plugin_dir_path(__DIR__) . 'print/booking_detail_seats.html';
            $my_var = ob_get_clean();
        } else {
            ob_start();
            $tpl_location = plugin_dir_path(__DIR__) . 'print/booking_detail_standing.html';
            $my_var = ob_get_clean();
        }
        
        if (file_exists($tpl_location))
            $html = file_get_contents($tpl_location);
        
        preg_match_all("/{{+(.*?)}}/", $html, $matches);
        if (isset($matches[1]) && count($matches[1]) > 0) {
            //Filling required data in template file
            foreach ($matches[1] as $index => $val) {
                // Check if value exists in data
                if (isset($data[$val])) {
                    $html = str_replace($matches[0][$index], $data[$val], $html);
                }
                else{
                    $html = str_replace($matches[0][$index],'', $html);
                }
            }
        }
        // check for One-Time event Fee
        if ($order_info['fixed_event_price'] > 0) {
            $html .= '<tr><th style=" font-weight: bold; text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>One-Time event Fee:<br></th><td style=" text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>' . em_price_with_position($order_info['fixed_event_price'], $currency_symbol) . '<br></td></tr>';
        }
        if ($data['discount'] > 0) {
            $html .= '<tr><th style=" font-weight: bold; text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>Discount:<br></th><td style=" text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>' . em_price_with_position($data['discount'], $currency_symbol) . '<br></td></tr>';
        }
        if ($order_info['coupon_discount'] > 0) {
            $html .= '<tr><th style=" font-weight: bold; text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>Coupon Code Discount:<br></th><td style=" text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>' . em_price_with_position($order_info['coupon_discount'], $currency_symbol) . '<br></td></tr>';
        }
        // check if early bird discount applied
        if ($order_info['applied_ebd'] > 0) {
            $html .= '<tr><th style=" font-weight: bold; text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>Automatic Discount:<br></th><td style=" text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>' . em_price_with_position($order_info['ebd_discount_amount'], $currency_symbol) . '<br></td></tr>';
        }
        $html .= '<tr><th style=" font-weight: bold; text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>Final Price<br></th><td style=" text-transform:capitalize; vertical-align:middle;border-bottom:1px solid #eceeef;"><br><br>' . em_price_with_position($data['final_price'], $currency_symbol) . '<br></td></tr>';
        $html .= '</table></body></html>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output("Booking'-'$booking->id.pdf", 'D');
    }

    public static function save_ticket_html($html, $args=array()){ 
        if (!class_exists('TCPDF'))
            require_once plugin_dir_path(dirname(__FILE__)) . 'lib/tcpdf_min/tcpdf.php';

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        if(!empty($args['title'])){
            $pdf->SetTitle($args['title']);
        }
        
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $font=  isset($args['font']) ? $args['font'] : 'courier';
        $pdf->SetFont($font, '', 10);

        // add a page
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $name = isset($args['name']) ? $args['name'] : '';
        $path = plugin_dir_path( __DIR__ ) . 'tickets-pdf/';
        if ( !file_exists( $path ) ) {
            mkdir($path, 0777, true);
        }
        $pdf_name = plugin_dir_path( __DIR__ ) . 'tickets-pdf/' . $name . '.pdf';
        $pdf->Output($pdf_name, 'F');
        return $pdf_name;
    }
}