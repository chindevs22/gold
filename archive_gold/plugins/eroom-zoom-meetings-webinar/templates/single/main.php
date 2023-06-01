<?php
if ( ! empty( $_GET['show_meeting'] ) ) {
    include get_zoom_template( 'single/meeting_view.php' );
} elseif ( ! empty( $_GET['ical_export'] ) && !empty( get_the_ID() ) ) {
    header( 'Content-type: text/calendar; charset=utf-8' );
    header( 'Content-Disposition: inline; filename=calendar_'.get_the_ID().'.ics' );
    echo stm_eroom_generate_ics_calendar();
    exit();
} else {
    get_header();
    echo do_shortcode( '[stm_zoom_conference post_id="' . get_the_ID() . '" hide_content_before_start=""]' );
    get_footer();
}
