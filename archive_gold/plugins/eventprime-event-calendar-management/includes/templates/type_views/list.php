  <!--  ep type list standard -->
<div class="ep-box-wrap ep-type-list-wrap">
    <?php foreach($types as $type){ 
        $type_url = add_query_arg("type", $type->id, $types_page_url);
        $enable_seo_urls = em_global_settings('enable_seo_urls');
        if(!empty($enable_seo_urls)){
            $type_url = get_term_link($type->id);
        }?>
        <div class="ep-box-list-wrap">
            <div class="ep-box-row">
                <div class="ep-box-col-4 ep-list-box-table ep-box-profile-image">
                <?php if (!empty($type->image_id)){ ?>
                        <a href="<?php echo $type_url; ?>" ><img src="<?php if (isset($type->image_id)) echo wp_get_attachment_image_src($type->image_id, 'large')[0]; else echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Event Type Image', 'eventprime-event-calendar-management'); ?>"> </a>
                <?php }else{ ?>
                    <a href="<?php echo $type_url; ?>" > <img src="<?php echo esc_url(plugins_url('../images/dummy_image.png', __FILE__)) ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>"> </a>
                <?php } ?>  
            </div>
            <div class="ep-box-col-6 ep-list-box-table">
                <div class="ep-box-list-items">
                    <div class="ep-box-title ep-box-list-title">
                        <a class="ep-color-hover" data-type-id="<?php echo $type->id;?>" href="<?php echo $type_url; ?>" target="_self" rel="noopener">
                            <?php echo $type->name; ?>
                        </a>
                    </div>
                    <div class="ep-box-card-role ep-type-age">
                        <?php _e('Age Group', 'eventprime-event-calendar-management'); ?>
                        <?php if ($type->age_group !== 'custom_group') echo em_code_to_display_string($type->age_group); else _e($type->custom_group, 'eventprime-event-calendar-management'); ?>
                    </div>
                    <div class="ep-event-description">
                        <div class="ep-event-meta ep-color-before">
                           <!--  <div class="ep-event-type-instructions"> -->
                                <?php /* if (isset($type->description) && $type->description !== '') echo $type->description; else _e('No description available', 'eventprime-event-calendar-management'); */ ?>
                           <!--  </div> -->
                           <div class="ep-view-details"><a class="ep-view-details-button" data-event-id="<?php echo $type->id;?>" href="<?php echo add_query_arg("type",$type->id, $types_page_url); ?>">View Detail</a></div>
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