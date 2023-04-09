jQuery(document).ready(function () {
    $=jQuery;
    $('#pm-change-password').click(function () {
        em_call_popup("#pm-change-password");
    });

    $(".em_photo_gallery a").colorbox({width: "75%", height: "75%"});
    setTimeout(function () {
        jQuery(".em_venue_info").ep-tabs();
    }, 3000);

    if(em_local_event_objects.social_sharing==1 && em_local_event_objects.fb_api!=''){
        $('.em_share_fb').click(function (e){
        e.preventDefault();
        FB.ui(
                {
                    method: 'share',
                    href: em_local_event_objects.fb_event_href,
                });
        });
    }
    
    em_load_map('single_event', 'em_event_map_canvas');
});

function em_call_popup(dialog) {
    var pmId = dialog + "-dialog";
    jQuery(pmId).siblings('.pm-popup-mask').show();
    jQuery(pmId).show();
    jQuery('.pm-popup-container').css("animation", "pm-popup-in 0.3s ease-out 1");
}
jQuery('.pm-popup-close, .pm-popup-mask').click(function () {
    jQuery('.pm-popup-mask').hide();
    jQuery('.pm-popup-mask').next().hide();
});

if(em_local_event_objects.social_sharing==1 && em_local_event_objects.fb_api!=''){
    window.fbAsyncInit = function () {
    FB.init({
        appId: em_local_event_objects.fb_api,
        status: true, // check login status
        cookie: true, // enable cookies to allow the server to access the session
        xfbml: true, // parse XFBML
        version: 'v2.8'
        });
    };

    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    (function () {
        var e = document.createElement('script');
        e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
        e.async = true;
        var fb_root = document.getElementById('em_fb_root');
        if (fb_root) {
            fb_root.appendChild(e);
        }
    }());
}


//}




