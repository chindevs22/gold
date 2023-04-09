<?php 
$organizers_text = em_global_settings_button_title('Organizers');
$organizer_text = em_global_settings_button_title('Organizer');
?>
<div class="kikfyre kf-container"  ng-controller="eventOrganizerCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('edit')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content" ng-hide="requestInProgress">
        <div class="kf-db-title">
            <?php echo esc_html__('Add/Edit Event ', 'eventprime-event-calendar-management') . $organizer_text; ?>
        </div>
        <div class="form_errors">
            <ul>
                <li class="emfield_error" ng-repeat="error in formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>  
        </div>
        <!-- FORM -->
        <form name="postForm" ng-submit="saveEventOrganizer(postForm.$valid)" novalidate >
            <div class="emrow">
                <div class="emfield"><?php _e('Name','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required type="text" name="organizer_name"  ng-model="data.post.name">
                    <div class="emfield_error">
                        <span ng-show="postForm.name.$error.required && !postForm.name.$pristine"><?php _e('This is  required field.','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php echo esc_html__('Name the Event ', 'eventprime-event-calendar-management') . $organizer_text .'.'; ?>
                </div>
            </div>

             <div class="emrow">
            <div class="emfield"><?php _e('Phone','eventprime-event-calendar-management'); ?></div>    
            <div class="eminput">
                <ul class="ep-organizer-input">
                    <li ng-repeat="(i,phone) in data.post.organizer_phones track by $index">
                        <input type="tel" ng-model="data.post.organizer_phones[i]" name="organizer_phones{{i}}">
                        <a ng-click="removePhone(phone)" class="ep-delete-organizer-input"><?php _e('Delete','eventprime-event-calendar-management'); ?></a>
                    </li>   
                    <div ng-click="addPhone()" class="ep-add-organizer-input"><?php _e('Add New','eventprime-event-calendar-management'); ?></div>
                </ul>
            </div>  
            <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                <?php 
                esc_html_e("Add the ",'eventprime-event-calendar-management');  
                echo $organizer_text;
                esc_html_e("'s phone numbers.",'eventprime-event-calendar-management'); ?>
            </div> 
                
            </div>
             <div class="emrow">
                <div class="emfield"><?php _e('Email','eventprime-event-calendar-management'); ?></div>
                <div class="eminput" >
                    <ul class="ep-organizer-input">
                        <li ng-repeat="(i,email) in data.post.organizer_emails track by $index">
                            <input type="email" ng-model="data.post.organizer_emails[i]" name="organizer_emails{{i}}" ng-pattern="/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/">
                            <a ng-click="removeEmail(email)" class="ep-delete-organizer-input"><?php _e('Delete','eventprime-event-calendar-management'); ?></a>
                            <div class="emfield_error">
                                <span ng-show="postForm.organizer_emails{{i}}.$error.pattern && postForm.organizer_emails{{i}}.$dirty"> <?php esc_html_e('Invalid email address','eventprime-event-calendar-management'); ?></span>
                            </div>
                        </li>
                       <div ng-click="addEmail()"  class="ep-add-organizer-input"><?php _e('Add New','eventprime-event-calendar-management'); ?></div>
                    </ul>  
                  
                </div>
                
                <div class="emnote">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php 
                    esc_html_e("Add the ",'eventprime-event-calendar-management');
                    echo $organizer_text;
                    esc_html_e("'s email addresses.",'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php _e('Website','eventprime-event-calendar-management'); ?></div>
                <div class="eminput" >
                    <ul class="ep-organizer-input">
                        <li ng-repeat="(i,website) in data.post.organizer_websites track by $index">
                           <input type="text" ng-model="data.post.organizer_websites[i]" name="organizer_websites{{i}}" ng-pattern="/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/">
                            <a ng-click="removeWebsites(website)" class="ep-delete-organizer-input"><?php _e('Delete','eventprime-event-calendar-management'); ?></a>
                            <div class="emfield_error">
                                <span ng-show="postForm.organizer_websites{{i}}.$error.pattern && postForm.organizer_websites{{i}}.$dirty"> <?php esc_html_e('Incorrect website URL format','eventprime-event-calendar-management'); ?></span> 
                            </div>
                        </li>
                          <div ng-click="addWebsite()" class="ep-add-organizer-input"><?php _e('Add New','eventprime-event-calendar-management'); ?></div> 
                    </ul>
                </div>            
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php 
                    esc_html_e("Add the ",'eventprime-event-calendar-management');
                    echo $organizer_text;
                    esc_html_e("'s website URLs.",'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php _e('Image','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input class="kf-upload" type="button" ng-click="mediaUploader(false)" value="<?php _e('Upload','eventprime-event-calendar-management'); ?>" />
                    <div class="em_cover_image em_gallery_images">
                     <img ng-src="{{data.post.image}}" />
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php 
                    esc_html_e('Image or icon of the Event ','eventprime-event-calendar-management');
                    echo $organizer_text.'.';
                    ?>
                </div>
                <input type="text" class="hidden" ng-model="post.image_id" />
            </div>

            <div class="emrow">
                <div class="emfield"><?php _e('Featured','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="is_featured"  ng-model="data.post.is_featured" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Check if you want to make this organizer featured.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow"  ng-repeat="social_link in data.post.social_fields">
                <div class="emfield"><?php _e('{{social_link}}', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="text"  ng-model="data.post.social_links[social_link]">         
                    <div class="emfield_error"> </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Enter {{social_link | capitalize}} link.', 'eventprime-event-calendar-management'); ?>
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
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('admin.php?page=em_event_organizers'); ?>">&#8592; &nbsp;<?php _e('Cancel','eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid || requestInProgress"><?php _e('Save','eventprime-event-calendar-management'); ?></button>
                <span class="kf-error" ng-show="postForm.$invalid && postForm.$dirty "><?php _e('Please fill all required fields.','eventprime-event-calendar-management'); ?></span>
            </div>
        </form>
    </div>
</div>