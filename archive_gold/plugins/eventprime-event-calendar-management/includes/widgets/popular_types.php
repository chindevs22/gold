<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('EventM_Popular_Type')){

    class EventM_Popular_Type extends WP_Widget {

        function __construct() {
            parent::__construct('eventm_popular_type', "Popular Event Types" , array( 'description' => 'Show list of popular event types.' )
            );
        }

        public function widget($args, $instance) {
            wp_enqueue_script('em-public');
            wp_enqueue_style('em-public-css');
            
            $title = ( !empty( $instance['title'] ) ) ? $instance['title'] : __( 'Popular Event Types', 'eventprime-event-calendar-management' );
            $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
            $number = ( !empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
            if ( !$number ) {
                $number = 5;
            }

            $html = '<div class="widget widget_featured_events"><div class="widget-content">';
                $html .= '<h2 class="widget-title subheading heading-size-3">'.$title.'</h2>';
                $type_service = EventM_Factory::get_service('EventTypeM_Service');
                $filter = array(
                    'post_status' => 'publish',
                    'meta_query' => array(
                        array(	
                            'relation' => 'OR',
                            array(
                                'key' => em_append_meta_key('display_front'),
                                'value' => 1,
                                'compare' => '='
                            ),
                            array(
                                'key'     => em_append_meta_key('display_front'),
                                'value'   => 'true',
                                'compare' => '='
                            ),
                        )
                    ) );
                $type_ids = $type_service->get_popular_types( $filter ); 
                $type_ids = array_slice( (array)$type_ids[0], 0, $number, true );
                $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
                $global_settings = $setting_service->load_model_from_db();
                if(!empty($type_ids)){
                    $i = 0;
                    foreach ( $type_ids as $type_id ) {
                       
                        $html .= '<div id="ep-featured-events"  class="ep-mw-wrap">';
                            $typeData = $type_service->load_model_from_db( $type_id );
                            $title = $typeData->name;
                            $url = $types_page_url = add_query_arg('type', $type_id, get_page_link( $global_settings->event_types ) );
                            $html .= '<div class="ep-fimage">';
                            if (!empty($typeData->image_id)):
                                $html .= '<a href="'.$url.'"><img src="'.wp_get_attachment_image_src( $typeData->image_id, 'large' )[0].'" alt="'.__( 'Event Type Image', 'eventprime-event-calendar-management' ).'"></a>';
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
                <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of event types to show:', 'eventprime-list-widgets' ); ?></label>
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
function em_load_popular_type() {
    register_widget('eventm_popular_type');
}

add_action('widgets_init', 'em_load_popular_type');
