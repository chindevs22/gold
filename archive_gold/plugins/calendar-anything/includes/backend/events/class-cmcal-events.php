<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Events {

    public function init() {

        // Event fields
        add_action('cmb2_admin_init', array($this, 'event_custom_fields'));

        // Event taxonomies fields
        add_action('cmb2-taxonomy_meta_boxes', array($this, 'event_taxonomies_custom_fields'));

        //Delete transients
        add_action('edit_post', array($this, 'delete_cmcal_transient'), 10, 2);
    }

    function delete_cmcal_transient($post_id, $post) {
        if (in_array($post->post_type, CMCAL()->setup_options['general_options']["post_types"])) {
            global $wpdb;
            $sql = "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE ('%\_cmcal_trans_calendar_events\_%')";
            $wpdb->query($sql);
        }
    }

    function event_custom_fields() {
        $options = CMCAL()->setup_options;
        $general_options = $options['general_options'];
        $post_types_options = $options['post_types_options'];

        $post_types = $general_options["post_types"];
        foreach ($post_types as $post_type) {
            $post_type_options = $post_types_options[$post_type];

            $prefix = 'codemine_event_' . $post_type . '_';
            $event_metaboxes = new_cmb2_box(array(
                'id' => $prefix . 'event_metaboxes',
                'title' => esc_html__('Event Options', 'calendar-anything'),
                'object_types' => array($post_type)
            ));
            if ($post_type_options["start_end_dates_mode"] == 'codemine_fields' || $post_type_options["all_day_enabled"] == 'codemine_field') {
                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'datatime_options',
                    'name' => esc_html__('Event Date and Time Options', 'calendar-anything'),
                    'type' => 'title',
                ));

                if ($post_type_options["all_day_enabled"] == 'codemine_field') {
                    $event_metaboxes->add_field(array(
                        'id' => $prefix . 'all_day',
                        'name' => esc_html__('All-Day Event', 'calendar-anything'),
                        'desc' => esc_html__('Whether an event occurs at a specific time-of-day. This property affects whether an event\'s time is shown. Also, in the agenda views, determines if it is displayed in the "all-day" section.', 'calendar-anything'),
                        'type' => 'radio_inline',
                        'options' => array(
                            'true' => esc_html__('Yes', 'calendar-anything'),
                            'false' => esc_html__('No', 'calendar-anything'),
                        ),
                        'default' => 'false',
                    ));
                }

                if ($post_type_options["start_end_dates_mode"] == 'codemine_fields') {

                    $event_metaboxes->add_field(array(
                        'id' => $prefix . 'date',
                        'name' => esc_html__('Event Start Date', 'calendar-anything'),
                        'desc' => esc_html__('The date an event begins.', 'calendar-anything'),
                        'type' => 'text_date',
                        'date_format' => 'Y-m-d',
                        'attributes' =>  apply_filters('cmcal_datepicker_attributes', array()),
                    ));

                    $event_metaboxes->add_field(array(
                        'id' => $prefix . 'start_time',
                        'name' => esc_html__('Event Start Time', 'calendar-anything'),
                        'desc' => esc_html__('The time an event begins.', 'calendar-anything'),
                        'type' => 'text_time',
                        'time_format' => apply_filters('cmcal_timepicker_time_format', 'H:i:s'),
                        'attributes' =>  apply_filters('cmcal_timepicker_attributes', array()),
                    ));

                    $event_metaboxes->add_field(array(
                        'id' => $prefix . 'end_date',
                        'name' => esc_html__('Event End Date', 'calendar-anything'),
                        'type' => 'text_date',
                        'date_format' => 'Y-m-d',
                        'attributes' =>  apply_filters('cmcal_datepicker_attributes', array()),
                    ));

                    $event_metaboxes->add_field(array(
                        'id' => $prefix . 'end_time',
                        'name' => esc_html__('Event End Time', 'calendar-anything'),
                        'type' => 'text_time',
                        'time_format' => apply_filters('cmcal_timepicker_time_format', 'H:i:s'),
                        'attributes' =>  apply_filters('cmcal_timepicker_attributes', array()),
                    ));
                }
            }
            if ($post_type_options["repetition_enabled"] == 'yes') {
                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repetition_options',
                    'name' => esc_html__('Event Repetition Options', 'calendar-anything'),
                    'type' => 'title',
                ));

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repeatable',
                    'name' => esc_html__('Repeatable Event', 'calendar-anything'),
                    'type' => 'radio_inline',
                    'options' => array(
                        'yes' => esc_html__('Yes', 'calendar-anything'),
                        'no' => esc_html__('No', 'calendar-anything'),
                    ),
                    'default' => 'no',
                ));

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repeatable_end_date',
                    'name' => esc_html__('Repeatable Event End Date', 'calendar-anything'),
                    'type' => 'text_date',
                    'date_format' => 'Y-m-d',
                    'attributes' => array(
                        'data-conditional-id' => $prefix . 'repeatable',
                        'data-conditional-value' => 'yes',
                        'required' => 'required',
                    ),
                ));

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repetition',
                    'name' => esc_html__('Event Repetion', 'calendar-anything'),
                    'type' => 'radio',
                    'options' => array(
                        'daily' => esc_html__('Daily', 'calendar-anything'),
                        'weekly' => esc_html__('Weekly', 'calendar-anything'),
                        'monthly' => esc_html__('Monthly', 'calendar-anything'),
                        'yearly' => esc_html__('Yearly', 'calendar-anything'),
                    ),
                    'attributes' => array(
                        'data-conditional-id' => $prefix . 'repeatable',
                        'data-conditional-value' => 'yes',
                    ),
                    'default' => 'daily',
                ));

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repetition_daily_n',
                    'name' => esc_html__('Repeat every N day where N is', 'calendar-anything'),
                    'type' => 'text',
                    'attributes' => array(
                        'type' => 'number',
                        'pattern' => '\d*',
                    ),
                    'sanitization_cb' => 'cmb2_positive_integer_sanitization',
                    'escape_cb' => 'absint',
                    'attributes' => array(
                        'data-conditional-id' => $prefix . 'repetition',
                        'data-conditional-value' => 'daily',
                    ),
                ));

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repetition_weekly_n',
                    'name' => esc_html__('Repeat every N next week where N is', 'calendar-anything'),
                    'type' => 'text',
                    'attributes' => array(
                        'type' => 'number',
                        'pattern' => '\d*',
                    ),
                    'sanitization_cb' => 'cmb2_positive_integer_sanitization',
                    'escape_cb' => 'absint',
                    'attributes' => array(
                        'data-conditional-id' => $prefix . 'repetition',
                        'data-conditional-value' => 'weekly',
                    ),
                ));

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repetition_monthly_n',
                    'name' => esc_html__('Repeat every N next month where N is', 'calendar-anything'),
                    'type' => 'text',
                    'attributes' => array(
                        'type' => 'number',
                        'pattern' => '\d*',
                    ),
                    'sanitization_cb' => 'cmb2_positive_integer_sanitization',
                    'escape_cb' => 'absint',
                    'attributes' => array(
                        'data-conditional-id' => $prefix . 'repetition',
                        'data-conditional-value' => 'monthly',
                    ),
                ));

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repetition_yearly_n',
                    'name' => esc_html__('Repeat every N next year where N is', 'calendar-anything'),
                    'type' => 'text',
                    'attributes' => array(
                        'type' => 'number',
                        'pattern' => '\d*',
                    ),
                    'sanitization_cb' => 'cmb2_positive_integer_sanitization',
                    'escape_cb' => 'absint',
                    'attributes' => array(
                        'data-conditional-id' => $prefix . 'repetition',
                        'data-conditional-value' => 'yearly',
                    ),
                ));
                
                // Event Certain Dates Repetition Options

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repetition_certain_dates_options',
                    'name' => esc_html__('Event Certain Dates Repetition Options', 'calendar-anything'),
                    'type' => 'title',
                ));
                $group_field_id = $event_metaboxes->add_field(array(
                    'id' => $prefix . 'certain_dates_group',
                    'type' => 'group',
                    'options' => array(
                        'group_title' => esc_html__('Repetition Date {#}', 'calendar-anything'), // since version 1.1.4, {#} gets replaced by row number
                        'add_button' => esc_html__('Add Another Date', 'calendar-anything'),
                        'remove_button' => esc_html__('Remove Date', 'calendar-anything'),
                        'sortable' => true,
                        'closed' => true,
                    ),
                ));
                $event_metaboxes->add_group_field($group_field_id, array(
                    'id' => $prefix . 'date',
                    'name' => esc_html__('Event Start Date', 'calendar-anything'),
                    'desc' => esc_html__('The date an event begins.', 'calendar-anything'),
                    'type' => 'text_date',
                    'date_format' => 'Y-m-d',
                ));
                $event_metaboxes->add_group_field($group_field_id, array(
                    'id' => $prefix . 'start_time',
                    'name' => esc_html__('Event Start Time', 'calendar-anything'),
                    'desc' => esc_html__('The time an event begins.', 'calendar-anything'),
                    'type' => 'text_time',
                    'time_format' => 'H:i:s',
                ));
                $event_metaboxes->add_group_field($group_field_id, array(
                    'id' => $prefix . 'end_date',
                    'name' => esc_html__('Event End Date', 'calendar-anything'),
                    'type' => 'text_date',
                    'date_format' => 'Y-m-d',
                ));
                $event_metaboxes->add_group_field($group_field_id, array(
                    'id' => $prefix . 'end_time',
                    'name' => esc_html__('Event End Time', 'calendar-anything'),
                    'type' => 'text_time',
                    'time_format' => 'H:i:s',
                ));
                $event_metaboxes->add_group_field($group_field_id, array(
                    'id' => $prefix . 'repeatable_certain',
                    'name' => esc_html__('Enable Repetition for this Date', 'calendar-anything'),
                    'type' => 'radio_inline',
                    'options' => array(
                        'yes' => esc_html__('Yes', 'calendar-anything'),
                        'no' => esc_html__('No', 'calendar-anything'),
                    ),
                    'attributes' => array(
                        'data-conditional-id' => $prefix . 'repeatable',
                        'data-conditional-value' => 'yes',
                    ),
                    'default' => 'no',
                ));
                
                // Event Exluce Certain Dates Repetition Options
                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repetition_dates_exclude',
                    'name' => esc_html__('Exclude Dates From Repetitive Events', 'calendar-anything'),
                    'type' => 'title',
                ));
                $repetition_dates_exclude_group_field_id = $event_metaboxes->add_field(array(
                    'id' => $prefix . 'repetition_dates_exclude_group',
                    'type' => 'group',
                    'options' => array(
                        'group_title' => esc_html__('Exclude Date {#}', 'calendar-anything'), // since version 1.1.4, {#} gets replaced by row number
                        'add_button' => esc_html__('Add Another Date', 'calendar-anything'),
                        'remove_button' => esc_html__('Remove Date', 'calendar-anything'),
                        'sortable' => true,
                        'closed' => true,
                    ),
                ));
                $event_metaboxes->add_group_field($repetition_dates_exclude_group_field_id, array(
                    'id' => $prefix . 'date',
                    'name' => esc_html__('Event Date', 'calendar-anything'),
                    'type' => 'text_date',
                    'date_format' => 'Y-m-d',
                ));
            }

            $warning_message = esc_html__('Warning: Calendars with setting "Event on click" set to "Go to Permalink" will overwrite the settings below.', 'calendar-anything');
            $event_metaboxes->add_field(array(
                'id' => $prefix . 'click_options',
                'name' => esc_html__('Event Click Options', 'calendar-anything'),
                'type' => 'title',
                'after_row' => '<div class="cmcal_warning">' . $warning_message . '</div>',
            ));

            $event_metaboxes->add_field(array(
                'id' => $prefix . 'action_on_click',
                'name' => esc_html__('Action on Click', 'calendar-anything'),
                'type' => 'radio_inline',
                'options' => array(
                    'none' => esc_html__('No Action', 'calendar-anything'),
                    'goto_permalink' => esc_html__('Go to Permalink', 'calendar-anything'),
                    'goto_custom_url' => esc_html__('Go to Custom URL', 'calendar-anything'),
                ),
                'default' => 'none',
            ));


            $event_metaboxes->add_field(array(
                'id' => $prefix . 'custom_url',
                'name' => esc_html__('Event custom URL', 'calendar-anything'),
                'desc' => esc_html__('A URL that will be visited when this event is clicked by the user.', 'calendar-anything'),
                'type' => 'text_url',
                'attributes' => array(
                    'data-conditional-id' => $prefix . 'action_on_click',
                    'data-conditional-value' => 'goto_custom_url',
                    'required' => 'required',
                ),
            ));

            $event_metaboxes->add_field(array(
                'id' => $prefix . 'url_new_window',
                'name' => esc_html__('Open url in new window?', 'calendar-anything'),
                'type' => 'radio_inline',
                'options' => array(
                    'true' => esc_html__('Yes', 'calendar-anything'),
                    'false' => esc_html__('No', 'calendar-anything'),
                ),
                'default' => 'false',
                'attributes' => array(
                    'data-conditional-id' => $prefix . 'action_on_click',
                    'data-conditional-value' => wp_json_encode(array('goto_permalink', 'goto_custom_url')),
                    'required' => 'required',
                ),
            ));

            if (($general_options["enable_background_color"] == "yes") ||
                    ($general_options["enable_border_color"] == "yes") ||
                    ($general_options["enable_text_color"] == "yes")) {

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'color_options',
                    'name' => esc_html__('Event Color Options', 'calendar-anything'),
                    'type' => 'title',
                ));

                if ($general_options["enable_background_color"] == "yes") {
                    $event_metaboxes->add_field(array(
                        'id' => $prefix . 'backgroundColor',
                        'name' => esc_html__('Event Background Color', 'calendar-anything'),
                        'type' => 'colorpicker',
                    ));
                }
                if ($general_options["enable_border_color"] == "yes") {
                    $event_metaboxes->add_field(array(
                        'id' => $prefix . 'borderColor',
                        'name' => esc_html__('Event Border Color', 'calendar-anything'),
                        'type' => 'colorpicker',
                    ));
                }
                if ($general_options["enable_text_color"] == "yes") {
                    $event_metaboxes->add_field(array(
                        'id' => $prefix . 'textColor',
                        'name' => esc_html__('Event Text Color', 'calendar-anything'),
                        'type' => 'colorpicker',
                    ));
                }
            }

            //Custom Fields
            $custom_fields = $general_options["custom_fields"];
            if ($custom_fields) {

                $event_metaboxes->add_field(array(
                    'id' => $prefix . 'custom_fields',
                    'name' => esc_html__('Custom Fields', 'calendar-anything'),
                    'type' => 'title',
                ));

                foreach ($custom_fields as $cf) {
                    if (isset($cf["id"])) {
                        $event_metaboxes->add_field(array(
                            'id' => $prefix . 'cf_' . $cf["id"],
                            'name' => $cf["title"],
                            'type' => $cf["type"],
                        ));
                    }
                }
            }
        }

        function cmb2_positive_integer_sanitization($value, $field_args, $field) {
            if ($value == 0) {
                return 1;
            } else {
                return absint($value);
            }
        }

    }

    function event_taxonomies_custom_fields(array $meta_boxes) {
        $options = CMCAL()->setup_options;
        $general_options = $options['general_options'];
        $prefix = 'codemine_event_taxonomy_';
        $post_types_options = $options['post_types_options'];

        $post_types = $general_options["post_types"];
        $taxonomies = array();

        foreach ($post_types as $post_type) {
            $post_type_options = $post_types_options[$post_type];
            $post_type_taxonomy_is_set = $post_type_options["post_type_taxonomy_is_set"];
            if ($post_type_taxonomy_is_set) {
                $taxonomies[] = $post_type_options["post_type_taxonomy"];
            }
        }
        if (empty($taxonomies))
            return;
        $fields = array();
        if (($general_options["enable_tax_background_color"] == "yes") ||
                ($general_options["enable_tax_border_color"] == "yes") ||
                ($general_options["enable_tax_text_color"] == "yes")) {
            $fields[] = array(
                'id' => $prefix . 'color_options',
                'name' => esc_html__('Event Category Color Options for Calendar Anything', 'calendar-anything'),
                'type' => 'title',
            );
        }
        if ($general_options["enable_tax_background_color"] == "yes") {
            $fields[] = array(
                'id' => $prefix . 'backgroundColor',
                'name' => esc_html__('Event Category Background Color', 'calendar-anything'),
                'type' => 'colorpicker',
            );
        }
        if ($general_options["enable_tax_border_color"] == "yes") {
            $fields[] = array(
                'id' => $prefix . 'borderColor',
                'name' => esc_html__('Event Category Border Color', 'calendar-anything'),
                'type' => 'colorpicker',
            );
        }
        if ($general_options["enable_tax_text_color"] == "yes") {
            $fields[] = array(
                'id' => $prefix . 'textColor',
                'name' => esc_html__('Event Category Text Color', 'calendar-anything'),
                'type' => 'colorpicker',
            );
        }

        $meta_boxes['cmal_tax_custom_fields'] = array(
            'id' => 'cmal_tax_custom_fields',
            'object_types' => $taxonomies, // Taxonomy
            'fields' => $fields
        );

        return $meta_boxes;
    }

}
