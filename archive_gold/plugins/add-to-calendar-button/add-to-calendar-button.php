<?php
/**
 * Plugin Name:       Add to Calendar Button
 * Plugin URI:        https://add-to-calendar-button.com
 * Description:       Create beautiful buttons, where people can add events to their calendars.
 * Version:           1.2.9
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            Jens Kuerschner
 * Author URI:        https://add-to-calendar-button.com
 * License:           GPLv3 or later
 * Text Domain:       add-to-calendar-button
 *
 * @package add-to-calendar-button
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 3
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

Mind that while this plugin is licensed under the GPLv3 licsense,
the underlying script to generate the buttons is licensed under
the  Elastic License 2.0 (ELv2). They are compatible for regular
use, but you are not allowed to rework the core script and 
provide the product (generating an add-to-calendar-button) to 
others as a managed service.
*/


$parentScriptVersion = '2.2.9';
$pluginVersion = '1.2.9';

// load button script
function atcb_enqueue_script() {
  global $parentScriptVersion;
  wp_enqueue_script( 'add-to-calendar-button', plugins_url('lib/atcb.js', __FILE__), '', $parentScriptVersion, true );
}
// ...on the website
add_action( 'wp_enqueue_scripts', 'atcb_enqueue_script' );
// ...as well as the admin panel
add_action( 'admin_enqueue_scripts', 'atcb_enqueue_script' );

// define shortcode
function atcb_shortcode_func( $atts ) {
    $output = '<add-to-calendar-button';
    foreach ( $atts as $key => $value ) {
      if ( is_numeric($key) ) {
        $output .= ' ' . esc_attr( $value );
      } else {
        $output .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
      }
    }
    $output .= '></add-to-calendar-button>';
    return $output;
}
add_shortcode( 'add-to-calendar-button', 'atcb_shortcode_func' );

// define Gutenberg block
function atcb_register_block() {
	global $pluginVersion;
  // register the block script
  wp_register_script( 'atcb-block', plugins_url('block.js', __FILE__), array('wp-blocks', 'wp-block-editor', 'wp-element'), $pluginVersion, true );
  // register the actual block
  register_block_type( 'add-to-calendar/button', array('editor_script' => 'atcb-block') );
  // add i18n
  load_plugin_textdomain( 'add-to-calendar-button', false, dirname(plugin_basename( __FILE__ )) . '/languages' );
  $locale = get_Locale();
  $language = explode( '_', $locale )[0];
  wp_localize_script(
    'atcb-block',
    'atcbI18nObj',
    [
      'language' => $language,
      'description' => __("Creates a button that adds an event to the user's calendar.", 'add-to-calendar-button'),
      'default' =>  [
        'title' => __("My Event Title", 'add-to-calendar-button'),
        'location' => __("World Wide Web", 'add-to-calendar-button'),
      ],
      'keywords' => [
        'k1' => __("Calendar", 'add-to-calendar-button'),
        'k2' => __("save", 'add-to-calendar-button'),
        'k3' => __("Date", 'add-to-calendar-button'),
        'k4' => __("Appointment", 'add-to-calendar-button')
      ],
      'label' => __("Attributes", 'add-to-calendar-button'),
      'help' => __("Click here for documentation", 'add-to-calendar-button'),
      'note' => __("Mind that the interaction with the button is blocked in edit mode", 'add-to-calendar-button')
    ]
  );
}
add_action( 'init', 'atcb_register_block' );

// set custom plugin links
function pluginDetailsLinks($links, $file) {
  if ($file == basename(dirname(__FILE__)) . '/' . basename(__FILE__)) {
    $locale = get_Locale();
    $language = explode( '_', $locale )[0];
    $supportedLanguages = ['en', 'de'];
    if ($language == 'en' or !in_array($language, $supportedLanguages)) {
      $language = '';
    } else {
      $language .= '/';
    }
    $links[] = '<a href="https://add-to-calendar-button.com/' . $language . 'configuration" target="_blank" rel="noopener">' . __("Configuration Options", 'add-to-calendar-button') . '</a>';
  }
  return $links;
}
add_filter('plugin_row_meta', 'pluginDetailsLinks', 10, 2);
