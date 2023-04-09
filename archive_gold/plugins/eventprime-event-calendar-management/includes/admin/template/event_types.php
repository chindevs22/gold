<?php
$gs_service = EventM_Factory::get_service('EventM_Setting_Service');
$gs_model = $gs_service->load_model_from_db();
$frontend_page_link = get_page_link($gs_model->event_types);
?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre" ng-app="eventMagicApp" ng-controller="eventTypeCtrl" ng-cloak="" ng-init="initialize('list')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div> 
    <div class="kf-hidden" style="{{requestInProgress ?'display:none':'display:block'}}">
        <!-- Operations bar Starts --> 
        <div class="kf-operationsbar dbfl">
            <div class="kf-title difl">
            <?php $manager_navs= em_manager_navs(); ?>
            <select class="kf-dropdown" onchange="em_manager_nav_changed(this.value)">
                <?php foreach($manager_navs as $nav): ?>
                <option <?php echo $nav['key']=='em_event_types' ? 'selected' : ''; ?> value="<?php echo $nav['key']; ?>"><?php echo $nav['label']; ?></option>
                <?php endforeach; ?>
            </select>
            </div>
            <div class="difr ep-support-links"><a target="__blank" href="https://eventprime.net/contact/"><?php _e('Submit Support Ticket', 'eventprime-event-calendar-management'); ?></a></div>
            <div class="difr ep-support-links"><a target="__blank" href="<?php echo $frontend_page_link; ?>"><?php _e('Frontend', 'eventprime-event-calendar-management'); ?></a></div>
            <div class="kf-nav dbfl">
                <ul>
                    <?php if( ! empty( em_check_context_user_capabilities( array( 'create_event_types' ) ) ) ) {?>
                        <li><a ng-href="<?php echo admin_url('admin.php?page=em_event_type_add'); ?>"><?php _e('Add New', 'eventprime-event-calendar-management'); ?></a></li>
                    <?php } 
                    if( ! empty( em_check_context_user_capabilities( array( 'delete_event_types', 'edit_others_event_types' ) ) ) ) {?>
                        <li><button class="em_action_bar_button" ng-click="deleteTerms()" ng-disabled="selections.length == 0" ><?php _e('Delete', 'eventprime-event-calendar-management'); ?></button></li><?php
                    }?>
                    <li><a target="_blank" href="https://eventprime.net/events-organized-event-types/"><?php _e('Event Types Guide','eventprime-event-calendar-management'); ?> <span class="dashicons dashicons-book-alt"></span></a></li>
                    <?php if( ! empty( em_check_context_user_capabilities( array( 'view_event_types' ) ) ) ) {?>
                        <li ng-if="!data.searchedKeyword"><?php _e('Sort By', 'eventprime-event-calendar-management'); ?>
                            <select class="kf-dropdown" ng-change="prepareListPage(sort_option)" ng-options="sort_option.key as sort_option.label for sort_option in data.sort_options" ng-model="data.sort_option">
                            </select>                                 
                        </li>
                        <li ng-if="data.searchedKeyword"><?php _e('Sort By', 'eventprime-event-calendar-management'); ?>
                            <select class="kf-dropdown" ng-change="prepareListPageWithSearch(sort_option)" ng-options="sort_option.key as sort_option.label for sort_option in data.sort_options" ng-model="data.sort_option">
                            </select>                                 
                        </li>
                        <li class="kf-toggle difr"> <span ng-show="data.searchedKeyword">You searched for {{data.searchedKeyword}}</span>  &nbsp;&nbsp; <input type="text" ng-model="searchKeyword" placeholder="Search Event Types"><button type="button" ng-disabled="!searchKeyword" class="btn btn-primary kf-upload" ng-click="prepareListPageWithSearch()"><i class="fa fa-search"></i></button>  &nbsp;&nbsp;<input type="button" ng-show="data.searchedKeyword" class="btn btn-primary kf-upload" ng-click="reloadPage()" value="Reset"></li><?php
                    }?>
                </ul>
            </div>

        </div>
        <!--  Operations bar Ends -->

        <!-- Content area Starts -->
        <div class="emagic-table dbfl">
            <table class="kf-etypes-table"><!-- remove class for default 80% view -->
                <tr>
                    <th class="table-header"><input type="checkbox" id="em_bulk_selector" ng-click="checkAll()" ng-model="selectedAll"  ng-checked="selections.length == data.terms.length"/></th>
                    <th class="table-header"><?php _e('ID', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Name', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Background Color', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Text Color', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Age Group', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Action', 'eventprime-event-calendar-management'); ?></th>
                </tr>

                <tr ng-repeat="term in data.terms">
                    <td>
                        <?php if( !empty( em_check_context_user_capabilities( array( 'delete_event_types' ) ) ) ) {
                            if( empty( em_check_context_user_capabilities( array( 'delete_others_event_types' ) ) ) ) {?>
                                <input type="checkbox" ng-model="term.Selected" ng-click="selectTerm(term.id)" ng-true-value="{{term.id}}" ng-false-value="0" ng-if="data.current_user_id == term.created_by" class="em-event-type-terms" id="em-evt-term-{{term.id}}"><?php
                            } else{?>
                                <input type="checkbox" ng-model="term.Selected" ng-click="selectTerm(term.id)" ng-true-value="{{term.id}}" ng-false-value="0" class="em-event-type-terms" id="em-evt-term-{{term.id}}"><?php
                            }
                        } elseif( !empty( em_check_context_user_capabilities( array( 'delete_others_event_types' ) ) ) ) {?>
                            <input type="checkbox" ng-model="term.Selected" ng-click="selectTerm(term.id)" ng-true-value="{{term.id}}" ng-false-value="0" ng-if="data.current_user_id != term.created_by" class="em-event-type-terms" id="em-evt-term-{{term.id}}"><?php
                        }?>
                    </td>
                    <td>{{term.id}}</td>
                    <td>{{term.name}}</td>
                    <td><span class="color-block" ng-style="bgColor(term)"></span></td>
                    <td><span class="color-block" ng-style="textColor(term)"></span></td>
                    <td>
                        <span ng-if="term.age_group=='custom_group'">{{term.custom_group.replace('-',' to ')}}</span>
                        <span ng-if="term.age_group=='parental_guidance'"><?php _e('All ages but parental guidance','eventprime-event-calendar-management'); ?></span>
                        <span ng-if="term.age_group=='all'"><?php _e('All','eventprime-event-calendar-management'); ?></span>
                    </td>
                    <td>
                        <?php if( !empty( em_check_context_user_capabilities( array( 'edit_event_types' ) ) ) ) {
                            if( empty( em_check_context_user_capabilities( array( 'edit_others_event_types' ) ) ) ) {?>
                                <a ng-href="<?php echo admin_url('admin.php?page=em_event_type_add'); ?>&term_id={{term.id}}" ng-show="data.current_user_id == term.created_by">
                                    <?php _e('View/Edit', 'eventprime-event-calendar-management'); ?>
                                </a><?php
                            } else{?>
                                <a ng-href="<?php echo admin_url('admin.php?page=em_event_type_add'); ?>&term_id={{term.id}}">
                                    <?php _e('View/Edit', 'eventprime-event-calendar-management'); ?>
                                </a><?php
                            }
                        } elseif( empty( em_check_context_user_capabilities( array( 'edit_others_event_types' ) ) ) ) {?>
                            <a ng-href="<?php echo admin_url('admin.php?page=em_event_type_add'); ?>&term_id={{term.id}}" ng-show="data.current_user_id != term.created_by">
                                <?php _e('View/Edit', 'eventprime-event-calendar-management'); ?>
                            </a><?php
                        }?>
                    </td>
                </tr>
            </table>
            </form>

        </div>
        
        <div class="em_empty_card difl ep-info-notice epnotice" ng-show="data.terms.length == 0 && !data.searchedKeyword">
            <?php _e('The Event Types you create will appear here in tabular Format . Presently, you do not have any event type created.', 'eventprime-event-calendar-management'); ?>
        </div>
        <div class="em_empty_card difl ep-info-notice epnotice" ng-show="data.terms.length == 0 && data.searchedKeyword">
            <?php _e('Sorry! No result found related to your search.', 'eventprime-event-calendar-management'); ?>
        </div>  

        <div class="kf-pagination dbfr" ng-if="data.terms.length != 0 && !data.searchedKeyword"> 
            <ul>
                <li dir-paginate="term in data.total_count | itemsPerPage: data.pagination_limit"></li>
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

