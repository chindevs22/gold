<!-- Author: Anjana -->
<div class="ms_lms_course_search_box__search_input <?php echo ( ! empty( $parent_terms ) ) ? 'has_categories' : ''; ?> <?php echo ( ! empty( $presets ) ) ? esc_attr( $presets ) : 'search_button_inside'; ?>">

	<?php 
	$params = $_GET;
    $refer_url = wp_get_referer();
    $path = parse_url($refer_url);
    $query = $path['query'];
    
	?>
	<div class=" autocomplete-wrapper" model="search">
		<input type="text" placeholder="Search..." name="search" autocomplete="off" class=" autocomplete-input">
	</div> 
	<a href="'<?php echo $target_url; ?>?<?php echo $query; ?>"
		class="ms_lms_course_search_box__search_input_button">
		<i class="lnricons-magnifier"></i>
	</a>
</div>