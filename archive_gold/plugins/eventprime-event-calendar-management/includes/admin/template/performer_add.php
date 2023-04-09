<?php do_action('event_magic_admin_promotion_banner'); 
$performer_text = em_global_settings_button_title('Performer');
$performers_text = em_global_settings_button_title('Performers');?>
<div class="kikfyre kf-container"  ng-controller="performerCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('edit')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content">
        <div class="kf-db-title">
            <?php echo $performer_text . esc_html__('(s)','eventprime-event-calendar-management'); ?>
        </div>

        <div class="form_errors">
            <ul>
                <li class="emfield_error" ng-repeat="error in formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>  
        </div>
        <!-- FORM -->
        <form name="postForm" ng-submit="savePerformer(postForm.$valid)" novalidate >
            <div class="emrow">
                <div class="emfield"> <?php echo $performer_text . ' ' . esc_html__('Type','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <ul class="emradio" >
                        <li ng-repeat="(key,value) in data.post.types">
                            <input required type="radio" name="type"  ng-model="data.post.type" value="{{value.key}}"> {{value.label}}
                        </li>
                    </ul>
                    <div class="emfield_error">
                        <span ng-show="postForm.title.$error.required && !postForm.title.$pristine"><?php esc_html_e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"></div>
            </div>
            
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Name','eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input required type="text" name="name" ng-model="data.post.name">
                    <div class="emfield_error">
                         <span ng-show="postForm.name.$error.required && !postForm.name.$pristine"><?php esc_html_e('This is a required field.','eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Person/Group name who is performing at event. Will be visible on Event page.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Role','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="text" name="role" ng-model="data.post.role">
                    <div class="emfield_error"></div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php echo $performer_text . esc_html__('\'s role. Example: Presenter, Stand-up comedian, musician, puppeteer, singer, etc.','eventprime-event-calendar-management'); ?>
                </div>
            </div>
            
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Cover Image','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input class="kf-upload" type="button" ng-click="mediaUploader(false)" value="<?php esc_html_e('Upload','eventprime-event-calendar-management'); ?>" />
                    <div class="em_cover_image em_gallery_images">
                     <img ng-src="{{data.post.feature_image}}" />
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Cover image of ' . $performer_text . '. Will be displayed on ' . $performer_text . ' page.','eventprime-event-calendar-management'); ?>
                </div>
             </div>

             <div class="emrow">
                <div class="emfield"><?php _e('Phone','eventprime-event-calendar-management'); ?></div>    
                <div class="eminput">
                    <ul class="ep-organizer-input">
                        <li ng-repeat="(i,phone) in data.post.performer_phones track by $index">
                            <input type="tel" ng-model="data.post.performer_phones[i]" name="performer_phones{{i}}">
                            <a ng-click="removePhone(phone)" class="ep-delete-organizer-input"><?php _e('Delete','eventprime-event-calendar-management'); ?></a> 
                        </li>   
                        <div ng-click="addPhone()" class="ep-add-organizer-input"><?php _e('Add New','eventprime-event-calendar-management'); ?></div>
                    </ul>
                </div>  
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e("Add the performer's phone numbers.",'eventprime-event-calendar-management'); ?>
                </div> 
            </div>

            <div class="emrow">
                <div class="emfield"><?php _e('Email','eventprime-event-calendar-management'); ?></div>
                <div class="eminput" >
                    <ul class="ep-organizer-input">
                        <li ng-repeat="(i,email) in data.post.performer_emails track by $index">
                            <input type="email" ng-model="data.post.performer_emails[i]" name="performer_emails{{i}}" ng-pattern="/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/">
                            <a ng-click="removeEmail(email)" class="ep-delete-organizer-input"><?php _e('Delete','eventprime-event-calendar-management'); ?></a>
                            <div class="emfield_error">
                                <span ng-show="postForm.performer_emails{{i}}.$error.pattern && postForm.performer_emails{{i}}.$dirty"> Invalid email address</span>
                            </div> 
                        </li>
                       <div ng-click="addEmail()"  class="ep-add-organizer-input"><?php _e('Add New','eventprime-event-calendar-management'); ?></div>
                    </ul>  
                  
                </div>
                
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e("Add the performer's email addresses.",'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php _e('Website','eventprime-event-calendar-management'); ?></div>
                <div class="eminput" >
                    <ul class="ep-organizer-input">
                        
                        <li ng-repeat="(i,website) in data.post.performer_websites track by $index">
                            
                           <input type="text" ng-model="data.post.performer_websites[i]" name="performer_websites{{i}}" ng-pattern="/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}(:[0-9]{1,5})?(\/.*)?$/">
                            <a ng-click="removeWebsites(website)" class="ep-delete-organizer-input"><?php _e('Delete','eventprime-event-calendar-management'); ?></a>  
                            <div class="emfield_error">
                                <span ng-show="postForm.performer_websites{{i}}.$error.pattern && postForm.performer_websites{{i}}.$dirty"> Incorrect website URL format</span> 
                            </div>
                        </li>
                          <div ng-click="addWebsite()" class="ep-add-organizer-input"><?php _e('Add New','eventprime-event-calendar-management'); ?></div> 
                    </ul>
                </div>            
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e("Add the performer's website URLs.",'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="emrow">
                <div class="emfield"><?php _e('Featured','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="is_featured"  ng-model="data.post.is_featured" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Check if you want to make this performer featured.','eventprime-event-calendar-management'); ?>
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
                <div class="emfield emeditor"><?php esc_html_e('Description','eventprime-event-calendar-management'); ?></div>
                <div class="eminput emeditor">
                    <?php 
                        $post_id = event_m_get_param('post_id');
                        $content = '';
                        if($post_id !== null && (int)$post_id > 0) {
                            $post= get_post($post_id);
                            if(!empty($post))
                                $content = $post->post_content;
                        }
                        wp_editor($content,'description');
                    ?>
                </div>
                <div class="emnote emeditor">
                    <?php echo $performer_text . '\'s ' . esc_html__("details.",'eventprime-event-calendar-management'); ?>
                </div>
             </div>
    
             <div class="emrow">
                <div class="emfield"><?php esc_html_e('Display on list of ' . $performers_text, 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" name="display_front" ng-model="data.post.display_front" ng-true-value="'true'" ng-false-value="'false'">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Hide/Show ' . $performer_text . ' in ' . $performers_text . ' directory.','eventprime-event-calendar-management'); ?>
                </div>
             </div>
            
            <input type="text" class="hidden" ng-model="post.feature_image_id" />
            <div class="dbfl kf-buttonarea">
            <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('/admin.php?page=em_performers') ?>">&#8592; &nbsp;<?php esc_html_e('Cancel','eventprime-event-calendar-management'); ?></a></div>
            <button type="submit" class="btn btn-primary" ng-disabled="postForm.$invalid || requestInProgress"><?php esc_html_e('Save','eventprime-event-calendar-management'); ?></button>
            </div>
            <div class="dbfl kf-required-errors" ng-show="postForm.$invalid && postForm.$dirty">
                <h3><?php esc_html_e("Looks like you missed out filling some required fields (*). You will not be able to save until all required fields are filled in. Here’s what's missing",'eventprime-event-calendar-management'); ?> - 
                <span ng-show="postForm.type.$error.required"><?php esc_html_e('Performer Type','eventprime-event-calendar-management'); ?></span>
                <span ng-show="postForm.name.$error.required"><?php esc_html_e('Name','eventprime-event-calendar-management'); ?></span>
                </h3>
            </div>
        </form>      
    </div>
</div>