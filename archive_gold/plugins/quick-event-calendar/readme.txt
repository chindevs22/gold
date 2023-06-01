=== Quick Event Calendar ===
Contributors: butterflymedia
Tags: calendar, event, date, schedule, year, month
Requires at least: 5.0
Tested up to: 6.0.0
Requires PHP: 7.0
Stable tag: 1.4.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Quick Event Calendar is a very simple, performant and WordPress-integrated event calendar plugin.

== Description ==

Quick Event Calendar is a very simple, performant and WordPress-integrated event calendar plugin. Quick Event Calendar allows you to add posts, events or any other custom post type to a flexible, responsive calendar which can be placed in a post, page or widget.

The calendar works on one page only using AJAX loading. All events (or posts) in one month are displayed under the calendar. It is simple, fast, with no bloated styles and visual scripts. Use the included themes to style your calendar. Use the default or the minimal theme to match your current WordPress theme appearance.

The calendar is feature-packed: works with custom posts, only needs one page, includes predefined themes and is responsive.

Add your own event using the `[qcc-form]` shortcode and display all events using the `[qcc-calendar]` shortcode.

[More WordPress plugins](https://getbutterfly.com/wordpress-plugins/ "More WordPress plugins")

== Installation ==

1. Upload the `qcc-calendar` folder to your `/wp-content/plugins/` directory
2. Activate the plugin via the Plugins menu in WordPress
3. A new Quick Event Calendar menu will appear in WordPress Settings with options and general help

= Help with shortcodes =

Use the `[qcc-calendar]` shortcode in any post, page or widget to show the calendar.
Use the `[qcc-form]` shortcode in any post, page or widget to show the calendar submission form.

= Help with template tags =

Use the `<?php echo do_shortcode("[qcc-calendar]"); ?>` code in any page template to show the calendar.
Use the `<?php echo do_shortcode("[qcc-form]"); ?>` code in any page template to show the calendar submission form.

= Help with styling =

Use the included themes to style your calendar. Use the default or the minimal theme to match your current WordPress theme appearance.

After installing the plugin, you need to configure it by selecting several options:

Select the desired post type, select the desired category (if post type is 'post') and select which posts you want to show. For an event calendar, the default should be both published and scheduled/future posts.

In case you want to use custom meta fields, the calendar works with preexisting meta fields. Using custom meta fields requires a third-party plugin to add new fields.

After selecting the desired options, pick one of the predefined themes to match your site.

After selecting a theme, you need to match your site's colours with the calendar template.

In your Configurator tab, select the accent colour, the event colour and the background colour for the current day.

When you are done with the settings, use the `[qcc-calendar]` shortcode in any post, page or widget to show the calendar.

== Changelog ==

= 1.4.2 =
* TODO: Compare JS to https://github.com/fdut/ContactDevOps/blob/22dd6f38bbd591aeb3a5e5653cf2cdcb066ca146/contact-web/ContactWeb/client/js/transports/calendarController.js
* TODO: Refactor JS to ES6
* TODO: Test form submission and add demo page
* TODO: Reconsider custom date field

= 1.4.1 =
* UPDATE: Updated WordPress compatibility
* UPDATE: Updated readme.txt and added shortcode documentation

= 1.4.0 =
* FIX: Fixed year not going back further than current year
* FIX: Fixed default styles for the submission form
* UPDATE: Updated WordPress compatibility
* UPDATE: Updated PHP 7 compatibility
* UPDATE: Updated PHP 8 compatibility
* UPDATE: Refactored JS

= 1.3.5 =
* UPDATE: Updated WordPress compatibility

= 1.3.4 =
* FIX: Fixed several condition loops
* UPDATE: Updated WordPress compatibility

= 1.3.3 =
* UPDATE: Updated WordPress compatibility
* UPDATE: Updated installation section

= 1.3.2 =
* UPDATE: Updated WordPress compatibility
* FIX: Fixed performance

= 1.3.1 =
* FIX: Fixed readme.txt file
* FIX: Fixed several i18n tags
* FIX: Fixed documentation
* FIX: Security fixes

= 1.3.0 =
* UPDATE: Moved to WordPress.org
* UPDATE: Updated FontAwesome to latest version
* FIX: Security fixes

= 1.2.3 =
* FIX: Fixed conflict with other calendar plugins by using more CSS specificity
* FIX: Moved some hardcoded styles to their own CSS stylesheet
* FIX: Added default styles for the submission form
* UI: Removed Dashicons requirement

= 1.2.2 =
* FIX: Only apply category query if post type is "post"

= 1.2.1 =
* UPDATE: Added default settings on plugin initialisation
* UPDATE: Updated FontAwesome library
* UPDATE: Added uninstall routine (plugin will clean up after uninstall)
* UPDATE: Added Material theme
* UPDATE: Combined Minimal theme into Default and Flat themes
* UPDATE: Updated translations

= 1.2.0 =
* FIX: Fixed custom CSS not appearing in wp_head()
* FIX: Removed FontAwesome from backend
* UPDATE: Updated FontAwesome version
* UPDATE: Added correct plugin URL in readme.txt
* UPDATE: Increased posts per page to 1000 (from 900)
* FEATURE: Added custom meta fields option for date

= 1.1.0 =
* UPDATE: Added frontend publishing
* UPDATE: Added internationalization

= 1.0.0 =
* First public release
