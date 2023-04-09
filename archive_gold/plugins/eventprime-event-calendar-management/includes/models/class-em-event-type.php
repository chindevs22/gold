<?php

class EventM_Event_Type_Model extends EventM_Array_Model
{
    public $id;
    public $name;
    public $color;
    public $type_text_color;
    public $age_group = 'all';
    public $image_id;
    public $description;
    public $custom_group;
    public $count = 0;
    public $created_by;
    public $last_updated_by;
    public $is_featured = 0;
}
