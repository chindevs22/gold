<?php do_action('event_magic_admin_promotion_banner'); ?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<div class="kikfyre"  ng-controller="priceManagerCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('event_price_manager')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-hidden" style="{{requestInProgress ?'display:none':'display:block'}}">
        <!-- Operations bar Starts --> 
        <div class="kf-operationsbar dbfl">
            <div class="form_errors">
                <ul>
                    <li class="emfield_error" ng-repeat="error in formErrors">
                        <span>{{error}}</span>
                    </li>
                </ul>
            </div>
            <div class="form_success">
                <ul>
                    <li class="emfield_success" ng-repeat="success in formSuccess">
                        <span>{{success}}</span>
                    </li>
                </ul>
            </div>
            <div class="kf-titlebar_dark dbfl">
                <div class="kf-title kf-title-1 difl"><?php _e('Price Manager','eventprime-event-calendar-management'); ?></div>
            </div>
            <div class="kf-nav dbfl">
                <ul>
                    <li><a ng-href="<?php echo esc_url(admin_url('admin.php?page=em_dashboard&post_id='.$post_id)); ?>"><?php _e('Back', 'eventprime-event-calendar-management'); ?></a></li>
                    <li><a ng-href="<?php echo esc_url(admin_url('admin.php?page=em_dashboard&tab=add_price_manager&post_id='.$post_id)); ?>"><?php _e('Add New', 'eventprime-event-calendar-management'); ?></a></li>
                    <li><button class="em_action_bar_button" ng-click="deleteOptions()" ng-disabled="selections.length == 0" ><?php _e('Delete', 'eventprime-event-calendar-management'); ?></button></li>
                </ul>
            </div>
        </div>
        <!--  Operations bar Ends -->
        <div class="emagic-table dbfl">
            <table class="kf-etypes-table ep-price-manager-table" id="ep-tblLocations">
                <tr>
                    <th class="table-header"></th>
                    <th class="table-header"><input type="checkbox" id="em_bulk_selector" ng-click="checkAll()" ng-model="selectedAll"  ng-checked="selections.length == data.coupons.length"/></th>
                    <th class="table-header"><?php _e('Name', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Start Date', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('End Date', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Price', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Special Price', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Capacity', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Default', 'eventprime-event-calendar-management'); ?></th>
                    <th class="table-header"><?php _e('Action', 'eventprime-event-calendar-management'); ?></th>
                </tr>

                <tr ng-repeat="price_cat in data" data-priceid="{{price_cat.id}}">
                    <td class="ep-price-block-sorting"><span class="material-icons" ng-if="data.length > 1">drag_indicator</span></td>
                    <td>
                        <input type="checkbox" ng-model="price_cat.Selected" ng-click="selectOption(price_cat.id)" ng-true-value="{{price_cat.id}}" ng-false-value="0" id="price-cat-{{price_cat.id}}" stringToNumber>
                    </td>
                    <td>{{price_cat.name}}</td>
                    <td>{{price_cat.start_date}}</td>
                    <td>{{price_cat.end_date}}</td>
                    <td>{{price_cat.price | currency}}</td>
                    <td>{{price_cat.special_price | currency}}</td>
                    <td>{{price_cat.capacity}}</td>
                    <td>{{price_cat.is_default}}</td>
                    <td>
                        <a ng-href="<?php echo admin_url('admin.php?page=em_dashboard&tab=add_price_manager&post_id='.$post_id); ?>&option_id={{price_cat.id}}"><?php _e('View/Edit', 'eventprime-event-calendar-management'); ?></a>
                    </td>
                </tr>
            </table>

            <div class="em_empty_card" ng-show="data.price_cat.length == 0">
                <?php _e('The Coupon Codes you create will appear here in tabular Format. Presently, you do not have any coupon code created.', 'eventprime-event-calendar-management'); ?>
            </div>
        </div>
        <div class="kf-pagination dbfr" ng-show="data.coupons.length != 0"> 
            <ul>
                <li dir-paginate="coupon in data.total_posts | itemsPerPage: data.pagination_limit"></li>
            </ul>
            <dir-pagination-controls on-page-change="pageChanged(newPageNumber)"></dir-pagination-controls>
        </div>
        <button type="button" id="ep-multi-price-sort" ng-click="epPriceSorting()" style="display: none;"></button>
    </div>
</div>
<script type="text/javascript">
jQuery(function () {
    jQuery("#ep-tblLocations").sortable({
        items: 'tr:not(tr:first-child)',
        cursor: 'pointer',
        axis: 'y',
        dropOnEmpty: false,
        start: function (e, ui) {
            ui.item.addClass("selected");
        },
        stop: function (e, ui) {
            ui.item.removeClass("selected");
            jQuery("#ep-multi-price-sort").trigger('click');
        }
    });
});
</script>