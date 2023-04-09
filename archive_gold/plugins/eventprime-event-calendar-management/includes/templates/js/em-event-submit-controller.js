eventMagicApp.controller('emEventSubmitCtrl', function($scope, $http, EMRequest, TermUtility) {
    $scope.data = {};
    $scope.formErrors = new Array();
    $scope.submitted = false;
    $scope.chkFormSubmitted = false;
    $scope.requestInProgress = false;
    $scope.showNewPerformerBlock = 0;
    $scope.showNewOrganizerBlock = 0;
    $ = jQuery;
    $scope.data.term = {};
   
    $scope.progressStart = function() {
        $scope.requestInProgress = true;
    }
    
    $scope.progressStop = function() {
        $scope.requestInProgress = false;
    }
    
    // Called at initialization
    $scope.initialize = function(event_id) {
        $scope.progressStart();

        // HTTP request to load data
        if(event_id){
            $scope.data.event_id = event_id;
        }
        EMRequest.send('em_frontend_event_submit_form_strings',$scope.data).then(function(response){
            var responseBody = response.data;
            if(responseBody.success){
                $scope.data = responseBody.data;
                if($scope.data.settings.id && $scope.data.settings.id == event_id){
                    $scope.data.settings.event_id = $scope.data.settings.id;
                    $("#em_color_picker").css("background-color","#" + $scope.data.settings.event_text_color);
                    if($('#event_description').is(':visible')){
                        $('#event_description').html($scope.data.settings.description);
                    } else {
                        if(typeof tinymce!="undefined"){
                            tinymce.get('event_description').setContent($scope.data.settings.description);
                        }
                    }
                    $scope.PreviewImage = $scope.data.settings.cover_image_data;
                    if($scope.data.settings.organizer){
                        //$scope.showNewOrganizerBlock = 1;
                        $scope.data.settings.organizer = $scope.data.settings.organizer;
                        $scope.data.settings.new_organizer_name = $scope.data.settings.organizer_name;
                        if($scope.data.settings.organizer_phones){
                            $scope.data.settings.new_organizer_phones = $scope.data.settings.organizer_phones.join(',');
                        }
                        if($scope.data.settings.organizer_emails){
                            $scope.data.settings.new_organizer_emails = $scope.data.settings.organizer_emails.join(',');
                        }
                        if($scope.data.settings.organizer_websites){
                            $scope.data.settings.new_organizer_websites = $scope.data.settings.organizer_websites.join(',');
                        }
                        $scope.data.settings.new_organizer_hide_organizer = $scope.data.settings.hide_organizer;
                    }
                }
                else{
                    $scope.data.settings.venue = $scope.data.settings.venues[0].id;
                    $scope.data.settings.event_type = $scope.data.settings.event_types[0].id;
                    //$scope.data.settings.organizer = $scope.data.settings.event_organizers[0].id;
                }
                $scope.data.settings.new_event_type = [];
                if($scope.data.settings.frontend_submission_required){
                    if($scope.data.settings.frontend_submission_required.fes_event_description == 1){
                        $("#event_description").attr('ng-model', 'data.settings.event_description');
                        $("#event_description").attr('required', true);
                    }
                }
                $scope.initializeDateTimePickers($scope.data.settings.datepicker_format);
                $scope.setupSlider();
            }
            $scope.progressStop();
        });
    }
    
    $scope.initializeDateTimePickers = function(datepicker_format) {
        var minDate = new Date();
        var maxDate;
        $("#event_start_date").datetimepicker({controlType: 'select',oneLine: true,changeYear: true,timeFormat: 'HH:mm',dateFormat: datepicker_format, timeInput: true});
        $("#event_end_date").datetimepicker({controlType: 'select',oneLine: true,changeYear: true,timeFormat: 'HH:mm',dateFormat: datepicker_format, timeInput: true});
        $("#event_start_booking_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true,dateFormat: datepicker_format, timeInput: true});
        $("#event_last_booking_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true,dateFormat: datepicker_format, timeInput: true});
    }
    
    $scope.submitEvent = function(isValid){
        $scope.chkFormSubmitted = true;
        if(isValid){
            if($('#event_description').is(':visible')){
                $scope.data.settings.description = $('#event_description').val();
            } else {
                if(typeof tinymce!="undefined"){
                    $scope.data.settings.description = tinymce.get('event_description').getContent();
                }
            }
            $newVenueAddressInp = $("#em-pac-input");
            if($newVenueAddressInp.length > 0){
                $scope.data.settings.new_venue_address = $newVenueAddressInp.val();
            }
            if($scope.data.settings.event_type == "new_event_type"){
                if($('#new_event_type_description').is(':visible')){
                    $scope.data.settings.new_event_type_description = $('#new_event_type_description').val();
                } else {
                    if(typeof tinymce!="undefined"){
                        $scope.data.settings.new_event_type_description = tinymce.get('new_event_type_description').getContent();
                    }
                }
            }
            if($scope.data.settings.new_performer){
                if($('#new_performer_description').is(':visible')){
                    $scope.data.settings.new_performer_description = $('#new_performer_description').val();
                } else {
                    if(typeof tinymce!="undefined"){
                        $scope.data.settings.new_performer_description= tinymce.get('new_performer_description').getContent();
                    }
                }
            }
            if($scope.data.settings.organizer == "new_event_organizer"){
                if($('#new_event_organizer_description').is(':visible')){
                    $scope.data.settings.new_event_organizer_description = $('#new_event_organizer_description').val();
                } else {
                    if(typeof tinymce!="undefined"){
                        $scope.data.settings.new_event_organizer_description = tinymce.get('new_event_organizer_description').getContent();
                    }
                }
            }
            $scope.progressStart();
            EMRequest.send('em_submit_frontend_event',$scope.data.settings).then(function(response){
                $scope.progressStop();
                var responseBody= response.data;
                if(responseBody.success){
                    EMRequest.send('em_event_submitted_mail',responseBody.data).then(function(response){});
                    $scope.submitted = true;
                } else {
                    if(responseBody.data.hasOwnProperty('errors')){
                        $scope.formErrors= responseBody.data.errors;
                    }
                }
                jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
            });
        }        
    }

    $scope.getFrontCapacity = function(key){
        if($scope.data.settings.venue == 'new_venue'){
            if(key != ''){
                $scope.setupMap();
            }
        }
    }

    // setup map on add new event site
    $scope.setupMap = function () {
        var gmarkers = []; // To store all the markers
        // Initializing Map
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 8,
            center: {lat: 40.731, lng: -73.997}
        });
        var latEl = $("#em_venue_lat");
        var lngEl = $("#em_venue_long");
        var addressEl=$("#em-pac-input");
        input = document.getElementById('em-pac-input'); //Searchbox
        var types = document.getElementById('type-selector');
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(types);
        var geocoder = new google.maps.Geocoder;
        var infowindow = new google.maps.InfoWindow;
        var autocomplete = new google.maps.places.SearchBox(input);
        autocomplete.bindTo('bounds', map);
        var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });
        if(input.value!=''){
            google.maps.event.trigger(autocomplete, 'places_changed');
        }
        resetMarkers=function(){
            for (i = 0; i < gmarkers.length; i++) {
                gmarkers[i].setMap(null);
            }
        }
        updateLatLngInput=function(lat,lng,zoom){
            $scope.data.settings.lat=lat;
            $scope.data.settings.lng=lng;
            $scope.data.settings.zoom_level=zoom;
            $scope.$apply();
        }
        // Listener for searchbox changes.
        autocomplete.addListener('places_changed', function () {
            resetMarkers();
            var places = autocomplete.getPlaces();
            if(places.length==0)
                return;
            var place=places[0];
            if (!place.geometry) {
                return;
            }
            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(8);  // Why 17? Because it looks good.
            }
            var marker = new google.maps.Marker({
                position: place.geometry.location,
                map: map
            });
            var address = '';
            if (place.address_components) {
                address = [
                    (place.address_components[0] && place.address_components[0].short_name || ''),
                    (place.address_components[1] && place.address_components[1].short_name || ''),
                    (place.address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
            }
            infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
            infowindow.open(map,marker);
            gmarkers.push(marker);
            updateLatLngInput(place.geometry.location.lat(),place.geometry.location.lng(),map.getZoom());
        });
    
        map.addListener('click', function (e) {
            resetMarkers();
            latLng = e.latLng;
            var latlng = {lat: parseFloat(latLng.lat()), lng: parseFloat(latLng.lng())};
            geocoder.geocode({'location': latlng}, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        map.setZoom(13);
                        var marker = new google.maps.Marker({
                            position: latlng,
                            map: map
                        });
                        input.value = results[1].formatted_address;
                        infowindow.setContent(results[1].formatted_address);
                        infowindow.open(map, marker);
                        gmarkers.push(marker);
                    }
                }
            });
        });
    }

    // remove featured image on click on X button
    $scope.removeFeatureImage = function(){
        angular.element(".ep-fes-featured-file").val(null);
        $scope.PreviewImage = null;
        $scope.data.settings.cover_image_id = '';
        $scope.data.settings.cover_image_data = '';
    }

    // remove organizer image on click on X button
    $scope.removeOrganizerImage = function(){
        angular.element(".ep-fes-organizer-file").val(null);
        $scope.PreviewOrganizerImage = null;
        $scope.data.settings.organizer_image_id = '';
        $scope.data.settings.organizer_image = '';
    }

    // remove performer image on click on X button
    $scope.removePerformerImage = function(){
        angular.element(".ep-fes-performer-file").val(null);
        $scope.PreviewPerformerImage = null;
        $scope.data.settings.performer_image_id = '';
        $scope.data.settings.performer_image = '';
    }

    $scope.setupSlider= function(){
        var ranges= [21,28];
        $scope.data.settings.new_event_type.custom_group = '';
        if($scope.data.settings.new_event_type.custom_group){
            if($scope.data.settings.new_event_type.custom_group.indexOf("-")>=0){
                rangeArr= $scope.data.settings.new_event_type.custom_group.split("-");
                ranges[0]=rangeArr[0];
                ranges[1]= rangeArr[1];
            }
        }
        if($scope.data.settings.new_event_type.custom_group==null){
            $scope.data.settings.new_event_type.custom_group=ranges[0]+ "-" + ranges[1];
        }
        $("#slider").slider({       
            create: function() {        
                $(this).slider(     
                {   
                    range: true,
                    min: 0,     
                    max: 100,
                    values: ranges,
                    slide: function(event,ui){
                        $scope.data.settings.new_event_type.custom_group = ui.values[0] +  "-" + ui.values[1]; 
                        $scope.$apply();
                    },
                });     
            }       
        });     
    }

    $scope.showNewPerformer = function(val){
        $scope.showNewPerformerBlock = val;
    }

    $scope.showNewOrganizer = function(val){
        $scope.showNewOrganizerBlock = val;
        if(val){
            $scope.addPhone();
            $scope.addEmail();
            $scope.addWebsite();
        }else{
            $scope.removePhone();
            $scope.removeEmail();
            $scope.removeWebsites();
        }
    }

    /*
     * Add functions for adding new contact fields for Organizers
     */
    $scope.addPhone = function() {
        $scope.data.settings.organizer_phones.push('');
    }
    
    $scope.addEmail = function() {
        $scope.data.settings.organizer_emails.push('');
    }
    
    $scope.addWebsite = function() {
        $scope.data.settings.organizer_websites.push('');
    }
    
    /*
     * Remove functions for removing contact fields for Organizers
     */
    $scope.removePhone = function(phone) {
       var index = $scope.data.settings.organizer_phones.indexOf(phone);
       $scope.data.settings.organizer_phones.splice(index,1);
    }
    
    $scope.removeEmail = function(email) {
       var index = $scope.data.settings.organizer_emails.indexOf(email);
       $scope.data.settings.organizer_emails.splice(index,1);
    }
    
    $scope.removeWebsites = function(website) {
       var index = $scope.data.settings.organizer_websites.indexOf(website);
       $scope.data.settings.organizer_websites.splice(index,1);
    }

    /*
     * 
     * WordPress default media uploader to choose image
     */
    $scope.mediaUploader = function (multiImage) {
        var mediaUploader = MediaUploader.openUploader(multiImage);
        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function () {
            attachments = mediaUploader.state().get('selection');
            attachments.map(function (attachment) {
                attachment = attachment.toJSON();
                if (!multiImage) {
                    // Event Organizer Image
                    $scope.data.settings.organizer_image = attachment.sizes.thumbnail === undefined ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;
                    $scope.data.settings.organizer_image_id = attachment.id;
                    $scope.$apply();
                }
            });
        });
        // Open the uploader dialog
        mediaUploader.open();
    }

    $scope.changeEventSiteType = function() {
        $scope.data.term = {};
        $scope.data.term.type = '';
        if($scope.data.settings.type == 'seats'){
            $scope.data.term.type = 'seats';
        }
    }
})
.directive("fileInput", ['$http', '$parse', function($http, $parse){  
    return{  
        link: function($scope, element, attrs){  
            element.on("change", function(event){  
                var files = event.target.files;
                var fileType = files[0].type;
                var allowedFileType = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if(allowedFileType.indexOf(fileType) > -1){
                    $scope.progressStart();
                    var ajUrl = em_ajax_object.ajax_url + "?action=em_upload_image_from_frontend";
                    $scope.form = [];
                    $scope.form.image = element[0].files[0];
                    $http({
                        method  : 'POST',
                        url     : ajUrl,
                        processData: false,
                        transformRequest: function (data) {
                            var formData = new FormData();
                            formData.append("image", $scope.form.image);  
                            return formData;  
                        },  
                        data : $scope.form,
                        headers: {
                            'Content-Type': undefined
                        }
                    }).success(function(response){
                        $scope.progressStop();
                        $scope.data.settings.attachment_id = response.data.attachment_id;
                        var reader = new FileReader();
                        reader.onload = function (event) {
                            $scope.PreviewImage = event.target.result;
                            $scope.$apply();
                        };
                        reader.readAsDataURL(event.target.files[0]);
                    });
                }
                else{
                    alert("Only Image File Allowed");
                    $scope.PreviewImage = null;
                    angular.element("input[type='file']").val(null);
                }
            });  
        }  
    }  
}])
.directive("performerFileInput", ['$http', '$parse', function($http, $parse){  
    return{  
        link: function($scope, element, attrs){  
            element.on("change", function(event){  
                var files = event.target.files;
                var fileType = files[0].type;
                var allowedFileType = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if(allowedFileType.indexOf(fileType) > -1){
                    $scope.progressStart();
                    var ajUrl = em_ajax_object.ajax_url + "?action=em_upload_image_from_frontend";
                    $scope.form = [];
                    $scope.form.image = element[0].files[0];
                    $http({
                        method  : 'POST',
                        url     : ajUrl,
                        processData: false,
                        transformRequest: function (data) {
                            var formData = new FormData();
                            formData.append("image", $scope.form.image);  
                            return formData;  
                        },  
                        data : $scope.form,
                        headers: {
                            'Content-Type': undefined
                        }
                    }).success(function(response){
                        $scope.progressStop();
                        $scope.data.settings.performer_image_id = response.data.attachment_id;
                        var reader = new FileReader();
                        reader.onload = function (event) {
                            $scope.PreviewPerformerImage = event.target.result;
                            $scope.$apply();
                        };
                        reader.readAsDataURL(event.target.files[0]);
                    });
                }
                else{
                    alert("Only Image File Allowed");
                    $scope.PreviewPerformerImage = null;
                    angular.element("input[type='file']").val(null);
                }
            });  
        }  
    }
}])
.directive("organizerFileInput", ['$http', '$parse', function($http, $parse){  
    return{  
        link: function($scope, element, attrs){  
            element.on("change", function(event){  
                var files = event.target.files;
                var fileType = files[0].type;
                var allowedFileType = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if(allowedFileType.indexOf(fileType) > -1){
                    $scope.progressStart();
                    var ajUrl = em_ajax_object.ajax_url + "?action=em_upload_image_from_frontend";
                    $scope.form = [];
                    $scope.form.image = element[0].files[0];
                    $http({
                        method  : 'POST',
                        url     : ajUrl,
                        processData: false,
                        transformRequest: function (data) {
                            var formData = new FormData();
                            formData.append("image", $scope.form.image);  
                            return formData;  
                        },  
                        data : $scope.form,
                        headers: {
                            'Content-Type': undefined
                        }
                    }).success(function(response){
                        $scope.progressStop();
                        $scope.data.settings.organizer_image_id = response.data.attachment_id;
                        var reader = new FileReader();
                        reader.onload = function (event) {
                            $scope.PreviewOrganizerImage = event.target.result;
                            $scope.$apply();
                        };
                        reader.readAsDataURL(event.target.files[0]);
                    });
                }
                else{
                    alert("Only Image File Allowed");
                    $scope.PreviewOrganizerImage = null;
                    angular.element("input[type='file']").val(null);
                }
            });  
        }  
    }
}]);