<?php

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class Cmcal_Setup_Custom_Fields {

    /**
     * Option key, and option page slug
     * @var string
     */
    protected $key = 'cmcal_setup_custom_fields_options';

    /**
     * Option key, and option page slug
     * @var string
     */
    public $option_prefix = 'cmcal_setup_custom_fields_';

    /**
     * Options page metabox id
     * @var string
     */
    protected $metabox_id = 'cmcal_setup_custom_fields_metabox';

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
     * @var CMCAL_Setup_Custom_Fields
     */
    protected static $instance = null;

    /**
     * Returns the running object
     *
     * @return CMCAL_Setup_Custom_Fields
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
        add_action("cmb2_save_field_" . $this->option_prefix . "custom_fields_group", array($this, 'action_cmb2_save_field_field_id'), 10, 3);
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
        $this->title = esc_html__('Custom Fields', 'calendar-anything');
        $this->options_page = add_submenu_page('cmcal_setup', $this->title, $this->title, $capability, 'cmcal_setup_custom_fields', array($this, 'admin_page_display'));
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

        $group_field_id = $cmb->add_field(array(
            'id' => $this->option_prefix . 'custom_fields_group',
            'type' => 'group',
            'options' => array(
                'group_title' => esc_html__('Custom Field {#}', 'calendar-anything'), // since version 1.1.4, {#} gets replaced by row number
                'add_button' => esc_html__('Add Another Custom Field', 'calendar-anything'),
                'remove_button' => esc_html__('Remove Custom Field', 'calendar-anything'),
                'sortable' => true, // beta
            ),
        ));

        // Id's for group's fields only need to be unique for the group. Prefix is not needed.
        $cmb->add_group_field($group_field_id, array(
            'name' => esc_html__('ID', 'calendar-anything'),
            'id' => 'id',
            'type' => 'text',
        ));
        $cmb->add_group_field($group_field_id, array(
            'name' => esc_html__('Entry Title', 'calendar-anything'),
            'id' => 'title',
            'type' => 'text',
        ));
        $cmb->add_group_field($group_field_id, array(
            'name' => esc_html__('Shortcode', 'calendar-anything'),
            'id' => 'shortcode',
            'type' => 'text',
        ));
        $cmb->add_group_field($group_field_id, array(
            'name' => esc_html__('Type', 'calendar-anything'),
            'id' => 'type',
            'type' => 'select',
            'options' => array(
                "text" => esc_html__('Text', 'calendar-anything'),
                "textarea" => esc_html__('Textarea', 'calendar-anything'),
                "wysiwyg" => esc_html__('WYSIWYG', 'calendar-anything'),
                "file" => esc_html__('File', 'calendar-anything'),
            ),
            'default' => ''
        ));
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
 * Helper function to get/return the CMCAL_Setup_Custom_Fields object
 * @since  0.1.0
 * @return CMCAL_Setup_Custom_Fields object
 */
function cmcal_setup_custom_fields_instance() {
    return Cmcal_Setup_Custom_Fields::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function cmcal_setup_custom_fields_get_option($key = '', $default = null) {
    if (function_exists('cmb2_get_option')) {
        // Use cmb2_get_option as it passes through some key filters.
        return cmb2_get_option(cmcal_setup_custom_fields_instance()->key, $key, $default);
    }
    // Fallback to get_option if CMB2 is not loaded yet.
    $opts = get_option(cmcal_setup_custom_fields_instance()->key, $key, $default);
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
cmcal_setup_custom_fields_instance();
