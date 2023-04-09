<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('EventM_Event_Countdown')):

    class EventM_Event_Countdown extends WP_Widget {

        function __construct() {
            parent::__construct('eventm_event_countdown', "Event Countdown", array()
            );
        }

        public function widget($args, $instance) {
            wp_enqueue_script('em-public');
            wp_enqueue_style('em-public-css');
            $title = apply_filters('widget_title', $instance['title'], $instance['event_id']);
            echo $args['before_widget'];
            
            $events_page_id = em_global_settings("events_page");
            $event_id = (int) $instance['event_id'];
            $event_service= EventM_Factory::get_service('EventM_Service');
            $event = $event_service->load_model_from_db($event_id);
            $event_url = (absint($event->custom_link_enabled) == 1) ? $event->custom_link : add_query_arg('event', $event_id, get_permalink($events_page_id));
            if (!empty($event->id) && $event->status == 'publish'):
                if (!empty($title)){
                    echo $args['before_title'] . $title . $args['after_title'];
                }?>
                <?php if ($event->start_date > current_time('timestamp') && $event->hide_event_from_events != 1): ?>
                    <div class="event_title dbfl"><a href="<?php echo $event_url; ?>"><?php echo $event->name; ?></a></div> 
                    <?php $start_date = em_showDateTime($event->start_date, true);
                          wp_enqueue_script("em_countdown_jquery", plugin_dir_url(__DIR__) . 'templates/js/jquery.countdown.min.js', false, EVENTPRIME_VERSION);
                    ?>
                    <div class="em_widget_container">
                        <div class="em_countdown_timer dbfl" id="em_widget_event_countdown_<?php echo $this->number; ?>">
                            <span class="days em_color" id="em_countdown_days_<?php echo $this->number; ?>"></span>
                            <span class="hours em_color" id="em_countdown_hours_<?php echo $this->number; ?>"></span>
                            <span class="minutes em_color" id="em_countdown_minutes_<?php echo $this->number; ?>"></span>
                            <span class="seconds em_color" id="em_countdown_seconds_<?php echo $this->number; ?>"></span>
                        </div>
                    </div>
                    <script type="text/javascript">
                        jQuery(document).ready(function () {
                            $= jQuery;
                            var date = new Date("<?php echo $start_date; ?>");
                            $('#em_widget_event_countdown_<?php echo $this->number; ?>').countdown(date, function (event) {
                                $("#em_countdown_days_<?php echo $this->number; ?>").html(event.strftime('%D'));
                                $("#em_countdown_hours_<?php echo $this->number; ?>").html(event.strftime('%H'));
                                $("#em_countdown_minutes_<?php echo $this->number; ?>").html(event.strftime('%M'));
                                $("#em_countdown_seconds_<?php echo $this->number; ?>").html(event.strftime('%S'));
                            });
                            var emDominentColor = $('.event_title').prepend('<a></a>');
                            var emColor = $('.event_title').find('a').css('color');
                            $(".em_color").css('color', emColor);
                            $(".em_bg").css('background-color', emColor);
                        });
                    </script>
                    <?php
                endif;
            endif;
            echo $args['after_widget'];
        }

        public function form($instance) {
            if (isset($instance['title'])) {
                $title = $instance['title'];
            } else {
                $title = __('New Title','eventprime-event-calendar-management');
            }

            if (isset($instance['event_id'])) {
                $event_id = $instance['event_id'];
            } else {
                $event_id = "";
            }
            ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </p>

            <p>
                <label for="event">Event</label> <br>
                <?php
                $event_service = EventM_Factory::get_service('EventM_Service');
                $events = $event_service->get_upcoming_events();
                ?>
                <select id="<?php echo $this->get_field_id('event_id'); ?>" name="<?php echo $this->get_field_name('event_id'); ?>">
                    <option><?php _e('Select Event','eventprime-event-calendar-management'); ?></option>
                    <?php
                    if (!empty($events)):
                        foreach ($events as $event):
                            if ($event->start_date <= current_time('timestamp'))
                                continue;
                            ?>
                            <option <?php if ($event_id == $event->id) echo 'selected'; ?> value="<?php echo $event->id ?>"><?php echo $event->name; ?></option>    
                            <?php
                        endforeach;
                    endif;
                    ?>
                </select>
            </p>
            <?php
        }

        // Updating widget replacing old instances with new
        public function update($new_instance, $old_instance) {

            $instance = array();
            $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
            $instance['event_id'] = (!empty($new_instance['event_id']) ) ? strip_tags($new_instance['event_id']) : '';
            return $instance;
        }

    }

    endif;

// Register and load the widget
function em_load_event_countdown() {
    register_widget('eventm_event_countdown');
}

add_action('widgets_init', 'em_load_event_countdown');
