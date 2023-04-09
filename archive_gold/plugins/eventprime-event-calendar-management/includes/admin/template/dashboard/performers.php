<?php do_action('event_magic_admin_promotion_banner');
$performer_text = em_global_settings_button_title('Performer');
$performers_text = em_global_settings_button_title('Performers');?>
<div class="kikfyre kf-container"  ng-controller="eventCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('event_performers')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content">
        <div class="kf-db-title">
            <?php _e( $performer_text . '(s)','eventprime-event-calendar-management'); ?>
        </div>
        <div class="form_errors">
            <ul>
                <li class="emfield_error" ng-repeat="error in  formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>  
        </div>
        <!-- FORM -->
        <form  name="postForm" ng-submit="savePost(postForm.$valid)" novalidate>

            <div class="emrow">
                <div class="emfield"><?php _e('Event has ' . $performer_text . '(s)','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="enable_performer"  ng-model="data.post.enable_performer"  ng-true-value="1" ng-false-value="0">
                </div>
                
            </div>

            <div ng-show="data.post.enable_performer">
                <div class="emrow">
                    <div class="emfield"><?php _e( $performers_text, 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                    <div class="eminput">
                        <select name="performer" ng-required="data.post.performer!='new_performer'"  id="em_performer" multiple ie-select-fix="data.post.performers"  ng-model="data.post.performer" ng-options="performer.id as performer.name for performer in data.post.performers"></select>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php _e('Person/Group who will be performing at the Event. You can choose multiple '.$performer_text.'. For additional entries, select “New '.$performer_text.'”.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div ng-show="data.post.performer == 'new_performer'">
                    <div class="emrow">
                        <div class="emfield"><?php _e('New '.$performer_text.' Name','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                        <div class="eminput">
                            <input ng-required="data.post.performer=='new_performer'" type="text" name="custom_performer_name" ng-model="data.post.custom_performer_name" /></select>
                            <div class="emfield_error">
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Name of the '.$performer_text.'.','eventprime-event-calendar-management'); ?>
                        </div>
                    </div>

                    <div class="emrow">
                        <div class="emfield"><?php _e('New '.$performer_text.' Type','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                        <div class="eminput">
                            <ul class="emradio" >
                                <li>
                                    <input ng-required="data.post.performer=='new_performer'" name="custom_performer_type" type="radio" name="type"  ng-model="data.post.custom_performer_type" value="person"><?php _e('Person','eventprime-event-calendar-management'); ?>
                                </li>
                                <li>
                                    <input  ng-required="data.post.performer=='new_performer'" name="custom_performer_type" type="radio" name="type"  ng-model="data.post.custom_performer_type" value="group"><?php _e('Group','eventprime-event-calendar-management'); ?>
                                </li>
                            </ul>
                            <div class="emfield_error">
                                <span ng-show="postForm.title.$error.required && !postForm.title.$pristine"><?php _e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="emrow">
                    <div class="emfield"> <?php _e('Match','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input type="checkbox" name="match" ng-model="data.post.match"  ng-true-value="1" ng-false-value="0">
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php _e('Match between two '.$performer_text.'.','eventprime-event-calendar-management'); ?>
                    </div>
                </div>    
            </div>

            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('/admin.php?page=em_dashboard&post_id=' . $post_id); ?>"> &#8592; &nbsp;<?php _e('Cancel','eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid || requestInProgress"> <?php _e('Save','eventprime-event-calendar-management'); ?></button>
            </div>

            <div class="dbfl kf-required-errors" ng-show="postForm.$dirty && postForm.$invalid">
                <h3><?php _e('Please select a '.$performer_text.'.','eventprime-event-calendar-management'); ?>
                    <span ng-show="postForm.custom_performer_name.$error.required"><?php _e('New '.$performer_text.' Name','eventprime-event-calendar-management'); ?></span>
                    <span ng-show="postForm.custom_performer_type.$error.required"><?php _e('New '.$performer_text.' Type','eventprime-event-calendar-management'); ?></span>
                    <span ng-show="postForm.custom_performer_slug.$error.required"><?php _e('New '.$performer_text.' Slug','eventprime-event-calendar-management'); ?></span>
                </h3>
            </div>
        </form>
    </div>
</div>