<div class="ep-box-wrap ep-venue-box-wrap">
    <div class="ep-box-row ep-box-top ep-venue-box-wrap">
        <?php 
        $b = 1;
        $venue_box_color = em_global_settings('venue_box_color');
        foreach( $venues as $venue ){
            $venue_url = add_query_arg('venue', $venue->id, get_permalink($venue_page_id));
            $enable_seo_urls = em_global_settings('enable_seo_urls');
            if(!empty($enable_seo_urls)){
                $venue_url = get_term_link($venue->id);
            }
            if ($b > 4) {
                $b = 1;
            }
            switch ($b) {
                case 1 :
                    $bg_color = (!empty($venue_box_color) && isset($venue_box_color[0])) ? '#'.$venue_box_color[0] : '#A6E7CF';
                    break;
                case 2 :
                    $bg_color = (!empty($venue_box_color) && isset($venue_box_color[1])) ? '#'.$venue_box_color[1] : '#DBEEC1';
                    break;
                case 3 :
                    $bg_color = (!empty($venue_box_color) && isset($venue_box_color[2])) ? '#'.$venue_box_color[2] : '#FFD3B6';
                    break;
                case 4 :
                    $bg_color = (!empty($venue_box_color) && isset($venue_box_color[3])) ? '#'.$venue_box_color[3] : '#FFA9A5';
                    break;
                default:
                    $bg_color = '#A6E7CF';
            }
            $light_bg_color = ep_hex2rgba($bg_color, .5);
            $bg_color = ep_hex2rgba($bg_color, 1);
            ?>
            <div class="ep-box-col-<?php echo $venue_cols;?> ep-box-column ep-box-px-0" data-id="<?php echo $venue->id;?>" data-element_type="column">
                <div class="ep-column-wrap ep-column-populated" style="background-image: linear-gradient(190deg,<?= $bg_color;?>,<?= $light_bg_color;?>); background-color: transparent;">
                    <div class="ep-box-widget-wrap" data-id="<?php echo $venue->id;?>">
                        <div class="ep-box-box-item">
                            <div class="ep-box-box-thumb">
                                <?php if ( ! empty( $venue->gallery_images ) ){ ?>
                                    <a href="<?php echo esc_url($venue_url); ?>" class="img-fluid"><img src="<?php echo wp_get_attachment_image_src( $venue->gallery_images[0], 'full' )[0]; ?>" alt="<?php _e('Event Site/Location Image', 'eventprime-event-calendar-management'); ?>"></a>
                                <?php }else{ ?>
                                    <a href="<?php echo esc_url($venue_url); ?>" class="img-fluid"><img src="<?php echo esc_url( plugins_url( '../images/dummy_image.png', __FILE__ ) ) ?>" class="img-fluid" alt="<?php _e( 'Dummy Image','eventprime-event-calendar-management' ); ?>"></a> 
                                <?php } ?> 
                            </div>
                            <div class="ep-venue-content">
                                <div class="ep-box-title ep-box-box-title">
                                    <a href="<?php echo esc_url($venue_url); ?>"><?php echo $venue->name; ?></a>
                                </div>
                                <div class="kf-venue-seating-capacity dbfl em_color">
                                    <?php if (!empty($venue->type)) : // here we are checking about the venue first because in Standing we dont have capacity and in Seat we have capacity
                                        if ($venue->type == 'standings'): ?>
                                            <div class="kf-event-attr-name em_color dbfl"><?php echo __("Type",'eventprime-event-calendar-management'); ?></div>
                                            <div class="kf-event-attr-value dbfl"><?php echo __("Standing",'eventprime-event-calendar-management'); ?></div>

                                        <?php else: ?>
                                            <div class="kf-event-attr-name em_color dbfl"><?php echo __("Capacity",'eventprime-event-calendar-management'); ?></div>
                                            <div class="kf-event-attr-value dbfl"> 
                                                <?php echo $venue->seating_capacity . ' '.__('People','eventprime-event-calendar-management'); ?>
                                            </div>
                                        <?php endif;
                                    endif;?>
                                </div>
                                <div class="em_venue_add dbfl">
                                    <?php 
                                    if (!empty($venue->address)){
                                        echo wp_trim_words($venue->address, 10);
                                    }
                                    ?>
                                </div>          
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
        <?php $b++; } ?>
    </div>
</div>