<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre" ng-app="eventMagicApp" ng-controller="bulkEmailsCtrl" ng-cloak="" ng-init="initialize()">
    <div class="bef-container">
        <div class="kf_progress_screen" ng-show="requestInProgress"></div>
        <div class="kf-db-content">
            <div class="kf-title">
                <?php esc_html_e('Email Attendees', 'eventprime-event-calendar-management'); ?>
            </div>

            <div class="form_errors">
                <ul>
                    <li class="emfield_error" ng-repeat="error in  formErrors">
                        <span>{{error}}</span>
                    </li>
                </ul>  
            </div>
            <!-- FORM -->
            <form name="bulkEmailForm" class="em-bulkEmailForm" ng-submit="sendEmails(bulkEmailForm.$valid)" novalidate >
                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('To (Email Address)', 'eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <textarea name="email_address" ng-model="email_address" rows="5" required></textarea>
                        <div class="em-bulkemail-btn dbfl"> 
                            <a href="javascript:void(0)" ng-click="checkEventDisplay()">
                                <?php esc_html_e( 'Auto-populate attendee email addresses from an event', 'eventprime-event-calendar-management' ); ?>
                            </a>
                        </div>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Multiple email addresses supported. You can auto-populate this field with email addresses of attendees from an event by using the link below.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('CC Email Address', 'eventprime-event-calendar-management'); ?></div>
                    <div class="eminput">
                        <input  type="text" name="cc_email_address" ng-model="cc_email_address" ng-pattern="/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/">
                        <div class="emfield_error">
                            <span ng-show="bulkEmailForm.cc_email_address.$error.pattern && bulkEmailForm.cc_email_address.$dirty"><?php esc_html_e('Enter valid email address.', 'eventprime-event-calendar-management'); ?></span>
                        </div>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Add CC email address.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow" ng-show="showEventDrop">
                    <div class="emfield">
                        <?php esc_html_e('Select Event', 'eventprime-event-calendar-management'); ?>
                    </div>
                    <div class="eminput em-bulk-email-input">
                        <div id="em-bulk-email-dropdown" class="em-bulk-dropdown-content">
                            <input type="text" placeholder="<?php esc_html_e('Search Event', 'eventprime-event-calendar-management'); ?>" id="em-bulk-input" onkeyup="filterFunction()">
                            <div class="em-bulk-email-event-list">
                                <a ng-class="{em_event_selected:event.id == selectedBulkEvent}" ng-click="fetchAttendees(event.id)" ng-repeat="event in data.events">{{event.title}}</a>
                            </div>
                        </div>
                        <div class="emfield_error">
                            <span>{{attendee_no_found_error}}</span>
                        </div>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Select the event from which you wish to fetch attendee email addresses.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow">
                    <div class="emfield"><?php esc_html_e('Subject', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                    <div class="eminput">
                        <input  type="text" name="email_subject" ng-model="email_subject" required>
                        <div class="emfield_error">
                            <span ng-show="bulkEmailForm.email_subject.$error.required && !bulkEmailForm.email_subject.$pristine"><?php esc_html_e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                        </div>
                    </div>
                    <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <?php esc_html_e('Subject of your email.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="emrow kf-bg-light">
                    <div class="emfield emeditor"><?php esc_html_e('Content', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                    <div class="eminput emeditor">
                        <?php
                        $content = '';
                        wp_editor($content, 'content'); ?>
                        <div class="emfield_error">
                            <span ng-show="bulkEmailForm.content.$error.required && !bulkEmailForm.content.$pristine"><?php esc_html_e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                        </div>
                    </div>
                    <div class="emnote emeditor">
                        <?php esc_html_e('Body of your email. Rich text supported.', 'eventprime-event-calendar-management'); ?>
                    </div>
                </div>

                <div class="dbfl kf-buttonarea">
                    <button type="submit" class="btn btn-primary" ng-disabled="bulkEmailForm.$invalid || requestInProgress"><?php esc_html_e('Send', 'eventprime-event-calendar-management'); ?></button>
                    <span class="kf-error" ng-show="bulkEmailForm.$invalid && bulkEmailForm.$dirty"><?php esc_html_e('Please fill all required fields.', 'eventprime-event-calendar-management'); ?></span>
                </div>

                <div class="dbfl kf-required-errors" ng-show="bulkEmailForm.$invalid && bulkEmailForm.$dirty">
                    <h3><?php esc_html_e("Looks like you missed out filling some required fields (*). You will not be able to save until all required fields are filled in. Here’s what's missing", 'eventprime-event-calendar-management'); ?> -
                        <span ng-show="bulkEmailForm.email_address.$error.required"><?php esc_html_e('Email Address', 'eventprime-event-calendar-management'); ?></span>
                        <span ng-show="bulkEmailForm.email_subject.$error.required"><?php esc_html_e('Subject', 'eventprime-event-calendar-management'); ?></span>
                        <span ng-show="bulkEmailForm.content.$error.required"><?php esc_html_e('Content', 'eventprime-event-calendar-management'); ?></span>
                    </h3>
                </div>
            </form>
        </div>
    </div>
    <!--Premium Banner -->
    <?php 
    if(empty(ep_has_paid_ext())){
        do_action('event_magic_admin_bottom_premium_banner');
    }?>
</div>

<script>
function filterFunction() {
    var input, filter, ul, li, a, i;
    input = document.getElementById("em-bulk-input");
    filter = input.value.toUpperCase();
    div = document.getElementById("em-bulk-email-dropdown");
    a = div.getElementsByTagName("a");
    for (i = 0; i < a.length; i++) {
        txtValue = a[i].textContent || a[i].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            a[i].style.display = "";
        } else {
            a[i].style.display = "none";
        }
    }
}
</script>