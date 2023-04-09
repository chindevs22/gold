<?php 
wp_enqueue_script('em-user-register-controller');
wp_enqueue_style('em-public-css');
wp_enqueue_style( 'em-register-int-tel', plugin_dir_url( __DIR__ ) . 'templates/css/em_registration_int_tel.css', array(), EVENTPRIME_VERSION, false ); // Int Tel Css
$return_url = '';
$extensions = event_magic_instance()->extensions;
$settings_service= EventM_Factory::get_service('EventM_Setting_Service');
$gs = $settings_service->load_model_from_db();
if(in_array('guest-booking', $extensions)){
    $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
    if(!empty($showBookNowForGuestUsers) && !empty($gs->guest_booking_page_redirect)){
        $return_url = get_permalink($gs->guest_booking_page_redirect);
    }
}
$is_recaptcha_enabled = 0;
if( $gs->google_recaptcha == 1 && !empty($gs->google_recaptcha_site_key) ){
    $is_recaptcha_enabled = 1;?>
    <script src='https://www.google.com/recaptcha/api.js'></script><?php 
}
$default = 'login';
if(isset($atts['default'])){
  $default = $atts['default'];
  if($default !== 'registration'){
    $default = 'login';
  }
}
$button_titles = (array)$gs->button_titles;?>
<div class="emagic ">
  <div class="em_register_form em_block dbfl" ng-app="eventMagicApp" ng-controller="emRegisterCtrl" ng-cloak="" ng-init="initialize('<?php echo $return_url;?>','<?php echo  $is_recaptcha_enabled;?>', '<?php echo trim($default);?>')" id="em_register_section">
    <div class="em_progress_screen ep-progress-loader" ng-show="requestInProgress"></div>
    <div ng-show="data.showLogin && !requestInProgress" class="em_login em_block em_bg_lt dbfl">
      <form name="emLoginForm" ng-submit="login(emLoginForm.$valid)" novalidate>
        <div class="em_notice dbfl">
          <div class="em_new_registration" ng-show="data.newRegistration">
            <?php _e('You have successfully registered. Please check your email and login.','eventprime-event-calendar-management'); ?>
          </div>
        </div>
        <div class="em_input_row dbfl">
          <h3 class="em_form_heading"><i class="fa fa-sign-in" aria-hidden="true"></i><?php _e('LOGIN','eventprime-event-calendar-management'); ?></h3>
          <div class="em_form_errors"> {{data.loginError}} </div>
          <div class="em_input_row dbfl">
            <div class="em_input_form_field">
              <label class="em_input_label"><?php _e('Email/Username','eventprime-event-calendar-management'); ?><sup>*</sup></label>
              <input type="text" required class="em_input_field" name="user_name" ng-model="data.user_name" />
              <div class="em_form_errors">
                <span ng-show="emLoginForm.user_name.$error.required && chkFormSubmitted">
                  <?php _e('This is a required field.','eventprime-event-calendar-management'); ?>
                </span>
              </div>
            </div>
          </div>
          <div class="em_input_row dbfl">
            <div class="em_input_form_field">
              <label class="em_input_label"><?php _e('Password','eventprime-event-calendar-management'); ?><sup>*</sup></label>
              <input type="password" required class="em_input_field" name="password" ng-model="data.password" />
              <div class="em_form_errors">
                <span ng-show="emLoginForm.password.$error.required && chkFormSubmitted">
                  <?php _e('This is a required field.','eventprime-event-calendar-management'); ?>
                </span>
              </div>
            </div>
          </div>
          <div class="em_input_row dbfl">
            <div class="em_input_submit_field">
              <label class="em_input_label">&nbsp;</label>
              <button type="submit" class="btn btn-primary" ng-disabled="requestInProgress">
                <?php echo em_global_settings_button_title('Login'); ?>
              </button>
            </div>
            <div class="em_input_row dbfl">
              <div class="em_login_notice"><?php _e('Forgot Password?','eventprime-event-calendar-management'); ?> <a href='<?php echo wp_login_url() . "?action=lostpassword"; ?>' ng-click="" ><?php _e('Click Here','eventprime-event-calendar-management'); ?></a></div>
              <div class="em_login_notice"><?php _e("Don't have an account.",'eventprime-event-calendar-management'); ?> <a href="javascript:void(0)" ng-click="data.showLogin = false; chkFormSubmitted = false;" class="em_login"><?php _e('Please Register','eventprime-event-calendar-management'); ?></a></div>
            </div>     
          </div>
        </div>
      </form>
    </div>
    <div class="em_reg_form em_block dbfl em_bg_lt" ng-show="!data.showLogin && !requestInProgress">
      <?php
      // Check if Registration Magic Form is configured
      $form= null;
      if(is_registration_magic_active()){
        $event_id= absint(event_m_get_param('event_id'));
        $event= $event_service->load_model_from_db($event_id);
        if(!empty($event->id) && !empty($event->rm_form)){
          $form = new RM_Forms;
          $form->load_from_db($event->rm_form);
        }
      }
      if (!empty($form) && $form->form_type==1):
        echo do_shortcode("[RM_Form id='$event->rm_form']");
      else:
        wp_enqueue_style('em-int-tel-input-css');
        wp_enqueue_script('em-int-tel-js');
        wp_enqueue_script('em-int-tel-input-js');
        wp_enqueue_script('em-int-tel-input-min-js');
        wp_enqueue_script('em-util-js');
        ?>
        <form name="emRegisterForm" ng-submit="register(emRegisterForm.$valid)" novalidate ng-show="!data.showLogin" class="ep-register-form">
          <div class="em_input_row dbfl">
            <h3 class="em_form_heading"> 
              <i class="fa fa-user-plus" aria-hidden="true"></i><?php _e('REGISTER','eventprime-event-calendar-management');?>
            </h3>
          </div>
          <div class="em_input_row dbfl">
            <div class="em_input_form_field">
              <label class="em_input_label"><?php _e('First Name','eventprime-event-calendar-management'); ?><sup>*</sup></label>
              <input required type="text" class="em_input_field" name="first_name" ng-model="data.first_name" />
              <div class="em_form_errors">
                <span ng-show="emRegisterForm.first_name.$error.required && chkFormSubmitted">
                  <?php _e('This is a required field.','eventprime-event-calendar-management'); ?>
                </span>
              </div>
            </div>
          </div>
          <div class="em_input_row dbfl">
            <div class="em_input_form_field">    
              <label class="em_input_label"><?php _e('Last Name','eventprime-event-calendar-management'); ?><sup>*</sup></label>
              <input type="text" required class="em_input_field" name="last_name" ng-model="data.last_name" />
              <div class="em_form_errors">
                <span ng-show="emRegisterForm.last_name.$error.required && chkFormSubmitted">
                  <?php _e('This is a required field.','eventprime-event-calendar-management'); ?>
                </span>
              </div>
            </div>
          </div>
          <div class="em_input_row dbfl">
            <div class="em_input_form_field">
              <label class="em_input_label"><?php _e('Email','eventprime-event-calendar-management'); ?><sup>*</sup></label>
              <input type="email" required  class="em_input_field" name="email" ng-model="data.email" />
              <div class="em_form_errors">
                <span ng-show="emRegisterForm.email.$error.required && chkFormSubmitted">
                  <?php _e('This is a required field.','eventprime-event-calendar-management'); ?>
                </span>
              </div>
            </div>
            <div class="em_form_errors"> {{data.registerError}} </div>
          </div>
          <div class="em_input_row dbfl">
            <div class="em_input_form_field">
              <label class="em_input_label"><?php _e('Password','eventprime-event-calendar-management'); ?><sup>*</sup></label>
              <input type="password" required  class="em_input_field" name="password" ng-model="data.password" />
              <div class="em_form_errors">
                <span ng-show="emRegisterForm.password.$error.required && chkFormSubmitted">
                  <?php _e('This is a required field.','eventprime-event-calendar-management'); ?>
                </span>
              </div>
            </div>
          </div>
          <div class="em_input_row dbfl">
            <div class="em_input_form_field">
              <label class="em_input_label"><?php _e('Phone','eventprime-event-calendar-management'); ?></label>
              <input type="text" name="country_code" id="country_code" ng-model="data.options.country_code" value="data.country_code" style="display:none;">
              <input type="text" class="em_input_field" name="phone" id="phone" ng-model="data.phone" />
              <div class="em_form_errors">
                <span id="valid-msg" class="ep-error-hide" style="color:green;">Valid number</span>
                <span id="error-msg" class="ep-error-hide">Invalid number</span>
              </div>
            </div>
          </div>
          <?php 
          if( $gs->google_recaptcha == 1 && !empty($gs->google_recaptcha_site_key) ){
            echo '<div class="em_input_row dbfl">
            <div class="g-recaptcha"  data-sitekey="'.$gs->google_recaptcha_site_key.'"></div>
            <div class="em_form_errors"> {{data.captchaError}} </div>
            </div>'; 
          } ?>

          <?php do_action('event_magic_registration_fields'); ?>
          <div class="em_input_row dbfl">
            <div class="em_input_submit_field">
              <button type="submit" class="btn btn-primary" ng-disabled="requestInProgress">
                <?php echo em_global_settings_button_title('Register'); ?>
              </button>
            </div>
          </div>
        </form><?php 
      endif; ?>
      <div class="em_login_notice dbfl"><?php _e('Already have an Account?','eventprime-event-calendar-management'); ?> <a href="javascript:void(0)" ng-click="data.showLogin = true; chkFormSubmitted = false;" class="em_login"><?php _e('Please Login.','eventprime-event-calendar-management'); ?></a></div>
    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    var telInput = $("#phone"),
    errorMsg = $("#error-msg"),
    validMsg = $("#valid-msg");

    // initialise plugin
    telInput.intlTelInput({
    allowExtensions: true,
    formatOnDisplay: true,
    autoFormat: true,
    autoHideDialCode: true,
    autoPlaceholder: true,
    defaultCountry: "auto",
    ipinfoToken: "yolo",

    nationalMode: false,
    numberType: "MOBILE",
    //onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
    preferredCountries: ['sa', 'ae', 'qa','om','bh','kw','ma'],
    preventInvalidNumbers: true,
    separateDialCode: false,
    /* initialCountry: "auto", */
    initialCountry: "us",
    geoIpLookup: function(callback) {
      $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
        var countryCode = (resp && resp.country) ? resp.country : "";
        callback(countryCode);
      });
    },
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.9/js/utils.js"
    });

    var reset = function() {
      telInput.removeClass("error");
      errorMsg.addClass("ep-error-hide");
      validMsg.addClass("ep-error-hide");
    };

    // on blur: validate
    telInput.blur(function() {
      reset();
      if ($.trim(telInput.val())) {
        var ccode = $(".country[class*='active']").attr("data-country-code");
        var myInput = $("#country_code");
            myInput.val(ccode);
            myInput.trigger('input');
        if (telInput.intlTelInput("isValidNumber")) {
          validMsg.removeClass("ep-error-hide");
        } else {
          telInput.addClass("error");
          errorMsg.removeClass("ep-error-hide");
        }
      }
    });

    // on keyup / change flag: reset
    telInput.on("keyup change", reset);

  });
</script>