<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class EventM_slider extends WP_Widget {

    function __construct() {
        parent::__construct('eventm_slider', __("EventPrime - Event Slider", 'eventprime-event-calendar-management'), array('description' => __("Event Slider to show all the events", 'eventprime-event-calendar-management'))
        );
    }

    public function widget($args, $instance) {
        wp_enqueue_script('em-public');
        wp_enqueue_style('em-public-css');
        wp_enqueue_script('em_responsive_slider_style');
        wp_enqueue_script('em_responsive_slider_js');
        ?>
        <?php
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events = $event_service->get_upcoming_events();
        ?>


        <div id="em_widget_container">
        <?php $data = $event_service->get_data_for_slider();
              $slider_images = array();
        ?>
            <ul class="em_event_slides">
                <?php foreach ($data->image_ids as $key => $image_id): ?>
                        <li>
                            <div class="kf-widget-slider-meta em_bg">
                                <?php
                                $event_id = $data->ids[$key];
                                $post_data = get_post($event_id);
                                $event_date = em_showDateTime(em_get_post_meta($event_id, 'start_date', true), true);
                                ?>
                                <div class="kf-widget-slider-title"><?php echo $post_data->post_title; ?></div>
                                <div class="kf-widget-slider-date"><?php echo $event_date; ?></div>
                            </div> 
                            <?php if(wp_attachment_is_image($image_id)): ?>
                                <a target="_blank" href="<?php echo $data->links[$key]; ?>"><?php echo wp_get_attachment_image($image_id, array(500, 300)); ?> </a>
                            <?php else: ?>
                                <a target="_blank" href="<?php echo $data->links[$key]; ?>"><img src="<?php echo EM_BASE_URL.'includes/templates/images/dummy_image.png' ?>" alt="Dummy Image" > </a>

                            <?php endif; ?>
                               
                        </li>
                <?php endforeach;?>
            </ul>    
        </div>

        <script>
            window.onload=function() { jQuery(".em_event_slides").responsiveSlides(); }
        </script>
        <?php
    }

}

// Register and load the widget
function em_load_slider_widget() {
    register_widget('eventm_slider');
}

add_action('widgets_init', 'em_load_slider_widget');


