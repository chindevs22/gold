require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) {
    // Plugin is not active, display an error message or redirect the user
    echo 'The required plugin is not active. Please activate it first.';
    return;
}

class User_Events extends Tribe__Tickets__Shortcodes__User_Event_Confirmation_List {
    protected $params;

    public function set_params( $params ) {
        $this->params = shortcode_atts( array(
            'limit' => -1,
            'user'  => get_current_user_id()
        ), $params, $this->shortcode_name );
    }

    public function generate( $params ) {
        $this->set_params( $params );

        ob_start();

        if ( ! is_user_logged_in() ) {
            include Tribe__Tickets__Templates::get_template_hierarchy( 'shortcodes/my-attendance-list-logged-out' );
        } else {
            return $this->generate_attendance_list();
        }
    }

    protected function generate_attendance_list() {
        $event_ids = $this->get_upcoming_attendances( $this->params );
        return $event_ids;
        include Tribe__Tickets__Templates::get_template_hierarchy( 'shortcodes/my-attendance-list' );
    }
}
