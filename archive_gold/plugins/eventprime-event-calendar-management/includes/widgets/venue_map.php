<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class EventM_Venue_Map extends WP_Widget {

    function __construct() {
        parent::__construct('eventm_venue_map', __("EventPrime - Sites Map"), array('description' => __("Map to show all the Event Site locations", 'eventprime-event-calendar-management'))
        );
    }

    public function widget($args, $instance) {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings= $setting_service->load_model_from_db();
        $queried_object = get_queried_object();
        if ($queried_object) {
            $post_id = $queried_object->ID;
            if($post_id==$global_settings->venues_page){
                return;
            }
        }

        $title = apply_filters('widget_title', $instance['title']);
        wp_enqueue_script('em-public');
        wp_enqueue_style('em-public-css');
        echo $args['before_widget'];
        if (!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];

        $gmap_api_key = em_global_settings('gmap_api_key');
        if ($gmap_api_key):
            em_localize_map_info("em-google-map");
            $events_page_id = em_global_settings("events_page");
            ?>
            <div class="em_widget_container">
                <div id="em_widget_venues_map_canvas" style="height: 300px;"></div>
            </div>
            <script>
                jQuery(document).ready(function () {
                    em_load_map("venue_widget", "em_widget_venues_map_canvas");
                });
            </script>
            <?php
        else:
            echo __("Please configure Google Map API key","eventprime-event-calendar-management");
        endif;
        echo $args['after_widget'];
    }

    /**
     * 
     * Widget Backend
     */
    public function form($instance) {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = "New Title";
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }

}

// Register and load the widget
function em_load_venue_map() {
    register_widget('eventm_venue_map');
}

add_action('widgets_init', 'em_load_venue_map');