<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('EventM_calendar')) {

    class EventM_calendar extends WP_Widget {

        function __construct() {
            parent::__construct('eventm_calendar', __("EventPrime Calendar"), array('description' => __("Event Calendar to show all the events", 'eventprime-event-calendar-management'))
            );
        }

        public function widget($args, $instance) {
            $title = apply_filters('widget_title', $instance['title']);
            echo $args['before_widget'];
            if (!empty($title))
                echo $args['before_title'] . $title . $args['after_title'];
            wp_enqueue_script('em-public');
            wp_enqueue_style('em-public-css');
            wp_enqueue_script('jquery-ui-datepicker', array('jquery'));
            $events_page_id = em_global_settings("events_page");
            wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css', array(), EVENTPRIME_VERSION);
            ?>


            <div class="em_widget_container">
                <a></a>
                <div id="em_calendar_widget"></div>
                <form name="em_calendar_event_form" method="get" action="<?php echo get_permalink($events_page_id); ?>">
                    <input type="hidden" name="em_s"s value="1" />
                    <input type="hidden" name="em_sd" id="em_start_date" value="" />
                    <div class="em_upcoming_events">
                        <div class="em_calendar_widget-events-title"><?php _e('Upcoming Events','eventprime-event-calendar-management') ?></div>
            <?php
            $event_service = EventM_Factory::get_service('EventM_Service');
            $events = $event_service->get_upcoming_events();
            $today = current_time('timestamp');
            if (!empty($events)) {
                for ($i = 0; $i < min(5, count($events)); $i++) {
                    $event= $events[$i];
                    $event_url = (absint($event->custom_link_enabled) == 1) ? $event->custom_link : add_query_arg('event', $event->id, get_permalink($events_page_id));
                    ?>
                                <div class="em_upcoming_event"><a href="<?php echo $event_url; ?>">
                                <?php echo $events[$i]->name; ?></a>
                                    <?php if ($today > $event->start_date && $today <$event->end_date): ?>
                                        <span class="kf-live"><?php _e('Live', 'eventprime-event-calendar-management'); ?></span>
                                    <?php endif; ?>
                                </div>
                                        <?php
                                    }
                                }
                                ?>
                    </div>
                </form>
            </div>

            <script type="text/javascript">
                jQuery(document).on('click', '#em_calendar_widget', function () {
                    em_change_dp_css();
                });
            </script>     

            <?php
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

// Updating widget replacing old instances with new
        public function update($new_instance, $old_instance) {
            $instance = array();
            $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
            return $instance;
        }

    }

}

// Register and load the widget
function em_load_calendar_widget() {
    register_widget('eventm_calendar');
}

add_action('widgets_init', 'em_load_calendar_widget');



