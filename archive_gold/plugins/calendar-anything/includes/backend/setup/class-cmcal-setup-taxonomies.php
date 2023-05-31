<?php

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class Cmcal_Setup_Taxonomies {

    /**
     * Option key, and option page slug
     * @var string
     */
    protected $key = 'cmcal_setup_taxonomies_options';

    /**
     * Option key, and option page slug
     * @var string
     */
    public $option_prefix = 'cmcal_setup_taxonomies_';

    /**
     * Options page metabox id
     * @var string
     */
    protected $metabox_id = 'cmcal_setup_taxonomies_metabox';

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
     * @var CMCAL_Setup_Taxonomies
     */
    protected static $instance = null;

    /**
     * Returns the running object
     *
     * @return CMCAL_Setup_Taxonomies
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
        $key = cmcal_setup_get_option(cmcal_setup_instance()->option_prefix . "post_type");
        if ($key)
            add_action("cmb2_save_field_" . $this->option_prefix . $key[0] . "taxonomy", array($this, 'action_cmb2_save_field_field_id'), 10, 3);
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
        $this->title = esc_html__('Taxonomies', 'calendar-anything');
        $this->options_page = add_submenu_page('cmcal_setup', $this->title, $this->title, $capability, 'cmcal_setup_taxonomies', array($this, 'admin_page_display'));
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


                $post_types = get_post_types();
                $emptyElement = array('' => '');
                $default_post_type_is_set = array_key_exists(CMCAL()->default_event_post_type, $post_types);
                if (!$default_post_type_is_set) {
                    $post_types = array(CMCAL()->default_event_post_type => CMCAL()->default_event_post_type) + $post_types;
                }

                if (!$default_post_type_is_set && $key == CMCAL()->default_event_post_type) {
                    $taxonomy_objects[CMCAL()->default_event_taxonomy_category] = CMCAL()->default_event_taxonomy_category;
                } else {
                    $taxonomy_objects = get_object_taxonomies($key, 'names');
                    $taxonomy_objects = array_combine($taxonomy_objects, $taxonomy_objects);
                }
                unset($taxonomy_objects["cmcal_calendar"]);
                unset($taxonomy_objects["cmcal_calendar" . "_" . $key]);
                if (empty($taxonomy_objects) && $key == CMCAL()->default_event_post_type) {
                    $taxonomy_objects[CMCAL()->default_event_taxonomy_category] = CMCAL()->default_event_taxonomy_category;
                }
                $cmb->add_field(array(
                    'name' => esc_html__('Basic Taxonomy of Event', 'calendar-anything'),
                    'desc' => esc_html__('The taxonomy used in Color Fields', 'calendar-anything'),
                    'id' => $this->option_prefix . $key . 'taxonomy',
                    'type' => 'select',
                    'options' => $emptyElement + $taxonomy_objects,
                    'default' => '',
                ));
                if (!empty($taxonomy_objects))
                    $cmb->add_field(array(
                        'name' => esc_html__('Taxonomies for Event Template/Filter ', 'calendar-anything'),
                        'id' => $this->option_prefix . $key . 'taxonomies_event_template_filter',
                        'type' => 'multicheck',
                        'select_all_button' => false,
                        'options' => $taxonomy_objects,
                        'after_row' => '</div>' // close div for styling purposes
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
 * Helper function to get/return the CMCAL_Setup_Taxonomies object
 * @since  0.1.0
 * @return CMCAL_Setup_Taxonomies object
 */
function cmcal_setup_taxonomies_instance() {
    return Cmcal_Setup_Taxonomies::get_instance();
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function cmcal_setup_taxonomies_get_option($key = '', $default = null) {
    if (function_exists('cmb2_get_option')) {
        // Use cmb2_get_option as it passes through some key filters.
        return cmb2_get_option(cmcal_setup_taxonomies_instance()->key, $key, $default);
    }
    // Fallback to get_option if CMB2 is not loaded yet.
    $opts = get_option(cmcal_setup_taxonomies_instance()->key, $key, $default);
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
cmcal_setup_taxonomies_instance();
