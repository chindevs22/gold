<?php
$gs_service = EventM_Factory::get_service('EventM_Setting_Service');
$gs_model = $gs_service->load_model_from_db();
$frontend_page_link = get_page_link($gs_model->performers_page);
$performer_text = em_global_settings_button_title('Performers');?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre" ng-app="eventMagicApp" ng-controller="performerCtrl" ng-init="initialize('list')" ng-cloak>
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-hidden" style="{{requestInProgress ?'display:none':'display:block'}}">
        <div class="kf-operationsbar dbfl">
            <div class="kf-title difl">
                <?php $manager_navs= em_manager_navs(); ?>
                <select class="kf-dropdown" onchange="em_manager_nav_changed(this.value)">
                    <?php foreach($manager_navs as $nav): ?>
                    <option <?php echo $nav['key']=='em_performers' ? 'selected' : ''; ?> value="<?php echo $nav['key']; ?>"><?php echo $nav['label']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="difr ep-support-links"><a target="__blank" href="https://eventprime.net/contact/"><?php _e('Submit Support Ticket', 'eventprime-event-calendar-management'); ?></a></div>
            <div class="difr ep-support-links"><a target="__blank" href="<?php echo $frontend_page_link; ?>"><?php _e('Frontend', 'eventprime-event-calendar-management'); ?></a></div>
            <div class="kf-nav dbfl">
                <ul>
                    <?php if( ! empty( em_check_context_user_capabilities( array( 'create_event_performers' ) ) ) ) {?>
                        <li><a ng-href="<?php echo admin_url('/admin.php?page=em_performers&tab=add'); ?>"><?php _e('Add New','eventprime-event-calendar-management'); ?></a></li>
                    <?php } 
                    if( ! empty( em_check_context_user_capabilities( array( 'delete_event_performers', 'delete_others_event_performers' ) ) ) ) {?>
                        <li><button class="em_action_bar_button" ng-click="deletePosts()" ng-disabled="selections.length == 0" ><?php _e('Delete','eventprime-event-calendar-management'); ?></button></li>

                        <li> <input type="checkbox" ng-model="selectedAll" ng-click="checkAll()"  ng-checked="selections.length == data.posts.length"id="select_all"/><label for="select_all"><?php _e('Select All','eventprime-event-calendar-management'); ?></label></li><?php
                    }?>
                    
                    <li><a target="_blank" href="https://eventprime.net/how-add-performers-wordpress-events/"><?php _e('Performers Guide','eventprime-event-calendar-management'); ?> <span class="dashicons dashicons-book-alt"></span></a></li>
                    <?php if( ! empty( em_check_context_user_capabilities( array( 'view_event_performers' ) ) ) ) {?>
                        <li ng-if="!data.searchedKeyword"><?php _e('Sort By', 'eventprime-event-calendar-management'); ?>
                            <select class="kf-dropdown" ng-change="preparePerformerListPage(sort_option)" ng-options="sort_option.key as sort_option.label for sort_option in data.sort_options" ng-model="data.sort_option">
                            </select>
                        </li>
                        <li ng-if="data.searchedKeyword"><?php _e('Sort By', 'eventprime-event-calendar-management'); ?>
                            <select class="kf-dropdown" ng-change="preparePerformerListPageWithSearch(sort_option)" ng-options="sort_option.key as sort_option.label for sort_option in data.sort_options" ng-model="data.sort_option">
                            </select>                                 
                        </li>
                        <li class="kf-toggle difr"> 
                            <span ng-show="data.searchedKeyword"> 
                                <?php echo esc_html__('You searched for', 'eventprime-event-calendar-management');?> {{data.searchedKeyword}}
                            </span>  &nbsp;&nbsp; 
                            <input type="text" ng-model="searchKeyword" placeholder="<?php echo esc_html__('Search', 'eventprime-event-calendar-management') . ' ' . $performer_text;?>">
                            <button type="button" ng-disabled="!searchKeyword" class="btn btn-primary kf-upload" ng-click="preparePerformerListPageWithSearch()">
                                <i class="fa fa-search"></i>
                            </button>  &nbsp;&nbsp;
                            <input type="button" ng-show="data.searchedKeyword" class="btn btn-primary kf-upload" ng-click="reloadPage()" value="Reset">
                        </li><?php
                    }?>
                </ul>
            </div>
        </div>
    
        <div class="kf-cards emagic-performers dbfl">
            <div class="kf-card difl" ng-repeat="post in data.posts">
                <div ng-if="post.cover_image_url" class="kf_cover_image dbfl">
                    <img ng-show="post.cover_image_url" ng-src="{{post.cover_image_url}}" />
                </div>
                <div ng-if="!post.cover_image_url" class="kf_cover_image dbfl">
                    <img ng-src="<?php echo esc_url(plugins_url('/images/event_dummy.png', __FILE__)) ?>" />
                </div>
                <div class="kf-card-content dbfl">
                    <div class="kf-card-title dbfl kf-wrap">
                        <?php 
                        if( !empty( em_check_context_user_capabilities( array( 'delete_event_performers' ) ) ) ) {
                            if( empty( em_check_context_user_capabilities( array( 'delete_others_event_performers' ) ) ) ) {?>
                                <input type="checkbox" ng-model="post.Selected" ng-click="selectPost(post.id)" ng-true-value="{{post.id}}" ng-false-value="0" id="{{post.name}}" ng-if="data.current_user_id == post.created_by"><?php
                            } else{?>
                                <input type="checkbox" ng-model="post.Selected" ng-click="selectPost(post.id)" ng-true-value="{{post.id}}" ng-false-value="0" id="{{post.name}}"><?php
                            }
                        } elseif( !empty( em_check_context_user_capabilities( array( 'delete_others_event_performers' ) ) ) ) {?>
                            <input type="checkbox" ng-model="post.Selected" ng-click="selectPost(post.id)" ng-true-value="{{post.id}}" ng-false-value="0" id="{{post.name}}" ng-if="data.current_user_id != post.created_by"><?php
                        }?>
                        <label for="{{post.name}}">{{post.name}}</label>
                    </div>
                    <div class="dbfl">
                        <div class="kf-per-role"> 
                            {{post.role}}
                        </div>
                        <div class="kf_upcoming dbfl"> 
                            <?php _e('Event(s)', 'eventprime-event-calendar-management'); ?> <span class="kf_upcoming_count">{{post.events}}</span>
                        </div>
                    </div>
                    <div class="em_venue_name kf_performer_descp kf-wrap dbfl">
                        {{post.short_description}}  
                    </div>
                   
                    <div class="ep-performer-shortcode dbfl">[em_performer id="{{post.id}}"]</div>
                    
                    <?php 
                    if( !empty( em_check_context_user_capabilities( array( 'edit_event_performers' ) ) ) ) {
                        if( empty( em_check_context_user_capabilities( array( 'edit_others_event_performers' ) ) ) ) {?>
                            <div class="kf-card-info ep-edit-performer dbfl">
                                <a ng-href="<?php echo admin_url('/admin.php?page=em_performers&tab=add'); ?>&post_id={{post.id}}" ng-if="data.current_user_id == post.created_by">
                                    <?php _e('Edit','eventprime-event-calendar-management'); ?>
                                </a>
                            </div><?php
                        } else{?>
                            <div class="kf-card-info ep-edit-performer dbfl">
                                <a ng-href="<?php echo admin_url('/admin.php?page=em_performers&tab=add'); ?>&post_id={{post.id}}">
                                    <?php _e('Edit','eventprime-event-calendar-management'); ?>
                                </a>
                            </div><?php
                        }
                    } elseif( !empty( em_check_context_user_capabilities( array( 'edit_others_event_performers' ) ) ) ) {?>
                        <div class="kf-card-info ep-edit-performer dbfl">
                            <a ng-href="<?php echo admin_url('/admin.php?page=em_performers&tab=add'); ?>&post_id={{post.id}}" ng-if="data.current_user_id != post.created_by">
                                <?php _e('Edit','eventprime-event-calendar-management'); ?>
                            </a>
                        </div><?php
                    }?>
                </div>
            </div>
            
            <div class="em_empty_card difl ep-info-notice epnotice" ng-show="data.posts.length==0 && !data.searchedKeyword">
                <?php _e('The Performer you create will appear here as neat looking Performer Cards. Presently, you do not have any performer created.','eventprime-event-calendar-management'); ?>
            </div>
            <div class="em_empty_card difl ep-info-notice epnotice" ng-show="data.posts.length == 0 && data.searchedKeyword">
                <?php _e('Sorry! No result found related to your search.', 'eventprime-event-calendar-management'); ?>
            </div>
        </div>
        <div class="kf-pagination dbfr" ng-if="data.posts.length != 0 && !data.searchedKeyword"> 
            <ul>
                <li class="difl" dir-paginate="post in data.total_posts | itemsPerPage: data.pagination_limit"></li>
            </ul>
            <dir-pagination-controls on-page-change="pageChanged(newPageNumber)"></dir-pagination-controls>
        </div>
        <div class="kf-pagination dbfr" ng-if="data.posts.length != 0 && data.searchedKeyword"> 
            <ul>
                <li class="difl" dir-paginate="post in data.total_posts | itemsPerPage: data.pagination_limit"></li>
            </ul>
            <dir-pagination-controls on-page-change="searchPageChanged(newPageNumber)"></dir-pagination-controls>
        </div>

        <!--Premium Banner -->
        <?php 
        if(empty(ep_has_paid_ext())){
            do_action('event_magic_admin_bottom_premium_banner');
        }?>
    </div>
</div>