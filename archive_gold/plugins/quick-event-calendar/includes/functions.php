<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function qcc_show_future_posts($posts) {
    global $wp_query, $wpdb;

    // Detect cases where WP usually shows 404
    if (is_single() && empty($posts)) {
        $posts = $wpdb->get_results($wp_query->request);

        // Make sure it only affects future posts, not trashed
        if (isset($posts[0]->post_status) && $posts[0]->post_status !== 'future') {
            $posts = [];
        }
    }

    return $posts;
}

function qcc_get_calendar() {
    global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

    $out = '<div class="qcc-calendar-container">
        <div class="qcc-calendar">
            <div class="hd">
                <h2 class="month"></h2>
                <a class="qcc-btn-prev" href="#"><span class="dashicons dashicons-arrow-left-alt2"></span></a>
                <a class="qcc-btn-next" href="#"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
            </div>
            <table>
                <thead>
                    <tr></tr>
                </thead>
                <tbody>
                    <tr class="1"></tr>
                    <tr class="2"></tr>
                    <tr class="3"></tr>
                    <tr class="4"></tr>
                    <tr class="5"></tr>
                </tbody>
            </table>

            <div class="qcc-list">';
                $qcc_post_type = get_option('qcc_post_type');
                $qcc_category = get_option('qcc_category');
                $qcc_show_published = get_option('qcc_show_published');
                $qcc_show_scheduled = get_option('qcc_show_scheduled');

                $qcc_publish = '';
                $qcc_schedule = '';
                if ((int) $qcc_show_published === 1) {
                    $qcc_publish = 'publish';
                }
                if ((int) $qcc_show_scheduled === 1) {
                    $qcc_schedule = 'future';
                }

                if ((string) $qcc_post_type === 'post') {
                    $args = [
                        'posts_per_page' => 1000,
                        'post_type' => $qcc_post_type,
                        'post_status' => [$qcc_publish, $qcc_schedule],
                        'category__in' => $qcc_category,
                        'orderby' => 'date'
                    ];
                } else {
                    $args = [
                        'posts_per_page' => 1000,
                        'post_type' => $qcc_post_type,
                        'post_status' => [$qcc_publish, $qcc_schedule],
                        'orderby' => 'date'
                    ];
                }
                $the_query = new WP_Query($args);

                $qcc_use_date_meta = get_option('qcc_use_date_meta');

                if ($the_query->have_posts()) {
                    while ($the_query->have_posts()) {
                        $the_query->the_post();

                        if ((int) $qcc_use_date_meta === 1) {
                            $qcc_date_meta_month = get_option('qcc_date_meta_month');
                            $qcc_date_meta_day = get_option('qcc_date_meta_day');

                            $month = get_post_meta(get_the_ID(), $qcc_date_meta_month, true);
                            $day = get_post_meta(get_the_ID(), $qcc_date_meta_day, true);
                            $event_date_string = date('Y') . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $day);
                            $event_date = date('l, F j Y', strtotime($event_date_string));
                        } else {
                            $month = get_the_time('n');
                            $day = get_the_time('j');
                            $year = get_the_time('Y');
                            $event_date = get_the_time('l, F j Y');
                        }

                        $out .= '<div class="qcc-day-event" date-year="' . $year . '" date-month="' . $month . '" date-day="' . $day . '">
                            <a href="#" class="qcc-close"><span class="dashicons dashicons-no"></span></a>
                            <h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>
                            <div class="qcc-day-event-date">' . $event_date . '</div>
                            ' . get_the_excerpt() . '
                        </div>';
                    }
                }

            $out .= '</div>
        </div>
    </div>';

    return $out;
}

function qcc_css() {
    $qcc_accent_colour = get_option('qcc_accent_colour');
    $qcc_day_colour = get_option('qcc_day_colour');
    $qcc_event_colour = get_option('qcc_event_colour');

    $out = '<style>
    .qcc-calendar-container .qcc-calendar tbody td:hover,
    .qcc-calendar-container .qcc-current-day {
        background-color: ' . $qcc_accent_colour . ';
    }
    .qcc-calendar-container .qcc-event:after {
        background-color: ' . $qcc_event_colour . ';
    }
    .qcc-calendar-container .qcc-btn-prev,
    .qcc-calendar-container .qcc-btn-next {
        color: ' . $qcc_accent_colour . ';
    }';

    $qcc_theme = get_option('qcc_theme');

    if ((int) $qcc_theme === 3) {
        $out .= '.qcc-calendar-container .qcc-event:after {
            background: none;
            border: 1px solid ' . $qcc_event_colour . ';
        }
        .qcc-calendar-container thead {
            border-bottom: 1px solid #cbd1d2;
        }';
    } else if ((int) $qcc_theme === 4) {
        $out .= '.qcc-calendar-container table {
            border-top: 1px solid #cbd1d2;
            border-right: 1px solid #cbd1d2;
        }
        .qcc-calendar td {
            border-bottom: 1px solid #cbd1d2;
            border-left: 1px solid #cbd1d2;
        }
        .qcc-calendar thead td {
            background-color: rgba(0, 0, 0, 0.05);
        ';
    }

    $out .= '</style>';

    echo $out;
}

function qcc_get_submission_form($atts) {
    extract(shortcode_atts([
        'name' => ''       
    ], $atts));

    $qcc_post_type = get_option('qcc_post_type');
    $qcc_category = get_option('qcc_category');

    if (isset($_POST['qcc_new_event_submit']) && wp_verify_nonce($_POST['qcc_new_event_field'], 'qcc_new_event_action')) {
        $qcc_title = sanitize_text_field($_POST['qcc_title']);
        $qcc_title_slug = sanitize_title($_POST['qcc_title']);
        $qcc_date = sanitize_text_field($_POST['qcc_date']);
        $qcc_description = sanitize_text_field($_POST['qcc_description']);

        // ADD THE FORM INPUT TO $new_post ARRAY
        $new_post = [
            'post_title' 	=> $qcc_title,
            'post_name' 	=> $qcc_title_slug,
            'post_content' 	=> $qcc_description,
            'post_category'	=> [$qcc_category], // Usable for custom taxonomies too
            'post_status' 	=> 'publish', // Choose: publish, preview, future, draft, etc.
            'post_type' 	=> $qcc_post_type,
            'post_date' 	=> $qcc_date . ' 12:00:00',
        ];

        // SAVE THE POST
        $pid = wp_insert_post($new_post);

        do_action('wp_insert_post', 'wp_insert_post');

        echo '<p>' . __('Event submitted successfully!', 'qcc') . '</p>';
    }

    $display = '<form method="post" class="qcc-form">
        <p>
            <input type="text" size="48" name="qcc_date" id="qcc-datepicker" placeholder="' . __('Date', 'qcc') . '" required>
        </p>

        <p>
            <input type="text" size="48" name="qcc_title" placeholder="' . __('Title', 'qcc') . '" required>
        </p>

        <p>
            <textarea name="qcc_description" rows="12" cols="80" placeholder="' . __('Description', 'qcc') . '"></textarea>
        </p>

        <p>
            <input id="submit" name="qcc_new_event_submit" type="submit" value="' . __('Add', 'qcc') . '">
        </p>
        ' . wp_nonce_field('qcc_new_event_action', 'qcc_new_event_field', true, false) . '
    </form>';

    return $display;
}
