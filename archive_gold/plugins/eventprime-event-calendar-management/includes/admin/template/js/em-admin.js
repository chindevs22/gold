function em_adjust_card_height()
{
    jQuery('.kikfyre').each(function(){  
        var highestBox = 0;
        jQuery(this).find('.kf-card').each(function(){ 
            if(jQuery(this).height() > highestBox){  
                highestBox = jQuery(this).height();  
            }
        })
        jQuery(this).find('.kf-card').height(highestBox);
    });
}

/*
 * Configuring Datepicker to avoid regional settings
 */
function em_set_date_defaults()
{
  jQuery.datepicker.setDefaults(jQuery.datepicker.regional[""]);
}

function em_manager_nav_changed(nav){
    document.location.href= '?page=' + nav;
}

function em_goto_event_dashboard(id){
    url= admin_vars.admin_url + "admin.php?page=em_dashboard&post_id=" + id;
    location.href= url;
}


function em_copy_to_clipboard(target) {
    $= jQuery;
    var text_to_copy = $(target).text();
    var tmp = $("<input id='fd_event_shortcode_input' readonly>");
    var target_html = $(target).html();
    $(target).html('');
    $(target).append(tmp);
    tmp.val(text_to_copy).select();
    var result = document.execCommand("copy");

    if (result != false) {
        $(target).html(target_html);
        $("#em_msg_copied_to_clipboard").fadeIn('slow');
        $("#em_msg_copied_to_clipboard").fadeOut('slow');
    } else {
        $(document).mouseup(function (e) {
            var container = $("#fd_event_shortcode_input");
            if (!container.is(e.target) // if the target of the click isn't the container... 
                    && container.has(e.target).length === 0) // ... nor a descendant of the container 
            {
                $(target).html(target_html);
            }
        });
    }
}

function em_redirect(path){
    url= admin_vars.admin_url + "admin.php" + path;
    location.href= url;
}


function showMore(){
    $=jQuery;
    $('.ep-dashboard-more, .ep_ext_promo_popup').toggle();
}

jQuery(document).ready(function(){
    $=jQuery;
    $(".ep-popup-close").click(function(){
        $(".ep-popup, .ep_ext_promo_popup").hide();
    })
});

function showExtFeatures(obj,id,cls){
    $=jQuery;
    $("." + cls).hide();
    $(".ep-fd-promo-nub").hide();
    $('#' + id).show();
    $(obj).find('.ep-fd-promo-nub').show();
}

function ep_activation_popup(){
    event.preventDefault();
    jQuery('#ep-activation-popup').toggle();
    jQuery('.ep-modal-box-wrap').removeClass('ep-modal-box-out');
    jQuery('.ep-modal-box-wrap').addClass('ep-modal-box-in');
}

function ep_fetch_offers(){
    jQuery('.ep-fetch_offers-spiner').show();
    jQuery.ajax({
        type: "POST",
        url: admin_vars.ajax_url,
        data: {action: 'em_fetch_offers'},
        success: function (response) {
            if(response) {
                jQuery('.ep-loader-wrap').hide();
                jQuery('.ep-offer-button').hide();
                jQuery('.ep-fetch-offers-popup').hide();
                jQuery('.ep-offers').addClass('pg-result-displayed');
                jQuery('.ep-offer-results').html(response.data);
            }
        }
    });
}

jQuery(document).ready(function () {
    jQuery('.ep-modal-box-close, .ep-modal-box-overlay').click(function () {
        setTimeout(function () {
            //jQuery(this).parents('.rm-modal-view').hide();
            jQuery('.ep-modal-box-main').hide();
        }, 400);
    });        
    jQuery('.ep-offer-button button').click(function () {
        jQuery('.ep-modal-box-main').show();
        jQuery('.ep-modal-box-wrap').removeClass('ep-modal-box-out');
        jQuery('.ep-modal-box-wrap').addClass('ep-modal-box-in');
    });
    
    jQuery('.ep-modal-box-close, .ep-modal-box-overlay').click(function () {
        jQuery('.ep-modal-box-wrap').removeClass('ep-modal-box-in');
        jQuery('.ep-modal-box-wrap').addClass('ep-modal-box-out');                 
    });

    jQuery('.ep-setting-modal-close, .ep-setting-modal-overlay').click(function () {
        jQuery('.ep-setting-modal-wrap').removeClass('ep-setting-popup-in');
        jQuery('.ep-setting-modal-wrap').addClass('ep-setting-popup-out');
        jQuery('.ep-setting-modal-overlay').removeClass('ep-setting-popup-overlay-fade-in');
        jQuery('.ep-setting-modal-overlay').addClass('ep-setting-popup-overlay-fade-out');
        setTimeout(function () {
            jQuery('.ep-setting-modal-view').hide();
        }, 400);
    });

    // side banner
    /*setTimeout(function(){
        jQuery(".ep-side-banner").show();
    }, 1000);*/

    jQuery('#em-ext-controls li a').click(
        function(e){
            e.preventDefault();
            jQuery("#em-ext-controls a").removeClass('ep-extension-list-active');
            jQuery(this).addClass('ep-extension-list-active');
            var that = this,
                $that = jQuery(that),
                id = that.id,
                ext_list = jQuery('.ep-extensions-box-wrap');
            if (id == 'all-extensions') {
                ext_list.find('.ep-ext-card').fadeIn(500);
            }
            else {
                ext_list.find('.ep-ext-card.' + id + ':hidden').fadeIn(500);
                ext_list.find('.ep-ext-card').not('.' + id).fadeOut(500);
            }
        }
    );

});

function bannerNewEventPopup(){
    jQuery('#ep-activation-popup').hide();
    jQuery("#em_add_new_link a").trigger('click');
}

function CallEPDashboardModal(ele) {
    jQuery("#"+ele).toggle();
    jQuery('.ep-setting-modal-wrap').removeClass('ep-setting-popup-out');
    jQuery('.ep-setting-modal-wrap').addClass('ep-setting-popup-in');
    jQuery('.ep-setting-modal-overlay').removeClass('ep-setting-popup-overlay-fade-out');
    jQuery('.ep-setting-modal-overlay').addClass('ep-setting-popup-overlay-fade-in');   
}

function em_dismiss_notice(){
    jQuery(".ep-notice-banner").remove();
    jQuery.ajax({
        type: "POST",
        url: admin_vars.ajax_url,
        data: {action: 'em_dismiss_notice_action'},
        success: function (response) { }
    });
}