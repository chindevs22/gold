var requestInProgress = false;

function progressStart()
{
    requestInProgress = true;
}

function progressStop()
{
    requestInProgress = false;
}

function em_show_venues_map(element_id, addresses) {
    /*
     * To store all the marker object for further operations
     * @type Array
     */
    var allMarkers = [];
    /*
     * Map object with default location and zoom level
     * @type google.maps.Map
     */

    var map = new google.maps.Map(document.getElementById(element_id), {
        center: {lat: -34.397, lng: 150.644},
        zoom: 2
    });

    /*
     * Textbox to contain formatted address. Same input box can be used to search location either 
     * by lat long or by address.
     * @type Element
     */

    var geocoder = new google.maps.Geocoder;


    // Adding marker on map for multiple addresses
    if (addresses) {
        geocodeAddress(geocoder, map, addresses);
    }


    // Sets the map on all markers in the array.
    function setMapOnAll(map) {
        for (var i = 0; i < allMarkers.length; i++) {
            allMarkers[i].setMap(map);
        }
    }

    /**
     * @summary Add markers from array of addresses.
     * @param {google.maps.Geocoder} geocoder
     * @param {google.maps.Map} resultsMap
     * @param {String Array} addresses
     * 
     */
    function geocodeAddress(geocoder, resultsMap, addresses) {
        var venueAddress = addresses;
        var venueZoomLevel = [];
        if(addresses['address']){
            venueAddress = addresses['address'];
        }
        if(addresses['zoom_level']){
            venueZoomLevel = addresses['zoom_level'];
        }
        for (var i = 0; i < venueAddress.length; i++)
        {
            var address = venueAddress[i];
            var zoomlevel = venueZoomLevel[i];
            if (address) {

                geocoder.geocode({'address': address}, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        resultsMap.setCenter(results[0].geometry.location);
                        resultsMap.setZoom(parseInt(zoomlevel));
                        var marker = new google.maps.Marker({
                            map: resultsMap,
                            position: results[0].geometry.location,
                            //    icon: em_map_info.gmarker
                        });
                        var infowindow = new google.maps.InfoWindow;

                        infowindow.setContent(results[0].formatted_address);
                        marker.addListener('click', function () {
                            infowindow.open(map, marker);
                        });

                        allMarkers.push(marker);
                        //  infowindow.open(map, marker);
                    } else if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT)
                    {
                        setTimeout(1000);//   document.getElementById('status').innerHTML +="request failed:"+status+"<br>";
                    } else {

                        //alert('Geocode was not successful for the following reason: ' + status);
                    }
                });
            }
        }
        ;

    }

}

function em_padNumber(number) {
    var ret = new String(number);
    if (ret.length == 1)
        ret = "0" + ret;
    return ret;
}


function em_start_timer(duration, display) {
    var start = Date.now(),
            diff,
            minutes,
            seconds,
            stop = false,
            counter = 1;
    function timer() {
        if (!stop)
        {
            // get the number of seconds that have elapsed since
            // startTimer() was called
            diff = duration - (((Date.now() - start) / 1000) | 0);

            // does the same job as parseInt truncates the float

            minutes = (diff / 60) | 0;
            seconds = (diff % 60) | 0;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;
            display.textContent = minutes + ":" + seconds;


            if (diff <= 0) {
                // add one second so that the count down starts at the full duration
                // example 04:00 not 03:59
                start = Date.now() + 1000;
            }

            if (diff == 0) {
                stop = true;
            } else {
                counter++;
                jQuery("#em_payment_progress").width(counter * (100 / 240) + "%");
            }


        }
    }
    ;
    // we don't want to wait a full second before the timer starts

    timer();
    setInterval(timer, 1000);
}


function rm_event_map_canvas(element_id, venue) {
    em_show_venues_map(element_id, venue);
}

function em_show_venue_map(element_id) {
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: {action: 'em_load_venue_addresses'},
        success: function (response) {

            var data = JSON.parse(response);
            em_show_venues_map(element_id, data);
        }
    });
}

function em_event_booking(id) {
    if (id > 0)
    { var formName = 'em_booking' + id;
        jQuery('form[name=' + formName + ']').submit();
    } 
    else
        document.em_booking.submit();
}

function em_event_map_canvas(element_id, venue) {
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: {action: 'em_load_venue_addresses', venue_id: venue},
        success: function (response) {
            var data = JSON.parse(response);
            em_show_venues_map(element_id, data);
        }
    });
}

function em_single_venue_map_canvas(element_id, venue) {
    //  alert(venue);
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: {action: 'em_load_venue_addresses', venue_id: venue},
        success: function (response) {
            var data = JSON.parse(response);
            em_show_venues_map(element_id, data);
        }
    });
}

function em_booking_map_canvas(element_id, venue) {
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: {action: 'em_load_venue_addresses', venue_id: venue},
        success: function (response) {

            var data = JSON.parse(response);
            //  alert(data);
            em_show_venues_map(element_id, data);
        }
    });
}

function em_user_event_venue(element_id, venue) {
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: {action: 'em_load_venue_addresses', venue_id: venue},
        success: function (response) {
            var data = JSON.parse(response);
            em_show_venues_map(element_id, data);
        }
    });
}
function em_change_dp_css()
{
    $ = jQuery;
    $(".em_widget_container .ui-datepicker-header").removeClass("ui-widget-header");
    var emColor = $('.em_widget_container').find('a').css('color');
    $(".em_color").css('color', emColor);
    $(".em_widget_container .ui-datepicker-header").css('background-color', emColor);
    $(".em_widget_container .ui-datepicker-current-day").css('background-color', emColor);
}


function em_show_calendar(dates) {
    $ = jQuery;
    $("#em_calendar_widget").datepicker({
        onChangeMonthYear: function () {
            setTimeout(em_change_dp_css, 40);
            return;
        },
        onHover: function () {},
        onSelect: function (dateText, inst) {
            var gotDate = $.inArray(dateText, dates);
            if (gotDate >= 0)
            {
                // Accessing only first element to avoid conflict if duplicate element exists on page
                $("#em_start_date:first").val(dateText);
                var search_url = $("form[name='em_calendar_event_form']:first").attr('action');
                search_url = em_add_param_to_url("em_s=" + $("input[name='em_s']:first").val(), search_url);
                search_url = em_add_param_to_url("em_sd=" + dateText, search_url);
                location.href = search_url;
            }

        },
        beforeShowDay: function (date) {
            setTimeout(em_change_dp_css, 10);
            var year = date.getFullYear();
            // months and days are inserted into the array in the form, e.g "01/01/2009", but here the format is "1/1/2009"
            var month = em_padNumber(date.getMonth() + 1);
            var day = em_padNumber(date.getDate());
            // This depends on the datepicker's date format
            var dateString = year + "-" + month + "-" + day;

            var gotDate = $.inArray(dateString, dates);
            if (gotDate >= 0) {
                // Enable date so it can be deselected. Set style to be highlighted
                return [true, "em-cal-state-highlight"];
            }
            // Dates not in the array are left enabled, but with no extra style
            return [true, ""];
        }, changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd"
    });

    em_change_dp_css();
}

function em_cancel_booking(booking_id)
{
    $ = jQuery;
    $("#em_booking_details_modal").addClass("kf_progress_screen");
    $.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: {action: 'em_cancel_booking_by_user', post_id: booking_id},
        success: function (response) {
            $("#em_booking_details_modal").removeClass('kf_progress_screen');
            var data = JSON.parse(response);
            if (data.error == false) {
                $("#em_booking_status").html(data.status);
                $("#em_booking_status_message").html(data.status_message);
                $("#em_action_bar").remove();
                $("#em_print_bar").remove();
                setTimeout(function(){
                    $("#em_booking_status_message").remove();
                }, 3000);
            } else {
                alert("Booking could not be cancelled");
            }
        }
    });
}

function em_set_dominent_color()
{
    $ = jQuery;
    $("#primary.content-area .entry-content").prepend("<a>");
    var emColor = $('.emagic, #primary.content-area .entry-content').find('a').css('color');
    $(".em_color").css('color', emColor);
    $(".emagic .em_event_views .ep-event-view-sort.ep-active-view a").css('color', emColor);
    $(".emagic .em_event_views_wrap .em_event-filter-reset button").css('color', emColor);
    $(".emagic .ep-event-type-age .ep-event-type-title").css('color',emColor);
    $(".emagic .ep-event-type-cards .ep-event-type-count").css('background-color',emColor);
    $(".emagic .kf-performer-wrap .em_event_count").css('background-color',emColor);
    $(".ep-masonry-load-more-wrap .ep-load-more-button").css('border-color',emColor);
    $(".ep-view-load-more-wrap .ep-loading-view-btn").css('border-color',emColor);
    $(".em_list_view .ep-event-list-standard .ep-listed-event-month-tag").css('border-color',emColor);
    $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
    $(".booking-details-border").css('border-bottom-color',emColor);
    $(':root').css('--themeColor', emColor);

    
    var style = document.createElement('style');
    style.type = 'text/css';
    style.innerHTML = '.em_bg {background-color:' + emColor + ' !important;} .em_color{color:' + emColor + ' !important;}';
    document.getElementsByTagName('head')[0].appendChild(style);
    $("li").css('border-color', emColor);
    $(".emagic .em_card .em-cards-footer .em_eventpage_register button.em_event-booking").css('color',emColor);
    $("#booking_dialog .em_progress_screen").css('border-color',emColor);
    $(".em_progress_screen.ep-progress-loader").css('border-color',emColor);
}

function em_update_calendar_time_display() {
    $ = jQuery;
    $('span.fc-time').each(function(index) {
        var str = $(this).text();
        if(str.charAt(str.length-1) == 'a' || str.charAt(str.length-1) == 'p') {
            $(this).text(str + 'm');
        }
    });
}
//CSS Manipulation



function em_width_adjust(cardClass) {
    $ = jQuery;
    kfWidth = $(".emagic").innerWidth();
    if (kfWidth < 720) {
        $(".emagic").addClass("narrow");
    }
    switch (true) {
        case kfWidth <= 650:
            $(cardClass).addClass("em_card1");
            break;
        case kfWidth <= 850:
            $(cardClass).addClass("em_card2");
            break;
        case kfWidth <= 1150:
            $(cardClass).addClass("em_card3");
            break;
        case kfWidth <= 1280:
            $(cardClass).addClass("em_card4");
            break;
        case kfWidth > 1280:
            $(cardClass).addClass("em_card5");
            break;
        default:
            $(cardClass).addClass("em_card2");
            break;
    }
}

jQuery(window).resize(function(){
    if(jQuery(".em_card").length > 0){
        jQuery(".em_card").removeClass(["em_card1", "em_card2", "em_card3", "em_card4", "em_card5"]);
        em_width_adjust(".em_card");
    }
    if(jQuery(".ep-masonry-item-wrap").length > 0){
        jQuery(".ep-masonry-item-wrap").removeClass(["em_card1", "em_card2", "em_card3", "em_card4", "em_card5"]);
        em_width_adjust(".ep-masonry-item-wrap");
    }
});

function em_add_param_to_url(param, url) {
    _url = url;
    _url += (_url.split('?')[1] ? '&' : '?') + param;
    return _url;
}

function em_get_ical_file(event_id) {
    window.location = window.location.href + '&download=ical';
}

/*********************** On Ready statement ***************/
jQuery(document).ready(function () {
    $ = jQuery;

    $('.emagic').prepend('<a></a>'); // Prepending a tag to detect dominent color
    em_width_adjust(".em_card");

    $("#em_register").click(function () { // Event register button
        $('html, body').animate({
            scrollTop: $("#em_register_section").offset().top
        }, 500);
    });

    em_set_dominent_color(); // Setting dominent colors for nice theme.

    // Events directory filter mouse leave
    /*$('.ep-event-filter-block .ep-event-types').mouseleave(function () {
        $(this).find('.ep-event-types-wrap').hide();
    });*/

    // For Event calendar widget 
    if ($("#em_calendar_widget").length > 0) {
        // Send ajax request to get all the event start dates
        $.ajax({
            type: "POST",
            url: em_js_vars.ajax_url,
            data: {action: 'em_load_event_dates'},
            success: function (response) {
                var data = JSON.parse(response);
                var dates = data.start_dates;
                em_show_calendar(dates);
            }
        });
    }

    if ($("#rm_map_canvas").length > 0) {
        var venue_name = $("#rm_map_canvas").attr("data-venue-name");
        rm_event_map_canvas("rm_map_canvas", venue_name);
    }

    // Global form reset 
    $('.em_reset_date_btn').on('click', function () {
        var field_selector = $(this).data('field');
        var form = $(this).closest('form');
        if (field_selector.length > 0 && form.length > 0) {
            form.find(field_selector).val('');
        }
    });

    // Global datepicker configuration
    if($.datepicker){
        $.datepicker.setDefaults( $.datepicker.regional[ em_js_vars.site_language ] );
    }
    var em_filter_date= $(".em_date");
    if(em_filter_date.length>0){
        em_filter_date.datepicker({
            dateFormat: em_js_vars.date_format
        }).datepicker(new Date());
        if(em_filter_date.val()==''){
            dt= new Date();
            //em_filter_date.val(dt.getFullYear() + '-' + ("0" + (dt.getMonth() + 1)).slice(-2) + '-' + dt.getDate());
        }
    }
    
    $('.em_hide_filter').click(function () {
        $(this).closest('form').find('.kf-event-search-filter').toggle();
    });

    $('.ep-event-title.ep_menu_item_visible').click(function () {
        $(this).closest('form').find('.ep_menu_item_hide').toggle();
    });

    // Event card wrapping
    var cards = $('.emagic .em_card.em_card2');
    if (cards.length) {
        for (var i = 0; i < cards.length; i += 2) {
            cards.slice(i, i + 2).wrapAll('<div class="em-cards-wrap"></div>');
        }
    }

    cards = $('.emagic .em_card.em_card3');
    if (cards.length) {
        for (var i = 0; i < cards.length; i += 3) {
            cards.slice(i, i + 3).wrapAll('<div class="em-cards-wrap"></div>');
        }
    }

    cards = $('.emagic .em_card.em_card4');
    if (cards.length) {
        for (var i = 0; i < cards.length; i += 4) {
            cards.slice(i, i + 4).wrapAll('<div class="em-cards-wrap"></div>');
        }
    }

    $('.em_event_cover a').each(function () {
        var wrapper = $(this);
        url = wrapper.find('img').prop('src');
        if (url) {
            wrapper
                    .css('backgroundImage', 'url(' + url + ')')
                    .addClass('em-compat-object-fit')
                    .children('img').hide();
        }
    });
    
    // hide disabled events footer
    $(".em_event_disabled .kf_ticket_price,.em_event_disabled .em-cards-footer,.em_event_disabled .kf-upcoming-event-price,.em_event_disabled .kf-upcoming-event-booking").hide();
    $(".em_event_disabled .em-masonry-footer").hide();
    $(".em_event_disabled .ep-list-view-footer").hide();
    $(".em_event_disabled .em-slider-footer").hide();
    // Profile Grid integration
    var pmDomColor = $(".pmagic").find("a").css('color');
    $("#pg_event_booking_tab_content .emActiveTab i").css('color', pmDomColor);
    $("#pg_group_events #em_event_search_form a").each(function(){
        var current_href= $(this).attr('href');
        $(this).attr('href',current_href + "#pg_group_events");
    });
});

function event_magic_print(el)
{
    $= jQuery;
    
    $("#"+el).printThis({
        importCSS: true,                // import parent page css
        importStyle: false,             // import style tags
        printContainer: true,           // grab outer container as well as the contents of the selector
        pageTitle: "",                  // add title to print page
        removeInline: false,            // remove all inline styles from print elements
        removeInlineSelector: "body *", // custom selectors to filter inline styles. removeInline must be true
        header: null,                   // prefix to html
        footer: null,                   // postfix to html 
        beforePrint: function(){ $("#"+el).show()},
        afterPrint: function(){$("#"+el).hide()}
   });
}

function em_show_ticket_print_btn(print_container){
    $= jQuery;
    el=$("#em_print_btn");
    if(el.length==0)
        return; 
    el.show();
}

function print_seating_ticket(url){
   var selected_val= jQuery("#em_seat_no").val();
   if(selected_val){
       url= url + '&seat_no=' + selected_val;
   }
   document.location.href=url;
}

//CSS Manipulation
function ep_card_width_adjust(performer_cardClass) {
    $ = jQuery;
    ep_card_Width = $(".emagic").innerWidth();
    if (ep_card_Width < 720) {
        $(".emagic").addClass("narrow");
    }
    switch (true) {
        
        case ep_card_Width <= 450:
            $(performer_cardClass).addClass("em_card1");
            break;
        case ep_card_Width <= 650:
            $(performer_cardClass).addClass("em_card2");
            break;
        case ep_card_Width <= 850:
            $(performer_cardClass).addClass("em_card3");
            break;
        case ep_card_Width <= 1150:
            $(performer_cardClass).addClass("em_card4");
            break;
        case ep_card_Width <= 1280:
            $(performer_cardClass).addClass("em_card5");
            break;
     
        default:
            $(performer_cardClass).addClass("em_card3");
            break;
    }
}

/*********************** On Ready statement ***************/
jQuery(document).ready(function () {
    $ = jQuery;
    ep_card_width_adjust(".em_performer_card");
    ep_card_width_adjust(".em_venue_card");
    
    $('#ep-filter-bar-collapse-toggle').on('click', function () {
        $(this).toggleClass('ep-filter-bar-open');
        $('#ep-event-filterbar').slideToggle('fast');
    });
    // events view dropdown
    $(".ep-sort-select-wrapper").on('click', function(){
        if($(".ep-sort-select").hasClass('open')){
            $(".ep-sort-select").removeClass('open');
        }
        else{
            $(".ep-sort-select").addClass('open');
        }
    });
    // click on dropdown
    $(".ep-sort-select-wrapper .ep-sort-option").on('click', function(){
        var thistext = $(this).data('text');
        var thisurl = $(this).data('url');
        $(".ep-sort-select-wrapper .ep-sort-option").each(function(){
            $(this).removeClass('selected');
        })
        $(this).addClass('selected');
        $(this).closest('.ep-sort-select').find('.ep-sort-select__trigger span').text(thistext);
        location.href = thisurl;
    });
    // check for selected view
    $(".ep-sort-select-wrapper .ep-sort-option").each(function(){
        if($(this).hasClass('selected')){
            var thistext = $(this).data('text');
            $(this).closest('.ep-sort-select').find('.ep-sort-select__trigger span').text(thistext);
        }
    });
    // close view dropdown if click on window
    window.addEventListener('click', function(e) {
        if(!$(e.target).hasClass('ep-sorting-arrow')){
            if($(".ep-sort-select").hasClass('open')){
                $(".ep-sort-select").removeClass('open');
            }
        }
    });

    if($("#em-event-type-filter").length > 0){
        setTimeout(function(){
            $("#em-event-type-filter").select2({
                placeholder: "Event Type",
                tags: true,
            });
            $('select').trigger('select2:close');
        }, 500);
    }
    if($("#em-event-venue-filter").length > 0){
        setTimeout(function(){
            $("#em-event-venue-filter").select2({
                placeholder: "Event Site",
                tags: true,
            });
            $('select').trigger('select2:close');
        }, 500);
    }
    $('select').on('select2:close', function (evt) {
        var uldiv = $(this).siblings('span.select2').find('ul')
        var count = uldiv.find('li').length - 1;
        if(count > 1){
            uldiv.html("<li>"+count+" items selected</li>")
        }
    });
});
    
    
  /*********************** Filters bar ***************/ 
  
var eventFilters  = function() {
    $ = jQuery;
    if ($(".emagic").width() < 728)
    {
       $("#em_event_search_form").addClass("ep-event-filter-bar");
       $("#em_event_search_form").removeClass("ep-event-filter-bar-open");
    }
    else
    {
        $("#em_event_search_form").removeClass("ep-event-filter-bar");
        $("#em_event_search_form").addClass("ep-event-filter-bar-open");
    }
};

eventFilters();

$(window).resize(function(){
    eventFilters();   
});

  /*********************** Event Calendar Mobile views ***************/ 
  
var epCalendarSelector  = function() {
    $ = jQuery;
    if ($(".emagic").width() < 728)
    {
       $(".emagic .ep-all_event_calendar").addClass("ep-calendar-selector");

    }
    else
    {
        $(".emagic .ep-all_event_calendar").removeClass("ep-calendar-selector");

    }
};

epCalendarSelector();

$(window).resize(function(){
    epCalendarSelector();   
});


// load more masonry events
function em_masonry_load_more_events(){
    if(!jQuery(".ep-masonry-load-more").hasClass('clicked')) {
        jQuery(".ep-masonry-load-more").addClass('clicked');
        var curr_page = jQuery(".ep-masonry-load-more").data('curr_page');
        var loading = jQuery(".ep-masonry-load-more").data('loading');
        var loaded = jQuery(".ep-masonry-load-more").data('loaded');
        var max_page = jQuery(".ep-masonry-load-more").data('max_page');
        var dataTypes = jQuery('.ep-masonry-load-more').data('types');
        var sites = jQuery('.ep-masonry-load-more').data('sites');
        var types = getUrlParameter('em_types[]');
        /* if(!types && dataTypes){
            types = dataTypes;
        } */
        if(types.length > 0){
            dataTypes = types.toString();
        }
        var venue = getUrlParameterSingle('em_venue');
        if(!venue && sites){
            venue = sites;
        }
        var em_sd = getUrlParameterSingle('em_sd');
        var view = getUrlParameterSingle('events_view');
        var em_s = getUrlParameterSingle('em_s');
        var em_search = getUrlParameterSingle('em_search');
        var next_page = parseInt(curr_page+1);
        var upcoming = jQuery(".ep-masonry-load-more").data('upcoming');
        var show = jQuery(".ep-masonry-load-more").data('show');
        var i_events = jQuery(".ep-masonry-load-more").data('i_events');
        var recurring = jQuery(".ep-masonry-load-more").data('recurring');
        var data = {
            'action' : 'em_load_masonry_events_data',
            'page' : next_page,
            'upcoming' : upcoming,
            'em_types' : dataTypes,
            'em_venue' : venue ? venue : '',
            'em_sd': em_sd,
            'view': 'masonry',
            'em_search': em_search,
            'em_s':em_s,
            'show' : show,
            'i_events' : i_events,
            'recurring' : recurring
        }
        $.ajax({
            type: "POST",
            url: em_js_vars.ajax_url,
            data: data,
            beforeSend: function (xhr) {
                jQuery(".ep-load-more-button").text(loading);
            },
            success: function (response) {
                var data = response.data;
                if(data){
                    var oldhtml = jQuery(".em_masonry_view").html();
                    var newhtml = oldhtml + data.html;
                    jQuery(".em_masonry_view").html(newhtml);
                    jQuery(".ep-load-more-button").text(loaded);
                    jQuery(".ep-masonry-load-more").data('curr_page', next_page);
                    var container = document.querySelector( '#ms-container' );
                    var msnry;
                    imagesLoaded( container, function() {
                        msnry = new Masonry( container, {
                        // adjust to match your own block wrapper/container class/id name
                        itemSelector: '.grid-item',
                        });
                    });
                    if(max_page == next_page){
                        jQuery(".ep-masonry-load-more").remove();
                    }
                    
                    em_width_adjust(".ep-masonry-item-wrap");
                    jQuery(this).prop("disabled", true);
                    var emColor = jQuery('.emagic').find('a').css('color');
                    $(".em_masonry_view .ep-view-woocommerce-product svg").css('fill',emColor);
                    $(".em_event_disabled .em-masonry-footer").hide();
                    jQuery(".ep-masonry-load-more").removeClass('clicked');
                }
                else{
                    jQuery(".ep-masonry-load-more").hide();
                }
            }
        });
    }
}

function em_fes_event_remove(event_id) {
    var em_delete_msg = jQuery("#em-fes-delete-"+event_id).data('delete_msg');
    if(confirm(em_delete_msg)){
        jQuery.ajax({
            type: "POST",
            url: em_js_vars.ajax_url,
            data: {action: 'em_delete_fes_event', event_id: event_id},
            success: function (response) {
                var message = response.data.message;
                if(response.success){
                    jQuery("#em-fes-row-"+event_id).html('<td colspan="5">'+ message + '</td>');
                    setTimeout(function(){
                        jQuery("#em-fes-row-"+event_id).remove();
                    }, 5000);
                }
            }
        });
    }
}

function em_event_download_attendees(event_id){
    if(event_id){
        jQuery.ajax({
            type: "POST",
            url: em_js_vars.ajax_url,
            data: {action: 'em_export_submittion_attendees', event_id: event_id},
            success: function (response) {
                //if(response.status==200){
                    var link = document.createElement('a');
                    link.download = "attendees.csv";
                    link.href = 'data:application/csv;charset=utf-8,' + encodeURIComponent(response);
                    link.click();
                //}
            }
        });
    }
}

function em_view_load_more_events(btn,btnClass,container){
    if(!jQuery(btn).hasClass('clicked')) {
        jQuery(btn).addClass('clicked');
        var curr_page = jQuery(btn).data('curr_page');
        var loading = jQuery(btn).data('loading');
        var loaded = jQuery(btn).data('loaded');
        var max_page = jQuery(btn).data('max_page');
        var next_page = parseInt(curr_page+1);
        var upcoming = jQuery(btn).data('upcoming');
        var sites = jQuery(btn).data('sites');
        var dataTypes = jQuery(btn).data('types');
        var types = getUrlParameter('em_types[]');
        /* if(dataTypes){
            types.push(dataTypes);
        } */
        if(types.length > 0){
            dataTypes = types.toString();
        }
        var venue = getUrlParameterSingle('em_venue');
        if(!venue && sites){
            venue = sites;
        }
        var em_sd = getUrlParameterSingle('em_sd');
        var view = getUrlParameterSingle('events_view');
        var em_s = getUrlParameterSingle('em_s');
        var em_search = getUrlParameterSingle('em_search');
        var show = jQuery(btn).data('show');
        var i_events = jQuery(btn).data('i_events');
        var recurring = jQuery(btn).data('recurring');
        var data = {
            'action' : 'em_load_cards_events_data',
            'page' : next_page,
            'upcoming' : upcoming,
            /* 'em_types' : types, */
            'em_types' : dataTypes,
            'em_venue' : venue ? venue : '',
            'em_sd': em_sd,
            'view': 'card',
            'em_search': em_search,
            'em_s':em_s,
            'show': show,
            'i_events' : i_events,
            'recurring' : recurring
        }
        jQuery.ajax({
            type: "POST",
            url: em_js_vars.ajax_url,
            data: data,
            beforeSend: function (xhr) {
                jQuery(btnClass).text(loading);
            },
            success: function (response) {
                var data = response.data;
                if(data){
                    var oldhtml = jQuery(container).html();
                    var newhtml = oldhtml + data.html;
                    jQuery(container).html(newhtml);
                    jQuery(btnClass).text(loaded);
                    jQuery(btn).data('curr_page', next_page);
                    em_width_adjust(".em_card");
                    em_card_wrap_adjust();
                    var emColor = jQuery('.emagic').find('a').css('color');
                    $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill', emColor);
                    $(".emagic .em_card .em-cards-footer .em_eventpage_register button.em_event-booking").css('color', emColor);
                    $(".em_event_disabled .kf_ticket_price,.em_event_disabled .em-cards-footer,.em_event_disabled .kf-upcoming-event-price,.em_event_disabled .kf-upcoming-event-booking").hide();
                    if(max_page == next_page){
                        jQuery(btn).remove();
                    }
                    jQuery(btn).removeClass('clicked');
                }
                else{
                    jQuery(".ep-view-load-more").hide();
                }
            }
        });
    }
}

function em_card_wrap_adjust(){
    // Event card wrapping
    if($('.emagic .em_card.em_card2').length > 0) {
        var cards = $('.emagic .em_card.em_card2');
        if (cards.length) {
            for (var i = 0; i < cards.length; i += 2) {
                cards.slice(i, i + 2).wrapAll('<div class="em-cards-wrap"></div>');
            }
        }
    }
    if($('.emagic .em_card.em_card3').length > 0) {
        var cards = $('.emagic .em_card.em_card3');
        if (cards.length) {
            for (var i = 0; i < cards.length; i += 3) {
                cards.slice(i, i + 3).wrapAll('<div class="em-cards-wrap"></div>');
            }
        }
    }
    if($('.emagic .em_card.em_card4').length > 0) {
        var cards = $('.emagic .em_card.em_card4');
        if (cards.length) {
            for (var i = 0; i < cards.length; i += 4) {
                if(cards[i].closest('.em-cards-wrap') !== null){
                    var wrapLenth = $(cards[i]).closest('.em-cards-wrap').find('.em_card4').length;
                    if(wrapLenth != 4){
                        cards.slice(i, i + 4).wrapAll('<div class="em-cards-wrap"></div>');
                    }
                }
                else{
                    cards.slice(i, i + 4).wrapAll('<div class="em-cards-wrap"></div>');
                }
            }
        }
    }
}

function getUrlParameterSingle(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
}
function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1));
    var array =[]
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) {
            array.push(sParameterName[1]);
        }
    }
    return array;
}

function em_load_map_nws(page,element) {  
    $ = jQuery;
    if(!em_map_info.gmap_uri) {
        $("#" + element).remove();
        return;
    }
    if (!em_gmap_loaded && !(typeof google === 'object' && typeof google.maps === 'object')){
        em_gmap_loaded = true;
        $.getScript(em_map_info.gmap_uri)
        .done(function( script, textStatus ) {
            em_load_map_div_nws(page,element);
        })
        .fail(function( jqxhr, settings, exception ) {
          alert('We are unable to load map.');
        });
    } else {
        em_load_map_div_nws(page,element);
    }
}

function em_load_map_div_nws(page,divId) {
    $ = jQuery;
    switch(page) {
        case "single_event":  
            var venue_id = $("#" + divId).attr("data-venue-id");
            em_event_map_canvas(divId,venue_id); 
            break;

        case "user_profile": 
            var venue_id = $("#" + divId).attr("data-venue-ids");
            em_user_event_venue(divId,venue_id);   
            break;
        case "venues": 
            em_show_venue_map("venues_map_canvas"); 
            break; 
        case "venue_widget" : 
            em_show_venue_map("em_widget_venues_map_canvas"); 
            break;
        case "single_venue":  
            var venue_id = $("#" + divId).attr("data-venue-id");
            em_single_venue_map_canvas(divId,venue_id); 
            break;
        case "booking" : 
            var venue_id = $("#" + divId).attr("data-venue-id");
            em_booking_map_canvas("em_booking_map_canvas",venue_id); 
            break;
   }
}

// load more list events
function em_list_load_more_events(){
    if(!jQuery(".ep-masonry-load-more").hasClass('clicked')) {
        jQuery(".ep-masonry-load-more").addClass('clicked');
        var curr_page = jQuery(".ep-masonry-load-more").data('curr_page');
        var loading = jQuery(".ep-masonry-load-more").data('loading');
        var loaded = jQuery(".ep-masonry-load-more").data('loaded');
        var max_page = jQuery(".ep-masonry-load-more").data('max_page');
        var next_page = parseInt(curr_page+1);
        var upcoming = jQuery(".ep-masonry-load-more").data('upcoming');
        var sites = jQuery(".ep-masonry-load-more").data('sites');
        var dataTypes = jQuery(".ep-masonry-load-more").data('types');
        var types = getUrlParameter('em_types[]');
        /* if(dataTypes){
            types.push(dataTypes);
        } */
        if(types.length > 0){
            dataTypes = types.toString();
        }
        var venue = getUrlParameterSingle('em_venue');
        if(!venue && sites){
            venue = sites;
        }
        var em_sd = getUrlParameterSingle('em_sd');
        var view = getUrlParameterSingle('events_view');
        var em_s = getUrlParameterSingle('em_s');
        var em_search = getUrlParameterSingle('em_search');
        var show = jQuery(".ep-masonry-load-more").data('show');
        var month_id = $(".ep-masonry-load-more").data('month_id');
        var i_events = $(".ep-masonry-load-more").data('i_events');
        var recurring = jQuery(".ep-masonry-load-more").data('recurring');
        var data = {
            'action' : 'em_load_list_events_data',
            'page' : next_page,
            'upcoming' : upcoming,
           /*  'em_types' : types, */
            'em_types' : dataTypes,
            'em_venue' : venue ? venue : '',
            'em_sd': em_sd,
            'view': 'card',
            'em_search': em_search,
            'em_s':em_s,
            'show': show,
            'last_month_id': month_id,
            'i_events': i_events,
            'recurring' : recurring
        }
        $.ajax({
            type: "POST",
            url: em_js_vars.ajax_url,
            data: data,
            beforeSend: function (xhr) {
                $(".ep-load-more-button").text(loading);
            },
            success: function (response) {
                var data = response.data;
                if(data){
                    var oldhtml = $(".ep-event-list-standard").html();
                    var newhtml = oldhtml + data.html;
                    $(".ep-event-list-standard").html(newhtml);
                    $(".ep-load-more-button").text(loaded);
                    $(".ep-masonry-load-more").data('curr_page', next_page);
                    $(".ep-masonry-load-more").data('month_id', data.last_month_id);
                    var emColor = jQuery('.emagic').find('a').css('color');
                    $(".em_list_view .ep-event-list-standard .ep-listed-event-month-tag").css('border-color',emColor);
                    $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
                    $(".ep-view-woocommerce-product svg").css('fill',emColor);
                    $(".em_event_disabled .ep-list-view-footer").hide();
                    if(max_page == next_page){
                        $(".ep-masonry-load-more").remove();
                    }
                    em_width_adjust(".ep-list-item-wrap");
                    $(this).prop("disabled", true);
                    jQuery(".ep-masonry-load-more").removeClass('clicked');
                }
            }
        });
    }
}


jQuery(document).ready(function($) {
    var a = jQuery('.emagic .kf-event-organizers .kf-organizer-card');
    for( var i = 0; i < a.length; i+=2 ) {
    a.slice(i, i+2).wrapAll('<div class="ep-organizer-cards-wrap"></div>');
    }
});

/* load more performers card */
function em_load_more_performers_card(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var featured = jQuery(btn).data('featured');
    var cols = jQuery(btn).data('cols');
    var next_page = parseInt(curr_page+1);
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var data = {
        'action' : 'em_load_performers_card_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}
/* load more performers box */
function em_load_more_performers_box(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var featured = jQuery(btn).data('featured');
    var cols = jQuery(btn).data('cols');
    var next_page = parseInt(curr_page+1);
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'box';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var bnum = jQuery(btn).data('bnum');
    var data = {
        'action' : 'em_load_performers_box_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
        'cols' : cols, 
        'bnum' : bnum, 
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}

// load more performer list
function em_load_more_performers_list(){
    var curr_page = jQuery(".ep-p-lists-load-more").data('curr_page');
    var loading = jQuery(".ep-p-lists-load-more").data('loading');
    var loaded = jQuery(".ep-p-lists-load-more").data('loaded');
    var max_page = jQuery(".ep-p-lists-load-more").data('max_page');
    var featured = jQuery(".ep-p-lists-load-more").data('featured');
    var next_page = parseInt(curr_page+1);
    
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'list';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(".ep-p-lists-load-more").data('show');
    var data = {
        'action' : 'em_load_performers_list_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
    }
    $.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            $(".ep-load-more-button").text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = $(".ep-performer-list-wrap").html();
                var newhtml = oldhtml + data.html;
                $(".ep-performer-list-wrap").html(newhtml);
                $(".ep-load-more-button").text(loaded);
                $(".ep-p-lists-load-more").data('curr_page', next_page);
                $(".ep-p-lists-load-more").data('month_id', data.last_month_id);
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em_list_view .ep-performer-list-wrap .ep-listed-event-month-tag").css('border-color',emColor);
                $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
                $(".ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    $(".ep-p-lists-load-more").remove();
                }
                
                em_width_adjust(".ep-list-item-wrap");                             
                $(this).prop("disabled", true);
            }
        }
    });
}

function em_load_more_performer_events_card_block(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var next_page = parseInt(curr_page+1);
    var p_id = jQuery(btn).data('p_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var cols = jQuery(btn).data('cols');
    var data = {
        'action' : 'em_load_performer_events_card_block',
        'page' : next_page,
        'p_id' : p_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}

function em_load_more_performer_events_list_block(){
    var curr_page = jQuery(".ep-masonry-load-more").data('curr_page');
    var loading = jQuery(".ep-masonry-load-more").data('loading');
    var loaded = jQuery(".ep-masonry-load-more").data('loaded');
    var max_page = jQuery(".ep-masonry-load-more").data('max_page');
    var next_page = parseInt(curr_page+1);
    var p_id = jQuery(".ep-masonry-load-more").data('p_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'list';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(".ep-masonry-load-more").data('show');
    var month_id = $(".ep-masonry-load-more").data('month_id');
    var data = {
        'action' : 'em_load_performer_events_list_block',
        'page' : next_page,
        'p_id' : p_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'last_month_id': month_id,
    }
    $.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            $(".ep-load-more-button").text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = $(".ep-performer-event-list-standard").html();
                var newhtml = oldhtml + data.html;
                $(".ep-performer-event-list-standard").html(newhtml);
                $(".ep-load-more-button").text(loaded);
                $(".ep-masonry-load-more").data('curr_page', next_page);
                $(".ep-masonry-load-more").data('month_id', data.last_month_id);
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em_list_view .ep-performer-event-list-standard .ep-listed-event-month-tag").css('border-color',emColor);
                $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
                $(".ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    $(".ep-masonry-load-more").remove();
                }
                
                em_width_adjust(".ep-list-item-wrap");                             
                $(this).prop("disabled", true);
            }
        }
    });
}

function em_load_more_performer_events_mini_list_block(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var next_page = parseInt(curr_page+1);
    var p_id = jQuery(btn).data('p_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var cols = jQuery(btn).data('cols');
    var data = {
        'action' : 'em_load_performer_events_mini_list_block',
        'page' : next_page,
        'p_id' : p_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}

/* load more types card */
function em_load_more_types_card(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var featured = jQuery(btn).data('featured');
    var cols = jQuery(btn).data('cols');
    var next_page = parseInt(curr_page+1);
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var data = {
        'action' : 'em_load_types_card_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(btn).hide();
            }
        }
    });
}
/* load more types box */
function em_load_more_types_box(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var featured = jQuery(btn).data('featured');
    var cols = jQuery(btn).data('cols');
    var next_page = parseInt(curr_page+1);
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'box';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var bnum = jQuery(btn).data('bnum');
    var data = {
        'action' : 'em_load_types_box_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
        'cols' : cols, 
        'bnum' : bnum
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(btn).hide();
            }
        }
    });
}

// load more types list
function em_load_more_types_list(){
    var curr_page = jQuery(".ep-type-lists-load-more").data('curr_page');
    var loading = jQuery(".ep-type-lists-load-more").data('loading');
    var loaded = jQuery(".ep-type-lists-load-more").data('loaded');
    var max_page = jQuery(".ep-type-lists-load-more").data('max_page');
    var featured = jQuery(".ep-type-lists-load-more").data('featured');
    var next_page = parseInt(curr_page+1);
    
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'list';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(".ep-type-lists-load-more").data('show');
    var data = {
        'action' : 'em_load_types_list_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
    }
    $.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            $(".ep-load-more-button").text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = $(".ep-type-list-wrap").html();
                var newhtml = oldhtml + data.html;
                $(".ep-type-list-wrap").html(newhtml);
                $(".ep-load-more-button").text(loaded);
                $(".ep-type-lists-load-more").data('curr_page', next_page);
                $(".ep-type-lists-load-more").data('month_id', data.last_month_id);
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em_list_view .ep-type-list-wrap .ep-listed-event-month-tag").css('border-color',emColor);
                $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
                $(".ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    $(".ep-type-lists-load-more").remove();
                }
                
                em_width_adjust(".ep-list-item-wrap");                             
                $(this).prop("disabled", true);
            }
        }
    });
}

function em_load_more_type_events_card_block(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var next_page = parseInt(curr_page+1);
    var p_id = jQuery(btn).data('p_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var cols = jQuery(btn).data('cols');
    var data = {
        'action' : 'em_load_type_events_card_block',
        'page' : next_page,
        'p_id' : p_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(btn).hide();
            }
        }
    });
}

function em_load_more_type_events_list_block(){
    var curr_page = jQuery(".ep-masonry-load-more").data('curr_page');
    var loading = jQuery(".ep-masonry-load-more").data('loading');
    var loaded = jQuery(".ep-masonry-load-more").data('loaded');
    var max_page = jQuery(".ep-masonry-load-more").data('max_page');
    var next_page = parseInt(curr_page+1);
    var p_id = jQuery(".ep-masonry-load-more").data('p_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'list';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(".ep-masonry-load-more").data('show');
    var month_id = $(".ep-masonry-load-more").data('month_id');
    var data = {
        'action' : 'em_load_type_events_list_block',
        'page' : next_page,
        'p_id' : p_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'last_month_id': month_id,
    }
    $.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            $(".ep-load-more-button").text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = $(".ep-performer-event-list-standard").html();
                var newhtml = oldhtml + data.html;
                $(".ep-performer-event-list-standard").html(newhtml);
                $(".ep-load-more-button").text(loaded);
                $(".ep-masonry-load-more").data('curr_page', next_page);
                $(".ep-masonry-load-more").data('month_id', data.last_month_id);
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em_list_view .ep-performer-event-list-standard .ep-listed-event-month-tag").css('border-color',emColor);
                $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
                $(".ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    $(".ep-masonry-load-more").remove();
                }
                
                em_width_adjust(".ep-list-item-wrap");                             
                $(this).prop("disabled", true);
            }
        }
    });
}

function em_load_more_type_events_mini_list_block(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var next_page = parseInt(curr_page+1);
    var p_id = jQuery(btn).data('p_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var cols = jQuery(btn).data('cols');
    var data = {
        'action' : 'em_load_type_events_mini_list_block',
        'page' : next_page,
        'p_id' : p_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}

/* load more organizers card */
function em_load_more_organizers_card(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var featured = jQuery(btn).data('featured');
    var cols = jQuery(btn).data('cols');
    var next_page = parseInt(curr_page+1);
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var data = {
        'action' : 'em_load_organizers_card_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}
/* load more organizers box */
function em_load_more_organizers_box(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var featured = jQuery(btn).data('featured');
    var cols = jQuery(btn).data('cols');
    var next_page = parseInt(curr_page+1);
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'box';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var bnum = jQuery(btn).data('bnum');
    var data = {
        'action' : 'em_load_organizers_box_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
        'cols' : cols,
        'bnum' : bnum
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}

// load more organizers list
function em_load_more_organizers_list(){
    var curr_page = jQuery(".ep-p-lists-load-more").data('curr_page');
    var loading = jQuery(".ep-p-lists-load-more").data('loading');
    var loaded = jQuery(".ep-p-lists-load-more").data('loaded');
    var max_page = jQuery(".ep-p-lists-load-more").data('max_page');
    var featured = jQuery(".ep-p-lists-load-more").data('featured');
    var next_page = parseInt(curr_page+1);
    
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'list';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(".ep-p-lists-load-more").data('show');
    var data = {
        'action' : 'em_load_organizers_list_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
    }
    $.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            $(".ep-load-more-button").text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = $(".ep-organizer-list-wrap").html();
                var newhtml = oldhtml + data.html;
                $(".ep-organizer-list-wrap").html(newhtml);
                $(".ep-load-more-button").text(loaded);
                $(".ep-p-lists-load-more").data('curr_page', next_page);
                $(".ep-p-lists-load-more").data('month_id', data.last_month_id);
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em_list_view .ep-organizer-list-wrap .ep-listed-event-month-tag").css('border-color',emColor);
                $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
                $(".ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    $(".ep-p-lists-load-more").remove();
                }
                
                em_width_adjust(".ep-list-item-wrap");                             
                $(this).prop("disabled", true);
            }
        }
    });
}

function em_load_more_organizer_events_card_block(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var next_page = parseInt(curr_page+1);
    var o_id = jQuery(btn).data('o_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var cols = jQuery(btn).data('cols');
    var data = {
        'action' : 'em_load_organizer_events_card_block',
        'page' : next_page,
        'o_id' : o_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}

function em_load_more_organizer_events_list_block(){
    var curr_page = jQuery(".ep-masonry-load-more").data('curr_page');
    var loading = jQuery(".ep-masonry-load-more").data('loading');
    var loaded = jQuery(".ep-masonry-load-more").data('loaded');
    var max_page = jQuery(".ep-masonry-load-more").data('max_page');
    var next_page = parseInt(curr_page+1);
    var o_id = jQuery(".ep-masonry-load-more").data('o_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'list';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(".ep-masonry-load-more").data('show');
    var month_id = $(".ep-masonry-load-more").data('month_id');
    var data = {
        'action' : 'em_load_organizer_events_list_block',
        'page' : next_page,
        'o_id' : o_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'last_month_id': month_id,
    }
    $.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            $(".ep-load-more-button").text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = $(".ep-organizer-event-list-standard").html();
                var newhtml = oldhtml + data.html;
                $(".ep-organizer-event-list-standard").html(newhtml);
                $(".ep-load-more-button").text(loaded);
                $(".ep-masonry-load-more").data('curr_page', next_page);
                $(".ep-masonry-load-more").data('month_id', data.last_month_id);
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em_list_view .ep-organizer-event-list-standard .ep-listed-event-month-tag").css('border-color',emColor);
                $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
                $(".ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    $(".ep-masonry-load-more").remove();
                }
                
                em_width_adjust(".ep-list-item-wrap");                             
                $(this).prop("disabled", true);
            }
        }
    });
}

function em_load_more_organizer_events_mini_list_block(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var next_page = parseInt(curr_page+1);
    var p_id = jQuery(btn).data('p_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var cols = jQuery(btn).data('cols');
    var data = {
        'action' : 'em_load_organizer_events_mini_list_block',
        'page' : next_page,
        'p_id' : p_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}

/* load more venues card */
function em_load_more_venues_card(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var featured = jQuery(btn).data('featured');
    var cols = jQuery(btn).data('cols');
    var next_page = parseInt(curr_page+1);
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var data = {
        'action' : 'em_load_venues_card_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(btn).hide();
            }
        }
    });
}
/* load more venues box */
function em_load_more_venues_box(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var featured = jQuery(btn).data('featured');
    var cols = jQuery(btn).data('cols');
    var next_page = parseInt(curr_page+1);
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'box';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var bnum = jQuery(btn).data('bnum');
    var data = {
        'action' : 'em_load_venues_box_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
        'cols' : cols,
        'bnum' : bnum 
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(btn).hide();
            }
        }
    });
}

// load more venues list
function em_load_more_venues_list(){
    var curr_page = jQuery(".ep-venue-lists-load-more").data('curr_page');
    var loading = jQuery(".ep-venue-lists-load-more").data('loading');
    var loaded = jQuery(".ep-venue-lists-load-more").data('loaded');
    var max_page = jQuery(".ep-venue-lists-load-more").data('max_page');
    var featured = jQuery(".ep-venue-lists-load-more").data('featured');
    var next_page = parseInt(curr_page+1);
    
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'list';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(".ep-venue-lists-load-more").data('show');
    var data = {
        'action' : 'em_load_venues_list_data',
        'page' : next_page,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'featured': featured,
    }
    $.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            $(".ep-load-more-button").text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = $(".ep-venue-list-wrap").html();
                var newhtml = oldhtml + data.html;
                $(".ep-venue-list-wrap").html(newhtml);
                $(".ep-load-more-button").text(loaded);
                $(".ep-venue-lists-load-more").data('curr_page', next_page);
                $(".ep-venue-lists-load-more").data('month_id', data.last_month_id);
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em_list_view .ep-venue-list-wrap .ep-listed-event-month-tag").css('border-color',emColor);
                $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
                $(".ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    $(".ep-venue-lists-load-more").remove();
                }
                
                em_width_adjust(".ep-list-item-wrap");                             
                $(this).prop("disabled", true);
            }
        }
    });
}

function em_load_more_venue_events_card_block(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var next_page = parseInt(curr_page+1);
    var venue_id = jQuery(btn).data('venue_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var cols = jQuery(btn).data('cols');
    var data = {
        'action' : 'em_load_venue_events_card_block',
        'page' : next_page,
        'venue_id' : venue_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(btn).hide();
            }
        }
    });
}

function em_load_more_venue_events_list_block(){
    var curr_page = jQuery(".ep-masonry-load-more").data('curr_page');
    var loading = jQuery(".ep-masonry-load-more").data('loading');
    var loaded = jQuery(".ep-masonry-load-more").data('loaded');
    var max_page = jQuery(".ep-masonry-load-more").data('max_page');
    var next_page = parseInt(curr_page+1);
    var venue_id = jQuery(".ep-masonry-load-more").data('venue_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'list';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(".ep-masonry-load-more").data('show');
    var month_id = $(".ep-masonry-load-more").data('month_id');
    var data = {
        'action' : 'em_load_venue_events_list_block',
        'page' : next_page,
        'venue_id' : venue_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'last_month_id': month_id,
    }
    $.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            $(".ep-load-more-button").text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = $(".ep-venue-event-list-standard").html();
                var newhtml = oldhtml + data.html;
                $(".ep-venue-event-list-standard").html(newhtml);
                $(".ep-load-more-button").text(loaded);
                $(".ep-masonry-load-more").data('curr_page', next_page);
                $(".ep-masonry-load-more").data('month_id', data.last_month_id);
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em_list_view .ep-venue-event-list-standard .ep-listed-event-month-tag").css('border-color',emColor);
                $(".em-list-view-venue-details .ep-list-event-location svg").css('fill',emColor);
                $(".ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    $(".ep-masonry-load-more").remove();
                }
                
                em_width_adjust(".ep-list-item-wrap");                             
                $(this).prop("disabled", true);
            }
        }
    });
}

function em_load_more_venue_events_mini_list_block(btn,btnClass,container){
    var curr_page = jQuery(btn).data('curr_page');
    var loading = jQuery(btn).data('loading');
    var loaded = jQuery(btn).data('loaded');
    var max_page = jQuery(btn).data('max_page');
    var next_page = parseInt(curr_page+1);
    var venue_id = jQuery(btn).data('venue_id');
    var em_sd = getUrlParameterSingle('em_sd');
    var view = 'card';
    var em_s = getUrlParameterSingle('em_s');
    var em_search = getUrlParameterSingle('em_search');
    var show = jQuery(btn).data('show');
    var cols = jQuery(btn).data('cols');
    var data = {
        'action' : 'em_load_venue_events_mini_list_block',
        'page' : next_page,
        'venue_id' : venue_id,
        'em_sd': em_sd,
        'view': view,
        'em_search': em_search,
        'em_s':em_s,
        'show': show,
        'cols': cols,
    }
    jQuery.ajax({
        type: "POST",
        url: em_js_vars.ajax_url,
        data: data,
        beforeSend: function (xhr) {
            jQuery(btnClass).text(loading);
        },
        success: function (response) {
            var data = response.data;
            if(data){
                var oldhtml = jQuery(container).html();
                var newhtml = oldhtml + data;
                jQuery(container).html(newhtml);
                jQuery(btnClass).text(loaded);
                jQuery(btn).data('curr_page', next_page);
                em_width_adjust(".em_card");
                em_card_wrap_adjust();
                var emColor = jQuery('.emagic').find('a').css('color');
                $(".em-cards-wrap .ep-view-woocommerce-product svg").css('fill',emColor);
                if(max_page == next_page){
                    jQuery(btn).remove();
                }
            }
            else{
                jQuery(".ep-view-load-more").hide();
            }
        }
    });
}