<?php 
$extensions = event_magic_instance()->extensions;?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="" ng-app="eventMagicApp" ng-controller="eventUserCapabilitiesCtrl" ng-cloak="" ng-init="initialize('list')">
    <form name="userCapForm" ng-submit="saveUserCaps(userCapForm.$valid)" novalidate >
        <div class="ep-promo-nav-tabs">
            <h2 class="nav-tab-wrapper" id="ep-promo-tabs">
                <a href="javascript:void(0)" id="tab1" class="nav-tab nav-tab-active">
                    <?php _e('Events', 'eventprime-event-calendar-management'); ?>
                </a>
            
                <a href="javascript:void(0)" id="tab2" class="nav-tab">
                    <?php _e('Performers/Organizers', 'eventprime-event-calendar-management'); ?>
                </a>
                <sup style="color:orange;">(Beta)</sup>
            </h2>
        </div>

        <div class="kikfyre kf-container" ng-show="!requestInProgress">
            <div class="ep-cap-nav-container" id="tab1C">
                <table class="em-custom-user-cap-table">
                    <tr>
                        <td colspan="2">
                            <table class="em-user-caps-table" style="width:auto;" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <th>Event</th>
                                        <th>Event Type</th>
                                        <th>Event Site/Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="(role, role_cap) in data.user_cap_data">
                                        <td class="cap">
                                            <div class="em-custom-user-cap-role">
                                                {{role}}
                                            </div>
                                        </td>
                                        <td class="em-user-caps-td">
                                            <div ng-repeat="(key, cap_data) in role_cap.event">
                                                <input type="checkbox" ng-model="data.em_user_capabilities[role][key]" id="{{role+'_'+key}}" ng-true-value="true" />&nbsp;
                                                <label class="em-custom-user-cap-lbl" for="{{role+'_'+key}}">{{key}}</label>&nbsp;
                                                <a href="#" title="{{cap_data.cap_help}}">?</a><br />
                                            </div>
                                        </td>
                                        <td class="em-user-caps-td">
                                            <div ng-repeat="(key, cap_data) in role_cap.event_types">
                                                <input type="checkbox" ng-model="data.em_user_capabilities[role][key]" id="{{role+'_'+key}}" ng-true-value="true" />&nbsp;
                                                <label class="em-custom-user-cap-lbl" for="{{role+'_'+key}}">{{key}}</label>&nbsp;
                                                <a href="#" title="{{cap_data.cap_help}}">?</a><br />
                                            </div>
                                        </td>
                                        <td class="em-user-caps-td">
                                            <div ng-repeat="(key, cap_data) in role_cap.event_sites">
                                                <input type="checkbox" ng-model="data.em_user_capabilities[role][key]" id="{{role+'_'+key}}" ng-true-value="true" />&nbsp;
                                                <label class="em-custom-user-cap-lbl" for="{{role+'_'+key}}">{{key}}</label>&nbsp;
                                                <a href="#" title="{{cap_data.cap_help}}">?</a><br />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="ep-cap-nav-container" id="tab2C">
                <table class="em-custom-user-cap-table">
                    <tr>
                        <td colspan="2">
                            <table class="em-user-caps-table" style="width:auto;" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <th>Performers</th>
                                        <th>Organizers</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="(role, role_cap) in data.user_cap_data">
                                        <td class="cap">
                                            <div class="em-custom-user-cap-role">
                                                {{role}}
                                            </div>
                                        </td>
                                        <td class="em-user-caps-td">
                                            <div ng-repeat="(key, cap_data) in role_cap.event_performers">
                                                <input type="checkbox" ng-model="data.em_user_capabilities[role][key]" id="{{role+'_'+key}}" ng-true-value="true" />&nbsp;
                                                <label class="em-custom-user-cap-lbl" for="{{role+'_'+key}}">{{key}}</label>&nbsp;
                                                <a href="#" title="{{cap_data.cap_help}}">?</a><br />
                                            </div>
                                        </td>
                                        <td class="em-user-caps-td">
                                            <div ng-repeat="(key, cap_data) in role_cap.event_organizers">
                                                <input type="checkbox" ng-model="data.em_user_capabilities[role][key]" id="{{role+'_'+key}}" ng-true-value="true" />&nbsp;
                                                <label class="em-custom-user-cap-lbl" for="{{role+'_'+key}}">{{key}}</label>&nbsp;
                                                <a href="#" title="{{cap_data.cap_help}}">?</a><br />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" ng-href="<?php echo admin_url('/admin.php?page=em_global_settings'); ?>"><?php _e('← &nbsp;Go to Settings Area', 'eventprime-event-calendar-management'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="userCapForm.$invalid || requestInProgress">
                    <?php _e('Save','eventprime-event-calendar-management'); ?>
                </button>
                <ul ng-if="response.message">
                    <li class="emfield_success">
                        <span>{{response.message}}</span>
                    </li>
                </ul>
            </div>
        </div>
    </form>
</div>
<script>
    (function($){ 
        $(document).ready(function() {    
            $('#ep-promo-tabs a:first').addClass('nav-tab-active');
            $('#ep-promo-tabs a:not(:first)').addClass('nav-tab-inactive');
            $('.ep-cap-nav-container').hide();
            $('.ep-cap-nav-container:first').show();

            $('#ep-promo-tabs a').click(function(){
                var t = $(this).attr('id');
                if($(this).hasClass('nav-tab-inactive')){ 
                    $('#ep-promo-tabs a').addClass('nav-tab-inactive');           
                    $(this).removeClass('nav-tab-inactive');
                    $(this).addClass('nav-tab-active');

                    $('.ep-cap-nav-container').hide();
                    $('#'+ t + 'C').fadeIn('slow');
                }
            });
        });   
    })(jQuery);
</script>