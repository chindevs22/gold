<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre kf-container"  ng-controller="eventCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('event_email')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content">
        <div class="kf-db-title">
            <?php _e('Booking Confirmation Email','eventprime-event-calendar-management'); ?>
        </div>
        <div class="form_errors">
            <ul>
                <li class="emfield_error" ng-repeat="error in  formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>  
        </div>
        <!-- FORM -->
        <form  name="postForm" ng-submit="savePost(postForm.$valid)" novalidate >
            <div class="emrow">
                <div class="emfield"><?php _e('Enable Booking Confirmation Email','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="enable_custom_booking_confirmation_email"  ng-model="data.post.enable_custom_booking_confirmation_email" ng-true-value="1" ng-false-value="0" string-to-number />
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                   <?php _e("Enable Booking Confirmation Email",'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow" ng-show="data.post.enable_custom_booking_confirmation_email">
                <div class="ep-email-setting-wrap">
                <div class="emfield"><?php _e('Subject','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input required type="text" name="custom_booking_confirmation_email_subject"  ng-model="data.post.custom_booking_confirmation_email_subject" />
                    <div class="emfield_error">
                        <span ng-show="postForm.custom_booking_confirmation_email_subject.$error.required && !postForm.custom_booking_confirmation_email_subject.$pristine"><?php _e('Subject is required','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                   <?php _e("Subject of Booking Confirmation email",'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow" ng-show="data.post.enable_custom_booking_confirmation_email">
                <div class="ep-email-setting-wrap">
                <div class="emfield"><?php _e('Body','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <?php 
                        $post_id = event_m_get_param('post_id');
                        $content = '';
                        if($post_id !==null && (int)$post_id > 0){
                            $content = get_post_meta($post_id, 'em_custom_booking_confirmation_email_body', true);
                        }
                        wp_editor($content,'custom_booking_confirmation_email_body',array('media_buttons'=>false));
                    ?>
                </div>
                </div>
                <div class="emnote" ng-non-bindable><i class="fa fa-info-circle" aria-hidden="true"></i>
                   <?php _e('Body of Booking Confirmation email. Use inline variables to insert end date or seats remaining before discount ends. {{event_name}} for Event name. {{venue_name}} for Event location name. {{venue_address}} for Event location address. {{booking_id}} for Booking ID. {{seat_no}} for Seat No. {{quantity}} for No. of seats. {{price}} for Total booking price. {{discount}} for Discount on booking price. {{fixed_fees}} for One Time event fees. {{subtotal}} for Subtotal. {{attendee_names}} for Attendee Names. {{gcal_link}} for Add event to Goole Calendar. {{iCal_link}} for Download iCal file. {{event_url}} for Event page url. {{event_type_name}} for Event Type name. {{organizer_name}} for List of organizer names. {{organizer_phone}} for List of organizer phone no. {{organizer_email}} for List of organizer emails. {{organizer_website}} for List of organizer websites. {{performer_name}} for Add performer name. {{performer_role}} for Add performer role. {{event_custom_link}} for Add event custom link.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('/admin.php?page=em_dashboard&post_id=' . $post_id); ?>">&#8592; &nbsp;<?php _e('Cancel','eventprime-event-calendar-management'); ?></a></div>
                <button ng-show="data.post.enable_custom_booking_confirmation_email" type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid || requestInProgress"><?php _e('Save','eventprime-event-calendar-management'); ?></button>
            </div>
        </form>
    </div>
</div>