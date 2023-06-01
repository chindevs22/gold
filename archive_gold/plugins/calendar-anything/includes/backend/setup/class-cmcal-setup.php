<?php

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class Cmcal_Setup {

    /**
     * Option key, and option page slug
     * @var string
     */
    protected $key = 'cmcal_setup_options';

    /**
     * Option key, and option page slug
     * @var string
     */
    public $option_prefix = 'cmcal_setup_';

    /**
     * Options page metabox id
     * @var string
     */
    protected $metabox_id = 'cmcal_setup_metabox';

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
     * @var CMCAL_Setup
     */
    protected static $instance = null;

    /**
     * Returns the running object
     *
     * @return CMCAL_Setup
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
        add_action("cmb2_save_field_" . $this->option_prefix . "post_type", array($this, 'action_cmb2_save_field_field_id'), 10, 3);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    function admin_enqueue_scripts() {
        wp_enqueue_style('cmcal_cmb2', plugins_url('assets/css/cmb2.css', Codemine_Calendar_PLUGIN_FILE), array());
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
        $this->title = esc_html__('Calendar Setup', 'calendar-anything');
        $this->options_page = add_menu_page($this->title, $this->title, $capability, 'cmcal_setup', array($this, 'admin_page_display'), 'dashicons-calendar', 6);
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
            <?php cmb2_metabox_form($this->metabox_id, $this->key); ?>
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

        $post_types = get_post_types();
        // Remove not needed post types
        unset($post_types['revision']);
        unset($post_types['nav_menu_item']);
        unset($post_types['oembed_cache']);
        unset($post_types['custom_css']);
        unset($post_types['customize_changeset']);

        $default_post_type_is_set = array_key_exists(CMCAL()->default_event_post_type, $post_types);
        if (!$default_post_type_is_set) {
            $post_types = array(CMCAL()->default_event_post_type => CMCAL()->default_event_post_type) + $post_types;
        }
        $post_types['cmcal_event'] = $post_types["cmcal_event"] . ' ' . esc_html__('(Calendar Anything Event)', 'calendar-anything');

        $cmb->add_field(array(
            'name' => esc_html__('Post Type as Event', 'calendar-anything'),
            'id' => $this->option_prefix . 'post_type',
            'type' => 'multicheck',
            'options' => $post_types,
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
 * Helper function to get/return the CMCAL_Setup object
 * @since  0.1.0
 * @return CMCAL_Setup object
 */
function cmcal_setup_instance() {
    return Cmcal_Setup::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function cmcal_setup_get_option($key = '', $default = null) {
    if (function_exists('cmb2_get_option')) {
        // Use cmb2_get_option as it passes through some key filters.
        return cmb2_get_option(cmcal_setup_instance()->key, $key, $default);
    }
    // Fallback to get_option if CMB2 is not loaded yet.
    $opts = get_option(cmcal_setup_instance()->key, $key, $default);
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
cmcal_setup_instance();
