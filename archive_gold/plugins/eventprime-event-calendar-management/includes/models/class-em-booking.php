<?php
class EventM_Booking_Model {
    public $id;
    public $event;
    public $date;
    public $order_info = array();
    public $notes = array();
    public $payment_log = array();
    public $user;
    public $name;
    public $booking_tmp_status = 1;
    public $status ='pending';
    public $booked_seats = '';
    public $attendee_names = array();
    public $multi_price_id = '';
}
