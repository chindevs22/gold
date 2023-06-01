<?php
/*
Plugin Name: Quick Event Calendar
Plugin URI: https://getbutterfly.com/wordpress-plugins/quick-event-calendar/
Description: Quick Event Calendar is a very simple, performant and WordPress-integrated event calendar plugin. Quick Event Calendar allows you to add posts, events or any other custom post type to a flexible, responsive calendar which can be placed in a post, page or widget.
Version: 1.4.1
Author: Ciprian Popescu
Author URI: https://getbutterfly.com/
Text Domain: quick-event-calendar

Copyright 2013-2021 Ciprian Popescu (email: getbutterfly@gmail.com)

Datepicker.js Copyright (c) 2014-2016 Fengyuan Chen

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

//
define('QEC_PLUGIN_URL', WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)));
define('QEC_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));
define('QEC_VERSION', '1.4.1');

add_shortcode('qcc-calendar', 'qcc_get_calendar');
add_shortcode('qcc-form', 'qcc_get_submission_form');

// plugin initialization
function qcc_init() {
    load_plugin_textdomain('qcc', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'qcc_init');

function qcc_load_calendar_style() {
    wp_enqueue_style('dashicons');

    $qcc_theme = (int) get_option('qcc_theme');

    if ((int) $qcc_theme === 0) {
        wp_enqueue_style('calendar', QEC_PLUGIN_URL . '/css/calendar.css', [], QEC_VERSION);
    } else if ((int) $qcc_theme === 1) {
        wp_enqueue_style('calendar', QEC_PLUGIN_URL . '/css/calendar.css', [], QEC_VERSION);
    } else if ((int) $qcc_theme === 2) {
        wp_enqueue_style('calendar', QEC_PLUGIN_URL . '/css/theme-ubuntu.css', [], QEC_VERSION);
    } else if ((int) $qcc_theme === 3) {
        wp_enqueue_style('calendar', QEC_PLUGIN_URL . '/css/theme-flat.css', [], QEC_VERSION);
    } else if ((int) $qcc_theme === 4) {
        wp_enqueue_style('calendar', QEC_PLUGIN_URL . '/css/theme-bright.css', [], QEC_VERSION);
    } else if ((int) $qcc_theme === 5) {
        wp_enqueue_style('calendar', QEC_PLUGIN_URL . '/css/theme-material.css', [], QEC_VERSION);
    } else if ((int) $qcc_theme === 6) {
        wp_enqueue_style('calendar', QEC_PLUGIN_URL . '/css/theme-freya.css', [], QEC_VERSION);
    } else {
        wp_enqueue_style('calendar', QEC_PLUGIN_URL . '/css/calendar.css', [], QEC_VERSION);
    }

    wp_enqueue_script('qcc-calendar', QEC_PLUGIN_URL . '/calendar.js', ['jquery'], QEC_VERSION);

    wp_enqueue_script('qcc-datepicker', plugins_url('js/datepicker.min.js', __FILE__), ['jquery'], QEC_VERSION, true);
    wp_enqueue_script('qcc-frontend', plugins_url('js/frontend.js', __FILE__), ['qcc-datepicker'], QEC_VERSION, true);
    wp_enqueue_style('qcc-datepicker', plugins_url('css/datepicker.min.css', __FILE__), [], QEC_VERSION);
}

add_action('wp_enqueue_scripts', 'qcc_load_calendar_style');
add_action('wp_head', 'qcc_css');

add_action('admin_menu', 'qcc_calendar_menu');



include QEC_PLUGIN_PATH . '/includes/functions.php';
include QEC_PLUGIN_PATH . '/includes/settings.php';

function qcc_calendar_menu() {
    add_options_page(__('Quick Event Calendar Settings', 'qcc'), __('Quick Event Calendar', 'qcc'), 'manage_options', 'qcc', 'qcc_calendar_admin_page');
}

// enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'qcc_enqueue_scripts');
function qcc_enqueue_scripts($hook_suffix) {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style('gbad', plugins_url('css/gbad.css', __FILE__));

    wp_enqueue_script('qcc-functions', plugins_url('js/functions.js', __FILE__), ['wp-color-picker'], QEC_VERSION, true);
}

function qcc_activate() {
    add_option('qcc_post_type', 'post');

    add_option('qcc_accent_colour', '#2196F3');
    add_option('qcc_day_colour', '#FFFFFF');
    add_option('qcc_event_colour', '#E8345A');
    add_option('qcc_category', 1);
    add_option('qcc_show_published', 1);
    add_option('qcc_show_scheduled', 0);
    add_option('qcc_use_date_meta', 0);
    add_option('qcc_date_meta_month', '');
    add_option('qcc_date_meta_day', '');
    add_option('qcc_theme', 0);
}

function qcc_deactivate() {
    flush_rewrite_rules();
}

function qcc_uninstall() {
    flush_rewrite_rules();

    delete_option('qcc_accent_colour');
    delete_option('qcc_day_colour');
    delete_option('qcc_event_colour');
    delete_option('qcc_post_type');
    delete_option('qcc_category');
    delete_option('qcc_show_published');
    delete_option('qcc_show_scheduled');
    delete_option('qcc_use_date_meta');
    delete_option('qcc_date_meta_month');
    delete_option('qcc_date_meta_day');
    delete_option('qcc_theme');
}

register_activation_hook(__FILE__, 'qcc_activate');
register_deactivation_hook(__FILE__, 'qcc_deactivate');
register_uninstall_hook(__FILE__, 'qcc_uninstall');

$qcc_show_scheduled = get_option('qcc_show_scheduled');

if ((int) $qcc_show_scheduled === 1) {
    add_filter('the_posts', 'qcc_show_future_posts');
}
