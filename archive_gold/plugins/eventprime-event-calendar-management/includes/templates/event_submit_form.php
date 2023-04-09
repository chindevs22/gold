<?php
$setting_service = EventM_Factory::get_service('EventM_Setting_Service');
$gs = $setting_service->load_model_from_db();
wp_enqueue_style('em-public-css');
// check for edit event action.
$url_event_id = event_m_get_param('event');
if(!empty($url_event_id)){
    if(!is_user_logged_in()){
        // non loggedin user can't edit event
        echo '<div class="emagic"><div class="ep-login-form em_block em_bg_lt dbfl">';
        echo "<div class='ep-login-header dbfl'> <h3 class='em_form_heading'>".__("No event found!", 'eventprime-event-calendar-management')."</h3></div>" ;
        echo '<div> <div>';
        exit();
    }
    else{
        // if event not submitted my loggedin user then can't edit
        $user = wp_get_current_user();
        $event_service = EventM_Factory::get_service('EventM_Service');
        $event = $event_service->load_model_from_db($url_event_id);
        if($user->ID != $event->user){
            echo '<div class="emagic"><div class="ep-login-form em_block em_bg_lt dbfl">';
            echo "<div class='ep-login-header dbfl'> <h3 class='em_form_heading'>".__("No event found!", 'eventprime-event-calendar-management')."</h3></div>" ;
            echo '<div> <div>';
            exit();
        }
    }
}
$allow_submission_by_anonymous_user = em_global_settings('allow_submission_by_anonymous_user');
if(is_user_logged_in() || !empty($allow_submission_by_anonymous_user)){
    $user = wp_get_current_user();
    // check user restriction
    $hasUserRestriction = 1;
    $frontend_submission_roles = em_global_settings('frontend_submission_roles');
    if(empty($frontend_submission_roles)){
        $hasUserRestriction = 0;
    }
    else{
        foreach ($user->roles as $key => $value) {
            if(in_array($value, $frontend_submission_roles)){
                $hasUserRestriction = 0;
            }
        }
    }
    // check allow by non logged in user condition
    if(!empty($allow_submission_by_anonymous_user)){
        $hasUserRestriction = 0;
    }
    if(!empty($hasUserRestriction)){
        echo '<div class="emagic"><div class="ep-login-form em_block em_bg_lt dbfl">';
        echo "<div class='ep-login-header dbfl'> <h3 class='em_form_heading'> $gs->ues_restricted_submission_message </h3></div>" ;
        echo '<div> <div>';
    }
    else{
        wp_enqueue_script('em-public');
        wp_enqueue_script('em-event-submit-controller');
        wp_enqueue_script('jquery-ui-datepicker', array('jquery'));
        wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css', array(), EVENTPRIME_VERSION);
        wp_enqueue_script('em-select2');
        wp_enqueue_style('em-select2-css');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_style('jquery-ui-css','https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',array(),EVENTPRIME_VERSION);
        $performer_text = em_global_settings_button_title('Performer');
        ?>
        <div class="emagic ep-event-submit-form-page">
            <div ng-app="eventMagicApp" ng-controller="emEventSubmitCtrl" ng-init="initialize('<?php echo $url_event_id;?>')" ng-cloak class="ep-event-submit-form dbfl">
                <div class="em_progress_screen ep-progress-loader" ng-show="requestInProgress"></div>
                <div ng-hide="requestInProgress" class="ep_event_form em_block dbfl">
                    <div ng-hide="submitted" >
                        <div class="form_errors">
                            <ul>
                                <li class="emfield_error" ng-repeat="error in formErrors">
                                    <span>{{error}}</span>
                                </li>
                            </ul>  
                        </div>
                        <form name="emEventSubmitForm" ng-submit="submitEvent(emEventSubmitForm.$valid)" novalidate>
                            <input type="hidden" ng-model="data.settings.event_id" name="event_id">
                            <div class="ep-submit-event-fields-box ep-submit-event-details dbfl">
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Event Name','eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                        <input required type="text" class="em_input_field" name="name" ng-model="data.settings.name">
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.name.$error.required && chkFormSubmitted">
                                                <?php _e('This is a required field.','eventprime-event-calendar-management'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>   
                                <div class="em_input_row dbfl" ng-show="data.settings.frontend_submission_sections.fes_event_text_color == 1">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Event Text Color','eventprime-event-calendar-management'); ?></label>
                                        <input id="em_color_picker" type="text" class="em_input_field jscolor" name="event_text_color" ng-model="data.settings.event_text_color">
                                    </div>
                                </div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Description','eventprime-event-calendar-management'); ?></label>
                                        <?php wp_editor('','event_description',array('media_buttons'=>false)); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="ep-submit-event-fields-box ep-submit-event-date-and-time dbfl" ng-show="data.settings.frontend_submission_sections.fes_event_featured_image == 1">
                                <div class="ep-submit-event-box-label"><?php _e('Feature Image','eventprime-event-calendar-management'); ?></div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <input class="ep-fes-featured-file" type="file" file-input="files" name="featured_img" accept="image/*">
                                        <input type="hidden" name="attachment_id" ng-model="data.settings.attachment_id">
                                    </div>
                                    <br/>
                                    <div ng-show="PreviewImage" class="ep-fes-featured-image-section">
                                        <span class="ep-fes-remove-image" ng-click="removeFeatureImage()">&#10005;</span>
                                        <img class="ep-fes-featured-image" ng-src="{{PreviewImage}}" alt="<?php _e('Featured Image','eventprime-event-calendar-management'); ?>" style="height:200px;width:200px">
                                    </div>
                                    <span ng-show="emEventSubmitForm.featured_img.$invalid">{{emEventSubmitForm.featured_img.errorMessage}}</span>
                                </div>
                            </div>
                            <div class="ep-submit-event-fields-box ep-submit-event-date-and-time dbfl">
                                <div class="ep-submit-event-box-label"><?php _e('Date And Time','eventprime-event-calendar-management'); ?></div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Start Date','eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                        <input required readonly="readonly" type="text" id="event_start_date" class="em_input_field" name="start_date" ng-model="data.settings.start_date">
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.start_date.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('End Date','eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                        <input required readonly="readonly" type="text" id="event_end_date" class="em_input_field" name="end_date" ng-model="data.settings.end_date">
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.end_date.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('All Day','eventprime-event-calendar-management'); ?></label>
                                        <input type="checkbox" id="all_day" name="all_day" ng-model="data.settings.all_day" ng-true-value="1" ng-false-value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="ep-submit-event-fields-box ep-submit-event-booking dbfl" ng-show="data.settings.frontend_submission_sections.fes_event_booking == 1">
                                <div class="ep-submit-event-box-label"><?php _e('Event Booking','eventprime-event-calendar-management'); ?></div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Enable Bookings','eventprime-event-calendar-management'); ?></label>
                                        <input ng-required="data.settings.frontend_submission_required.fes_event_booking == 1" type="checkbox" id="enable_bookings" name="enable_booking" ng-model="data.settings.enable_booking" ng-true-value="1" ng-false-value="0">
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.enable_booking.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="em_input_row dbfl" ng-show="data.settings.enable_booking == 1">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Bookings Start Date','eventprime-event-calendar-management'); ?></label>
                                        <input ng-required="data.settings.frontend_submission_required.fes_event_booking == 1" readonly="readonly" type="text" id="event_start_booking_date" class="em_input_field" name="start_booking_date" ng-model="data.settings.start_booking_date">
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.start_booking_date.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="em_input_row dbfl" ng-show="data.settings.enable_booking == 1">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Bookings End Date','eventprime-event-calendar-management'); ?></label>
                                        <input ng-required="data.settings.frontend_submission_required.fes_event_booking == 1" readonly="readonly" type="text" id="event_last_booking_date" class="em_input_field" name="last_booking_date" ng-model="data.settings.last_booking_date">
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.last_booking_date.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="em_input_row dbfl" ng-show="data.settings.enable_booking == 1">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Booking Price','eventprime-event-calendar-management'); ?>({{data.settings.currency}})</label>
                                        <input ng-required="data.settings.frontend_submission_required.fes_booking_price == 1" type="number" class="em_input_field" ng-min="0" min="0" step="1" name="ticket_price" ng-model="data.settings.ticket_price">
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.ticket_price.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="em_input_row dbfl" ng-show="data.settings.enable_booking == 1">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('One-Time Event Fee','eventprime-event-calendar-management'); ?>({{data.settings.currency}})</label>
                                        <input type="number" class="em_input_field" ng-min="0" min="0" step="1" name="fixed_event_price" ng-model="data.settings.fixed_event_price">
                                    </div>
                                </div>
                                <div class="em_input_row dbfl" ng-show="data.settings.enable_booking == 1">
                                    <div class="em_input_form_field">
                                        <input type="checkbox" id="show_fixed_event_price" name="show_fixed_event_price" ng-model="data.settings.show_fixed_event_price" ng-true-value="1" ng-false-value="0">
                                        <label class="em_input_label"><?php _e('Show One-Time Event Fee instead of Booking Fee on front end.','eventprime-event-calendar-management'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="ep-submit-event-fields-box ep-submit-event-custom-link dbfl" ng-show="data.settings.frontend_submission_sections.fes_event_link == 1">
                                <div class="ep-submit-event-box-label"><?php _e('Custom Link','eventprime-event-calendar-management'); ?></div>     
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Enable Custom Link','eventprime-event-calendar-management'); ?></label>
                                        <input ng-required="data.settings.frontend_submission_required.fes_event_link == 1" type="checkbox" id="custom_link_enabled" name="custom_link_enabled" ng-model="data.settings.custom_link_enabled" ng-true-value="1" ng-false-value="0">
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.custom_link_enabled.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="em_input_row" ng-show="data.settings.custom_link_enabled == 1">
                                    <div class="em_input_form_field">
                                        <input ng-required="data.settings.frontend_submission_required.fes_event_link == 1" type="text" name="custom_link" ng-model="data.settings.custom_link" class="em_input_field" placeholder="<?php _e('Enter Link','eventprime-event-calendar-management'); ?>">
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.custom_link.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ep-submit-event-fields-box ep-submit-event-type dbfl" ng-show="data.settings.frontend_submission_sections.fes_event_type == 1">
                                <div class="ep-submit-event-box-label"><?php _e('Event Type','eventprime-event-calendar-management'); ?></div>     
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <select name="event_type" ng-required="data.settings.frontend_submission_required.fes_event_type == 1" ng-model="data.settings.event_type" ng-options="event_type.id as event_type.name for event_type in data.settings.event_types"></select>
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.event_type.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="em_input_row" ng-show="data.settings.event_type=='new_event_type'">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Name','eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                        <input type="text" class="em_input_field" name="new_event_type_name" ng-required="data.settings.event_type=='new_event_type'" ng-model="data.settings.new_event_type_name" />
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.new_event_type_name.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Background Color','eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                        <input type="text" class="em_input_field jscolor" name="new_event_type_background_color" ng-required="data.settings.event_type=='new_event_type'" ng-model="data.settings.new_event_type_background_color" />
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.new_event_type_background_color.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Text Color','eventprime-event-calendar-management'); ?></label>
                                        <input type="text" class="em_input_field jscolor" name="new_event_type_text_color" ng-model="data.settings.new_event_type_text_color" />
                                    </div>
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Age Group','eventprime-event-calendar-management'); ?></label>
                                        <select ng-model="data.settings.new_event_type_age_group" name="new_event_type_age_group" ng-options="group.key as group.label for group in data.settings.age_groups_dropdown"></select>
                                    </div>
                                    <div class="em_input_form_field" ng-show="data.settings.new_event_type_age_group == 'custom_group'">
                                        <label class="em_input_label"><?php _e('Custom Age','eventprime-event-calendar-management'); ?></label>
                                        <div class="eminput ep_custom_age-slider">
                                            <div id="slider"></div>
                                            <input style="display:none" id="custom_group" type="text" name="new_event_type_custom_group" ng-model="data.settings.new_event_type_custom_group">
                                            <div class="em_custom_group">{{data.settings.new_event_type.custom_group}}</div>
                                        </div>
                                    </div>
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Special Instructions','eventprime-event-calendar-management'); ?></label>
                                        <?php wp_editor('','new_event_type_description',array('media_buttons'=>false)); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="ep-submit-event-fields-box ep-submit-event-vanue dbfl" ng-show="data.settings.frontend_submission_sections.fes_event_location == 1">
                                <?php
                                    em_localize_map_info('em-google-map');
                                    $gmap_api_key = em_global_settings('gmap_api_key');
                                    if(!empty($gmap_api_key)){
                                        wp_enqueue_script('google_map_key', 'https://maps.googleapis.com/maps/api/js?key='.$gmap_api_key.'&libraries=places', array(), EVENTPRIME_VERSION);
                                    }
                                ?>
                                <div class="ep-submit-event-box-label"><?php _e('Event Sites/Locations','eventprime-event-calendar-management'); ?></div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <select name="venue" ng-required="data.settings.frontend_submission_required.fes_event_location == 1" ng-model="data.settings.venue" ng-options="venue.id as venue.name for venue in data.settings.venues" ng-change="getFrontCapacity('<?php echo $gmap_api_key?>')"></select>
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.venue.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="em_input_row" ng-show="data.settings.venue=='new_venue'">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('New Event Site Name','eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                        <input type="text" class="em_input_field" name="new_venue" ng-required="data.settings.venue=='new_venue'" ng-model="data.settings.new_venue" />
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.new_venue.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                    <div class="em_input_row dbfl">
                                        <label class="em_input_label"><?php _e('Address','eventprime-event-calendar-management'); ?></label>
                                        <div class="eminput emeditor">
                                            <input id="em-pac-input" ng-required="data.settings.venue=='new_venue'" name="new_venue_address" ng-model="data.settings.new_venue_address" ng-keydown="editingAddress=true" ng-keyup="editingAddress=false" class="em-map-controls" type="text">
                                            <?php if(!empty($gmap_api_key)) : ?>
                                                <div id="map" style="height: 300px;"></div>
                                                <div id="type-selector" class="em-map-controls" style="display:none">
                                                    <input type="radio" name="type" id="changetype-all" checked="checked">
                                                    <label for="changetype-all"><?php _e('All','eventprime-event-calendar-management'); ?></label>
                                                    <input type="radio" name="type" id="changetype-establishment">
                                                    <label for="changetype-establishment"><?php _e('Established','eventprime-event-calendar-management'); ?></label>
                                                    <input type="radio" name="type" id="changetype-address">
                                                    <label for="changetype-address"><?php _e('Addresses','eventprime-event-calendar-management'); ?></label>
                                                    <input type="radio" name="type" id="changetype-geocode">
                                                    <label for="changetype-geocode"><?php _e('Geocodes','eventprime-event-calendar-management'); ?></label>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if(!empty($gmap_api_key)) : ?>
                                            <div class="emnote ep-map-note">
                                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                <?php _e('Mark map location for the Event Site. This will be displayed on Event page.', 'eventprime-event-calendar-management'); ?>
                                            </div>
                                            <input type="text" name="lat" ng-model="data.settings.lat" id="em_venue_lat" style="display:none;" />
                                            <input type="text" name="lng" ng-model="data.settings.lng" id="em_venue_lng" style="display:none;" />
                                            <input type="number" string-to-number name="zoom_level" ng-model="data.term.zoom_level" id="em_venue_zoom_level" style="display:none;" >
                                        <?php endif; ?>
                                    </div>
                                    <div class="em_input_row dbfl">
                                          <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Seating Type', 'eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                            <select ng-required="data.settings.venue=='new_venue'" name="seating_type" ng-model="data.settings.seating_type" ng-options="type.key as type.label for type in data.settings.venue_types" ng-change="changeEventSiteType()"></select>
                                            <div class="emfield_error">
                                                <span ng-show="emEventSubmitForm.seating_type.$error.required && chkFormSubmitted"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="em_input_row dbfl" ng-if="data.settings.seating_type == 'standings'">
                                        <div class="emfield"><?php _e('Standing Capacity','eventprime-event-calendar-management'); ?></div>
                                        <div class="eminput">
                                            <input type="number" class="em_input_field" ng-min="0" name="standing_capacity"  ng-model="data.settings.standing_capacity">
                                            <div class="emfield_error">
                                                <span ng-show="emEventSubmitForm.standing_capacity.$error.number"><?php _e('Only numeric value allowed','eventprime-event-calendar-management'); ?></span>
                                                <span ng-show="emEventSubmitForm.standing_capacity.$error.capacityExceeded"><?php _e('Capacity exceeded than Venue.','eventprime-event-calendar-management'); ?></span>
                                                <span ng-show="emEventSubmitForm.standing_capacity.$error.min"><?php _e('Invalid Value','eventprime-event-calendar-management'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php 
                                    $term_id = 0;
                                    do_action('event_magic_venue_seatings_frontend_submission', $term_id); ?>
                                </div>
                            </div>
                            <div class="ep-submit-event-fields-box ep-submit-event-performer em_input_row dbfl" ng-show="data.settings.frontend_submission_sections.fes_event_performer == 1">
                                <div class="ep-submit-event-box-label"><?php _e( $performer_text . '(s)','eventprime-event-calendar-management'); ?></div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <select id="ep_fes_performers" ng-required="data.settings.frontend_submission_required.fes_event_performer == 1 && showNewPerformerBlock == 0" name="performer" multiple ie-select-fix="data.settings.performers" ng-model="data.settings.performer" ng-options="performer.id as performer.name for performer in data.settings.performers"></select>
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.performer.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <a href="javascript:void(0);" class="em-add-new-performer-button" ng-click="showNewPerformer(1);" ng-show="showNewPerformerBlock == 0" ng-if="data.settings.frontend_submission_sections.fes_new_event_performer == 1"><?php _e('Add New ' . $performer_text,'eventprime-event-calendar-management'); ?></a>
                                <a href="javascript:void(0);" class="em-add-hide-performer-button" ng-click="showNewPerformer(0);" ng-show="showNewPerformerBlock == 1"><?php _e('Hide Details','eventprime-event-calendar-management'); ?></a>
                                <div class="ep-new-performer-block" ng-show="showNewPerformerBlock == 1">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e( $performer_text . ' Type','eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                        <ul class="emradio" >
                                            <li ng-repeat="(key,value) in data.settings.performer_types">
                                                <input ng-required="showNewPerformerBlock == 1" type="radio" name="new_performer_type"  ng-model="data.settings.new_performer_type" value="{{value.key}}"> {{value.label}}
                                            </li>
                                        </ul>
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.new_performer_type.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Name','eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                        <input type="text" class="em_input_field" name="new_performer_name" ng-required="showNewPerformerBlock == 1" ng-model="data.settings.new_performer_name" />
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.new_performer_name.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Role','eventprime-event-calendar-management'); ?></label>
                                        <input type="text" class="em_input_field" name="new_performer_role" ng-required="showNewPerformerBlock == 1" ng-model="data.settings.new_performer_role" />
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.new_performer_role.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                    <div class="ep-submit-event-fields-box ep-submit-event-date-and-time dbfl">
                                        <div class="ep-submit-event-box-label"><?php _e('Performer Image','eventprime-event-calendar-management'); ?></div>
                                        <div class="em_input_row dbfl">
                                            <div class="em_input_form_field">
                                                <input class="ep-fes-performer-file" type="file" performer-file-input="files" name="performer_image" accept="image/*">
                                                <input type="hidden" name="performer_image_id" ng-model="data.settings.performer_image_id">
                                            </div>
                                            <br/>
                                            <div ng-show="PreviewPerformerImage" class="ep-fes-featured-image-section">
                                                <span class="ep-fes-remove-image" ng-click="removePerformerImage()">&#10005;</span>
                                                <img class="ep-fes-performer-image" ng-src="{{PreviewPerformerImage}}" alt="<?php _e('Performer Image','eventprime-event-calendar-management'); ?>" style="height:200px;width:200px">
                                            </div>
                                            <span ng-show="emEventSubmitForm.performer_image.$invalid">{{emEventSubmitForm.performer_image.errorMessage}}</span>
                                        </div>
                                    </div>
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Description','eventprime-event-calendar-management'); ?></label>
                                        <?php wp_editor('','new_performer_description',array('media_buttons'=>false)); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="ep-submit-event-fields-box ep-submit-event-organizer dbfl" ng-show="data.settings.frontend_submission_sections.fes_event_organizer == 1">
                                <div class="ep-submit-event-box-label"><?php _e('Organizer','eventprime-event-calendar-management'); ?><sup ng-show="data.settings.frontend_submission_required.fes_event_organizer == 1">*</sup></div> 
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                      <select id="ep_fes_organizers" name="organizer" ng-required="data.settings.frontend_submission_required.fes_event_organizer == 1 && showNewOrganizerBlock == 0" multiple ie-select-fix="data.settings.event_organizers" ng-model="data.settings.organizer" ng-options="event_organizer.id as event_organizer.name for event_organizer in data.settings.event_organizers"></select>
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.organizer.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                </div>    
                               
                                <a href="javascript:void(0);" class="em-add-new-organizer-button" ng-click="showNewOrganizer(1);" ng-show="showNewOrganizerBlock == 0" ng-if="data.settings.frontend_submission_sections.fes_new_event_organizer == 1"><?php _e('Add New Organizer','eventprime-event-calendar-management'); ?></a>
                                <a href="javascript:void(0);" class="em-add-hide-organizer-button" ng-click="showNewOrganizer(0);" ng-show="showNewOrganizerBlock == 1"><?php _e('Hide Details','eventprime-event-calendar-management'); ?></a>
                                <div class="ep-new-organizer-block em_input_row" ng-show="showNewOrganizerBlock == 1">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Name','eventprime-event-calendar-management'); ?><sup>*</sup></label>
                                        <input type="text" class="em_input_field" name="new_organizer_name" ng-required="showNewOrganizerBlock == 1" ng-model="data.settings.new_organizer_name" />
                                        <div class="emfield_error">
                                            <span ng-show="emEventSubmitForm.new_organizer_name.$error.required && chkFormSubmitted"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                                        </div>
                                    </div>
                                    <div class="emrow">
                                        <label class="emfield"><?php _e('Phone','eventprime-event-calendar-management'); ?></label>    
                                        <div class="eminput">
                                            <ul class="ep-organizer-input">
                                                <li ng-repeat="(i,phone) in data.settings.organizer_phones track by $index">
                                                    <input type="tel" ng-model="data.settings.organizer_phones[i]" name="organizer_phones{{i}}">
                                                    <a ng-click="removePhone(phone)" class="ep-delete-organizer-input"><?php _e('Delete','eventprime-event-calendar-management'); ?></a>
                                                </li>   
                                                <a href="javascript:void(0)" ng-click="addPhone()" class="ep-add-organizer-input"><?php _e('Add New','eventprime-event-calendar-management'); ?></a>
                                            </ul>
                                        </div>    
                                    </div>
                                    <div class="emrow">
                                        <label class="emfield"><?php _e('Email','eventprime-event-calendar-management'); ?></label>
                                        <div class="eminput" >
                                            <ul class="ep-organizer-input">
                                                <li ng-repeat="(i,email) in data.settings.organizer_emails track by $index">
                                                    <input type="email" ng-model="data.settings.organizer_emails[i]" name="organizer_emails{{i}}" ng-pattern="/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/">
                                                    <a ng-click="removeEmail(email)" class="ep-delete-organizer-input"><?php _e('Delete','eventprime-event-calendar-management'); ?></a>
                                                    <div class="emfield_error">
                                                        <span ng-show="emEventSubmitForm.organizer_emails{{i}}.$error.pattern && emEventSubmitForm.organizer_emails{{i}}.$dirty"> Invalid email address</span>
                                                    </div>    
                                                </li>
                                           <a href="javascript:void(0)"  ng-click="addEmail()"  class="ep-add-organizer-input"><?php _e('Add New','eventprime-event-calendar-management'); ?></a>
                                            </ul>  
                                        </div>
                                    </div>
                                    <div class="emrow">
                                        <label class="emfield"><?php _e('Website','eventprime-event-calendar-management'); ?></label>
                                        <div class="eminput" >
                                            <ul class="ep-organizer-input">
                                                <li ng-repeat="(i,website) in data.settings.organizer_websites track by $index">
                                                <input type="text" ng-model="data.settings.organizer_websites[i]" name="organizer_websites{{i}}" ng-pattern="/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/">
                                                    <a ng-click="removeWebsites(website)" class="ep-delete-organizer-input"><?php _e('Delete','eventprime-event-calendar-management'); ?></a>
                                                    <div class="emfield_error">
                                                        <span ng-show="emEventSubmitForm.organizer_websites{{i}}.$error.pattern && emEventSubmitForm.organizer_websites{{i}}.$dirty"> Incorrect website URL format</span> 
                                                    </div>     
                                                </li>
                                                 <a href="javascript:void(0)" ng-click="addWebsite()" class="ep-add-organizer-input"><?php _e('Add New','eventprime-event-calendar-management'); ?></a> 
                                            </ul>
                                        </div>            
                                    </div>
                                    <div class="ep-submit-event-fields-box ep-submit-event-date-and-time dbfl">
                                        <div class="ep-submit-event-box-label"><?php _e('Organizer Image','eventprime-event-calendar-management'); ?></div>
                                        <div class="em_input_row dbfl">
                                            <div class="em_input_form_field">
                                                <input class="ep-fes-organizer-file" type="file" organizer-file-input="files" name="organizer_image" accept="image/*">
                                                <input type="hidden" name="organizer_image_id" ng-model="data.settings.organizer_image_id">
                                            </div>
                                            <br/>
                                            <div ng-show="PreviewOrganizerImage" class="ep-fes-featured-image-section">
                                                <span class="ep-fes-remove-image" ng-click="removeOrganizerImage()">&#10005;</span>
                                                <img class="ep-fes-featured-image" ng-src="{{PreviewOrganizerImage}}" alt="<?php _e('Organizer Image','eventprime-event-calendar-management'); ?>" style="height:200px;width:200px">
                                            </div>
                                            <span ng-show="emEventSubmitForm.organizer_image.$invalid">{{emEventSubmitForm.organizer_image.errorMessage}}</span>
                                        </div>
                                    </div>
                                    <div class="emrow kf-bg-light">
                                        <label class="em_input_label"><?php _e('Description','eventprime-event-calendar-management'); ?></label>
                                        <?php wp_editor('','new_event_organizer_description',array('media_buttons'=>false)); ?>
                                    </div>
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Hide Organizer Details','eventprime-event-calendar-management'); ?></label>
                                        <input type="checkbox" name="new_organizer_hide_organizer"  ng-model="data.settings.new_organizer_hide_organizer" ng-true-value="1" ng-false-value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="ep-submit-event-fields-box ep-submit-event-custom-link dbfl" ng-show="data.settings.frontend_submission_sections.fes_event_more_options == 1">
                                <div class="ep-submit-event-box-label"><?php _e('More Options','eventprime-event-calendar-management'); ?></div>     
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Facebook Page','eventprime-event-calendar-management'); ?></label>
                                        <input class="em_input_field" ng-pattern="/(?:(?:http|https):\/\/)?(?:www.)?facebook.com\/?/"  type="url" name="facebook_page" ng-model="data.settings.facebook_page" placeholder="Eg.:https://www.facebook.com/XYZ/" />
                                        <div class="emfield_error">
                                            <span ng-show="!emEventSubmitForm.facebook_page.$valid && !emEventSubmitForm.facebook_page.$pristine && chkFormSubmitted">
                                                <?php _e('Invalid Facebook URL','eventprime-event-calendar-management'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div> 
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Hide on Events Calendar Widget','eventprime-event-calendar-management'); ?></label>
                                        <input  type="checkbox" name="hide_event_from_calendar"  ng-model="data.settings.hide_event_from_calendar" ng-true-value="1" ng-false-value="0">
                                    </div>
                                </div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Hide from Events Directory','eventprime-event-calendar-management'); ?></label>
                                        <input  type="checkbox" name="hide_event_from_events"  ng-model="data.settings.hide_event_from_events" ng-true-value="1" ng-false-value="0">
                                    </div>
                                </div>
                                <div class="em_input_row dbfl">
                                    <div class="em_input_form_field">
                                        <label class="em_input_label"><?php _e('Note for Attendees','eventprime-event-calendar-management'); ?></label>
                                        <textarea class="kf-note" name="audience_notice"  ng-model="data.settings.audience_notice"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="em_input_row dbfl">
                                <div class="em_input_submit_field">
                                    <button type="submit" class="btn btn-primary" ng-disabled="requestInProgress">
                                        <?php _e('Submit Event', 'eventprime-event-calendar-management'); ?>
                                    </button>
                                    <span class="kf-error emfield_error" ng-show="emEventSubmitForm.$invalid && chkFormSubmitted">
                                        <?php _e('Please fill all required fields.','eventprime-event-calendar-management'); ?>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div ng-show="submitted" class="ep-success-message">
                        <?php echo $gs->ues_confirm_message; ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function (event) {
                jQuery(document).ready(function () {
                    jQuery(".em_input_row input#event_start_date, .em_input_row input#event_end_date, .em_input_row input#event_start_booking_date, .em_input_row input#event_last_booking_date").click(function(){
                        jQuery("#ui-datepicker-div").addClass("ep-datepicker");
                    });
                    setTimeout(function(){
                        jQuery("#ep_fes_performers").select2({
                            placeholder: "Please Select",
                            tags: true,
                            width: "80%"
                        });
                        jQuery("#ep_fes_organizers").select2({
                            placeholder: "Please Select",
                            tags: true,
                            width: "80%"
                        });
                    }, 2000);
                });
            });
        </script>
        <?php
    }
}
else {
    echo '<div class="emagic"><div class="ep-login-form em_block em_bg_lt dbfl">';
    echo "<div class='ep-login-header dbfl'> <h3 class='em_form_heading'> $gs->ues_login_message </h3></div>" ;
    wp_login_form();
    echo '<div> <div>';
}