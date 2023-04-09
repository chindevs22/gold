<?php
wp_enqueue_script('em-public');
wp_enqueue_style('em-public-css');
$event_service = EventM_Factory::get_service('EventM_Service');
$type->count = $event_service->event_count_by_type($type->id);
$column_class = isset($column_class) ? $column_class : '';
$type_url = add_query_arg("type", $type->id, $types_page_url);
$enable_seo_urls = em_global_settings('enable_seo_urls');
if(!empty($enable_seo_urls)){
    $type_url = get_term_link($type->id);
}?>
<div class="ep-event-type-card <?php echo $column_class;?>" style="border-bottom:3px solid #<?php echo $type->color; ?>">
    <a href="<?php echo esc_url($type_url); ?>" target="_blank">
        <div class="ep-event-type-cover">
            <img src="<?php if (isset($type->image_id)) echo wp_get_attachment_image_src($type->image_id, 'medium')[0]; else echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Event Type Image', 'eventprime-event-calendar-management'); ?>" <?php if(!isset($type->image_id)){ echo 'class="em-no-image"'; }?>>
        </div>  
        <div class="ep-event-type-name"><?php echo $type->name; ?></div>
    </a>
    <div class="ep-event-type-age">
        <span class="ep-event-type-title"><?php _e('Age Group', 'eventprime-event-calendar-management'); ?></span>
        <span><?php if ($type->age_group !== 'custom_group') echo em_code_to_display_string($type->age_group); else _e($type->custom_group, 'eventprime-event-calendar-management'); ?></span>
    </div>
    <div class="ep-event-type-instructions">
        <?php if (isset($type->description) && $type->description !== '') echo $type->description; else _e('No description available', 'eventprime-event-calendar-management'); ?>
    </div>
    <a href="<?php echo esc_url($type_url); ?>" target="_blank">
        <div class="ep-event-type-count">
            <span class="em_event_typecount"><?php echo $type->count; ?></span>
        </div>
    </a>
</div>              