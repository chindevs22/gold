<div style="background-color:<?php echo!empty($data['background_color']) ? '#' . $data['background_color'] : '' ?>;">
    <br/>
    <table border="0" border-color="red" cellpadding="0" cellspacing="0" style="background-color: <?php echo!empty($data['background_color']) ? '#' . $data['background_color'] : '' ?>;color:<?php echo!empty($data['font_color']) ? '#' . $data['font_color'] : '' ?>;">
        <tr>
            <td style=" width:80%;border-right: 10px solid <?php echo!empty($data['border_color']) ? '#' . $data['border_color'] : '' ?>;">
                <table>
                    <tr>
                        <th align="center"><span style="font-size: 30pt; text-transform: uppercase; font-weight: 600"><?php echo $data['event_title']; ?></span>
                        </th>
                    </tr>
                    
                    <tr><td align="center"><span style="font-size: 16pt;"><?php echo $data['date_time']; ?></span> 
                            <br/>
                            <br/>
                        </td></tr>
                    <tr>
                        <td width="20%" align="center">
                            <?php if (!empty($data['ticket_logo1'])): ?> <img style=" width: 180px;margin: 0px 10px;" src="<?php echo $data['ticket_logo1']; ?>">
                            <?php else: ?> &nbsp;
                            <?php endif; ?>
                        </td>
                        <td width="5%">&nbsp;</td>
                        <td width="70%" align="center"> 
                            <br/>
                            <?php if (!empty($data['venue_name'])): ?> <span style="font-size: 16pt; text-transform: uppercase;"><?php echo $data['venue_name']; ?></span>
                                <br/><br/>
                            <?php endif; ?>
                            <?php if (!empty($data['venue_address'])): ?> <span style="font-size: 12pt;"><?php echo $data['venue_address']; ?></span> <br/><br/>
                              
                            <?php endif; ?>
                            <?php if (!empty($data['organiser'])): ?> <span style=" font-size: 16pt;text-transform: uppercase;"><?php echo $data['organiser']; ?></span>
                                <br/>
                            <?php endif; ?>
                            <?php if (!empty($data['age_group'])): ?> <span style="font-size: 16pt; text-transform: capitalize; ">Age Group : <?php echo $data['age_group']; ?></span>
                                <br/>
                            <?php endif; ?>
                        </td>
                       <td width="5%">&nbsp;</td>
                    </tr>
                    <tr>
                        <td width="5%">&nbsp;</td>
                        <td width="55%" align="justify" valign="top" style="font-size: 10pt;">                              
                            <span  style="text-align:justify"> <?php echo $data['audience_note']; ?></span>
                        </td>
                        <td width="35%" align="right">
                            <br/> <br/><br/>
                            <span style="font-size: 16pt; text-transform: uppercase;">Price</span>
                            <br/> 
                            <span style="font-size: 25pt; text-transform: uppercase; font-weight: 700"><?php echo $data['currency_symbol']; ?><?php echo $data['ticket_price']; ?>
                                <span style="font-size:16pt"><?php echo $data['ticket_price_dec']; ?></span>
                                <?php if(!empty($data['price_option_name'])){?>
                                    <br/>
                                    <span style="font-size:16pt"><?php echo $data['price_option_name']; ?></span>
                                <?php }?>
                            </span>
                        </td>
                    <td width="5%">&nbsp;</td>
                    </tr>
                </table>
            </td>
            <td style=" width:20%;" align="center">
                <br/>
                <br/>
                <br/>
                <br/> <span style="text-transform: uppercase;font-size: 15pt;"><?php echo $data['seat_type']; ?></span>
                <br/>
                <br/> <span style="font-size: 40pt;"><?php echo $data['seat_no']; ?></span>
                <br/>
                <br/> <span style="font-size: 22pt; font-family: monospace !important;padding:0px; margin: 0px;">ID #<?php echo $data['booking_id']; ?></span>
                <br/>
                <br/>
                <?php 
                $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
                $global_settings = $setting_service->load_model_from_db();
                if($global_settings->show_qr_code_on_ticket == 1){?>
                    <span class="kf-event-attr difl">
                        <?php 
                        $url = get_permalink($global_settings->booking_details_page);
                        $url = add_query_arg('id', $data['booking_id'], $url);
                        $file_name = 'ep_qr_'.md5($url).'.png';
                        $upload_dir = wp_upload_dir();
                        $file_path = $upload_dir['basedir'] . '/ep/' . $file_name;
                        if(!file_exists($file_path)){
                            if(!file_exists(dirname($file_path))){
                                mkdir(dirname($file_path), 0755);
                            }
                            require_once EM_BASE_DIR . 'includes/lib/qrcode.php';
                            $qrCode = new QRcode();
                            $qrCode->png($url, $file_path, 'M', 4, 2);
                        }
                        $image_url = $upload_dir['baseurl'].'/ep/'.$file_name;?>
                        <div class="ep-qrcode-details"  style="width:100%; text-align: center">
                            <img src="<?php echo $image_url; ?>" width="95" height="95" alt="<?php echo __('QR Code', 'eventprime-event-calendar-management'); ?>" />
                        </div>
                    </span><?php
                }?>
            </td>
        </tr>
    </table>
</div>