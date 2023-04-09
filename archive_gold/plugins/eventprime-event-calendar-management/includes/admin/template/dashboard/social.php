<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre kf-container"  ng-controller="eventCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('event_social')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content">
        <div class="kf-db-title">
             <?php _e('Social Integration','eventprime-event-calendar-management'); ?>
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
                    <div class="emfield"><?php _e('Facebook Page','eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                         <input class="kf-fb-field" ng-pattern="/(?:(?:http|https):\/\/)?(?:www.)?facebook.com\/?/"  type="url" name="facebook_page"  ng-model="data.post.facebook_page" />
                        <div class="emfield_error">
                            <span ng-show="!postForm.facebook_page.$valid && !postForm.facebook_page.$pristine"><?php _e('Invalid Facebook URL','eventprime-event-calendar-management'); ?></span>
                        </div>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                       <?php _e("Facebook page URL for the Event, if available. Eg.:https://www.facebook.com/XYZ/",'eventprime-event-calendar-management'); ?>
                    </div>
             </div>

            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('/admin.php?page=em_dashboard&post_id=' . $post_id); ?>">&#8592; &nbsp;<?php _e('Cancel','eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid || requestInProgress"><?php _e('Save','eventprime-event-calendar-management'); ?></button>

            </div>
        </form>
    </div>
</div>


