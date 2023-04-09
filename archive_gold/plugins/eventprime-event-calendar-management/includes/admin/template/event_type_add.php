<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre kf-container"  ng-controller="eventTypeCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('edit')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content" ng-hide="requestInProgress">
        <div class="kf-db-title">
            <?php _e('Add/Edit Event Type','eventprime-event-calendar-management'); ?>
        </div>
        <div class="form_errors">
            <ul>
                <li class="emfield_error" ng-repeat="error in formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>  
        </div>
        <!-- FORM -->
        <form name="termForm" ng-submit="saveEventType(termForm.$valid)" novalidate >
            <div class="emrow">
                <div class="emfield"><?php _e('Name','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required type="text" name="name"  ng-model="data.term.name">
                    <div class="emfield_error">
                        <span ng-show="termForm.name.$error.required && !termForm.name.$pristine"><?php _e('This is  required field.','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Name the Event type.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php _e('Background Color','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required id="em_color_picker" class="jscolor" type="text" name="color" ng-model="data.term.color" >
                    <div class="emfield_error">
                        
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Background color for events of this type when they appear on the events calendar.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php _e('Text Color','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input id="em_text_color_picker" class="jscolor"  type="text" name="type_text_color" ng-model="data.term.type_text_color" >
                    <div class="emfield_error"></div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Text color for events of this type when they appear on the events calendar. Can be overridden for individual events from their respective settings.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php _e('Age Group','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <select  ng-model="data.term.age_group" name="age_group" ng-options="group.key as group.label for group in data.term.age_groups">
                    </select>
                    <div class="emfield_error">
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Valid age group for the Event. This will be displayed on Event page.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow" ng-show="data.term.age_group=='custom_group'">
                <div class="emfield"> <?php _e('Custom Age','eventprime-event-calendar-management'); ?></div>
                <div class="eminput ep_custom_age-slider">
                    <div id="slider"></div>
                    <input style="display:none" id="custom_group" type="text" name="custom_group" ng-model="data.term.custom_group" >
                    <div class="em_custom_group">{{data.term.custom_group}}</div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                     <?php _e('Enter Age advisory for this Event Type. For example, age should be between 21-28.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php _e('Image','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input class="kf-upload" type="button" ng-click="mediaUploader(false)" value="<?php _e('Upload','eventprime-event-calendar-management'); ?>" />
                    <div class="em_cover_image em_gallery_images">
                     <img ng-src="{{data.term.image}}" />
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Image or icon of the Event Type. Will be displayed on the Event Types directory page.','eventprime-event-calendar-management'); ?>
                </div>
                <input type="text" class="hidden" ng-model="term.image_id" />
            </div>
            <div class="emrow">
                <div class="emfield"><?php _e('Featured','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="is_featured"  ng-model="data.term.is_featured" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Check if you want to make this event type featured.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow kf-bg-light">
                <div class="emfield emeditor"><?php _e('Special Instructions','eventprime-event-calendar-management'); ?></div>
                <div class="eminput emeditor">
                    <?php 
                        $term_id= event_m_get_param('term_id');
                        $content='';
                        if($term_id!==null && (int)$term_id>0)
                        {
                            $term= get_term($term_id);
                            $content= em_get_term_meta($term->term_id, 'description', true);
                        }
                        wp_editor($content,'description');
                    ?>
                    <div class="emfield_error">
                    </div>
                </div>
                <div class="emnote emeditor">
                    <?php _e('Special Instructions','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('admin.php?page=em_event_types'); ?>">&#8592; &nbsp;<?php _e('Cancel','eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="termForm.$invalid || requestInProgress"><?php _e('Save','eventprime-event-calendar-management'); ?></button>
                <span class="kf-error" ng-show="termForm.$invalid && termForm.$dirty "><?php _e('Please fill all required fields.','eventprime-event-calendar-management'); ?></span>
            </div>
            <div class="dbfl kf-required-errors" ng-show="termForm.$invalid && termForm.$dirty">
                <h3><?php _e("Looks like you missed out filling some required fields (*). You will not be able to save until all required fields are filled in. Here’s what's missing",'eventprime-event-calendar-management'); ?> -
                <span ng-show="termForm.name.$error.required"><?php _e("Name",'eventprime-event-calendar-management'); ?></span>
                <span ng-show="termForm.color.$error.required"><?php _e('Color','eventprime-event-calendar-management'); ?></span>
                </h3>
            </div>
        </form>
    </div>
</div>