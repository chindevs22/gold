<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('EventM_Popular_Venue')){

    class EventM_Popular_Venue extends WP_Widget {

        function __construct() {
            parent::__construct('eventm_popular_venue', "Popular Event Venues" , array( 'description' => 'Show list of popular event venues.' )
            );
        }

        public function widget($args, $instance) {
            wp_enqueue_script('em-public');
            wp_enqueue_style('em-public-css');
            
            $title = ( !empty( $instance['title'] ) ) ? $instance['title'] : __( 'Popular Event Venues', 'eventprime-event-calendar-management' );
            $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
            $number = ( !empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
            if ( !$number ) {
                $number = 5;
            }

            $html = '<div class="widget widget_featured_events"><div class="widget-content">';
                $html .= '<h2 class="widget-title subheading heading-size-3">'.$title.'</h2>';
                $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
                $filter = array(
                    'post_status' => 'publish',
                    'meta_query' => array(
                        
                    ) );
                $venue_ids = $venue_service->get_popular_venues( $filter ); 
                $venue_ids = array_slice( (array)$venue_ids[0], 0, $number, true );
                $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
                $global_settings = $setting_service->load_model_from_db();
                if(!empty($venue_ids)){
                    $i = 0;
                    foreach ( $venue_ids as $venue_id ) {
                       
                        $html .= '<div id="ep-featured-events"  class="ep-mw-wrap">';
                            $venueData = $venue_service->load_model_from_db( $venue_id );
                            $title = $venueData->name;
                            $url = $venues_page_url = add_query_arg('venue', $venue_id, get_page_link( $global_settings->venues_page ) );
                            $html .= '<div class="ep-fimage">';
                            if (!empty($venueData->gallery_images)):
                                $html .= '<a href="'.$url.'"><img src="'.wp_get_attachment_image_src( $venueData->gallery_images[0], 'full'  )[0].'" alt="'.__( 'Event Venue Image', 'eventprime-event-calendar-management' ).'"></a>';
                            else:
                                $html .= '<a href="'.$url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-list-widgets').'" ></a>';
                            endif;
                            $html .= '</div>';
                            $html .= '<div class="ep-fdata"><div class="ep-fname"><a href="'.$url.'">'.$title.'</a></div>';               
                            $html .= '</div>';
                        $html .= '</div>';
                    }
                }
            $html .= '</div></div>';
            echo $html;
        }

        public function form($instance) {
            $title = !empty( $instance['title'] ) ? $instance['title'] : '';
            $number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'eventprime-list-widgets' ); ?></label> 
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of event venues to show:', 'eventprime-list-widgets' ); ?></label>
                <input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" />
            </p>
            <?php 
        }

        // Updating widget replacing old instances with new
        public function update($new_instance, $old_instance) {

            $instance = array();
            $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
            $instance['number'] = (int) $new_instance['number'];
            return $instance;
        }

    }

}

// Register and load the widget
function em_load_popular_venue() {
    register_widget('eventm_popular_venue');
}

add_action('widgets_init', 'em_load_popular_venue');
