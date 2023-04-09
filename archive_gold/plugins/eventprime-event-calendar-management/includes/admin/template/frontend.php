<?php 
$setting_service = EventM_Factory::get_service('EventM_Setting_Service');
$options = $setting_service->load_model_from_db();?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="ep-frontend ep-shortcode-page">
    <div class="ep-sc-wrap dbfl">
        <div class="ep-sc-blocks dbfl">
            <div class="ep-sc-block-row dbfl  ep-scpagetitle"> <b><?php _e('EventPrime','eventprime-event-calendar-management') ?></b> <span class=""><?php _e('Shortcodes','eventprime-event-calendar-management') ?></span> </div>
        </div>
        <div class="ep-sc-blocks dbfl">
            <div class="ep-sc-block ep-all-event-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('All Events','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code"><a target="_blank" href="<?php echo admin_url('post.php?post='.$options->events_page.'&action=edit'); ?>">[em_events view="x" id="{EVENT_ID}" types="1,2,&#133;" sites="1,2,&#133;" show="5" upcoming="0 or 1" recurring="0 or 1" disable_filter="0 or 1" filter_elements="keyword,type,date,site" individual_events="yesterday or today or tomorrow or this month"]</a></span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-all-event.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Shows all Events on the frontend. Use 'view', 'types', 'sites', 'show', 'upcoming' and 'recurring' attributes to set their default values. 'types' should be Event Type IDs (comma separated). 'sites' should be Event Site IDs (comma separated). 'show' attribute will be use to list no. of events with card view. 'upcoming' for show/hide upcoming events. 'recurring' for show/hide recurring events. 'disable_filter' for hide/show event filter. 'filter_elements' to display only given filters. 'individual_events' shows events according to the given value.<br> <b>NOTE: If you use id and view attributes together then it will show event in view form, otherwise without view attribute it will show event details page. </b></div>
                </div>
            </div>
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('All Event Types','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code"><a target="_blank" href="<?php echo admin_url('post.php?post='.$options->event_types.'&action=edit'); ?>">[em_event_types display_style="card/list/box" limit="{NUMBER}" cols="{NUMBER}" load_more="0 or 1" search="0 or 1" featured="0 or 1" popular="0 or 1"]</a></span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-all-event-types.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Displays the all Event Types panel on the frontend where users can view the categories and the number of events that belong to them. Use 'display_style', 'limit', 'cols', 'load_more', 'search', 'featured' and 'popular' attributes to set their default values. 'limit' will be use to list no. of event types. 'cols' will be use to list no. of event types in one column. 'load_more' for show/hide load more event types. 'search' for hide/show search event types. 'featured' for hide/show featured event types. 'popular' for hide/show popular event types.</div>
                </div>
            </div>
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('All Performers','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code"><a target="_blank" href="<?php echo admin_url('post.php?post='.$options->performers_page.'&action=edit'); ?>">[em_performers display_style="card/list/box" limit="{NUMBER}" cols="{NUMBER}" load_more="0 or 1" search="0 or 1" featured="0 or 1" popular="0 or 1"]</a></span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-all-performers.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Displays the all Performers panel on the frontend where users can view the list of Performers that can perform in events. Use 'display_style', 'limit', 'cols', 'load_more', 'search', 'featured' and 'popular' attributes to set their default values. 'limit' will be use to list no. of performers. 'cols' will be use to list no. of performers in one column. 'load_more' for show/hide load more performers. 'search' for hide/show search performers. 'featured' for hide/show featured performers. 'popular' for hide/show popular performers.</div>
                </div>
            </div>
        </div>
        <div class="ep-sc-blocks dbfl">
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('All Sites/Locations','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code"><a target="_blank" href="<?php echo admin_url('post.php?post='.$options->venues_page.'&action=edit'); ?>">[em_sites display_style="card/list/box" limit="{NUMBER}" cols="{NUMBER}" load_more="0 or 1" search="0 or 1" featured="0 or 1" popular="0 or 1"]</a></span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-all-Venues.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Displays the all Event Sites panel on the frontend where users can view the list of Event Sites at which events can take place. Use 'display_style', 'limit', 'cols', 'load_more', 'search', 'featured' and 'popular' attributes to set their default values. 'limit' will be use to list no. of sites/locations. 'cols' will be use to list no. of sites/locations in one column. 'load_more' for show/hide load more sites/locations. 'search' for hide/show search sites/locations. 'featured' for hide/show featured sites/locations. 'popular' for hide/show popular sites/locations.</div>
                </div>
            </div>
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('User Account Area','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code"><a target="_blank" href="<?php echo admin_url('post.php?post='.$options->profile_page.'&action=edit'); ?>">[em_profile default="login or registration"]</a></span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-user-profile.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Displays the user profile panel where a user can login and see his/her bookings for events. 'default' attribute will be use to show login or registration screens when user is not loggedin.</div>
                </div>
            </div>
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('Display Individual Event','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code">[em_event id="x" upcoming="0 or 1"]</span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-single-event.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Displays all details of a single Event. Replace x with the Event ID. 'upcoming' attribute will be use to hide or show upcoming events.</div>
                </div>
            </div>
        </div>
        <div class="ep-sc-blocks dbfl">
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('Display Individual Event Type','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code">[em_event_type id="{EVENT_TYPE_ID}" event_style="card/list/slider" event_limit="{NUMBER}" event_cols="{NUMBER}" load_more="0 or 1" hide_past_events="0 or 1"]</span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-single-event-type.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Displays all details of a single Event Type. 'event_limit' will be use to list no. of events. 'event_cols' will be use to list no. of events in one column. 'load_more' for show/hide load more events. 'hide_past_event' for hide/show past events.</div>
                </div>
            </div>
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('Display Individual Performer','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code">[em_performer id="{PERFORMER_ID}" event_style="card/list/mini-list" event_limit="{NUMBER}" event_cols="{NUMBER}" load_more="0 or 1" hide_past_event="0 or 1"]</span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-single-performer.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Displays all details of a single Performer. 'event_limit' will be use to list no. of events. 'event_cols' will be use to list no. of events in one column. 'load_more' for show/hide load more events. 'hide_past_event' for hide/show past events.</div>
                </div>
            </div>
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php  _e('Display Individual Event Site/Location','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code">[em_event_site id="{EVENT_SITE_ID}" event_style="card/list/mini-list" event_limit="{NUMBER}" event_cols="{NUMBER}" load_more="0 or 1" hide_past_events="0 or 1"]</span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-single-vanue.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Displays all details of a single Event Site. 'event_limit' will be use to list no. of events. 'event_cols' will be use to list no. of events in one column. 'load_more' for show/hide load more events. 'hide_past_event' for hide/show past events.</div>
                </div>
            </div>
        </div>
        
        <div class="ep-sc-blocks dbfl">
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('Processes Event Bookings','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code"><a target="_blank" href="<?php echo admin_url('post.php?post='.$options->booking_page.'&action=edit'); ?>">[em_booking]</a></span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-booking-shortcode.png' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Add this shortcode to the page on which you want to process your event bookings.</div>
                </div>
            </div>
            
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('Display Event Submission Form','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code">[em_event_submit_form]</span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/event-submission-form.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Add this shortcode to the page on which you want to display the frontend event submission form.</div>
                </div>
            </div>
            
            <div class="ep-sc-block difl">
                <div class="ep-sc-block-row dbfl ep-sctitle"><?php _e('Event Organizers','eventprime-event-calendar-management') ?></div>
                <div class="ep-sc-block-row dbfl"><span class="ep-code"><a target="_blank" href="<?php echo admin_url('post.php?post='.$options->event_organizers.'&action=edit'); ?>">[em_event_organizers display_style="card/list/box" limit="{NUMBER}" cols="{NUMBER}" load_more="0 or 1" search="0 or 1" featured="0 or 1" popular="0 or 1"]</a></span></div>
                <div class="ep-sc-block-row dbfl"> <img class="ep-scimg" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/em-all-event-types.jpg' ?>">
                    <div class="ep-sc-block-row dbfl ep-scdesc">Displays the all Event Organizers panel on the frontend where users can view the categories and the number of events that belong to them. Use 'display_style', 'limit', 'cols', 'load_more', 'search', 'featured' and 'popular' attributes to set their default values. 'limit' will be use to list no. of event organizers. 'cols' will be use to list no. of event organizers in one column. 'load_more' for show/hide load more event organizers. 'search' for hide/show search event organizers. 'featured' for hide/show featured event organizers. 'popular' for hide/show popular event organizers.</div>
                </div>
            </div>
        </div>
    </div>
</div>