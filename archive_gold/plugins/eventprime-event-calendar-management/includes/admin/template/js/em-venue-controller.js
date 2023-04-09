/**
 * 
 * Venue controller
 */
eventMagicApp.controller('venueCtrl', function ($scope, $http, MediaUploader, Seat, TermUtility, EMRequest) {
    $scope.data = {};
    $scope.data.sort_option = "name";
    $scope.paged = 1;
    $scope.order = 'ASC';
    $scope.term_edit = false;
    $scope.requestInProgress = false;
    $scope.seat_container_width = '';
    $scope.IsVisible = false;
    $scope.formErrors = [];
    $scope.editingAddress=false;
    $scope.seat_border_color = '3px solid #8cc600';
    $scope.seat_color = '#8cc600';
    $scope.searchKeyword;
    $scope.pagedS = 1;
    $=jQuery;

    angular.element(document).ready(function () {
        em_set_date_defaults();
    });

    $scope.showPopup = function () {
        $scope.seatPopup = true;
    }

    $scope.progressStart = function ()
    {
        $scope.requestInProgress = true;
    }

    $scope.progressStop = function ()
    {
        $scope.requestInProgress = false;
    }

    //Loads page data for Add/Edit Venue page 
    $scope.preparePageData = function () {

        // If "Edit" page
        var id = em_get('term_id');
        if (id > 0)
            $scope.data.id = id;

        $scope.data.em_request_context = 'admin_venue';
        $scope.data.em_event_site_nonce = em_venue_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function (response) {
            var responseBody = response.data;
            $scope.progressStop();
            if (responseBody.success){
                $scope.data = responseBody.data;
                /*
                 * Incase of edit page
                 */
                if (id > 0) {
                    $scope.term_edit = true;
                    // Dispatching event to load marker on Map as per the current address
                    // em_event_dispatcher('change', 'em-pac-input');
                    $scope.data.term.addresses.push($scope.data.term.address);
                    if ($scope.data.term.map_configured) {
                        $scope.setupMap();
                    }

                    // Updating rows column values in seating structure
                    if ($scope.data.term.seats.length > 0)
                    {
                        $scope.rows = $scope.data.term.seats.length;
                        $scope.columns = $scope.data.term.seats[0].length;
                        var seat_container_width = ($scope.data.term.seats[0].length * 35) + 80 + "px";
                        $scope.seat_container_width = {"width": seat_container_width};
                        if($scope.data.term.seat_color){
                            $("#em_color_picker").css("background-color","#" + $scope.data.term.seat_color);
                        }
                        if($scope.data.term.booked_seat_color){
                            $("#em_color_picker_booked").css("background-color","#" + $scope.data.term.booked_seat_color);
                        }
                        if($scope.data.term.reserved_seat_color){
                            $("#em_color_picker_reserved").css("background-color","#" + $scope.data.term.reserved_seat_color);
                        }
                        if($scope.data.term.selected_seat_color){
                            $("#em_color_picker_selected").css("background-color","#" + $scope.data.term.selected_seat_color);
                        }
                    }
                } else {
                    if ($scope.data.term.map_configured){
                        $scope.setupMap();
                    }
                }
            } else{
                if (responseBody.data.hasOwnProperty('errors')) {
                    $scope.formErrors = responseBody.data.errors;
                    $('html, body').animate({scrollTop: 0}, 'slow');
                }
            }
        });
    };

    /*
     * 
     * WordPress default media uploader to choose gallery images for Venue
     */
    $scope.mediaUploader = function (multiImage) {
        var mediaUploader = MediaUploader.openUploader(multiImage);
        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function () {
            attachments = mediaUploader.state().get('selection');
            attachments.map(function (attachment) {
                attachment = attachment.toJSON();
                console.log(attachment);
                var imageObj = attachment.sizes.thumbnail === undefined ? {src: [attachment.sizes.full.url], id: attachment.id} : {src: [attachment.sizes.thumbnail.url], id: attachment.id};
                $scope.data.term.images.push(imageObj);
                // Pushing attachment ID in model
                $scope.data.term.gallery_images.push(attachment.id);
                $scope.$apply();

            });
        });
        // Open the uploader dialog
        mediaUploader.open();
    };

    $scope.adjustContainerWidth = function (seat)
    {
        var width = parseInt($scope.seat_container_width.width);
        var columnMargin = seat.columnMargin;
        if (seat.row == 0 && columnMargin > 0)
        {
            width += columnMargin;
            $scope.seat_container_width.width = width + "px";
        }

        $scope.updateSeatSequence(seat);
    }

    /*
     * Save venue information
     */
    $scope.saveTerm = function (isValid) {
        $scope.formErrors = [];
        $scope.$broadcast('beforeVenueSave');
        $addressVal= $("#em-pac-input").val();
        $scope.data.term.address = $addressVal;
        // If form is valid against all the angular validations
        if (isValid && $scope.formErrors.length == 0) {
            if ($('#description').length > 0) {
                if ($('#description').is(':visible')) {
                    $scope.data.term.description = $('#description').val();
                } else {
                    if (tinymce !== undefined) {
                        $scope.data.term.description = tinymce.get('description').getContent();
                    }
                }
            }
            $scope.data.term.em_event_site_nonce = em_venue_object.nonce;
            $scope.progressStart();
            EMRequest.send('em_save_venue', $scope.data.term).then(function (response) {
                var responseBody = response.data;
                if (responseBody.success) {
                    if (responseBody.data.hasOwnProperty('redirect')) {
                        location.href = responseBody.data.redirect;
                    }
                } else {
                    if (responseBody.data.hasOwnProperty('errors')) {
                        $scope.formErrors = data.errors;
                        $('html, body').animate({scrollTop: 0}, 'slow');
                    }
                }
                $scope.progressStop();
            });
        }
    }

    // Deletion of gallery images from current Venue model (Actual deletion will be done after save)
    $scope.deleteGalleryImage = function (image_id, index) {
        $scope.data.term.images.splice(index, 1);
        $scope.data.term.gallery_images = em_remove_from_array($scope.data.term.gallery_images, image_id);
    }

    $scope.initialize = function (type) {
        if (type == "edit") {
            //   $scope.preparePageData();
            $(document).ready(function () {

                // Loading all the required data before form load
                $scope.preparePageData();
                if ($("#em_draggable").length > 0) {
                    $("#em_draggable").sortable({
                        stop: function (event, ui) {
                            $scope.data.term.gallery_images = $("#em_draggable").sortable('toArray');
                            $scope.$apply();
                        }
                    });
                }


                $("#established").datepicker({changeMonth: true, yearRange: "-300:+0", changeYear: true, dateFormat: $scope.data.date_format, maxDate: new Date});

            });
        }

        if (type == "list") {
            $scope.prepareVenueListPage();
        }

    };

    $scope.prepareVenueListPage = function () {
        if ($scope.data.sort_option == "count")
            $scope.order = "DESC";
        else
            $scope.order = "ASC";

        $scope.data.em_request_context = 'admin_venues';
        $scope.data.paged = $scope.paged;
        $scope.data.order = $scope.order;
        $scope.data.em_event_site_nonce = em_venue_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function (response) {
            var responseBody = response.data;
            $scope.progressStop();
            if (responseBody.success){
                $scope.data = responseBody.data;
            } else{
                if (responseBody.data.hasOwnProperty('errors')) {
                    $scope.formErrors = data.errors;
                    $('html, body').animate({scrollTop: 0}, 'slow');
                }
            }
        });
    };

    /*  list page with search */
    $scope.prepareVenueListPageWithSearch = function () {
        if ($scope.data.sort_option == "count")
            $scope.order = "DESC";
        else
            $scope.order = "ASC";

        $scope.data.em_request_context = 'admin_venues_search';
        $scope.data.pagedS= $scope.pagedS;
        $scope.data.order = $scope.order;
        $scope.data.em_event_site_nonce = em_venue_object.nonce;
        if($scope.searchKeyword)
        $scope.data.searchKeyword= $scope.searchKeyword;

        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function (response) {
            var responseBody = response.data;
            if (!responseBody.success)
                return;
            $scope.data = responseBody.data;
            $scope.progressStop();
        });
    };

    $scope.selections = [];

    $scope.selectTerm = function (id) {
        if ($scope.selections.indexOf(id) >= 0) {
            $scope.selections = em_remove_from_array($scope.selections, id)
        } else {
            $scope.selections.push(id);
        }
    }

    $scope.deleteTerms = function () {
        var confirmed = confirm("All events associated to Event Site(s) will be deleted. Please confirm");
        if (confirmed) {
            $scope.progressStart();
            TermUtility.delete($scope.selections, $scope.data.tax_type).then(function (data) {
                $scope.progressStop();
                location.reload();
            });
        }
    }

    $scope.deleteTerms= function(){
        var confirmed = confirm(em_venue_object.delete_confirm);
        if(confirmed){
            $scope.progressStart();
            TermUtility.delete($scope.selections, $scope.data.tax_type, em_venue_object.nonce, 'event_site').then(function(response) {
               $scope.progressStop();
                if( response.data.success == false ) {
                    alert(response.data.data.errors[0]);
                } else{
                    location.reload();
                }
            });
        }
    }

    $scope.pageChanged = function (pageNumber) {
        $scope.selectedAll = false;
        $scope.paged = pageNumber;
        $scope.prepareVenueListPage();
    }

    /*
     * Called when pagination changes in searched results
     */
    $scope.searchPageChanged= function(pageNumber){
        $scope.pagedS= pageNumber;
        $scope.prepareVenueListPageWithSearch();
        $scope.selectedAll=false;
    }

    $scope.reloadPage = function(){
        window.location.reload();
     }
 
    $scope.checkAllVENUES = function () {

//        if ($scope.selectAll) {      
//          
//            $scope.selectAll = true;
//        } else {
//            $scope.selectAll = false;
//        }        
//          angular.forEach($scope.data.terms, function (term) {
//             //   alert(term.id);
//               $scope.selections.push(term.id);
//                term.Selected = $scope.selectAll ? term.id : 0; 
//               
//               
//             
//        });
        angular.forEach($scope.data.terms, function (term) {
            if ($scope.selectedAll) {
                $scope.selections.push(term.id);
                // console.log($scope.selections.push(post.id));
                term.Selected = $scope.selectedAll ? term.id : 0;
            } else {
                $scope.selections = [];
                term.Selected = 0;
            }
        });
    };





    $scope.checkCapacity = function (event)
    {
        if ($scope.data.term.seats[0] == undefined)
            return;
        var totalNoSeats = $scope.data.term.seats.length * ($scope.data.term.seats[0].length);
        if (totalNoSeats > 0)
        {

            if ($scope.data.term.seating_capacity > totalNoSeats)
            {
                alert("Seating capacity is not matching with total seats.");
                $scope.data.term.seating_capacity = totalNoSeats;
            }
        }

    }

    $scope.$watch('data.term.seating_capacity', function (newValue, oldValue) {
        if ($scope.data.hasOwnProperty('term') && $scope.data.term.type == "seats")
        {
            if (newValue <= 0)
                $scope.termForm.seating_capacity.$setValidity("min", false);
            else
                $scope.termForm.seating_capacity.$setValidity("min", true);

            if ($scope.data.term.seats[0] == undefined)
                return;


            var totalNoSeats = $scope.data.term.seats.length * ($scope.data.term.seats[0].length);
            // console.log(angular.element('#row').val());  console.log('--');   console.log(angular.element('#col').val());  console.log('--'); console.log(totalNoSeats);  console.log('--');
            //console.log($scope.data.term.seats.length);  console.log('--'); console.log($scope.data.term.seats[0].length);
            if (newValue != totalNoSeats)
                $scope.termForm.seating_capacity.$setValidity("invalidCapacity", false);
            else
                $scope.termForm.seating_capacity.$setValidity("invalidCapacity", true);


        }
    })
    $scope.getRowAlphabet = function (rowIndex) {
        var indexNumber = "";
        if (rowIndex > 25) {
            indexNumber = parseInt(rowIndex / 26);
            rowIndex = rowIndex % 26;
        }

        return String.fromCharCode(65 + parseInt(rowIndex)) + indexNumber;
    }

    $scope.updateSeatSequence = function (seat) {
        if(!seat.seatSequence){
            var seatColInd = parseInt(seat.col) + parseInt(1);
            seat.seatSequence= $scope.getRowAlphabet(seat.row) + "-" + seatColInd;    
        }
    }

    $scope.em_call_popup = function (dialog) {

        var pmId = dialog + "-dialog";

        $(pmId).siblings('.pm-popup-mask').show();
        $(pmId).show();
        $('.pm-popup-container').css("animation", "pm-popup-in 0.3s ease-out 1");
    }


    $('.pm-popup-close, .pm-popup-mask').click(function () {
        $('.pm-popup-mask').hide();
        $('.pm-popup-mask').next().hide();
    });

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
        if($scope.data.term.zoom_level && $scope.data.term.zoom_level !== ''){
            map.setZoom(parseInt($scope.data.term.zoom_level));
        }
        var geocoder = new google.maps.Geocoder;
        var infowindow = new google.maps.InfoWindow;
        var autocomplete = new google.maps.places.SearchBox(input);
        autocomplete.bindTo('bounds', map);
        var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });
        if(input.value!=''){
            alert('test');
            google.maps.event.trigger(autocomplete, 'places_changed');
        }
        resetMarkers=function(){
            for (i = 0; i < gmarkers.length; i++) {
                gmarkers[i].setMap(null);
            }
        }
        
        updateLatLngInput=function(lat,lng,zoom){
            $scope.data.term.lat=lat;
            $scope.data.term.lng=lng;
            $scope.data.term.zoom_level=zoom;
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
                //map.setZoom(8);  // Why 17? Because it looks good.
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
                        //map.setZoom(13);
                        var marker = new google.maps.Marker({
                            position: latlng,
                            map: map
                        });
                        input.value = results[1].formatted_address;
                        infowindow.setContent(results[1].formatted_address);
                        infowindow.open(map, marker);
                        gmarkers.push(marker);
                        updateLatLngInput(parseFloat(latLng.lat()),parseFloat(latLng.lng()),map.getZoom());
                    }
                }
            });
        });

        map.addListener('zoom_changed', function (e) {
            var zoom = map.getZoom();
            $scope.data.term.zoom_level = zoom;
            $scope.$apply();
        });

        $("#em_venue_lat,#em_venue_long").change(function(){
            resetMarkers();
            var lat = latEl.val();
            var lng = lngEl.val();
            if(lat!="" && lng!=""){
                var geocoder = new google.maps.Geocoder;
                var infowindow = new google.maps.InfoWindow;
                var latlng = {lat: parseFloat(lat), lng: parseFloat(lng)};
                geocoder.geocode({'location': latlng}, function (results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            //map.setZoom(11);
                            var marker = new google.maps.Marker({
                                position: latlng,
                                map: map
                            });
                            infowindow.setContent(results[0].formatted_address);
                            infowindow.open(map, marker);
                            gmarkers.push(marker);
                            $("#em-pac-input").val(results[1].formatted_address);
                        }
                    }
                });
            }
        });

        if($scope.data.term.lat!='' && $scope.data.term.lng!=''){
            var latlng = {lat: parseFloat($scope.data.term.lat), lng: parseFloat($scope.data.term.lng)};
            geocoder.geocode({'location': latlng}, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        //map.setZoom(13);
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
        }
        
    }
})
.directive('stringToNumber', function() {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModel) {
            ngModel.$parsers.push(function(value) {
                return '' + value;
            });
            ngModel.$formatters.push(function(value) {
                return parseFloat(value);
            });
        }
    };
});