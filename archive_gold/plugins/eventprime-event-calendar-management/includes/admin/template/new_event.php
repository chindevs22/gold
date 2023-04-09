<?php do_action('event_magic_admin_promotion_banner'); ?>
<div  class="kikfyre" ng-app="eventMagicApp" ng-controller="eventCtrl" ng-init="initialize('edit')" ng-cloak>
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content">
        <div class="kf-db-title">
            <?php _e('Add/Edit Event', 'eventprime-event-calendar-management'); ?>

            <div ng-click="closeEventPopup()" style="float:right;">close</div>
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
                <div class="emfield"><?php _e('Name', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required type="text" name="name"  ng-model="data.post.name">
                    <div class="emfield_error">
                        <span ng-show="postForm.name.$error.required && (showFormErrors || !postForm.name.$pristine)"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Name of your Event. Should be unique.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow" ng-show="post_edit">
                <div class="emfield"><?php _e('Slug', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input ng-required="post_edit" type="text" name="slug"  ng-model="data.post.slug">
                    <div class="emfield_error">
                        <span ng-show="postForm.slug.$error.required && !postForm.slug.$pristine"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Slug is the user friendly URL of an Event. Example: /popatbrooklyn, /jazznight,/monstertruckracing etc.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>



            <div class="emrow">
                <div class="emfield"><?php _e('Day Starts', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required id="event_start_date" readonly=""  type="text" name="start_date"  ng-model="data.post.start_date">
                    <div class="emfield_error">
                        <span ng-show="postForm.start_date.$error.required && !postForm.start_date.$pristine"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                        <span ng-show="postForm.start_date.$error.pattern && !postForm.start_date.$pristine"><?php _e('Format should be DD/MM/YYYY', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Event start date and time.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php _e('Day Ends', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required id="event_end_date" readonly="readonly" type="text" name="end_date"  ng-model="data.post.end_date" ng-change="update_start_booking_date()">
                    <div class="emfield_error">
                        <span ng-show="postForm.end_date.$error.required && !postForm.end_date.$pristine"><?php _e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                        <span ng-show="postForm.end_date.$error.pattern && !postForm.end_date.$pristine"><?php _e('Format should be DD/MM/YYYY', 'eventprime-event-calendar-management'); ?></span>
                        <span ng-show="postForm.end_date.$error.invalidEndDate"><?php _e('End date should be greater than start date', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Event end date and time. Should always be later than the Start Date.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" ng-click="closeEventPopup()">&#8592; &nbsp;<?php _e('Cancel', 'eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid || requestInProgress"><?php _e('Save', 'eventprime-event-calendar-management'); ?></button>

            </div>

        </form>

    </div>



</div>