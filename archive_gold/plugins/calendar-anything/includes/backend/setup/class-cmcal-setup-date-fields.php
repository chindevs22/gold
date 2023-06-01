<?php

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class Cmcal_Setup_Date_Fields {

    /**
     * Option key, and option page slug
     * @var string
     */
    protected $key = 'cmcal_setup_date_fields_options';

    /**
     * Option key, and option page slug
     * @var string
     */
    public $option_prefix = 'cmcal_setup_date_fields_';

    /**
     * Options page metabox id
     * @var string
     */
    protected $metabox_id = 'cmcal_setup_date_fields_metabox';

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
     * @var CMCAL_Setup_Date_Fields
     */
    protected static $instance = null;

    /**
     * Returns the running object
     *
     * @return CMCAL_Setup_Date_Fields
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
        add_action("cmb2_save_field_" . $this->option_prefix . "start_end_dates_mode", array($this, 'action_cmb2_save_field_field_id'), 10, 3);
    }

    function action_cmb2_save_field_field_id($updated, $action, $instance) {
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
        $this->title = esc_html__('Date Fields', 'calendar-anything');
        $this->options_page = add_submenu_page('cmcal_setup', $this->title, $this->title, $capability, 'cmcal_setup_date_fields', array($this, 'admin_page_display'));
        // Include CMB CSS in the head to avoid FOUC
        add_action("admin_print_styles-{$this->options_page}", array('CMB2_hookup', 'enqueue_cmb_css'));
    }

    /**
     * Admin page markup. Mostly handled by CMB2
     * @since  0.1.0
     */
    public function admin_page_display() {
        ?>
        <div class="wrap cmb2-options-page <?php echo esc_html($this->key); ?>">
            <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
            <?php cmcal_cmb2_metabox_form($this->metabox_id, $this->key); ?>
        </div>
        <?php
    }

    /**
     * Add the options metabox to the array of metaboxes
     * @since  0.1.0
     */
    function add_options_page_metabox() {
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

        $post_type = cmcal_setup_get_option(cmcal_setup_instance()->option_prefix . "post_type");

        if ($post_type) {
            foreach ($post_type as $key) {
                $cmb->add_field(array(
                    'name' => esc_html__('Post type', 'calendar-anything') . ': ' . $key,
                    'type' => 'title',
                    'id' => $this->option_prefix . $key . 'date_section_title',
                    'before_row' => '<div class="post_type_row">' // open div for styling purposes
                ));

                $cmb->add_field(array(
                    'name' => esc_html__('Event Start/End Dates', 'calendar-anything'),
                    'id' => $this->option_prefix . $key . 'start_end_dates_mode',
                    'type' => 'radio',
                    'default' => 'publish_date',
                    'show_option_none' => false,
                    'options' => array(
                        'publish_date' => esc_html__('Publish Date', 'calendar-anything'),
                        'codemine_fields' => esc_html__('Custom Fields (4 new custom fields - start date, start time, end date & and time - will be created inside each post of your selected post type)', 'calendar-anything'),
                        'meta_keys' => esc_html__('Meta Keys (If you use an event plugin, you can set the plugin\'s start/end date/time)', 'calendar-anything'),
                    ),
                ));
                $meta_keys = $this->get_meta_keys($key);
                if (!empty($meta_keys)) {
                    $cmb->add_field(array(
                        'name' => esc_html__('Start Date from Meta key', 'calendar-anything'),
                        'id' => $this->option_prefix . $key . 'start_date_meta_key',
                        'type' => 'select',
                        'options' => $meta_keys,
                        'attributes' => array(
                            'data-conditional-id' => $this->option_prefix . $key . 'start_end_dates_mode',
                            'data-conditional-value' => 'meta_keys',
                        ),
                    ));
                    $cmb->add_field(array(
                        'name' => esc_html__('Start Time from Meta key', 'calendar-anything'),
                        'id' => $this->option_prefix . $key . 'start_time_meta_key',
                        'type' => 'select',
                        'options' => $meta_keys,
                        'attributes' => array(
                            'data-conditional-id' => $this->option_prefix . $key . 'start_end_dates_mode',
                            'data-conditional-value' => 'meta_keys',
                        ),
                    ));
                    $cmb->add_field(array(
                        'name' => esc_html__('End Date from Meta key', 'calendar-anything'),
                        'id' => $this->option_prefix . $key . 'end_date_meta_key',
                        'type' => 'select',
                        'options' => $meta_keys,
                        'attributes' => array(
                            'data-conditional-id' => $this->option_prefix . $key . 'start_end_dates_mode',
                            'data-conditional-value' => 'meta_keys',
                        ),
                    ));
                    $cmb->add_field(array(
                        'name' => esc_html__('End Time from Meta key', 'calendar-anything'),
                        'id' => $this->option_prefix . $key . 'end_time_meta_key',
                        'type' => 'select',
                        'options' => $meta_keys,
                        'attributes' => array(
                            'data-conditional-id' => $this->option_prefix . $key . 'start_end_dates_mode',
                            'data-conditional-value' => 'meta_keys',
                        ),
                    ));
                }

                $cmb->add_field(array(
                    'name' => esc_html__('Event All Day', 'calendar-anything'),
                    'id' => $this->option_prefix . $key . 'all_day_enabled',
                    'type' => 'radio',
                    'default' => 'no',
                    'options' => array(
                        'no' => esc_html__('Disabled', 'calendar-anything'),
                        'all_events_all_day' => esc_html__('All Events are All Day', 'calendar-anything'),
                        'codemine_field' => esc_html__('All Day from codemine field', 'calendar-anything'),
                        'meta_key' => esc_html__('All Day from meta key', 'calendar-anything'),
                    ),
                ));
                if (!empty($meta_keys)) {
                    $cmb->add_field(array(
                        'name' => esc_html__('All Day from Meta key', 'calendar-anything'),
                        'id' => $this->option_prefix . $key . 'all_day_meta_key',
                        'type' => 'select',
                        'options' => $meta_keys,
                        'attributes' => array(
                            'data-conditional-id' => $this->option_prefix . $key . 'all_day_enabled',
                            'data-conditional-value' => 'meta_key',
                        ),
                    ));
                    $cmb->add_field(array(
                        'name' => esc_html__('Meta key value for All Day', 'calendar-anything'),
                        'id' => $this->option_prefix . $key . 'all_day_meta_key_value',
                        'type' => 'text',
                        'attributes' => array(
                            'data-conditional-id' => $this->option_prefix . $key . 'all_day_enabled',
                            'data-conditional-value' => 'meta_key',
                        ),
                    ));
                }

                $cmb->add_field(array(
                    'name' => esc_html__('Event Repetition', 'calendar-anything'),
                    'id' => $this->option_prefix . $key . 'repetition_enabled',
                    'type' => 'radio',
                    'default' => 'no',
                    'options' => array(
                        'yes' => esc_html__('Enabled', 'calendar-anything'),
                        'no' => esc_html__('Disabled', 'calendar-anything'),
                    ),
                    'after_row' => '</div>' // close div for styling purposes
                ));
            }
        }
    }

    function generate_meta_keys($post_type) {
        global $wpdb;

        $query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key) 
        FROM $wpdb->posts 
        LEFT JOIN $wpdb->postmeta 
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
        WHERE $wpdb->posts.post_type = '%s' 
        AND $wpdb->postmeta.meta_key != '' 
        AND $wpdb->postmeta.meta_key NOT LIKE 'codemine_event%%'
    ";
        $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
        return array_combine($meta_keys, $meta_keys);
        ;
    }

    //To get a list of meta_keys in your template for generating your dropdown, use the following function:
    function get_meta_keys($post_type) {
        $cache = get_transient($post_type . '_meta_keys');
        $meta_keys = $cache ? $cache : $this->generate_meta_keys($post_type);
        return $meta_keys;
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
        add_settings_error($this->key . '-notices', '', esc_html__('Settings updated.', 'calendar-anything'), 'updated');
        settings_errors($this->key . '-notices');


        header('Location: ' . $_SERVER['REQUEST_URI']);
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
 * Helper function to get/return the CMCAL_Setup_Date_Fields object
 * @since  0.1.0
 * @return CMCAL_Setup_Date_Fields object
 */
function cmcal_setup_date_fields_instance() {
    return Cmcal_Setup_Date_Fields::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function cmcal_setup_date_fields_get_option($key = '', $default = null) {
    if (function_exists('cmb2_get_option')) {
        // Use cmb2_get_option as it passes through some key filters.
        return cmb2_get_option(cmcal_setup_date_fields_instance()->key, $key, $default);
    }
    // Fallback to get_option if CMB2 is not loaded yet.
    $opts = get_option(cmcal_setup_date_fields_instance()->key, $key, $default);
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
cmcal_setup_date_fields_instance();
