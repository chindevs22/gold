<div class="ep-box-wrap">
    <div class="ep-box-row ep-box-top ep-performer-box-wrap ep-type-box-wrap ep-box-mx-0">
        <?php 
        $b = 1;
        $type_box_color = em_global_settings('type_box_color');
        foreach( $types as $type ){
            $type_url = add_query_arg("type", $type->id, $types_page_url);
            $enable_seo_urls = em_global_settings('enable_seo_urls');
            if(!empty($enable_seo_urls)){
                $type_url = get_term_link($type->id);
            }
            if( $b > 4 ){ 
                $b = 1;
            }
            switch ($b) {
                case 1 :
                    $bg_color = (!empty($type_box_color) && isset($type_box_color[0])) ? '#'.$type_box_color[0] : '#A6E7CF';
                    break;
                case 2 :
                    $bg_color = (!empty($type_box_color) && isset($type_box_color[1])) ? '#'.$type_box_color[1] : '#DBEEC1';
                    break;
                case 3 :
                    $bg_color = (!empty($type_box_color) && isset($type_box_color[2])) ? '#'.$type_box_color[2] : '#FFD3B6';
                    break;
                case 4 :
                    $bg_color = (!empty($type_box_color) && isset($type_box_color[3])) ? '#'.$type_box_color[3] : '#FFA9A5';
                    break;
                default:
                    $bg_color = '#A6E7CF';
            }
            $light_bg_color = ep_hex2rgba($bg_color, .5);
            $bg_color = ep_hex2rgba($bg_color, 1);
            ?>
            <div class="ep-box-col-<?php echo $type_cols;?> ep-box-column ep-box-px-0" data-id="<?php echo $type->id;?>" data-element_type="column">
                <div class="ep-column-wrap ep-column-populated" style="background-image: linear-gradient(190deg,<?= $bg_color;?>,<?= $light_bg_color;?>); background-color: transparent;">
                    <div class="ep-box-widget-wrap" data-id="<?php echo $type->id;?>">
                        <div class="ep-box-box-item">
                            <div class="ep-box-box-thumb">
                                <?php if ( ! empty( $type->image_id ) ){ ?>
                                    <a href="<?php echo $type_url; ?>" class="img-fluid"><img src="<?php echo wp_get_attachment_image_src( $type->image_id, 'large' )[0]; ?>" alt="<?php _e('Event Type Image', 'eventprime-event-calendar-management'); ?>"></a>
                                <?php }else{ ?>
                                    <img src="<?php echo esc_url( plugins_url( '../images/dummy_image.png', __FILE__ ) ) ?>" class="img-fluid" alt="<?php _e( 'Dummy Image','eventprime-event-calendar-management' ); ?>"> 
                                <?php } ?>         
                            </div>
                            <div class="ep-performer-content">
                                <div class="ep-box-title ep-box-box-title"><a href="<?php echo $type_url; ?>"><?php echo $type->name; ?></a> </div>
                                <div class="ep-box-card-role ep-type-role">
                                    <?php _e('Age Group', 'eventprime-event-calendar-management'); ?>
                                    <?php if ($type->age_group !== 'custom_group') echo em_code_to_display_string($type->age_group); else _e($type->custom_group, 'eventprime-event-calendar-management'); ?>
                                </div>        
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
        <?php $b++; } ?>
    </div>
</div>