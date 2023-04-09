<div class="ep-box-wrap ep-event-type-card-wrap">
    <div class="ep-box-row ep-type-cards">                  
        <?php 
        foreach($types as $type){
            $type_url = add_query_arg("type", $type->id, $types_page_url);
            $enable_seo_urls = em_global_settings('enable_seo_urls');
            if(!empty($enable_seo_urls)){
                $type_url = get_term_link($type->id);
            }?>
            <div class="ep-box-col-<?php echo $type_cols;?> ep-col-md-6">
                <div class="ep-box-card-item">
                    <div class="ep-box-card-thumb" >
                        <?php if ( ! empty( $type->image_id ) ) { ?>
                            <a href="<?php echo $type_url; ?>" class="ep-img-link"><img src="<?php echo wp_get_attachment_image_src( $type->image_id, 'large' )[0] ; ?>" alt="<?php _e( 'Event Type Image', 'eventprime-event-calendar-management' ); ?>"> </a>
                        <?php }else{ ?>
                            <a href="<?php echo $type_url; ?>" class="ep-img-link"><img src="<?php echo esc_url( plugins_url( '../images/dummy_image.png', __FILE__ ) ) ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>"></a> 
                        <?php } ?>
                    </div>
                    <div class="ep-box-card-content">
                        <div class="ep-box-title ep-box-card-title">
                            <a href="<?php echo $type_url; ?>">
                                <?php echo $type->name; ?>
                            </a> 
                        </div>
                        <div class="ep-box-card-role ep-event-type-age">
                            <?php _e('Age Group', 'eventprime-event-calendar-management'); ?>
                            <?php if ($type->age_group !== 'custom_group') echo em_code_to_display_string($type->age_group); else _e($type->custom_group, 'eventprime-event-calendar-management'); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>