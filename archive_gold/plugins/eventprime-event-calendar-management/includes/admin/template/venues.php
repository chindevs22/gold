<?php
$gs_service = EventM_Factory::get_service('EventM_Setting_Service');
$gs_model = $gs_service->load_model_from_db();
$frontend_page_link = get_page_link($gs_model->venues_page);
?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre" ng-app="eventMagicApp" ng-controller="venueCtrl" ng-init="initialize('list')" ng-cloak>
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>  
    <div class="kf-hidden" style="{{requestInProgress ?'display:none':'display:block'}}">
        <div class="kf-operationsbar dbfl">
            <div class="kf-title difl">
                <?php $manager_navs = em_manager_navs(); ?>
                <select class="kf-dropdown test" onchange="em_manager_nav_changed(this.value)">
                    <?php foreach ($manager_navs as $nav): ?>
                        <option <?php echo $nav['key'] == 'em_venues' ? 'selected' : ''; ?> value="<?php echo $nav['key']; ?>"><?php echo $nav['label']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="difr ep-support-links"><a target="__blank" href="https://eventprime.net/contact/"><?php _e('Submit Support Ticket', 'eventprime-event-calendar-management'); ?></a></div>
            <div class="difr ep-support-links"><a target="__blank" href="<?php echo $frontend_page_link; ?>"><?php _e('Frontend', 'eventprime-event-calendar-management'); ?></a></div>
            <div class="kf-icons difl">
            </div>
            <div class="kf-nav dbfl">
                <ul class="dbfl">
                    <?php if( ! empty( em_check_context_user_capabilities( array( 'create_event_sites' ) ) ) ) {?>
                        <li><a href="?page=em_venue_add"><?php _e('Add New', 'eventprime-event-calendar-management'); ?></a></li>
                        <?php
                    } 
                    if( ! empty( em_check_context_user_capabilities( array( 'delete_event_sites', 'delete_private_ep_event_sites' ) ) ) ) {?>
                        <li><button class="em_action_bar_button" ng-click="deleteTerms()" ng-disabled="selections.length == 0" ><?php _e('Delete', 'eventprime-event-calendar-management'); ?></button></li>
                        <li> <input type="checkbox" ng-model="selectedAll" ng-click="checkAllVENUES()" ng-checked="selections.length == data.terms.length" id="select_all"/>
                            <label for="select_all"><?php _e('Select All', 'eventprime-event-calendar-management'); ?></label>
                        </li><?php
                    }?>
                    <li><a target="_blank" href="https://eventprime.net/how-showcase-event-location-details-event-sites/"><?php _e('Event Sites Guide','eventprime-event-calendar-management'); ?> <span class="dashicons dashicons-book-alt"></span></a></li>
                    <li ng-if="!data.searchedKeyword"><?php _e('Sort By', 'eventprime-event-calendar-management'); ?>
                        <select class="kf-dropdown" ng-change="prepareVenueListPage(sort_option)" ng-options="sort_option.key as sort_option.label for sort_option in data.sort_options" ng-model="data.sort_option">
                        </select>                                 
                    </li>
                    <?php if( ! empty( em_check_context_user_capabilities( array( 'view_event_sites' ) ) ) ) {?>
                        <li ng-if="data.searchedKeyword"><?php _e('Sort By', 'eventprime-event-calendar-management'); ?>
                            <select class="kf-dropdown" ng-change="prepareVenueListPageWithSearch(sort_option)" ng-options="sort_option.key as sort_option.label for sort_option in data.sort_options" ng-model="data.sort_option">
                            </select>                                 
                        </li>
                        <li class="kf-toggle difr"> <span ng-show="data.searchedKeyword">You searched for {{data.searchedKeyword}}</span>  &nbsp;&nbsp; <input type="text" ng-model="searchKeyword" placeholder="Search Event Sites"><button type="button" ng-disabled="!searchKeyword" class="btn btn-primary kf-upload" ng-click="prepareVenueListPageWithSearch()"><i class="fa fa-search"></i></button>  &nbsp;&nbsp;<input type="button" ng-show="data.searchedKeyword" class="btn btn-primary kf-upload" ng-click="reloadPage()" value="Reset"></li><?php
                    }?>
                </ul>
            </div>

        </div>

        <div class="kf-cards emagic-venue-cards dbfl">

            <div class="kf-card difl" ng-repeat="term in data.terms">
                <div ng-if="term.feature_image" class="kf_cover_image dbfl"><img ng-show="term.feature_image" ng-src="{{term.feature_image}}" /></div>
                <div ng-if="!term.feature_image" class="kf_cover_image dbfl"><img ng-src="<?php echo esc_url(plugins_url('/images/event_dummy.png', __FILE__)) ?>" /></div>
                <div class="kf-card-content dbfl">
                    <div class="kf-card-title kf-wrap dbfl" title="{{term.name}}">
                        <?php 
                        if( !empty( em_check_context_user_capabilities( array( 'delete_event_sites' ) ) ) ) {
                            if( empty( em_check_context_user_capabilities( array( 'delete_others_event_sites' ) ) ) ) {?>
                                <input type="checkbox" ng-model="term.Selected" ng-click="selectTerm(term.id)" ng-true-value="{{term.id}}" ng-false-value="0" ng-if="data.current_user_id == term.created_by" class="em-event-site-terms" id="em-evs-term-{{term.id}}">
                                <?php
                            } else{?>
                                <input type="checkbox" ng-model="term.Selected" ng-click="selectTerm(term.id)" ng-true-value="{{term.id}}" ng-false-value="0" class="em-event-site-terms" id="em-evs-term-{{term.id}}">
                                <?php
                            }
                        } elseif( empty( em_check_context_user_capabilities( array( 'delete_others_event_sites' ) ) ) ) {?>
                            <input type="checkbox" ng-model="term.Selected" ng-click="selectTerm(term.id)" ng-true-value="{{term.id}}" ng-false-value="0" ng-if="data.current_user_id != term.created_by" class="em-event-site-terms" id="em-evs-term-{{term.id}}">
                            <?php
                        }?>

                        <label for="{{term.name}}">{{term.name}}</label>
                    </div>

                    <div class="kf_upcoming dbfl">  
                        <?php _e('Event(s)', 'eventprime-event-calendar-management'); ?> <span class="kf_upcoming_count">{{term.event_count}}</span>
                    </div>
                    <div class="kf_venue_seats kf-wrap dbfl" ng-show="term.type == 'standings'">  
                        <span class="kf_upcoming_count">{{term.standing_capacity}} <?php _e('Standing Capacity', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                    <?php
                    $em = event_magic_instance();
                    if (in_array('seating', $em->extensions)):
                        ?>
                        <div class="kf_venue_seats kf-wrap dbfl" ng-show="term.type=='seats'">  
                            <span class="kf_upcoming_count" ng-show="term.seating_capacity != null">{{term.seating_capacity}} <?php _e('Seats', 'eventprime-event-calendar-management'); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="em_venue_address kf-wrap dbfl" title="{{term.address}}">  
                        {{term.address}}
                    </div>

                    <div class="ep-eventsite-shortcode dbfl">[em_event_site id="{{term.id}}"]</div>

                    <?php 
                    if( !empty( em_check_context_user_capabilities( array( 'edit_event_sites' ) ) ) ) {
                        if( empty( em_check_context_user_capabilities( array( 'edit_others_event_sites' ) ) ) ) {?>
                            <div class="ep-edit-eventsite  dbfl" ng-show="data.current_user_id == term.created_by">
                                <a ng-href="<?php echo admin_url('/admin.php?page=em_venue_add'); ?>&term_id={{term.id}}"><?php _e('Edit Site', 'eventprime-event-calendar-management'); ?></a>
                            </div><?php
                        } else{?>
                            <div class="ep-edit-eventsite  dbfl">
                                <a ng-href="<?php echo admin_url('/admin.php?page=em_venue_add'); ?>&term_id={{term.id}}"><?php _e('Edit Site', 'eventprime-event-calendar-management'); ?></a>
                            </div><?php
                        }
                    } elseif( empty( em_check_context_user_capabilities( array( 'edit_others_event_sites' ) ) ) ) {?>
                        <div class="ep-edit-eventsite  dbfl" ng-show="data.current_user_id != term.created_by">
                            <a ng-href="<?php echo admin_url('/admin.php?page=em_venue_add'); ?>&term_id={{term.id}}"><?php _e('Edit Site', 'eventprime-event-calendar-management'); ?></a>
                        </div><?php
                    }?>
                </div>
            </div>

            <div class="em_empty_card difl ep-info-notice epnotice" ng-show="data.terms.length == 0 && !data.searchedKeyword">
                <?php _e('The Event Site you create will appear here as neat looking Event Site Cards. Presently, you do not have any Event Site created.', 'eventprime-event-calendar-management'); ?>
            </div>
            <div class="em_empty_card difl ep-info-notice epnotice" ng-show="data.terms.length == 0 && data.searchedKeyword">
                <?php _e('Sorry! No result found related to your search.', 'eventprime-event-calendar-management'); ?>
            </div> 
        </div>
        <div class="kf-pagination dbfr" ng-if="data.terms.length != 0 && !data.searchedKeyword"> 
            <ul class="empagination">
                <li class="difl" dir-paginate="term in data.total_count | itemsPerPage: data.pagination_limit"></li>
            </ul>
            <dir-pagination-controls on-page-change="pageChanged(newPageNumber)"></dir-pagination-controls>
        </div>
        <div class="kf-pagination dbfr" ng-if="data.terms.length != 0 && data.searchedKeyword"> 
            <ul>
                <li dir-paginate="term in data.total_count | itemsPerPage: data.pagination_limit"></li>
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