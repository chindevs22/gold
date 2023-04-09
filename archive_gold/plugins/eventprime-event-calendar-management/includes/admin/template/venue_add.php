<?php 
do_action('event_magic_admin_promotion_banner');
$em = event_magic_instance(); ?>
<div class="kikfyre kf-container" ng-controller="venueCtrl" ng-app="eventMagicApp" ng-init="initialize('edit')" ng-cloak>
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content">
        <div class="kf-title">
            <?php esc_html_e('Event Site/Location', 'eventprime-event-calendar-management'); ?>
        </div>

        <div class="form_errors">
            <ul>
                <li class="emfield_error" ng-repeat="error in  formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>  
        </div>

        <div class="em_notice">
            <div class="map_notice" ng-show="!data.term.map_configured">
                {{data.term.map_notice}}
            </div>
        </div>
        <!-- FORM -->
        <form name="termForm" ng-submit="saveTerm(termForm.$valid)" novalidate >
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Name', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input placeholder="" required type="text" name="name"  ng-model="data.term.name">
                    <div class="emfield_error">
                        <span ng-show="termForm.name.$error.required && !termForm.name.$pristine"><?php esc_html_e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Name of the Event Site. Should be unique.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow" ng-show="term_edit" style="display:none;">
                <div class="emfield"><?php esc_html_e('Slug', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input ng-required="term_edit" type="text" name="slug"  ng-model="data.term.slug">
                    <div class="emfield_error">
                        <span ng-show="termForm.slug.$error.required && !termForm.slug.$pristine"><?php esc_html_e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Slug is the user friendly URL for the Venue. Example: /minnesotagrounds,/sydneyolympicpark, /millersstadium etc.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow kf-bg-light">
                <div class="emfield emeditor"><?php esc_html_e('Description', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput emeditor">
                    <?php
                    $id = event_m_get_param('term_id');
                    $content = '';
                    if (!empty($id)) {
                        $term = get_term($id);
                        $content = em_get_term_meta($id, 'description', true);
                    }
                    wp_editor($content, 'description');
                    ?>
                    <div class="emfield_error">
                    </div>
                </div>
                <div class="emnote emeditor">
                    <?php esc_html_e('Details about the Event Site. Will be displayed on Event and Event Site page.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow kf-bg-light">
                <div class="emfield emeditor"><?php esc_html_e('Address', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput emeditor">
                    <input id="em-pac-input" name="address" ng-model="data.term.address" ng-keydown="editingAddress=true" ng-keyup="editingAddress=false" class="em-map-controls" type="text">
                    <div id="map" ng-show="data.term.map_configured"></div>
                    <div id="type-selector" class="em-map-controls" style="display:none">
                        <input type="radio" name="type" id="changetype-all" checked="checked">
                        <label for="changetype-all"><?php esc_html_e('All', 'eventprime-event-calendar-management'); ?></label>

                        <input type="radio" name="type" id="changetype-establishment">
                        <label for="changetype-establishment"><?php esc_html_e('Establishments', 'eventprime-event-calendar-management'); ?></label>

                        <input type="radio" name="type" id="changetype-address">
                        <label for="changetype-address"><?php esc_html_e('Addresses', 'eventprime-event-calendar-management'); ?></label>

                        <input type="radio" name="type" id="changetype-geocode">
                        <label for="changetype-geocode"><?php esc_html_e('Geocodes', 'eventprime-event-calendar-management'); ?></label>
                    </div>
                </div>
                <div class="emnote emeditor" ng-show="data.term.map_configured"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Mark map location for the Event Site. This will be displayed on Event page.', 'eventprime-event-calendar-management'); ?>
                </div>
                
                <div class="emrow" ng-show="data.term.map_configured">
                    <div class="emfield"><?php esc_html_e('Latitude', 'eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input  type="text" name="lat"  ng-model="data.term.lat" id="em_venue_lat">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Latitude', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
                
                <div class="emrow" ng-show="data.term.map_configured">
                    <div class="emfield"><?php esc_html_e('Longitude', 'eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input  type="text" name="lng"  ng-model="data.term.lng" id="em_venue_long">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Longitude', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow" ng-show="data.term.map_configured">
                    <div class="emfield"><?php esc_html_e('Zoom Level', 'eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input type="number" string-to-number name="zoom_level" ng-model="data.term.zoom_level" id="em_venue_zoom_level">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Zoom Level On Map', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow" ng-show="data.term.map_configured">
                    <div class="emfield"><?php esc_html_e('Display Address', 'eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input type="checkbox" name="display_address_on_frontend" ng-model="data.term.display_address_on_frontend" id="em_venue_display_address" ng-true-value="1" ng-false-value="0">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Display Address On Frontend', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Established', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input readonly="readonly" type="text" name="established"  ng-model="data.term.established" id="established">
                    <input type="button" value="Reset" ng-click="data.term.established = ''" />
                    <div class="emfield_error">
                        <span ng-show="termForm.established.$error.pattern && !termForm.established.$pristine"></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('When the Event Site opened for public.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Seating Type', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <!-- <select required name="type" ng-model="data.term.type" ng-options="type.key as type.label for type in data.term.types"></select> -->

                    <select required name="type" ng-model="data.term.type" class="search_category">
                        <option ng-repeat="type in data.term.types" value="{{type.key}}" ng-selected="type.key == data.term.type">{{type.label}}</option>
                        <?php if (!in_array('seating', $em->extensions)){?>
                            <option value="seats" disabled><?php esc_html_e('Seating','eventprime-event-calendar-management');?></option>
                        <?php }?>
                    </select>
                    <div class="emfield_error">
                        <span ng-show="termForm.type.$error.required && !termForm.type.$pristine"><?php esc_html_e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Type of seating arrangement- Standing or Seating.', 'eventprime-event-calendar-management'); ?>
                    <?php if (!in_array('seating', $em->extensions)){?>
                        <br><br>
                        <span class="ep_buy_pro_inline">
                            <?php esc_html_e('To add this feature (and many more), please upgrade','eventprime-event-calendar-management'); ?> 
                            <a href="<?php echo empty($buy_link) ? esc_url('https://eventprime.net/plans/') : $buy_link; ?>" target="blank">
                                <?php esc_html_e('Click here','eventprime-event-calendar-management'); ?>
                            </a>
                        </span><?php 
                    }?>
                </div>
            </div>

            <div class="emrow" ng-if="data.term.type=='standings'">
                <div class="emfield"><?php esc_html_e('Standing Capacity', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input type="number" name="standing_capacity" ng-model="data.term.standing_capacity" required min="1">
                    <div class="emfield_error">
                        <span ng-show="termForm.standing_capacity.$error.required && data.term.type=='standings'"><?php esc_html_e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                        <span ng-show="termForm.standing_capacity.$error.number && data.term.type=='standings'"><?php esc_html_e('Only numeric value allowed.', 'eventprime-event-calendar-management'); ?></span>
                        <span ng-show="termForm.standing_capacity.$error.min && data.term.type=='standings'"><?php esc_html_e('Value should be greater than 0', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Maximum capacity of the venue or the maximum number of bookings you wish to allow.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Operator', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input  type="text" name="seating_organizer"  ng-model="data.term.seating_organizer" ng-disabled="isSeatingDisabled">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Event Site coordinator name or contact details.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Facebook Page', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input class="kf-fb-field" ng-pattern="/(?:(?:http|https):\/\/)?(?:www.)?facebook.com\/?/" type="url" name="facebook_page"  ng-model="data.term.facebook_page">
                    <div class="emfield_error">
                        <span ng-show="termForm.facebook_page.$error.url && !termForm.facebook_page.$pristine"><?php esc_html_e('Invalid Facebook URL', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Facebook page URL of the Event Site, if available. Eg.:https://www.facebook.com/XYZ/', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow kf-bg-light">
                <div class="emfield emeditor"><?php esc_html_e('Site Gallery', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput emeditor">
                    <input id="media_upload" type="button" ng-click="mediaUploader(true)" class="button kf-upload" value="<?php esc_html_e('Upload', 'eventprime-event-calendar-management'); ?>" />
                    <div class="em_gallery_images">
                        <ul id="em_draggable" class="dbfl">
                            <li class="kf-db-image difl" ng-repeat="(key, value) in data.term.images" id="{{value.id}}">
                                <div><img class="difl" ng-src="{{value.src[0]}}" />
                                    <span><input class="em-remove_button" type="button" ng-click="deleteGalleryImage(value.id, key)" value="Remove"/></span> 
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="emnote emeditor">
                    <?php esc_html_e('Displays multiple images of the Event Site as gallery.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <input class="hidden" type="text" name="gallery_images" ng-model="data.term.gallery_images" />

            <div class="emrow">
                <div class="emfield"><?php _e('Featured','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="is_featured"  ng-model="data.term.is_featured" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Check if you want to make this event site featured.','eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <?php do_action('event_magic_venue_seatings', $term_id); ?>

            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo esc_url(admin_url('/admin.php?page=em_venues')); ?>">&#8592; &nbsp;<?php esc_html_e('Cancel', 'eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="termForm.$invalid || requestInProgress || editingAddress"><?php esc_html_e('Save', 'eventprime-event-calendar-management'); ?></button>
                <span class="kf-error" ng-show="termForm.$invalid && termForm.$dirty"><?php esc_html_e('Please fill all required fields.', 'eventprime-event-calendar-management'); ?></span>
            </div>

            <div class="dbfl kf-required-errors" ng-show="termForm.$invalid && termForm.$dirty">
                <h3><?php esc_html_e("Looks like you missed out filling some required fields (*). You will not be able to save until all required fields are filled in. Here’s what's missing", 'eventprime-event-calendar-management'); ?> -
                    <span ng-show="termForm.name.$error.required"><?php esc_html_e('Name', 'eventprime-event-calendar-management'); ?></span>
                    <span ng-show="termForm.type.$error.required"><?php esc_html_e('Seating Type', 'eventprime-event-calendar-management'); ?></span>
                    <span ng-show="termForm.seating_capacity.$error.required"><?php esc_html_e('Seating Capacity', 'eventprime-event-calendar-management'); ?></span>
                    <span ng-show="termForm.rows.$error.required"><?php esc_html_e('Rows', 'eventprime-event-calendar-management'); ?></span>
                    <span ng-show="termForm.columns.$error.required"><?php esc_html_e('Columns', 'eventprime-event-calendar-management'); ?></span>
                </h3>
            </div>
        </form>
        <div id="show_popup" ng-show = "IsVisible">
            <div class="pm-popup-mask"></div>    
            <div id="pm-change-password-dialog">
                <div class="pm-popup-container">
                    <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>