eventMagicApp.controller( 'priceManagerCtrl', function( $scope, $http, PostUtility, EMRequest, MediaUploader ) {
    $scope.data = {};
    $scope.price_option = {};
    $scope.screen = '';
    $scope.requestInProgress = false;
    $scope.formErrors = [];
    $scope.formSuccess = [];
    $scope.post_id = 0;
    $scope.paged = 1;
    $scope.selections = [];
    $scope.datepicker_format = '';
    $scope.show_seat_layout = 0;
    $scope.selectedSeats = [];
    $scope.currentSeat = '';
    $scope.currentSelection = '';
    $scope.seatSequence = [];
    $scope.is_seat_update = 0;
    $scope.genColor = '#null';
    $scope.em_model_length_class = 'ep-modal-small';

    /* Show/Hide element while performing HTTP request */
    $scope.progressStart = function() {
        $scope.requestInProgress = true;
    }
    
    $scope.progressStop = function() {
        $scope.requestInProgress = false;
    }
    
    /*
     * Controller intitialization for data load 
     */
    $scope.initialize = function(screen) {
        if(screen == 'event_price_manager'){
            $scope.prepareEventPriceManagerList();
        }
        else if(screen == 'event_add_price_manager'){
            $scope.addEventPriceManagerList();
        }
    }

    // Event Price Manager 
    $scope.prepareEventPriceManagerList = function() {
        $scope.progressStart();
        $scope.data.em_request_context = 'admin_event_price_manager_list';
        if (em_get('post_id') > 0) {
            $scope.post_id = em_get('post_id');
            $scope.data.post_id= $scope.post_id;
        }
        $scope.data.em_price_manager_nonce = em_price_manager_cap_object.nonce;
        // HTTP request to load data
        EMRequest.send('em_load_strings',$scope.data).then(function(response){
            var responseBody = response.data;
            if(responseBody.success){
                $scope.data = responseBody.data;
            }
            $scope.progressStop();
        });
    }

    $scope.addEventPriceManagerList = function() {
        $scope.progressStart();
        $scope.data.em_request_context = 'admin_event_price_manager_add';
        if (em_get('post_id') > 0) {
            $scope.post_id = em_get('post_id');
            $scope.data.post_id= $scope.post_id;
        }
        if (em_get('option_id') > 0) {
            $scope.option_id = em_get('option_id');
            $scope.data.option_id= $scope.option_id;
        }
        $scope.data.em_price_manager_nonce = em_price_manager_cap_object.nonce;
        // HTTP request to load data
        EMRequest.send('em_load_strings',$scope.data).then(function(response){
            var responseBody = response.data;
            if(responseBody.success){
                $scope.data = responseBody.data;
                if($scope.data.option_data && $scope.data.option_data.id){
                    $scope.price_option = $scope.data.option_data;
                    $scope.price_option.event_id = $scope.price_option.event_id;
                    $scope.price_option.option_id = $scope.price_option.id;
                    if($scope.price_option.variation_color) {
                        jQuery("#em_color_picker").css("background-color","#" + $scope.price_option.variation_color);
                    }
                    if($scope.data.venue && $scope.data.venue.type && $scope.data.venue.type == 'seats'){
                        $scope.setSeatContainerWidth();
                        $scope.selectedSeats = $scope.price_option.seat_data;
                        if($scope.data.venue.seat_color){
                            $scope.genColor = '#'+$scope.data.venue.seat_color;
                        }
                        var seatArr = $scope.data.venue.seating_capacity;
                        if(seatArr >= 500 && seatArr <= 1000){
                            $scope.em_model_length_class = 'ep-modal-medium';
                        }
                        if(seatArr >= 1001){
                            $scope.em_model_length_class = 'ep-modal-large';
                        }
                    }
                }
                else{
                    $scope.price_option.event_id = $scope.post_id;
                    if($scope.data.venue && $scope.data.venue.type && $scope.data.venue.type == 'seats'){
                        $scope.setSeatContainerWidth();
                        if($scope.data.venue.seat_color){
                            $scope.genColor = '#'+$scope.data.venue.seat_color;
                        }
                        var seatArr = $scope.data.venue.seating_capacity;
                        if(seatArr >= 500 && seatArr <= 1000){
                            $scope.em_model_length_class = 'ep-modal-medium';
                        }
                        if(seatArr >= 1001){
                            $scope.em_model_length_class = 'ep-modal-large';
                        }
                    }
                }
                $scope.initializeDateTimePickers($scope.data);
            }
            $scope.progressStop();
        });
    }

    $scope.initializeDateTimePickers= function(data) {
        var minDate = new Date(data.start_booking_date);
        var maxDate = new Date(data.last_booking_date);
        jQuery("#em-pm-start-date-input").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: data.datepicker_format, minDate: minDate, maxDate: maxDate});
        jQuery("#em-pm-end-date-input").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: data.datepicker_format, minDate: minDate, maxDate: maxDate});
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
                    $scope.price_option.icon_image = attachment.sizes.thumbnail === undefined ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;
                    $scope.price_option.icon = attachment.id;
                    $scope.$apply();
                }
            });
        });
        // Open the uploader dialog
        mediaUploader.open();
    }

    /**
     * save price option in custom table
     */
    $scope.savePriceOption = function() {
        $scope.progressStart();
        if( jQuery('#description').is(':visible') ) {
            $scope.price_option.description= jQuery('#description').val();
        } 
        else {
            if(typeof tinymce!="undefined") {
                if(tinymce.get('description')) {
                    $scope.price_option.description= tinymce.get('description').getContent();
                }
            }
        }
        if($scope.data.venue && $scope.data.venue.type && $scope.data.venue.type == 'seats'){
            $scope.price_option.selectedSeats = $scope.selectedSeats;
            $scope.price_option.genColor = $scope.genColor;
            $scope.price_option.is_seat_update = $scope.is_seat_update;
            if($scope.price_option.capacity !== $scope.selectedSeats.length){
                $scope.formErrors = ['Selected seats are not same with capacity'];
                $scope.progressStop();
                return false;
            }
        }
        $scope.price_option.em_price_manager_nonce = em_price_manager_cap_object.nonce;
        // HTTP request to load data
        EMRequest.send('em_save_event_price_option',$scope.price_option).then(function(response){
            var responseBody = response.data;
            if(responseBody.success){
                $scope.data = responseBody.data;
                if(responseBody.data.hasOwnProperty('redirect')){
                    location.href = responseBody.data.redirect;
                }
            }
            else{
                if(responseBody.data.hasOwnProperty('errors')){
                    $scope.formErrors = responseBody.data.errors;
                }
            }
            $scope.progressStop();
        });
    }

    /*
    * Select item
    */
    $scope.selectOption = function(option_id){
        if($scope.selections.indexOf(option_id) >= 0)
            $scope.selections = em_remove_from_array($scope.selections,option_id);
        else
            $scope.selections.push(option_id);
    }

    /*
    * Select all options lists
    */
    $scope.checkAll = function () {
        angular.forEach($scope.data, function (price_cat) {
            if ($scope.selectedAll) { 
                $scope.selections.push(price_cat.id);
                price_cat.Selected = $scope.selectedAll ? price_cat.id : 0; 
                jQuery("#price-cat-"+price_cat.id).prop('checked', true).attr('checked', 'checked');
            }
            else{
                $scope.selections = [];
                price_cat.Selected = 0;
                jQuery("#price-cat-"+price_cat.id).prop('checked', false).removeAttr('checked');
            }
        });
    };

    /*
    * Function to delete option
    */
    $scope.deleteOptions = function(){
        var confirmed = confirm("Do you want to delete this option ?");
        if(confirmed){
            $scope.progressStart();
            var deleteIds = {};
            deleteIds.option_id = $scope.selections;
            deleteIds.event_id = $scope.post_id;
            deleteIds.em_price_manager_nonce = em_price_manager_cap_object.nonce;
            EMRequest.send('em_delete_event_price_option', deleteIds).then(function(response){
                var responseBody = response.data;
                if(responseBody.success){
                    $scope.formSuccess = responseBody.data.message;
                    if(responseBody.data.hasOwnProperty('redirect')){
                        location.href = responseBody.data.redirect;
                    }
                }
                else{
                    if(responseBody.data.hasOwnProperty('errors')){
                        $scope.formErrors = responseBody.data.errors;
                    }
                }
                $scope.progressStop();
            });
        } 
    }

    /**
     * sorting
     */
    $scope.epPriceSorting = function(){
        $scope.progressStart();
        var priceids = [];
        $("#ep-tblLocations").find("tr").each(function (index, item) {
            var rlid = $(this).data('priceid');
            if(rlid){
                priceids.push(rlid);
            }
        });
        if(priceids.length > 0){
            var pricepositions = {};
            pricepositions.option_id = priceids;
            pricepositions.event_id = $scope.post_id;
            pricepositions.em_price_manager_nonce = em_price_manager_cap_object.nonce;
            EMRequest.send('em_multi_price_list_sorting', pricepositions).then(function(response){
                $scope.progressStop();
                var responseBody = response.data;
            });
        }
    }

    $scope.setSeatContainerWidth = function() {
        if( $scope.data.event.seats.length > 0 ) {
            var seat_container_width = ($scope.data.event.seats[0].length * 35) + 40 + "px";
            $scope.seat_container_width = { "width" : seat_container_width };
        }
    }

    $scope.selectSeat = function (seat, row, col, scolor, sel_color) {
        // Preventing reserver,blocked and sold seats from selection
        if (seat.type == "reserve" || seat.type == "tmp" || seat.type == "sold") {
            return;
        }

        if (seat.type == 'selected' && seat.type != 'general') {   
            // If seat already selected then deselect it.
            var index = $scope.selectedSeats.indexOf(seat);
            if (index >= 0) {
                $scope.selectedSeats.splice(index, 1);
            }
            seat.type = 'general';
            seat.seatColor = '#'+scolor;
            seat.seatBorderColor = '3px solid #'+scolor;
        } else {
            if(seat.type == 'general'){
                if(seat.variation_id){
                    if(seat.variation_id == $scope.price_option.option_id){
                        // remove existing seat data
                        angular.forEach($scope.selectedSeats, function(svalue, skey){
                            if(svalue.uniqueIndex == seat.uniqueIndex){
                                seat.type = 'general';
                                seat.seatColor = $scope.genColor;
                                seat.seatBorderColor = '3px solid '+$scope.genColor;
                                delete(seat.variation_id);
                                $scope.selectedSeats.splice(skey, 1);
                            }
                        });
                    }
                }
                else{
                    var sel_color = $scope.price_option.variation_color;
                    seat.type = 'selected';
                    seat.seatColor = '#'+sel_color;
                    seat.seatBorderColor = '3px solid #'+sel_color;
                    $scope.selectedSeats.push(seat);
                }
            }
        }
        $scope.is_seat_update = 1;
    }

    /********************** Generate Alphabet seat sequence ****************/
    $scope.getRowAlphabet = function (rowIndex) {
        var indexNumber = "";
        if (rowIndex > 25) {
            indexNumber = parseInt(rowIndex / 26);
            rowIndex = rowIndex % 26;
        }
        return String.fromCharCode(65 + parseInt(rowIndex)) + indexNumber;
    }

    $scope.adjustContainerWidth = function(columnMargin,index) {
        if($scope.seat_container_width){
            var width = parseInt($scope.seat_container_width.width);
            if(index == 0 && columnMargin > 0) {  
                width += columnMargin;
                $scope.seat_container_width.width = width + "px";
            }
        }
    }

    $scope.selectRow = function (index) {
        var term_seat_color = $scope.price_option.variation_color;
        for (var i = 0; i < $scope.data.event.seats[index].length; i++) {
            // check if already exist
            if ($scope.data.event.seats[index][i].type == 'selected' && $scope.data.event.seats[index][i].type != 'general') {
                var seat_index = $scope.selectedSeats.indexOf($scope.data.event.seats[index][i]);
                if (seat_index >= 0) {
                    $scope.data.event.seats[index][i].type = 'general';
                    $scope.data.event.seats[index][i].seatColor = $scope.genColor;
                    $scope.data.event.seats[index][i].seatBorderColor = '3px solid '+$scope.genColor;
                    $scope.selectedSeats.splice(seat_index, 1);
                }
            }
            else{
                if($scope.data.event.seats[index][i].type == 'general'){
                    if($scope.data.event.seats[index][i].variation_id){
                        if($scope.data.event.seats[index][i].variation_id == $scope.price_option.option_id){
                            // remove existing seat data
                            angular.forEach($scope.selectedSeats, function(svalue, skey){
                                if(svalue.uniqueIndex == $scope.data.event.seats[index][i].uniqueIndex){
                                    $scope.data.event.seats[index][i].type = 'general';
                                    $scope.data.event.seats[index][i].seatColor = $scope.genColor;
                                    $scope.data.event.seats[index][i].seatBorderColor = '3px solid '+$scope.genColor;
                                    delete($scope.data.event.seats[index][i].variation_id);
                                    $scope.selectedSeats.splice(skey, 1);
                                }
                            });
                        }
                    }
                    else{
                        $scope.data.event.seats[index][i].type = 'selected';
                        if(term_seat_color){
                            $scope.data.event.seats[index][i].seatBorderColor = '3px solid #'+term_seat_color;
                            $scope.data.event.seats[index][i].seatColor = '#'+term_seat_color;
                        }
                        $scope.selectedSeats.push($scope.data.event.seats[index][i]);
                    }
                }
            }
        }
        $scope.currentSelection = 'row';
        $scope.currentSelectionIndex = index;
        $scope.is_seat_update = 1;
    };

    $scope.selectColumn = function (index) {
        var term_seat_color = $scope.price_option.variation_color;
        for (var i = 0; i < $scope.data.event.seats.length; i++) {
            if($scope.data.event.seats[i][index].type === 'general'){
                $scope.data.event.seats[i][index].type = 'selected';
                if(term_seat_color){
                    $scope.data.event.seats[i][index].seatBorderColor = '3px solid #'+term_seat_color;
                    $scope.data.event.seats[i][index].seatColor = '#'+term_seat_color;
                }
                $scope.selectedSeats.push($scope.data.event.seats[i][index]);
            }
        }

        for (var i = 0; i < $scope.data.event.seats.length; i++) {
            // check if already exist
            if ($scope.data.event.seats[i][index].type == 'selected' && $scope.data.event.seats[i][index].type != 'general') {
                var seat_index = $scope.selectedSeats.indexOf($scope.data.event.seats[i][index]);
                if (seat_index >= 0) {
                    $scope.data.event.seats[i][index].type = 'general';
                    $scope.data.event.seats[i][index].seatColor = $scope.genColor;
                    $scope.data.event.seats[i][index].seatBorderColor = '3px solid ' + $scope.genColor;
                    $scope.selectedSeats.splice(seat_index, 1);
                }
            }
            else{
                if($scope.data.event.seats[i][index].type == 'general'){
                    if($scope.data.event.seats[i][index].variation_id){
                        if($scope.data.event.seats[i][index].variation_id == $scope.price_option.option_id){
                            // remove existing seat data
                            angular.forEach($scope.selectedSeats, function(svalue, skey){
                                if(svalue.uniqueIndex == $scope.data.event.seats[i][index].uniqueIndex){
                                    $scope.data.event.seats[i][index].type = 'general';
                                    $scope.data.event.seats[i][index].seatColor = $scope.genColor;
                                    $scope.data.event.seats[i][index].seatBorderColor = '3px solid ' + $scope.genColor;
                                    delete($scope.data.event.seats[i][index].variation_id);
                                    $scope.selectedSeats.splice(skey, 1);
                                }
                            });
                        }
                    }
                    else{
                        $scope.data.event.seats[i][index].type = 'selected';
                        if(term_seat_color){
                            $scope.data.event.seats[i][index].seatBorderColor = '3px solid #'+term_seat_color;
                            $scope.data.event.seats[i][index].seatColor = '#'+term_seat_color;
                        }
                        $scope.selectedSeats.push($scope.data.event.seats[i][index]);
                    }
                }
            }
        }
        $scope.currentSelection = 'col'
        $scope.currentSelectionIndex = index;
        $scope.is_seat_update = 1;
    };

    $scope.resetSelections = function () {
        var term_seat_color = $scope.data.venue.seat_color;
        for (var i = 0; i < $scope.selectedSeats.length; i++) {
            $scope.selectedSeats[i].type = 'general';
            if(term_seat_color){
                $scope.selectedSeats[i].seatColor = '#'+term_seat_color;
                $scope.selectedSeats[i].seatBorderColor = '3px solid #'+term_seat_color;
            }
        }
        $scope.selectedSeats = [];
        $scope.currentSelection = '';
        $scope.currentSelectionIndex = '';
    }

    $scope.variation_color_change = function(){
        var nscolor = $scope.price_option.variation_color;
        if($scope.selectedSeats.length > 0){
            angular.forEach($scope.selectedSeats, function(value, key){
                value.seatBorderColor = '3px solid #'+nscolor;
                value.seatColor = '#'+nscolor;
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