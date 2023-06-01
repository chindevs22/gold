<?php

function cmcal_setup_post_type_is_set() {
    $post_type = cmcal_setup_get_option(cmcal_setup_instance()->option_prefix . "post_type");
    return !empty($post_type);
}

function cmcal_cmb2_metabox_form($metabox_id, $key) {
    if (cmcal_setup_post_type_is_set()) {
        cmb2_metabox_form($metabox_id, $key);
    } else {
        $redirect_admin_page = admin_url('admin.php?page=cmcal_setup');
        echo '<div class="cmcal_notice">' . sprintf( __( 'You must first define a post type for your calendar. To do so, go <a href="%s">here</a>.', 'calendar-anything' ), $redirect_admin_page ) . '</a></div>';
    }
}
