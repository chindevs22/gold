<?php do_action('event_magic_admin_promotion_banner'); ?>
<?php  
  wp_enqueue_script('em-select2');
  wp_enqueue_style('em_select2_css', plugin_dir_url(__DIR__) . '/css/select2.min.css', false, EVENTPRIME_VERSION);
?>
<div class="kikfyre kf-container" ng-controller="eventCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('event_organizer')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content" ng-hide="requestInProgress">
        <div class="kf-db-title">
            <?php _e('Organizer','eventprime-event-calendar-management'); ?>
        </div>
        <div class="form_errors">
            <ul>
                <li class="emfield_error" ng-repeat="error in formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>  
        </div>
        <!-- FORM -->
        <form name="postForm" ng-submit="savePost(postForm.$valid)" novalidate>
            <div class="emrow">
                <div class="emfield"><?php _e('Event Organizer','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <select multiple id="organizer" name="organizer" ng-model="data.post.organizer" ng-options="organizer.id as organizer.name for organizer in data.post.organizers"></select>
                </div>
                <div class="emfield_error"></div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Select the Organizer.','eventprime-event-calendar-management'); ?>
                </div>
            </div> 
        
            <div class="emrow">
                <div class="emfield"><?php _e('Hide Organizer Details','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="hide_organizer" ng-model="data.post.hide_organizer" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Enable this option to hide organizer details on the event page. If disabled, organizer details will appear on the event page.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('/admin.php?page=em_dashboard&post_id=' . $post_id); ?>"> &#8592; &nbsp;<?php _e('Cancel','eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid || requestInProgress"> <?php _e('Save','eventprime-event-calendar-management'); ?></button>
            </div>
            <div class="dbfl kf-required-errors" ng-show="postForm.$dirty && postForm.$invalid">
            </div>
        </form>
    </div>
</div>

<script>
(function($){    
    $(document).ready(function(){
        setTimeout(function(){
            $("#organizer").select2({
                placeholder: "Select Oragnizer",
                tags: true,
                width: "80%"
            });
        }, 2000);
    });
})(jQuery);  
</script>