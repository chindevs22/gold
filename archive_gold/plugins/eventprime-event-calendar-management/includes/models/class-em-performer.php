<?php

class EventM_Performer_Model extends EventM_Array_Model
{
    public $id;
    public $name;
    public $slug;
    public $description;
    public $type;
    public $role;
    public $display_front = 'true';
    public $status= 'publish';
    public $feature_image_id;
    public $created_by;
    public $last_updated_by;
    public $performer_phones = array();
    public $performer_emails = array();
    public $performer_websites = array();
    public $is_featured = 0;
    public $social_links;
}
