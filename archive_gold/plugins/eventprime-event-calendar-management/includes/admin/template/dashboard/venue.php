<?php   
    $gmap_api_key= em_global_settings('gmap_api_key');
    if(!empty($gmap_api_key)){
        wp_enqueue_script('google_map_key', 'https://maps.googleapis.com/maps/api/js?key='.$gmap_api_key.'&libraries=places', array(), EVENTPRIME_VERSION);
    }
?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre kf-container"  ng-controller="eventCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('event_venue')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content">
        <div class="kf-db-title">
            <?php _e('Site & Location','eventprime-event-calendar-management'); ?>
        </div>
        <div class="form_errors">
            <ul>
                <li class="emfield_error kf-required-errors" ng-repeat="error in  formErrors">
                    <h3 ng-bind-html="error | unsafe"></h3>
                </li>
            </ul>  
        </div>
        <!-- FORM -->
        <form  name="postForm" ng-submit="savePost(postForm.$valid)" novalidate>
            <div class="emrow">
                <div class="emfield"><?php _e('Event Site','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <select id="em_venue" ng-change="getCapacity()" name="venue"  ng-model="data.post.venue" ng-options="venue.id as venue.name for venue in data.post.venues"></select>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Select the Site. To quickly add a new one, choose "Add New Site".','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            
            <div class="emrow" ng-show="data.post.venue=='new_venue'">
                <div class="emfield"><?php _e('New Event Site Name','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input type="text" name="new_venue" ng-required="data.post.venue=='new_venue'" ng-model="data.post.new_venue" />
                   <div class="emfield_error">
                        <span ng-show="postForm.new_venue.$error.required && !postForm.new_venue.$pristine"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Name of the Venue where Event will be hosted.','eventprime-event-calendar-management'); ?>
                </div>
                
                <div class="emrow kf-bg-light">
                    <div class="emfield emeditor"><?php _e('Address','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput emeditor">
                        <input id="em-pac-input" ng-required="data.post.venue=='new_venue'" name="new_venue_address" ng-model="data.post.new_venue_address" ng-keydown="editingAddress=true" ng-keyup="editingAddress=false" class="em-map-controls" type="text">
                        <?php if(!empty($gmap_api_key)) : ?>
                        <div id="map"></div>
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
                    <div class="emnote ep-map-note"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php _e('Mark map location for the Event Site. This will be displayed on Event page.', 'eventprime-event-calendar-management'); ?>
                    </div>
                    <input type="hidden" name="new_venue_lat" ng-model="data.post.new_venue_lat" id="em_venue_lat"/>
                    <input type="hidden" name="new_venue_lng" ng-model="data.post.new_venue_lng" id="em_venue_lng"/>
                    <?php endif; ?>
                </div>
                
            </div>
            
            <div class="emrow" >             
                <div class="emfield"><span class="kf_dragger">&#x2020;</span><?php _e('Capacity','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="number" ng-min="0" name="seating_capacity"  ng-if="data.post.venue_type == 'seats'"ng-model="data.post.seating_capacity">
                    <input type="number" ng-min="0" name="standing_capacity" ng-if="data.post.venue_type == 'standings' || data.post.venue_type == ''"  ng-model="data.post.standing_capacity">
                    <div class="emfield_error">
                        <span ng-show="postForm.seats.$error.number && !postForm.seats.$pristine"><?php _e('Only numeric value allowed','eventprime-event-calendar-management'); ?></span>
                        <span ng-show="postForm.seating_capacity.$error.capacityExceeded"><?php _e('Capacity exceeded than Venue.','eventprime-event-calendar-management'); ?></span>
                        <span ng-show="postForm.seating_capacity.$error.min">Invalid Value</span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Maximum number of bookings allowed for this Event. Leave blank or 0 for unlimited bookings.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            
            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('/admin.php?page=em_dashboard&post_id=' .$post_id); ?>">&#8592; &nbsp;<?php _e('Cancel','eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid || requestInProgress || editingAddress"><?php _e('Save','eventprime-event-calendar-management'); ?></button>
            </div>


            <div class="dbfl kf-required-errors" ng-show="postForm.$dirty && postForm.$invalid">
                <h3><?php _e("Looks like you missed out filling some required fields (*). You will not be able to save until all required fields are filled in. Here’s what's missing",'eventprime-event-calendar-management') ?> -

                   <span ng-show="postForm.new_venue.$error.required || postForm.new_venue.$error.required"><?php _e('New Event Site Name','eventprime-event-calendar-management'); ?></span>
                    <?php if(!empty($gmap_api_key)) : ?>
                        <span ng-show="postForm.new_venue.$error.required || postForm.new_venue_address.$error.required"><?php _e('Address','eventprime-event-calendar-management'); ?></span>
                    <?php endif; ?>     
                   <span ng-show="postForm.new_venue.$error.required || postForm.new_venue_capacity.$error.required"><?php _e('Seating Capacity','eventprime-event-calendar-management'); ?></span>
                </h3>

            </div>

        </form>
    </div>
</div>