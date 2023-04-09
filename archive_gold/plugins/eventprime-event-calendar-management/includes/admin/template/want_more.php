<?php $extensions= event_magic_instance()->extensions; ?>
<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="ep-promo-nav-tabs">
    <h2 class="nav-tab-wrapper" id="ep-promo-tabs">
        <?php if(!in_array('seating',$extensions)){ ?>
            <a href="javascript:void(0)" id="tab1" class="nav-tab nav-tab-active"><?php _e('Ticket Manager', 'eventprime-event-calendar-management'); ?></a>
            <a href="javascript:void(0)" id="tab2" class="nav-tab"><?php _e('Live Seating', 'eventprime-event-calendar-management'); ?></a>
        <?php }
        if(!in_array('analytics',$extensions)){ ?>
            <a href="javascript:void(0)" id="tab3" class="nav-tab"><?php _e('Analytics', 'eventprime-event-calendar-management'); ?></a>
        <?php } 
        if(!in_array('coupons',$extensions)){?>
            <a href="javascript:void(0)" id="tab4" class="nav-tab"><?php _e('Coupon Codes', 'eventprime-event-calendar-management'); ?></a>
        <?php } ?>
    </h2>
</div>
<?php if(!in_array('seating',$extensions)){ ?>
    <div class="ep-promo-nav-container" id="tab1C">   
        <a href="admin.php?page=em_extensions" target="_blank" class="ep-promo-banner-wrap"> 
            <div class="ep-upgrade-banner">        
                <div class="ep-upgrade-banner-title"><?php _e('This is a preview of pages added to EventPrime when you upgrade to Premium Bundle.', 'eventprime-event-calendar-management'); ?><span><?php _e('More Info', 'eventprime-event-calendar-management'); ?></span></div>
                <div class="ep-upgrade-banner-box"><img src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-premium-img.png' ?>">
                </div>
            </div>

        </a>

        <div class="kikfyre kf-container">

            <form name="postForm" ng-submit="saveEventTicket(postForm.$valid)" novalidate class="em_ticket_form">

                <div class="em_ticket_template">
                    <div class="kf-ticket-wrapper" style="background: rgb(226, 198, 153);"> 
                        <div class="kf-event-details-wrap">
                            <div class="kf-font-color1 kf-event-title dbfl" style="color: rgb(134, 92, 22);"><?php echo _e('Event Name','eventprime-event-seating'); ?></div>
                            <div class="kf-logo-details dbfl">

                                <div class="event-details difl">
                                    <div class="dbfl">
                                        <div class="kf-ticket-row dbfl">
                                            <div class="kf-font-color1 kf-event-title"></div>
                                            <p class="kf-font-color1 kf-font1" style="color: rgb(134, 92, 22);"><?php _e('21st December, 2017 4:30 PM-7:00 PM', 'eventprime-event-seating'); ?></p>
                                        </div>
                                        <div class="kf-spacer dbfl"></div>
                                        <div class="kf-ticket-row dbfl">
                                            <p class="kf-font-color1 kf-font1" style="color: rgb(134, 92, 22);"><?php _e('EVENT SITE NAME', 'eventprime-event-seating'); ?></p>
                                            <p class="kf-font-color1 kf-font2" style="color: rgb(134, 92, 22);"><?php _e('Address Line 1, Address Line 2, City  ZipCode', 'eventprime-event-seating'); ?></p>
                                        </div>
                                        <div class="kf-ticket-row dbfl">
                                            <p class="kf-font-color1 kf-font1" style="color: rgb(134, 92, 22);"><?php _e('BOOKING COORDINATOR', 'eventprime-event-seating'); ?></p>
                                        </div>
                                        <div class="kf-ticket-row dbfl">
                                            <p class="kf-font-color1 kf-font1" style="color: rgb(134, 92, 22);"><?php _e('Age group: 18 years and above', 'eventprime-event-seating'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="kf-ticket-blank">&nbsp;</div>
                            </div>
                            <div class="kf-note-price-wrap dbfl">
                                <div class="kf-note-price">
                                    <div class="kf-special-note kf-font-color1" style="color: rgb(134, 92, 22);">
                                        <?php _e('Special Instructions: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sagittis eget
                                        ex sit amet tempor. Maecenas mi nunc, pellentesque quis eleifend eget, fermentum vel nulla.', 'eventprime-event-seating'); ?>
                                    </div>
                                    <div class="kf-ticket-price">
                                        <span class="kf-font-color1 kf-font1 kf-price-tag dbfl" style="color: rgb(134, 92, 22);"><?php _e('Price', 'eventprime-event-seating'); ?></span>
                                        <span class="kf-price dbfl kf-font-color1"  style="color: rgb(134, 92, 22);"><?php _e('$10', 'eventprime-event-seating'); ?><sup class="kf-font-color1">.00</sup></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Seat Number -->
                        <div class="kf-seat-wrap">
                            <div class="dbfl">
                                <p class="kf-font-color1 kf-seat-tag" style="color: rgb(134, 92, 22);"><?php _e('SEAT NO.', 'eventprime-event-seating'); ?></p>
                                <div class="kf-font-color1 kf-seat-no" style="color: rgb(134, 92, 22);"><?php _e('A-21', 'eventprime-event-seating'); ?></div>
                                <p class=" kf-font-color1 kf-seat-id" style="color: rgb(134, 92, 22);"><?php _e('ID # 1003459234', 'eventprime-event-seating'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="emrow">
                        <div class="emfield"><?php _e('Name', 'eventprime-event-seating'); ?><sup>*</sup></div>
                        <div class="eminput">
                            <input placeholder="Event Name"   type="text" name="name"  disabled >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Unique template name for identification.', 'eventprime-event-seating'); ?>
                        </div>
                    </div>

                    <div class="emrow">
                        <div class="emfield"><?php _e('Font', 'eventprime-event-seating'); ?></div>
                        <div class="eminput">
                            <select name="font1"  disabled >
                              <option value="string:Helvetica" label="Helvetica">Helvetica</option>
                            </select>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Font to be used in ticket template.', 'eventprime-event-seating'); ?>
                        </div>
                    </div>
                    <!-- -->
                    <div class="emrow">
                        <div class="emfield"><?php _e('Font Color', 'eventprime-event-seating'); ?></div>
                        <div class="eminput">
                            <input id="" placeholder="865C16" type="text" name="font_color"  disabled  >
                            <div class="emfield_error">
                            </div>
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e("Ticket font's color. Will be visible in PDF format.", 'eventprime-event-seating'); ?>
                        </div>
                    </div>
                    <!-- -->
                    <div class="emrow">
                        <div class="emfield"><?php _e('Background Color', 'eventprime-event-seating'); ?></div>
                        <div class="eminput">
                            <input id="" placeholder="E2C699"  type="text" name="background_color"  disabled >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Ticket background color. Will be visible in PDF format.', 'eventprime-event-seating'); ?>
                        </div>
                    </div>

                    <div class="emrow">
                        <div class="emfield"><?php _e('Border Color', 'eventprime-event-seating'); ?></div>
                        <div class="eminput">
                            <input placeholder="C8A366"  type="text" name="border_color" disabled >
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Ticket border color. Will be visible in PDF format.', 'eventprime-event-seating'); ?>
                        </div>
                    </div>

                    <div class="emrow">
                        <div class="emfield"><?php _e('Logo','eventprime-event-seating'); ?></div>
                        <div class="eminput">
                            <input type="button" class="kf-upload" value="<?php _e('Upload', 'eventprime-event-seating'); ?>"  disabled  />
                        </div>
                        <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                            <?php _e('Logo for the Event or Organizer. This will be visible on ticket printouts.', 'eventprime-event-seating'); ?>
                        </div>
                    </div>
                </div>
            </form>  
        </div>
    </div>

    <div class="ep-promo-nav-container" id="tab2C">
        <div class="ep-promo-banner-wrap">
            <div class="ep-upgrade-banner">        
                <div class="ep-upgrade-banner-title">This is a preview of pages added to EventPrime when you upgrade to Premium Bundle.<span><a href="admin.php?page=em_extensions" target="_blank">More Info</a></span></div>
                <div class="ep-upgrade-banner-box"><img src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-premium-img.png' ?>">
                </div>
            </div>

        </div>   
        
        <div class="kikfyre kf-container">
            <form class="ng-pristine ng-valid" name="postForm" novalidate="">
                <div>
                    <div class="">
                        <div class="emrow em_seat_table kf-bg-light">
                            <table class="em_venue_seating" style="width: 885px;">
                                <tbody>
                                    <tr id="row0" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">A</div>
                                        </td>
                                        <td id="ui0-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">0</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-0</div>
                                        </td>
                                        <td id="ui0-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">1</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-1</div>
                                        </td>
                                        <td id="ui0-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">2</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-2</div>
                                        </td>
                                        <td id="ui0-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">3</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-3</div>
                                        </td>
                                        <td id="ui0-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">4</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-4</div>
                                        </td>
                                        <td id="ui0-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">5</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-5</div>
                                        </td>
                                        <td id="ui0-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">6</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-6</div>
                                        </td>
                                        <td id="ui0-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">7</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-7</div>
                                        </td>
                                        <td id="ui0-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">8</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-8</div>
                                        </td>
                                        <td id="ui0-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">9</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-9</div>
                                        </td>
                                        <td id="ui0-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">10</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-10</div>
                                        </td>
                                        <td id="ui0-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">11</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-11</div>
                                        </td>
                                        <td id="ui0-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">12</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-12</div>
                                        </td>
                                        <td id="ui0-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">13</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-13</div>
                                        </td>
                                        <td id="ui0-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">14</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-14</div>
                                        </td>
                                        <td id="ui0-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">15</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-15</div>
                                        </td>
                                        <td id="ui0-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">16</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-16</div>
                                        </td>
                                        <td id="ui0-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">17</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-17</div>
                                        </td>
                                        <td id="ui0-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">18</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-18</div>
                                        </td>
                                        <td id="ui0-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">19</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-19</div>
                                        </td>
                                        <td id="ui0-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">20</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-20</div>
                                        </td>
                                        <td id="ui0-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">21</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-21</div>
                                        </td>
                                        <td id="ui0-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="em_seat_col_number ng-binding ng-scope">22</div>
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">0-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row1" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">B</div>
                                        </td>
                                        <td id="ui1-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-0</div>
                                        </td>
                                        <td id="ui1-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-1</div>
                                        </td>
                                        <td id="ui1-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-2</div>
                                        </td>
                                        <td id="ui1-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-3</div>
                                        </td>
                                        <td id="ui1-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-4</div>
                                        </td>
                                        <td id="ui1-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-5</div>
                                        </td>
                                        <td id="ui1-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-6</div>
                                        </td>
                                        <td id="ui1-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-7</div>
                                        </td>
                                        <td id="ui1-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-8</div>
                                        </td>
                                        <td id="ui1-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-9</div>
                                        </td>
                                        <td id="ui1-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-10</div>
                                        </td>
                                        <td id="ui1-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-11</div>
                                        </td>
                                        <td id="ui1-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-12</div>
                                        </td>
                                        <td id="ui1-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-13</div>
                                        </td>
                                        <td id="ui1-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-14</div>
                                        </td>
                                        <td id="ui1-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-15</div>
                                        </td>
                                        <td id="ui1-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-16</div>
                                        </td>
                                        <td id="ui1-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-17</div>
                                        </td>
                                        <td id="ui1-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-18</div>
                                        </td>
                                        <td id="ui1-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-19</div>
                                        </td>
                                        <td id="ui1-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-20</div>
                                        </td>
                                        <td id="ui1-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-21</div>
                                        </td>
                                        <td id="ui1-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">1-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row2" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">C</div>
                                        </td>
                                        <td id="ui2-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-0</div>
                                        </td>
                                        <td id="ui2-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-1</div>
                                        </td>
                                        <td id="ui2-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-2</div>
                                        </td>
                                        <td id="ui2-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-3</div>
                                        </td>
                                        <td id="ui2-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-4</div>
                                        </td>
                                        <td id="ui2-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-5</div>
                                        </td>
                                        <td id="ui2-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-6</div>
                                        </td>
                                        <td id="ui2-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-7</div>
                                        </td>
                                        <td id="ui2-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-8</div>
                                        </td>
                                        <td id="ui2-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-9</div>
                                        </td>
                                        <td id="ui2-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-10</div>
                                        </td>
                                        <td id="ui2-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-11</div>
                                        </td>
                                        <td id="ui2-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-12</div>
                                        </td>
                                        <td id="ui2-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-13</div>
                                        </td>
                                        <td id="ui2-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-14</div>
                                        </td>
                                        <td id="ui2-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-15</div>
                                        </td>
                                        <td id="ui2-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-16</div>
                                        </td>
                                        <td id="ui2-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-17</div>
                                        </td>
                                        <td id="ui2-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-18</div>
                                        </td>
                                        <td id="ui2-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-19</div>
                                        </td>
                                        <td id="ui2-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-20</div>
                                        </td>
                                        <td id="ui2-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-21</div>
                                        </td>
                                        <td id="ui2-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">2-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row3" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">D</div>
                                        </td>
                                        <td id="ui3-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-0</div>
                                        </td>
                                        <td id="ui3-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-1</div>
                                        </td>
                                        <td id="ui3-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-2</div>
                                        </td>
                                        <td id="ui3-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-3</div>
                                        </td>
                                        <td id="ui3-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-4</div>
                                        </td>
                                        <td id="ui3-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-5</div>
                                        </td>
                                        <td id="ui3-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-6</div>
                                        </td>
                                        <td id="ui3-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-7</div>
                                        </td>
                                        <td id="ui3-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-8</div>
                                        </td>
                                        <td id="ui3-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-9</div>
                                        </td>
                                        <td id="ui3-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-10</div>
                                        </td>
                                        <td id="ui3-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-11</div>
                                        </td>
                                        <td id="ui3-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-12</div>
                                        </td>
                                        <td id="ui3-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-13</div>
                                        </td>
                                        <td id="ui3-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-14</div>
                                        </td>
                                        <td id="ui3-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-15</div>
                                        </td>
                                        <td id="ui3-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-16</div>
                                        </td>
                                        <td id="ui3-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-17</div>
                                        </td>
                                        <td id="ui3-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-18</div>
                                        </td>
                                        <td id="ui3-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-19</div>
                                        </td>
                                        <td id="ui3-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-20</div>
                                        </td>
                                        <td id="ui3-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-21</div>
                                        </td>
                                        <td id="ui3-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">3-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row4" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">E</div>
                                        </td>
                                        <td id="ui4-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-0</div>
                                        </td>
                                        <td id="ui4-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-1</div>
                                        </td>
                                        <td id="ui4-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-2</div>
                                        </td>
                                        <td id="ui4-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-3</div>
                                        </td>
                                        <td id="ui4-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-4</div>
                                        </td>
                                        <td id="ui4-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-5</div>
                                        </td>
                                        <td id="ui4-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-6</div>
                                        </td>
                                        <td id="ui4-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-7</div>
                                        </td>
                                        <td id="ui4-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-8</div>
                                        </td>
                                        <td id="ui4-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-9</div>
                                        </td>
                                        <td id="ui4-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-10</div>
                                        </td>
                                        <td id="ui4-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-11</div>
                                        </td>
                                        <td id="ui4-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-12</div>
                                        </td>
                                        <td id="ui4-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-13</div>
                                        </td>
                                        <td id="ui4-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-14</div>
                                        </td>
                                        <td id="ui4-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-15</div>
                                        </td>
                                        <td id="ui4-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-16</div>
                                        </td>
                                        <td id="ui4-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-17</div>
                                        </td>
                                        <td id="ui4-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-18</div>
                                        </td>
                                        <td id="ui4-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-19</div>
                                        </td>
                                        <td id="ui4-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-20</div>
                                        </td>
                                        <td id="ui4-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-21</div>
                                        </td>
                                        <td id="ui4-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">4-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row5" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">F</div>
                                        </td>
                                        <td id="ui5-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-0</div>
                                        </td>
                                        <td id="ui5-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-1</div>
                                        </td>
                                        <td id="ui5-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-2</div>
                                        </td>
                                        <td id="ui5-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-3</div>
                                        </td>
                                        <td id="ui5-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-4</div>
                                        </td>
                                        <td id="ui5-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-5</div>
                                        </td>
                                        <td id="ui5-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-6</div>
                                        </td>
                                        <td id="ui5-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-7</div>
                                        </td>
                                        <td id="ui5-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-8</div>
                                        </td>
                                        <td id="ui5-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-9</div>
                                        </td>
                                        <td id="ui5-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-10</div>
                                        </td>
                                        <td id="ui5-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-11</div>
                                        </td>
                                        <td id="ui5-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-12</div>
                                        </td>
                                        <td id="ui5-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-13</div>
                                        </td>
                                        <td id="ui5-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-14</div>
                                        </td>
                                        <td id="ui5-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-15</div>
                                        </td>
                                        <td id="ui5-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-16</div>
                                        </td>
                                        <td id="ui5-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-17</div>
                                        </td>
                                        <td id="ui5-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-18</div>
                                        </td>
                                        <td id="ui5-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-19</div>
                                        </td>
                                        <td id="ui5-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-20</div>
                                        </td>
                                        <td id="ui5-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-21</div>
                                        </td>
                                        <td id="ui5-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">5-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row6" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">G</div>
                                        </td>
                                        <td id="ui6-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-0</div>
                                        </td>
                                        <td id="ui6-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-1</div>
                                        </td>
                                        <td id="ui6-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-2</div>
                                        </td>
                                        <td id="ui6-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-3</div>
                                        </td>
                                        <td id="ui6-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-4</div>
                                        </td>
                                        <td id="ui6-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-5</div>
                                        </td>
                                        <td id="ui6-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-6</div>
                                        </td>
                                        <td id="ui6-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-7</div>
                                        </td>
                                        <td id="ui6-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-8</div>
                                        </td>
                                        <td id="ui6-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-9</div>
                                        </td>
                                        <td id="ui6-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-10</div>
                                        </td>
                                        <td id="ui6-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-11</div>
                                        </td>
                                        <td id="ui6-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-12</div>
                                        </td>
                                        <td id="ui6-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-13</div>
                                        </td>
                                        <td id="ui6-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-14</div>
                                        </td>
                                        <td id="ui6-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-15</div>
                                        </td>
                                        <td id="ui6-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-16</div>
                                        </td>
                                        <td id="ui6-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-17</div>
                                        </td>
                                        <td id="ui6-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-18</div>
                                        </td>
                                        <td id="ui6-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-19</div>
                                        </td>
                                        <td id="ui6-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-20</div>
                                        </td>
                                        <td id="ui6-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-21</div>
                                        </td>
                                        <td id="ui6-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">6-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row7" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">H</div>
                                        </td>
                                        <td id="ui7-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-0</div>
                                        </td>
                                        <td id="ui7-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-1</div>
                                        </td>
                                        <td id="ui7-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-2</div>
                                        </td>
                                        <td id="ui7-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-3</div>
                                        </td>
                                        <td id="ui7-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-4</div>
                                        </td>
                                        <td id="ui7-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-5</div>
                                        </td>
                                        <td id="ui7-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-6</div>
                                        </td>
                                        <td id="ui7-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-7</div>
                                        </td>
                                        <td id="ui7-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-8</div>
                                        </td>
                                        <td id="ui7-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-9</div>
                                        </td>
                                        <td id="ui7-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-10</div>
                                        </td>
                                        <td id="ui7-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-11</div>
                                        </td>
                                        <td id="ui7-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-12</div>
                                        </td>
                                        <td id="ui7-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-13</div>
                                        </td>
                                        <td id="ui7-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-14</div>
                                        </td>
                                        <td id="ui7-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-15</div>
                                        </td>
                                        <td id="ui7-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-16</div>
                                        </td>
                                        <td id="ui7-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-17</div>
                                        </td>
                                        <td id="ui7-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-18</div>
                                        </td>
                                        <td id="ui7-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-19</div>
                                        </td>
                                        <td id="ui7-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-20</div>
                                        </td>
                                        <td id="ui7-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-21</div>
                                        </td>
                                        <td id="ui7-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">7-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row8" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">I</div>
                                        </td>
                                        <td id="ui8-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-0</div>
                                        </td>
                                        <td id="ui8-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-1</div>
                                        </td>
                                        <td id="ui8-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-2</div>
                                        </td>
                                        <td id="ui8-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-3</div>
                                        </td>
                                        <td id="ui8-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-4</div>
                                        </td>
                                        <td id="ui8-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-5</div>
                                        </td>
                                        <td id="ui8-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-6</div>
                                        </td>
                                        <td id="ui8-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-7</div>
                                        </td>
                                        <td id="ui8-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-8</div>
                                        </td>
                                        <td id="ui8-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-9</div>
                                        </td>
                                        <td id="ui8-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-10</div>
                                        </td>
                                        <td id="ui8-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-11</div>
                                        </td>
                                        <td id="ui8-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-12</div>
                                        </td>
                                        <td id="ui8-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-13</div>
                                        </td>
                                        <td id="ui8-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-14</div>
                                        </td>
                                        <td id="ui8-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-15</div>
                                        </td>
                                        <td id="ui8-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-16</div>
                                        </td>
                                        <td id="ui8-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-17</div>
                                        </td>
                                        <td id="ui8-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-18</div>
                                        </td>
                                        <td id="ui8-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-19</div>
                                        </td>
                                        <td id="ui8-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-20</div>
                                        </td>
                                        <td id="ui8-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-21</div>
                                        </td>
                                        <td id="ui8-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">8-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row9" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">J</div>
                                        </td>
                                        <td id="ui9-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-0</div>
                                        </td>
                                        <td id="ui9-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-1</div>
                                        </td>
                                        <td id="ui9-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-2</div>
                                        </td>
                                        <td id="ui9-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-3</div>
                                        </td>
                                        <td id="ui9-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-4</div>
                                        </td>
                                        <td id="ui9-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-5</div>
                                        </td>
                                        <td id="ui9-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-6</div>
                                        </td>
                                        <td id="ui9-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-7</div>
                                        </td>
                                        <td id="ui9-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-8</div>
                                        </td>
                                        <td id="ui9-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-9</div>
                                        </td>
                                        <td id="ui9-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-10</div>
                                        </td>
                                        <td id="ui9-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-11</div>
                                        </td>
                                        <td id="ui9-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-12</div>
                                        </td>
                                        <td id="ui9-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-13</div>
                                        </td>
                                        <td id="ui9-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-14</div>
                                        </td>
                                        <td id="ui9-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-15</div>
                                        </td>
                                        <td id="ui9-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-16</div>
                                        </td>
                                        <td id="ui9-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-17</div>
                                        </td>
                                        <td id="ui9-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-18</div>
                                        </td>
                                        <td id="ui9-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-19</div>
                                        </td>
                                        <td id="ui9-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-20</div>
                                        </td>
                                        <td id="ui9-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-21</div>
                                        </td>
                                        <td id="ui9-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">9-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row10" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">K</div>
                                        </td>
                                        <td id="ui10-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-0</div>
                                        </td>
                                        <td id="ui10-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-1</div>
                                        </td>
                                        <td id="ui10-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-2</div>
                                        </td>
                                        <td id="ui10-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-3</div>
                                        </td>
                                        <td id="ui10-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-4</div>
                                        </td>
                                        <td id="ui10-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-5</div>
                                        </td>
                                        <td id="ui10-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-6</div>
                                        </td>
                                        <td id="ui10-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-7</div>
                                        </td>
                                        <td id="ui10-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-8</div>
                                        </td>
                                        <td id="ui10-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-9</div>
                                        </td>
                                        <td id="ui10-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-10</div>
                                        </td>
                                        <td id="ui10-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-11</div>
                                        </td>
                                        <td id="ui10-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-12</div>
                                        </td>
                                        <td id="ui10-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-13</div>
                                        </td>
                                        <td id="ui10-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-14</div>
                                        </td>
                                        <td id="ui10-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-15</div>
                                        </td>
                                        <td id="ui10-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-16</div>
                                        </td>
                                        <td id="ui10-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-17</div>
                                        </td>
                                        <td id="ui10-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-18</div>
                                        </td>
                                        <td id="ui10-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-19</div>
                                        </td>
                                        <td id="ui10-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-20</div>
                                        </td>
                                        <td id="ui10-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-21</div>
                                        </td>
                                        <td id="ui10-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">10-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row11" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">L</div>
                                        </td>
                                        <td id="ui11-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-0</div>
                                        </td>
                                        <td id="ui11-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-1</div>
                                        </td>
                                        <td id="ui11-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-2</div>
                                        </td>
                                        <td id="ui11-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-3</div>
                                        </td>
                                        <td id="ui11-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-4</div>
                                        </td>
                                        <td id="ui11-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-5</div>
                                        </td>
                                        <td id="ui11-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-6</div>
                                        </td>
                                        <td id="ui11-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-7</div>
                                        </td>
                                        <td id="ui11-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-8</div>
                                        </td>
                                        <td id="ui11-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-9</div>
                                        </td>
                                        <td id="ui11-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-10</div>
                                        </td>
                                        <td id="ui11-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-11</div>
                                        </td>
                                        <td id="ui11-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-12</div>
                                        </td>
                                        <td id="ui11-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-13</div>
                                        </td>
                                        <td id="ui11-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-14</div>
                                        </td>
                                        <td id="ui11-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-15</div>
                                        </td>
                                        <td id="ui11-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-16</div>
                                        </td>
                                        <td id="ui11-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-17</div>
                                        </td>
                                        <td id="ui11-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-18</div>
                                        </td>
                                        <td id="ui11-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-19</div>
                                        </td>
                                        <td id="ui11-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-20</div>
                                        </td>
                                        <td id="ui11-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-21</div>
                                        </td>
                                        <td id="ui11-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">11-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row12" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">M</div>
                                        </td>
                                        <td id="ui12-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-0</div>
                                        </td>
                                        <td id="ui12-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-1</div>
                                        </td>
                                        <td id="ui12-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-2</div>
                                        </td>
                                        <td id="ui12-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-3</div>
                                        </td>
                                        <td id="ui12-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-4</div>
                                        </td>
                                        <td id="ui12-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-5</div>
                                        </td>
                                        <td id="ui12-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-6</div>
                                        </td>
                                        <td id="ui12-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-7</div>
                                        </td>
                                        <td id="ui12-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-8</div>
                                        </td>
                                        <td id="ui12-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-9</div>
                                        </td>
                                        <td id="ui12-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-10</div>
                                        </td>
                                        <td id="ui12-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-11</div>
                                        </td>
                                        <td id="ui12-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-12</div>
                                        </td>
                                        <td id="ui12-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-13</div>
                                        </td>
                                        <td id="ui12-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-14</div>
                                        </td>
                                        <td id="ui12-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-15</div>
                                        </td>
                                        <td id="ui12-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-16</div>
                                        </td>
                                        <td id="ui12-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-17</div>
                                        </td>
                                        <td id="ui12-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-18</div>
                                        </td>
                                        <td id="ui12-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-19</div>
                                        </td>
                                        <td id="ui12-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-20</div>
                                        </td>
                                        <td id="ui12-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-21</div>
                                        </td>
                                        <td id="ui12-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">12-22</div>
                                        </td>
                                    </tr>
                                    <tr id="row13" class="row isles_row_spacer ng-scope" style="margin-top: 0px;">
                                        <td class="row_selection_bar">
                                            <div class="em_seat_row_number ng-binding">N</div>
                                        </td>
                                        <td id="ui13-0" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">1</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-0</div>
                                        </td>
                                        <td id="ui13-1" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">2</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-1</div>
                                        </td>
                                        <td id="ui13-2" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">3</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-2</div>
                                        </td>
                                        <td id="ui13-3" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">4</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-3</div>
                                        </td>
                                        <td id="ui13-4" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">5</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-4</div>
                                        </td>
                                        <td id="ui13-5" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">6</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-5</div>
                                        </td>
                                        <td id="ui13-6" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">7</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-6</div>
                                        </td>
                                        <td id="ui13-7" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">8</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-7</div>
                                        </td>
                                        <td id="ui13-8" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">9</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-8</div>
                                        </td>
                                        <td id="ui13-9" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">10</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-9</div>
                                        </td>
                                        <td id="ui13-10" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">11</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-10</div>
                                        </td>
                                        <td id="ui13-11" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">12</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-11</div>
                                        </td>
                                        <td id="ui13-12" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">13</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-12</div>
                                        </td>
                                        <td id="ui13-13" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">14</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-13</div>
                                        </td>
                                        <td id="ui13-14" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">15</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-14</div>
                                        </td>
                                        <td id="ui13-15" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">16</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-15</div>
                                        </td>
                                        <td id="ui13-16" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">17</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-16</div>
                                        </td>
                                        <td id="ui13-17" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">18</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-17</div>
                                        </td>
                                        <td id="ui13-18" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">19</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-18</div>
                                        </td>
                                        <td id="ui13-19" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">20</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-19</div>
                                        </td>
                                        <td id="ui13-20" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">21</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-20</div>
                                        </td>
                                        <td id="ui13-21" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">22</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-21</div>
                                        </td>
                                        <td id="ui13-22" class="seat isles_col_spacer ng-scope general" style="margin-left: 0px;">
                                            <div class="seat_avail seat_avail_number seat_status ng-binding">23</div>
                                            <div id="pm_seat" class="seat_avail seat_status ng-binding">13-22</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>

            </form>
        </div>   
    </div>
<?php }
if(!in_array('analytics',$extensions)){ ?>
    <div class="ep-promo-nav-container" id="tab3C">

        <div class="ep-promo-banner-wrap">
            <div class="ep-upgrade-banner">        
                <div class="ep-upgrade-banner-title">This is a preview of pages added to EventPrime when you upgrade to Premium Bundle.<span><a href="admin.php?page=em_extensions" target="_blank">More Info</a></span></div>
                <div class="ep-upgrade-banner-box"><img src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-premium-img.png' ?>">
                </div>
            </div>

        </div>
        <div class="kikfyre kf-container">
            <img class="ep-promo-analytics" src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-analytics.png' ?>">
        </div>
    </div>
<?php } 
if(!in_array('coupons',$extensions)){ ?>
    <div class="ep-promo-nav-container" id="tab4C">
    <div class="ep-promo-banner-wrap">
        <div class="ep-upgrade-banner">        
            <div class="ep-upgrade-banner-title">This is a preview of pages added to EventPrime when you upgrade to Premium Bundle.<span><a href="admin.php?page=em_extensions" target="_blank">More Info</a></span></div>
            <div class="ep-upgrade-banner-box"><img src="<?php echo EM_BASE_URL . 'includes/admin/template/images/ep-premium-img.png' ?>">
            </div>
        </div>
    </div>
    <div class="kikfyre kf-container">
        <table class="kf-etypes-table ep-coupon-code-table "><!-- remove class for default 80% view -->
            <tbody>
                <tr>
                    <th class="table-header"><input type="checkbox" id="em_bulk_selector" ng-click="checkAll()" ng-model="selectedAll" ng-checked="selections.length == data.coupons.length" class="ng-pristine ng-untouched ng-valid"></th>
                    <th class="table-header">Name</th>
                    <th class="table-header">Code</th>
                    <th class="table-header">Discount</th>
                    <th class="table-header">Start Date</th>
                    <th class="table-header">End Date</th>
                    <th class="table-header">No. of Uses</th>
                    <th class="table-header">Action</th>
                </tr>

                <tr ng-repeat="coupon in data.coupons" class="ng-scope">
                    <td>
                        <input type="checkbox" ng-model="coupon.Selected" ng-click="selectCoupon(coupon.id)" ng-true-value="192" ng-false-value="0" class="ng-pristine ng-untouched ng-valid">
                    </td>
                    <td class="ng-binding">Early Bird</td>
                    <td class="ng-binding">Summit20</td>
                    <td class="ng-binding">20 Percentage</td>
                    <td class="ng-binding"></td>
                    <td class="ng-binding"></td>
                    <td class="ng-binding">0</td>
                    <td>
                        <a ng-href="#">View/Edit</a>
                    </td>
                </tr>
                <!-- end ngRepeat: coupon in data.coupons -->
                <tr ng-repeat="coupon in data.coupons" class="ng-scope">
                    <td>
                        <input type="checkbox" ng-model="coupon.Selected" ng-click="selectCoupon(coupon.id)" ng-true-value="191" ng-false-value="0" class="ng-pristine ng-untouched ng-valid">
                    </td>
                    <td class="ng-binding">Black Friday</td>
                    <td class="ng-binding">DOORBUSTER20</td>
                    <td class="ng-binding">20 Fixed</td>
                    <td class="ng-binding">October 16, 2020 00:00</td>
                    <td class="ng-binding">November 24, 2020 00:00</td>
                    <td class="ng-binding">0</td>
                    <td>
                        <a ng-href="#">View/Edit</a>
                    </td>
                </tr>
                <!-- end ngRepeat: coupon in data.coupons -->
                <tr ng-repeat="coupon in data.coupons" class="ng-scope">
                    <td>
                        <input type="checkbox" ng-model="coupon.Selected" ng-click="selectCoupon(coupon.id)" ng-true-value="190" ng-false-value="0" class="ng-pristine ng-untouched ng-valid">
                    </td>
                    <td class="ng-binding">Halloween</td>
                    <td class="ng-binding">BIGTREAT</td>
                    <td class="ng-binding">50 Percentage</td>
                    <td class="ng-binding">October 1, 2020 00:00</td>
                    <td class="ng-binding">October 31, 2020 00:00</td>
                    <td class="ng-binding">0</td>
                    <td>
                        <a ng-href="#">View/Edit</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" ng-model="coupon.Selected" ng-click="selectCoupon(coupon.id)" ng-true-value="189" ng-false-value="0" class="ng-pristine ng-untouched ng-valid">
                    </td>
                    <td class="ng-binding">Christmas sales</td>
                    <td class="ng-binding">JOLLY30</td>
                    <td class="ng-binding">30 Percentage</td>
                    <td class="ng-binding">December 1, 2020 00:00</td>
                    <td class="ng-binding">October 31, 2020 00:00</td>
                    <td class="ng-binding">0</td>
                    <td>
                        <a ng-href="#" href="#">View/Edit</a>
                    </td>
                </tr>
                <tr ng-repeat="coupon in data.coupons" class="ng-scope">
                    <td>
                        <input type="checkbox"  class="ng-pristine ng-untouched ng-valid">
                    </td>
                    <td class="ng-binding">SPOOKY15</td>
                    <td class="ng-binding">SPOOKY15</td>
                    <td class="ng-binding">15 Percentage</td>
                    <td class="ng-binding"></td>
                    <td class="ng-binding"></td>
                    <td class="ng-binding">0</td>
                    <td>
                        <a ng-href="#" href="#">View/Edit</a>
                    </td>
                </tr><!-- end ngRepeat: coupon in data.coupons -->
            </tbody>
        </table>
    </div>
    </div><?php
}?>
<script>

    (function($){ 

        $(document).ready(function() {    
            $('#ep-promo-tabs a:first').addClass('nav-tab-active');
            $('#ep-promo-tabs a:not(:first)').addClass('nav-tab-inactive');
            $('.ep-promo-nav-container').hide();
            $('.ep-promo-nav-container:first').show();

            $('#ep-promo-tabs a').click(function(){
                var t = $(this).attr('id');
                if($(this).hasClass('nav-tab-inactive')){ 
                    $('#ep-promo-tabs a').addClass('nav-tab-inactive');           
                    $(this).removeClass('nav-tab-inactive');
                    $(this).addClass('nav-tab-active');

                    $('.ep-promo-nav-container').hide();
                    $('#'+ t + 'C').fadeIn('slow');
                }
            });

        });   

    })(jQuery);




</script>

<style>


    .emagic-table .ep-coupon-code-table

    .kikfyre table.kf-etypes-table.ep-coupon-code-table th:nth-child(1) {width: 10%;}
    .kikfyre table.kf-etypes-table.ep-coupon-code-table th:nth-child(2) {width: 15%; }
    .kikfyre table.kf-etypes-table.ep-coupon-code-table th:nth-child(3) {width: 15%; }
    .kikfyre table.kf-etypes-table.ep-coupon-code-table th:nth-child(4) {width: 12%; }
    .kikfyre table.kf-etypes-table.ep-coupon-code-table th:nth-child(5) {width: 15%; }
    .kikfyre table.kf-etypes-table.ep-coupon-code-table th:nth-child(6) {width: 13%; }
    .kikfyre table.kf-etypes-table.ep-coupon-code-table th:nth-child(7) {width: 10%; }
    .kikfyre table.kf-etypes-table.ep-coupon-code-table th:nth-child(8) {width: 10%; }
    
</style>