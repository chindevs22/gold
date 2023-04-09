<?php $column_class = isset($column_class) ? $column_class : '';?>
<div class="em_performer_card difl <?php echo $column_class;?>">
    <div class="kf-performer-wrap dbfl">
        <div class="em_performer_image em_block dbfl">
            <?php
            $performer_url = add_query_arg("performer", $performer->id, $performers_page_url);
            $enable_seo_urls = em_global_settings('enable_seo_urls');
            if(!empty($enable_seo_urls)){
                $performer_url = get_permalink($performer->id);
            }
            if (!empty($performer->feature_image_id)): ?>
                <a href="<?php echo $performer_url; ?>" target="_blank">
                    <?php echo get_the_post_thumbnail($performer->id,'thumbnail'); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo $performer_url; ?>" target="_blank">
                    <img height="150" width="150" src="<?php echo esc_url(plugins_url('/images/dummy-performer.png', __FILE__)) ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" >
                </a>
            <?php endif; ?>
        </div>
        <div class="em_performer_description dbfl">                   
            <div class="em_performer_name em_wrap"><a href="<?php echo $performer_url; ?>" target="_blank"><?php echo $performer->name; ?></a></div>
            <?php if(!empty($performer->role)): ?>                   
                <div title="<?php echo $performer->role; ?>" class="em_performer_role em_color em_wrap"><?php echo $performer->role; ?></div>
            <?php endif; ?>
                <div class="kf_performer_desc em_block"><?php if (isset($performer->description) && $performer->description !== '') echo $performer->description; else _e('No desciption available','eventprime-event-calendar-management'); ?></div>
        </div> 

        <?php
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events = $event_service->upcoming_events_for_performer($performer->id);
        ?>
        <div class="em_event_count">
            <span class="em_event_hendling em-bg"><?php echo count($events); ?></span>
        </div>
    </div>
</div>