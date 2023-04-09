<?php

class EventM_Event_Model extends EventM_Array_Model
{
    public $name;
    public $id;
    public $status = "publish";
    public $slug;
    public $event_type = 0;
    public $description='';
    public $venue = 0;
    public $performer = 0;
    public $start_date = "";
    public $end_date = "";
    public $seating_capacity=0;
    public $organizer_name="";
    public $organizer_phones=array();
    public $organizer_emails=array();
    public $organizer_websites=array();
    public $hide_event_from_calendar = 0;
    public $hide_event_from_events = 0;
    public $ticket_template = 0;
    public $max_tickets_per_person;
    public $allow_cancellations = 0;
    public $audience_notice;
    public $allow_discount = 0;
    public $discount_no_tickets = 2;
    public $discount_per;
    public $facebook_page;
    public $cover_image_id;
    public $sponser_image_ids = array();
    public $gallery_image_ids = array();
    public $ticket_price=0;
    public $hide_organizer = 0;
    public $hide_booking_status = 0;
    public $last_booking_date = "";
    public $start_booking_date = "";
    public $rm_form = 0;
    public $seats = array();
    public $booked_seats = 0;
    public $match = 0;
    public $is_daily_event = 0;
    public $enable_performer = 0;
    public $enable_booking = 0;
    public $en_ticket = 0;
    public $all_day=0;
    public $custom_link_enabled=0;
    public $custom_link="";
    public $user_submitted=0;
    public $user;
    public $enable_attendees=1;
    public $show_attendees=0;
    public $is_featured=0;
    public $allow_comments = 1;
    public $event_text_color = '';
    public $fixed_event_price=0;
    public $show_fixed_event_price = 0;
    public $custom_meta = array();
    public $enable_custom_booking_confirmation_email;
    public $custom_booking_confirmation_email_subject;
    public $custom_booking_confirmation_email_body;
    public $standing_capacity = 0;
    public $is_zoom_meetings = 0;
    public $meeting_data = array();
    public $organizer = 0;
    public $show_tier_name_on_booking = 0;
    public $hide_end_date = 0;
    public $is_sms_enabled = 1;
    
    public function __construct(){
        $dynamic_properties = apply_filters('ep_event_model_fields',array());
        foreach($dynamic_properties as $key=>$val){
            $this->$key = $val;
        }
    }
}
