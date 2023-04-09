<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class EventM_Event_DAO extends EventM_Post_Dao
{
    public function __construct() {
        parent::__construct(EM_EVENT_POST_TYPE);
    }
    
    public function create($event,$id=0)
    {  
        if ($id>0) 
        {
            $event['ID']= $id;
            $id = wp_update_post($event);
        }
        else 
            $id = wp_insert_post($event);
        
        return $id;
    }
    
     public function save($model)
     {  
        return parent::save($model);
     }
     
    /**
     * 
     * @param Event ID $id
     * @param Type ID $type
     */
    public function set_type($id,$type)
    {
        wp_set_object_terms($id, $type, EM_EVENT_TYPE_TAX, false);
    }
    
    /**
     * 
     * @param Event ID $id
     * @param Venue ID $type
     */
    public function set_venue($id,$venue)     
    {  
        wp_set_object_terms($id, $venue, EM_EVENT_VENUE_TAX, false);
    }
    
    public function remove_venue($id,$tax){
        wp_remove_object_terms($id,$tax,EM_EVENT_VENUE_TAX );
    }
    
    public function set_performer($id,$performer)    
    {  
        update_post_meta($id,EM_PERFORMER_POST_TYPE, $performer,  false);
       
    }
   
    
    public function set_thumbnail($id,$img_id)
    {
        set_post_thumbnail($id, $img_id);
    }

    // Get all the events  
    public function get_events($args = array()) {
        $defaults = array(
            'post_status' => 'any',
            'meta_key' => 'em_start_date',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'post_type' => EM_EVENT_POST_TYPE,
            'numberposts' => -1,
        );
        $args = wp_parse_args($args, $defaults);
        $posts = get_posts($args);
        return $posts;
    }
    
     public function get_past_events(){
         $filter = array(
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status'=> 'publish',
            'meta_query' => array(// WordPress has all the results, now, return only the events after today's date
                'relation' => 'AND',
                array(
                    'key' => em_append_meta_key('start_date'), // Check the start date field
                    'value' => current_time( 'timestamp' ), // Set today's date (note the similar format)
                    'compare' => '<=', // Return the ones less than today's date
                    'type' => 'NUMERIC,' // Let WordPress know we're working with numbers
                ),
                array(
                    'key' => em_append_meta_key('end_date'), // Check the start date field
                    'value' => current_time( 'timestamp' ), // Set today's date (note the similar format)
                    'compare' => '<=', // Return the ones greater than today's date
                    'type' => 'NUMERIC,' // Let WordPress know we're working with numbers
                )                
            ),
            'post_type' => $this->post_type);
         
         return $this->get_events($filter);
     }

    // Get upcoming events
    public function get_upcoming_events() { 
        $filter = array(
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => 'meta_value_num',
            'numberposts' => -1,
            'order' => 'ASC',
            'post_status' => 'publish',
            'meta_query' => array('relation' => 'AND',
                array(
                    array(
                        'key' => em_append_meta_key('hide_event_from_events'),
                        'value' => '1', //
                        'compare' => '!='
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => em_append_meta_key('start_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '>=',
                        ),
                        array(
                            'key' => em_append_meta_key('end_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '>=',
                        )),
                    array(
                        'key' => em_append_meta_key('hide_event_from_calendar'),
                        'value' => '1',
                        'compare' => '!='
                        )
                    )
            ),
            'post_type' => $this->post_type);

        return $this->get_events($filter);
    }
    
    
      public function get_upcoming_events_calendar() { 
        $filter = array(
            'meta_key'=> em_append_meta_key('start_date'),         
            'orderby' => 'meta_value_num',
            'numberposts'=>-1,
            'order' => 'ASC',  
            'post_status'=> 'publish',          
            'meta_query' => array('relation'=>'AND',// WordPress has all the results, now, return only the events after today's date
                array('relation'=>'AND',
                array(
              
                array(
                                        'key' => em_append_meta_key('hide_event_from_events'), 
                                        'value' => '1', //
                                        'compare' => '!='
                 ), 
                array(   
               'relation' => 'OR',
                array(
                    'key' => em_append_meta_key('start_date'), // Check the start date field
                    'value' => current_time( 'timestamp' ), // Set today's date (note the similar format)
                    'compare' => '>=', // Return the ones greater than today's date
                     // Let WordPress know we're working with numbers
                ),
                array(
                    'key' => em_append_meta_key('end_date'), // Check the start date field
                    'value' => current_time( 'timestamp' ), // Set today's date (note the similar format)
                    'compare' => '>=', // Return the ones greater than today's date
                     // Let WordPress know we're working with numbers
                )),
                array(
                'key'     => em_append_meta_key('hide_event_from_calendar'),
		'value'   => '1',
                'compare' => '!='            
               )),
                                  array(    
                                 'relation' => 'OR',
                                    array(
                                   'key' => em_append_meta_key('parent_event'), 
                                   'value' => 0, 
                                   'compare' => '=', 
                                   'type' => 'NUMERIC,'
                                    ),
                                    array(
                                        'key' => em_append_meta_key('parent_event'), 
                                        'compare' => 'NOT EXISTS',  
                                   )
                                 )
                    
                    )),   
            'post_type' => $this->post_type);
        
       
        return $this->get_events($filter);
        
    }
    
    public function get_venue($event_id)
    {
        $terms = wp_get_post_terms($event_id, EM_VENUE_TYPE_TAX);
        foreach($terms as $term){
            if($term->taxonomy==EM_VENUE_TYPE_TAX){
                return $term;
            }
        }
        return null;
    }
    
    public function get_type($event_id)
    {
        $terms = wp_get_post_terms($event_id, EM_EVENT_TYPE_TAX);
        foreach($terms as $term){
            if($term->taxonomy==EM_EVENT_TYPE_TAX){
                return $term;
            }
        }
        return null;
    }
    
    public function available_seats($event_id)
    {
        $sum= $this->booked_seats($event_id);
        $capacity= absint(em_get_post_meta($event_id, 'seating_capacity', true));
        if(!empty($capacity))
        {   
            if( $capacity>0)                  
             return $capacity-$sum;
        }
        return 99999999;  
    }
    
    
    public function booked_seats($event_id) {  
        //return em_get_post_meta($event_id, 'booked_seats', true, true);
        
        $args = array(
            'numberposts' => -1,
            'post_status'=> 'completed',
            'post_type'=> EM_BOOKING_POST_TYPE,
            'meta_key' => em_append_meta_key('event'),
            'meta_value' => $event_id,
        );
        
        $booking_posts = get_posts($args);
        $booked_seats = 0;
        foreach ($booking_posts as $post) {
            $order_info = em_get_post_meta($post->ID, 'order_info');
            if(!empty($order_info) && isset($order_info[0]['quantity'])){
                $booked_seats = $booked_seats + $order_info[0]['quantity'];
            }
        }
        return $booked_seats;
    }
    
    public function get($id)
    {
        $post= empty($id) ? 0 : get_post($id);
          if(empty($post))
              return new EventM_Event_Model(0);
        
        $event= new EventM_Event_Model($id);
        $meta= $this->get_meta($id,'',true);
        if(is_array($meta)){ 
            foreach($meta as $key=>$val) {
                $key= str_replace('em_','',$key);
                if (property_exists($event, $key)) {
                    $event->{$key}= maybe_unserialize($val[0]);
                } 
            }
        }
        
        $event->id= $post->ID;
        $event->name=$post->post_title;
        $event->slug=$post->post_name;
        $event->description=$post->post_content;
        $event->status=$post->post_status;
        $event->user=$post->post_author;
        
        $event = apply_filters('eventprime_load_into_event_model', $event, $post);
        return $event;
    }
    
    public function get_events_by_venue($venue_id){
        $filter = array(
            'post_status' => 'any',
            'posts_per_page' => '-1',
            'tax_query' => array(array('taxonomy' => 'em_venue', 'field' => 'term_id', 'terms' => $venue_id)),
            'post_type' => $this->post_type);

        $posts= $this->get_events($filter);
        $events= array();
        foreach($posts as $post){
            $events[]= $this->get($post->ID);
        }
        return $events;
    }
    
    public function get_upcoming_events_by_venue($venue_id) {
        $filter = array(
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'post_status' => 'publish',
            'posts_per_page' => '-1', // Let's show them all.  
            'meta_query' => array(// WordPress has all the results, now, return only the events after today's date
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'em_venue',
                        'value' => $venue_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => em_append_meta_key('hide_event_from_events'),
                        'value' => '1', //
                        'compare' => '!='
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => em_append_meta_key('parent_event'),
                            'value' => 0,
                            'compare' => '=',
                            'type' => 'NUMERIC'
                        ),
                        array(
                            'key' => em_append_meta_key('parent_event'),
                            'compare' => 'NOT EXISTS',
                        )
                    )
                )
            ),
            'post_type' => $this->post_type
        );

        $posts= $this->get_events($filter);
        $events= array();
        foreach($posts as $post){
            $events[]= $this->get($post->ID);
        }
        return $events;
    }
    
    public function event_count_by_type($type_id){
        return count($this->events_by_type($type_id));
    }
    
    public function events_by_type($type_id) {
        $args = array(
            'post_status' => 'any',
            'posts_per_page' => -1,
            'post_type' => EM_EVENT_POST_TYPE,
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'relation' => 'AND',
                    array(
                        'key' => EM_EVENT_TYPE_TAX,
                        'value' => $type_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => em_append_meta_key('hide_event_from_events'),
                        'value' => '1',
                        'compare' => '!='
                    )
                )
            )
        );
        $events = $this->get_events($args);
        return $events;
    }

    public function get_bookings_by_user_id_event_id($user_id, $event_id){ 
        $args = array(
           'orderby' => 'date',
           'order' => 'DESC',
           'post_status'=> 'completed',
           'meta_query' => array(
               'relation' => 'AND',
                array(
                   'key' => em_append_meta_key('user'), 
                   'value' => $user_id, 
                   'compare' => '=', 
                   'type' => 'NUMERIC,'
                ),
                array(
                   'key' => em_append_meta_key('event'), 
                   'value' => $event_id, 
                   'compare' => '=', 
                   'type' => 'NUMERIC,' 
                ),
            ),
            'post_type' => $this->post_type
        );
        return $this->get_all($args);
    }

    public function event_count_by_organizer( $organizer_id ) {
        return count( $this->events_by_organizer( $organizer_id ) );
    }
    public function events_by_organizer( $organizer_id ) {
        $args = array(
            'post_status' => 'any',
            'posts_per_page' => -1,
            'post_type' => EM_EVENT_POST_TYPE,
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key'     => 'em_organizer',
                    'value'   => sprintf(':%s;', $organizer_id),
                    'compare' => 'LIKE'
                )
            )
        );
        $events = get_posts($args);
        return $events;
    }

    // Get upcoming events for single performer
    public function get_upcoming_events_for_performer($performer_id, $args = array()) { 
        $filter = array(
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => em_append_meta_key('start_date'),
            'numberposts' => -1,
            'order' => 'ASC',
            'meta_query' => array('relation' => 'AND',
                array(
                    array(
                        'key' => em_append_meta_key('performer'),
                        'value' => sprintf(';i:%d;', $performer_id),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => em_append_meta_key('hide_event_from_events'),
                        'value' => '1', //
                        'compare' => '!='
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => em_append_meta_key('start_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '>=',
                        ),
                        array(
                            'key' => em_append_meta_key('end_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '<=',
                        )
                    ),
                    array(
                        'key' => em_append_meta_key('hide_event_from_calendar'),
                        'value' => '1',
                        'compare' => '!='
                    )
                )
            ),
            'post_type' => $this->post_type
        );

        $args = wp_parse_args($args, $filter);
        add_filter('posts_orderby', 'em_posts_order_by');
        $wp_query = new WP_Query($args);
        $wp_query->performer_id = $performer_id;
        remove_filter('posts_orderby', 'em_posts_order_by'); 
        return $wp_query;
    }

    public function get_upcoming_events_for_type( $type_id, $args ){
        $filter = array(
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => em_append_meta_key('start_date'),
            'numberposts' => -1,
            'order' => 'ASC',
            'meta_query' => array('relation' => 'AND',
                array(
                    array(
                        'key' => em_append_meta_key('event_type'),
                        'value' => $type_id,
                    ),
                    array(
                        'key' => em_append_meta_key('hide_event_from_events'),
                        'value' => '1', //
                        'compare' => '!='
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => em_append_meta_key('start_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '>=',
                        ),
                        array(
                            'key' => em_append_meta_key('end_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '<=',
                        )
                    ),
                    array(
                        'key' => em_append_meta_key('hide_event_from_calendar'),
                        'value' => '1',
                        'compare' => '!='
                    )
                )
            ),
            'post_type' => $this->post_type
        );

        $args = wp_parse_args( $args, $filter );
        add_filter('posts_orderby', 'em_posts_order_by');
        $wp_query = new WP_Query( $args );
        $wp_query->type_id = $type_id;
        remove_filter( 'posts_orderby', 'em_posts_order_by' ); 
        return $wp_query;
    }

    public function upcoming_events_for_type( $type_id, $args ){
        $results = $this->dao->get_upcoming_events_for_type( $type_id, $args );
        return $results;
    }
    
    public function print_upcoming_event_block_for_types( $upcoming_events, $event_args ) {
        $setting_service = EventM_Factory::get_service( 'EventM_Setting_Service' );
        $global_settings = $setting_service->load_model_from_db();
        $today = em_current_time_by_timezone();
        $currency_symbol = em_currency_symbol();
        $display_view = $event_args->event_style;
        $posts_per_page = $event_args->event_limit;
        $event_cols = $event_args->event_cols;
        $load_more = $event_args->load_more;
        $hide_past_events = $event_args->hide_past_events;
        $events = $upcoming_events->posts; 
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $recurring = 1; $column_class = ''; 
        ?>
        <div class="ep-event-type-events em_block dbfl">
            <div class="kf-row-heading">
                <span class="kf-row-title"><?php echo __( 'Upcoming Events', 'eventprime-event-calendar-management' ); ?>
                    <span class="em_events_count-wrap em_bg">
                        <?php /* echo '<span class="em_events_count_no em_color">' . count($events) . '</span>'; */ ?>
                    </span>
                </span>
            </div>
        <?php  
        if ( ! empty( $events ) ){
            $i = 1;
            if( $display_view == 'card' ){ ?>
                <div class="em_cards em_type_event_cards">
                    <!-- the loop -->
                    <?php foreach ( $events as $event ) :
                        $eventId = isset( $event->id ) ? $event->id : $event->ID;
                        $event= $this->load_model_from_db( $eventId );
                        if( empty( $recurring ) && isset( $event->parent ) && !empty( $event->parent ) ){
                            continue;
                        }
                        // check for booking allowed
                        $booking_allowed = 1;
                        if((isset( $event->parent ) && ! empty( $event->parent )) && ( isset( $event->enable_recurrence_automatic_booking ) && ! empty( $event->enable_recurrence_automatic_booking ) ) ){
                            // if event is recurring and parent has automatic booking enable than not allowed
                            $booking_allowed = 0;
                        }
                        if( absint( $event->custom_link_enabled ) == 1 ){
                            $event->url = $event->custom_link;
                            if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                $event->url = add_query_arg('event_id', $event->id, get_permalink($global_settings->profile_page));
                            }
                        }
                        else{
                            $event->url = add_query_arg('event', $event->id, get_page_link($global_settings->events_page));
                        }
                        ?>
                        <div class="<?php if(empty($section_id)){ echo 'em_card'; } else{ echo 'em_card_edt';}?> difl <?php if (em_is_event_expired($event->id)) echo 'emcard-expired'; ?> <?php echo (empty($event->enable_booking) && absint($event->custom_link_enabled) == 0) ? 'em_event_disabled' : ''; ?> <?php echo $column_class;?> col-md-<?php echo $event_cols;?>">
                            <div class="em_event_cover dbfl">
                                <?php 
                                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                if (!empty($event->cover_image_id)): ?>
                                    <?php 
                                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                    if(empty($thumbImage)){
                                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                        }
                                    }?>
                                    <a href="<?php echo $event->url; ?>">
                                        <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="dbfl em-card-description">
                                <div class="em_event_title em_block dbfl"  title="<?php  echo $event->name; ?>">
                                    <a href="<?php echo $event->url; ?>"><?php echo $event->name; ?></a>
                                    <?php if(is_user_logged_in()): ?>
                                        <?php do_action('event_magic_wishlist_link',$event); ?>
                                    <?php endif; ?>
                                </div>
                                <?php do_action('event_magic_popup_custom_data_before_details',$event);?>
                                <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                    if (em_compare_event_dates($event->id)){
                                        $day = date_i18n(get_option('date_format'),$event->start_date);
                                        $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                        $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                    } else {
                                        $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                        $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                    }
                                if($event->all_day):?>
                                    <div class="em_event_start difl em_color em_wrap">
                                        <?php echo date_i18n(get_option('date_format'),$event->start_date); ?><span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                    </div>
                                <?php elseif(!empty($day)): ?>
                                    <div class="em_event_start difl em_color em_wrap">
                                        <?php echo $day; ?>
                                    </div>
                                    <div class="em_event_start difl em_color em_wrap"><?php echo $start_time.'  to  '.$end_time; ?></div>
                                <?php else: ?>
                                    <div class="em_event_start difl em_color em_wrap">
                                        <?php echo $start_date; ?> -    
                                    </div>
                                    <div class="em_event_start difl em_color em_wrap">
                                        <?php echo $end_date; ?>  
                                    </div>
                                <?php endif; ?>
                                <?php 
                                if(!empty($event->venue)){  
                                    $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                    $venue= $venue_service->load_model_from_db($event->venue);
                                    if(!empty($venue->id)){  ?>
                                        <div class="em_event_address dbfl" title="<?php echo $venue->address; ?>"><?php echo $venue->address; ?></div>
                                        <?php 
                                    }
                                }?>
                                <?php if(!empty($event->description)) { ?>
                                    <div class="em_event_description dbfl"><?php echo $event->description; ?></div>
                                <?php } ?>

                                <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                    $sum = $this->booked_seats($event->id);
                                    $capacity = em_event_seating_capcity($event->id);?>  
                                    <div class="dbfl">
                                        <div class="kf-event-attr-value dbfl">  
                                            <?php if ($capacity > 0): ?>
                                                <div class="dbfl">
                                                    <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                                </div>
                                            <?php $width = ($sum / $capacity) * 100; ?>
                                                <div class="dbfl">
                                                    <div id="progressbar" class="em_progressbar dbfl">
                                                        <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                                    </div>
                                                </div>
                                            <?php
                                                else:
                                                    echo '<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                            ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>  
                                    <?php
                                endif;?>
                                <?php do_action('event_magic_popup_custom_data_before_footer',$event);?>
                            </div>
                            <div class="em-cards-footer dbfl">
                                <div class="em_event_price  difl">
                                    <?php 
                                    $ticket_price = $event->ticket_price;
                                    $ticket_price = apply_filters('event_magic_load_calender_ticket_price', $ticket_price, $event);
                                    // check if show one time event fees at front enable
                                    if($event->show_fixed_event_price){
                                        if($event->fixed_event_price > 0){
                                            $ticket_price = $event->fixed_event_price;
                                        }
                                    }
                                    if(!is_numeric($ticket_price)){
                                        echo $ticket_price;
                                    }
                                    else{
                                        echo !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                                    } ?>
                                </div>
                                <?php do_action('event_magic_card_view_after_price',$event); ?>
                                <div class="kf-tickets-button difr">
                                    <div class="em_event_attr_box em_eventpage_register difl">
                                        <?php 
                                        if(absint($event->custom_link_enabled) == 1):?>
                                            <div class="em_header_button em_event_custom_link kf-tickets">
                                                <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                    <?php 
                                                    if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                        echo em_global_settings_button_title('Login to View');
                                                    }
                                                    else{
                                                        echo em_global_settings_button_title('Click for Details');
                                                    }?>
                                                </a>
                                            </div>
                                        <?php
                                        elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                            <?php if($event->status=='expired'):?>
                                                <div class="em_header_button em_event_expired kf-tickets">
                                                    <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                </div>
                                            <?php elseif($current_ts>$event->last_booking_date): ?>
                                                <div class="em_header_button em_booking-closed kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                            <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                <div class="em_header_button em_not_started kf-tickets"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                            <?php else: ?>
                                                <?php 
                                                if(!empty($booking_allowed)):
                                                    if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                        <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                            <button class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking"><?php echo em_global_settings_button_title('Book Now'); ?></button>
                                                            <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                            <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                        </form>
                                                    <?php else: ?> 
                                                        <a class="em_header_button kf-tickets" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php elseif($event->status == 'publish'): ?>
                                            <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                <div class="em_event_attr_box em_eventpage_register difl">
                                                    <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('All Seats Booked'); ?></div>
                                                </div>
                                            <?php else:?>
                                                <div class="em_event_attr_box em_eventpage_register difl">
                                                    <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php do_action('event_magic_card_view_after_footer',$event); ?>
                        </div>
                    <?php $i++; endforeach; ?>
                    </div> 
                    <?php
                    if($upcoming_events->max_num_pages > 1 && $load_more == 1):?>
                    <?php $curr_page = $upcoming_events->query_vars['paged'];?>
                        <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_type_events_card_block('.ep-view-load-more','.ep-loading-view-btn','.em_type_event_cards')" data-curr_page="<?php echo $curr_page?>" data-type_id="<?php echo $upcoming_events->type_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>" data-show="<?php echo $posts_per_page;?>" data-cols = "<?php echo $event_cols;?>" data-recurring="<?php echo $recurring;?>">
                            <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                        </div>
                    <?php endif;?>
                 
            
            <?php }elseif($display_view == 'list'){ ?>
                
                    <div class="em_list_view ep-events-list-wrap em_cards" id="ms-container">
                        <div class="ep-wrap">
                            <div class="ep-event-list-standard ep-type-event-list-standard">
                                <!-- the loop -->
                                <?php foreach ($events as $event) :
                                    $eventId = isset($event->id) ? $event->id : $event->ID;
                                    $event = $this->load_model_from_db($eventId);
                                    if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                                        continue;
                                    }
                                    $month_id = date('Ym', $event->start_date);
                                    if(empty($last_month_id) || $last_month_id != $month_id){
                                        $last_month_id = $month_id;?>
                                        <div class="ep-month-divider"><span class="ep-listed-event-month"><?php echo date_i18n('F Y', $event->start_date); ?><span class="ep-listed-event-month-tag"></span></span></div><?php
                                    }
                                    // check for booking allowed
                                    $booking_allowed = 1;
                                    if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                                        // if event is recurring and parent has automatic booking enable than not allowed
                                        $booking_allowed = 0;
                                    }
                                    if(absint($event->custom_link_enabled) == 1){
                                        $event->url = $event->custom_link;
                                        if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                            $event->url = add_query_arg('event_id', $event->id, get_permalink($global_settings->profile_page));
                                        }
                                    }
                                    else{
                                        $event->url = add_query_arg('event', $event->id, get_page_link($global_settings->events_page));
                                    }
                                    ?>
                                    <div class="ep-event-article <?php if (em_is_event_expired($event->id)) echo 'emlist-expired'; ?> <?php echo empty($event->enable_booking) ? 'em_event_disabled' : ''; ?>">
                                        <div class="ep-topsec">
                                            <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                                                <div class="em_event_cover_list dbfl">
                                                    <?php 
                                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                                    if (!empty($event->cover_image_id)): ?>
                                                        <?php 
                                                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                                        if(empty($thumbImage)){
                                                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                                                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                                            }
                                                        }?>
                                                        <a href="<?php echo $event->url; ?>">
                                                            <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                                                <div class="ep-event-content">
                                                    <h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="<?php echo $event->id;?>" href="<?php echo $event->url; ?>" target="_self"><?php  echo $event->name; ?></a>
                                                    </h3>
                                                    <?php if(is_user_logged_in()): ?>
                                                        <?php do_action('event_magic_wishlist_link',$event); ?>
                                                    <?php endif; ?>
                                                    <?php if(!empty($event->description)) { ?>
                                                        <div class="ep-event-description"><?php echo $event->description; ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                                                <div class="ep-event-meta ep-color-before">
                                                    <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                                    if (em_compare_event_dates($event->id)){
                                                        $day = date_i18n(get_option('date_format'),$event->start_date);
                                                        $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                                        $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                                    } else {
                                                        $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                                        $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                                    }
                                                    if($event->all_day):?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> 
                                                            <div class="ep-list-event-date">
                                                                <?php echo date_i18n(get_option('date_format'),$event->start_date); ?>
                                                                <span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                                            </div>
                                                        </div>
                                                    <?php elseif(!empty($day)): ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $day; ?> - <?php echo $start_time.'  to  '.$end_time; ?></div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $start_date; ?> - <?php echo $end_date; ?> </div>   
                                                        </div>
                                                    <?php endif; ?> 
                                                    <?php 
                                                    if(!empty($event->venue)){
                                                        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                                        $venue= $venue_service->load_model_from_db($event->venue);
                                                        if(!empty($venue->id) && !empty($venue->address)){ ?>
                                                            <div class="em-list-view-venue-details" title="<?php echo $venue->address; ?>"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><div class="em-list-event-address"><span><?php echo $venue->address; ?></span></div>
                                                            </div><?php 
                                                        }
                                                    } ?> 

                                                    <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                                        $sum = $this->booked_seats($event->id);
                                                        $capacity = em_event_seating_capcity($event->id);?>  
                                                        <div class="ep-list-booking-status ep-event-attenders-main">
                                                            <div class="kf-event-attr-value dbfl"> 
                                                                <?php if ($capacity > 0): ?>
                                                                    <div class="dbfl">
                                                                        <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                                                    </div>
                                                                    <?php $width = ($sum / $capacity) * 100; ?>
                                                                    <div class="dbfl ">
                                                                        <div id="progressbar" class="em_progressbar dbfl">
                                                                            <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                else:
                                                                    if($sum > 0){
                                                                        echo '<div class="ep-event-attenders-wrap"><span class="material-icons em_color">person</span><span class="ep-event-attenders">' . $sum . ' </span>'.__('Attending','eventprime-event-calendar-management').'</div>';
                                                                    }?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif;?>
                                                    <?php do_action('event_magic_popup_custom_data_before_footer',$event);?>
                                                    <div class="ep-list-view-footer">
                                                        <div class="em_event_price difl">
                                                            <?php 
                                                            $ticket_price = $event->ticket_price;
                                                            // check if show one time event fees at front enable
                                                            if($event->show_fixed_event_price){
                                                                if($event->fixed_event_price > 0){
                                                                    $ticket_price = $event->fixed_event_price;
                                                                }
                                                            }
                                                            echo !empty($ticket_price) ? $currency_symbol.$ticket_price : ''; ?>
                                                        </div>
                                                        <?php do_action('event_magic_card_view_after_price',$event); ?>
                                                        <div class="kf-tickets-button difr">
                                                            <div class="em_event_attr_box em_eventpage_register difl">
                                                                <?php 
                                                                if(absint($event->custom_link_enabled) == 1):?>
                                                                    <div class="em_header_button em_event_custom_link kf-tickets">
                                                                        <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                                            <?php 
                                                                            if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                                                echo em_global_settings_button_title('Login to View');
                                                                            }
                                                                            else{
                                                                                echo em_global_settings_button_title('Click for Details');
                                                                            }?>
                                                                        </a>
                                                                    </div>
                                                                <?php
                                                                elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                                                    <?php if($event->status=='expired'):?>
                                                                        <div class="em_header_button em_event_expired kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts>$event->last_booking_date): ?>
                                                                        <div class="em_header_button em_booking-closed kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                                        <div class="em_header_button em_not_started kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings not started yet'); ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <?php 
                                                                        if(!empty($booking_allowed)):
                                                                            if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                                                <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                                                    <button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking">
                                                                                        <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                    </button>
                                                                                    <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                                                    <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                                                </form>
                                                                            <?php else: ?> 
                                                                                <a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>">
                                                                                    <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                </a>
                                                                            <?php endif; ?>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                <?php elseif($event->status == 'publish'):?>
                                                                    <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('All Seats Booked'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php else:?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php do_action('event_magic_card_view_after_footer',$event); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php  $i++; endforeach; ?>
                            </div>
                            <?php
                            if($upcoming_events->max_num_pages > 1 && $load_more == 1){
                                $curr_page = $upcoming_events->query_vars['paged'];?>
                                <div class="ep-masonry-load-more ep-masonry-load-more-wrap" onclick="em_load_more_type_events_list_block()" data-curr_page="<?php echo $curr_page?>" data-type_id="<?php echo $upcoming_events->type_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>"  data-show="<?php echo $posts_per_page;?>"  data-month_id="<?php echo $last_month_id;?>" data-recurring="<?php echo $recurring;?>"><div class="ep-load-more-button em_color"><?php _e('Load More');?></div></div><?php
                            }?>
                        </div>
                    </div>
                
            <?php }else { ?>
                <div class="ep-element ep-element-2247824 ep-widget ep-widget-ep-event-slider" data-id="2247824" data-element_type="widget" data-widget_type="ep-event-slider.default">
                    <div class="ep-widget-container">
                        <div class="ep-event-wrapper ep-event-slider swiper-container-initialized swiper-container-horizontal" data-count="3" data-autoplay="yes">
                            <div class="swiper-wrapper"  style="transform: translate3d(0px, 0px, 0px);">      
                                <?php foreach ($events as $event) {
                                        $eventId = isset($event->id) ? $event->id : $event->ID;
                                        $event_model = $this->load_model_from_db($eventId);
                                        if( empty( $recurring ) && isset( $event_model->parent ) && !empty( $event_model->parent ) ){
                                            continue;
                                        }
                                        // check for booking allowed
                                        $booking_allowed = 1;
                                        if((isset($event_model->parent) && !empty($event_model->parent)) && (isset($event_model->enable_recurrence_automatic_booking) && !empty($event_model->enable_recurrence_automatic_booking))){
                                            // if event is recurring and parent has automatic booking enable than not allowed
                                            $booking_allowed = 0;
                                        }
                                        if(absint($event_model->custom_link_enabled) == 1){
                                            $event_model->url = $event_model->custom_link;
                                            if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                $event_model->url = add_query_arg('event_id', $event_model->id, get_permalink($global_settings->profile_page));
                                            }
                                        }
                                        else{
                                            $event_model->url = add_query_arg('event', $event_model->id, get_page_link($global_settings->events_page));
                                        }
                                        ?>
                    
                                            <div class="swiper-slide  slide <?php echo empty($event_model->enable_booking) ? 'em_event_disabled' : ''; ?>" style="width: 353.333px; margin-right: 30px;" >
                                                <div class="ep-event-item">
                                                    <div class="ep-event-thumb">
                                                        <a href="<?php echo $event_model->url; ?>">
                                                        <?php 
                                                            $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                                            if (!empty($event_model->cover_image_id)): ?>
                                                                <?php 
                                                                $thumbImage = wp_get_attachment_image_src($event_model->cover_image_id, 'large')[0];
                                                                if(empty($thumbImage)){
                                                                    $thumbImage = get_the_post_thumbnail($event_model->id,'large');
                                                                    if(isset($event_model->parent) && !empty($event_model->parent) && empty($thumbImage)){
                                                                        $thumbImage = get_the_post_thumbnail($event_model->parent,'large');
                                                                    }
                                                                }?>
                                                                <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                                            <?php else: ?>
                                                                <img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" >
                                                            <?php endif; ?>
                                                        </a>
                                                        <!-- <div class="ep-event-category">
                                                            <span>event</span> <span>sports</span>
                                                        </div> -->
                                                    </div>
                                                    <!-- thumbnail start-->
                                                    <!-- content start-->
                                                    <div class="ep-event-content">
                                                         
                                                        <h3 class="ep-title ep-event-title"><a href="<?php echo $event_model->url; ?>">
                                                        <?php echo $event_model->name; ?></a> </h3>
                                                        <p><?php do_action( 'event_magic_popup_custom_data_before_details', $event_model );?></p>
                                                        
                                                        <?php if(!empty($event_model->venue)){ 
                                                            $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                                            $venue= $venue_service->load_model_from_db($event_model->venue);
                                                            if(!empty($venue->id) && !empty($venue->address)){ ?>
                                                        <div class="ep-event-location" title="<?php echo $venue->address; ?>"><i class="fas fa-map-marker-alt"></i> <?php echo $venue->address; ?></div>
                                                        <?php  } } ?> 
                                                        
                                                        <?php if(!empty($event_model->description)) { ?>
                                                            <p><?php echo $event_model->description; ?></p>
                                                        <?php } ?>
                                                        <div class="ep-event-footer">
                                                            <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                                                if (em_compare_event_dates($event_model->id)){
                                                                    $day = date_i18n(get_option('date_format'), $event_model->start_date);
                                                                    $start_time = date_i18n(get_option('time_format'), $event_model->start_date);
                                                                    $end_time = date_i18n(get_option('time_format'), $event_model->end_date);
                                                                } else {
                                                                    $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->start_date);
                                                                    $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->end_date);
                                                                }
                                                            if($event_model->all_day):?>
                                                               <div class="ep-event-date">
                                                                    <i class="far fa-calendar-alt"></i>
                                                                    <?php echo date_i18n(get_option('date_format'), $event_model->start_date); ?><span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                                                </div>
                                                            <?php elseif(!empty($day)): ?>
                                                                <div class="ep-event-date">
                                                                    <i class="far fa-calendar-alt"></i>
                                                                    <?php echo $day; ?>
                                                                </div>
                                                                <div class="ep-event-date"><i class="far fa-calendar-alt"></i><?php echo $start_time.'  to  '.$end_time; ?></div>
                                                            <?php else: ?>
                                                                <div class="ep-event-date">
                                                                    <i class="far fa-calendar-alt"></i>
                                                                    <?php echo $start_date; ?> -    
                                                                </div>
                                                                <div class="ep-event-date">
                                                                    <i class="far fa-calendar-alt"></i>
                                                                    <?php echo $end_date; ?>  
                                                                </div>
                                                            <?php endif; ?>
                                                           
                                                            <div class="ep-atend-btn">
                                                                <?php if ($this->is_bookable($event_model) && absint($event_model->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone(); ?>
                                                                <?php if ($event_model->status=='expired'): ?>
                                                                    <a href="javascript:void(0);" class="ep-btn ep-btn-border"><?php echo em_global_settings_button_title('Bookings Expired'); ?></a>
                                                                <?php elseif ($current_ts>$event_model->last_booking_date): ?>
                                                                    <a href="javascript:void(0);" class="ep-btn ep-btn-border"><?php echo em_global_settings_button_title('Bookings Closed'); ?></a>
                                                                <?php elseif($current_ts<$event_model->start_booking_date): ?>
                                                                    <a href="javascript:void(0);" class="ep-btn ep-btn-border"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></a> 
                                                                <?php else: ?>
                                                                    <?php if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                                    <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                                    <button class="ep-btn ep-btn-border" name="tickets" onclick="em_event_booking(<?php echo $event_model->id; ?>)" class="em_header_button" id="em_booking">
                                                                        <i class="fa fa-ticket" aria-hidden="true"></i>
                                                                        <?php
                                                                        echo em_global_settings_button_title('Book Now');
                                                                        if ($event_model->ticket_price > 0){
                                                                            $ticketPrice = $event_model->ticket_price;
                                                                            // check if show one time event fees at front enable
                                                                            if($event_model->show_fixed_event_price){
                                                                                if($event_model->fixed_event_price > 0){
                                                                                    $ticketPrice = $event_model->fixed_event_price;
                                                                                }
                                                                            }
                                                                            if ($ticketPrice > 0){
                                                                                echo " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                                            }
                                                                            do_action('event_magic_single_event_ticket_price_after', $event_model, $ticketPrice);
                                                                        }
                                                                        ?>
                                                                        <i class="fas fa-arrow-right"></i>
                                                                    </button>
                                                                        <input type="hidden" name="event_id" value="<?php echo $event_model->id; ?>" />
                                                                        <input type="hidden" name="venue_id" value="<?php echo $event_model->venue; ?>" />
                                                                    </form>
                                                                    <?php else: ?>
                                                                        <a class="ep-btn ep-btn-border" target="_blank" href="<?php echo add_query_arg('event_id',$event_model->id, get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?><i class="fas fa-arrow-right"></i></a>
                                                                    <?php endif; ?>
                                                                
                                                                <?php endif; ?>
                                                                <?php elseif(absint($event_model->custom_link_enabled) != 1): ?>
                                                                    <a href="javascript:void(0);" class="ep-btn ep-btn-border"><?php echo em_global_settings_button_title('Bookings Closed'); ?></a>
                                                                <?php endif; ?>           
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- content end-->
                                                </div>
                                            </div>
                                        
                                <?php }?>
                            </div>
                            <!-- next / prev arrows -->
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                                <!--  <div class="swiper-button-next" tabindex="0" role="button" aria-label="Next slide" aria-disabled="false"> <i
                                        class="fas fa-arrow-right"></i> </div>
                                <div class="swiper-button-prev swiper-button-disabled" tabindex="0" role="button"
                                    aria-label="Previous slide" aria-disabled="true"> <i class="fas fa-arrow-left"></i> </div> -->
                                <!-- !next / prev arrows -->
                                <!-- pagination dots -->
                                <!-- <div class="swiper-pagination swiper-pagination-clickable swiper-pagination-bullets"><span
                                        class="swiper-pagination-bullet swiper-pagination-bullet-active" tabindex="0" role="button"
                                        aria-label="Go to slide 1"></span><span class="swiper-pagination-bullet" tabindex="0" role="button"
                                        aria-label="Go to slide 2"></span></div> -->
                                <!-- !pagination dots -->

                                <!--  <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span> -->
                        </div>
                    </div>
                </div> 
        <?php }
        }else {
            if($_POST){ ?>
                <article>
                    <p><?php _e('No events match your criterion.','eventprime-event-calendar-management'); ?></p>
                </article>
            <?php }else{ ?>
                <article>
                    <p><?php _e('There are no Events available right now.','eventprime-event-calendar-management'); ?></p>
                </article>
            <?php } 
        } ?>
        </div>
        <?php 
    }

    public function get_upcoming_events_for_venue( $venue_id, $args ){
        $filter = array(
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => em_append_meta_key('start_date'),
            'numberposts' => -1,
            'order' => 'ASC',
            'meta_query' => array('relation' => 'AND',
                array(
                    array(
                        'key' => em_append_meta_key('venue'),
                        'value' => $venue_id,
                       /*  'compare' => 'LIKE' */
                    ),
                    array(
                        'key' => em_append_meta_key('hide_event_from_events'),
                        'value' => '1', //
                        'compare' => '!='
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => em_append_meta_key('start_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '>=',
                        ),
                        array(
                            'key' => em_append_meta_key('end_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '<=',
                        )),
                    array(
                        'key' => em_append_meta_key('hide_event_from_calendar'),
                        'value' => '1',
                        'compare' => '!='
                        )
                    )
            ),
            'post_type' => $this->post_type );

        $args = wp_parse_args( $args, $filter );
        add_filter('posts_orderby', 'em_posts_order_by');
        $wp_query = new WP_Query( $args );
        $wp_query->venue_id = $venue_id;
        remove_filter( 'posts_orderby', 'em_posts_order_by' ); 
        return $wp_query;
    }

    /* get upcoming events for single organizer */
    public function get_upcoming_events_for_organizer( $organizer_id, $args = array() ) { 
        $filter = array(
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => em_append_meta_key('start_date'),
            'numberposts' => -1,
            'order' => 'ASC',
            'meta_query' => array('relation' => 'AND',
                array(
                    array(
                        'key' => em_append_meta_key('organizer'),
                        'value' => sprintf(';i:%d;', $organizer_id),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => em_append_meta_key('hide_event_from_events'),
                        'value' => '1', //
                        'compare' => '!='
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => em_append_meta_key('start_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '>=',
                        ),
                        array(
                            'key' => em_append_meta_key('end_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '<=',
                        )),
                    array(
                        'key' => em_append_meta_key('hide_event_from_calendar'),
                        'value' => '1',
                        'compare' => '!='
                        )
                    )
            ),
            'post_type' => $this->post_type );

        $args = wp_parse_args( $args, $filter );
        add_filter('posts_orderby', 'em_posts_order_by');
        $wp_query = new WP_Query( $args );
        $wp_query->organizer_id = $organizer_id;
        remove_filter( 'posts_orderby', 'em_posts_order_by' ); 
        return $wp_query;
    }
}