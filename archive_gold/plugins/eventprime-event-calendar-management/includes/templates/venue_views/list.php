<!--  ep venue list standard -->
<div class="ep-box-wrap ep-venue-list-wrap">
    <?php foreach( $venues as $venue ){ 
         $venue_url = add_query_arg('venue', $venue->id, get_permalink($venue_page_id));
         $enable_seo_urls = em_global_settings('enable_seo_urls');
         if(!empty($enable_seo_urls)){
             $venue_url = get_term_link($venue->id);
         } ?>
        <div class="ep-box-list-wrap">
            <div class="ep-box-row">
                <div class="ep-box-col-4 ep-list-box-table ep-box-profile-image">
                <?php if ( ! empty( $venue->gallery_images ) ){ ?>
                        <a href="<?php echo esc_url($venue_url); ?>" ><img src="<?php if ( isset( $venue->gallery_images ) ) echo wp_get_attachment_image_src( $venue->gallery_images[0], 'full' )[0]; else echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Event Site/Location Image', 'eventprime-event-calendar-management'); ?>"> </a>
                <?php }else{ ?>
                    <a href="<?php echo esc_url($venue_url); ?>" > <img src="<?php echo esc_url(plugins_url('../images/dummy_image.png', __FILE__)) ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>"> </a>
                <?php } ?>  
            
            </div>
            <div class="ep-box-col-6 ep-list-box-table">
                <div class="ep-box-list-items">
                    <div class="ep-box-title ep-box-list-title">
                        <a class="ep-color-hover" data-venue-id="<?php echo $venue->id;?>" href="<?php echo esc_url($venue_url); ?>" target="_self" rel="noopener">
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
                    <div class="ep-event-description">
                        <div class="ep-event-meta ep-color-before">
                        <div class="em_venue_add dbfl">
                            <?php 
                            if (!empty($venue->address)){
                                echo wp_trim_words($venue->address, 10);
                            }
                            ?>
                        </div>      
                            <div class="ep-view-details"><a class="ep-view-details-button" data-venue-id="<?php echo $venue->id;?>" href="<?php echo add_query_arg("venue",$venue->id, $venues_page_url); ?>">View Detail</a></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ep-box-col-2 ep-list-box-table box-boder-l">
                <ul class="ep-box-social-links">                           
                </ul>
            </div>
        </div>
    </div>
    <?php  } ?>
</div>