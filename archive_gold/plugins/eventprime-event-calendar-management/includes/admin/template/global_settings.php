<?php add_thickbox(); 
wp_enqueue_script('moment');
wp_enqueue_script('em-global-settings-controller');
wp_enqueue_script('em-select2');
wp_enqueue_style('em_select2_css');
$em = event_magic_instance(); 
$buy_link = event_m_get_buy_link(); ?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre ep-global-settings-wrap" ng-app="eventMagicApp" ng-controller="globalSettingsCtrl" ng-init="initialize()" ng-cloak>
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="content kf-hidden" style="{{requestInProgress ?'display:none':'display:block'}}">
        <?php if (event_m_get_param('show_payment')): ?>      
            <div  ng-init="show_payments(true)">  </div>
        <?php endif; ?>
       
        <div class="form_errors">
            <ul>
                <li class="emfield_error" ng-repeat="error in  formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>  
        </div>
        <div class="ep-global-settings dbfl" ng-hide="enabledAnyGlobalService">
            <div class="ep-settings-area dbfl">
                <div class="kf-db-title dbfl"><?php _e('Global Settings', 'eventprime-event-calendar-management'); ?></div> 
                
                <a href="javascript:void(0)" ng-click="enableGlobalService('showNotification');">
                    <div class="em-settings-box">
                        <img class="em-settings-icon"  src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/rm-email-notifications.png">
                        <div class="em-settings-description">

                        </div>
                        <div class="em-settings-subtitle"><?php _e('Email Notifications', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Notification Contents', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>        

                <a href="javascript:void(0)" ng-click="enableGlobalService('showPayments')" >
                    <div class="em-settings-box">
                        <img class="em-settings-icon" ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/rm-payments.png">
                        <div class="em-settings-subtitle"><?php _e('Payments','eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Payment gateway configuration settings','eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showExternalIntegration')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon"  ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/rm-third-party.png">
                        <div class="em-settings-description">

                        </div>
                        <div class="em-settings-subtitle"><?php _e('External Integration', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Map Integration, Social Sharing...', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showPageIntegration')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon"  ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/kf-pages.png">
                        <div class="em-settings-description">

                        </div>
                        <div class="em-settings-subtitle"><?php _e('Default Pages', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Pages with shortcodes', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showPageGSettings')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon"  ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/ep-global-setting-icon.png">
                        <div class="em-settings-description">

                        </div>
                        <div class="em-settings-subtitle"><?php _e('Regular Settings', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Regular Settings', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>
                
                <a href="javascript:void(0)" ng-click="enableGlobalService('showCustomCSS')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon"  ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/ep-custom-css-icon.png">
                        <div class="em-settings-description">

                        </div>
                        <div class="em-settings-subtitle"><?php _e('Custom CSS', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Add custom CSS code', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>
                
                <a href="javascript:void(0)" ng-click="enableGlobalService('showUserEventSubmit')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon"  ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/event-submission-icon.png">
                        <div class="em-settings-description">

                        </div>
                        <div class="em-settings-subtitle"><?php _e('Frontend Event Submission', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Configure settings for frontend event submissions', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showCustomBookingFields')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon"  ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/custom-field-icon.png">
                        <div class="em-settings-description">

                        </div>
                        <div class="em-settings-subtitle"><?php _e('Attendee Booking Fields', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Attendee Booking Fields', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showButtonSettings')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon" ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/buttun-text-icon.png">
                        <div class="em-settings-description"></div>
                        <div class="em-settings-subtitle"><?php _e('Button Labels', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Change labels for a more customized user experience', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showSeoSettings')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon" ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/seo-setting-icon.png">
                        <div class="em-settings-description"></div>
                        <div class="em-settings-subtitle"><?php _e('SEO', 'eventprime-event-calendar-management'); ?>&nbsp;&nbsp;<sup style="color:orange;">(Beta)</sup></div>
                        <span><?php _e('Customize SEO URLs', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showPerformerSettings')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon" ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/performer-setting-icon.png">
                        <div class="em-settings-description"></div>
                        <div class="em-settings-subtitle"><?php _e('Performer Views', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Customize Performer page views', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showEventTypeSettings')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon" ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/event-type-setting-icon.png">
                        <div class="em-settings-description"></div>
                        <div class="em-settings-subtitle"><?php _e('Event Types Views', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Customize Event Type page views', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showOrganizerSettings')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon" ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/oragnizer-setting-icon.png">
                        <div class="em-settings-description"></div>
                        <div class="em-settings-subtitle"><?php _e('Organizer Views', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Customize Organizer page views', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a href="javascript:void(0)" ng-click="enableGlobalService('showEventSiteSettings')">
                    <div class="em-settings-box">
                        <img class="em-settings-icon" ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/event-site-setting-icon.png">
                        <div class="em-settings-description"></div>
                        <div class="em-settings-subtitle"><?php _e('Event Site Views', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Customize Event Site page views', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>

                <a ng-href="<?php echo admin_url('/admin.php?page=em_user_capabilities'); ?>">
                    <div class="em-settings-box">
                        <img class="em-settings-icon" ng-src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/user-capabilities-setting-icon.png">
                        <div class="em-settings-description"></div>
                        <div class="em-settings-subtitle"><?php _e('User Capabilities', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php _e('Customize user roles capabilities', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </a>
                <!-- Call Global Settings section from the extensions -->
                <?php do_action('event_magic_gs_section_settings'); ?>
            </div>            
            
            <div class="ep-settings-area dbfl"> 
                <div class="kf-db-title dbfl"><?php _e('Extensions Settings', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-active-extensions-container">
                    <?php do_action('event_magic_gs_settings'); ?>
                </div>
                <div class="ep-inactive-extensions-container">
                    <?php do_action('event_magic_inactive_gs_settings'); ?>
                </div>
            </div>
        </div>    
        <form name="optionForm" ng-submit="saveSettings(optionForm.$valid)" novalidate  class="em-email-settings-form">
            <div class="ep-setting-wrap" ng-show="enabledAnyGlobalService">
                <div ng-show="showPageIntegration">  
                    <div class="kf-db-title dbfl"><?php _e('Default Pages', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Performers Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="performers_page" ng-model="data.options.performers_page"  ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>                        
                        <div class="emfield_error">
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Performers Page will navigate to selected page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Sites', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="venues_page" ng-model="data.options.venues_page"  ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>                        

                        <div class="emfield_error">
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Venues Page will navigate to selected page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Events Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="events_page" ng-model="data.options.events_page"  ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>                        

                        <div class="emfield_error">
                        </div>

                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Events Page will navigate to selected page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"> <?php _e('Bookings Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="booking_page" ng-model="data.options.booking_page"  ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>                        

                        <div class="emfield_error">
                        </div>

                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Bookings Page will navigate to selected page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Profile Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="profile_page" ng-model="data.options.profile_page"  ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>                        
                        <div class="emfield_error">
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Profile Page will navigate to selected page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Types Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="event_types" ng-model="data.options.event_types"  ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>                        
                        <div class="emfield_error">
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Event Types Page will navigate to selected page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Submit Event Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="event_submit_form" ng-model="data.options.event_submit_form"  ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>
                        <div class="emfield_error">
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Event Submission Page will navigate to selected page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Booking Details Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="booking_details_page" ng-model="data.options.booking_details_page" ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>
                        <div class="emfield_error">
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Booking Details Page will navigate to selected page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Organizers Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="event_organizers" ng-model="data.options.event_organizers"  ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>                        
                        <div class="emfield_error">
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Event Organizers Page will navigate to selected page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showExternalIntegration">
                    <div class="em-external-integration">
                        <div class="kf-db-title dbfl"><?php _e('External Integration', 'eventprime-event-calendar-management'); ?></div>
                        <div class="emrow">
                            <div class="emfield"><?php _e('Google Map API Key', 'eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <input type="text" name="gmap_api_key"  ng-model="data.options.gmap_api_key">
                                <div class="emfield_error">

                                </div>
                            </div>
                            <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                <?php _e('Enter Google Map API Key after registering your application for Google Maps.', 'eventprime-event-calendar-management'); ?>
                            </div>
                        </div>
                    </div>   

                    <div class="em-external-integration" >
                        <div class="emrow">
                            <div class="emfield"><?php _e('Allow Social Sharing', 'eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <input type="checkbox" ng-true-value="1" ng-fale-value="0" name="social_sharing"  ng-model="data.options.social_sharing">
                                <div class="emfield_error">

                                </div>
                            </div>
                            <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                <?php _e('Allow visitors to share Events on Facebook and Twitter.', 'eventprime-event-calendar-management'); ?>
                            </div>
                        </div>
                    </div>


                    <!-- <div class="em-external-integration" ng-show="data.options.social_sharing == 1">
                        <div class="emrow">
                            <div class="emfield"><?php //_e('Facebook API Key', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                            <div class="eminput">
                                <input type="text" name="fb_api_key" ng-required="data.options.social_sharing==1"  ng-model="data.options.fb_api_key">
                                <div class="emfield_error">
                                    <span ng-show="optionForm.fb_api_key.$error.required && !optionForm.fb_api_key.$pristine"><?php //_e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                                </div>
                            </div>
                            <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                <?php //_e('Enter your Facebook API key.', 'eventprime-event-calendar-management'); ?>
                            </div>
                        </div>
                    </div> -->


                    <div class="em-external-integration" >
                        <div class="emrow">
                            <div class="emfield"><?php _e('Google Calendar Sharing', 'eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <input type="checkbox" ng-true-value="1" ng-fale-value="0" name="gcal_sharing"  ng-model="data.options.gcal_sharing">
                                <div class="emfield_error">

                                </div>
                            </div>
                            <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                <?php _e('Allow users to share events through Google Calendar. Calendar API lets you display, create and modify Events.', 'eventprime-event-calendar-management'); ?>
                            </div>
                        </div>
                    </div>

                    <div ng-show="data.options.gcal_sharing == 1">
                        <div class="em-external-integration">
                            <div class="emrow">
                                <div class="emfield"><?php _e('Google Calendar Client ID', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                                <div class="eminput">
                                    <input type="text" ng-required="data.options.gcal_sharing==1" name="google_cal_client_id"  ng-model="data.options.google_cal_client_id">
                                    <div class="emfield_error">
                                        <span ng-show="optionForm.google_cal_client_id.$error.required && !optionForm.google_cal_client_id.$pristine"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                                    </div>
                                </div>
                                <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                    <?php _e('Enter your Google Calendar Client ID.', 'eventprime-event-calendar-management'); ?>
                                </div>
                            </div>
                        </div> 



                        <div class="em-external-integration">
                            <div class="emrow">
                                <div class="emfield"><?php _e('Google Calendar API Key', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                                <div class="eminput">
                                    <input type="text" ng-required="data.options.gcal_sharing==1" name="google_cal_api_key"  ng-model="data.options.google_cal_api_key">
                                    <div class="emfield_error">
                                        <span ng-show="optionForm.google_cal_api_key.$error.required && !optionForm.google_cal_api_key.$pristine"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                                    </div>
                                </div>
                                <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                    <?php _e('Enter your Google Calendar API Key.', 'eventprime-event-calendar-management'); ?>
                                </div>
                            </div>
                        </div>  
                    </div> 
                    <div class="em-external-integration" >
                        <div class="emrow">
                            <div class="emfield"><?php _e('Enable Google Recaptcha', 'eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <input type="checkbox" ng-true-value="1" ng-fale-value="0" name="google_recaptcha"  ng-model="data.options.google_recaptcha">
                                <div class="emfield_error">

                                </div>
                            </div>
                            <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                <?php _e('Enable Google Recaptcha for booking registration form. Helps in preventing spam.', 'eventprime-event-calendar-management'); ?>
                            </div>
                        </div>
                    </div>

                    <div ng-show="data.options.google_recaptcha == 1">
                        <div class="em-external-integration">
                            <div class="emrow">
                                <div class="emfield"><?php _e('Google Recaptcha Site Key', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                                <div class="eminput">
                                    <input type="text" ng-required="data.options.google_recaptcha==1" name="google_recaptcha_site_key"  ng-model="data.options.google_recaptcha_site_key">
                                    <div class="emfield_error">
                                        <span ng-show="optionForm.google_recaptcha_site_key.$error.required && !optionForm.google_recaptcha_site_key.$pristine"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                                    </div>
                                </div>
                                <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                    <?php _e('Enter your Google Recaptcha Site Key.', 'eventprime-event-calendar-management'); ?>
                                    <span><a href="https://www.google.com/recaptcha/admin/create" target="_blank"><?php _e('Generate Keys', 'eventprime-event-calendar-management'); ?></a></span>
                                </div>
                            </div>
                        </div> 

                        <div class="em-external-integration">
                            <div class="emrow">
                                <div class="emfield"><?php _e('Google Recaptcha Secret Key', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                                <div class="eminput">
                                    <input type="text" ng-required="data.options.google_recaptcha==1" name="google_recaptcha_secret_key"  ng-model="data.options.google_recaptcha_secret_key">
                                    <div class="emfield_error">
                                        <span ng-show="optionForm.google_recaptcha_secret_key.$error.required && !optionForm.google_recaptcha_secret_key.$pristine"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                                    </div>
                                </div>
                                <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                    <?php _e('Enter your Google Recaptcha Secret Key.', 'eventprime-event-calendar-management'); ?>
                                    <span><a href="https://www.google.com/recaptcha/admin/create" target="_blank"><?php _e('Generate Keys', 'eventprime-event-calendar-management'); ?></a></span>
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>

                <div ng-show="showNotification">
                    <div class="kf-db-title dbfl"><?php _e('Email Notifications');?></div>
                    <div class="emrow">
                        <div class="emfield emeditor"><?php _e('Registration Email Subject', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <input type="text" name="registration_email_subject"  ng-model="data.options.registration_email_subject">
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Subject for email that will be sent to the user on registration.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 

                    <div class="emrow">
                        <div class="emfield emeditor"><?php _e('Registration Email Body', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('registration_email_content');
                            wp_editor($content, 'registration_email_content');
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Message to be sent in email when user is registered.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>

                    <div class="emrow">
                        <div class="emfield"><?php _e('Send Booking Pending Mail', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="send_booking_pending_email" id="send_booking_pending_email"  ng-true-value="1" ng-false-value="0" ng-model="data.options.send_booking_pending_email" >
                        </div>
                    </div>
                    <div class="emrow" ng-show="data.options.send_booking_pending_email == 1">
                        <div class="emfield emeditor"><?php _e('Booking Pending Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('booking_pending_email');
                            em_add_editor('booking_pending_email', $content);
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Email with pending notification is sent to the user when either payment has not been made, or there is any issue related to it.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>

                    <div class="emrow">   
                        <div class="emfield"><?php _e('Send Booking Confirmation Mail', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="send_booking_confirm_email" id="send_booking_confirm_email"  ng-true-value="1" ng-false-value="0" ng-model="data.options.send_booking_confirm_email" >
                        </div>
                    </div>

                    <div class="emrow" ng-show="data.options.send_booking_confirm_email == 1">
                        <div class="emfield emeditor"><?php _e('Booking Confirmation Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('booking_confirmed_email');
                            em_add_editor('booking_confirmed_email', $content);
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote" ng-non-bindable><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Confirmation email is sent to the user once his/ her payment has been received and seats reserved. Use inline variables {{gcal_link}} for Add to Google calendar, {{iCal_link}} for Add download iCal file.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>


                    <div class="emrow">   
                        <div class="emfield"><?php _e('Send Booking Cancellation Mail', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="send_booking_cancellation_email" id="send_booking_cancellation_email"  ng-true-value="1" ng-false-value="0"  ng-model="data.options.send_booking_cancellation_email" >
                        </div>
                    </div>

                    <div class="emrow" ng-show="data.options.send_booking_cancellation_email == 1">
                        <div class="emfield emeditor"><?php _e('Booking Cancellation Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('booking_cancelation_email');
                            em_add_editor('booking_cancelation_email', $content);
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('User will receive this message on requesting cancellation for a booking.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>

                    <div class="emrow">
                        <div class="emfield emeditor"><?php _e('Reset User Password', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('reset_password_mail');
                            em_add_editor('reset_password_mail', $content);
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('In case user requests password reset, admin may initiate it from Booking Manager Page, triggering this email with new password to the requesting user.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>

                    <div class="emrow">   
                        <div class="emfield"><?php _e('Send Booking Refund Mail', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="send_booking_refund_email" id="send_booking_refund_email"  ng-true-value="1" ng-false-value="0" ng-model="data.options.send_booking_refund_email" >
                        </div>
                    </div>
                    <div class="emrow" ng-show="data.options.send_booking_refund_email == 1">
                        <div class="emfield emeditor"><?php _e('Booking Refund Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('booking_refund_email');
                            em_add_editor('booking_refund_email', $content);
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Refund Mail is sent to the user when admin accepts cancellation request and issues a refund.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    
                    <div class="emrow">   
                        <div class="emfield"><?php _e('Send Event Submitted Mail', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="send_event_submitted_email" id="send_event_submitted_email"  ng-true-value="1" ng-false-value="0" ng-model="data.options.send_event_submitted_email" >
                        </div>
                    </div>
                    <div class="emrow" ng-show="data.options.send_event_submitted_email == 1">
                        <div class="emfield emeditor"><?php _e('Event Submitted Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('event_submitted_email');
                            if(empty($content) || is_null($content)){
                                ob_start();
                                include(EM_BASE_DIR . 'includes/mail/event_submitted.html');
                                $content = ob_get_clean();
                            }
                            em_add_editor('event_submitted_email', $content);
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Event Submitted Email is sent to the admin when a user has submitted an event from the frontend.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    
                    <div class="emrow">
                        <div class="emfield"><?php _e('Send Event Approved Mail', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="send_event_approved_email" id="send_event_approved_email"  ng-true-value="1" ng-false-value="0" ng-model="data.options.send_event_approved_email" >
                        </div>
                    </div>
                    <div class="emrow" ng-show="data.options.send_event_approved_email == 1">
                        <div class="emfield emeditor"><?php _e('Event Approved Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('event_approved_email');
                            if(empty($content) || is_null($content)){
                                ob_start();
                                include(EM_BASE_DIR . 'includes/mail/event_approved.html');
                                $content = ob_get_clean();
                            }
                            em_add_editor('event_approved_email', $content);
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Event Approved Email is sent to the user that submitted the event when the event is approved by the admin and published on the frontend.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">   
                        <div class="emfield"><?php _e('Disable All Admin Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="disable_admin_email" id="disable_admin_email"  ng-true-value="1" ng-false-value="0"  ng-model="data.options.disable_admin_email">
                        </div>
                    </div>

                    <div class="emrow">   
                        <div class="emfield"><?php _e('Disable All Frontend Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="disable_frontend_email" id="disable_frontend_email"  ng-true-value="1" ng-false-value="0"  ng-model="data.options.disable_frontend_email">
                        </div>
                    </div>

                    <div class="emrow">   
                        <div class="emfield"><?php _e('Send Admin Booking Confirmation Mail', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="send_admin_booking_confirm_email" id="send_admin_booking_confirm_email"  ng-true-value="1" ng-false-value="0" ng-model="data.options.send_admin_booking_confirm_email" >
                        </div>
                    </div>

                    <div class="emrow" ng-show="data.options.send_admin_booking_confirm_email == 1">
                        <div class="emfield emeditor"><?php _e('Admin Booking Confirmation Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('admin_booking_confirmed_email');
                            em_add_editor('admin_booking_confirmed_email', $content);
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote" ng-non-bindable><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Confirmation email is sent to the admin once user payment has been received and seats reserved.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    
                    <div class="emrow" ng-show="data.options.is_offline_payment_enabled == 1">
                        <div class="emfield emeditor"><?php _e('Payment Confirmation Email', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <?php
                            $content = em_global_settings('payment_confirmed_email');
                            em_add_editor('payment_confirmed_email', $content);
                            ?>    
                            <div class="emfield_error">

                            </div>
                        </div>
                        <div class="emnote emeditor GSnote" ng-non-bindable><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Payment confirmation email used for sending email when payment status is set to received in offline payment extension.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showPageGSettings">
                    <div class="kf-db-title dbfl"><?php _e('Regular Settings', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Past Events from Events Directory', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-true-value="1" ng-false-value="0" name="hide_past_events" id="hide_past_events" ng-model="data.options.hide_past_events" />
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('This will hide past events from Events Directory page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>
                
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Default Calendar View', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select id="default_cal_view" name="default_cal_view" ng-model="data.options.default_cal_view">
                                <option value="month"><?php _e('Month','eventprime-event-calendar-management') ?></option>
                                <option value="week"><?php _e('Week','eventprime-event-calendar-management') ?></option>
                                <option value="day"><?php _e('Day','eventprime-event-calendar-management') ?></option>
                                <option value="card"><?php _e('Card','eventprime-event-calendar-management') ?></option>
                                <option value="listweek"><?php _e('List Week','eventprime-event-calendar-management') ?></option>
                                <option value="masonry"><?php _e('Masonry','eventprime-event-calendar-management') ?></option>
                                <option value="slider"><?php _e('Slider','eventprime-event-calendar-management') ?></option>
                                <option value="list"><?php _e('List','eventprime-event-calendar-management') ?></option>
                            </select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select the default view for the Event Calendar on the frontend.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>

                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Display View Options to Visitors', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select multiple id="front_switch_view_option" name="front_switch_view_option" ng-model="data.options.front_switch_view_option" ng-options="key as label for (key,label) in data.options.front_view_option"></select>
                        </div>
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Visitors will be able to change events list view on frontend. A new set of icons will appear for switching the view.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>
                
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Show No. of Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select id="show_no_of_events_card" name="show_no_of_events_card" ng-model="data.options.show_no_of_events_card">
                                <option value="10"><?php _e('10','eventprime-event-calendar-management') ?></option>
                                <option value="20"><?php _e('20','eventprime-event-calendar-management') ?></option>
                                <option value="30"><?php _e('30','eventprime-event-calendar-management') ?></option>
                                <option value="50"><?php _e('50','eventprime-event-calendar-management') ?></option>
                                <option value="all"><?php _e('All','eventprime-event-calendar-management') ?></option>
                                <option value="custom"><?php _e('Custom','eventprime-event-calendar-management') ?></option>
                            </select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Show no. of events on card, masonry, and list view on the frontend.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>
                
                <div ng-show="showPageGSettings" ng-if="(data.options.default_cal_view == 'card' || data.options.default_cal_view == 'masonry' || data.options.default_cal_view == 'list') && data.options.show_no_of_events_card == 'custom'">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Enter Value', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="1" id="card_view_custom_value" name="card_view_custom_value"  ng-model="data.options.card_view_custom_value">
                        </div>
                        <div class="emfield_error"></div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enter value to show no. of events on card view on the frontend.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>
            
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Disable Event Filter Options on Frontend', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-true-value="1" ng-false-value="0" name="disable_filter_options" id="disable_filter_options" ng-model="data.options.disable_filter_options" />
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('This will disable event search, filter and view options that appear on the frontend events page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>
                
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Bookings of Past Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-true-value="1" ng-false-value="0" name="hide_old_bookings" id="hide_old_bookings" ng-model="data.options.hide_old_bookings" />
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enabling this option will hide all bookings on the Attendees list in the backend that are for past events. This will also remove past events from the event selection filter on the Attendees list.'); ?>
                        </div>
                    </div> 
                </div>
                
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Time Format', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="time_format" ng-model="data.options.time_format" ng-options="key as value for (key, value) in data.options.time_formats">
                            </select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select the time format in which the event time will display on the event calendar. Note: This will only affect the time format of events on the calendar. Please also change date and time formats from WordPress General Settings to fully change time formats according to your preference.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>
                
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Enable Default Calendar Date', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-true-value="1" ng-fale-value="0" name="enable_default_calendar_date" ng-model="data.options.enable_default_calendar_date">
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable or disable default date for frontend calendar views.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>
                
                <div ng-show="showPageGSettings && data.options.enable_default_calendar_date==1">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Default Default Calendar Date', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="text" readonly="" name="default_calendar_date" ng-model="data.options.default_calendar_date">
                        </div>                   
                        <div class="emfield_error"></div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('The calendar views on the frontend will use this date as the default date for display.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>
                
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Calendar Title Format', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select id="calendar_title_format" name="calendar_title_format" ng-model="data.options.calendar_title_format">
                                <option value=""><?php _e('Select Option', 'eventprime-event-calendar-management'); ?></option>
                            </select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Frontend event calendar title format for week, day and list view.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>
                
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Calendar Column Header Format', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select id="calendar_column_header_format" name="calendar_column_header_format" ng-model="data.options.calendar_column_header_format">
                                <option value=""><?php _e('Select Option', 'eventprime-event-calendar-management'); ?></option>
                            </select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Frontend event calendar column header format for week and day view.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                </div>
                
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Upcoming Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-true-value="1" ng-fale-value="0" name="shortcode_hide_upcoming_events" ng-model="data.options.shortcode_hide_upcoming_events">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Hide upcoming events section when you publish an event, event type, event site and performer using shortcode.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>
            
                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Previous and Next Month Rows From Calendar', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-false-value="1" ng-true-value="0" name="hide_calendar_rows" ng-model="data.options.hide_calendar_rows">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Hide Previous and Next Month Rows From Calendar.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Attendee Names Mandatory', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-false-value="0" ng-true-value="1" name="required_booking_attendee_name" ng-model="data.options.required_booking_attendee_name">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Make attendee names required on bookings page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Price for Free Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-false-value="0" ng-true-value="1" name="hide_0_price_from_frontend" ng-model="data.options.hide_0_price_from_frontend">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Hide the price on frontend when booking price of an event is 0.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Datepicker Date Format', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select id="datepicker_format" name="datepicker_format" ng-model="data.options.datepicker_format">
                                <option value=""><?php _e('Select Option', 'eventprime-event-calendar-management'); ?></option>
                            </select>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select the format of date as it appears inside the datepicker selector.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Custom Link for Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-false-value="0" ng-true-value="1" name="hide_event_custom_link" ng-model="data.options.hide_event_custom_link">
                        </div>
                        <div class="emnote">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Hide custom link for website users who are are not logged in.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Display QR Code on Event Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-false-value="0" ng-true-value="1" name="show_qr_code_on_single_event" ng-model="data.options.show_qr_code_on_single_event">
                        </div>
                        <div class="emnote">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to show QR code on event page which users can scan to open it on their mobile devices.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Print QR Code on Tickets', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-false-value="0" ng-true-value="1" name="show_qr_code_on_ticket" ng-model="data.options.show_qr_code_on_ticket">
                        </div>
                        <div class="emnote">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to print QR code on tickets which users can scan using their mobile devices to open their booking details page.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide event time from calendar view', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-false-value="0" ng-true-value="1" name="hide_time_on_front_calendar" ng-model="data.options.hide_time_on_front_calendar">
                        </div>
                        <div class="emnote">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to hide event time from the front end calendar view.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showPageGSettings">
                    <div class="emrow">
                        <div class="emfield"><?php _e('Redirect user after registration', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="redirect_after_registration" ng-model="data.options.redirect_after_registration"  ng-options="pages.id as pages.name for pages in data.options.pages"></select>
                        </div>
                        <div class="emnote">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select page to redirect user after registration.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>
                
                <div ng-show="showPayments">
                    <div class="kf-db-title dbfl"><?php _e('Payments', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow kf_pricefield_checkbox">
                        <div class="emfield"> <?php _e('Payment Processor', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput em-payments">
                            <ul  class="payment_processor">
                                <li>
                                    <input type="checkbox" name="paypal_processor" id="paypal_processor" ng-true-value="1" ng-fale-value="0"  ng-model="data.options.paypal_processor" />
                                    <span><img src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/payment-paypal.png" alt=""></span>
                                    <div class="emrow"><div class="rminput" ><a ng-class="{'disable-Stripe-Config': !data.options.paypal_processor}"  ng-click="configure_paypal = true"><?php _e('Configure', 'eventprime-event-calendar-management'); ?></a></div></div>
                                    <div class="emfield_error" ng-show="optionForm.paypal_email.$invalid">
                                        <span> <?php _e('Please configure Paypal Email.', 'eventprime-event-calendar-management'); ?></span>  
                                    </div>
                                </li>
                                <?php do_action('event_magic_gs_pp'); ?>
                                <?php if (!in_array('stripe', $em->extensions)): ?>
                                <li>
                                    <input type="checkbox" name="em_stripe_processor" id="em-strip_processor"  />

                                    <span><img src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/payment-stripe.png" alt=""></span>
                                    <div class="emrow"><div class="rminput"><a href="<?php echo empty($buy_link) ? 'https://eventprime.net/plans/' : $buy_link; ?>" target="_blank" class="disable-Stripe-Config"><?php _e('Configure', 'eventprime-event-calendar-management'); ?></a></div></div>
                                </li>
                                <?php endif; ?>
                                <?php if (!in_array('offline_payments', $em->extensions)): ?>
                                <li>
                                    <input type="checkbox" name="em_offlinr_processor" id="em_offline_processor"  />
                                    <span class="payment_processor_offiline"><strong><?php _e("OFFLINE", 'eventprime-offline'); ?></strong></span>
                                    <div class="emrow"><div class="rminput"><a href="<?php echo empty($buy_link) ? 'https://eventprime.net/plans/' : $buy_link; ?>" target="_blank" class="disable-Stripe-Config"><?php _e('Configure', 'eventprime-event-calendar-management'); ?></a></div></div>
                                </li>
                                <?php endif; ?>
                            </ul>
                            <div id="kf_pproc_config_parent_backdrop" class="pg_options kf_config_pop_wrap" ng-show="configure_paypal">
                                <div id="kf_pproc_config_parent" class="paypa_settings kf_config_pop" ng-show="data.options.paypal_processor == 1">
                                    <div  class="kf_pproc_config_single" id="kf_pproc_config_paypal">
                                        <div class="kf_pproc_config_single_titlebar">
                                            <div class="kf_pproc_title">
                                                <img src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/payment-paypal.png" alt=""></div>
                                            <span ng-click="configure_paypal = false" class="kf-popup-close">×</span></div>
                                    </div> 

                                    <div class="emrow">
                                        <div class="emfield"><?php _e('Test Mode', 'eventprime-event-calendar-management'); ?></div>
                                        <div class="eminput">
                                            <input type="checkbox" name="payment_test_mode" ng-true-value="1" ng-false-value="0"  ng-model="data.options.payment_test_mode">
                                            <div class="emfield_error">

                                            </div>
                                        </div>
                                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <?php _e('Enable PayPal Sandbox. PayPal Sandbox can be used to test payments without initiating actual transactions.', 'eventprime-event-calendar-management'); ?>
                                        </div>
                                    </div> 

                                    <div class="emrow">
                                        <div class="emfield"><?php _e('Paypal Email', 'eventprime-event-calendar-management'); ?></div>
                                        <div class="eminput">
                                            <input ng-required="data.options.paypal_processor==1" type="email" name="paypal_email"  ng-model="data.options.paypal_email">
                                            <div class="emfield_error" ng-show="optionForm.paypal_email.$invalid">
                                                <span> <?php _e('Invalid Email', 'eventprime-event-calendar-management'); ?></span>  
                                            </div>
                                        </div>
                                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <?php _e('Enter your email registered with PayPal.', 'eventprime-event-calendar-management'); ?>
                                        </div>
                                    </div> 

                                    <div class="emrow">
                                        <div class="emfield"><?php _e('Paypal API Username', 'eventprime-event-calendar-management'); ?></div>
                                        <div class="eminput">
                                            <input type="text" name="paypal_api_username"  ng-model="data.options.paypal_api_username">
                                            <div class="emfield_error">

                                            </div>
                                        </div>
                                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <?php _e('Enter your PayPal API Username.', 'eventprime-event-calendar-management'); ?>
                                        </div>
                                    </div> 

                                    <div class="emrow">
                                        <div class="emfield"><?php _e('Paypal API Password', 'eventprime-event-calendar-management'); ?></div>
                                        <div class="eminput">
                                            <input type="text" name="paypal_api_password"  ng-model="data.options.paypal_api_password">
                                            <div class="emfield_error">

                                            </div>
                                        </div>
                                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <?php _e('Enter your PayPal API Password.', 'eventprime-event-calendar-management'); ?>
                                        </div>
                                    </div>  

                                    <div class="emrow">
                                        <div class="emfield"><?php _e('Paypal API Signature', 'eventprime-event-calendar-management'); ?></div>
                                        <div class="eminput">
                                            <input type="text" name="paypal_api_sig"  ng-model="data.options.paypal_api_sig">
                                            <div class="emfield_error">

                                            </div>
                                        </div>
                                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <?php _e('Enter your PayPal API Signature.', 'eventprime-event-calendar-management'); ?>
                                        </div>
                                    </div>

                                    <div class="emrow">
                                        <div class="emfield"><?php _e('Enable New PayPal', 'eventprime-event-calendar-management'); ?></div>
                                        <div class="eminput">
                                            <input type="checkbox" name="modern_paypal" ng-true-value="1" ng-false-value="0"  ng-model="data.options.modern_paypal">
                                            <div class="emfield_error">

                                            </div>
                                        </div>
                                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <?php _e('This will turn on the new PayPal checkout experience which completes the payment without requiring the user to leave your site.', 'eventprime-event-calendar-management'); ?>
                                        </div>
                                    </div>

                                    <div class="emrow" ng-show="data.options.modern_paypal==1">
                                        <div class="emfield"><?php _e('Paypal Client ID', 'eventprime-event-calendar-management'); ?></div>
                                        <div class="eminput">
                                            <input type="text" ng-required="data.options.modern_paypal==1" name="paypal_client_id"  ng-model="data.options.paypal_client_id">
                                            <div class="emfield_error" ng-show="optionForm.paypal_client_id.$invalid">
                                                <span> <?php _e('Please fill Paypal Client ID', 'eventprime-event-calendar-management'); ?></span>  
                                            </div>
                                        </div>
                                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <?php _e('Enter your PayPal Client ID.', 'eventprime-event-calendar-management'); ?>
                                        </div>
                                    </div> 

                                    <div class="emrow">
                                        <div class="dbfl kf-buttonarea"><button class="btn btn-primary" ng-click="saveSettings(optionForm.$valid)" ng-disabled="postForm.$invalid" value="<?php _e('Save', 'eventprime-event-calendar-management'); ?>" ><?php _e('Save', 'eventprime-event-calendar-management'); ?></button></div>
                                    </div>
                                </div>

                            </div> 
                            <?php do_action('event_magic_gs_pp_options'); ?>
                            <?php do_action('event_magic_gs_ppof_options'); ?>
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select your payment gateway.', 'eventprime-event-calendar-management'); ?>
                        </div>
                        <div class="emrow">
                            <div class="emfield"><?php _e('Currency', 'eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <select id="currency" name="currency" ng-model="data.options.currency" ng-options="cur.key as cur.label for cur in data.options.currencies"></select>
                                <div class="emfield_error">
                                </div>
                            </div>                        
                            <div class="emnote "><i class="fa fa-info-circle" aria-hidden="true"></i>
                                <?php _e('Default Currency for accepting payments. Usually, this will be default currency in your PayPal account.', 'eventprime-event-calendar-management'); ?>
                            </div>
                        </div>

                        <div class="emrow">
                            <div class="emfield"><?php _e('Currency Symbol Position', 'eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <select id="currency_position" name="currency_position" ng-model="data.options.currency_position" ng-options="key as label for (key,label) in data.options.currency_view_option">
                                </select>
                            </div>
                            <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                <?php _e('Select where you wish to place currency symbol with prices.', 'eventprime-event-calendar-management'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div ng-show="showCustomCSS">
                    <div class="kf-db-title dbfl"><?php _e('Custom CSS', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield emeditor"><?php _e('Custom CSS', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput emeditor">
                            <textarea id="custom_css" name="custom_css" ng-model="data.options.custom_css"></textarea>
                        </div>                        
                        <div class="emnote emeditor"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Add your very own custom CSS code here to change the appearance of EventPrime frontend according to your preference.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>
                
                <div ng-show="showUserEventSubmit">
                    <div class="kf-db-title dbfl"><?php _e('Frontend Event Submission', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Confirmation Message', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="text" name="ues_confirm_message" ng-model="data.options.ues_confirm_message">
                            <div class="emfield_error">
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('This message will display to the user after event has been submitted.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Allow submission by anonymous users', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-true-value="1" ng-false-value="0" name="allow_submission_by_anonymous_user" id="allow_submission_by_anonymous_user" ng-model="data.options.allow_submission_by_anonymous_user" />
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('This will allow frontend user to submit event without login.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow" ng-hide="data.options.allow_submission_by_anonymous_user == 1">
                        <div class="emfield"><?php _e('Login Message', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="text" name="ues_login_message" ng-model="data.options.ues_login_message">
                            <div class="emfield_error">
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('This message will display to the user if he/she is not logged in to submit event.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Status', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select name="ues_default_status" ng-model="data.options.ues_default_status" ng-options="key as label for (key,label) in data.options.status_list"></select>
                        </div>                        
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('This will be the status of the event once it is submitted from the frontend. Setting this to "Active" will publish the event on frontend as soon as it is submitted.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow" ng-hide="data.options.allow_submission_by_anonymous_user == 1">
                        <div class="emfield"><?php _e('Restricted Submission By User Roles', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select multiple id="select_user_roles" name="frontend_submission_roles" ng-model="data.options.frontend_submission_roles" ng-options="key as label for (key,label) in data.options.user_roles"></select>
                        </div>
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Only selected users will be allow to do submission of frontend event.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow" ng-hide="data.options.allow_submission_by_anonymous_user == 1">
                        <div class="emfield"><?php _e('Restricted Message', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="text" name="ues_restricted_submission_message" ng-model="data.options.ues_restricted_submission_message">
                            <div class="emfield_error">
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('This message will display if user is restricted.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Frontend Event Submission Sections', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <ul>
                                <li ng-repeat="(key, value) in data.options.fes_sections">
                                    <input type="checkbox" ng-true-value="1" ng-false-value="0" name="frontend_submission_sections[]" ng-model="data.options.frontend_submission_sections[key]" /> {{value}}
                                </li>
                            </ul>
                        </div>
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select the fields which you wish to appear on frontend event submission form.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Required Fields', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <ul>
                                <li ng-repeat="(key, value) in data.options.fes_required">
                                    <input type="checkbox" ng-true-value="1" ng-false-value="0" name="frontend_submission_required[]" ng-model="data.options.frontend_submission_required[key]" /> {{value}}
                                </li>
                            </ul>
                        </div>
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select the fields which you wish to make required on frontend even submission form.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showCustomBookingFields">
                    <div class="kf-db-title dbfl"><?php _e('Attendee Booking Fields', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="eminput em-cbf-builder">
                            <div class="em-cbf-container">
                                <ul id="em-cbf-fields">
                                    <custom-field-data></custom-field-data>
                                </ul>
                                <div id="em-cbf-types">
                                    <button type="button" class="button" ng-repeat="(key, value) in data.options.custom_fields_option" data-type="{{key}}" ng-click="addFieldInCustomizer(key)">{{value}}</button> 
                                </div>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Use custom fields to collect additional attendees information at the time of checkout.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showButtonSettings">
                    <div class="kf-db-title dbfl"><?php _e('General Labels', 'eventprime-event-seating'); ?></div>
                    <div class="emrow" ng-repeat="button_title in data.options.labelsections">
                        <div class="emfield no-text-transform"><?php _e('{{button_title}}', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="text" ng-model="data.options.button_titles[button_title]" >         
                            <div class="emfield_error"> </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enter the title you want to display on button in the given input box.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    
                    <div class="kf-db-title dbfl"><?php _e('Button', 'eventprime-event-seating'); ?></div>
                    <div class="emrow" ng-repeat="button_title in data.options.buttonsections">
                        <div class="emfield"><?php _e('{{button_title}}', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="text" ng-model="data.options.button_titles[button_title]" >         
                            <div class="emfield_error"> </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enter the title you want to display on button in the given input box.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>

                <div ng-show="showSeoSettings">
                    <div class="kf-db-title dbfl"><?php _e('SEO', 'eventprime-event-seating'); ?>&nbsp;&nbsp;<sup style="color:orange;">(Beta)</sup></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Enable Pretty URLs', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" ng-true-value="1" ng-fale-value="0" name="enable_seo_urls"  ng-model="data.options.enable_seo_urls">
                            <div class="emfield_error"></div>
                        </div>
                        <div class="emnote GSnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Turning this on will replace current ID based event URLs with custom subdirectory and slug structure. You may wish to redirect your old URLs to new ones if they are already cached by search engines.', 'eventprime-event-calendar-management'); ?>
                            <span><a href="<?php echo esc_url('https://yoast.com/create-301-redirect-wordpress/');?>" target="_blank"><?php _e('More information about setting up redirections', 'eventprime-event-calendar-management'); ?></a></span>
                        </div>
                    </div>
                    <div ng-show="data.options.enable_seo_urls == 1" class="emrow" ng-repeat="(key, value) in data.options.seo_url">
                        <div class="emfield">{{value.title}}</div>
                        <div class="eminput">
                            <input type="text" ng-model="data.options.seo_urls[key]" >         
                            <div class="emfield_error"> </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            {{value.desc}}
                        </div>
                    </div>
                </div>
                
                <div ng-show="showPerformerSettings">
                    <div class="kf-db-title dbfl"><?php _e('Performers Directory View', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Display Style', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select id="performer_display_view" name="performer_display_view" ng-model="data.options.performer_display_view" ng-options="key as label for (key,label) in data.options.performer_front_view_options"></select>
                        </div>
                        <div class="emfield_error"></div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select how you wish to display performers on performers directory pages.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow" ng-show="data.options.performer_display_view == 'box'">
                        <div class="emfield"><?php _e('Card Background', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <ul>
                                <li ng-repeat="box_color in data.options.performer_box_color">
                                    <input type="text" class="jscolor_{{$index}}" id="box_view_color_{{$index}}" name="performer_box_color[]" ng-model="data.options.performer_box_color[$index]" ng-change="change_box_color($index)">
                                </li>
                            </ul>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Please choose 4 different background colors for this view. These colors will be used to display alternative cards on the frontend.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Items per Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="0" id="performer_limit" name="performer_limit"  ng-model="data.options.performer_limit">
                            <div class="emfield_error">
                                <span ng-show="optionForm.performer_limit.$invalid"><?php _e('Performer limit value is lesser than minimum value.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of performers to display on a single page before pagination or \'Load More\' button appears.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Columns', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="1" max="4" id="performer_no_of_columns" name="performer_no_of_columns"  ng-model="data.options.performer_no_of_columns">
                            <div class="emfield_error">
                                <span ng-show="optionForm.performer_no_of_columns.$invalid"><?php _e('Column size must be 1 to 4.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of columns in card or box view. Maximum 4 allowed.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Load More Button', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="performer_load_more" id="performer_load_more"  ng-true-value="1" ng-false-value="0" ng-model="data.options.performer_load_more" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display \'Load More\' button at the end of list to load remaining items based on the user input.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Search', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="performer_search" id="performer_search"  ng-true-value="1" ng-false-value="0" ng-model="data.options.performer_search" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to display search box above the list.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                  
                    <div class="emrow"> <?php _e('Shortcode', 'eventprime-event-calendar-management'); ?> : [em_performers display_style="card/list/box" limit="{NUMBER}" cols="{NUMBER}"  load_more="0 or 1" search="0 or 1" featured="0 or 1" popular="0 or 1"] </div>
                    <div class="kf-db-title dbfl"><?php _e('Single Performer View', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Show Related Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_performer_show_events" id="single_performer_show_events"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_performer_show_events" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display events for the performer below the performer details.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Display Type', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select id="single_performer_event_display_view" name="single_performer_event_display_view" ng-model="data.options.single_performer_event_display_view" ng-options="key as label for (key,label) in data.options.single_performer_event_front_view_options"></select>
                        </div>                   
                        <div class="emfield_error"></div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select the display style of related events. Simple list is a basic list view which shows the items in table format without any images.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Limit', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="0" id="single_performer_event_limit" name="single_performer_event_limit"  ng-model="data.options.single_performer_event_limit">
                            <div class="emfield_error">
                                <span ng-show="optionForm.single_performer_event_limit.$invalid"><?php _e('Event limit value is lesser than minimum value.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Limit the max number of related events to display under the performer details.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Columns', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="1" max="4" id="single_performer_event_column" name="single_performer_event_column"  ng-model="data.options.single_performer_event_column">
                            <div class="emfield_error">
                                <span ng-show="optionForm.single_performer_event_column.$invalid"><?php _e('Column size must be 1 to 4.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of columns in event card or box view. Maximum 4 allowed.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Load More', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_performer_event_load_more" id="single_performer_event_load_more" ng-true-value="1" ng-false-value="0" ng-model="data.options.single_performer_event_load_more" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display \'Load More\' button at the end of related events list to load more events based on the user input.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Past Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_performer_hide_past_events" id="single_performer_hide_past_events"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_performer_hide_past_events" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to hide related events which have already ended.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow"> <?php _e('Shortcode', 'eventprime-event-calendar-management'); ?> : [em_performer id="{PERFORMER_ID}" event_style="card/list/mini-list" event_limit="{NUMBER}" event_cols="{NUMBER}"  load_more="0 or 1" hide_past_event="0 or 1"] </div>  
                </div>

                <div ng-show="showEventTypeSettings">
                    <div class="kf-db-title dbfl"><?php _e('Event Types Directory View', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Display Style', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select id="type_display_view" name="type_display_view" ng-model="data.options.type_display_view" ng-options="key as label for (key,label) in data.options.type_front_view_options"></select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select how you wish to display event types on event types directory pages.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow" ng-show="data.options.type_display_view == 'box'">
                        <div class="emfield"><?php _e('Card Background', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <ul>
                                <li ng-repeat="box_color in data.options.type_box_color">
                                    <input type="text" class="jscolor_{{$index}}" id="box_view_color_{{$index}}" name="type_box_color[]" ng-model="data.options.type_box_color[$index]" ng-change="change_type_box_color($index)">
                                </li>
                            </ul>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Please choose 4 different background colors for this view. These colors will be used to display alternative cards on the frontend.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Items per Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="0" id="type_limit" name="type_limit"  ng-model="data.options.type_limit" ng-required ="true">
                            <div class="emfield_error">
                                <span ng-show="optionForm.type_limit.$invalid && !optionForm.type_limit.$error.required"><?php _e('Event type limit value is lesser than minimum value.', 'eventprime-event-calendar-management'); ?></span>
                                <span ng-show="optionForm.type_limit.$error.required"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of event types to display on a single page before pagination or \'Load More\' button appears.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Columns', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="1" max="4" id="type_no_of_columns" name="type_no_of_columns"  ng-model="data.options.type_no_of_columns" ng-required ="true">
                            <div class="emfield_error">
                                <span ng-show="optionForm.type_no_of_columns.$invalid && !optionForm.type_no_of_columns.$error.required"><?php _e('Column size must be 1 to 4.', 'eventprime-event-calendar-management'); ?></span>
                                <span ng-show="optionForm.type_no_of_columns.$error.required"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of columns in card or box view. Maximum 4 allowed.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Load More Button', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="type_load_more" id="type_load_more"  ng-true-value="1" ng-false-value="0" ng-model="data.options.type_load_more" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display \'Load More\' button at the end of list to load remaining items based on the user input.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Search', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="type_search" id="type_search"  ng-true-value="1" ng-false-value="0" ng-model="data.options.type_search" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to display search box above the list.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow"> <?php _e('Shortcode', 'eventprime-event-calendar-management'); ?> : [em_event_types display_style="card/list/box" limit="{NUMBER}" cols="{NUMBER}"  load_more="0 or 1" search="0 or 1" featured="0 or 1" popular="0 or 1"] </div>
                    <div class="kf-db-title dbfl"><?php _e('Single Event Type View', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Show Related Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_type_show_events" id="single_type_show_events" ng-true-value="1" ng-false-value="0" ng-model="data.options.single_type_show_events" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display events for the event type below the event type details.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Display Style', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                        <select id="single_type_event_display_view" name="single_type_event_display_view" ng-model="data.options.single_type_event_display_view" ng-options="key as label for (key,label) in data.options.single_type_efv_options"></select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select the display style of related events. Simple list is a basic list view which shows the items in table format without any images.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Limit', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="0" id="single_type_event_limit" name="single_type_event_limit"  ng-model="data.options.single_type_event_limit" ng-required ="true">
                            <div class="emfield_error">
                                <span ng-show="optionForm.single_type_event_limit.$invalid  && !optionForm.single_type_event_limit.$error.required"><?php _e('Event limit value is lesser than minimum value.', 'eventprime-event-calendar-management'); ?></span>
                                <span ng-show="optionForm.single_type_event_limit.$error.required"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Limit the max number of related events to display under the performer details.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Columns', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="1" max="4" id="single_type_event_column" name="single_type_event_column"  ng-model="data.options.single_type_event_column" ng-required ="true">
                            <div class="emfield_error">
                                <span ng-show="optionForm.single_type_event_column.$invalid  && !optionForm.single_type_event_column.$error.required"><?php _e('Column size must be 1 to 4.', 'eventprime-event-calendar-management'); ?></span>
                                <span ng-show="optionForm.single_type_event_column.$error.required"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of columns in event card or box view. Maximum 4 allowed.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Load More', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_type_event_load_more" id="single_type_event_load_more"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_type_event_load_more" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display \'Load More\' button at the end of related events list to load more events based on the user input.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Past Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_type_hide_past_events" id="single_type_hide_past_events"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_type_hide_past_events" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to hide related events which have already ended.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow"><?php _e('Shortcode', 'eventprime-event-calendar-management'); ?> : [em_event_type id="{EVENT_TYPE_ID}" event_style="card/list/mini-list" event_limit="{NUMBER}" event_cols="{NUMBER}"  load_more="0 or 1" hide_past_events="0 or 1"] </div>
                </div>

                <div ng-show="showOrganizerSettings">
                    <div class="kf-db-title dbfl"><?php _e('Organizers Directory View', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Display Style', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <select id="organizer_display_view" name="organizer_display_view" ng-model="data.options.organizer_display_view" ng-options="key as label for (key,label) in data.options.organizer_front_view_options"></select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select how you wish to display organizers on organizers directory pages.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow" ng-show="data.options.organizer_display_view == 'box'">
                        <div class="emfield"><?php _e('Card Background', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <ul>
                                <li ng-repeat="box_color in data.options.organizer_box_color">
                                    <input type="text" class="jscolor_{{$index}}" id="box_view_color_{{$index}}" name="organizer_box_color[]" ng-model="data.options.organizer_box_color[$index]" ng-change="change_organizer_box_color($index)">
                                </li>
                            </ul>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Please choose 4 different background colors for this view. These colors will be used to display alternative cards on the frontend.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Items per Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="0" id="organizer_limit" name="organizer_limit"  ng-model="data.options.organizer_limit">
                            <div class="emfield_error">
                                <span ng-show="optionForm.organizer_limit.$invalid"><?php _e('Organizer limit value is lesser than minimum value.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of organizers to display on a single page before pagination or \'Load More\' button appears.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Columns', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="1" max="4" id="organizer_no_of_columns" name="organizer_no_of_columns"  ng-model="data.options.organizer_no_of_columns">
                            <div class="emfield_error">
                                <span ng-show="optionForm.organizer_no_of_columns.$invalid"><?php _e('Column size must be 1 to 4.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of columns in card or box view. Maximum 4 allowed.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Load More Button', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="organizer_load_more" id="organizer_load_more"  ng-true-value="1" ng-false-value="0" ng-model="data.options.organizer_load_more" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display \'Load More\' button at the end of list to load remaining items based on the user input.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Search', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="organizer_search" id="organizer_search"  ng-true-value="1" ng-false-value="0" ng-model="data.options.organizer_search" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to display search box above the list.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow"> <?php _e('Shortcode', 'eventprime-event-calendar-management'); ?> : [em_event_organizers display_style="card/list/box" limit="{NUMBER}" cols="{NUMBER}"  load_more="0 or 1" search="0 or 1" featured="0 or 1" popular="0 or 1"] </div>
                    <div class="kf-db-title dbfl"><?php _e('Single Organizer View', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Show Related Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_organizer_show_events" id="single_organizer_show_events"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_organizer_show_events" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display events for the organizer below the organizer details.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Display Style', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                        <select id="single_organizer_event_display_view" name="single_performer_event_display_view" ng-model="data.options.single_organizer_event_display_view" ng-options="key as label for (key,label) in data.options.single_organizer_efv_options"></select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select the display style of related events. Simple list is a basic list view which shows the items in table format without any images.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Limit', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="0" id="single_organizer_event_limit" name="single_organizer_event_limit"  ng-model="data.options.single_organizer_event_limit">
                            <div class="emfield_error">
                                <span ng-show="optionForm.single_organizer_event_limit.$invalid"><?php _e('Event limit value is lesser than minimum value.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Limit the max number of related events to display under the organizer details.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Columns', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="1" max="4" id="single_organizer_event_column" name="single_organizer_event_column"  ng-model="data.options.single_organizer_event_column">
                            <div class="emfield_error">
                                <span ng-show="optionForm.single_organizer_event_column.$invalid"><?php _e('Column size must be 1 to 4.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of columns in event card or box view. Maximum 4 allowed.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Load More', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_organizer_event_load_more" id="single_organizer_event_load_more"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_organizer_event_load_more" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display \'Load More\' button at the end of related events list to load more events based on the user input.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Past Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_organizer_hide_past_events" id="single_organizer_hide_past_events"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_organizer_hide_past_events" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to hide related events which have already ended.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow"><?php _e('Shortcode', 'eventprime-event-calendar-management'); ?> : [em_event_organizer id="{EVENT_ORGANIZER_ID}" event_style="card/list/mini-list" event_limit="{NUMBER}" event_cols="{NUMBER}"  load_more="0 or 1" hide_past_events="0 or 1"] </div>  
                </div>
                
                <div ng-show="showEventSiteSettings">
                    <div class="kf-db-title dbfl"><?php _e('Event Site/Location Directory View', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Display Style', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                        <select id="venue_display_view" name="venue_display_view" ng-model="data.options.venue_display_view" ng-options="key as label for (key,label) in data.options.venue_front_view_options"></select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select how you wish to display event sites on event sites directory pages.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow" ng-show="data.options.venue_display_view == 'box'">
                        <div class="emfield"><?php _e('Card Background', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <ul>
                                <li ng-repeat="box_color in data.options.venue_box_color">
                                    <input type="text" class="jscolor_{{$index}}" id="box_view_color_{{$index}}" name="venue_box_color[]" ng-model="data.options.venue_box_color[$index]" ng-change="change_box_color($index)">
                                </li>
                            </ul>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Please choose 4 different background colors for this view. These colors will be used to display alternative cards on the frontend.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Items Per Page', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="0" id="venue_limit" name="venue_limit"  ng-model="data.options.venue_limit" ng-required ="true">
                            <div class="emfield_error">
                                <span ng-show="optionForm.venue_limit.$invalid && !optionForm.venue_limit.$error.required"><?php _e('Event site limit value is lesser than minimum value.', 'eventprime-event-calendar-management'); ?></span>
                                <span ng-show="optionForm.venue_limit.$error.required"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e("No. of event sites to display on a single page before pagination or 'Load More' button appears.", 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Columns', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="1" max="4" id="venue_no_of_columns" name="venue_no_of_columns"  ng-model="data.options.venue_no_of_columns" ng-required ="true">
                            <div class="emfield_error">
                                <span ng-show="optionForm.venue_no_of_columns.$invalid && !optionForm.venue_no_of_columns.$error.required"><?php _e('Column size must be 1 to 4.', 'eventprime-event-calendar-management'); ?></span>
                                <span ng-show="optionForm.venue_no_of_columns.$error.required"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of columns in card or box view. Maximum 4 allowed.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Load More Button', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="venue_load_more" id="venue_load_more"  ng-true-value="1" ng-false-value="0" ng-model="data.options.venue_load_more" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e("Display 'Load More' button at the end of list to load remaining items based on the user input.", 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Search', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="venue_search" id="venue_search"  ng-true-value="1" ng-false-value="0" ng-model="data.options.venue_search" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to display search box above the list.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow"> <?php _e('Shortcode', 'eventprime-event-calendar-management'); ?> : [em_sites display_style="card/list/box" limit="{NUMBER}" cols="{NUMBER}"  load_more="0 or 1" search="0 or 1" featured="0 or 1" popular="0 or 1"] </div>
                    <div class="kf-db-title dbfl"><?php _e('Single Event Site/Location View', 'eventprime-event-calendar-management'); ?></div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Show Related Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_venue_show_events" id="single_venue_show_events"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_venue_show_events" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Display events for the event site below the event site details.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Display Style', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                        <select id="single_venue_event_display_view" name="single_venue_event_display_view" ng-model="data.options.single_venue_event_display_view" ng-options="key as label for (key,label) in data.options.single_venue_efv_options"></select>
                        </div>                   
                        <div class="emfield_error">
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Select the event display style on single event site page. Simple list is a basic list view which shows the items in table format without any images.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Limit', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="0" id="single_venue_event_limit" name="single_venue_event_limit"  ng-model="data.options.single_venue_event_limit" ng-required ="true">
                            <div class="emfield_error">
                                <span ng-show="optionForm.single_venue_event_limit.$invalid  && !optionForm.single_venue_event_limit.$error.required"><?php _e('Event limit value is lesser than minimum value.', 'eventprime-event-calendar-management'); ?></span>
                                <span ng-show="optionForm.single_venue_event_limit.$error.required"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Limit the max number of related events to display under the event site details.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Event Columns', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="number" min="1" max="4" id="single_venue_event_column" name="single_venue_event_column"  ng-model="data.options.single_type_event_column" ng-required ="true">
                            <div class="emfield_error">
                                <span ng-show="optionForm.single_venue_event_column.$invalid  && !optionForm.single_venue_event_column.$error.required"><?php _e('Column size must be 1 to 4.', 'eventprime-event-calendar-management'); ?></span>
                                <span ng-show="optionForm.single_venue_event_column.$error.required"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('No. of columns in event card view. Maximum 4 allowed.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div> 
                    <div class="emrow">
                        <div class="emfield"><?php _e('Load More', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_venue_event_load_more" id="single_venue_event_load_more"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_venue_event_load_more" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e("Display 'Load More' button at the end of related events list to load more events based on the user input.", 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow">
                        <div class="emfield"><?php _e('Hide Past Events', 'eventprime-event-calendar-management'); ?></div>
                        <div class="eminput">
                            <input type="checkbox" name="single_venue_hide_past_events" id="single_venue_hide_past_events"  ng-true-value="1" ng-false-value="0" ng-model="data.options.single_venue_hide_past_events" >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Enable to hide related events which have already ended.', 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                    <div class="emrow"><?php _e('Shortcode', 'eventprime-event-calendar-management'); ?> : [em_event_site id="{EVENT_SITE_ID}" event_style="card/list/mini-list" event_limit="{NUMBER}" event_cols="{NUMBER}"  load_more="0 or 1" hide_past_events="0 or 1"] </div>
                </div>
                
                <?php do_action('event_magic_gs_popup'); ?>

                <div class="dbfl kf-buttonarea" ng-show="enabledAnyGlobalService">
                    <div class="em_cancel"><a class="kf-cancel" href="javascript:void(0)" ng-click="showSettingOptions()">&#8592; &nbsp;<?php _e('Go to Settings Area', 'eventprime-event-calendar-management'); ?></a></div>
                    <button type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid"><?php _e('Save', 'eventprime-event-calendar-management'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="ep-payment-config-single" id="ep-payment-config-stripe" style="display: none;">
    <div  class="ep-payment-config-overlay"></div>
    <div class="ep-payment-config-view">
        <div class="ep-payment-config-titlebar">
            <div class="ep-payment-config-title">
                <img src="<?php echo EM_BASE_URL; ?>/includes/admin/template/images/payment-stripe.png" alt="">
            </div>
            <span class="ep-payment-config-close">&#215;</span>
        </div>
        <div class="ep-payment-config-wrap">
            <div class="ep-promo-payment-content">
               <p class="ep_buy_pro_wrap"><span class="ep_buy_pro_inline">To unlock this feature (and many more), please upgrade <a href="<?php echo empty($buy_link) ? 'https://eventprime.net/plans/' : $buy_link; ?>" target="blank">Click here</a></span></p>   
            </div>
        </div>
    </div>
</div>

<div class="ep-payment-config-single" id="ep-payment-config-offiline" style="display: none;">
    <div  class="ep-payment-config-overlay"></div>
    <div class="ep-payment-config-view">
        <div class="ep-payment-config-titlebar">
          <div class="ep-payment-config-title">
          <strong>Offline</strong>      
          </div>
          <span class="ep-payment-config-close">&#215;</span>
        </div>
        <div class="ep-payment-config-wrap">
            <div class="ep-promo-payment-content">
               <p class="ep_buy_pro_wrap"><span class="ep_buy_pro_inline">To unlock this feature (and many more), please upgrade <a href="<?php echo empty($buy_link) ? 'https://eventprime.net/plans/' : $buy_link; ?>" target="blank">Click here</a></span></p>   
            </div>
        </div>
    </div>
</div>

<div id="ep-setting-popup" class="ep-setting-modal-view" style="display: none;">
    <div class="ep-setting-modal-overlay ep-setting-popup-overlay-fade-in"></div>
    <div class="ep-setting-modal-wrap ep-setting-popup-out">
        <div class="ep-setting-modal-titlebar">
            <span class="ep-setting-modal-close">×</span>
        </div>
        <div class="ep-setting-container">                
            <!--Guest Booking-->
            <div class="ep-extension-wrap" id="ep-guest-booking-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/event-guest-booking-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Guest Booking', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Configure guest bookings.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des">
                    <?php _e('Allow attendees to complete their event bookings without registering or logging in.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
            </div> 
            <!--Guest Booking End-->
            <!--Recurring Events-->
            <div class="ep-extension-wrap" id="ep-recurring-events-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-recurring-events-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Recurring Events', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Create recurring events.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Create events that recur by your specified numbers of days, weeks, months, or years. Make updates to all recurring events at once by updating the main event. Or make custom changes to individual recurring events, such as different performers, event sites, booking amount etc.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong><?php _e('Congratulations', 'eventprime-event-calendar-management');?></strong>, <?php _e('you have successfully installed and activated this extension!', 'eventprime-event-calendar-management');?></div>
                    <div class="ep-extension-modal-des">
                        <?php _e('Recurring events can be created from regular event creation form. You will now see new options to set an event as recurring.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div> 
            <!--Recurring Events End-->
            <!--Events Analytics-->
            <div class="ep-extension-wrap" id="ep-events-analytics-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-analytics-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Events Analytics', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Analyze bookings data.', 'eventprime-event-calendar-management'); ?><span class="ep-ext-label">Free</span></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Stay updated on all the Revenue and Bookings coming your way through EventPrime. The Event Analytics extension empowers you with data and graphs that you need to know how much your events are connecting with their audience.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="https://eventprime.net/extensions/event-analytics/" target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong><?php _e('Congratulations', 'eventprime-event-calendar-management');?></strong>, <?php _e('you have successfully installed and activated this extension!', 'eventprime-event-calendar-management');?></div>
                    <div class="ep-extension-modal-des">
                        <?php 
                        echo sprintf(__('Event Analytics can be accessed by clicking new menu item in the left menu. %s to check it now.'), '<a href="'.admin_url('admin.php?page=em_analytics').'">'.__('Click here', 'eventprime-event-calendar-management').'</a>'); ?>
                    </div>
                </div>
            </div>                 
            <!--Events Analytics End-->
            <!--Attendees List-->
            <div class="ep-extension-wrap" id="ep-attendees-lists-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-attendees-list-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Attendees List', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Publish frontend attendee lists.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Display names of your Event Attendees on the Event page. Or within the new Attendees List widget.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                   <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                        <?php _e('Individual event settings will now have option to display list of users who have bookings for the event.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div>
            <!--Attendees List End-->
            <!--Coupon Codes-->
            <div class="ep-extension-wrap" id="ep-coupon-codes-ext" style="display: none">
               <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/coupon-code-extension-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Coupon Codes', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Create custom coupon codes.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Create and activate coupon codes for allowing Attendees for book for events at a discount. Set discount type and limits on coupon code usage, or deactivate at will.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                        <?php 
                        echo sprintf(__('Discount coupons can now be created by clicking on new menu item in the left menu. %s to check it now.'), '<a href="'.admin_url('admin.php?page=em_coupons').'">'.__('Click here', 'eventprime-event-calendar-management').'</a>'); ?>
                    </div>
                </div>
            </div>
            <!--Coupon Codes End--> 
            <!--Event Sponsorses-->
            <div class="ep-extension-wrap" id="ep-event-sponsors-ext" style="display: none">
               <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-sponser-icon.png"> </div>
               <div class="ep-extension-modal-title"> <?php _e('Event Sponsors', 'eventprime-event-calendar-management'); ?></div>
               <div class="ep-extension-modal-subhead"><?php _e('Add sponsors to events.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                   <?php _e('Add Sponsor(s) to your events. Upload Sponsor logos and they will appear on the event page alongside all other details of the event.', 'eventprime-event-calendar-management'); ?>
                   <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                   <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                       <?php _e('Option to add event sponsors is now available inside individual event dashboards.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div>
            <!--Event Sponsors End-->
            <!--Automatic Discounts-->
            <div class="ep-extension-wrap" id="ep-automatic-discounts-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/event-early-bird-discount-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Automatic Discounts', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Auto-apply conditional discounts.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                   <?php _e('Automatically display discounts on an event for a user based on Admin rules. With Automatic Discount Extension, you can create and activate discounts by setting rules (eligibility criteria) to offer the eligible users a discount on bookings. The discounts are automatically applied to the bookings.', 'eventprime-event-calendar-management'); ?>
                   <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
            </div>                
            <!--Automatic Discounts End-->  
            <!--Live Seating-->
            <div class="ep-extension-wrap" id="ep-live-seating-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/seating-integration-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Live Seating', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Add seat plan and seat selection.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Add live seat selection on your events and provide seat based tickets to your event attendees. Set a seating arrangement for all your Event Sites with specific rows, columns, and walking aisles using EventPrime\'s very own Event Site Seating Builder.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                        <?php _e('Live seating configuration is now available inside individual event dashboards.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div> 
            <!--Live Seating End-->  
            <!--Offline Payments-->
            <div class="ep-extension-wrap" id="ep-offline-payments-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-offline-payment.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Offline Payments', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Allow offline payments.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e(' Don\'t want to use any online payment gateway to collect your event booking payments? Don\'t worry. With the Offline Payments extension, you can accept event bookings online while you collect booking payments from attendees offline.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                        <?php _e(' Offline payment option will now automatically appear for users during booking checkout.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div> 
            <!--Offline Payments End-->  
            <!--Stripe Payments-->
            <div class="ep-extension-wrap" id="ep-stripe-payments-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-stripe-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Stripe Payments', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Accept payments via Stripe.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Start accepting Event Booking payments using the Stripe Payment Gateway. By integrating Stripe with EventPrime, event attendees can now pay with their credit cards while you receive the payment in your Stripe account.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                        <?php
                        echo sprintf(__('You can configure your Stripe credentials in Global Settings -> Payments section.'), '<a href="'.admin_url('admin.php?page=em_global_settings').'">'.__('Go there now.', 'eventprime-event-calendar-management').'</a>'); ?>
                    </div>
                </div>
            </div> 
            <!--Stripe Payments End-->
            <!--Event Wishlist-->
            <div class="ep-extension-wrap" id="ep-event-wishlist-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-save-events-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Event Wishlist', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Allow users to wish-list events.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Users can now wishlist events that they would like to attend and can see the list of all their wishlisted events on their frontend profiles.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                        <?php _e('Users can now wishlist events from event cards and event pages. Wishlists can be managed from user account area.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div> 
            <!--Event Wishlist End-->
            <!--Event List Widgets-->
            <div class="ep-extension-wrap" id="ep-event-list-widget-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/event-more-widget-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Event List Widgets', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Display event data on frontend.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Add 3 new Event Listing widgets to your website. These are the Popular Events list, Featured Events list, and Related Events list widgets.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                        <?php _e('New EventPrime widgets are now available in Appearance -> Widgets area. Each widget has its own configuration options.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div> 
            <!--Event List Widgets End-->  
            <!--Event Comments-->
            <div class="ep-extension-wrap" id="ep-event-event-comments-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-event-comment-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Event Comments', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Allow users to post comments on event page.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des">
                    <?php _e('Allow users to post comments on EventPrime events. Admins can manage these comments the same way as they manage WordPress comments.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
            </div> 
            <!--Event Comments End--> 
            <!--Event Admin Attendee Booking-->
            <div class="ep-extension-wrap" id="ep-event-event-attendee-booking-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-manually-attendees-booking.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Admin Attendee Bookings', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Create bookings from dashboard.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Admins can now create custom attendee bookings from the backend EventPrime dashboard.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                        <?php _e('You can now create bookings from the dashboard area by visiting Attendees left menu item.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div> 
            <!--Event Admin Attendee Booking-->
            <!--Google Import Export extension-->
            <div class="ep-extension-wrap" id="ep-event-google-ix-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-google-ie.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Google Events Import Export', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Integration with Google Calendar.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Admin now import and export his Google Calender events to and from EventPrime Calendar.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-extension-modal-des ep-ext-active">
                    <?php echo sprintf(__('Admin now import and export his Google Calender events to and from EventPrime Calendar. %s to check it now.'), '<a href="'.admin_url('admin.php?page=em_google_import_export_events').'">'.__('Click here', 'eventprime-event-calendar-management').'</a>'); ?>
                </div>
            </div> 
            <!--Google Import Export extension-->
            <!--Events Import Export extension-->
            <div class="ep-extension-wrap" id="ep-event-file-ix-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-file-import-export-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Events Import Export', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Allow Events Import / Export.', 'eventprime-event-calendar-management'); ?><span class="ep-ext-label">Free</span></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Admin now import and export events with various file formats like CSV, iCal, XML, JSON.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="https://eventprime.net/extensions/events-import-export/" target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-extension-modal-des ep-ext-active">
                    <?php echo sprintf(__('Import or export events in popular file formats like CSV, ICS, XML and JSON. %s to check it now.'), '<a href="'.admin_url('admin.php?page=em_file_import_export_events').'">'.__('Click here', 'eventprime-event-calendar-management').'</a>'); ?>
                </div>
            </div> 
            <!--Events Import Export extension-->
            <!--Mailpoet extension-->
            <div class="ep-extension-wrap" id="ep-event-mailpoet-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/event-mailpoet-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Mailpoet', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead">
                    <?php _e('Integration with MailPoet Plugin.', 'eventprime-event-calendar-management'); ?>
                    <span class="ep-ext-label">Free</span>
                </div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Connect and engage with your users by subscribing event attendees to MailPoet lists. Users can opt-in multiple newsletters during checkout and can also manage subscriptions in user account area.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="https://eventprime.net/extensions/eventprime-mailpoet/" target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-extension-modal-des ep-ext-active">
                    <div class="ep-extension-modal-active-extension"><strong><?php _e('Congratulations', 'eventprime-event-calendar-management');?></strong>, <?php _e('you have successfully installed and activated this extension!', 'eventprime-event-calendar-management');?></div>
                    <div class="ep-extension-modal-des">
                        <?php echo sprintf(__('You can now subscribe attendees to MailPoet lists but creating EventPrime List connections from new menu item in the left menu. %s to check it now.'), '<a href="'.admin_url('admin.php?page=em_mailpoet').'">'.__('Click here', 'eventprime-event-calendar-management').'</a>'); ?>
                    </div>
                </div>
            </div> 
            <!--Mailpoet extension-->
            <!--Woocommerce extension-->
            <div class="ep-extension-wrap" id="ep-event-woo-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-woo-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('WooCommerce Integration', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead">
                    <?php _e('Sell store products with your events!', 'eventprime-event-calendar-management'); ?>
                    <span class="ep-ext-label">Free</span>
                </div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('This extension allows you to add optional and/ or mandatory products to your events. You can define quantity or let users chose it themselves. Fully integrates with EventPrime checkout experience and WooCommerce order management.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="https://eventprime.net/extensions/eventprime-woocommerce-integration/" target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
            </div> 
            <!--Woocommerce extension-->
            <!--Zoom extension-->
            <div class="ep-extension-wrap" id="ep-event-zoom-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-zoom-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('EventPrime Zoom Integration', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead">
                    <?php _e('Organize and conduct virtual events seamlessly.', 'eventprime-event-calendar-management'); ?>
                    <span class="ep-ext-label">Free</span>
                </div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('This extension seamlessly creates virtual events to be conducted on Zoom through the EventPrime plugin. The extension provides easy linking of your website to that of Zoom. Commence and let the attendees join the event with a single click.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="https://eventprime.net/extensions/eventprime-zoom-integration/" target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
            </div> 
            <!--Zoom extension-->
            <!--Zapier extension-->
            <div class="ep-extension-wrap" id="ep-event-zapier-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-zapier-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Zapier Integration', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead">
                    <?php _e('Automate EventPrime Workflows using Zapier.', 'eventprime-event-calendar-management'); ?>
                    <span class="ep-ext-label">Free</span>
                </div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Extend the power of EventPrime using Zapier\'s powerful automation tools! Connect with over 3000 apps by building custom templates using EventPrime triggers.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="https://eventprime.net/extensions/eventprime-zapier-integration/" target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
            </div> 
            <!--Zapier extension-->
            <!--Invoice extension-->                
            <div class="ep-extension-wrap" id="ep-invoice-ext" style="display: none">
                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-invoice-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('Invoices', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead">
                    <?php _e('Generate and send PDF invoices.', 'eventprime-event-calendar-management'); ?>
                    <span class="ep-ext-label">Free</span>
                </div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Allows fully customizable PDF invoices, complete with your company branding, to be generated and emailed with booking details to your users.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="<?php echo esc_url('https://eventprime.net/extensions/eventprime-invoices/');?>" target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
            </div>
            <!--Invoice extension End-->
            <!--Twilio Text extension-->
            <div class="ep-extension-wrap" id="ep-twilio-integration-ext" style="display: none">
                <div class="ep-extension-modal-icon" > <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-sms-integration-icon.png"> </div>
                <div class="ep-extension-modal-title"> <?php _e('EventPrime Twilio Text Notifications', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-subhead"><?php _e('Use mobile text messaging to send notifications to users and admins.', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-extension-modal-des ep-ext-inactive">
                    <?php _e('Keep your users engaged with text/ SMS notification system. Creating Twilio account is quick and easy. With this extension installed, you will be able to configure admin and user notifications separately, with personalized content.', 'eventprime-event-calendar-management'); ?>
                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Interested? Checkout more information', 'eventprime-event-calendar-management'); ?></a></span>
                </div>
                <div class="ep-ext-active ep-no-setting">
                    <div class="ep-extension-modal-active-extension"><strong>Congratulations</strong>, you have successfully installed and activated this extension!</div>
                    <div class="ep-extension-modal-des">
                        <?php 
                        echo sprintf(__('Add Twilio Configuration from the left menu. %s to check it now.'), '<a href="'.admin_url('admin.php?page=em_sms_settings').'">'.__('Click here', 'eventprime-event-calendar-management').'</a>'); ?>
                    </div>
                </div>
            </div> 
            <!--Twilio Text End-->
        </div>
    </div>
</div>

<script>
(function($){    
    $(document).ready(function(){
        $('#em-strip_processor, #em_offline_processor').change(function(){
            if(this.checked)
                $('#ep-payment-config-stripe, #ep-payment-config-offiline').show();
            else
                $('#ep-payment-config-stripe, #ep-payment-config-offiline').hide();
        });
        $(".ep-payment-config-close, .ep-payment-config-overlay").click(function(){
            $(".ep-payment-config-single").toggle();
        });
        setTimeout(function(){
            $("#select_user_roles").select2({
                placeholder: "Select Roles",
                tags: true,
                width: "80%"
            });
            $("#front_switch_view_option").select2({
                placeholder: "Select Views",
                tags: true,
                width: "80%"
            });
        }, 2000);

        var fid = ["DD MMMM, YYYY", "MMMM DD, YYYY", "DD-MMMM-YYYY", "MMMM-DD-YYYY", "DD/MMMM/YYYY", "MMMM/DD/YYYY"];
        $.each(fid, function(ind, val){
            var fval = moment().format(val);
            $("#calendar_title_format").append(new Option(fval, val));
        });
        
        var fhd = ["ddd", "dddd", "ddd D/M", "ddd M/D"];
        $.each(fhd, function(idx, val){
            var fval = moment().format(val);
            $("#calendar_column_header_format").append(new Option(fval, val));
        });

        let dpf = {"d-m-Y": "DD-MM-YYYY", "m-d-Y": "MM-DD-YYYY", "Y-m-d": "YYYY-MM-DD", "d/m/Y": "DD/MM/YYYY", "m/d/Y": "MM/DD/YYYY", "Y/m/d": "YYYY/MM/DD", "d.m.Y": "DD.MM.YYYY", "m.d.Y": "MM.DD.YYYY", "Y.m.d": "YYYY.MM.DD"};
        $.each(dpf, function(idx, val){
            let fval = moment().format(val);
            fval += ' (' + idx + ')';
            let valData = val.toLowerCase();
            valData = valData.replace('yyyy', 'yy');
            valData += '&'+idx;
            let newOption = new Option(fval, valData);
            $("#datepicker_format").append( newOption );
        });
    });
})(jQuery);  
</script>
<script>
jQuery('.ep-extension-modal').click(function(){
    jQuery('.ep-extension-wrap').hide();
    jQuery('#' + jQuery(this).attr('data-popup')).show();
    jQuery('#' + jQuery(this).attr('data-popup') + ' .ep-ext-active').hide();
});

jQuery('.ep-no-global-settings-model').click(function(){
    jQuery('.ep-extension-wrap').hide();
    jQuery('#' + jQuery(this).attr('data-popup')).show();
    jQuery('#' + jQuery(this).attr('data-popup') + ' .ep-ext-inactive').hide();
});
  
function CallEPExtensionModal(ele) {
    jQuery("#ep-setting-popup").toggle();
    jQuery('.ep-setting-modal-wrap').removeClass('ep-setting-popup-out');
    jQuery('.ep-setting-modal-wrap').addClass('ep-setting-popup-in');
    jQuery('.ep-setting-modal-overlay').removeClass('ep-setting-popup-overlay-fade-out');
    jQuery('.ep-setting-modal-overlay').addClass('ep-setting-popup-overlay-fade-in');   
}

jQuery(document).ready(function () {
    jQuery('.ep-setting-modal-close, .ep-setting-modal-overlay').click(function () {
        setTimeout(function () {
            //jQuery(this).parents('.rm-modal-view').hide();
            jQuery('.ep-setting-modal-view').hide();
        }, 400);
    });
    jQuery('.ep-setting-modal-close, .ep-setting-modal-overlay').on('click', function () {
        jQuery('.ep-setting-modal-wrap').removeClass('ep-setting-popup-in');
        jQuery('.ep-setting-modal-wrap').addClass('ep-setting-popup-out');
        jQuery('.ep-setting-modal-overlay').removeClass('ep-setting-popup-overlay-fade-in');
        jQuery('.ep-setting-modal-overlay').addClass('ep-setting-popup-overlay-fade-out');
    });

    setTimeout(function(){
        for( var i = 0; i < 4; i++) {
            jscolor.installByClassName("jscolor_" + i);
        }
    }, 2000);
});

jQuery(".ep-extension-with-gs").click(function(){
    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
});

function removeFieldFromCustomizer(buttonKey){
    jQuery("#em_booking_fields_"+buttonKey).remove();
}
</script>