<?php
$em = event_magic_instance();
$buy_link = event_m_get_buy_link();
?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre kf-container"  ng-controller="eventCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('event_settings')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content">
        <div class="kf-db-title">
             <?php esc_html_e('Event Settings','eventprime-event-calendar-management'); ?>
        </div>
        <div class="emrow" ng-if="data.post.parent > 0">
            <div class="epnotice"><?php esc_html_e('This is a recurring event. Any custom changes you make to this event will be overridden if you make changes to the main event later.', 'eventprime-event-calendar-management'); ?></div>
        </div>
        <div>
            <ul>
                <li class="eperror" ng-repeat="error in formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>
        </div>
        <!-- FORM -->
        <form  name="postForm" ng-submit="savePost(postForm.$valid)" novalidate>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Name','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required type="text" name="name" ng-model="data.post.name">
                    <div class="emfield_error">
                        <span ng-show="postForm.name.$error.required && (showFormErrors || !postForm.name.$pristine)"><?php esc_html_e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Name of your Event. Should be unique.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Slug','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required type="text" name="slug" ng-model="data.post.slug">
                    <div class="emfield_error">
                        <span ng-show="postForm.slug.$error.required && (showFormErrors || !postForm.slug.$pristine)"><?php esc_html_e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Slug of your Event. Should be unique.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Event Text Color','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input id="em_color_picker" class="jscolor"  type="text" name="event_text_color"  ng-model="data.post.event_text_color" >
                    <div class="emfield_error"></div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Define text color for this event when it appears on event calendars (both dashboard and frontend).','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Event Type','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <select name="event_type"  ng-model="data.post.event_type" ng-options="event_type.id as event_type.name for event_type in data.post.event_types"></select>
                    <div class="emfield_error"></div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Event category. Example: Musical Fest, Seminar, Sports etc.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            
            <!-- New Event Type Fields -->
            <div ng-show="data.post.event_type == 'new_event_type'" class="ep-childfieldsrow">
                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('New Event Type Name','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                    <div class="eminput">
                        <input type="text" name="new_event_type" ng-required="data.post.event_type=='new_event_type'" ng-model="data.post.new_event_type" />
                   
                    <div class="emfield_error">
                        <span ng-show="postForm.new_event_type.$error.required && !postForm.new_event_type.$pristine"><?php esc_html_e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                    </div>
                    </div>

                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Name of your Event Category.Should be unique.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('New Event Type Background Color','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                    <div class="eminput">
                        <input class="jscolor" type="text" name="new_event_type_color" ng-model="data.post.new_event_type_color" /></select>
                    </div>
                    <div class="emfield_error">
                        <span>&nbsp;</span>
                    </div>
                    <div class="emnote">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Background color for events of this type when they appear on the events calendar.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('New Event Type Text Color','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input class="jscolor" type="text" name="new_event_type_text_color" ng-model="data.post.new_event_type_text_color" /></select>
                    </div>
                    <div class="emfield_error">
                        <span>&nbsp;</span>
                    </div>
                    <div class="emnote">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Text color for events of this type when they appear on the events calendar. Can be overridden for individual events from their respective settings.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div>
            <!-- New Event Type Ends here -->
            
            <div class="emrow kf-bg-light">
                <div class="emfield emeditor"><?php esc_html_e('Description','eventprime-event-calendar-management'); ?></div>
                <div class="eminput emeditor">
                    <?php
                    $post_id = event_m_get_param('post_id');
                    $content = '';
                    if ($post_id !== null && (int) $post_id > 0) {
                        $post = get_post($post_id);
                        if (!empty($post))
                            $content = $post->post_content;
                    }
                    em_add_editor('description', $content);
                    ?>
                    <div class="emfield_error">
                    </div>
                </div>
                <div class="emnote emeditor">
                    <?php esc_html_e('Details about the Event that will be visible to the users on events page.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Feature Image','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="button" ng-click="mediaUploader(false)" class="button kf-upload" value="<?php esc_html_e('Upload','eventprime-event-calendar-management'); ?>" />
                    <input type="button" ng-show="data.post.cover_image_id != 0 && data.post.cover_image_url != ''" ng-click="deleteFeatureImage(data.post.id)" class="button kf-upload" value="<?php esc_html_e('Remove','eventprime-event-calendar-management'); ?>" />
                    <div class="em_cover_image">
                        <img ng-src="{{data.post.cover_image_url}}" />
                    </div>
                </div>

                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Large image for the Event. This will be displayed above Description prominently.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow kf-bg-light">
                <div class="emfield emeditor"><?php esc_html_e('Event Gallery','eventprime-event-calendar-management'); ?></div>
                <div class="eminput emeditor">
                    <input  type="button" ng-click="mediaUploader(true)" class="button kf-upload" value="<?php esc_html_e('Upload','eventprime-event-calendar-management'); ?>" />
                    <div class="em_gallery_images">
                        <ul id="em_draggable" class="dbfl">
                            <li class="kf-db-image difl" ng-repeat="(key, value) in data.post.images" id="{{value.id}}">
                                <div><img ng-src="{{value.src[0]}}" />
                                    <span><input class="em-remove_button" type="button" ng-click="deleteGalleryImage(value.id, key, data.post.images, data.post.gallery_image_ids)" value="<?php esc_html_e('Remove','eventprime-event-calendar-management'); ?>" /></span> 
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="emnote emeditor">
                    <?php esc_html_e('Displays multiple images related to the event as gallery view on Event page.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Starts','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required id="event_start_date" readonly=""  type="text" name="start_date"  ng-model="data.post.start_date">
                    <div class="emfield_error">
                        <span ng-show="postForm.start_date.$error.required && !postForm.start_date.$pristine"><?php esc_html_e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                        <span ng-show="postForm.start_date.$error.pattern && !postForm.start_date.$pristine"><?php esc_html_e('Format should be DD/MM/YYYY','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Event start date and time.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Ends','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required id="event_end_date" readonly="readonly" type="text" name="end_date"  ng-model="data.post.end_date" ng-change="update_start_booking_date()">
                    <div class="emfield_error">
                        <span ng-show="postForm.end_date.$error.required && !postForm.end_date.$pristine"><?php esc_html_e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                        <span ng-show="postForm.end_date.$error.pattern && !postForm.end_date.$pristine"><?php esc_html_e('Format should be DD/MM/YYYY','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Event end date and time. Should always be later than the Start Date.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Hide Ends','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="hide_end_date" ng-model="data.post.hide_end_date" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Enable this option to hide event date & time from frontend views and single event page.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('All Day','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input  type="checkbox" name="all_day" ng-change="allDay()"  ng-model="data.post.all_day" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Enable this option if your event has no specific start & end time.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
        
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Enable Bookings','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="enable_booking"  ng-model="data.post.enable_booking"  ng-true-value="1" ng-false-value="0">
                </div>
            </div>
            
            <div ng-show="data.post.enable_booking" class="ep-childfieldsrow">
                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('Start Booking Date','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input id="event_start_booking_date" readonly="readonly" type="text" name="start_booking_date"  ng-model="data.post.start_booking_date">
                        <div class="emfield_error">
                            <span ng-show="postForm.start_booking_date.$error.pattern && !postForm.start_booking_date.$pristine"><?php esc_html_e('Format should be DD/MM/YYYY','eventprime-event-calendar-management'); ?></span>
                        </div>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('The date from which booking opens.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('Last Booking Date','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input id="event_last_booking_date" readonly="readonly" type="text" name="last_booking_date"  ng-model="data.post.last_booking_date">
                        <div class="emfield_error">
                            <span ng-show="postForm.last_booking_date.$error.pattern && !postForm.last_booking_date.$pristine"><?php esc_html_e('Format should be DD/MM/YYYY','eventprime-event-calendar-management'); ?></span>
                        </div>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('The date on which booking closes.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>
                
                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('Booking Price', 'eventprime-event-seating'); ?></div>
                    <div class="eminput em_price_input"><span class="em_price_symbol">{{data.post.currency}}</span>
                        <input type="number" ng-min="0" name="ticket_price" ng-min="1"  ng-model="data.post.ticket_price" >
                        <div class="emfield_error">
                            <span ng-show="postForm.ticket_price.$error.number && !postForm.ticket_price.$pristine"><?php esc_html_e('Only numeric value allowed.', 'eventprime-event-seating'); ?></span>
                            <span ng-show="postForm.ticket_price.$error.min && !postForm.ticket_price.$pristine"><?php esc_html_e('Invalid value.', 'eventprime-event-seating'); ?></span>
                            <span ng-show="postForm.ticket_price.$error.required && !postForm.ticket_price.$pristine"><?php esc_html_e('This is a required field.', 'eventprime-event-seating'); ?></span>
                        </div>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Single booking price. Currency can be changed through Global Settings.', 'eventprime-event-seating'); ?>
                    </div>
                </div>

                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('One-Time event Fee', 'eventprime-event-seating'); ?></div>
                    <div class="eminput em_price_input"><span class="em_price_symbol">{{data.post.currency}}</span>
                        <input type="number" ng-min="0" name="fixed_event_price" ng-min="1"  ng-model="data.post.fixed_event_price" >
                        <div class="emfield_error">
                            <span ng-show="postForm.fixed_event_price.$error.number && !postForm.fixed_event_price.$pristine"><?php esc_html_e('Only numeric value allowed.', 'eventprime-event-seating'); ?></span>
                            <span ng-show="postForm.fixed_event_price.$error.min && !postForm.fixed_event_price.$pristine"><?php esc_html_e('Invalid value.', 'eventprime-event-seating'); ?></span>
                            <span ng-show="postForm.fixed_event_price.$error.required && !postForm.fixed_event_price.$pristine"><?php esc_html_e('This is a required field.', 'eventprime-event-seating'); ?></span>
                        </div>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('This is an optional fixed fee added to total booking amount during checkout. Leave it blank if there is no fixed fee for this event.', 'eventprime-event-seating'); ?>
                    </div>
                </div>

                <div class="emrow" ng-show="data.post.fixed_event_price > 0">
                    <div class="emfield"><?php esc_html_e('Show One-Time Event Fee','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input type="checkbox" name="show_fixed_event_price"  ng-model="data.post.show_fixed_event_price"  ng-true-value="1" ng-false-value="0">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Show Event Fee instead of Booking Fee on front end.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('Hide Booking Status','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input type="checkbox" name="hide_booking_status"  ng-model="data.post.hide_booking_status"  ng-true-value="1" ng-false-value="0">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Hides booking status from the viewers on Event page.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow">
                    <div class="emfield"> <?php esc_html_e('Allow Cancellations','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input type="checkbox" name="allow_cancellations"  ng-model="data.post.allow_cancellations" ng-true-value="1" ng-false-value="0">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Allow users to request for booking cancellation from User Profile page. Seats will be revoked and freed up for selection just after cancellation request.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>
                
                <div class="emrow">
                    <div class="emfield"> <?php esc_html_e('Enable Attendee Names','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input type="checkbox" name="enable_attendees"  ng-model="data.post.enable_attendees" ng-true-value="1" ng-false-value="0">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Disabling this option will disable adding Attendee names with Event Bookings.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>
                
                <?php if (!in_array('attendees-list', $em->extensions)): ?>
                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('Show Attendee Names','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input type="checkbox" disabled="disabled">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Enable this option to display names of attendees on the event page.','eventprime-attendees-list'); ?>
                        <br><br>
                        <span class="ep_buy_pro_inline"><?php esc_html_e('To add this feature (and many more), please upgrade','eventprime-event-calendar-management'); ?> <a href="<?php echo empty($buy_link) ? 'https://eventprime.net/plans/' : $buy_link; ?>" target="blank"><?php esc_html_e('Click here','eventprime-event-calendar-management'); ?></a></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php do_action('event_magic_attendees_list_setting'); ?>
            </div>
            
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Enable Custom Link','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="custom_link_enabled"  ng-model="data.post.custom_link_enabled"  ng-true-value="1" ng-false-value="0">
                </div>
            </div>
            <div  ng-show="data.post.custom_link_enabled" class="ep-childfieldsrow">
                <div class="emrow" >
                    <div class="emfield"><?php esc_html_e('Custom Link','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input type="text" name="custom_link" ng-model="data.post.custom_link">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Add custom link for this event here. Attendees will be directed to this link when clicking the event from the frontend.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div>
        
            <?php $show_rm= is_registration_magic_active(); ?>
            <div class="emrow" ng-show="data.rm_forms && <?php echo absint($show_rm); ?>">
                <div class="emfield"><?php esc_html_e('Event Registration Form','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <select name="rm_form" ng-model="data.post.rm_form" ng-options="key as value for (key, value) in data.rm_forms" convert-to-number></select>
                    <div class="emfield_error">
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php printf(__("Select a <a target='_blank' href='%s'>RegistrationMagic</a> form as this event's user registration form. Leave the option at ''Default EventPrime Form' if you do not wish to use a <a target='_blank' href='%s'>RegistrationMagic</a> form. On choosing 'Default EventPrime Form',  EventPrime will auto-generate a user registration form for this event.",'eventprime-event-calendar-management'),admin_url('admin.php?page=rm_form_manage'),admin_url('admin.php?page=rm_form_manage')); ?>
                </div>
            </div>
            
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Hide on Events Calendar Widget','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input  type="checkbox" name="hide_event_from_calendar"  ng-model="data.post.hide_event_from_calendar" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Hides Event from calendar widget.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Hide from Events Directory','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input  type="checkbox" name="hide_event_from_events"  ng-model="data.post.hide_event_from_events" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e("Hides Event from Event's directory.",'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            
            <?php do_action('event_magic_custom_extension_setting'); ?>
            
            <div class="emrow kf-bg-light">
                <div class="emfield"><?php esc_html_e('Note for Attendees','eventprime-event-calendar-management'); ?></div>
                <div class="eminput emeditor">
                    <textarea class="kf-note" name="audience_notice"  ng-model="data.post.audience_notice"></textarea>
                    <div class="emfield_error">
                    </div>
                </div>
                <div class="emnote emeditor">
                    <?php esc_html_e('Notice for audience on Event page. Can be used for important instructions related to the event.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Status','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <select required name="status" ng-model="data.post.status" ng-options="status.key as status.label for status in data.post.status_list"></select>
                    <div class="emfield_error">
                        <span ng-show="postForm.status.$error.required && !postForm.status.$pristine"><?php esc_html_e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote">
                    <?php esc_html_e('Bookings are not allowed for Unpublished and Draft Events. Draft events will not appear on the frontend. Status will automatically be changed to Unpublished for events older than the current date.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <input type="text" class="hidden"  ng-model="data.post.cover_image_id" />
            <input type="text" class="hidden" ng-model="data.post.gallery_image_ids" />

            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('/admin.php?page=em_dashboard&post_id=' . $post_id); ?>">&#8592; &nbsp;<?php esc_html_e('Cancel','eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid || requestInProgress"><?php esc_html_e('Save','eventprime-event-calendar-management'); ?></button>

            </div>


            <div class="dbfl kf-required-errors" ng-show="postForm.$dirty && postForm.$invalid">
                <h3><?php esc_html_e("Looks like you missed out filling some required fields (*). You will not be able to save until all required fields are filled in. Here’s what's missing",'eventprime-event-calendar-management') ?> -

                    <span ng-show="postForm.name.$error.required">
                        <?php esc_html_e('Name','eventprime-event-calendar-management'); ?>
                    </span>

                    <span ng-show="postForm.start_date.$error.required">
                        <?php esc_html_e('Starts','eventprime-event-calendar-management'); ?>
                    </span>

                    <span ng-show="postForm.end_date.$error.required">
                        <?php esc_html_e('Ends','eventprime-event-calendar-management'); ?>
                    </span>
                </h3>
            </div>
        </form>
    </div>
</div>