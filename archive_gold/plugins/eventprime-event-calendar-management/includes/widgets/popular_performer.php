<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('EventM_Popular_Performer')){

    class EventM_Popular_Performer extends WP_Widget {

        function __construct() {
            parent::__construct('eventm_popular_performer', "EventPrime Popular Performer" , array( 'description' => 'Show list of popular performers.' )
            );
        }

        public function widget($args, $instance) {
            wp_enqueue_script('em-public');
            wp_enqueue_style('em-public-css');
            
            $title = ( !empty( $instance['title'] ) ) ? $instance['title'] : __( 'Popular Performers', 'eventprime-event-calendar-management' );
            $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
            $number = ( !empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
            if ( !$number ) {
                $number = 5;
            }

            $html = '<div class="widget widget_featured_events"><div class="widget-content">';
                $html .= '<h2 class="widget-title subheading heading-size-3">'.$title.'</h2>';
                $performer_service = EventM_Factory::get_service('EventM_Performer_Service');
                $performer_ids = $performer_service->get_popular_performers($number); 
                $performer_ids = array_slice( (array)$performer_ids[0], 0, $number, true );
                $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
                $global_settings= $setting_service->load_model_from_db();
                if(!empty($performer_ids)){
                    $i = 0;
                    foreach ($performer_ids as $performer_id) {
                       
                        $html .= '<div class="ep-popular-performer">';
                            $performerData = $performer_service->load_model_from_db($performer_id);
                            $title = $performerData->name;
                            $performers_page_url= get_permalink( em_global_settings("performers_page") );
                            $url = add_query_arg("performer", $performer_id, $performers_page_url);
                            $enable_seo_urls = em_global_settings('enable_seo_urls');
                            if(!empty($enable_seo_urls)){
                                $url = get_permalink($performer_id);
                            }
                            $html .= '<div class="ep-fimage">';
                            if (!empty($performerData->feature_image_id)):
                                $html .= '<a href="'.$url.'">'.get_the_post_thumbnail($performer_id, 'large').'</a>';
                            else:
                                $html .= '<a href="'.$url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy-performer.png').'" alt="'.__('Dummy Image','eventprime-list-widgets').'" ></a>';
                            endif;
                            $html .= '</div>';
                            $html .= '<div class="ep-fdata"><div class="ep-fname"><a href="'.$url.'">'.$title.'</a></div>';
                            if(!empty($performerData->role)){
                                $html .= '<div class="ep-performer-role">'.$performerData->role.'</div>';
                            }                  
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
                <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of performers to show:', 'eventprime-list-widgets' ); ?></label>
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
function em_load_popular_performer() {
    register_widget('eventm_popular_performer');
}

add_action('widgets_init', 'em_load_popular_performer');
