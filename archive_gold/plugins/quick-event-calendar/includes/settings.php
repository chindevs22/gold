<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

function qcc_calendar_admin_page() {
	?>
	<div class="wrap">
		<h1><?php _e('Quick Event Calendar', 'quick-event-calendar'); ?></h1>

		<?php
		$t = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard_tab';
		if(isset($_GET['tab']))
			$t = $_GET['tab'];

        $i = get_option('ip_slug');
		?>
		<h2 class="nav-tab-wrapper">
			<a href="options-general.php?page=qcc&amp;tab=dashboard_tab" class="nav-tab <?php echo $t == 'dashboard_tab' ? 'nav-tab-active' : ''; ?>"><?php _e('Dashboard', 'quick-event-calendar'); ?></a>
			<a href="options-general.php?page=qcc&amp;tab=settings_tab" class="nav-tab <?php echo $t == 'settings_tab' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'quick-event-calendar'); ?></a>
			<a href="options-general.php?page=qcc&amp;tab=configurator_tab" class="nav-tab <?php echo $t == 'configurator_tab' ? 'nav-tab-active' : ''; ?>"><?php _e('Configurator', 'quick-event-calendar'); ?></a>
			<a href="options-general.php?page=qcc&amp;tab=documentation" class="nav-tab <?php echo $t == 'documentation' ? 'nav-tab-active' : ''; ?>"><?php _e('Documentation', 'quick-event-calendar'); ?></a>
		</h2>

		<?php if($t == 'dashboard_tab') {
            global $wpdb; ?>

            <div class="gb-ad" id="gb-ad">
                <h3 class="gb-ad--header"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 68 68"><defs/><rect width="100%" height="100%" fill="none"/><g class="currentLayer"><path fill="#fff" d="M34.76 33C22.85 21.1 20.1 13.33 28.23 5.2 36.37-2.95 46.74.01 50.53 3.8c3.8 3.8 5.14 17.94-5.04 28.12-2.95 2.95-5.97 5.84-5.97 5.84L34.76 33"/><path fill="#fff" d="M43.98 42.21c5.54 5.55 14.59 11.06 20.35 5.3 5.76-5.77 3.67-13.1.98-15.79-2.68-2.68-10.87-5.25-18.07 1.96-2.95 2.95-5.96 5.84-5.96 5.84l2.7 2.7m-1.76 1.75c5.55 5.54 11.06 14.59 5.3 20.35-5.77 5.76-13.1 3.67-15.79.98-2.69-2.68-5.25-10.87 1.95-18.07 2.85-2.84 5.84-5.96 5.84-5.96l2.7 2.7" class="selected"/><path fill="#fff" d="M33 34.75c-11.9-11.9-19.67-14.67-27.8-6.52-8.15 8.14-5.2 18.5-1.4 22.3 3.8 3.79 17.95 5.13 28.13-5.05 3.1-3.11 5.84-5.97 5.84-5.97L33 34.75"/></g></svg> Thank you for using Quick Event Calendar!</h3>
                <div class="gb-ad--content">
                    <p>If you enjoy this plugin, do not forget to <a href="https://wordpress.org/support/plugin/quick-event-calendar/reviews/?filter=5" rel="external">rate it</a>! We work hard to update it, fix bugs, add new features and make it compatible with the latest web technologies.</p>
                    <p>Have you tried our other <a href="https://getbutterfly.com/wordpress-plugins/">WordPress plugins</a>?</p>
                </div>
                <div class="gb-ad--footer">
                    <p>For support, feature requests and bug reporting, please visit the <a href="https://getbutterfly.com/" rel="external">official website</a>.<br>Built by <a href="https://getbutterfly.com/" rel="external"><strong>getButterfly</strong>.com</a> &middot; <a href="https://getbutterfly.com/quick-event-calendar/">Documentation</a> &middot; <small>Code wrangling since 2005</small></p>

                    <p>
                        <small>You are using PHP <?php echo PHP_VERSION; ?> and MySQL <?php echo $wpdb->db_version(); ?>.</small>
                    </p>
                </div>
            </div>

            <?php
            echo '<h2>Dashboard (Help and General Usage)</h2>

            <p>Thank you for using <strong>Quick Event Calendar</strong>, a very simple, performant and WordPress-integrated event calendar plugin. <strong>Quick Event Calendar</strong> allows you to add posts, events or any other custom post type to a flexible, responsive calendar which can be placed in a post, page or widget.</p>
            <p>
                <small>You are using Quick Event Calendar plugin version <strong>' . QEC_VERSION . '</strong>.</small>
            </p>';
		} else if($t == 'configurator_tab') {
            if(isset($_POST['isQCCSubmit'])) {
                update_option('qcc_accent_colour', sanitize_text_field($_POST['qcc_accent_colour']));
                update_option('qcc_day_colour', sanitize_text_field($_POST['qcc_day_colour']));
                update_option('qcc_event_colour', sanitize_text_field($_POST['qcc_event_colour']));

                echo '<div class="updated notice is-dismissible"><p>Settings updated successfully!</p></div>';
			}
			?>
            <style>
            .wp-color-result {
                margin: 0 6px 0 0;
                vertical-align: middle;
            }
            </style>
			<form method="post" action="">
				<h2><?php _e('Calendar Configurator', 'qcc'); ?></h2>
				<p>The <b>Calendar configurator</b> allows you to select calendar colours to match your site design.</p>
			    <table class="form-table">
			        <tbody>
			            <tr>
			                <th scope="row"><label>Calendar design</label></th>
			                <td>
                                <p>
                                    <input type="text" name="qcc_accent_colour" id="qcc_accent_colour" placeholder="#1e2833" class="qcc-color-picker" value="<?php echo get_option('qcc_accent_colour'); ?>" data-default-color="#1e2833"> <label for="qcc_accent_colour">Accent colour</label>
                                    <br><small><?php _e('The accent colour is used for selected dates background and hover states.', 'qcc'); ?></small>
                                </p>
                                <p>
                                    <input type="text" name="qcc_day_colour" id="qcc_day_colour" placeholder="#1e2833" class="qcc-color-picker" value="<?php echo get_option('qcc_day_colour'); ?>" data-default-color="#1e2833"> <label for="qcc_day_colour">Current day background colour</label>
                                    <br><small><?php _e('The current day colour is used for current date background.', 'qcc'); ?></small>
                                </p>
                                <p>
                                    <input type="text" name="qcc_event_colour" id="qcc_event_colour" placeholder="#1e2833" class="qcc-color-picker" value="<?php echo get_option('qcc_event_colour'); ?>" data-default-color="#1e2833"> <label for="qcc_event_colour">Event colour</label>
                                    <br><small><?php _e('The event colour is used for event dot indicators.', 'qcc'); ?></small>
                                </p>
							</td>
			            </tr>
			        </tbody>
			    </table>

                <hr>
				<p><input type="submit" name="isQCCSubmit" value="<?php _e('Save Changes', 'qcc'); ?>" class="button-primary"></p>
            </form>
		<?php } else if($t == 'settings_tab') { ?>
			<?php
			if(isset($_POST['isQCCSubmit'])) {
				update_option('qcc_post_type', sanitize_text_field($_POST['qcc_post_type']));
				update_option('qcc_category', (int) $_POST['qcc_category']);

                update_option('qcc_show_published', (int) $_POST['qcc_show_published']);
				update_option('qcc_show_scheduled', (int) $_POST['qcc_show_scheduled']);

                update_option('qcc_use_date_meta', (int) $_POST['qcc_use_date_meta']);
                update_option('qcc_date_meta_month', sanitize_text_field($_POST['qcc_date_meta_month']));
                update_option('qcc_date_meta_day', sanitize_text_field($_POST['qcc_date_meta_day']));

                update_option('qcc_theme', (int) $_POST['qcc_theme']);

                echo '<div class="updated notice is-dismissible"><p>Settings updated successfully!</p></div>';
			}
			?>
			<form method="post" action="">
				<h2><?php _e('General Settings', 'qcc'); ?></h2>
				<p><?php _e('These settings apply to the default behaviour of your calendar.', 'qcc'); ?></p>
			    <table class="form-table">
			        <tbody>
			            <tr>
			                <th scope="row"><label for="qcc_post_type">(Custom) Post Type</label></th>
			                <td>
                                <p>
                                    <select name="qcc_post_type" id="qcc_post_type" placeholder="<?php _e('Select a custom post type...', 'qcc'); ?>">
                                        <option value="" disabled selected><?php _e('Select a custom post type...', 'qcc'); ?></option>
                                        <?php
                                        $qcc_post_type = get_option('qcc_post_type');

                                        $args = [
                                            'public' => true,
                                        ];
                                        $post_types = get_post_types($args, 'names');
                                        foreach($post_types as $post_type) {
                                            if($qcc_post_type == $post_type) {
                                                $selected = 'selected';
                                            } else {
                                                $selected = '';
                                            }
                                            echo '<option value="' . $post_type . '" ' . $selected . '>' . $post_type . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <br><small><?php _e('Use this (custom) post type as calendar event type.', 'qcc'); ?></small>
                                </p>
                                <p>
                                    <?php
                                    $qcc_category = get_option('qcc_category');

                                    $args = [
                                        'show_count'        => 1,
                                        'hide_empty'        => 1,
                                        'hierarchical'      => 1,
                                        'name'              => 'qcc_category',
                                        'taxonomy'          => 'category',
                                        'selected'          => $qcc_category,
                                    ];

                                    wp_dropdown_categories($args);
                                    ?>
                                    <br><small><?php _e('Use this category as calendar event category.', 'qcc'); ?></small>
                                </p>
			                </td>
			            </tr>
			            <tr>
			                <th scope="row"><label for="qcc_show_published">Visibility</label></th>
			                <td>
                                <p>
                                    <input type="checkbox" class="wppd-ui-toggle" name="qcc_show_published" id="qcc_show_published" value="1" <?php if(get_option('qcc_show_published') == 1) echo 'checked'; ?>> <label for="qcc_show_published"><?php _e('Show published posts', 'qcc'); ?></label><br>
                                    <small><?php _e('Show published posts in the calendar.', 'qcc'); ?></small><br>

                                    <input type="checkbox" class="wppd-ui-toggle" name="qcc_show_scheduled" id="qcc_show_scheduled" value="1" <?php if(get_option('qcc_show_scheduled') == 1) echo 'checked'; ?>> <label for="qcc_show_scheduled"><?php _e('Show scheduled/future posts', 'qcc'); ?></label><br>
                                    <small><?php _e('Show scheduled/future posts in the calendar.', 'qcc'); ?></small>
                                </p>
			                </td>
			            </tr>
			            <tr>
			                <th scope="row"><label for="qcc_use_date_meta">Date</label></th>
			                <td>
                                <p>
                                    <input type="checkbox" class="wppd-ui-toggle" name="qcc_use_date_meta" id="qcc_use_date_meta" value="1" <?php if(get_option('qcc_use_date_meta') == 1) echo 'checked'; ?>> <label for="qcc_use_date_meta"><?php _e('Use custom fields for date', 'qcc'); ?></label>
                                    <br><small><?php _e('Use existing custom fields for date (as opposed to post date). Use third party plugins to add custom meta data to your post types. Make sure your existing custom fields are numerical. If this option is checked, it will override the visibility options above.', 'qcc'); ?></small>
                                </p>
                                <p class="qcc_use_date_meta_hidden">
                                    <input type="text" class="regular-text" name="qcc_date_meta_month" id="qcc_date_meta_month" value="<?php echo get_option('qcc_date_meta_month'); ?>">
                                    <label for="qcc_date_meta_month"><?php _e('Month custom field name', 'qcc'); ?></label>
                                    <br><small><?php _e('Your month custom field name.', 'qcc'); ?></small>
                                </p>
                                <p class="qcc_use_date_meta_hidden">
                                    <input type="text" class="regular-text" name="qcc_date_meta_day" id="qcc_date_meta_day" value="<?php echo get_option('qcc_date_meta_day'); ?>">
                                    <label for="qcc_date_meta_day"><?php _e('Day custom field name', 'qcc'); ?></label>
                                    <br><small><?php _e('Your day custom field name.', 'qcc'); ?></small>
                                </p>
			                </td>
			            </tr>
			        </tbody>
			    </table>

                <hr>
				<h2><?php _e('Display Settings', 'qcc'); ?></h2>
				<p><?php _e('These settings apply to the default appearance of your calendar.', 'qcc'); ?></p>
			    <table class="form-table">
			        <tbody>
			            <tr>
			                <th scope="row"><label for="qcc_theme"><?php _e('Calendar Theme', 'qcc'); ?></label></th>
			                <td>
								<select name="qcc_theme" id="qcc_theme">
									<option value="0"<?php if(get_option('qcc_theme') == 0) echo ' selected'; ?>><?php _e('Default', 'qcc'); ?></option>
									<option value="2"<?php if(get_option('qcc_theme') == 2) echo ' selected'; ?>><?php _e('Ubuntu', 'qcc'); ?></option>
									<option value="3"<?php if(get_option('qcc_theme') == 3) echo ' selected'; ?>><?php _e('Flat', 'qcc'); ?></option>
									<option value="4"<?php if(get_option('qcc_theme') == 4) echo ' selected'; ?>><?php _e('Bright', 'qcc'); ?></option>
									<option value="5"<?php if(get_option('qcc_theme') == 5) echo ' selected'; ?>><?php _e('Material Design', 'qcc'); ?></option>
									<option value="6"<?php if(get_option('qcc_theme') == 6) echo ' selected'; ?>><?php _e('Freya', 'qcc'); ?></option>
								</select>
								<br><small><?php _e('Select a theme/skin based on your current WordPress appearance.', 'qcc'); ?></small>
			                </td>
			            </tr>
			        </tbody>
			    </table>

                <hr>
				<p><input type="submit" name="isQCCSubmit" value="<?php _e('Save Changes', 'qcc'); ?>" class="button-primary"></p>
			</form>
		<?php } else if($t == 'documentation') { ?>
            <h2>Installation</h2>

            <p>After installing the plugin, you need to configure it by selecting several options:</p>
            <p>In your <b>Settings</b> tab, Select the desired post type, select the desired category (if post type is 'post') and select which posts you want to show. For an event calendar, the default should be both published and scheduled/future posts.</p>
            <p>In case you want to use custom meta fields, the calendar works with preexisting meta fields. Using custom meta fields requires a third-party plugin to add new fields.</p>
            <p>After selecting the desired options, pick one of the predefined themes to match your site.</p>
            <p>After selecting a theme, you need to match your site's colours with the calendar template.</p>
            <p>In your <b>Configurator</b> tab, select the accent colour, the event colour and the background colour for the current day.</p>
            <p>When you are done with the settings, use the <code>[qcc-calendar]</code> shortcode in any post, page or widget to show the calendar.</p>

            <h3>Help with shortcodes</h3>
            <p>Use the <code>[qcc-calendar]</code> shortcode in any post or page or widget to show the calendar.</p>
            <p>Use the <code>[qcc-form]</code> shortcode in any post or page or widget to show the calendar submission form.</p>

            <h3>Help with template tags</h3>
            <p>Use the <code>&lt;php echo do_shortcode("[qcc-calendar]"); ?&gt;</code> code in any page template to show the calendar.</p>
            <p>Use the <code>&lt;php echo do_shortcode("[qcc-form]"); ?&gt;</code> code in any page template to show the calendar submission form.</p>

            <h3>Help with styling</h3>
			<p>Use the included themes to style your calendar. Use the Default or the Flat theme to match your current WordPress theme appearance.</p>
		<?php } ?>
	</div>
	<?php
}