<div class="ep-box-wrap ep-venue-card-wrap">
    <div class="ep-box-row ep-venue-cards">
        <?php 
        foreach( $venues as $venue ){
            $venue_url = add_query_arg('venue', $venue->id, get_permalink($venue_page_id));
            $enable_seo_urls = em_global_settings('enable_seo_urls');
            if(!empty($enable_seo_urls)){
                $venue_url = get_term_link($venue->id);
            } ?>
            <div class="ep-box-col-<?php echo $venue_cols;?> ep-col-md-6">
                <div class="ep-box-card-item">
                    <div class="ep-box-card-thumb" >
                        <?php if ( ! empty( $venue->gallery_images ) ) { ?>
                            <a href="<?php echo esc_url($venue_url); ?>" class="ep-img-link"><img src="<?php echo wp_get_attachment_image_src( $venue->gallery_images[0], 'full' )[0] ; ?>" alt="<?php _e( 'Event Site/Location Image', 'eventprime-event-calendar-management' ); ?>"> </a>
                        <?php }else{ ?>
                            <a href="<?php echo esc_url($venue_url); ?>" class="ep-img-link"><img src="<?php echo esc_url( plugins_url( '../images/dummy_image.png', __FILE__ ) ) ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>"></a> 
                        <?php } ?>
                    </div>
                    <div class="ep-box-card-content">
                        <div class="ep-box-title ep-box-card-title">
                            <a href="<?php echo esc_url($venue_url); ?>">
                                <?php echo $venue->name; ?>
                            </a> 
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
        <?php } ?>
    </div>
</div>