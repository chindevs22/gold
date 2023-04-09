<?php
$post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
if (empty($post_id))
    return;
$booking_service = EventM_Factory::get_service('EventM_Booking_Service');
$bookings = $booking_service->get_by_event($post_id); // Rcent bookings
$em = event_magic_instance();
$performer_text = em_global_settings_button_title('Performer');
$organizer_text = em_global_settings_button_title('Organizer');
$current_user = get_current_user_id();
?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="ep-event-configuration-wrapper">
    <div class="ep-grid-top dbfl">
        <div class="ep-grid-title difl"><?php echo $event->name; ?></div>
        <span class="ep-fd-form-toggle difr" id="ep_form_toggle">
            <?php _e('Toggle Event', 'eventprime-event-calendar-management'); ?> →
            <?php $events = $event_service->get_all(); ?>
            <select id="ep-event-dropdown" onchange="em_goto_event_dashboard(this.value)">
                <?php foreach ($events as $ev): ?>
                    <?php 
                    if( empty( em_check_context_user_capabilities( array( 'edit_others_events' ) ) ) ) {
                        if( $ev->user != $current_user) continue;
                    }?>
                    <option <?php echo $ev->id == $post_id ? 'selected' : '' ?> value="<?php echo $ev->id ?>"><?php echo $ev->name; ?></option>
                <?php endforeach; ?>
            </select>
        </span>
        
        <div class="ep-fd-all-form"><a href="<?php echo admin_url().'admin.php?page=event_magic'; ?>"><span><?php _e('← Back to All Events', 'eventprime-event-calendar-management'); ?></span></a></div>
    </div>   
    <?php if (isset($event->parent) && $event->parent > 0 && in_array('recurring_events',$em->extensions)) { ?>
    <div class="epnotice"><?php _e('This is a recurring event. Any custom changes you make to this event will be overridden if you make changes to the main event later. Bookings of this event will remain unaffected.', 'eventprime-event-calendar-management'); ?></div>
    <?php } ?>
    <div class="ep-grid difl">   
        <div class="ep-grid-section dbfl">
            <div class="ep-grid-section-title dbfl">Configure</div>

            <div class="ep-grid-icon difl" id="ep-event-settings">
                <a href="<?php echo admin_url("/admin.php?page=em_dashboard&tab=setting&post_id=$post_id") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/kf-general-setting-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Event Settings', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>

            <!-- Price Manager -->
            <div class="ep-grid-icon difl" id="ep-price-managers">
                <a href="<?php echo admin_url("/admin.php?page=em_dashboard&tab=price_manager&post_id=$post_id") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/multiprice-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Price Manager', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>

            <div class="ep-grid-icon difl" id="ep-event-site-location">
                <a href="<?php echo admin_url("/admin.php?page=em_dashboard&tab=venue&post_id=$post_id") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-event-site-location-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Event Site/Location', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>

            <div class="ep-grid-icon difl" id="ep-event-performer">
                <a href="<?php echo admin_url("/admin.php?page=em_dashboard&tab=performer&post_id=$post_id") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-event-performer-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php echo $performer_text . '(s)'; ?></div>
                </a>
            </div>
            
            <div class="ep-grid-icon difl" id="ep-event-organizer">
                <a href="<?php echo admin_url("/admin.php?page=em_dashboard&tab=organizer&post_id=$post_id") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-event-organizer-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php echo $organizer_text ?></div>
                </a>
            </div>

            <?php do_action('event_magic_dashboard_seating_link', $post_id); ?>
            <?php do_action('event_magic_dashboard_sponser_link', $post_id); ?>
            <?php do_action('event_magic_dashboard_recurring_link', $post_id); ?>
            <?php do_action('event_magic_dashboard_early_bird_discount_link', $post_id); ?>

            <?php do_action('event_magic_custom_extensions_link', $post_id); ?>

            <div class="ep-grid-icon difl" id="ep-social-integration">
                <a href="<?php echo admin_url("/admin.php?page=em_dashboard&tab=social&post_id=$post_id") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-social-integration.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Social Integration', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>
            <div class="ep-grid-icon difl" id="ep-event-email">
                <a href="<?php echo admin_url("/admin.php?page=em_dashboard&tab=email&post_id=$post_id") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/rm-email-notifications.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Email', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>
            <?php
            $extensions = $em->extensions;
            if (!in_array('sponser', $extensions)) {?>
                <div class="ep-grid-icon difl">
                    <a href="javascript:void(0)" class="ep-dash-link" data-popup="ep-event-sponsors-ext" onclick="CallEPDashboardModal('ep-event-sponsors-ext')">
                        <div class="ep-grid-icon-area dbfl">
                            <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-sponser-icon.png' ?>">
                        </div>
                        <div class="ep-grid-icon-label dbfl"><?php _e('Event Sponsors', 'eventprime-event-calendar-management'); ?></div>
                    </a>
                </div>
                <div id="ep-event-sponsors-ext" class="ep-setting-modal-view" style="display: none;">
                    <div class="ep-setting-modal-overlay ep-setting-popup-overlay-fade-in"></div>
                    <div class="ep-setting-modal-wrap ep-setting-popup-out">
                        <div class="ep-setting-modal-titlebar">
                            <span class="ep-setting-modal-close">×</span>
                        </div>
                        <div class="ep-setting-container">
                            <div class="ep-extension-wrap">
                                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-sponser-icon.png"> </div>
                                <div class="ep-extension-modal-label"> <span><?php _e('Not Installed', 'eventprime-event-calendar-management'); ?></span></div>
                                <div class="ep-extension-modal-title"> <?php _e('Event Sponsors', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-subhead"><?php _e('Add sponsors to events.', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-des ep-ext-inactive">
                                   <?php _e('Add Sponsor(s) to your events. Upload Sponsor logos and they will appear on the event page alongside all other details of the event.', 'eventprime-event-calendar-management'); ?>
                                   <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
                <?php
            }

            if (!in_array('seating', $extensions)) {?>
                <div class="ep-grid-icon difl">
                    <a href="javascript:void(0)" class="ep-dash-link" data-popup="ep-ticket-manager-ext" onclick="CallEPDashboardModal('ep-ticket-manager-ext')">
                        <div class="ep-grid-icon-area dbfl">
                            <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-ticket-integration-icon.png' ?>">
                        </div>
                        <div class="ep-grid-icon-label dbfl"><?php _e('Ticket Manager', 'eventprime-event-calendar-management'); ?></div>
                    </a>
                </div>
                <div id="ep-ticket-manager-ext" class="ep-setting-modal-view" style="display: none;">
                    <div class="ep-setting-modal-overlay ep-setting-popup-overlay-fade-in"></div>
                    <div class="ep-setting-modal-wrap ep-setting-popup-out">
                        <div class="ep-setting-modal-titlebar">
                            <span class="ep-setting-modal-close">×</span>
                        </div>
                        <div class="ep-setting-container">
                            <div class="ep-extension-wrap">
                                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-ticket-integration-icon.png"> </div>
                                          <div class="ep-extension-modal-label"> <span><?php _e('Not Installed', 'eventprime-event-calendar-management'); ?></span></div>
                                <div class="ep-extension-modal-title"> <?php _e('Ticket Manager', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-subhead"><?php _e('Create Customizable Ticket.', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-des ep-ext-inactive">
                                    <?php _e('Create and assign customizable ticket templates for your events to give booking tickets a unique look and feel. You can also allow volume discounts on tickets based on number of bookings.', 'eventprime-event-calendar-management'); ?>
                                    <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
                <?php
            }

            if (!in_array('seating', $extensions)) {?>
                <div class="ep-grid-icon difl">
                    <a href="javascript:void(0)" class="ep-dash-link" data-popup="ep-live-seating-ext" onclick="CallEPDashboardModal('ep-live-seating-ext')">
                        <div class="ep-grid-icon-area dbfl">
                            <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/seating-integration-icon.png' ?>">
                        </div>
                        <div class="ep-grid-icon-label dbfl"><?php _e('Live Seating', 'eventprime-event-calendar-management'); ?></div>
                    </a>
                </div>
                <div id="ep-live-seating-ext" class="ep-setting-modal-view" style="display: none;">
                    <div class="ep-setting-modal-overlay ep-setting-popup-overlay-fade-in"></div>
                    <div class="ep-setting-modal-wrap ep-setting-popup-out">
                        <div class="ep-setting-modal-titlebar">
                            <span class="ep-setting-modal-close">×</span>
                        </div>
                        <div class="ep-setting-container">
                            <div class="ep-extension-wrap">
                                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/seating-integration-icon.png"> </div>
                                <div class="ep-extension-modal-label"> <span><?php _e('Not Installed', 'eventprime-event-calendar-management'); ?></span></div>
                                <div class="ep-extension-modal-title"> <?php _e('Live Seating', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-subhead"><?php _e('Add seat plan and seat selection.', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-des ep-ext-inactive">
                                    <?php _e('Add live seat selection on your events and provide seat based tickets to your event attendees. Set a seating arrangement for all your Event Sites with specific rows, columns, and walking aisles using EventPrime\'s very own Event Site Seating Builder.', 'eventprime-event-calendar-management'); ?>
                                    <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
                <?php
            }

            if (!in_array('recurring_events', $extensions)) {?>
                <div class="ep-grid-icon difl">
                    <a href="javascript:void(0)" class="ep-dash-link" data-popup="ep-recurring-events-ext" onclick="CallEPDashboardModal('ep-recurring-events-ext')">  
                        <div class="ep-grid-icon-area dbfl">
                            <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-recurring-events-icon.png' ?>">
                        </div>
                        <div class="ep-grid-icon-label dbfl"><?php _e('Recurring Events', 'eventprime-event-calendar-management'); ?></div>
                    </a>
                </div>
                <div id="ep-recurring-events-ext" class="ep-setting-modal-view" style="display: none;">
                    <div class="ep-setting-modal-overlay ep-setting-popup-overlay-fade-in"></div>
                    <div class="ep-setting-modal-wrap ep-setting-popup-out">
                        <div class="ep-setting-modal-titlebar">
                            <span class="ep-setting-modal-close">×</span>
                        </div>
                        <div class="ep-setting-container">
                            <div class="ep-extension-wrap">
                                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/ep-recurring-events-icon.png"> </div>
                                <div class="ep-extension-modal-label"> <span><?php _e('Not Installed', 'eventprime-event-calendar-management'); ?></span></div>
                                <div class="ep-extension-modal-title"> <?php _e('Recurring Events', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-subhead"><?php _e('Create recurring events.', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-des ep-ext-inactive">
                                    <?php _e('Create events that recur by your specified numbers of days, weeks, months, or years. Make updates to all recurring events at once by updating the main event. Or make custom changes to individual recurring events, such as different performers, event sites, booking amount etc.', 'eventprime-event-calendar-management'); ?>
                                    <span><a href="admin.php?page=em_extensions" target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
                <?php
            }

            if (!in_array('em_automatic_discounts', $extensions)) {?>
                <div class="ep-grid-icon difl">
                    <a href="javascript:void(0)" class="ep-dash-link" data-popup="ep-automatic-discounts-ext" onclick="CallEPDashboardModal('ep-automatic-discounts-ext')">
                        <div class="ep-grid-icon-area dbfl">
                            <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/event-early-bird-discount-icon.png' ?>">
                        </div>
                        <div class="ep-grid-icon-label dbfl"><?php _e('Automatic Discounts', 'eventprime-event-calendar-management'); ?></div>
                    </a>
                </div>
                <div id="ep-automatic-discounts-ext" class="ep-setting-modal-view" style="display: none;">
                    <div class="ep-setting-modal-overlay ep-setting-popup-overlay-fade-in"></div>
                    <div class="ep-setting-modal-wrap ep-setting-popup-out">
                        <div class="ep-setting-modal-titlebar">
                            <span class="ep-setting-modal-close">×</span>
                        </div>
                        <div class="ep-setting-container">
                            <div class="ep-extension-wrap">
                                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/event-early-bird-discount-icon.png"> </div>
                                <div class="ep-extension-modal-label"> <span><?php _e('Not Installed', 'eventprime-event-calendar-management'); ?></span></div>
                                <div class="ep-extension-modal-title"> <?php _e('Automatic Discounts', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-subhead"><?php _e('Auto-apply conditional discounts.', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-des ep-ext-inactive">
                                   <?php _e('Automatically display discounts on an event for a user based on Admin rules. With Automatic Discount Extension, you can create and activate discounts by setting rules (eligibility criteria) to offer the eligible users a discount on bookings. The discounts are automatically applied to the bookings.', 'eventprime-event-calendar-management'); ?>
                                   <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
                <?php
            }

            if (!in_array('em_mailpoet', $extensions)) {?>
                <div class="ep-grid-icon difl">
                    <a href="javascript:void(0)" class="ep-dash-link" data-popup="ep-mailpoet-ext" onclick="CallEPDashboardModal('ep-mailpoet-ext')">
                        <div class="ep-grid-icon-area dbfl">
                            <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/event-mailpoet-icon.png' ?>">
                        </div>
                        <div class="ep-grid-icon-label dbfl"><?php _e('MailPoet', 'eventprime-event-calendar-management'); ?></div>
                    </a>
                </div>
                <div id="ep-mailpoet-ext" class="ep-setting-modal-view" style="display: none;">
                    <div class="ep-setting-modal-overlay ep-setting-popup-overlay-fade-in"></div>
                    <div class="ep-setting-modal-wrap ep-setting-popup-out">
                        <div class="ep-setting-modal-titlebar">
                            <span class="ep-setting-modal-close">×</span>
                        </div>
                        <div class="ep-setting-container">
                            <div class="ep-extension-wrap">
                                <div class="ep-extension-modal-icon" >  <img class='em-settings-icon' src="<?php echo EM_BASE_URL; ?>includes/admin/template/images/event-mailpoet-icon.png"> </div>
                                <div class="ep-extension-modal-label"> <span><?php _e('Not Installed', 'eventprime-event-calendar-management'); ?></span></div>
                                <div class="ep-extension-modal-title"> <?php _e('Mailpoet', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-subhead"><?php _e('Integration with MailPoet Plugin.', 'eventprime-event-calendar-management'); ?></div>
                                <div class="ep-extension-modal-des ep-ext-inactive">
                                   <?php _e('Connect and engage with your users by subscribing event attendees to MailPoet lists. Users can opt-in multiple newsletters during checkout and can also manage subscriptions in user account area.', 'eventprime-event-calendar-management'); ?>
                                   <span><a href="admin.php?page=em_extensions"  target="_blank"><?php _e('Download Now', 'eventprime-event-calendar-management'); ?></a></span>
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
                <?php
            }

            if (!in_array('sponser', $extensions) || !in_array('seating', $extensions) || !in_array('recurring_events', $extensions)) {
                ?>
                <!-- <div class="ep-grid-icon difl" id="ep-event-global-settings">
                    <a href="javascript:void(0)" class="ep-dash-link" onclick="showMore()">    
                        <div class="ep-grid-icon-area dbfl">
                            <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/dash-more-options.png' ?>">
                        </div>
                        <div class="ep-grid-icon-label dbfl"><?php _e('More', 'eventprime-event-calendar-management'); ?></div>
                    </a>
                </div>
                <div class="kikfyre">
                    <div class="ep_ext_promo_popup" style="display: none">
                        <div class="ep-modal-view">

                            <div class="ep-modal-overlay"></div>
                            <div class="ep-modal-wrap ep-fd-promo-popup">

                                <div class="ep-modal-titlebar">
                                    <div class="ep-modal-title"><?php _e('Extend Power of EventPrime', 'eventprime-event-calendar-management'); ?></div>
                                    <span class="ep-popup-close">&times;</span>
                                </div>

                                <div class="ep-dashboard-more ep-popup" style="display:none">

                                    <div class="ep-fd-promo-section" id="ep-fd-promo-config" >
                                        <div class="ep-fd-promo-subsection ep_fd_promo_subsect_tabs">
                                            <ul class="ep-fd-promo-section-icons ep-main-promo-section">
                                                <div class="ep-fd-promo-section-title"><?php _e('More Feature(s)', 'eventprime-event-calendar-management'); ?></div>
                                                <?php if (!in_array('seating', $extensions)): ?>
                                                            <li onmouseover="showExtFeatures(this, 'ep-ext-ticket-features', 'ep-ext-hover-features')"> <img  src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-ticket-integration-icon.png' ?>"> <span class="ep-fd-promo-nub" style="display: none;"></span> </li>
                                                 <?php endif; ?> 
                                                  <?php if (!in_array('seating', $extensions)): ?>
                                                            <li onmouseover="showExtFeatures(this, 'ep-ext-seat-features', 'ep-ext-hover-features')"> <img  src="<?php echo EM_BASE_URL . 'includes/admin/template/images/seating-integration-icon.png' ?>"> <span class="ep-fd-promo-nub" style="display: none;"></span> </li> 
                                               <?php endif; ?>
                                                <?php if (!in_array('sponser', $extensions)): ?>
                                                    <li onmouseover="showExtFeatures(this, 'ep-ext-sponser-features', 'ep-ext-hover-features')"> <img  src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-sponser-icon.png' ?>"> <span class="ep-fd-promo-nub" style="display: none;"></span> </li>
                                                <?php endif; ?>
                                                <?php if (!in_array('recurring_events', $extensions)): ?>
                                                    <li onmouseover="showExtFeatures(this, 'ep-ext-recurring-features', 'ep-ext-hover-features')"> <img src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-recurring-events-icon.png' ?>"> <span class="ep-fd-promo-nub" style="display: none;"></span> </li>
                                                <?php endif; ?>  
                                            </ul>
                                            <?php if (!in_array('seating', $extensions)): ?>
                                                   
                                                        <div class="ep-fd-promo-content-wrap ep-ext-hover-features" id="ep-ext-ticket-features" style="display:none">
                                                         <div class="ep-fd-promo-section-title"><?php _e('Ticket Manager', 'eventprime-event-calendar-management'); ?></div>

                                                         <div class="ep-fd-promo-content"><?php _e('Create and assign customizable ticket templates for your events to give booking tickets a unique look and feel. You can also allow volume discounts on tickets based on number of bookings.', 'eventprime-event-calendar-management'); ?></div>
                                                        </div>
                                            <?php endif; ?> 
                                             <?php if (!in_array('seating', $extensions)): ?>
                                                        <div class="ep-fd-promo-content-wrap ep-ext-hover-features" id="ep-ext-seat-features" style="display: none;">
                                                         <div class="ep-fd-promo-section-title"><?php _e('Live Seating', 'eventprime-event-calendar-management'); ?></div>

                                                            <div class="ep-fd-promo-content"><?php _e('Setup seating arrangement for your Event Sites with specific rows and columns of seats. Seating arrangement can also have walking aisles so attendees can pick and choose seats according to their convenience. Seat chosen by attendees upon booking will be visible on the assigned ticket template.', 'eventprime-event-calendar-management'); ?></div>
                                                        </div>                                              
                                             
                                            <?php endif; ?>  
                                            <?php if (!in_array('sponser', $extensions)): ?>
                                                <div class="ep-fd-promo-content-wrap ep-ext-hover-features" id="ep-ext-sponser-features" style="display:none">
                                                    <div class="ep-fd-promo-section-title"><?php _e('Event Sponsors', 'eventprime-event-calendar-management'); ?></div>
                                                    <div class="ep-fd-promo-content"><?php _e('Add Sponsors to your events by uploading Sponsor logos. The Sponsor logos will appear on the event page on the frontend.', 'eventprime-event-calendar-management'); ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!in_array('recurring_events', $extensions)): ?>
                                                <div class="ep-fd-promo-content-wrap ep-ext-hover-features" id="ep-ext-recurring-features" style="display:none">
                                                    <div class="ep-fd-promo-section-title"><?php _e('Recurring Events', 'eventprime-event-calendar-management'); ?></div>
                                                    <div class="ep-fd-promo-content"><?php _e('Create events that recur by your specified numbers of days, weeks, months, or years. Make updates to all recurring events at once by updating the main event. Or make custom changes to individual recurring events, such as different performers, event sites, booking amount etc.', 'eventprime-event-calendar-management'); ?></div>
                                                </div>
                                            <?php endif; ?> 
                                        </div>

                                    </div>

                                </div>

                                <div class="ep-modal-footer"><a href="admin.php?page=em_extensions" target="_blank">More Information</a></div>
                            </div>

                        </div>
                    </div>
                </div> -->
            <?php } ?>


        </div>

        <div class="ep-grid-section dbfl">
            <div class="ep-grid-section-title dbfl"><?php _e('Global Settings', 'eventprime-event-calendar-management'); ?></div>
            <div class="ep-grid-icon difl" id="ep-event-type">
                <a  target="_blank" href="<?php echo admin_url("/admin.php?page=em_event_types") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-event-type-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Event Types', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>


            <div class="ep-grid-icon difl" id="ep-event-sites-locations">
                <a  target="_blank" href="<?php echo admin_url("/admin.php?page=em_venues") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-event-sites-location-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Event Sites & Locations', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>
            
            <div class="ep-grid-icon difl" id="ep-event-sites-organizers">
                <a  target="_blank" href="<?php echo admin_url("/admin.php?page=em_event_organizers") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-event-sites-organizer-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Event Organizers', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>

            <?php do_action('event_magic_dashboard_ticket_templates'); ?>

            <div class="ep-grid-icon difl" id="ep-event-attendees">
                <a  target="_blank" href="<?php echo admin_url("/admin.php?page=em_bookings") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-event-booking.icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Bookings', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>


            <div class="ep-grid-icon difl" id="ep-event-performers">
                <a  target="_blank" href="<?php echo admin_url("/admin.php?page=em_performers") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-event-performers-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Performers', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>


            <div class="ep-grid-icon difl" id="ep-event-global-settings">
                <a  target="_blank" href="<?php echo admin_url("/admin.php?page=em_global_settings") ?>" class="ep-dash-link">    
                    <div class="ep-grid-icon-area dbfl">
                        <img class="ep-grid-icon dibfl" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-event-global-settings-icon.png' ?>">
                    </div>
                    <div class="ep-grid-icon-label dbfl"><?php _e('Global Settings', 'eventprime-event-calendar-management'); ?></div>
                </a>
            </div>

        </div>
        
        <?php $external_integrations = apply_filters('ep_external_integrations',array());  ?>
        <?php if(!empty($external_integrations)): ?>
            <div class="ep-grid-section dbfl">
                <div class="ep-grid-section-title dbfl"><?php _e('Integrate','eventprime-event-calendar-management'); ?></div>
                <?php do_action('ep_ext_integrations',$post_id); ?>
            </div>
        <?php endif; ?>

    </div>


    <div class="ep-grid-sidebar-1 difl">

        <div class="ep-grid-section-cards dbfl">
            <?php
            foreach ($bookings as $booking) {
                $user = get_user_by('id', $booking->user);
                if (empty($user))
                    continue;
                ?>
                <div class="ep-grid-sidebar-card dbfl ep-grid-user-new">
                    <a href="<?php echo admin_url('admin.php?page=em_booking_add&post_id=' . $booking->id); ?>" class="ep-dash-link">
                        <div class="ep-grid-card-profile-image dbfl">
                            <img class="fd_img" src="<?php echo get_avatar_url($user->user_email); ?>">
                        </div>
                        <div class="ep-grid-card-content difl">
                            <div class="dbfl"><?php echo $user->user_email; ?></div>
                            <div class="ep-grid-card-content-subtext dbfl"><?php echo em_showDateTime($booking->date, true); ?></div></div>
                    </a>
                </div> 
            <?php } ?>
            <?php if (empty($bookings)): ?>
                <div class="ep-grid-sidebar-card dbfl ep-grid-user-new">
                    <?php _e('No bookings yet.', 'eventprime-event-calendar-management'); ?>
                </div>
            <?php endif; ?>
            <div class="ep-grid-quick-tasks dbfl">
                <div class="ep-grid-sidebar-row dbfl">
                    <div class="ep-grid-sidebar-row-label difl">
                        <a class="" href="<?php echo admin_url('admin.php?page=em_bookings&event=' . $post_id) ?>"><?php _e('View All', 'eventprime-event-calendar-management'); ?></a>
                    </div>
                </div>
            </div>   
        </div>
    </div>

    <div class="ep-grid-sidebar-2 difl">
        <div class="ep-grid-section dbfl">
            <div class="ep-grid-section-title dbfl">
                <?php _e('Status', 'eventprime-event-calendar-management'); ?> 
            </div>
            <?php
            $sum = $event_service->booked_seats($post_id);
            $capacity = em_event_seating_capcity($post_id);?>  
            <div class="ep-grid-sidebar-row  dbfl">
                <div class="ep-grid-sidebar-row-icon difl"><span class="dashicons dashicons-post-status"></span></div>
                <div class="ep-grid-sidebar-row-label difl"><?php esc_html_e('Booking Status', 'eventprime-event-calendar-management');?></div>
                <div class="ep-grid-sidebar-row-value difl"><?php
                    if ($capacity > 0){?>
                        <div class="dbfl">
                            <?php echo $sum; ?> / <?php echo $capacity; ?> 
                        </div><?php 
                        $width = ($sum / $capacity) * 100; ?>
                        <div class="dbfl ">
                            <div id="progressbar" class="em_progressbar dbfl">
                                <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                            </div>
                        </div><?php
                    } else{
                        if($sum > 0){
                            echo '<div class="ep-event-attenders-wrap"><span class="ep-event-attenders">' . $sum . ' </span>'.__('Attending','eventprime-event-calendar-management').'</div>';
                        }
                    }?>
                </div>
            </div> 
            <div class="ep-grid-sidebar-row dbfl">
                <div class="ep-grid-sidebar-row-icon difl" id="ep-sidebar-sc-icon">
                    <span class="dashicons dashicons-editor-code"></span>
                </div>
                <div class="ep-grid-sidebar-row-label difl"><?php _e('Shortcode', 'eventprime-event-calendar-management'); ?>:</div>
                <div class="ep-grid-sidebar-row-value difl"> <span id="emeventshortcode"><?php echo "[em_event id='" . $event->id . "']" ?></span><a href="javascript:void(0)" onclick="em_copy_to_clipboard(document.getElementById('emeventshortcode'))"><?php _e('Copy', 'eventprime-event-calendar-management'); ?></a>
                    <div style="display: none;" id="em_msg_copied_to_clipboard"><?php _e('Copied to clipboard.', 'eventprime-event-calendar-management'); ?></div>
                    <div style="display:none" id="em_msg_not_copied_to_clipboard"><?php _e('Could not be copied. Please try manually.', 'eventprime-event-calendar-management'); ?></div>
                </div> 

            </div>  


            <div class="ep-grid-sidebar-row dbfl">
                <div class="ep-grid-sidebar-row-icon difl">
                    <span class="dashicons dashicons-calendar-alt"></span>
                </div>
                <div class="ep-grid-sidebar-row-label difl"><?php _e('Start Date', 'eventprime-event-calendar-management'); ?>:</div>
                <div class="ep-grid-sidebar-row-value difl"><?php echo em_showDateTime($event->start_date, true); ?></div>
            </div>

        </div>
    </div>
</div>
