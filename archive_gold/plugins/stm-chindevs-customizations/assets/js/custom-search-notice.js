jQuery(document).ready(function ($) {
    console.log(searchData);
    if (typeof searchData !== 'undefined') {
        $('.search-notice').remove(); // Remove any existing notice
        var noticeMessage = searchData.noticeMessage;
        $('.ms_lms_course_search_box').after('<p class="search-notice">' + noticeMessage + '</p>');
    }
});