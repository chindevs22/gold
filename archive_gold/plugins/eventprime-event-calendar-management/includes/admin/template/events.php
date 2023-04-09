<?php
$gs_service = EventM_Factory::get_service('EventM_Setting_Service');
$gs_model = $gs_service->load_model_from_db();
$frontend_page_link = get_page_link($gs_model->events_page);
$tour_status = get_option(EM_GLOBAL_SETTINGS)['event_tour'];
$em = event_magic_instance();
$curr_user = get_current_user_id();?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre-old ep-all-event-old" ng-app="eventMagicApp" ng-controller="eventCtrl" ng-init="initialize('list')" ng-cloak>
    <div class="kikfyre ep-all-event em-events-cl-conatiner">
    <input id="em_tour-status" type="hidden" value="<?php echo $tour_status; ?>">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-operationsbar dbfl" ng-hide="requestInProgress">
        <div class="kf-title difl">
            <?php $manager_navs = em_manager_navs(); ?>
            <select class="kf-dropdown" onchange="em_manager_nav_changed(this.value)">
                <?php foreach ($manager_navs as $nav): ?>
                    <option <?php echo $nav['key'] == 'events' ? 'selected' : ''; ?> value="<?php echo $nav['key']; ?>"><?php echo $nav['label']; ?></option>
                <?php endforeach; ?>
            </select>   
        </div>
        <div class="difr ep-support-links" id="em_support_link"><a target="__blank" href="https://eventprime.net/contact/"><?php _e('Submit Support Ticket', 'eventprime-event-calendar-management'); ?></a></div>
        <div class="difr ep-support-links" id="em_frontend_link"><a target="__blank" href="<?php echo $frontend_page_link; ?>"><?php _e('Frontend', 'eventprime-event-calendar-management'); ?></a></div>
        <div class="difr ep-support-links"><a id="tour_link" href="#" ng-click="event_tour()"><?php _e('Tour','eventprime-event-calendar-management'); ?></a></div>
        <div class="difr ep-support-links"><a href="https://eventprime.net/how-to-translate-wordpress-plugins/" target="__blank"><?php _e('Translate','eventprime-event-calendar-management'); ?></a></div>
        <div class="kf-nav dbfl">
            <ul class="dbfl">
                <?php
                if( !empty( em_check_context_user_capabilities( array( 'create_events' ) ) ) ) {?>
                    <li id="em_add_new_link"><a ng-click="addNewPopup()">
                        <?php _e('Add New', 'eventprime-event-calendar-management'); ?></a>
                    </li>
                    <li id="em_duplicate_link" ng-show="eventView == 'cards'"><button class="em_action_bar_button" ng-click="duplicatePosts()" ng-disabled="selectedAll || selections.length == 0" >
                        <?php _e('Duplicate', 'eventprime-event-calendar-management'); ?></button>
                    </li><?php
                }
                if( !empty( em_check_context_user_capabilities( array( 'delete_events' ) ) ) ) {?>
                    <li id="em_delete_link" ng-show="eventView == 'cards'"><button class="em_action_bar_button" ng-click="deletePost()" ng-disabled="selections.length == 0" ><?php _e('Delete', 'eventprime-event-calendar-management'); ?></button></li><?php
                }?>
                <li id="em_select_events" ng-show="eventView == 'cards'"><input type="checkbox" id="em_select_all" ng-model="selectedAll" ng-click="checkAll()" ng-true-value='true'/><label for="em_select_all"><?php _e('Select All', 'eventprime-event-calendar-management'); ?></label></li>
                <li id="em_hide_link" ng-show="eventView == 'cards'"><input type="checkbox" ng-model="data.hideExpired" ng-click="prepareEventListPage(hideExpired)" ng-true-value="1" id="hide_expired"/><label for="hide_expired"><?php _e('Hide Past Events', 'eventprime-event-calendar-management'); ?></label></li>
                <li id="em_guide_link"><a target="_blank" href="https://eventprime.net/starter-guide/"><?php _e('Starter Guide','eventprime-event-calendar-management'); ?> <span class="dashicons dashicons-book-alt"></span></a></li>
                <li id="em_view_selector" class="kf-toggle difr"><?php _e('View As', 'eventprime-event-calendar-management'); ?>
                    <select class="kf-dropdown" ng-change="changeEventView()" ng-model="eventView">
                        <option value="month"><?php _e('Month', 'eventprime-event-calendar-management'); ?></option>
                        <option value="basicWeek"><?php _e('Week', 'eventprime-event-calendar-management'); ?></option>
                        <option value="basicDay"><?php _e('Day', 'eventprime-event-calendar-management'); ?></option>
                        <option value="listWeek"><?php _e('List', 'eventprime-event-calendar-management'); ?></option>
                        <option value="cards"><?php _e('Cards', 'eventprime-event-calendar-management'); ?></option>
                    </select>
                </li>
            </ul>
        </div>
    </div>
    <div ng-hide="requestInProgress">
        <div class="difl em-calendar-info epnotice" ng-show="eventView != 'cards'">
            <?php _e('Click on any date in the calendar to create an event starting from that date. Drag and drop events from one date to another for quick date changes.', 'eventprime-event-calendar-management'); ?>
        </div>
        <?php if (in_array('recurring_events',$em->extensions)) { ?>
        <div class="difl em-calendar-info epnotice" ng-show="eventView != 'cards'">
            <?php _e('Any custom changes made to recurring events will be overridden if changes are made to the main event later. Bookings for those recurring events will remain unaffected.', 'eventprime-event-calendar-management'); ?>
        </div>
        <?php } ?>
    </div>
    <!-------------------------------- Calendar View ------------------------------------->
    <div ng-show="eventView != 'cards'">
        <!----- Calendar container ------->
        <div class="ep_calendar-wrap">
            <div id="em_calendar" class="ep-event-calendar dbfl"></div>
        </div>


    </div>
    <!-------------------------------- Calendar View ends here----------------------------->

    <div ng-show="eventView == 'cards'">
        <div class="kf-cards dbfl emagic-event-cards">

            <!---------------Single Card Loop--------------------->
            <div class="kf-card difl" ng-repeat="post in data.posts" ng-class="{'emcard-expired':post.is_expired}">
                <div ng-if="post.cover_image_url" class="kf_cover_image dbfl">
                    <img ng-show="post.cover_image_url" ng-src="{{post.cover_image_url}}" />
                </div>
                <div ng-if="!post.cover_image_url" class="kf_cover_image dbfl">
                    <img  ng-src="<?php echo esc_url(plugins_url('/images/event_dummy.png', __FILE__)) ?>" />
                </div>
                <div class="kf-card-content dbfl">
                    <div class="kf-card-title kf-wrap dbfl" title="{{post.name}}">
                        <input type="checkbox" ng-model="post.Selected" ng-click="selectPost(post.id)" ng-true-value="{{post.id}}" ng-false-value="0" id="em-evt-{{post.id}}" ng-if="post.show_edit == 1">
                        <label for="{{post.name}}">{{post.name}}</label>
                    </div>
                    <div class="kf-card-info kf-wrap dbfl" ng-show="post.venue_name"><?php _e('at', 'eventprime-event-calendar-management'); ?>
                        <span title="{{post.venue_name}}"> {{post.venue_name}}</span>
                    </div>
                    <div class="kf-card-info kf-wrap dbfl em_event_date">
                        <span>{{post.between}}</span>
                    </div> 
                    <div class="kf-event-progress dbfl" ng-show="post.capacity > 0"> 
                        <?php _e('Booking Status', 'eventprime-event-calendar-management'); ?> {{post.sum}}/{{post.capacity}}
                        <div class="kf-progressbar-bg dbfl" data-sums='{{post.sum}}' data-total='{{post.capacity}}'>
                            <div ng-style="getProgressStyle(post)" class="kf-progressbar" ></div>
                        </div>
                    </div>  
                    <div class="event-progress" ng-show="post.capacity == 0 && post.sum > 0">
                        <?php _e('Booked', 'eventprime-event-calendar-management'); ?> {{post.sum}}
                    </div>  
                    <!-- Shortcodes -->
                    <div class="ep-event-shortcode dbfl">[em_event id="{{post.id}}"]</div>

                    <div class="ep-card-info" ng-if="post.show_edit == 1">
                        <div class="ep-card-event-dash difl">
                            <a ng-href="<?php echo admin_url('/admin.php?page=em_dashboard'); ?>&post_id={{post.id}}"><?php esc_html_e('Dashboard', 'eventprime-event-calendar-management'); ?></a>
                        </div>
                        
                        <div class="ep-card-event-setting difl">
                            <a ng-href="<?php echo admin_url('/admin.php?page=em_dashboard&tab=setting'); ?>&post_id={{post.id}}"><?php esc_html_e('Settings', 'eventprime-event-calendar-management'); ?></a>
                        </div>
                    </div>
                </div>
                <!---------------Card Loop ends here--------------------->

                <!---------------Empty card if data not available--------------------->
                <div class="em_empty_card" ng-show="data.posts.length == 0">
                    <?php _e('The Events you create will appear here as neat looking Event Cards. Presently, you do not have any event scheduled.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>   

            <!-------------------Card pagination----------------------->
            <div class="kf-pagination dbfl" ng-show="data.posts.length != 0">       
                <ul>        
                    <li class="difr" dir-paginate="post in data.total_posts | itemsPerPage: data.pagination_limit"></li>        
                </ul>
                <dir-pagination-controls on-page-change="pageChanged(newPageNumber)"></dir-pagination-controls>     
            </div>
        </div>
    </div>
    <!----- New Event Popup ------->
    <div ng-show="addNewClicked" id="newPopup">
        <div class="kikfyre kf-container">
            <div class="ep-modal-view">
                <div class="ep-modal-overlay" ng-click="addNewClicked = false;"></div> 
                <div class="ep-modal-wrap ep-add-newevent">
                    <form  name="calendarForm" ng-submit="savePopupEvent(calendarForm.$valid)" novalidate >
                        <div class="kf_progress_screen" ng-show="requestInProgress"></div>

                        <div class="ep-modal-titlebar">
                            <div class="ep-modal-title"><?php _e('Add New Event', 'eventprime-event-calendar-management'); ?></div>
                            <span class="ep-modal-close" ng-click="addNewClicked = false;" >&times;</span>
                        </div>

                        <div class="emrow">
                            <div class="eminput">
                                <input type="text" ng-model="calendarNewEventPopup.title" placeholder="<?php _e('Add Event Title...', 'eventprime-event-calendar-management'); ?>" />
                            </div>
                        </div>

                        <div class="emrow">
                            <div class="eminput">
                                <input type="text" readonly ng-model="calendarNewEventPopup.start_date" id="new_calendar_start_date" />
                                -
                                <input type="text" readonly ng-model="calendarNewEventPopup.end_date" id="new_calendar_end_date" />
                            </div>
                        </div>

                        <div class="emrow">
                            <div class="eminput">
                                <label><input type="checkbox" ng-model="calendarNewEventPopup.all_day" ng-change="checkForAllDay()" ng-true-value="1" ng-false-value="0"><?php _e('All Day', 'eventprime-event-calendar-management'); ?></label>
                            </div>
                        </div>
                        
                        <div class="emrow">
                            <div class="eminput">
                                <label>
                                    <input type="checkbox" ng-model="calendarNewEventPopup.enable_booking" ng-true-value="1" ng-false-value="0"><?php _e('Enable Bookings', 'eventprime-event-calendar-management'); ?>
                                </label>

                                <div class="ep-event-popup-notice" ng-show="calendarNewEventPopup.enable_booking==1">
                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                    <?php _e('Bookings will open immediately and close once the event begins. You can set custom booking dates from the Event Settings.', 'eventprime-event-calendar-management'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="emrow" ng-show="calendarNewEventPopup.enable_booking==1">
                            <div class="eminput">
                                <span class="em_price_symbol ng-binding"><?php echo em_currency_symbol(); ?></span> 
                                <input id="new_calendar_booking_price" type="number" ng-model="calendarNewEventPopup.ticket_price" min="0">
                            
                                <?php if (!em_is_payment_gateway_enabled()) { ?>
                                    <div class="ep-event-popup-notice"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                        <?php _e(
                                            sprintf(
                                                'Configure payment gateway from %sGlobal Settings%s before adding booking price here. Payment gateway is not required for 0 booking price.',
                                                '<a href="' . add_query_arg('page', 'em_global_settings', admin_url().'admin.php') . '" target="_blank">',
                                                '</a>'
                                            ),
                                            'eventprime-event-calendar-management');
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <?php do_action('event_magic_popup_custom_settings'); ?>
                        
                        <div class="emrow" ng-show="calendarNewEventPopup.performers.length>0">
                            <div class="emlabel"><?php _e('Select Performers','eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <select name="performer" class="emmultiselect" ie-select-fix="calendarNewEventPopup.performers"  ng-model="calendarNewEventPopup.performer" multiple ng-options="performer.id as performer.name for performer in calendarNewEventPopup.performers"></select>
                            </div>
                        </div>

                        <div class="emrow" ng-if="calendarNewEventPopup.venues.length>0">
                            <div class="emlabel"><?php _e('Select Location','eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <select name="venue" ie-select-fix="calendarNewEventPopup.venues"  ng-model="calendarNewEventPopup.venue" ng-options="venue.id as venue.name for venue in calendarNewEventPopup.venues"></select>
                            </div>
                        </div>
                        
                        <div class="emrow" ng-if="calendarNewEventPopup.event_types.length>0">
                            <div class="emlabel"><?php _e('Select Event Type','eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <select name="event_type" ie-select-fix="calendarNewEventPopup.event_types"  ng-model="calendarNewEventPopup.event_type" ng-options="event_type.id as event_type.name for event_type in calendarNewEventPopup.event_types"></select>
                            </div>
                        </div>

                        <div class="emrow" ng-show="calendarNewEventPopup.organizers.length > 0">
                            <div class="emlabel"><?php esc_html_e('Select Organizers','eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <select name="organizer" class="emmultiselect" ie-select-fix="calendarNewEventPopup.organizers"  ng-model="calendarNewEventPopup.organizer" multiple ng-options="organizer.id as organizer.name for organizer in calendarNewEventPopup.organizers"></select>
                            </div>
                        </div>

                        <div class="emrow">
                            <div class="eminput">
                                <label><?php esc_html_e('Feature Image','eventprime-event-calendar-management'); ?></label>
                                <input type="button" ng-click="mediaUploader(false, 'calendarNewEventPopup')" class="button kf-upload" value="<?php _e('Upload','eventprime-event-calendar-management'); ?>" />
                                <div class="em_cover_image" ng-show="calendarNewEventPopup.cover_image_url !=''">
                                    <img ng-src="{{calendarNewEventPopup.cover_image_url}}" />
                                </div>
                                <input type="text" class="hidden" ng-show="calendarNewEventPopup.cover_image_id != 0" ng-model="calendarNewEventPopup.cover_image_id" />
                            </div>
                        </div>
                        
                        <div class="emrow">
                            <div class="emlabel"><?php _e('Status','eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <select name="status" ie-select-fix="calendarNewEventPopup.status_list" ng-model="calendarNewEventPopup.status" ng-options="status_list.key as status_list.label for status_list in calendarNewEventPopup.status_list"></select>
                            </div>
                        </div>

                        <div class="form_errors">
                            <ul>
                                <li class="emfield_error" ng-repeat="error in formErrors">
                                    <span>{{error}}</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="dbfl emrow kf-popup-button-area">
                            <div ng-show="calendarNewEventPopup.id > 0"><a ng-href="<?php echo admin_url('admin.php?page=em_dashboard') ?>&post_id={{calendarNewEventPopup.id}}"><?php _e('Dashboard', 'eventprime-event-calendar-management') ?></a></div>
                            <div><button type="submit" class="btn btn-primary" ng-disabled="calendarForm.$invalid || requestInProgress"><?php _e('Save', 'eventprime-event-calendar-management'); ?></button></div>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="ep-modal-wrap" id="calendarPopup" ng-show="calendarDayClicked">
        <form  name="calendarForm" ng-submit="savePopupEvent(calendarForm.$valid)" novalidate >
            <div class="ep-modal-titlebar">
                <div class="ep-modal-title" id="em_edit_event_title" data-text="<?php esc_html_e('Add Event Title...', 'eventprime-event-calendar-management'); ?>" contenteditable="true">{{calendarNewEventPopup.title}}</div>

                <span class="ep-modal-close" ng-click="calendarDayClicked = false;" >&times;</span>
            </div>

            <div class="kf_progress_screen" ng-show="requestInProgress"></div>
        
            <div class="emrow" style="display:none">
                <div class="epinputicon"><i class="material-icons" title="<?php esc_html_e('Add Event Title', 'eventprime-event-calendar-management'); ?>">add_box</i></div>
                <div class="eminput">
                    <input type="text" ng-model="calendarNewEventPopup.title" placeholder="<?php _e('Add Title', 'eventprime-event-calendar-management'); ?>" />
                </div>
            </div>

            <div class="emrow">
                <div class="epinputicon"><i class="material-icons" title="<?php esc_html_e('Enter Event Time', 'eventprime-event-calendar-management'); ?>">schedule</i></div>
                <div class="eminput">
                    <input type="text" readonly ng-model="calendarNewEventPopup.start_date" id="calendar_start_date" />
                    -
                    <input type="text" readonly ng-model="calendarNewEventPopup.end_date" id="calendar_end_date" />
                </div>
            </div>

            <div class="emrow">
                <div class="epinputicon">&nbsp;</div>
                <div class="eminput">
                    <input type="checkbox" ng-model="calendarNewEventPopup.all_day" ng-change="checkForAllDay()"ng-true-value="1" ng-false-value="0" /> <?php _e('All Day', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            
            <div class="emrow">
                <div class="epinputicon epbooking-icon">
                    <i class="material-icons" title="<?php esc_html_e('Enable Booking', 'eventprime-event-calendar-management'); ?>">monetization_on</i>
                </div>
                <div class="eminput">
                    <label>
                        <input type="checkbox" ng-model="calendarNewEventPopup.enable_booking" ng-true-value="1" ng-false-value="0"><?php _e('Enable Bookings', 'eventprime-event-calendar-management'); ?>
                    </label>
                
                    <div class="ep-event-popup-notice" ng-show="calendarNewEventPopup.enable_booking==1 && calendarNewEventPopup.start_booking_date==''">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Bookings will open immediately and close once the event begins. You can set custom booking dates from the Event Settings.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div>
                        
            <div class="emrow" ng-show="calendarNewEventPopup.enable_booking==1">
                <div class="epinputicon">&nbsp;</div>
                <div class="eminput">
                    <span class="em_price_symbol ng-binding"><?php echo em_currency_symbol(); ?></span>
                    <input id="calendar_booking_price" type="number" ng-model="calendarNewEventPopup.ticket_price" min="0">
                
                    <?php if (!em_is_payment_gateway_enabled()) { ?>
                        <div class="ep-event-popup-notice">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e(
                                sprintf(
                                    'Configure payment gateway from %sGlobal Settings%s before adding booking price here. Payment gateway is not required for 0 booking price.',
                                    '<a href="' . add_query_arg('page', 'em_global_settings', admin_url().'admin.php') . '" target="_blank">',
                                    '</a>'
                                ),
                                'eventprime-event-calendar-management');
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php do_action('event_magic_popup_custom_settings_edit'); ?>
            
            <div class="emrow" ng-show="calendarNewEventPopup.performers.length>0">
                <div class="epinputicon">
                    <?php $performers_text = em_global_settings_button_title('Performers');?>
                    <i class="material-icons" title="<?php echo $performers_text; ?>">person_add</i>
                </div>
                <div class="eminput">
                    <select name="performer" class="emmultiselect" ie-select-fix="calendarNewEventPopup.performers"  ng-model="calendarNewEventPopup.performer" multiple ng-options="performer.id as performer.name for performer in calendarNewEventPopup.performers"></select>
                </div>
            </div>

            <div class="emrow" ng-if="calendarNewEventPopup.venues.length>0">
                <div class="epinputicon ep-add-location">
                    <i class="material-icons" title="<?php esc_html_e('Event Site/Locations', 'eventprime-event-calendar-management'); ?>">add_location</i>
                </div>
                <div class="eminput">
                    <select name="venue" ie-select-fix="calendarNewEventPopup.venues" ng-model="calendarNewEventPopup.venue" ng-options="venue.id as venue.name for venue in calendarNewEventPopup.venues"></select>
                </div>
            </div>
            
            <div class="emrow" ng-if="calendarNewEventPopup.event_types.length>0">
                <div class="epinputicon ep-add-location">
                    <i class="material-icons" title="<?php esc_html_e('Event Types', 'eventprime-event-calendar-management'); ?>">style</i>
                </div>
                <div class="eminput">
                    <select name="event_type" ie-select-fix="calendarNewEventPopup.event_types" ng-model="calendarNewEventPopup.event_type" ng-options="event_type.id as event_type.name for event_type in calendarNewEventPopup.event_types"></select>
                </div>
            </div>

            <div class="emrow" ng-show="calendarNewEventPopup.organizers.length > 0">
                <div class="epinputicon">
                    <i class="material-icons" title="<?php esc_html_e('Organizers', 'eventprime-event-calendar-management'); ?>">person</i>
                </div>
                <div class="eminput">
                    <select name="organizer" class="emmultiselect" ie-select-fix="calendarNewEventPopup.organizers" ng-model="calendarNewEventPopup.organizer" multiple ng-options="organizer.id as organizer.name for organizer in calendarNewEventPopup.organizers"></select>
                </div>
            </div>

            <div class="emrow">
                <div class="epinputicon">
                    <i class="material-icons" title="<?php esc_html_e('Feature Image', 'eventprime-event-calendar-management'); ?>">image</i>
                </div>
                <div class="eminput ep-event-feature-image">
                    <label><?php esc_html_e('Feature Image','eventprime-event-calendar-management'); ?></label>
                    <input type="button" ng-click="mediaUploader(false, 'calendarNewEventPopup')" class="button kf-upload" value="<?php _e('Upload','eventprime-event-calendar-management'); ?>" />
                    <input type="text" class="hidden"  ng-show="calendarNewEventPopup.cover_image_id != 0" ng-model="calendarNewEventPopup.cover_image_id" />
                </div>
                <div class="ep-event-feature-image-icon" ng-show="calendarNewEventPopup.cover_image_url !=''">
                    <img ng-src="{{calendarNewEventPopup.cover_image_url}}" />
                </div>
            </div>
            
            <div class="emrow">
                <div class="epinputicon ep-add-location">
                    <i class="material-icons" title="<?php esc_html_e('Status', 'eventprime-event-calendar-management'); ?>">new_releases</i>
                </div>
                <div class="eminput">
                    <select name="status" ie-select-fix="calendarNewEventPopup.status_list" ng-model="calendarNewEventPopup.status" ng-options="status_list.key as status_list.label for status_list in calendarNewEventPopup.status_list"></select>
                </div>
            </div>
            
            <?php do_action('ep_admin_event_popup'); ?>
            
            <div class="form_errors">
                <ul>
                    <li class="emfield_error" ng-repeat="error in  formErrors">
                        <span>{{error}}</span>
                    </li>
                </ul>  
            </div>
            <div class="dbfl emrow kf-popup-button-area">
                <div ng-show="calendarNewEventPopup.id > 0" class="epcalendarPopup-dash"><a ng-href="<?php echo admin_url('admin.php?page=em_dashboard') ?>&post_id={{calendarNewEventPopup.id}}"><?php _e('Dashboard', 'eventprime-event-calendar-management') ?></a></div>
                <div class="epcalendarPopup-save"><button type="submit"  ng-disabled="calendarForm.$invalid || requestInProgress"><?php _e('Save', 'eventprime-event-calendar-management'); ?></button></div>
            </div>
        </form>
    </div>
    <ol id="em-events-joytips">
        <li data-class="fc-toolbar" data-prev-text="Back" data-options="tipLocation:top;nubPosition:hide;tipAdjustmentX:200;tipAdjustmentY:230"><?php _e('<h5>Welcome to EventPrime!</h5><br>This begins a tour of Eventprime\'s Event Manager screen, giving you a quick overview of the interface and how to make best use of it.<br><br>Click the <strong>Next</strong> button to get started.<br><br>You can close this tour at any step by clicking the <strong>X</strong> icon in the top-right of the tour popup.<br><br>','eventprime-event-calendar-management'); ?></li>
        <li data-class="kf-title" data-prev-text="Back"><?php _e('<h5>Title</h5><br>This is the Title of the screen, telling you on which EventPrime screen you are.<br><br>In addition to being the Title, this is also a dropdown menu from where you can navigate to other screens of EventPrime.<br><br>Click on this Title to see a list of other EventPrime screens you can navigate to.<br><br>','eventprime-event-calendar-management'); ?></li>
        <li data-id="em_add_new_link" data-prev-text="Back"><?php _e('<h5>Add New Event</h5><br>Clicking on the <strong>Add New</strong> link will reveal the Add New Event popup form.<br><br>You can use this form to create new events with EventPrime.<br><br>','eventprime-event-calendar-management'); ?></li>
        <?php if (isset($_GET['view']) && $_GET['view'] == 'cards') { ?>
        <li data-id="em_duplicate_link" data-prev-text="Back"><?php _e('<h5>Duplicate Events</h5><br>Clicking on the <strong>Duplicate</strong> link will take the selected event and create a copy of it.<br><br>The link will remain disabled when no events are selected or more than one events are selected.<br><br>','eventprime-event-calendar-management'); ?></li>
        <li data-id="em_delete_link" data-prev-text="Back"><?php _e('<h5>Delete Events</h5><br>Clicking on the <strong>Delete</strong> link will delete the selected events.<br><br>The link will remain disabled when no events are selected.<br><br>','eventprime-event-calendar-management'); ?></li>
        <li data-id="em_select_events" data-prev-text="Back"><?php _e('<h5>Select All Events</h5><br>Use this checkbox to select all events in one go.<br><br>This\'ll help when you wish to delete all the events with a single click by clicking on the <strong>Delete</strong> link.<br><br>','eventprime-event-calendar-management'); ?></li>
        <li data-id="em_hide_link" data-prev-text="Back"><?php _e('<h5>Hide Past Events</h5><br>Use this checkbox to hide older events, which are no longer active, from the all events display.<br><br>','eventprime-event-calendar-management'); ?></li>
        <?php } ?>
        <li data-id="em_guide_link" data-prev-text="Back"><?php _e('<h5>Starter Guide</h5><br>Clicking on this link will take you to the <strong>EventPrime Starter Guide</strong> on the EventPrime website.<br><br>The guide will give you a detailed overview on how to create your first event with EventPrime and publish it on frontend.<br><br>','eventprime-event-calendar-management'); ?></li>
        <li data-id="em_view_selector" data-prev-text="Back"><?php _e('<h5>All Events View Selection</h5><br>From this dropdown menu, you can choose any one of the various display layouts EventPrime offers for displaying all events.<br><br>','eventprime-event-calendar-management'); ?></li>
        <li data-id="em_frontend_link" data-prev-text="Back"><?php _e('<h5>View the Frontend</h5><br>EventPrime provides you this link to help you see how your events appear on the frontend with a single click from the backend.<br><br>You can change which page this link opens from the <strong>Global Settings</strong>.<br><br>Just make sure that new page has the shortcode: <strong>[em_events]</strong> on it.<br><br>','eventprime-event-calendar-management'); ?></li>
        <li data-id="em_support_link" data-prev-text="Back"><?php _e('<h5>Contact EventPrime Support</h5><br>Click on this link to visit the contact form on EventPrime\'s website.<br><br>Our support team is always ready to respond back to your questions/concerns promptly.<br><br>','eventprime-event-calendar-management'); ?></li>
        <?php if (isset($_GET['view']) && $_GET['view'] === 'cards') { ?>
        <li data-prev-text="Back"><?php _e('<h5>All Events</h5><br>Below all the header options and links, you can see the section that will display all the events in neat looking card view.<br><br>Each event card will give you a brief overview of the event\'s details as well as a link to the event\'s dashboard.<br><br>You\'ll get all the options you need inside the <strong>Event Dashbord</strong> to configure an event just the way you want.<br><br>','eventprime-event-calendar-management'); ?></li>
        <?php } else { ?>
        <li data-class="fc-toolbar" data-prev-text="Back"><?php _e('<h5>The Event Calendar</h5><br>Below all the header options and links, you can see the event calendar. This will display all the events you create in EventPrime. Click on the right and left arrow signs to navigate to other months/days/weeks.<br><br>As a unique feature of EventPrime, this calendar also allows you to create new events right from it. Click on a date inside the calendar to begin creating an event starting from that date.<br><br>Hover over an exisiting event in the calendar to view its quick detailed popup. Clicking on the popup will allow you to edit its details right there.<br><br>You can also drag and drop existing events from one date to another to quickly change the event\'s date.<br><br>','eventprime-event-calendar-management'); ?></li>
        <?php } ?>
        <li data-class="fc-toolbar" data-button="End" data-prev-text="Back" data-options="tipLocation:top;nubPosition:hide;tipAdjustmentX:200;tipAdjustmentY:230"><?php _e('<h5>Tour Complete!</h5><br>This concludes the tour of EventPrime\'s Event Manager screen.<br><br>You are now fully equiped with the knowledge to work with EventPrime\'s backend interface!<br><br>Well done!<br><br>','eventprime-event-calendar-management'); ?></li>
    </ol>
    </div>
    <div class="ep-side-banner" ng-hide="requestInProgress">
        <div class="ep-sidebanner-image">
            <img src="<?php echo esc_url(plugins_url('/images/ep-side-banner-logo.png', __FILE__)) ?>" />
        </div>
        <div class="ep-sidebanner-mg-logo">
            <img  src="<?php echo esc_url(plugins_url('/images/mg-logo.png', __FILE__)) ?>" />
        </div>
        <div class="ep-sidebanner-content-wrapper" >
            <!--<div class="ep-sidebanner-text-content">
                <div class="ep-sidebanner-text"><?php _e('Limited Time Offer', 'eventprime-event-calendar-management'); ?></div>
                <div class="ep-sidebanner-help-text"><?php _e('22% Off on Upgrade', 'eventprime-event-calendar-management'); ?></div>
                <p> <?php _e('<strong>New Year Sale!</strong> Get 22% discount on EventPrime Premium+ Bundle to celebrate the arrival of 2022. Use coupon <strong>EPNY2022</strong> during checkout. Offer ends 15th Jan 2022.', 'eventprime-event-calendar-management'); ?> </p>                  
                <div class="ep-sidebanner-checkout"><a href="https://eventprime.net/plans/?utm_source=plugin&utm_medium=welcome_modal&utm_campaign=promo" target="_blank" title="Save Now">Save Now</a></div>
            </div>-->
            <div class="ep-sidebanner-buttons">
                <div class="ep-sidebanner-button">
                    <a target="_blank" href="https://eventprime.net/create-first-event-wordpress-starter-guide/"> <?php _e('Starter Guide', 'eventprime-event-calendar-management'); ?></a>         
                </div>
                <div class="ep-sidebanner-button">
                    <a target="_blank" href="<?php echo admin_url("/admin.php?page=em_frontend") ?>"> <?php _e('Publish Shortcodes', 'eventprime-event-calendar-management'); ?></a>         
                </div>
                <div class="ep-sidebanner-button">
                    <a href="javascript:void(0)" onclick="ep_activation_popup()"> <?php _e('Welcome Popup', 'eventprime-event-calendar-management'); ?> </a>            
                </div>
            </div>
        </div>
    </div>
</div>

<div class="ep-side-banner1111 " style="display: none;">
    <div class="ep-sidebanner-image" ng-hide="requestInProgress">
        <img src="<?php echo esc_url(plugins_url('/images/ep-side-banner-logo.png', __FILE__)) ?>" />
    </div>
    <div class="ep-sidebanner-mg-logo" ng-hide="requestInProgress">
        <img  src="<?php echo esc_url(plugins_url('/images/mg-logo.png', __FILE__)) ?>" />
    </div>
    <div class="ep-sidebanner-content-wrapper" ng-hide="requestInProgress">
        <!--<div class="ep-sidebanner-text-content">
            <div class="ep-sidebanner-text"><?php _e('Limited Time Offer', 'eventprime-event-calendar-management'); ?></div>
            <div class="ep-sidebanner-help-text"><?php _e('22% Off on Upgrade', 'eventprime-event-calendar-management'); ?></div>
            <p> <?php _e('<strong>New Year Sale!</strong> Get 22% discount on EventPrime Premium+ Bundle to celebrate the arrival of 2022. Use coupon <strong>EPNY2022</strong> during checkout. Offer ends 15th Jan 2022.', 'eventprime-event-calendar-management'); ?> </p>                  
            <div class="ep-sidebanner-checkout"><a href="https://eventprime.net/plans/?utm_source=plugin&utm_medium=welcome_modal&utm_campaign=promo" target="_blank" title="Save Now">Save Now</a></div>
        </div>-->
        <div class="ep-sidebanner-buttons">
            <div class="ep-sidebanner-button">
                <a target="_blank" href="https://eventprime.net/create-first-event-wordpress-starter-guide/"> <?php _e('Starter Guide', 'eventprime-event-calendar-management'); ?></a>         
            </div>
            <div class="ep-sidebanner-button">
                <a target="_blank" href="<?php echo admin_url("/admin.php?page=em_frontend") ?>"> <?php _e('Publish Shortcodes', 'eventprime-event-calendar-management'); ?></a>         
            </div>
            <div class="ep-sidebanner-button">
                <a href="javascript:void(0)" onclick="ep_activation_popup()"> <?php _e('Welcome Popup', 'eventprime-event-calendar-management'); ?> </a>            
            </div>
        </div>
    </div>
</div>

<div id="ep-activation-popup" class="ep-welcome-banner ep-modal-box-main"  style="display: none;">
    <div class="ep-modal-box-overlay ep-modal-box-overlay-fade-in"></div>
    <div class="ep-modal-box-wrap ep-modal-box-out">
        <div class="ep-modal-box-header">
            <div class="ep-popup-title"><!--<span><img src="<?php //echo $path; ?>images/ep-outline-logo.png"></span>--> <?php _e('Welcome to EventPrime', 'eventprime-event-calendar-management'); ?>  </div>
            <span class="ep-modal-box-close" onclick="ep_activation_popup()">×</span>
        </div>

        <div class="ep-welcome-banner-wrap">
            <div class="ep-welcome-banner-row">                
              <!--<a href="admin.php?page=em_offers_section" class="ep-welcome-banner-box">
                    <div class="ep-welcome-banner-icon">
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                             viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve">
                        <g>
                        <path d="M64.1,38.1l-30.2,24c-0.6,0.5-0.8,1.5-0.2,2.1c0.3,0.4,0.7,0.6,1.2,0.6c0.3,0,0.7-0.1,0.9-0.3l30.2-24
                              c0.6-0.5,0.8-1.5,0.2-2.1C65.7,37.7,64.8,37.6,64.1,38.1z"/>
                        <path d="M42,48.8c0.4,0,0.8,0,1.2-0.1c4.1-0.6,7-4.5,6.3-8.7c-0.3-2-1.4-3.8-3-5c-1.6-1.2-3.6-1.7-5.6-1.4c-2,0.3-3.8,1.4-5,3
                              c-1.2,1.6-1.7,3.6-1.4,5.6C35.1,46.1,38.4,48.8,42,48.8z M41.3,36.7c0.2,0,0.5-0.1,0.7-0.1c2.2,0,4.2,1.6,4.5,3.9
                              c0.4,2.5-1.3,4.8-3.8,5.2c-2.5,0.4-4.8-1.3-5.2-3.8C37.1,39.4,38.9,37.1,41.3,36.7z"/>
                        <path d="M58,53.8c-4.2,0-7.6,3.4-7.6,7.6c0,4.2,3.4,7.6,7.6,7.6s7.6-3.4,7.6-7.6C65.5,57.2,62.1,53.8,58,53.8z M58,65.9
                              c-2.5,0-4.6-2.1-4.6-4.6c0-2.5,2.1-4.6,4.6-4.6s4.6,2.1,4.6,4.6C62.5,63.9,60.5,65.9,58,65.9z"/>
                        <path d="M95.4,50.3c0-0.1,0.1-0.3,0.1-0.4c0-4.3-4.5-6.5-8.4-8.4c-2.8-1.4-5.8-2.9-6.6-4.7c-0.8-1.9,0.3-5,1.4-7.9
                              c1.5-4.1,3-8.3,0.2-11.1c-2.8-2.8-6.8-1.8-10.8-0.9c-2.8,0.6-5.7,1.3-7.6,0.5c-1.9-0.8-3.5-3.3-5-5.7c-2.2-3.4-4.6-7.3-8.8-7.3
                              c-4.2,0-6.6,3.9-8.7,7.4c-1.5,2.5-3,5-4.9,5.8c-1.9,0.8-4.9,0.1-7.7-0.6c-4-1-8.1-2-10.8,0.7c-2.8,2.8-1.4,7-0.1,11.1
                              c1,3,1.9,6,1.1,8c-0.8,1.9-3.6,3.3-6.3,4.7c-3.8,2-8.1,4.2-8.1,8.4c0,0.1,0,0.3,0.1,0.4c0,0.1-0.1,0.3-0.1,0.4
                              c0,4.2,4.3,6.5,8.1,8.4c2.7,1.4,5.5,2.8,6.3,4.7c0.8,1.9-0.2,5-1.1,8c-1.3,4.1-2.7,8.3,0.1,11.1c2.8,2.8,6.9,1.7,10.8,0.7
                              c2.8-0.7,5.8-1.4,7.7-0.6c1.9,0.8,3.4,3.3,4.9,5.8c2.1,3.5,4.5,7.4,8.7,7.4c4.2,0,6.7-3.9,8.8-7.3c1.5-2.4,3.1-4.9,5-5.7
                              c2-0.8,4.8-0.2,7.6,0.5c3.9,0.9,8,1.9,10.8-0.9c2.8-2.8,1.3-7-0.2-11.1c-1.1-3-2.2-6-1.4-7.9c0.8-1.9,3.7-3.3,6.6-4.7
                              c3.9-2,8.4-4.2,8.4-8.4C95.5,50.5,95.4,50.4,95.4,50.3z M85.7,56.4c-3.3,1.7-6.8,3.4-8,6.3c-1.3,3,0.1,6.6,1.3,10.1
                              c1.2,3.3,2.4,6.5,0.9,7.9c-1.6,1.6-4.7,0.9-8,0.1c-3.3-0.8-6.6-1.5-9.5-0.3c-2.8,1.2-4.6,4-6.3,6.8c-1.9,3-3.7,5.9-6.3,5.9
                              c-2.5,0-4.3-2.9-6.2-6c-1.7-2.9-3.5-5.8-6.3-7c-2.9-1.2-6.3-0.4-9.6,0.5c-3.3,0.8-6.4,1.6-8,0.1c-1.5-1.5-0.5-4.7,0.6-8
                              c1.1-3.5,2.3-7.1,1-10c-1.2-2.9-4.5-4.6-7.7-6.2c-3.3-1.7-6.4-3.3-6.4-5.8c0-0.1,0-0.3-0.1-0.4c0-0.1,0.1-0.3,0.1-0.4
                              c0-2.4,3.1-4,6.4-5.8c3.2-1.6,6.5-3.3,7.7-6.2c1.2-3,0.1-6.6-1-10c-1.1-3.4-2.1-6.6-0.6-8c1.6-1.6,4.7-0.8,8,0.1
                              c3.3,0.8,6.7,1.7,9.6,0.5c2.8-1.2,4.6-4.1,6.3-7c1.9-3.1,3.6-6,6.2-6c2.6,0,4.4,2.9,6.3,5.9c1.8,2.8,3.6,5.6,6.3,6.8
                              c2.9,1.2,6.2,0.4,9.5-0.3c3.3-0.8,6.4-1.5,8,0.1c1.5,1.4,0.3,4.6-0.9,7.9c-1.3,3.5-2.6,7.1-1.3,10.1c1.2,2.9,4.7,4.6,8,6.3
                              c3.5,1.7,6.7,3.4,6.7,5.8c0,0.1,0,0.3,0.1,0.4c0,0.1-0.1,0.3-0.1,0.4C92.5,53,89.2,54.7,85.7,56.4z"/>
                        </g>
                        </svg>
                    </div>
                    <div class="ep-welcome-banner-link"><?php _e('Check out latest offers', 'eventprime-event-calendar-management'); ?></div>
                </a> -->
              
                <a href="https://eventprime.net/plans/?utm_source=plugin&utm_medium=welcome_modal&utm_campaign=promo" target="_blank" class="ep-welcome-banner-box ep-welcome-banner-box-mini">
                    <div class="ep-welcome-banner-icon"><svg width="100%" height="100%" viewBox="0 0 500 500" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><g id="Star_Medal"><path d="M151.015,334.021L102.072,442.976C101.137,445.058 101.411,447.484 102.787,449.305C104.163,451.126 106.422,452.052 108.68,451.721L159.817,444.219C159.817,444.219 188.174,487.429 188.174,487.429C189.426,489.337 191.619,490.411 193.894,490.23C196.17,490.05 198.165,488.643 199.1,486.561L250,373.25L300.9,486.561C301.835,488.643 303.83,490.05 306.106,490.23C308.381,490.411 310.574,489.337 311.826,487.429L340.183,444.219C340.183,444.219 391.32,451.721 391.32,451.721C393.578,452.052 395.837,451.126 397.213,449.305C398.589,447.484 398.863,445.058 397.928,442.976L348.985,334.021C396.186,302.164 427.25,248.181 427.25,187C427.25,89.173 347.827,9.75 250,9.75C152.173,9.75 72.75,89.173 72.75,187C72.75,248.181 103.814,302.164 151.015,334.021ZM338.284,340.703C314.878,354.186 288.107,362.477 259.547,363.997L307.63,471.04L331.92,434.028C333.254,431.995 335.646,430.92 338.052,431.273C338.052,431.273 381.854,437.698 381.854,437.698L338.284,340.703ZM161.716,340.703L118.146,437.698L161.948,431.273C164.354,430.92 166.746,431.995 168.08,434.028C168.08,434.028 192.37,471.04 192.37,471.04L240.453,363.997C211.895,362.48 185.12,354.188 161.716,340.703ZM250,22.25C340.928,22.25 414.75,96.072 414.75,187C414.75,277.928 340.928,351.75 250,351.75C159.072,351.75 85.25,277.928 85.25,187C85.25,96.072 159.072,22.25 250,22.25ZM250,39.573C168.633,39.573 102.573,105.633 102.573,187C102.573,268.367 168.633,334.427 250,334.427C331.367,334.427 397.427,268.367 397.427,187C397.427,105.633 331.367,39.573 250,39.573ZM250,52.073C324.468,52.073 384.927,112.532 384.927,187C384.927,261.468 324.468,321.927 250,321.927C175.532,321.927 115.073,261.468 115.073,187C115.073,112.532 175.532,52.073 250,52.073ZM255.684,84.342C254.667,82.118 252.446,80.691 250,80.691C247.554,80.691 245.333,82.118 244.316,84.342L217.754,142.449C217.754,142.449 154.283,149.755 154.283,149.755C151.853,150.035 149.809,151.706 149.053,154.033C148.298,156.359 148.968,158.912 150.77,160.567L197.824,203.785C197.824,203.785 185.159,266.407 185.159,266.407C184.674,268.805 185.633,271.265 187.612,272.702C189.591,274.14 192.226,274.291 194.357,273.089L250,241.693C250,241.693 305.643,273.089 305.643,273.089C307.774,274.291 310.409,274.14 312.388,272.702C314.367,271.265 315.326,268.805 314.841,266.407L302.176,203.785C302.176,203.785 349.23,160.567 349.23,160.567C351.032,158.912 351.702,156.359 350.947,154.033C350.191,151.706 348.147,150.035 345.717,149.755L282.246,142.449C282.246,142.449 255.684,84.342 255.684,84.342ZM250,101.974L272.344,150.853C273.254,152.844 275.139,154.213 277.313,154.464L330.705,160.61C330.705,160.61 291.123,196.964 291.123,196.964C289.511,198.445 288.791,200.661 289.224,202.806L299.878,255.484C299.878,255.484 253.071,229.073 253.071,229.073C251.165,227.998 248.835,227.998 246.929,229.073L200.122,255.484C200.122,255.484 210.776,202.806 210.776,202.806C211.209,200.661 210.489,198.445 208.877,196.964L169.295,160.61C169.295,160.61 222.687,154.464 222.687,154.464C224.861,154.213 226.746,152.844 227.656,150.853L250,101.974Z"/></g></svg></div>
                    <div class="ep-welcome-banner-icon"><?php _e('Upgrade to Premium', 'eventprime-event-calendar-management'); ?> </div>
                </a>
              
            </div> 
            <div class="ep-welcome-banner-row">
                <a href="https://eventprime.net/extensions/events-import-export/?utm_source=plugin&utm_medium=welcome_modal&utm_campaign=promo" target="_blank" class="ep-welcome-banner-box ep-welcome-banner-box-mini">
                    <div class="ep-welcome-banner-icon">
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                             viewBox="0 0 412 412"  xml:space="preserve">
                        <g><g><g>
                        <path d="M334,140h-64c-4.4,0-8,3.6-8,8c0,4.4,3.6,8,8,8h64c13.2,0,24,10.8,24,24v192c0,13.2-10.8,24-24,24H78
                              c-13.2,0-24-10.8-24-24V180c0-13.2,10.8-24,24-24h72c4.4,0,8-3.6,8-8c0-4.4-3.6-8-8-8H78c-22,0-40,18-40,40v192c0,22,18,40,40,40
                              h256c22,0,40-18,40-40V180C374,158,356,140,334,140z"/>
                        <path d="M206,28c4.4,0,8-3.6,8-8V8c0-4.4-3.6-8-8-8c-4.4,0-8,3.6-8,8v12C198,24.4,201.6,28,206,28z"/>
                        <path d="M129.6,211.6c-3.2,3.2-3.2,8,0,11.2l70.8,70.8c1.6,1.6,3.6,2.4,5.6,2.4s4-0.8,5.6-2.4l70.8-70.8c3.2-3.2,3.2-8,0-11.2
                              s-8-3.2-11.2,0L214,268.8V56c0-4.4-3.6-8-8-8c-4.4,0-8,3.6-8,8v212.8l-57.2-57.2C137.6,208.4,132.8,208.4,129.6,211.6z"/>
                        </g></g></g><g></g> <g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g>
                        </svg>
                    </div>
                    <div class="ep-welcome-banner-link"><?php _e('Test out Import Export Extension', 'eventprime-event-calendar-management'); ?></div>
                </a>                  
                <a href="https://eventprime.net/eventprime-free-extensions/" target="_blank" class="ep-welcome-banner-box ep-welcome-banner-box-mini">
                    <div class="ep-welcome-banner-icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000" xml:space="preserve"><g><g><path d="M571.2,161.4C571.2,106.9,527.5,52,430,52c-85.9,0-143.6,44-143.6,109.4c0,38.2,15.5,55.6,25.7,67.1c5.4,6.1,7.6,8.8,7.6,11.6c0,4.5,0,7.3-15.6,7.9H133.3C67.7,248,10,300.3,10,360v104.5c1.1,26.4,16,42.9,39,42.9c13.1,0,22.8-7.5,32.2-14.7C93.8,483,108,472,136,472c47.5,0,98,39.3,98,112c0,73.6-51.2,124.9-97.1,124.9c-27.7,0-42.2-12-55-22.6c-9.6-8-19.5-16.2-32.9-16.2c-23,0-37.9,16.4-39,43.4v114.9C10,891,68.8,948,133.3,948h171.6c26-1.1,42.8-16.4,42.8-39.1c0-13.6-7.8-22.4-14.6-30.1c-9.2-10.3-18.6-21-18.6-48.6c0-49.4,44.9-81.4,114.4-81.4c69.5,0,114.4,31.9,114.4,81.4c0,27.6-9.5,38.2-18.6,48.6c-6.9,7.7-14.6,16.5-14.6,30.1c0,23,16.4,38,43.3,39.1l110.3,0c60.3,0,102.4-47,102.4-114.2V703.6c0.7-3.4,9.4-16.5,22.5-16.5c2.7,0,5.4,2.2,11.5,7.6c11.5,10.3,28.9,25.8,67,25.8c61.1,0,123.1-46.9,123.1-136.6c0-96.7-56.3-140-112-140c-37.6,0-59.4,17.2-73.8,28.6c-6.4,5-12.4,9.8-15.8,9.8c-13.2,0-21.8-13.1-22.5-16c0-0.2,0-0.4-0.1-0.7V365.4c0-68-43.1-117.4-102.4-117.4H553.8c-0.4,0-0.7-0.1-1.1-0.1c-14.8-0.6-14.8-3.3-14.8-7.8c0-2.7,2.2-5.5,7.6-11.6C555.7,217,571.2,199.6,571.2,161.4L571.2,161.4z M524.5,210c-6.9,7.7-14.6,16.5-14.6,30.2c0,15.4,7,33.9,41.2,35.8c0.6,0.1,1.3,0.1,2,0.1h110.5c48.8,0,74.4,45,74.4,89.4v101.7c0,1-0.2,2,0,2.9c2.6,17.1,23.6,40.3,50.5,40.3c13.2,0,22.8-7.7,33.2-15.8C835,484,850.1,472,878,472c51,0,84,44,84,112c0,74.6-49.3,108.5-95.1,108.5c-27.4,0-38.1-9.5-48.4-18.7c-7.7-6.9-16.5-14.7-30.1-14.7c-26.9,0-47.9,23.2-50.5,40.3c-0.2,1,0,1.9,0,2.9v131.4c0,42.9-23,86.2-74.4,86.2H553.8c-15.9-0.6-15.9-8-15.9-11.1c0-2.7,2.2-5.5,7.6-11.6c10.2-11.5,25.7-29,25.7-67.1c0-54.5-44-109.4-142.4-109.4c-85.1,0-142.4,44-142.4,109.4c0,38.2,15.5,55.6,25.7,67.1c5.4,6.1,7.6,8.8,7.6,11.6c0,3.1,0,10.5-15.3,11.1h-171C85.2,920,38,874.6,38,828.4V714.1c0.6-16,7.9-16,11-16c3.2,0,9,4.7,15,9.7c13.9,11.6,35.1,29,72.9,29c60.4,0,125.1-61.5,125.1-152.9c0-90.9-64.9-140-126-140c-37.5,0-58.2,15.9-71.9,26.5c-6.2,4.8-11.6,8.9-15.1,8.9c-3.1,0-10.3,0-11-15.4V360c0-49.5,50.2-84,95.3-84h171.2c0.4,0,0.9,0,1.3-0.1l0,0c34.6-1.3,41.8-20.2,41.8-35.8c0-13.6-7.8-22.4-14.6-30.1c-9.2-10.3-18.6-21-18.6-48.6c0-48.7,46.5-81.4,115.6-81.4c68.7,0,113.2,31.9,113.2,81.4C543.2,189,533.7,199.7,524.5,210L524.5,210z"></path></g></g></svg></div>
                    <div class="ep-welcome-banner-icon"><?php _e('Try out Our Free Extensions', 'eventprime-event-calendar-management'); ?> </div>
                </a>
                
            </div>

            <div class="ep-welcome-banner-row">
                <a onclick="bannerNewEventPopup()"  class="ep-welcome-banner-box ep-welcome-banner-box-mini">
                    <div class="ep-welcome-banner-icon">
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                             viewBox="0 0 100.353 100.353"  xml:space="preserve">
                        <g>
                        <path  d="M33.46,43.065h-9.051c-0.829,0-1.5,0.671-1.5,1.5v9.045c0,0.829,0.671,1.5,1.5,1.5h9.051
                              c0.829,0,1.5-0.671,1.5-1.5v-9.045C34.96,43.737,34.288,43.065,33.46,43.065z M31.96,52.111h-6.051v-6.045h6.051V52.111z"/>
                        <path  d="M54.571,43.065h-9.054c-0.829,0-1.5,0.671-1.5,1.5v9.045c0,0.829,0.671,1.5,1.5,1.5h9.054
                              c0.829,0,1.5-0.671,1.5-1.5v-9.045C56.071,43.737,55.4,43.065,54.571,43.065z M53.071,52.111h-6.054v-6.045h6.054V52.111z"/>
                        <path " d="M33.46,63.677h-9.051c-0.829,0-1.5,0.672-1.5,1.5v9.051c0,0.829,0.671,1.5,1.5,1.5h9.051
                              c0.829,0,1.5-0.671,1.5-1.5v-9.051C34.96,64.349,34.288,63.677,33.46,63.677z M31.96,72.728h-6.051v-6.051h6.051V72.728z"/>
                        <path  d="M54.571,63.677h-9.054c-0.829,0-1.5,0.672-1.5,1.5v9.051c0,0.829,0.671,1.5,1.5,1.5h9.054
                              c0.829,0,1.5-0.671,1.5-1.5v-9.051C56.071,64.349,55.4,63.677,54.571,63.677z M53.071,72.728h-6.054v-6.051h6.054V72.728z"/>
                        <path  d="M75.024,63.677h-9.047c-0.829,0-1.5,0.672-1.5,1.5v9.051c0,0.829,0.671,1.5,1.5,1.5h9.047
                              c0.829,0,1.5-0.671,1.5-1.5v-9.051C76.524,64.349,75.852,63.677,75.024,63.677z M73.524,72.728h-6.047v-6.051h6.047V72.728z"/>
                        <path  d="M86.04,16.132h-9.111v-1.739c0-3.09-2.513-5.604-5.601-5.604c-3.09,0-5.604,2.514-5.604,5.604v1.739
                              H55.592v-1.739c0-3.09-2.513-5.604-5.601-5.604c-3.092,0-5.607,2.514-5.607,5.604v1.739H34.255v-1.739
                              c0-3.09-2.512-5.604-5.601-5.604c-3.092,0-5.607,2.514-5.607,5.604v1.739h-9.085c-0.829,0-1.5,0.671-1.5,1.5v72.08
                              c0,0.828,0.671,1.5,1.5,1.5h72.08c0.829,0,1.5-0.672,1.5-1.5v-72.08C87.54,16.803,86.869,16.132,86.04,16.132z M68.723,14.393
                              c0-1.437,1.168-2.604,2.604-2.604c1.434,0,2.601,1.168,2.601,2.604v7.676c0,1.436-1.167,2.604-2.601,2.604
                              c-1.436,0-2.604-1.168-2.604-2.604V14.393z M49.99,11.788c1.434,0,2.601,1.168,2.601,2.604v7.676c0,1.436-1.167,2.604-2.601,2.604
                              c-1.438,0-2.607-1.168-2.607-2.604v-4.272c0.006-0.055,0.017-0.108,0.017-0.165s-0.011-0.11-0.017-0.165v-3.074
                              C47.383,12.956,48.553,11.788,49.99,11.788z M26.046,14.393c0-1.437,1.17-2.604,2.607-2.604c1.434,0,2.601,1.168,2.601,2.604v7.676
                              c0,1.436-1.167,2.604-2.601,2.604c-1.438,0-2.607-1.168-2.607-2.604V14.393z M84.54,88.211H15.46v-69.08h7.585v2.937
                              c0,3.09,2.516,5.604,5.607,5.604c3.088,0,5.601-2.514,5.601-5.604v-2.937h10.129v2.937c0,3.09,2.516,5.604,5.607,5.604
                              c3.088,0,5.601-2.514,5.601-5.604v-2.937h10.132v2.937c0,3.09,2.514,5.604,5.604,5.604c3.088,0,5.601-2.514,5.601-5.604v-2.937
                              h7.611v69.08H84.54z"/>
                        <path d="M76.683,38.729l-7.654,9.434l-3.193-3.048c-0.599-0.572-1.548-0.55-2.121,0.049
                              c-0.572,0.6-0.55,1.549,0.049,2.121l4.369,4.171c0.28,0.267,0.651,0.415,1.036,0.415c0.032,0,0.063-0.001,0.095-0.003
                              c0.418-0.026,0.806-0.227,1.07-0.552l8.679-10.696c0.522-0.643,0.423-1.588-0.22-2.11C78.15,37.987,77.205,38.086,76.683,38.729z"
                              />
                        </g>
                        </svg>
                    </div>
                    <div class="ep-welcome-banner-link"><?php _e('Add Event', 'eventprime-event-calendar-management'); ?></div>
                </a>  
                <a href="admin.php?page=em_frontend"  class="ep-welcome-banner-box ep-welcome-banner-box-mini">
                    <div class="ep-welcome-banner-icon"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve"><g id="Layer_1_1_"><path d="M11,7H5v42h34v-6h6V1H11V7z M37,47H7V9h4h19v7h7v27V47z M32,10.414L35.586,14H32V10.414z M13,3h30v38h-4V14.586L31.414,7 H13V3z"/>
                        <path d="M22,14H10v12h12V14z M20,24h-8v-8h8V24z"/>
                        <rect x="25" y="19" width="9" height="2"/>
                        <rect x="25" y="24" width="9" height="2"/>
                        <rect x="10" y="29" width="15" height="2"/>
                        <rect x="28" y="29" width="6" height="2"/>
                        <rect x="10" y="34" width="6" height="2"/>
                        <rect x="19" y="34" width="15" height="2"/>
                        <rect x="10" y="39" width="14" height="2"/>
                        <rect x="27" y="39" width="7" height="2"/>
                        </g>
                        </svg>
                    </div>
                    <div class="ep-welcome-banner-icon"><?php _e('Publish Shortcodes', 'eventprime-event-calendar-management'); ?> </div>
                </a>
                
                <a href="admin.php?page=em_global_settings"  class="ep-welcome-banner-box ep-welcome-banner-box-mini">
                    <div class="ep-welcome-banner-icon"><svg id="Layer_1" style="enable-background:new 0 0 24 24;" version="1.1" viewBox="0 0 24 24" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M21.485,10.499l0.365-0.98l-1.042-2.515l-0.951-0.434l-1.101,0.367l-0.275-0.086c-0.358-0.44-0.763-0.844-1.206-1.204  L17.189,5.37l0.367-1.102l-0.434-0.951l-2.515-1.042l-0.979,0.366l-0.52,1.039l-0.256,0.134c-0.568-0.059-1.14-0.06-1.704-0.002  l-0.256-0.133l-0.519-1.037L9.394,2.275L6.878,3.317L6.444,4.268l0.367,1.1L6.724,5.644C6.284,6.002,5.879,6.407,5.52,6.85  L5.244,6.936L4.143,6.569L3.192,7.003L2.15,9.519l0.365,0.98l1.039,0.52l0.134,0.256c-0.059,0.567-0.059,1.139-0.002,1.704  l-0.133,0.256l-1.037,0.519l-0.365,0.98l1.042,2.515l0.951,0.434l1.1-0.366l0.275,0.086c0.358,0.44,0.763,0.844,1.206,1.204  l0.086,0.275l-0.367,1.102l0.435,0.951l2.515,1.042l0.98-0.366l0.519-1.038l0.256-0.134c0.568,0.059,1.14,0.059,1.705,0.001  l0.255,0.134l0.519,1.037l0.979,0.365l2.515-1.042l0.434-0.951l-0.366-1.1l0.086-0.275c0.441-0.359,0.845-0.764,1.204-1.206  l0.275-0.086l1.102,0.367l0.951-0.434l1.042-2.516l-0.365-0.98l-1.039-0.52l-0.134-0.255c0.059-0.567,0.059-1.139,0.002-1.705  l0.133-0.256L21.485,10.499z M19.766,10.324l-0.403,0.772l0.017,0.142c0.07,0.586,0.07,1.182-0.002,1.771l-0.017,0.143l0.404,0.773  l0.98,0.49l0.111,0.298l-0.764,1.843l-0.289,0.132l-1.04-0.346l-0.832,0.261l-0.089,0.113c-0.365,0.467-0.787,0.889-1.251,1.254  l-0.113,0.088l-0.261,0.832l0.346,1.039l-0.132,0.289l-1.843,0.764l-0.298-0.111l-0.49-0.979l-0.772-0.404l-0.142,0.017  c-0.585,0.07-1.181,0.07-1.772-0.002l-0.142-0.017l-0.773,0.404l-0.49,0.981l-0.298,0.111l-1.843-0.763L7.436,19.93l0.346-1.04  l-0.261-0.832l-0.113-0.089c-0.468-0.367-0.89-0.788-1.254-1.252l-0.088-0.113l-0.831-0.261L4.196,16.69l-0.289-0.132l-0.764-1.843  l0.111-0.298L4.1,13.993l0.151-0.101l0.386-0.738L4.62,13.012c-0.07-0.586-0.07-1.182,0.002-1.771l0.017-0.143l-0.404-0.773  l-0.98-0.49L3.143,9.537l0.764-1.843l0.289-0.132l1.04,0.346l0.832-0.261l0.089-0.113C6.523,7.066,6.944,6.645,7.408,6.28  l0.113-0.089l0.26-0.832L7.436,4.322l0.132-0.289l1.843-0.764L9.709,3.38l0.49,0.979l0.772,0.404l0.142-0.017  c0.586-0.07,1.182-0.07,1.771,0.002l0.143,0.017L13.8,4.36l0.491-0.981l0.298-0.111l1.843,0.764l0.132,0.289l-0.346,1.04l0.26,0.832  l0.113,0.089c0.467,0.366,0.889,0.787,1.254,1.252l0.088,0.113l0.832,0.261l1.039-0.346l0.289,0.132l0.764,1.843l-0.111,0.298  L19.766,10.324z"/><path d="M11.994,6.998c-2.804,0-5.085,2.277-5.085,5.077s2.281,5.077,5.085,5.077s5.085-2.277,5.085-5.077  S14.798,6.998,11.994,6.998z M11.994,16.228c-2.294,0-4.16-1.863-4.16-4.153s1.866-4.153,4.16-4.153s4.16,1.863,4.16,4.153  S14.288,16.228,11.994,16.228z"/></svg></div>
                    <div class="ep-welcome-banner-icon"><?php _e('Global Settings', 'eventprime-event-calendar-management'); ?> </div>
                </a> 
                
            </div> 
        </div>

        <div class="ep-modal-box-footer">
            <div class="ep-welcome-banner-close" onclick="ep_activation_popup()" ><span class="dashicons dashicons-arrow-left-alt2"></span><?php _e('Back to WordPress Dashboard', 'eventprime-event-calendar-management'); ?></div>
        </div>
    </div>
</div>