<?php
/**
 * @var $page
 */

$page = (isset($page)) ? $page : '';
?>

<div class="slms-search-input-group">
    <form action="/" method="get">
        <div class="search-wrapper">
            <input type="text" class="form-control search-input"
                   v-model="search"
                   placeholder="<?php _e('Search...', 'slms'); ?>">
            <input type="hidden" name="current-page" value="<?php echo sanitize_text_field($page); ?>" v-model="current_page">
            <button type="button" class="search-submit" @click="searchRequest">
                <i class="fa fa-search"></i>
            </button>
        </div>
    </form>
</div>
