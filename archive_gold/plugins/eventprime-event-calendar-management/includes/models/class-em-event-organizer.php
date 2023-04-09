<?php

class EventM_Event_Organizer_Model extends EventM_Array_Model
{
    public $id;
    public $name;
    public $organizer_phones = array();
    public $organizer_emails = array();
    public $organizer_websites = array();
    public $image_id;
    public $description;
    public $created_by;
    public $last_updated_by;
    public $is_featured = 0;
    public $social_links;
}
