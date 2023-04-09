<?php
/*
  Plugin Name: CopySafe PDF Protection
  Plugin URI: https://artistscope.com/copysafe_pdf_protection_wordpress_plugin.asp
  Description: This Wordpress plugin enables sites using CopySafe PDF to easily add protected PDF for display in the ArtisBrowser.
  Author: ArtistScope
  Version: 1.27
  Author URI: https://artistscope.com/

  Copyright 2022 ArtistScope Pty Limited


  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// ================================================================================ //
//                                                                                  //
//  WARNING : DON'T CHANGE ANYTHING BELOW IF YOU DONT KNOW WHAT YOU ARE DOING        //
//                                                                                  //
// ================================================================================ //

if (!defined('ABSPATH')) {
  exit;
} // Exit if accessed directly

# set script max execution time to 5mins
set_time_limit(300);

function wpcsp_enable_extended_upload($mime_types = []) {

  // This function is added to allow the upload of .CLASS file in wordpress. In case this function does not
  // work properly, add the line define('ALLOW_UNFILTERED_UPLOADS', true); in the start of this file

  // You can add as many MIME types as you want.
  $mime_types['class'] = 'application/octet-stream';
  // If you want to forbid specific file types which are otherwise allowed,
  // specify them here.  You can add as many as possible.
  return $mime_types;
}

add_filter('upload_mimes', 'wpcsp_enable_extended_upload');

// ============================================================================================================================
# register WordPress menus
function wpcsp_admin_menus() {
  add_menu_page('CopySafe PDF', 'CopySafe PDF', 'publish_posts', 'wpcsp_list');
  add_submenu_page('wpcsp_list', 'CopySafe PDF List Files', 'List Files', 'publish_posts', 'wpcsp_list', 'wpcsp_admin_page_list');
  add_submenu_page('wpcsp_list', 'CopySafe PDF Settings', 'Settings', 'publish_posts', 'wpcsp_settings', 'wpcsp_admin_page_settings');
}

// ============================================================================================================================
# "List" Page
function wpcsp_admin_page_list() {
  $msg = '';
  $table = '';
  $files = _get_wpcsp_uploadfile_list();

  if (!empty($_POST)) {
    $wpcsp_options = get_option('wpcsp_settings');

    $wp_upload_dir = wp_upload_dir();
    $wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);
    if (!empty($wpcsp_options['settings']['upload_path'])) {
      $target_dir = $wp_upload_dir_path . '/' . $wpcsp_options['settings']['upload_path'];
    } else {
      $target_dir = $wp_upload_dir_path;
    }

    $target_file = $target_dir . basename($_FILES["copysafe-pdf-class"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    if (isset($_POST["copysafe-pdf-class-submit"])) {
      // Allow only .class file formats
	   if ($_FILES["copysafe-pdf-class"]["name"] == "") {
        $msg .= '<div class="error"><p><strong>' . __('Please upload file to continue.') . '</strong></p></div>';
        $uploadOk = 0;
      }
      else if ($imageFileType != "class") {
        $msg .= '<div class="error"><p><strong>' . __('Sorry, only .class files are allowed.') . '</strong></p></div>';
        $uploadOk = 0;
      }
      // Check if $uploadOk is set to 0 by an error
      else if ($uploadOk == 0) {
        $msg .= '<div class="error"><p><strong>' . __('Sorry, your file was not uploaded.') . '</strong></p></div>';
        // if everything is ok, try to upload file
      }
      else {
        if (move_uploaded_file($_FILES["copysafe-pdf-class"]["tmp_name"], $target_file)) {
          $base_url = get_site_url();
          $msg .= '<div class="updated"><p><strong>' . __('The file ' . basename($_FILES["copysafe-pdf-class"]["name"]) . ' has been uploaded. Click <a href="' . $base_url . '/wp-admin/admin.php?page=wpcsp_list">here</a> to update below list.') . '</strong></p></div>';
        }
        else {
          $msg .= '<div class="error"><p><strong>' . __('Sorry, there was an error uploading your file.') . '</strong></p></div>';
        }
      }
    }
  }

  if (!empty($files)) {
    foreach ($files as $file) {

      $bare_url = 'admin.php?page=wpcsp_list&cspfilename=' . $file["filename"] . '&action=cspdel';

      $complete_url = wp_nonce_url($bare_url, 'cspdel', 'cspdel_nonce');

      $link = "<div class='row-actions'>
					<span><a href='" . $complete_url . "' title=''>Delete</a></span>											
				</div>";
      // prepare table row
      $table .= "<tr><td></td><td>{$file["filename"]} {$link}</td><td>{$file["filesize"]}</td><td>{$file["filedate"]}</td></tr>";
    }
  }


  if (!$table) {
    $table .= '<tr><td colspan="3">' . __('No file uploaded yet.') . '</td></tr>';
  }

  $wpcsp_options = get_option('wpcsp_settings');
  if ($wpcsp_options["settings"]) {
    extract($wpcsp_options["settings"], EXTR_OVERWRITE);
  }

  $wp_upload_dir = wp_upload_dir();
  $wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);
  $upload_dir = $wp_upload_dir_path . '/' . $upload_path;

  $display_upload_form = !is_dir($upload_dir) ? FALSE : TRUE;

  if (!$display_upload_form) {
    $msg = '<div class="updated"><p><strong>' . __('Upload directory doesn\'t exist. Please configure upload directory to upload class files.') . '</strong></p></div>';
  }
  ?>
    <div class="wrap">
        <div class="icon32" id="icon-file"><br/></div>
      <?php echo $msg; ?>
        <h2>List PDF Class Files</h2>
        <?php if ($display_upload_form): ?>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="file" name="copysafe-pdf-class" value=""/>
                <input type="submit" name="copysafe-pdf-class-submit"
                       value="Upload"/>
            </form>
        <?php endif; ?>
        <div id="col-container" style="width:700px;">
            <div class="col-wrap">
                <h3>Uploaded PDF Class Files</h3>
                <table class="wp-list-table widefat">
                    <thead>
                    <tr>
                        <th width="5px">&nbsp;</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php echo $table; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>&nbsp;</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Date</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="clear"></div>
    </div>
  <?php
}

// ============================================================================================================================
# "Settings" page
function wpcsp_admin_page_settings() {
  $msg = '';
  $wp_upload_dir = wp_upload_dir();
  $wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);

  if (!empty($_POST)) {
    if (wp_verify_nonce($_POST['wpcsp_wpnonce'], 'wpcsp_settings')) {

      $wpcsp_options = get_option('wpcsp_settings');
      extract($_POST, EXTR_OVERWRITE);

      if (!$upload_path) {
        $upload_path = 'copysafe-pdf/';
      }
      else {
        $upload_path = sanitize_text_field($upload_path);
      }

      $upload_path = str_replace("\\", "/", stripcslashes($upload_path));
      if (substr($upload_path, -1) != "/") {
        $upload_path .= "/";
      }

      $wpcsp_options['settings'] = [
        'admin_only' => sanitize_text_field($admin_only),
        'upload_path' => $upload_path,
        'mode' => $mode,
        'language' => sanitize_text_field($language),
        'background' => $background,
        'asps' => !empty(sanitize_text_field($asps))  ? 'checked' : '',
        'ff' => !empty(sanitize_text_field($ff)) ? 'checked' : '',
        'ch' => !empty(sanitize_text_field($ch)) ? 'checked' : '',
        'latest_version' => $latest_version,
      ];

      $max_upload_size = wp_max_upload_size();
      if ( ! $max_upload_size ) {
        $max_upload_size = 0;
      }

      $wpcsp_options['settings']['max_size'] = esc_html(size_format($max_upload_size));

      $upload_path = $wp_upload_dir_path . '/' . $upload_path;
      if (!is_dir($upload_path)) {
        mkdir($upload_path, 0, TRUE);
      }

      update_option('wpcsp_settings', $wpcsp_options);
      $msg = '<div class="updated"><p><strong>' . __('Settings Saved') . '</strong></p></div>';
    }
  }
  $wpcsp_options = get_option('wpcsp_settings');
  if ($wpcsp_options["settings"]) {
    extract($wpcsp_options["settings"], EXTR_OVERWRITE);
  }

  $upload_dir = $wp_upload_dir_path . '/' . $upload_path;

  if (!is_dir($upload_dir)) {
    $msg = '<div class="updated"><p><strong>' . __('Upload directory doesn\'t exist.') . '</strong></p></div>';
  }

  $select = '<option value="demo">Demo Mode</option><option value="licensed">Licensed</option><option value="debug">Debugging Mode</option>';
  $select = str_replace('value="' . $mode . '"', 'value="' . $mode . '" selected', $select);

  $lnguageOptions = [
    "0c01" => "Arabic",
    "0004" => "Chinese (simplified)",
    "0404" => "Chinese (traditional)",
    "041a" => "Croatian",
    "0405" => "Czech",
    "0413" => "Dutch",
    "" => "English",
    "0464" => "Filipino",
    "000c" => "French",
    "0007" => "German",
    "0408" => "Greek",
    "040d" => "Hebrew",
    "0439" => "Hindi",
    "000e" => "Hungarian",
    "0421" => "Indonesian",
    "0410" => "Italian",
    "0411" => "Japanese",
    "0412" => "Korean",
    "043e" => "Malay",
    "0415" => "Polish",
    "0416" => "Portuguese (BR)",
    "0816" => "Portuguese (PT)",
    "0419" => "Russian",
    "0c0a" => "Spanish",
    "041e" => "Thai",
    "041f" => "Turkish",
    "002a" => "Vietnamese",
  ];
  $lnguageOptionStr = '';
  foreach ($lnguageOptions as $k => $v) {
    $chk = str_replace("value='$language'", "value='$language' selected", "value='$k'");
    $lnguageOptionStr .= "<option $chk >$v</option>";
  }
  ?>
    <style type="text/css">#wpcsp_page_setting img {
            cursor: pointer;
        }</style>
    <div class="wrap">
        <div class="icon32" id="icon-settings"><br/></div>
      <?php echo $msg; ?>
        <h2>Default Settings</h2>
        <form action="" method="post">
          <?php echo wp_nonce_field('wpcsp_settings', 'wpcsp_wpnonce'); ?>
            <table cellpadding='1' cellspacing='0' border='0'
                   id='wpcsp_page_setting'>
                <p><strong>Default settings applied to all protected PDF
                        pages:</strong></p>
                <tbody>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo WPCSP_PLUGIN_URL; ?>images/help-24-30.png'
                                border='0'
                                alt='Allow admin only for new uploads.'></td>
                    <td align="left" nowrap>Allow Admin Only:</td>
                    <td align="left"><input name="admin_only" type="checkbox"
                                            value="checked" <?php echo $admin_only; ?>>
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo WPCSP_PLUGIN_URL; ?>images/help-24-30.png'
                                border='0'
                                alt='Path to the upload folder for PDF.'>
                    <td align="left" nowrap>Upload Folder:</td>
                    <td align="left"><input value="<?php echo $upload_path; ?>"
                                            name="upload_path"
                                            class="regular-text code"
                                            type="text"><br />
                        Only specify the folder name. It will be located in site's upload directory, <?php print $wp_upload_dir_path; ?>.
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo WPCSP_PLUGIN_URL; ?>images/help-24-30.png'
                                border='0'
                                alt='Set the mode to use. Use Licensed if you have licensed images. Otherwise set for Demo or Debug mode.'>
							</td>
                    <td align="left">Mode</td>
                    <td align="left"><select name="mode">
                        <?php echo $select; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo WPCSP_PLUGIN_URL; ?>images/help-24-30.png'
                                border='0'
                                alt='Enter minimum version for ArtisBrowser to check.'>
								</td>
                    <td align="left">Latest Version</td>
                    <td align="left">
					<input type="text" name="latest_version" size="8" value="<?php echo $latest_version ? $latest_version : 27.11; ?>" />
					<br />
                        Enter minimum version for ArtisBrowser to check.
					</td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo WPCSP_PLUGIN_URL; ?>images/help-24-30.png'
                                border='0'
                                alt='Set the language that is used in the viewer toolbar and messages. Default is English.'>
                    <td align="left">Language:</td>
                    <td align="left"><select name="language">
                        <?php echo $lnguageOptionStr; ?>
                        </select></td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo WPCSP_PLUGIN_URL; ?>images/help-24-30.png'
                                border='0'
                                alt='Set the color for the unused space in the PDF viewer.'>
                    </td>
                    <td align="left">Page color:</td>
                    <td align="left"><input value="<?php echo $background; ?>"
                                            name="background" type="text"
                                            size="8"></td>
                </tr>
                <tr class="copysafe-video-browsers">
                    <td colspan="5"><h2 class="title">Browser allowed</h2></td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo WPCSP_PLUGIN_URL; ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using the ArtisBrowser to access this page.'>
                    </td>
                    <td align="left" nowrap>Allow ArtisBrowser:</td>
                    <td align="left"><input name="asps" type="checkbox"
                                            value="checked" <?php echo $asps; ?>>
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo WPCSP_PLUGIN_URL; ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using the Firefox web browser to access this page.'>
                    </td>
                    <td align="left">Allow Firefox:</td>
                    <td align="left"><input name="ff"
                                            type="checkbox" <?php echo $ff; ?>> ( for testing only by admin )
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo WPCSP_PLUGIN_URL; ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using the Chrome web browser to access this page.'>
                    </td>
                    <td align="left">Allow Chrome:</td>
                    <td align="left"><input name="ch"
                                            type="checkbox" <?php echo $ch; ?>> ( for testing only by admin )
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" value="Save Settings"
                       class="button-primary" id="submit" name="submit">
            </p>
        </form>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <script type='text/javascript'>
      jQuery(document).ready(function () {
        jQuery("#wpcsp_page_setting img").click(function () {
          alert(jQuery(this).attr("alt"));
        });
      });
    </script>
  <?php
}

// ============================================================================================================================
# convert shortcode to html output
function wpcsp_shortcode($atts) {
	wpcsp_check_artis_browser_version();
  global $post;
  $postid = $post->ID;
  $filename = $atts["name"];

  if (!file_exists(WPCSP_UPLOAD_PATH . $filename)) {
    return "<div style='padding:5px 10px;background-color:#fffbcc'><strong>File($filename) don't exist</strong></div>";
  }

  $settings = wpcsp_get_first_class_settings();


  // get plugin options
  $wpcsp_options = get_option('wpcsp_settings');
  if ($wpcsp_options["settings"]) {
    $settings = wp_parse_args($wpcsp_options["settings"], $settings);
  }

  if ($wpcsp_options["classsetting"][$postid][$filename]) {
    $settings = wp_parse_args($wpcsp_options["classsetting"][$postid][$filename], $settings);
  }

  $settings = wp_parse_args($atts, $settings);

  extract($settings);

  $asps = ($asps) ? '1' : '0';
  $firefox = ($ff) ? '1' : '0';
  $chrome = ($ch) ? '1' : '0';

  $print_anywhere = ($print_anywhere) ? '1' : '0';
  $allow_capture = ($allow_capture) ? '1' : '0';
  $allow_remote = ($allow_remote) ? '1' : '0';

  $plugin_url = WPCSP_PLUGIN_URL;
  $plugin_path = WPCSP_PLUGIN_PATH;
  $upload_path = WPCSP_UPLOAD_PATH;
  $upload_url = WPCSP_UPLOAD_URL;
  // display output
  $output = <<<html
     <script type="text/javascript">
		var wpcsp_plugin_url = "$plugin_url" ;
		var wpcsp_upload_url = "$upload_url" ;
	 </script>
	 <script type="text/javascript">
	<!-- hide JavaScript from non-JavaScript browsers
		var m_bpDebugging = false;
		var m_szMode = "$mode";
		var m_szClassName = "$name";
		var m_szImageFolder = "$upload_url";		//  path from root with / on both ends
		var m_bpPrintsAllowed = "$prints_allowed";
		var m_bpPrintAnywhere = "$print_anywhere";
		var m_bpAllowCapture = "$allow_capture";
		var m_bpAllowRemote = "$allow_remote";
		var m_bpLanguage = "$language";
		var m_bpBackground = "$background";			// background colour without the #
		var m_bpWidth = "$bgwidth";				// width of PDF display in pixels
		var m_bpHeight = "$bgheight";			// height of PDF display in pixels

		var m_bpASPS = "$asps";
		var m_bpChrome = "$chrome";	
		var m_bpFx = "$firefox";			// all firefox browsers from version 5 and later

		if (m_szMode == "debug") {
			m_bpDebugging = true;
		}
		// -->
	 </script>
	 <script src="{$plugin_url}js/wp-copysafe-pdf.js" type="text/javascript"></script>
     <div>
		 <script type="text/javascript">
			<!-- hide JavaScript from non-JavaScript browsers
			if ((m_szMode == "licensed") || (m_szMode == "debug")) {
				insertCopysafePDF("$name");
			}
			else {
				document.writeln("<img src='{$plugin_url}images/demo_placeholder.jpg' border='0' alt='Demo mode'>");
			}
			// -->
		 </script>
     </div>
html;

  return $output;
}

// ============================================================================================================================
# delete short code
function wpcsp_delete_shortcode() {
  // get all posts
  $posts_array = get_posts();
  foreach ($posts_array as $post) {
    // delete short code
    $post->post_content = wpcsp_deactivate_shortcode($post->post_content);
    // update post
    wp_update_post($post);
  }
}

// ============================================================================================================================
# deactivate short code
function wpcsp_deactivate_shortcode($content) {
  // delete short code
  $content = preg_replace('/\[copysafepdf name="[^"]+"\]\[\/copysafepdf\]/s', '', $content);
  return $content;
}

// ============================================================================================================================
# search short code in post content and get post ids
function wpcsp_search_shortcode($file_name) {
  // get all posts
  $posts = get_posts();
  $IDs = FALSE;
  foreach ($posts as $post) {
    $file_name = preg_quote($file_name, '\\');
    preg_match('/\[copysafepdf name="' . $file_name . '"\]\[\/copysafepdf\]/s', $post->post_content, $matches);
    if (is_array($matches) && isset($matches[1])) {
      $IDs[] = $post->ID;
    }
  }
  return $IDs;
}

// ============================================================================================================================
# delete file options
function wpcsp_delete_file_options($file_name) {
  $file_name = trim($file_name);
  $wpcsp_options = get_option('wpcsp_settings');
  foreach ($wpcsp_options["classsetting"] as $k => $arr) {
    if (@$wpcsp_options["classsetting"][$k][$file_name]) {
      unset($wpcsp_options["classsetting"][$k][$file_name]);
      if (!count($wpcsp_options["classsetting"][$k])) {
        unset($wpcsp_options["classsetting"][$k]);
      }
    }
  }
  update_option('wpcsp_settings', $wpcsp_options);
}

// ============================================================================================================================
# install media buttons
function wpcsp_media_buttons($context) {
  global $post_ID;
  // generate token for links
  $token = wp_create_nonce('wpcsp_token');
  $url = admin_url('?wpcsp-popup=file_upload&wpcsp_token=' . $token . '&post_id=' . $post_ID);
  return $context .= "<a href='$url' class='thickbox' id='wpcsp_link' data-body='no-overflow' title='CopySafe PDF'><img src='" . plugin_dir_url(__FILE__) . "/images/copysafepdfbutton.png'></a>";
}

// ============================================================================================================================
# browser detector js file
function wpcsp_load_js() {
  // load custom JS file
  //wp_enqueue_script( 'wpcsp-browser-detector', plugins_url( '/browser_detection.js', __FILE__), array( 'jquery' ) );
}

// ============================================================================================================================
# admin page scripts
function wpcsp_admin_load_js() {
  // load jquery suggest plugin
  wp_enqueue_script('suggest');
  wp_enqueue_script('plupload-all');
}

// ============================================================================================================================
# admin page styles
function wpcsp_admin_load_styles() {
  // register custom CSS file & load
  wp_register_style('wpcsp-style', plugins_url('/css/wp-copysafe-pdf.css', __FILE__));
  wp_enqueue_style('wpcsp-style');
}

function wpcsp_is_admin_postpage() {
  $chk = FALSE;
  $script_name = explode("/", $_SERVER["SCRIPT_NAME"]);
  $ppage = end($script_name);
  if ($ppage == "post-new.php" || $ppage == "post.php") {
    return TRUE;
  }
}

function wpcsp_includecss_js() {
  if (!wpcsp_is_admin_postpage()) {
    return;
  }
  global $wp_popup_upload_lib;
  if ($wp_popup_upload_lib) {
    return;
  }
  $wp_popup_upload_lib = TRUE;
  echo "<link rel='stylesheet' href='//code.jquery.com/ui/1.9.2/themes/redmond/jquery-ui.css' type='text/css' />";
  // wp_enqueue_script( 'jquery.uploadify');
  wp_enqueue_script('jquery');
  wp_enqueue_script('jquery-ui-progressbar');

  wp_enqueue_script('jquery.json', FALSE, ['jquery']);
  //wp_enqueue_script('plugin-script', false, array('jquery','plupload-all'));

}

// ============================================================================================================================
# setup plugin
function wpcsp_setup() {
  //----add codding----
  $options = get_option("wpcsp_settings");
  define('WPCSP_PLUGIN_PATH', str_replace("\\", "/", plugin_dir_path(__FILE__))); //use for include files to other files
  define('WPCSP_PLUGIN_URL', plugins_url('/', __FILE__));

  $wp_upload_dir = wp_upload_dir();
  $wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);
  $upload_path = $wp_upload_dir_path . '/' . $options["settings"]["upload_path"];
  define('WPCSP_UPLOAD_PATH', $upload_path); //use for include files to other files

  $wp_upload_dir = wp_upload_dir();
  $wp_upload_dir_url = str_replace("\\", "/", $wp_upload_dir['baseurl']);
  $upload_url = $wp_upload_dir_url . '/' . $options["settings"]["upload_path"];
  define('WPCSP_UPLOAD_URL', $upload_url);

  include(WPCSP_PLUGIN_PATH . "function.php");
  add_action('admin_head', 'wpcsp_includecss_js');
  add_action('wp_ajax_wpcsp_ajaxprocess', 'wpcsp_ajaxprocess');

  //Sanitize the GET input variables
  $pagename = !empty(@$_GET['page']) ? sanitize_key(@$_GET['page']) : '';

  $cspfilename = !empty(@$_GET['cspfilename']) ? sanitize_file_name(@$_GET['cspfilename']) : '';

  $action = !empty(@$_GET['action']) ? sanitize_key(@$_GET['action']) : '';

  $cspdel_nonce = !empty(@$_GET['cspdel_nonce']) ? sanitize_key(@$_GET['cspdel_nonce']) : '';

  if ($pagename == 'wpcsp_list' && $cspfilename != '' && $action == 'cspdel') {
    //check that nonce is valid and user is administrator
    if (current_user_can('administrator') && wp_verify_nonce($cspdel_nonce, 'cspdel')) {
      echo "Nonce has been verified";
      wpcsp_delete_file_options($cspfilename);
      if (file_exists(WPCSP_UPLOAD_PATH . $cspfilename)) {
        unlink(WPCSP_UPLOAD_PATH . $cspfilename);
      }
      wp_redirect('admin.php?page=wpcsp_list');
    }
    else {
      wp_nonce_ays();
    }
  }

  if (isset($_GET['wpcsp-popup']) && @$_GET["wpcsp-popup"] == "file_upload") {
    require_once(WPCSP_PLUGIN_PATH . "popup_load.php");
    exit();
  }
  //=============================
  // load js file
  add_action('wp_enqueue_scripts', 'wpcsp_load_js');

  // load admin CSS
  add_action('admin_print_styles', 'wpcsp_admin_load_styles');

  // add short code
  add_shortcode('copysafepdf', 'wpcsp_shortcode');

  // if user logged in
  if (is_user_logged_in()) {
    // install admin menu
    add_action('admin_menu', 'wpcsp_admin_menus');

    // check user capability
    if (current_user_can('edit_posts')) {
      // load admin JS
      add_action('admin_print_scripts', 'wpcsp_admin_load_js');
      // load media button
      add_action('media_buttons_context', 'wpcsp_media_buttons');
    }
  }


  wp_register_script('plugin-script', WPCSP_PLUGIN_URL . 'js/copysafepdf_media_uploader.js');
  wp_register_script('jquery.json', WPCSP_PLUGIN_URL . 'lib/jquery.json-2.3.js', ['plupload-all']);

}

// ============================================================================================================================
# runs when plugin activated
function wpcsp_activate() {
  // if this is first activation, setup plugin options
  if (!get_option('wpcsp_settings')) {
    $wp_upload_dir = wp_upload_dir();
    $wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);

    // set plugin folder
    $upload_dir = 'copysafe-pdf/';
    $upload_path = $wp_upload_dir_path . '/' . $upload_dir;

    // set default options
    $wpcsp_options['settings'] = [
      'admin_only' => "checked",
      'upload_path' => $upload_dir,
      'mode' => "demo",
      'language' => "",
      'background' => "EEEEEE",
      'asps' => "checked",
      'ff' => "",
      'ch' => "",
    ];

    update_option('wpcsp_settings', $wpcsp_options);

    if (!is_dir($upload_path)) {
      mkdir($upload_path, 0, TRUE);
    }
    // create upload directory if it is not exist
  }
}

// ============================================================================================================================
# runs when plugin deactivated
function wpcsp_deactivate() {
  // remove text editor short code
  remove_shortcode('copysafepdf');
}

// ============================================================================================================================
# runs when plugin deleted.
function wpcsp_uninstall() {
  // delete all uploaded files
  $wp_upload_dir = wp_upload_dir();
  $wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);

  $default_upload_dir = $wp_upload_dir_path . '/copysafe-pdf/';

  if (is_dir($default_upload_dir)) {
    $dir = scandir($default_upload_dir);
    foreach ($dir as $file) {
      if ($file != '.' || $file != '..') {
        unlink($default_upload_dir . $file);
      }
    }
    rmdir($default_upload_dir);
  }

  // delete upload directory
  $options = get_option("wpcsp_settings");

  if ($options["settings"]["upload_path"]) {
    $upload_path = $wp_upload_dir_path . '/' . $options["settings"]["upload_path"];
    if (is_dir($upload_path)) {
      $dir = scandir($upload_path);
      foreach ($dir as $file) {
        if ($file != '.' || $file != '..') {
          unlink($upload_path . '/' . $file);
        }
      }
      // delete upload directory
      rmdir($upload_path);
    }
  }

  // delete plugin options
  delete_option('wpcsp_settings');

  // unregister short code
  remove_shortcode('copysafepdf');

  // delete short code from post content
  wpcsp_delete_shortcode();
}

// ============================================================================================================================
# register plugin hooks
register_activation_hook(__FILE__, 'wpcsp_activate'); // run when activated
register_deactivation_hook(__FILE__, 'wpcsp_deactivate'); // run when deactivated
register_uninstall_hook(__FILE__, 'wpcsp_uninstall'); // run when uninstalled

add_action('init', 'wpcsp_setup');
//Imaster Coding

function wpcsp_admin_js() {

  wp_register_script('wp-copysafe-pdf-uploader', WPCSP_PLUGIN_URL . 'js/copysafepdf_media_uploader.js', [
    'jquery',
    'plupload-all',
  ]);
}

add_action('admin_enqueue_scripts', 'wpcsp_admin_load_js');


function wpcsp_admin_head() {
  $uploader_options = [
    'runtimes' => 'html5,silverlight,flash,html4',
    'browse_button' => 'mfu-plugin-uploader-button',
    'container' => 'mfu-plugin-uploader',
    'drop_element' => 'mfu-plugin-uploader',
    'file_data_name' => 'async-upload',
    'multiple_queues' => TRUE,
    'max_file_size' => wp_max_upload_size() . 'b',
    'url' => admin_url('admin-ajax.php'),
    'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
    'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
    'filters' => [
      [
        'title' => __('Allowed Files'),
        'extensions' => '*',
      ],
    ],
    'multipart' => TRUE,
    'urlstream_upload' => TRUE,
    'multi_selection' => TRUE,
    'multipart_params' => [
      '_ajax_nonce' => '',
      'action' => 'my-plugin-upload-action',
    ],
  ];
  ?>
    <script type="text/javascript">
      var global_uploader_options =<?php echo json_encode($uploader_options); ?>;
    </script>
  <?php
}

add_action('admin_head', 'wpcsp_admin_head');

function wpcsp_includecss_js_to_footer(){
	if (!wpcsp_is_admin_postpage())
		return;
	
	?>
	<script>
	if( jQuery("#wpcsp_link").length > 0 ){
		if( jQuery("#wpcsp_link").data("body") == "no-overflow" ){
			jQuery("body").addClass("wps-no-overflow");
			
		}
	}
	</script>
	<?php
}

add_action('admin_footer', 'wpcsp_includecss_js_to_footer') ;

function wpcsp_ajax_action() {
  add_filter('upload_dir', 'wpcsp_upload_dir');
  // check ajax nonce
  //check_ajax_referer( __FILE__ );
  if (current_user_can('upload_files')) {
    $response = [];
    // handle file upload
    $id = media_handle_upload(
      'async-upload',
      0,
      [
        'test_form' => TRUE,
        'action' => 'my-plugin-upload-action',
      ]
    );

    // send the file' url as response
    if (is_wp_error($id)) {
      $response['status'] = 'error22';
      $response['error'] = $id->get_error_messages();
    }
    else {
      $response['status'] = 'success';

      $src = wp_get_attachment_image_src($id, 'thumbnail');
      $response['attachment'] = [];
      $response['attachment']['id'] = $id;
      $response['attachment']['src'] = $src[0];
    }

  }
  remove_filter('upload_dir', 'wpcsp_upload_dir');
  echo json_encode($response);
  exit;
}

add_action('wp_ajax_my-plugin-upload-action', 'wpcsp_ajax_action');


$upload = wp_upload_dir();
remove_filter('upload_dir', 'wpcsp_upload_dir');

function wpcsp_upload_dir($upload) {

  $upload['subdir'] = '/copysafe-pdf';
  $upload['path'] = $upload['basedir'] . $upload['subdir'];
  $upload['url'] = $upload['baseurl'] . $upload['subdir'];
  return $upload;
}

?>