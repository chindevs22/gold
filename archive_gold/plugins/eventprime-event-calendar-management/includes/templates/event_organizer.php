<?php
wp_enqueue_script('em-public');
wp_enqueue_style('em-public-css');
$event_service = EventM_Factory::get_service('EventM_Service');
$organizer->count = $event_service->event_count_by_organizer($organizer->id);
$organizer_url = add_query_arg("organizer", $organizer->id, $organizers_page_url);
$enable_seo_urls = em_global_settings('enable_seo_urls');
if(!empty($enable_seo_urls)){
    $organizer_url = get_term_link($organizer->id);
}?>
<div class="ep-event-type-card" style="border-bottom:3px solid">
    <a href="<?php echo esc_url($organizer_url); ?>" target="_blank">
        <div class="ep-event-type-cover">
            <img src="<?php if (isset($organizer->image_id)) echo wp_get_attachment_image_src($organizer->image_id, 'medium')[0]; else echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Event Organizer Image', 'eventprime-event-calendar-management'); ?>" <?php if(!isset($organizer->image_id)){ echo 'class="em-no-image"'; }?>>
        </div>  
        <div class="ep-event-type-name"><?php echo $organizer->name; ?></div>
    </a>

    <div class="ep-event-type-age">
        <span class="ep-event-type-title"><?php _e('Phone Number', 'eventprime-event-calendar-management'); ?></span>
        <span><?php if (!empty($organizer->organizer_phones)) echo implode(', ',$organizer->organizer_phones); else _e('Not Available', 'eventprime-event-calendar-management'); ?></span>
    </div>
    <div class="ep-event-type-age">
        <span class="ep-event-type-title"><?php _e('Email Address', 'eventprime-event-calendar-management'); ?></span>
        <span>
            <?php if (!empty($organizer->organizer_emails)) {foreach($organizer->organizer_emails as $key => $val) {
                $organizer->organizer_emails[$key] = '<a href="mailto:'.$val.'">'.htmlentities($val).'</a>';
            }
            echo implode(', ',$organizer->organizer_emails);} else _e('Not Available', 'eventprime-event-calendar-management'); ?>
        </span>
    </div>
    <div class="ep-event-type-age">
        <span class="ep-event-type-title"><?php _e('Website', 'eventprime-event-calendar-management'); ?></span>
        <span><?php if (!empty($organizer->organizer_websites)){ foreach($organizer->organizer_websites as $key => $val) {
                if(!empty($val)){
                    $organizer->organizer_websites[$key] = '<a href="'.$val.'" target="_blank">'.htmlentities($val).'</a>';
                }
            }
            echo implode(', ',$organizer->organizer_websites);} else _e('Not Available', 'eventprime-event-calendar-management'); ?>
        </span>
    </div>
    <div class="ep-event-type-instructions">
        <?php if (isset($organizer->description) && $organizer->description !== '') echo $organizer->description; else _e('No description available', 'eventprime-event-calendar-management'); ?>
    </div>
    
    <div class="ep-event-type-count">
        <span class="em_event_typecount"><?php echo $organizer->count; ?></span>
    </div>
    
</div>              