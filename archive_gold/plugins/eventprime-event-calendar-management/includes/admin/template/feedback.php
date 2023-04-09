<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre">
<div ng-app="eventMagicApp" ng-controller="feedbackFormCtrl" ng-cloak>
    <div id="ep-deactivate-feedback-dialog-wrapper" class="ep-modal-view" style="display:none">
        <div class="ep-modal-overlay" style="display:none" ng-click="cancelForm()"></div>
        <div class="ep-modal-wrap ep-deactivate-feedback">
            <div class="ep-modal-titlebar">
                <div class="ep-modal-title"><?php _e('EventPrime Feedback','eventprime-event-calendar-management'); ?> </div>
                <div class="ep-modal-close" ng-click="cancelForm()">&times;</div>
            </div>
            <form id="ep-deactivate-feedback-dialog-form" method="post">
                <input type="hidden" name="action" value="ep_deactivate_feedback" />
                <div class="ep-modal-container">
                    <div class="ep-uimrow">
                        <div id="ep-deactivate-feedback-dialog-form-caption"><?php _e('If you have a moment, please share why you are deactivating EventPrime:','eventprime-event-calendar-management'); ?></div>
                        <div id="ep-deactivate-feedback-dialog-form-body">
                            <div class="ep-deactivate-feedback-dialog-input-wrapper">
                                <input id="ep-deactivate-feedback-feature_not_available" class="ep-deactivate-feedback-dialog-input" type="radio" name="ep_feedback_key" value="feature_not_available">
                                <label for="ep-deactivate-feedback-feature_not_available" class="ep-deactivate-feedback-dialog-label"><span class="ep-feedback-emoji">&#x1f61e;</span><?php _e("Doesn't have the feature I need","eventprime-event-calendar-management");?></label>
                                <div class="epinput" id="ep_reason_feature_not_available" style="display:none"><input class="ep-feedback-text" type="text" name="ep_reason_feature_not_available" placeholder="<?php _e("Please let us know the missing feature...","eventprime-event-calendar-management");?>"></div>
                            </div>
                            <div class="ep-deactivate-feedback-dialog-input-wrapper">
                                <input id="ep-deactivate-feedback-feature_not_working" class="ep-deactivate-feedback-dialog-input" type="radio" name="ep_feedback_key" value="feature_not_working" >
                                <label for="ep-deactivate-feedback-feature_not_working" class="ep-deactivate-feedback-dialog-label"><span class="ep-feedback-emoji">&#x1f615;</span><?php _e("One of the features didn't work","eventprime-event-calendar-management");?></label>
                                <div class="epinput" id="ep_reason_feature_not_working" style="display:none"><input class="ep-feedback-text" type="text" name="ep_reason_feature_not_working" placeholder="<?php _e("Please let us know the feature, like 'email notifications'","eventprime-event-calendar-management");?>"></div>
                            </div>
                            <div class="ep-deactivate-feedback-dialog-input-wrapper">
                                <input id="ep-deactivate-feedback-found_a_better_plugin" class="ep-deactivate-feedback-dialog-input" type="radio" name="ep_feedback_key" value="found_a_better_plugin" >
                                <label for="ep-deactivate-feedback-found_a_better_plugin" class="ep-deactivate-feedback-dialog-label"><span class="ep-feedback-emoji">&#x1f60a;</span><?php _e("Moved to a different plugin","eventprime-event-calendar-management");?></label>
                                <div class="epinput" id="ep_reason_found_a_better_plugin" style="display:none"><input class="ep-feedback-text" type="text" name="ep_reason_found_a_better_plugin" placeholder="<?php _e("Could you please share the plugin's name","eventprime-event-calendar-management");?>"></div>
                            </div>
                            <div class="ep-deactivate-feedback-dialog-input-wrapper">
                                <input id="ep-deactivate-feedback-plugin_broke_site" class="ep-deactivate-feedback-dialog-input" type="radio" name="ep_feedback_key" value="plugin_broke_site">
                                <label for="ep-deactivate-feedback-plugin_broke_site" class="ep-deactivate-feedback-dialog-label"><span class="ep-feedback-emoji">&#x1f621;</span><?php _e("The plugin broke my site","eventprime-event-calendar-management");?></label>
                            </div>
                            <div class="ep-deactivate-feedback-dialog-input-wrapper">
                                <input id="ep-deactivate-feedback-plugin_stopped_working" class="ep-deactivate-feedback-dialog-input" type="radio" name="ep_feedback_key" value="plugin_stopped_working">
                                <label for="ep-deactivate-feedback-plugin_stopped_working" class="ep-deactivate-feedback-dialog-label"><span class="ep-feedback-emoji">&#x1f620;</span><?php _e("The plugin suddenly stopped working","eventprime-event-calendar-management");?></label>
                            </div>
                            <div class="ep-deactivate-feedback-dialog-input-wrapper">
                                <input id="ep-deactivate-feedback-temporary_deactivation" class="ep-deactivate-feedback-dialog-input" type="radio" name="ep_feedback_key" value="temporary_deactivation">
                                <label for="ep-deactivate-feedback-temporary_deactivation" class="ep-deactivate-feedback-dialog-label"><span class="ep-feedback-emoji">&#x1f60a;</span><?php _e("It's a temporary deactivation","eventprime-event-calendar-management");?></label>
                            </div>
                            <div class="ep-deactivate-feedback-dialog-input-wrapper">
                                <input id="ep-deactivate-feedback-other" class="ep-deactivate-feedback-dialog-input" type="radio" name="ep_feedback_key" value="other">
                                <label for="ep-deactivate-feedback-other" class="ep-deactivate-feedback-dialog-label"><span class="ep-feedback-emoji">&#x1f610;</span><?php _e("Other","eventprime-event-calendar-management");?></label>
                                <div class="epinput" id="ep_reason_other"  style="display:none"><input class="ep-feedback-text" type="text" name="ep_reason_other" placeholder="<?php _e("Please share the reason","eventprime-event-calendar-management");?>"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ep-ajax-loader" style="display:none">
                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                    <span class=""><?php _e("Deactivating EventPrime...","eventprime-event-calendar-management"); ?></span>
                </div>
                <div class="ep-modal-footer">
                    <input type="button" id="ep-feedback-cancel-btn" value="<?php _e("← &nbsp;Cancel","eventprime-event-calendar-management");?>" ng-click="cancelForm()"/>
                    <input type="button" id="ep-feedback-btn" value="<?php _e("Submit & Deactivate","eventprime-event-calendar-management"); ?>" ng-click="submitForm()"/>
                   
                </div>
            </form>
        </div>  
    </div>
</div>
</div>