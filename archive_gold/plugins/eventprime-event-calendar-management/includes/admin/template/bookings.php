<?php $extensions = event_magic_instance()->extensions;?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre" ng-app="eventMagicApp" ng-controller="bookingCtrl" ng-cloak="" ng-init="initialize('list')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <!-----Operations bar Starts-->
    
    <div class="kf-operationsbar dbfl">
        <div class="kf-title difl"><?php _e('Bookings','eventprime-event-calendar-management'); ?></div>
        <div class="difr ep-support-links"><a target="__blank" href="https://eventprime.net/contact/"><?php _e('Submit Support Ticket', 'eventprime-event-calendar-management'); ?></a></div>
        <div class="kf-nav dbfl">
            <ul>
                <li>
                    <form action="admin-ajax.php?action=em_export_bookings" method="post" name="em_booking_export">
                          <a ng-click="prepare_export_link(true)" class="export_data" ><?php _e('Export All','eventprime-event-calendar-management'); ?></a>
                          <input type="hidden" name="post_query" id="em_post_query" />
                          <input type="hidden" name="selected_bookings" id="em_selected_bookings">
                    </form>
                </li>
                <li><button ng-disabled="selections.length == 0"  ng-click="cancelPosts()"><?php _e('Cancel','eventprime-event-calendar-management'); ?></button></li>
                <li><button ng-disabled="selections.length == 0"  ng-click="deletePosts()"><?php _e('Delete','eventprime-event-calendar-management'); ?></button></li>
                <li><button ng-disabled="selections.length == 0"  ng-click="cancelPosts('delete')"><?php _e('Cancel & Delete','eventprime-event-calendar-management'); ?></button></li>
                <li><a target="_blank" href="https://eventprime.net/how-manage-attendees-wordpress-events/"><?php _e('Attendee Manager Guide','eventprime-event-calendar-management'); ?> <span class="dashicons dashicons-book-alt"></span></a></li>
                <!-- attendees booking section -->
                <?php
                if(in_array('attendees_booking', $extensions)){ 
                    do_action( 'event_magic_add_attendees_booking_button' );
                }
                ?>
                <li class="kf-toggle difr">
                    <?php _e('Displaying For','eventprime-event-calendar-management'); ?>
                    <select class="kf-dropdown" id="em_form_dropdown" ng-change="filter()" name="event" ng-model="data.event" 
                            ng-options="event.id as event.title for event in data.events">
                    </select>
                </li>
            </ul>
        </div>
    </div>
    <!--  Operations bar Ends----->

    <!-------Content area Starts----->
    <div class="emagic-table dbfl">
        <div class="kf-sidebar difl" ng-disabled="paged>1">
            <div class="kf-filter dbfl">
                <?php _e('Time','eventprime-event-calendar-management'); ?>
                <div class="filter-row dbfl" ng-click="filter()"><input type="radio"  name="filter_between" value="all" ng-click="filter()" ng-model="data.filter_between"><?php _e('All','eventprime-event-calendar-management'); ?></div>
                <div class="filter-row dbfl" ><input type="radio" ng-click="filter()" ng-model="data.filter_between"  name="filter_between" value="today"><?php _e('Today','eventprime-event-calendar-management'); ?></div>
                <div class="filter-row dbfl" ng-click="filter()"><input type="radio" ng-model="data.filter_between" name="filter_between" value="week"  ><?php _e('This Week','eventprime-event-calendar-management'); ?></div>
                <div class="filter-row dbfl" ng-click="filter()"><input type="radio" ng-model="data.filter_between" name="filter_between" value="month" ><?php _e('This Month','eventprime-event-calendar-management'); ?></div>
                <div class="filter-row dbfl" ng-click="filter()"><input type="radio" ng-model="data.filter_between" name="filter_between" value="year"  ><?php _e('This Year','eventprime-event-calendar-management'); ?></div>
                <div class="filter-row dbfl"><input type="radio" ng-model="data.filter_between" name="filter_between" value="range"  ><?php _e('Specific Period','eventprime-event-calendar-management'); ?></div>
                <div id="date_box" ng-show="showDates">
                    <div class="filter-row dbfl"><span><?php _e('From','eventprime-event-calendar-management'); ?></span><input type="text"   id="em_date_from" name="date_from" ng-model="data.date_from"></div>
                    <div class="filter-row dbfl"><span><?php _e('To','eventprime-event-calendar-management'); ?></span> <input type="text"   id="em_date_to" name="date_to" ng-model="data.date_to"></div>
                    <div class="filter-row dbfl"><input class="kf-upload" type="button" ng-click="filter()" value="<?php _e('Search','eventprime-event-calendar-management'); ?>"></div>
                </div>
                <div class="filter-row dbfl">
                    <div class="filter-row-label"><?php _e('Booking Status','eventprime-event-calendar-management'); ?></div>
                    <select class="dbfl" id="filter_status" ng-model="data.filter_status" ng-change="prepareListPage()" name="filter_status" ng-options="status.key as status.label for status in data.status">
                    </select>
                </div>
                <div class="filter-row dbfl">
                    <div class="filter-row-label"><?php _e('Show','eventprime-event-calendar-management'); ?></div>
                    <select class="dbfl" id="show_no_of_booking" ng-model="data.show_no_of_booking" ng-change="prepareListPage()" name="show_no_of_booking">
                        <option value="10" ng-selected="data.show_no_of_booking == 10">10</option>
                        <option value="20" ng-selected="data.show_no_of_booking == 20">20</option>
                        <option value="30" ng-selected="data.show_no_of_booking == 30">30</option>
                        <option value="40" ng-selected="data.show_no_of_booking == 40">40</option>
                        <option value="50" ng-selected="data.show_no_of_booking == 50">50</option>
                        <option value="All" ng-selected="data.show_no_of_booking == 'All'">All</option>
                    </select>
                </div>
                
                <div class="filter-row">        
                    <input type="button" value="Reset" onclick="location.reload()" class="btn btn-primary kf-upload" />         
               </div>
                
            </div>


        </div>

        <!--*******Side Bar Ends*********-->
        <table class="kf-table ep-attendees-table difl ">
            <tr>
                <th><input type="checkbox" id="em_bulk_selector" ng-click="markAll()" ng-model="selectedAll"  ng-checked="selections.length == data.posts.length"/></th>
                <th><?php _e('ID','eventprime-event-calendar-management'); ?></th>
                <th><?php _e('Username','eventprime-event-calendar-management'); ?></th>
                <th><?php _e('User Email','eventprime-event-calendar-management'); ?></th>
                <th><?php _e('Event','eventprime-event-calendar-management'); ?></th>
                <th><?php _e('Event Date','eventprime-event-calendar-management'); ?></th>
                <th><?php _e('No. Of Attendees','eventprime-event-calendar-management'); ?></th>
                <th><?php _e('Booking Status','eventprime-event-calendar-management'); ?></th>
                <th><?php _e('Payment Gateway','eventprime-event-calendar-management'); ?></th>
                <th><?php _e('Actions','eventprime-event-calendar-management'); ?></th>
            </tr>

            <tr ng-repeat="post in data.posts">
                <td><input ng-click="updateSelection(post.id)" class="em_card_check" type="checkbox" ng-model="post.selected" ng-true-value="{{post.id}}" ng-false-value="0"  id="{{post.id}}"/></td>
                <td class="ep-booking-id">
                    <label for="{{post.id}}">{{post.id}}</label>
                    <?php 
                    if (in_array('recurring_events', $extensions)): ?>
                        <span ng-show="post.order_info.parent_booking_id" title='This Attendee Booking will remain active for all recurring events of "{{post.parent_event_name}}"'>
                            <span class="ep-recurring-info">Recurring</span>
                        </span>
                    <?php endif; ?>
                </td>
                <td>{{post.user.display_name}}</td>
                <td>{{post.user.email}}</td>
                <td>{{post.event_name}}</td>
                <td>{{post.event_date}}</td>
                <td>{{post.no_tickets}}</td>
                <td class="em-attendee-booking-status">{{post.status}}</td>
                <td class="em-attendee-booking-status">{{post.payment_gateway}}</td>
                <td><a href="<?php echo admin_url('/admin.php?page=em_booking_add'); ?>&post_id={{post.id}}"><?php _e('View','eventprime-event-calendar-management'); ?></a></td>
            </tr>
        </table>
        </form>

        <div class="em_empty_card" ng-hide="data.posts.length>0">
            <?php _e('No Attendee Matches your Criteria','eventprime-event-calendar-management'); ?>
        </div>

    </div>

    <div class="em_pagination" ng-show="data.posts.length !== 0"> 
       <div class="kf-pagination dbfr"> 
            <ul>
                <li dir-paginate="post in data.total_bookings | itemsPerPage: data.pagination_limit" current-page="data.paged"></li>
            </ul>
            <dir-pagination-controls on-page-change="pageChanged(newPageNumber)"></dir-pagination-controls>
       </div>
    </div>

    <!--Premium Banner -->
    <?php 
    if(empty(ep_has_paid_ext())){
        do_action('event_magic_admin_bottom_premium_banner');
    }?>
</div>
<script>
jQuery(document).ready(function () {
    jQuery("#em_date_from").datepicker({
        onSelect: function (date) {

            var selectedDate = new Date(date);
            var msecsInADay = 86400000;
            var endDate = new Date(selectedDate.getTime() + msecsInADay);

            jQuery("#em_date_to").datepicker("option", "minDate", endDate);

        }
    });

    jQuery("#em_date_to").datepicker({
        onSelect: function (date) {
            var selectedDate = new Date(date);
            var msecsInADay = 86400000;
            var endDate = new Date(selectedDate.getTime() + msecsInADay);
            jQuery("#em_date_from").datepicker("option", "maxDate", endDate);
        }
    }); 
});   
</script>