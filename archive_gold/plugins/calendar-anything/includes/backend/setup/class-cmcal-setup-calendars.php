<?php

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class Cmcal_Setup_Calendars {

    /**
     * Option key, and option page slug
     * @var string
     */
    protected $key = 'cmcal_setup_calendars_options';

    /**
     * Option key, and option page slug
     * @var string
     */
    public $option_prefix = 'cmcal_setup_calendars_';

    /**
     * Options page metabox id
     * @var string
     */
    protected $metabox_id = 'cmcal_setup_calendars_metabox';

    /**
     * Options Page title
     * @var string
     */
    protected $title = '';

    /**
     * Options Page hook
     * @var string
     */
    protected $options_page = '';

    /**
     * Holds an instance of the object
     *
     * @var CMCAL_Setup_Calendars
     */
    protected static $instance = null;

    /**
     * Returns the running object
     *
     * @return CMCAL_Setup_Calendars
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Constructor
     * @since 0.1.0
     */
    protected function __construct() {
        
    }

    /**
     * Initiate our hooks
     * @since 0.1.0
     */
    public function hooks() {
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_options_page'));
        add_action('cmb2_admin_init', array($this, 'add_options_page_metabox'));
        add_action("cmb2_save_" . "options-page" . "_fields_" . $this->metabox_id, array($this, 'action_cmb2_save_fields'), 10, 3);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    public function admin_enqueue_scripts() {
        if (isset($_GET['page']) && $_GET['page'] == "cmcal_setup_calendars") {
            wp_enqueue_script('cmcal-edit-calendar', plugins_url('assets/js/cmcal-edit-calendar.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script('jquery.blockUI', plugins_url('assets/js/jquery.blockUI.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_style('cmcal-edit-calendar', plugins_url('assets/css/cmcal-edit-calendar.css', Codemine_Calendar_PLUGIN_FILE), empty($required_for_fullcalendar) ? array() : array($required_for_fullcalendar));

            $this->localize_script();
        }
    }

    public function localize_script() {
        $js_vars = array();
        $schema = is_ssl() ? 'https' : 'http';
        $js_vars['ajaxurl'] = admin_url('admin-ajax.php', $schema);
        $js_vars['delete_options_confirm_message'] = esc_html__("Your calendar settings will be lost.\nAre you sure?", 'calendar-anything');

        wp_localize_script('cmcal-edit-calendar', 'CMCAL_admin_edit_calendar_vars', $js_vars);
    }

    function action_cmb2_save_fields($object_id, $updated, $instance) {
        global $wpdb;
        $sql = "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE ('%\_cmcal_trans_calendar_events\_%')";
        $wpdb->query($sql);
    }

    /**
     * Register our setting to WP
     * @since  0.1.0
     */
    public function init() {
        register_setting($this->key, $this->key);
    }

    /**
     * Add menu options page
     * @since 0.1.0
     */
    public function add_options_page() {
        $capability = apply_filters('cmcal_capability', 'manage_options');
        $this->title = esc_html__('Calendars', 'calendar-anything');
        $this->options_page = add_submenu_page('cmcal_setup', $this->title, $this->title, $capability, 'cmcal_setup_calendars', array($this, 'admin_page_display'));
        // Include CMB CSS in the head to avoid FOUC
        add_action("admin_print_styles-{$this->options_page}", array('CMB2_hookup', 'enqueue_cmb_css'));
    }

    /**
     * Admin page markup. Mostly handled by CMB2
     * @since  0.1.0
     */
    public function admin_page_display() {
        $terms = get_terms('cmcal_calendar', array(
            'hide_empty' => false,
            'orderby' => 'id',
            'order' => 'ASC',
        ));
        $has_calendars = (!is_wp_error($terms) && !empty($terms));
        ?>
        <div class="wrap cmb2-options-page <?php echo esc_html($this->key); ?>">
            <div class="cmcal-calendar-settings-section" >
                <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
                <?php if (cmcal_setup_post_type_is_set()) { ?>
                    <input type="button" name="cmcal-calendar-add-new" id="cmcal-calendar-add-new" class="button button-primary button-large cmcal-btn" value="<?php esc_attr_e('Add New Calendar', 'calendar-anything'); ?>">
                    <div id="cmcal-calendar-edit-form">
                        <input id="calendar_action" type="hidden"> </input>
                        <input id="calendar_id" type="hidden"> </input>
                        <div>
                            <label for="calendar_name"><?php _e('Calendar Name', 'calendar-anything') ?></label>
                        </div>
                        <div>
                            <input id="calendar_name" name="calendar_name" type="text"></input>
                        </div>
                        <div>
                            <input type="button" name="cmcal-calendar-save" id="cmcal-calendar-save" class="button button-primary button-large cmcal-btn" value="<?php esc_attr_e('Save', 'calendar-anything'); ?>">
                            <input type="button" name="cmcal-calendar-cancel" id="cmcal-calendar-cancel" class="cancel button cmcal-btn" value="<?php esc_attr_e('Cancel', 'calendar-anything'); ?>">
                        </div>
                    </div>
                    <?php
                }
                if ($has_calendars)
                    cmcal_cmb2_metabox_form($this->metabox_id, $this->key);
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Add the options metabox to the array of metaboxes
     * @since  0.1.0
     */
    function add_options_page_metabox() {
        $terms = get_terms('cmcal_calendar', array(
            'hide_empty' => false,
            'orderby' => 'id',
            'order' => 'ASC',
        ));

        $cmb = new_cmb2_box(array(
            'id' => $this->metabox_id,
            'hookup' => false,
            'cmb_styles' => false,
            'show_on' => array(
                // These are important, don't remove
                'key' => 'options-page',
                'value' => array($this->key,)
            ),
        ));
        $info_header = '
                    <table class="wp-list-table widefat fixed posts cmcal-calendars-table">
                        <tbody>
                            <tr>
                                <td>' . esc_html__('Name', 'calendar-anything') . '</td>
                                <td>' . esc_html__('Shortcode', 'calendar-anything') . '</td>
                                <td></td>                              
                            </tr>
                        </tbody>
                    </table>';
        $cmb->add_field(array(
            'name' => $info_header,
            'type' => 'title',
            'id' => $this->option_prefix . 'calendars_table_header',
            'classes' => 'cmcal-calendars-table-header'
        ));

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $term_id = $term->term_id;
                $id_prefix = $this->option_prefix . $term_id . "_";
                $url = admin_url('/' . CMCAL()->setup_options["general_options"]["post_type_page"] . 'page=customize-calendar&calendar_id=' . $term->term_id);
                $customize_appearance = '<a class="preview button cmcal-btn" href="' . esc_url($url) . '" >' . esc_html__('Customize Calendar Appearance', 'calendar-anything') . '</a>';
                $data_buttons = 'data-calendar_id="' . esc_attr($term->term_id) . '"  data-calendar_name="' . esc_attr($term->name) . '"';
                $delete_btn = '<input ' . $data_buttons . ' type="button" name="cmcal-calendar-delete"  class="button button-primary button-large delete-btn cmcal-btn" value="' . esc_attr__('Delete', 'calendar-anything') . '">';
                $update_btn = '<input ' . $data_buttons . ' type="button" name="cmcal-calendar-update"  class="button button-primary button-large cmcal-btn" value="' . esc_attr__('Update Name', 'calendar-anything') . '">';

                $info_group = '
                    <table class="wp-list-table widefat fixed posts cmcal-calendars-table">
                        <tbody>
                            <tr>
                                <td>' . esc_html($term->name) . '</td>
                                <td>' . '<pre>[calendar_anything id="' . esc_attr($term->term_id) . '"]' . '</td>
                                <td>' . $customize_appearance . '</td>
                            </tr>
                        <tbody>
                    </table>';


                $info_actions = '
                    <table class="cmcal-calendars-table">
                        <tbody>
                            <tr>
                                <td>' . $delete_btn . $update_btn . '</td>
                            </tr>
                        <tbody>
                    </table>';

                $group_field_id = $cmb->add_field(array(
                    'id' => $id_prefix . 'calendar_settings',
                    'type' => 'group',
                    'repeatable' => false, // use false if you want non-repeatable group
                    'options' => array(
                        'group_title' => $info_group, // since version 1.1.4, {#} gets replaced by row number
                        'closed' => true, // true to have the groups closed by default
                    ),
                ));

                $post_type = cmcal_setup_get_option(cmcal_setup_instance()->option_prefix . "post_type");

                if ($post_type) {
                    $cmb->add_group_field($group_field_id, array(
                        'name' => esc_html__('Posts included as events', 'calendar-anything'),
                        'type' => 'title',
                        'id' => 'posts_included_title',
                        'classes' => 'cmcal_colorize_title',
                    ));
                    foreach ($post_type as $key) {
                        $cmb->add_group_field($group_field_id, array(
                            'name' => esc_html__('Post type: ', 'calendar-anything') . $key,
                            'id' => $key . 'posts_included',
                            'type' => 'radio',
                            'default' => 'all',
                            'show_option_none' => false,
                            'options' => array(
                                'all' => esc_html__('All posts', 'calendar-anything'),
                                'selected' => esc_html__('Only posts attached to calendar', 'calendar-anything'),
                                'none' => esc_html__('None', 'calendar-anything'),
                            ),
                        ));
                    }
                }

                $cmb->add_group_field($group_field_id, array(
                    'name' => esc_html__('Event General Options', 'calendar-anything'),
                    'type' => 'title',
                    'id' => 'event_general_options_title',
                    'classes' => 'cmcal_colorize_title',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'name' => esc_html__('Event on click', 'calendar-anything'),
                    'id' => 'event_general_action_on_click',
                    'type' => 'radio',
                    'default' => 'goto_permalink',
                    'show_option_none' => false,
                    'options' => array(
                        'goto_permalink' => esc_html__('Go to Permalink', 'calendar-anything'),
                        'custom' => esc_html__('Manage click for each event separately', 'calendar-anything'),
                    ), 'closed' => true
                ));

                $cmb->add_group_field($group_field_id, array(
                    'id' => 'event_general_url_new_window',
                    'name' => esc_html__('Open url in new window?', 'calendar-anything'),
                    'desc' => esc_html__('Applied only if "Event on click" is set to "Go to Permalink"', 'calendar-anything'),
                    'type' => 'radio',
                    'options' => array(
                        'true' => esc_html__('Yes', 'calendar-anything'),
                        'false' => esc_html__('No', 'calendar-anything'),
                    ),
                    'default' => 'false',
                ));

                //Dates Range
                $number_options = array();
                $number_options[] = '';
                for ($x = 0; $x <= 30; $x++) {
                    $number_options[$x] = $x;
                }
                $date_type = array(
                    '' => '',
                    'days' => esc_html__('Days', 'calendar-anything'),
                    'weeks' => esc_html__('Weeks', 'calendar-anything'),
                    'months' => esc_html__('Months', 'calendar-anything'),
                    'years' => esc_html__('Years', 'calendar-anything'),
                );
                $cmb->add_group_field($group_field_id, array(
                    'name' => esc_html__('Date Range', 'calendar-anything'),
                    'desc' => esc_html__('Only events within Start and End date will be displayed.', 'calendar-anything'),
                    'type' => 'title',
                    'id' => 'date_restriction_title',
                    'classes' => 'cmcal_colorize_title',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'id' => 'events_date_range_navigation_type',
                    'name' => esc_html__('Navigate button for dates out of range', 'calendar-anything'),
                    'type' => 'radio',
                    'default' => 'disabled_buttons',
                    'options' => array(
                        "enabled_buttons" => esc_html__('Enabled', 'calendar-anything'),
                        "disabled_buttons" => esc_html__('Disabled', 'calendar-anything'),
                        "hidden_buttons" => esc_html__('Hidden', 'calendar-anything'),
                    ),
                ));
                $cmb->add_group_field($group_field_id, array(
                    'name' => esc_html__('Start Date: ', 'calendar-anything'),
                    'type' => 'title',
                    'id' => 'events_min_date_title',
                    'classes' => 'cmcal-inline-cmb2',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'id' => 'events_min_date_number',
                    'type' => 'select',
                    'default' => '',
                    'options' => $number_options,
                    'classes' => 'cmcal-inline-cmb2',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'id' => 'events_min_date_type',
                    'type' => 'select',
                    'default' => '',
                    'options' => $date_type,
                    'desc' => esc_html__('before current date.', 'calendar-anything'),
                    'classes' => 'cmcal-inline-cmb2',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'type' => 'title',
                    'id' => 'events_min_date_title_after',
                    'classes' => 'cmcal-range-dummy-item',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'name' => esc_html__('End Date: ', 'calendar-anything'),
                    'type' => 'title',
                    'id' => 'events_max_date_title',
                    'classes' => 'cmcal-inline-cmb2',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'id' => 'events_max_date_number',
                    'type' => 'select',
                    'default' => '',
                    'options' => $number_options,
                    'classes' => 'cmcal-inline-cmb2',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'id' => 'events_max_date_type',
                    'desc' => esc_html__('after current date.', 'calendar-anything'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $date_type,
                    'classes' => 'cmcal-inline-cmb2',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'type' => 'title',
                    'id' => 'events_max_date_title_after',
                    'classes' => 'cmcal-range-dummy-item',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'name' => esc_html__('Rendering', 'calendar-anything'),
                    'type' => 'title',
                    'id' => 'rendering_title',
                    'classes' => 'cmcal_colorize_title',
                ));
                $cmb->add_group_field($group_field_id, array(
                    'name' => esc_html__('Event Rendering', 'calendar-anything'),
                    'id' => 'event_rendering',
                    'type' => 'radio',
                    'default' => 'prerender',
                    'show_option_none' => false,
                    'options' => array(
                        'prerender' => esc_html__('Prerender', 'calendar-anything'),
                        'ajax' => esc_html__('Ajax', 'calendar-anything'),
                    ), 'closed' => true
                ));
                $cmb->add_group_field($group_field_id, array(
                    'name' => esc_html__('Event Caching', 'calendar-anything'),
                    'id' => 'event_cashing',
                    'type' => 'radio',
                    'default' => 'no',
                    'show_option_none' => false,
                    'options' => array(
                        'yes' => esc_html__('Enabled', 'calendar-anything'),
                        'no' => esc_html__('Disabled', 'calendar-anything'),
                    ),
                ));
                $cmb->add_group_field($group_field_id, array(
                    'name' => esc_html__('iCal Feed', 'calendar-anything'),
                    'type' => 'title',
                    'id' => 'ical_feed_title',
                    'classes' => 'cmcal_colorize_title',
                ));

                $ical_feed_url = '
                    <div>iCal feed URL</div>
                    <pre>' . get_feed_link('cmcal-ical') . '?calendar-id=' . esc_attr($term->term_id) . '</pre>
                    <p>Get Feed for iCal (Google Calendar). This is for subscribing to the events in the Calendar. Add this URL to either iCal (Mac) or Google Calendar, or any other calendar that supports iCal Feed.</p>
                    <p><em>Make sure that you save permalinks first.</em></p>
                    <a href="' . get_feed_link('cmcal-ical') . '?file&calendar-id=' . esc_attr($term->term_id) . '" class="button-primary">Download ICS file</a>';

                $cmb->add_group_field($group_field_id, array(
                    'name' => $ical_feed_url,
                    'id' => 'ical_feed_url',
                    'type' => 'title',
                    'classes' => 'ical_holder',
                ));

                $cmb->add_group_field($group_field_id, array(
                    'name' => $info_actions,
                    'id' => $this->option_prefix . 'calendar_actions',
                    'type' => 'title',
                ));
            }
        }
    }

    /**
     * Register settings notices for display
     *
     * @since  0.1.0
     * @param  int   $object_id Option key
     * @param  array $updated   Array of updated fields
     * @return void
     */
    public function settings_notices($object_id, $updated) {
        if ($object_id !== $this->key || empty($updated)) {
            return;
        }
        add_settings_error($this->key . '-notices', '', esc_html__('Settings updated.', 'cmcal_setup_calendars'), 'updated');
        settings_errors($this->key . '-notices');
    }

    /**
     * Public getter method for retrieving protected/private variables
     * @since  0.1.0
     * @param  string  $field Field to retrieve
     * @return mixed          Field value or exception is thrown
     */
    public function __get($field) {
        // Allowed fields to retrieve
        if (in_array($field, array('key', 'metabox_id', 'title', 'options_page'), true)) {
            return $this->{$field};
        }
        throw new Exception('Invalid property: ' . $field);
    }

}

/**
 * Helper function to get/return the CMCAL_Setup_Calendars object
 * @since  0.1.0
 * @return CMCAL_Setup_Calendars object
 */
function cmcal_setup_calendars_instance() {
    return Cmcal_Setup_Calendars::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function cmcal_setup_calendars_get_option($key = '', $default = null) {
    if (function_exists('cmb2_get_option')) {
        // Use cmb2_get_option as it passes through some key filters.
        return cmb2_get_option(cmcal_setup_calendars_instance()->key, $key, $default);
    }
    // Fallback to get_option if CMB2 is not loaded yet.
    $opts = get_option(cmcal_setup_calendars_instance()->key, $key, $default);
    $val = $default;
    if (!is_array($opts))
        $opts = array($opts);
    if ('all' == $key) {
        $val = $opts;
    } elseif (array_key_exists($key, $opts) && false !== $opts[$key]) {
        $val = $opts[$key];
    }
    return $val;
}

// Get it started
cmcal_setup_calendars_instance();
