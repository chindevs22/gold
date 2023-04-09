<?php

class EventM_Venue_Model extends EventM_Array_Model
{
    public $id=0;
    public $name;
    public $slug;
    public $description;
    public $seats = array();
    public $facebook_page;
    public $address = '';
    public $type = '';
    public $seating_capacity;
    public $seating_organizer;
    public $established;
    public $gallery_images = array();
    public $lng;
    public $lat;
    public $zoom_level = 0;
    public $display_address_on_frontend = 0;
    public $seat_color;
    public $booked_seat_color;
    public $reserved_seat_color;
    public $selected_seat_color;
    public $standing_capacity = 999;
    public $created_by;
    public $last_updated_by;
    public $is_featured = 0;
}
