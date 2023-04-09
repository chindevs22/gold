eventMagicApp.controller('emBookingCtrl', function ($scope, $http, EMRequest, Seat, $compile, $filter) {
    $scope.data = {};
    $scope.show_cart= false;
    $scope.event= {};
    $scope.discount= 0;
    $scope.price= 0;
    $scope.order = {};
    $scope.selectedSeats = [];
    $scope.requestInProgress = false;
    $scope.orders= [];
    $scope.order_ids= [];
    $scope.order_id_arr =[];
    $scope.currency_symbol;
    $scope.payment_processors= [];
    $scope.venue_id;
    $scope.event_id;
    $scope.bookable= true;   
    $scope.update_cart= false;
    $scope.booking_notice;
    $scope.is_timer_on= false;
    $scope.minute=0;
    $scope.second=0;
    $scope.errorMsg= '';
    $scope.booking_quantity= 0;
    $scope.attendee_names= [];
    $scope.couponAppliedPopup = 0;
    $scope.couponCodeApplied = 0;
    $scope.couponPopupMsg = '';
    $scope.coupon_code_discount_amount = 0;
    $scope.formErrors = [];
    $scope.isUserLoggedin = 0;
    $scope.ebd = 0;
    $scope.applyWoocommerce = 0;
    $scope.fixed_event_price = 0;
    $scope.is_custom_booking_field = 0;
    $scope.is_reset_woo_product = false;
    $scope.event_multi_price_option = {val: 0};
    $scope.setFocus = 0;
    $scope.em_selected_seat_price = 0;
    $scope.seatAttendeeInfo = [];
    $scope.standingAttendeeInfo = [];
    $scope.em_all_variation_name = [];
    $scope.gbdata = [];
    $scope.currency_code;
    
    $scope.setSeatContainerWidth= function()
    {
       if( $scope.event.seats.length>0 )
        {
           var seat_container_width= ($scope.event.seats[0].length*35) + 40 + "px";
           $scope.seat_container_width={ "width" : seat_container_width };
        }
    }
   
    $scope.progressStart= function()
    {
        $scope.requestInProgress = true;
    }
    
    $scope.progressStop= function()
    {
        $scope.requestInProgress = false;
    }
    
    // Called at initialization
    $scope.initialize = function (venue_id, event_id) {
        $scope.venue_id= venue_id;
        $scope.event_id= event_id;
        $scope.load_payment_configuration($scope.event_id); 
    }
    
    // Loading Global payment configuration
    $scope.load_payment_configuration= function(event_id)
    {
        $scope.progressStart();
        $scope.request = {};
        $scope.request.event_id = event_id || $scope.event_id;
        $scope.event_id = event_id || $scope.event_id;
        EMRequest.send('em_load_payment_configuration', $scope.request).then(function (response) {
            $scope.progressStop();
            if(!response.data.is_payment_configured && response.data.ticket_price>0)
            {
                $scope.errorMsg= 'Payment system is not configured.';
                return;
            }
            $scope.payment_processors= response.data.payment_prcoessor;
            $scope.currency_symbol= response.data.currency_symbol;
            $scope.data.payment_processor = response.data.selected_payment_method;
            $scope.currency_code= response.data.currency_code;
            $scope.data.enable_modern_paypal = response.data.enable_modern_paypal;
            // As payment system is confiured loading Event's data for booking
            $scope.loadEventForBooking();
        });
    }
    
    $scope.loadEventForBooking= function(event_id)
    {
        $scope.request = {};
        $scope.request.event_id = event_id || $scope.event_id;
        $scope.event_id = event_id || $scope.event_id;
        
        // Loading Seats and other payment options related to Event
        $scope.progressStart();
        EMRequest.send('em_load_event_for_booking', $scope.request).then(function (response) {
            $scope.progressStop();
            $scope.event = response.data;
            $scope.isUserLoggedin = response.data.is_user_logged_in;
            $scope.setSeatContainerWidth(); 

            // brodcast event for child controller
            $scope.$broadcast ('initilizeBookingChild');

            if(response.data.venue.type=="seats")
                $scope.allowSeatSelectionUpdate();
            else
                $scope.allowSeatQuantityUpdate();
            
            $scope.display_cart(false);

            // add multi price default option
            if($scope.event.price_option_data && $scope.event.price_option_data.length > 0){
                angular.forEach($scope.event.price_option_data, function(value, key){
                    if(value.is_default == 1){
                        $scope.event_multi_price_option.val = value;
                    }
                    $scope.em_all_variation_name[value.id] = value.name;
                });
            }

        });
    }
    
    $scope.allowSeatQuantityUpdate= function()
    {
        $scope.selectedSeats= [];
        var order_exist= false;
        if($scope.orders.length>0)
        {  
            for(var i=0;i<$scope.orders.length;i++)
            {   
                if($scope.orders[i].event_id==$scope.event_id)
                {
                    document.getElementById("standing_order_quantity").value= $scope.orders[i].quantity;
                    $scope.update_cart= true;
                    order_exist= true;
                    break;
                }
                     
            }
        } 
        if(!order_exist)
        {
            $scope.update_cart= false;
            $scope.booking_quantity = 1;
        }
            
        
    }
    
    // Allows selection and deselection of seats after order creation.
    $scope.allowSeatSelectionUpdate= function(){
        $scope.selectedSeats= [];
        if($scope.orders.length>0)
        {  
            for(var i=0;i<$scope.orders.length;i++)
            {   
                if($scope.orders[i].event_id==$scope.event_id)
                {
                    var seatPositions= $scope.orders[i].seat_pos;
                    for(var j=0;j<seatPositions.length;j++)
                    {   
                        var seatIndexes= seatPositions[j].split("-");
                        var row= $scope.event.seats[seatIndexes[0]];
                        var seat = row[seatIndexes[1]];
                        seat.type='selected';
                        seat.seatColor = '#'+$scope.event.venue.selected_seat_color;
                        seat.seatBorderColor = '3px solid #'+$scope.event.venue.selected_seat_color;
                        $scope.update_cart= true;
                        $scope.selectedSeats.push(seat);
                    }
                }
            }
        } 
        
        if($scope.selectedSeats.length==0)
            $scope.update_cart= false;
    }
    
    // Delete current event's order and create new 
    $scope.updateOrder= function()
    {
        $scope.progressStart();
        // Deleting previous order
        if($scope.orders.length>0)
        {
            for(var i=0;i<$scope.orders.length;i++)
            {   
                if($scope.orders[i].event_id==$scope.event_id)
                {
                    $scope.request= {
                        "order_id": $scope.orders[i].order_id
                    }
                      
                    //$scope.progressStart();
                    EMRequest.send('em_delete_order', $scope.request).then(function (response) {
                        $scope.progressStop();
                         // Removing old order
                         $scope.orders.splice(i,1);
                         // Check if any seats are selected
                        if($scope.event.venue.type=="seats" && $scope.selectedSeats.length > 0)
                            $scope.orderSeats();
                        else if($scope.event.venue.type=="standings")
                            $scope.orderStandings();
                        else
                           $scope.update_cart= false;
                         
                    });
                    break;
                }
            }
        }
    }
    
    $scope.orderStandings= function()
    {
        if($scope.isUserLoggedin == 0){
            if($scope.checkGuestValidations() === false) return;
        }
        $scope.progressStart();
        $scope.order= {};
        $scope.request= {
            'event_id': $scope.event_id
        };
        var noBooking = 0;
        // Order quantity related checks
        var quantity = document.getElementById("standing_order_quantity").value;
        var standing_available_qty = $("#kf_standing_update").data('available_standing');
        if(quantity > standing_available_qty){
            var max_booking_qty = $("#kf_standing_update").data('max_booking_qty');
            alert(max_booking_qty);
            $scope.progressStop();
            return;
        }
        if(!(quantity>0)){
            var no_seat_msg = $("#kf_standing_update").data('no_seat');
            alert(no_seat_msg);
            $scope.progressStop();
            return;
        } else {
            // Checking if any attendee names are empty
            if($scope.attendee_names.length > 0) {
                if($scope.event.custom_booking_field_data && $scope.event.custom_booking_field_data.length > 0){
                    $scope.is_custom_booking_field = 1;
                    var cbfd = {};
                    $scope.event.custom_booking_field_data.forEach(function(data, dk){
                        if(data){
                            cbfd[data.type] = data.required;
                        }
                    });
                    
                    $(".em-booking-standing-data").html('');
                    $scope.attendee_names.forEach(function(item, index) {
                        if(noBooking == 0){
                            angular.forEach(item, function(type, type_key){
                                if(isNaN(type_key)){
                                    delete item[type_key];
                                } else{
                                    angular.forEach(type, function(indx, indx_key){
                                        angular.forEach(indx, function(label, label_key){
                                            if((!label.value || label.value == null || label.value == undefined || label.value == '') && cbfd[indx_key] == 1){
                                                jQuery("#em-attendee-name-standing-"+index+"-"+indx_key).html('This is required field');
                                                jQuery("#ep-standings-input-"+index+"-"+indx_key).focus();
                                                $scope.progressStop();
                                                noBooking = 1;
                                                return;
                                            }
                                            if(indx_key == 'email'){
                                                if(label.value){
                                                    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                                                    if(!regex.test(label.value)) {
                                                        jQuery("#em-attendee-name-standing-"+index+"-"+indx_key).html('Please enter valid email');
                                                        jQuery("#ep-standings-input-"+index+"-"+indx_key).focus();
                                                        $scope.progressStop();
                                                        noBooking = 1;
                                                        return;
                                                    }
                                                }
                                            }
                                            if(indx_key == 'tel'){
                                                if(label.value){
                                                    if(!$.isNumeric(label.value)|| label.value.length < 10){
                                                        jQuery("#em-attendee-name-standing-"+index+"-"+indx_key).html('Please enter valid phone');
                                                        jQuery("#ep-standings-input-"+index+"-"+indx_key).focus();
                                                        $scope.progressStop();
                                                        noBooking = 1;
                                                        return;
                                                    }
                                                }
                                            }
                                            if(label.value == null || label.value == undefined || label.value.trim() == '') {
                                                $scope.attendee_names[index][type_key][indx_key][label_key].value = 'N/A';
                                            }
                                        });
                                    });
                                }
                            });
                        }
                    });
                }
                else{
                    $scope.attendee_names.forEach(function(item, index) {
                        $scope.is_custom_booking_field = 0;
                        if(noBooking == 0){
                            $(".em-booking-standing-data").html('');
                            if(item == null || item == undefined || item.trim() == '') {
                                if($scope.event.required_booking_attendee_name == 1 && $scope.event.enable_attendees == 1){
                                    $("#em-attendee-name-standing-"+index).html('This is required field');
                                    $("#em-attendee-name-input-standing-"+index).focus();
                                    $scope.progressStop();
                                    noBooking = 1;
                                    return;
                                }
                                else{
                                    $scope.attendee_names[index] = 'N/A';
                                }
                            }
                        }
                    });
                }
                if(noBooking == 1) return;
            }
        }
        
        if($scope.event.max_tickets_per_person>0 && quantity>$scope.event.max_tickets_per_person)
        {
            alert("Maximum tickets allowed per booking - " + $scope.event.max_tickets_per_person);
            $scope.progressStop();
            return;
        }
        // Check if event is bookable
        EMRequest.send('em_check_bookable', $scope.request).then(function (response) {
            $scope.progressStop();
            if ($scope.check_errors(response))
                return true;
            else
            {
                $scope.order.item_number = $scope.get_item_number($scope.event_id);
                $scope.order.quantity = quantity;
                $scope.no_seats = true;
                $scope.order.ticket_limit = $scope.event.max_tickets_per_person;
                $scope.order.seats = $scope.event.seats;
                $scope.order.en_ticket = $scope.event.en_ticket;
                $scope.order.allow_discount = $scope.event.allow_discount;
                $scope.order.discount_per = $scope.event.discount_per;
                $scope.order.discount_no_tickets = $scope.event.discount_no_tickets;           
                
                $scope.order.order_ticket_price = 0;$scope.order.single_price = 0;
                // add fixed event price to order
                $scope.order.fixed_event_price = $scope.event.fixed_event_price;
                // sub total
                var subTotal = 0;
                var order_item_data = [];
                if($scope.event_multi_price_option.val != 0){
                    var price_option_data = [];
                    if($scope.event.price_option_data.length > 0){
                        angular.forEach($scope.event.price_option_data, function(pvalue, pkey){
                            price_option_data[pvalue.id] = pvalue;
                        });
                    }
                    var eventPriceOption = $scope.event_multi_price_option.val;
                    var seatPrice = eventPriceOption.price;
                    if(eventPriceOption.special_price != '' && eventPriceOption.special_price > 0){
                        seatPrice = eventPriceOption.special_price;
                    }
                    var event_variation_id = eventPriceOption.id;
                    order_item_data[event_variation_id] = [];
                    var oid = {};
                    var var_name = '';
                    if(price_option_data[event_variation_id]){
                        var_name = price_option_data[event_variation_id].name
                    }
                    oid.variation_name = var_name;
                    oid.quantity = $scope.order.quantity;
                    oid.price = seatPrice;
                    oid.sub_total = parseFloat(seatPrice) * $scope.order.quantity;
                    oid.variation_id = event_variation_id;
                    oid.event_id = $scope.event_id;
                    order_item_data[event_variation_id] = oid;
                    subTotal = oid.sub_total;
                }
                $scope.order.order_item_data = order_item_data;
                $scope.order.order_ticket_price = subTotal;
                $scope.order.subtotal = subTotal;

                $scope.order.payment_gateway = "paypal";
                $scope.order.event_id = $scope.event_id;
                $scope.order.name = $scope.event.name;
                $scope.order.start_date = $scope.event.start_date;
                $scope.order.attendee_names = $scope.attendee_names;
                // coupon code variables
                $scope.order.applied_coupon = 0;
                $scope.order.coupon_amount = 0;
                $scope.order.coupon_code = '';
                $scope.order.coupon_type = '';
                $scope.order.coupon_discount = 0;
                
                // check custom booking fields have data
                $scope.order.is_custom_booking_field = $scope.is_custom_booking_field;
                //mailpoet variables
                $scope.order.event_magic_mailpoet_optin_box = $scope.event_magic_mailpoet_optin_box;
                // check for woocommerce products
                $scope.order.cart_selected_product = [];
                $scope.order.cart_selected_product_variation_id = [];
                $scope.order.cart_selected_product_variation_price = [];
                $scope.order.woocommerce_products = [];
                $scope.order.billing_address = {};
                $scope.order.shipping_address = {};
                if($scope.ep_product_quantity){
                    angular.forEach($scope.ep_product_quantity, function (item, index) {
                        if(item > 0){
                            var pdata = {'id' : index, 'qty' : item};
                            $scope.order.cart_selected_product.push(pdata);
                        }
                    });
                    if($scope.ep_product_variation_id){                        
                        angular.forEach($scope.ep_product_variation_id, function(item, index){
                            var pdata = {'id' : index, 'variation' : item};
                            $scope.order.cart_selected_product_variation_id.push(pdata);
                        })
                    }
                    // variation price
                    if($scope.ep_product_variation_price){                        
                        angular.forEach($scope.ep_product_variation_price, function(item, index){
                            var pdata = {'id' : index, 'variation_price' : item};
                            $scope.order.cart_selected_product_variation_price.push(pdata);
                        })
                    }
                    EMRequest.send('em_get_woocommerce_event_cart_product', $scope.order).then(function (response) {
                        $scope.order.woocommerce_products = response.data.products;
                        $scope.order.billing_address = response.data.billing_address;
                        if(response.data.billing_address.billing_country && response.data.billing_address.billing_country !== ''){
                            $scope.getWoocommerceCountryState("billing_address", "billing_country", "billing_state");
                        }
                        
                        $scope.order.shipping_address = response.data.shipping_address;
                        $scope.applyWoocommerce = 1;
                        angular.forEach(response.data.products, function(item, index){
                            var psubtotal = item.sub_total;
                            $scope.order.subtotal += parseFloat(psubtotal);
                            $scope.price += parseFloat(psubtotal);
                        });
                        $scope.order.shipping_address.address_option = 'same';
                    });
                }
                // data for guest booking
                $scope.order.username = $scope.gbname;
                $scope.order.useremail = $scope.gbemail;
                $scope.order.userphone = $scope.gbphone;
                $scope.order.guest_booking_personal_info = $scope.gbdata
                $scope.progressStart();
                EMRequest.send('em_book_seat', $scope.order).then(function (response) {
                    $scope.progressStop();
                    if ($scope.check_errors(response)) {
                        return true;
                    }
                    $scope.order.order_id = response.data.order_id;
                    $scope.orders.push($scope.order);
                    $scope.calculate_discount();
                    $scope.calculate_price();
                    $scope.update_order_ids();
                    $scope.display_cart(true);
                    $scope.getPaypalFormValuesForGuestUser();
                    if($scope.event.allow_automatic_discounts == 1){
                    	$scope.calculate_ebd();
                    }
                    // Payment countdown timer
                    var timeInMinutes = 60*4 ,
                    display = document.querySelector('#em_payment_timer');
                    $scope.startTimer(timeInMinutes, display);
                    jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
                });
            }
        });
    }
    /*
     * Setting Order object for seats
     */
    $scope.orderSeats = function () {
        if($scope.isUserLoggedin == 0){
            if($scope.checkGuestValidations() === false) return;
        }
        $scope.progressStart();
        // Temporarily storing seating position and seat sequences
        var tmpSequences = [];
        var seatPos = [];
        $scope.order= {};
        $scope.request= {
            'event_id': $scope.event_id
        };
        var noBooking = 0;
        // Check if any seats are selected
        if(!($scope.selectedSeats.length > 0))
        {
            var no_seat_msg = $("#kf_seat_update").data('no_seat');
            alert(no_seat_msg); 
            $scope.progressStop();
            return;
        } else {
            // Checking if any attendee names are empty
            if($scope.attendee_names.length > 0) {
                if($scope.event.custom_booking_field_data && $scope.event.custom_booking_field_data.length > 0){
                    $scope.is_custom_booking_field = 1;
                    var cbfd = {};
                    $scope.event.custom_booking_field_data.forEach(function(data, dk){
                        if(data){
                            cbfd[data.type] = data.required;
                        }
                    });
                    
                    $(".em-booking-seating-data").html('');
                    $scope.attendee_names.forEach(function(item, index) {
                        if(noBooking == 0){
                            angular.forEach(item, function(type, type_key){
                                if(isNaN(type_key)){
                                    delete item[type_key];
                                } else{
                                    angular.forEach(type, function(indx, indx_key){
                                        angular.forEach(indx, function(label, label_key){
                                            if((!label.value || label.value == null || label.value == undefined || label.value == '') && cbfd[indx_key] == 1){
                                                jQuery("#em-attendee-name-standing-"+index+"-"+indx_key).html('This is required field');
                                                jQuery("#ep-standings-input-"+index+"-"+indx_key).focus();
                                                $scope.progressStop();
                                                noBooking = 1;
                                                return;
                                            }
                                            if(indx_key == 'email'){
                                                if(label.value){
                                                    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                                                    if(!regex.test(label.value)) {
                                                        jQuery("#em-attendee-name-standing-"+index+"-"+indx_key).html('Please enter valid email');
                                                        jQuery("#ep-standings-input-"+index+"-"+indx_key).focus();
                                                        $scope.progressStop();
                                                        noBooking = 1;
                                                        return;
                                                    }
                                                }
                                            }
                                            if(indx_key == 'tel'){
                                                if(label.value){
                                                    if(!$.isNumeric(label.value)|| label.value.length < 10){
                                                        jQuery("#em-attendee-name-standing-"+index+"-"+indx_key).html('Please enter valid phone');
                                                        jQuery("#ep-standings-input-"+index+"-"+indx_key).focus();
                                                        $scope.progressStop();
                                                        noBooking = 1;
                                                        return;
                                                    }
                                                }
                                            }
                                            if(label.value == null || label.value == undefined || label.value.trim() == '') {
                                                $scope.attendee_names[index][type_key][indx_key][label_key].value = 'N/A';
                                            }
                                        });
                                    });
                                }
                            });
                        }
                    });
                }
                else{
                    $scope.attendee_names.forEach(function(item, index) {
                        if(noBooking == 0){
                            $(".em-booking-seat-data").html('');
                            if(item == null || item == undefined || item.trim() == '') {
                                if($scope.event.required_booking_attendee_name == 1 && $scope.event.enable_attendees == 1){
                                    $("#em-attendee-name-"+index).html('This is required field');
                                    $("#em-attendee-name-input-"+index).focus();
                                    $scope.progressStop();
                                    noBooking = 1;
                                    return;
                                }
                                else{
                                    $scope.attendee_names[index] = 'N/A';
                                }
                            }
                        }
                    });
                }
                if(noBooking == 1) return;
            }
        }
    
        // Check if event is bookable
        EMRequest.send('em_check_bookable', $scope.request).then(function (response) {
           $scope.progressStop();
            if ($scope.check_errors(response))
                return true;
            else
            {
                // If seats are selected for booking
                if ($scope.selectedSeats.length > 0) {

                    angular.forEach($scope.selectedSeats, function (seat, key) {
                        tmpSequences.push(seat.seatSequence);
                        seatPos.push(seat.row + "-" + seat.col);

                        // Updating seat type to temporarily block the seat from other users
                        seat.type = "tmp";
                    });

                    $scope.order.item_number = tmpSequences.join(', ');
                    $scope.order.seat_sequences = tmpSequences;
                    $scope.order.seat_pos = seatPos;
                    $scope.order.quantity = tmpSequences.length;
                } else if ($scope.event.seats.length == 0)
                {
                    $scope.order.item_number = $scope.get_item_number($scope.event_id);
                    $scope.order.quantity = 1;
                    $scope.no_seats = true;
                } 
                // Show final checkout section
                $scope.order.ticket_limit = $scope.event.max_tickets_per_person;
                $scope.order.seats = $scope.event.seats;
                $scope.order.en_ticket = $scope.event.en_ticket;
                $scope.order.allow_discount = $scope.event.allow_discount;
                $scope.order.discount_per = $scope.event.discount_per;
                $scope.order.discount_no_tickets = $scope.event.discount_no_tickets;           
                
                $scope.order.single_price = 0;$scope.order.order_ticket_price = 0;
                // add fixed event price to order
                $scope.order.fixed_event_price = $scope.event.fixed_event_price;
                // sub total
                var subTotal = 0;
                var order_item_data = [];
                if($scope.selectedSeats.length > 0){
                    var price_option_data = [];
                    if($scope.event.price_option_data.length > 0){
                        angular.forEach($scope.event.price_option_data, function(pvalue, pkey){
                            price_option_data[pvalue.id] = pvalue;
                        });
                    }
                    angular.forEach($scope.selectedSeats, function(svalue, skey){
                        var seatPrice = svalue.price;
                        if(isNaN(seatPrice) && seatPrice.indexOf('-') > -1) {
                            var spl = seatPrice.split('-');
                            seatPrice = spl[1];
                        }
                        if(svalue.variation_id){
                            if(order_item_data[svalue.variation_id]){
                                var oid = order_item_data[svalue.variation_id];
                                oid.seatNo += ',' + svalue.seatSequence;
                                oid.quantity++;
                                oid.sub_total = parseFloat(oid.sub_total) + parseFloat(seatPrice);
                                order_item_data[svalue.variation_id] = oid;
                            }
                            else{
                                order_item_data[svalue.variation_id] = [];
                                var oid = {};
                                var var_name = '';
                                if(price_option_data[svalue.variation_id]){
                                    var_name = price_option_data[svalue.variation_id].name
                                }
                                oid.variation_name = var_name;
                                oid.seatNo = svalue.seatSequence;
                                oid.quantity = 1;
                                oid.price = seatPrice;
                                oid.sub_total = seatPrice;
                                oid.variation_id = svalue.variation_id;
                                oid.event_id = $scope.event_id;
                                order_item_data[svalue.variation_id] = oid;
                            }
                        }
                        else{
                            if(order_item_data[0]){
                                var oid = order_item_data[0];
                                oid.seatNo += ',' + svalue.seatSequence;
                                oid.quantity++;
                                oid.sub_total = parseFloat(oid.sub_total) + parseFloat(seatPrice);
                                order_item_data[0] = oid;
                            }
                            else{
                                order_item_data[0] = [];
                                var oid = {};
                                oid.variation_name = '';
                                oid.seatNo = svalue.seatSequence;
                                oid.quantity = 1;
                                oid.price = seatPrice;
                                oid.sub_total = seatPrice;
                                oid.variation_id = '';
                                oid.event_id = $scope.event_id;
                                order_item_data[0] = oid;
                            }
                        }
                        subTotal = parseFloat(subTotal) + parseFloat(seatPrice);
                    });
                }
                $scope.order.order_item_data = order_item_data;
                $scope.order.order_ticket_price = subTotal;
                $scope.order.subtotal = subTotal;

                $scope.order.payment_gateway = "paypal";
                $scope.order.event_id = $scope.event_id;
                $scope.order.name=$scope.event.name;
                $scope.order.attendee_names=$scope.attendee_names;
                $scope.order.start_date=$scope.event.start_date;
                $scope.order.applied_coupon = 0;
                $scope.order.coupon_amount = 0;
                $scope.order.coupon_code = '';
                $scope.order.coupon_type = '';
                $scope.order.coupon_discount = 0;
                // check custom booking fields have data
                $scope.order.is_custom_booking_field = $scope.is_custom_booking_field;
                //mailpoet variables
                $scope.order.event_magic_mailpoet_optin_box=$scope.event_magic_mailpoet_optin_box;
                // check for woocommerce products
                $scope.order.cart_selected_product = [];
                $scope.order.cart_selected_product_variation_id = [];
                $scope.order.cart_selected_product_variation_price = [];
                $scope.order.woocommerce_products = [];
                $scope.order.billing_address = {};
                $scope.order.shipping_address = {};
                if($scope.ep_product_quantity){
                    angular.forEach($scope.ep_product_quantity, function (item, index) {
                        if(item > 0){
                            var pdata = {'id' : index, 'qty' : item};
                            $scope.order.cart_selected_product.push(pdata);
                        }
                    });
                    if($scope.ep_product_variation_id){                        
                        angular.forEach($scope.ep_product_variation_id, function(item, index){
                            var pdata = {'id' : index, 'variation' : item};
                            $scope.order.cart_selected_product_variation_id.push(pdata);
                        })
                    }
                    // variation price
                    if($scope.ep_product_variation_price){                        
                        angular.forEach($scope.ep_product_variation_price, function(item, index){
                            var pdata = {'id' : index, 'variation_price' : item};
                            $scope.order.cart_selected_product_variation_price.push(pdata);
                        })
                    }
                    EMRequest.send('em_get_woocommerce_event_cart_product', $scope.order).then(function (response) {
                        $scope.order.woocommerce_products = response.data.products;
                        $scope.order.billing_address = response.data.billing_address;
                        if(response.data.billing_address.billing_country && response.data.billing_address.billing_country !== ''){
                            $scope.getWoocommerceCountryState("billing_address", "billing_country", "billing_state");
                        }
                        
                        $scope.order.shipping_address = response.data.shipping_address;
                        $scope.applyWoocommerce = 1;
                        angular.forEach(response.data.products, function(item, index){
                            var psubtotal = item.sub_total;
                            $scope.order.subtotal += parseFloat(psubtotal);
                            $scope.price += parseFloat(psubtotal);
                        });
                        $scope.order.shipping_address.address_option = 'same';
                    });
                }
                // data for guest booking
                $scope.order.username = $scope.gbname;
                $scope.order.useremail = $scope.gbemail;
                $scope.order.userphone = $scope.gbphone;
                $scope.order.guest_booking_personal_info = $scope.gbdata;
                $scope.progressStart();
                EMRequest.send('em_book_seat', $scope.order).then(function (response) {
                    $scope.progressStop();
                    if ($scope.check_errors(response))
                    {
                        return true;
                    }
                    
                    $scope.order.order_id = response.data.order_id;
                    $scope.orders.push($scope.order);
                    $scope.selectedSeats= [];
                    $scope.calculate_discount();
                    $scope.calculate_price();
                    $scope.update_order_ids();
                    $scope.display_cart(true);
                    $scope.getPaypalFormValuesForGuestUser();
                    if($scope.event.allow_automatic_discounts == 1){
                    	$scope.calculate_ebd();
                    }
                    // Payment countdown timer
                    var timeInMinutes = 60*4 ,
                    display = document.querySelector('#em_payment_timer');
                    $scope.startTimer(timeInMinutes, display);
                    jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
                });
            }
        });
    }
    
    $scope.add_update_quantity= function()
    {   newVal= $scope.order.quantity;
         if ($scope.order.seat_post == undefined) {
            $scope.bookable = false;
            if ($scope.order.ticket_limit != 0 && newVal >= $scope.order.ticket_limit) {
                $scope.order.quantity = $scope.order.ticket_limit;
            }

            if ($scope.order.quantity > 0)
            {
                $scope.calculate_discount();
                $scope.progressStart();
                EMRequest.send('em_update_booking', $scope.order).then(function (response) {
                    $scope.progressStop();
                    $scope.check_errors(response);
                });
            }

        }
    }
    
    $scope.get_item_number= function(event_id)
    {  
        return $scope.event.name; 
    }
    
    $scope.display_cart= function(show)
    {
        if(show){
             $scope.show_cart= true; 
        }  
        else{
            $scope.show_cart= false;    } 
    }
    
    $scope.calculate_price= function()
    {
        $scope.price = 0;
        $scope.fixed_event_price = 0;
        $scope.item_numbers = 0
        for(var i = 0;i < $scope.orders.length;i++) {
            if($scope.orders[i].order_ticket_price && $scope.orders[i].order_ticket_price > 0){
                $scope.price = parseFloat($scope.price + $scope.orders[i].order_ticket_price);
            } else {
                $scope.price += $scope.orders[i].quantity * $scope.orders[i].single_price;
            }
            $scope.item_numbers += $scope.orders[i].quantity;
            // check for fixed event price
            if($scope.orders[i].fixed_event_price){
                $scope.price = parseFloat($scope.price + $scope.orders[i].fixed_event_price);
                $scope.fixed_event_price = parseFloat($scope.fixed_event_price + $scope.orders[i].fixed_event_price);
            }
        }
        
        if($scope.price > 0){
            if($scope.order.ebd_discount_amount){
                $scope.ebd = $scope.order.ebd_discount_amount;
                if($scope.order.applied_coupon == 1){
                    if($scope.order.ebd_disable_if_coupon_code_discount == 1){
                        var code_message = $scope.couponPopupMsg;
                        if($scope.code_message){
                            code_message += ' '+$scope.code_message;
                            $scope.couponPopupMsg = code_message;
                        }
                        $scope.ebd = 0;
                        $scope.order.applied_ebd = 0;
                        $scope.order.ebd_discount = '';
                        $scope.order.ebd_discount_amount = '';
                        $scope.order.ebd_discount_type = '';
                    }
                }
                if($scope.ebd){
                    var ebd_discount_amount = $scope.order.ebd_discount_amount;
                    var pp = $scope.price;
                    if(ebd_discount_amount){
                        $scope.price -= ebd_discount_amount;
                        if($scope.price < 0){
                            $scope.price = 0;
                            $scope.order.ebd_discount_amount = pp;
                        }
                    }
                }
            }
            $scope.price = $scope.price -$scope.discount;
            if($scope.couponCode){
                var discountAmt = $scope.order.coupon_amount;
                var discountType = $scope.order.coupon_type;
                if($scope.price){
                    var pp = $scope.price;
                    var sp = $scope.order.subtotal;
                    var dis = 0;
                    if(discountType == 'Percentage'){
                        ppdis = parseFloat(parseFloat((((pp / 100)) * discountAmt)).toFixed(2));
                        spdis = parseFloat(parseFloat((((sp / 100)) * discountAmt)).toFixed(2));
                    }
                    else{
                        ppdis = discountAmt;
                        spdis = discountAmt;
                    }
                    var changePP = pp - ppdis;
                    $scope.price = changePP;
                    if($scope.price < 0){
                        $scope.price = 0;
                    }
                    var changeSP = sp - spdis;
                    if($scope.order.subtotal < 0){
                        $scope.order.subtotal = 0;
                    }
                    $scope.order.applied_coupon = 1;
                    $scope.order.coupon_code = $scope.couponCode;
                    // if discount price greater then subtotal then discount will be subtotal price
                    if($scope.order.subtotal == 0){
                        $scope.order.coupon_discount = sp;
                    }
                }
            }
            // check for woocommerce products price
            if($scope.order.woocommerce_products){
                angular.forEach($scope.order.woocommerce_products, function(item, index){
                    var psubtotal = item.sub_total;
                    $scope.price += parseFloat(psubtotal);
                });
            }
        }
    }
    
    $scope.update_order_ids= function()
    {   var order_ids= [];
        for(var i=0;i<$scope.orders.length;i++)
               order_ids.push($scope.orders[i].order_id);
           
        $scope.order_ids= order_ids.join(',');    
    }
    
    $scope.startTimer= function(duration, display) {
       
                if($scope.is_timer_on)                    
                    return true;
        
                var start = Date.now(),
                diff,
                minutes,
                seconds,
                stop= false,
                counter=1;
                function timer() {
                    if(!stop)
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
                   
                   
                    if (diff <= 0){
                                        // add one second so that the count down starts at the full duration
                                        // example 04:00 not 03:59
                        start = Date.now() + 1000; 
                    }

                    if(diff == 0){
                        stop= true;
                        $scope.bookable= false;
                      
                        $scope.$apply();
                        jQuery(".kf-seat-table-popup").hide();
                        jQuery(".kf-standing-type-popup").hide();
                        //jQuery("#kf-reconfirm-popup").show();

                    }
                    else{
                        counter++;
                        jQuery("#em_payment_progress").width(counter*(100/240) + "%");
                    }
                    
                    
                    }
                };
                    // we don't want to wait a full second before the timer starts
                    
                    timer();
                    setInterval(timer, 1000);
                    $scope.is_timer_on= true;
            }

    // Submitting payment to Paypal
    $scope.proceedToPaypal = function () {
        $scope.progressStart();
        if ($scope.order.order_id > 0){
            var booking = {};
            booking.booking_id = $scope.order.order_id;
            booking.gateway = 'paypal';
            booking.all_order_data = $scope.order;
            if($scope.order.coupon_code){
                booking.coupon_code = $scope.order.coupon_code;
                booking.coupon_discount = $scope.order.coupon_discount;
                booking.coupon_amount = $scope.order.coupon_amount;
                booking.coupon_type = $scope.order.coupon_type;
            }
            var total_price= $scope.calculate_price();
            EMRequest.send('em_verify_booking', booking).then(function (response) {
                $scope.progressStop();
                if (response.data.success) {
                    if($scope.price == 0) {
                        if($scope.applyWoocommerce == 1){
                            $scope.validateOrderBillingShipping();
                        }
                        if($scope.setFocus == 0){
                            $scope.proceedWithoutPayment();
                        }
                    } else{
                        if($scope.isUserLoggedin == 0){
                            localStorage.setItem("guest_paypal_orderid", $scope.order.order_id);
                            localStorage.setItem("guest_paypal_booking", 1);
                        }
                        if($scope.applyWoocommerce == 1){
                            $scope.validateOrderBillingShipping();
                        }
                        if($scope.setFocus == 0){
                            jQuery("[name=emPaypalForm]").submit();
                        }
                    } 
                }
                else{
                    alert("There seems to be a problem. Please refresh the page and try again");
                }
            });
        } else {
            alert("There seems to be a problem. Please refresh the page and try again");
        }
    }

    // Submitting payment to Paypal
    $scope.proceedToModernPaypal = function () {
        $scope.progressStart();
        if ($scope.order.order_id > 0){
            jQuery('#booking_summary').addClass("ep-paypal-active");
            var booking = {};
            booking.booking_id = $scope.order.order_id;
            booking.gateway = 'paypal';
            booking.all_order_data = $scope.order;
            if($scope.order.coupon_code){
                booking.coupon_code = $scope.order.coupon_code;
                booking.coupon_discount = $scope.order.coupon_discount;
                booking.coupon_amount = $scope.order.coupon_amount;
                booking.coupon_type = $scope.order.coupon_type;
            }
            var total_price= $scope.calculate_price();
            EMRequest.send('em_verify_booking', booking).then(function (response) {
                $scope.progressStop();
                if (response.data.success) {
                    if($scope.price == 0) {
                        if($scope.applyWoocommerce == 1){
                            $scope.validateOrderBillingShipping();
                        }
                        if($scope.setFocus == 0){
                            $scope.proceedWithoutPayment();
                        }
                    } else{
                        if($scope.isUserLoggedin == 0){
                            localStorage.setItem("guest_paypal_orderid", $scope.order.order_id);
                            localStorage.setItem("guest_paypal_booking", 1);
                        }
                        if($scope.applyWoocommerce == 1){
                            $scope.validateOrderBillingShipping();
                        }
                        if($scope.setFocus == 0){
                            /* Render the PayPal button into #paypal-button-container */
                            var price = $scope.price;
                            var currency_code = $scope.currency_code;
                            
                            /* if no currency code found */
                            currency_code = ( currency_code != '' ) ? currency_code : 'USD';
                            var create_order_data = {};
                            create_order_data = $scope.order;
                            
                            var items = [];
                            
                            items.push(
                                {
                                    "name": create_order_data.name,
                                    "description": "",
                                    "unit_amount": {
                                    "currency_code": currency_code,
                                    "value": price
                                    },
                                    "quantity": "1"
                                }
                            );

                            paypal.Buttons({

                            // Set up the transaction
                            createOrder: function(data, actions) {
                                
                            return actions.order.create({
                                
                            "purchase_units": [{
                            "custom_id" : create_order_data.order_id,
                            "amount": {
                            "currency_code": currency_code,
                            "value": price,
                            "breakdown": {
                                "item_total": {
                                "currency_code": currency_code,
                                "value": price
                                },
                            }
                            },
                            "items": items
                            }]
                            });
                            },
                            // Finalize the transaction
                            onApprove: function(data, actions) {
                                
                                actions.order.capture().then(function(orderData) {
                                    // Successful capture!
                                    $scope.progressStart();
                                    $scope.paypalPaymentOnApprove(orderData);
                                    /* console.log(orderData); */
                                });
                            },
                            onError: function (err) {
                                alert("There seems to be a problem. Please refresh the page and try again");
                            },
                            }).render('#paypal-button-container').then(function() { 
                                /* jQuery("#paypal-button-container").trigger('click'); */
                            });
                        }
                    } 
                }
                else{
                    alert("There seems to be a problem. Please refresh the page and try again");
                }
            });
        } else {
            alert("There seems to be a problem. Please refresh the page and try again");
        }
    }
    
    $scope.proceedWithoutPayment= function()
    {   
        var booking = {};
        var order_ids= [];
        var id=[];
        for(var i=0;i<$scope.orders.length;i++){
            order_ids.push($scope.orders[i].order_id);
            booking.coupon_code = $scope.orders[i].coupon_code;
            booking.coupon_discount = $scope.orders[i].coupon_discount;
            booking.coupon_amount = $scope.orders[i].coupon_amount;
            booking.coupon_type = $scope.orders[i].coupon_type;
        }
        booking.booking_id = order_ids;
        booking.all_order_data = $scope.orders;
        //console.log(booking);
        $scope.progressStart();
        EMRequest.send('em_confirm_booking_without_payment', booking).then(function (res){
            $scope.progressStop();                
            if ($scope.check_errors(res))
            {
                return true;
            }
            if(res.data.guest_booking && res.data.guest_booking == 1){
                $scope.data.gbid = $scope.orders[0].order_id;
                $scope.data.redirect_url = res.data.redirect;
                /*EMRequest.send('em_guest_booking_show_order_detail', $scope.data).then(function (response) {
                    jQuery("#booking_dialog").append(response.data);
                });*/

                location.href= res.data.redirect;
            }
            else{
                location.href= res.data.redirect;
            }
        });
    }
    
    // Calculate discount if configured
    $scope.calculate_discount = function () {
        $scope.discount = 0;
        if ($scope.order.en_ticket && $scope.order.allow_discount) {
            for(var i = 0;i < $scope.orders.length;i++) {
                if ($scope.orders[i].quantity >= $scope.orders[i].discount_no_tickets) {
                    total_price = $scope.orders[i].order_ticket_price;
                    $scope.discount += parseFloat(parseFloat(((total_price / 100) * $scope.orders[i].discount_per)).toFixed(2));
                } else {
                    // Making sure discount is 0
                    $scope.discount += 0;
                }   
            }
        }
    }
    
    $scope.check_errors = function (response)
    {
        if (response.data.errors)
        {
            if (response.data.errors.error_capacity) {
                $scope.errorMsg= response.data.errors.error_capacity[0];
                return true;
            }

            if (response.data.errors.booking_expired) {
                $scope.bookable = false;
                return true;
            }
            
            if (response.data.errors.booking_finished) {
                $scope.errorMsg=response.data.errors.booking_finished[0];
                return true;
            }
            
            if (response.data.errors.seat_conflict) {
                $scope.errorMsg= response.data.errors.seat_conflict[0];
                return true;
            } 

        }

        $scope.bookable = true;
        return false;
    }

    /**************************** Seating selection code *************/
    $scope.selectSeat = function (seat, row, col, scolor, sel_color) {
        // Preventing reserver,blocked and sold seats from selection
        if (seat.type == "reserve" || seat.type == "tmp" || seat.type == "sold") {
            return;
        }

        if (seat.type == 'selected' && seat.type != 'general'){
            // If seat already selected then deselect it.
            var index = $scope.selectedSeats.indexOf(seat);
            if (index >= 0) {
                $scope.selectedSeats.splice(index, 1);
                $scope.attendee_names.splice($scope.attendee_names.length-1,1);
            }
            seat.type = 'general';
            if(seat.mainSeatColor && seat.mainSeatColor !== '#null'){
                seat.seatColor = seat.mainSeatColor
                seat.seatBorderColor = seat.mainSeatBorderColor
            }else{
                seat.seatColor = '#'+scolor;
                seat.seatBorderColor = '3px solid #'+scolor;
            }
            // remove seat price from booking
            var selectedSeatPrice = seat.price;
            if(isNaN(selectedSeatPrice) && selectedSeatPrice.indexOf('-') > -1) {
                var spl = seat.price.split('-');
                selectedSeatPrice = spl[1];
            }
            $scope.em_selected_seat_price -= selectedSeatPrice;
        } else {
            // If number of tickets are  more than configured limit
            if (($scope.event.max_tickets_per_person != 0 && $scope.selectedSeats.length == $scope.event.max_tickets_per_person) || ($scope.selectedSeats.length==$scope.event.available_seats)) {
                angular.forEach($scope.selectedSeats, function (seat, key) {
                    seat.type = "general";
                    if(seat.mainSeatColor && seat.mainSeatColor !== '#null'){
                        seat.seatColor = seat.mainSeatColor
                        seat.seatBorderColor = seat.mainSeatBorderColor
                    }else{
                        seat.seatColor = '#'+scolor;
                        seat.seatBorderColor = '3px solid #'+scolor;
                    }
                    // remove seat price from booking
                    var selectedSeatPrice = seat.price;
                    if(isNaN(selectedSeatPrice) && selectedSeatPrice.indexOf('-') > -1) {
                        var spl = seat.price.split('-');
                        selectedSeatPrice = spl[1];
                    }
                    $scope.em_selected_seat_price -= selectedSeatPrice;
                });
                $scope.selectedSeats = [];
                $scope.attendee_names = [];
            }
            seat.type = 'selected';
            /*seat.mainSeatColor = seat.seatColor;
            seat.mainSeatBorderColor = seat.seatBorderColor;*/
            seat.seatColor = '#'+sel_color;
            seat.seatBorderColor = '3px solid #'+sel_color;
            $scope.selectedSeats.push(seat);
            if($scope.event.custom_booking_field_data && $scope.event.custom_booking_field_data.length > 0){
                var nkey = $scope.attendee_names.length;
                $scope.attendee_names[nkey] = {};
                $scope.event.custom_booking_field_data.forEach(function(item, key){
                    if(item){
                        $scope.attendee_names[nkey][key] = {};
                        $scope.attendee_names[nkey][key][item.type] = {};
                        $scope.attendee_names[nkey][key][item.type][item.label] = {};
                        $scope.attendee_names[nkey][key][item.type][item.label].value = '';
                        setTimeout(function(){
                            jQuery(".em-cbf-datepicker").datepicker({
                                changeMonth: true,
                                changeYear: true
                            });
                        }, 500);
                    }
                });
            }
            else{
                $scope.attendee_names.push('');
            }
            // update attendee info title
            var infoId = $scope.attendee_names.length;
            var infoHtml = 'Attendee ' + infoId + '.';
            if(seat.seatSequence){
                infoHtml += ' (Seat No. ' + seat.seatSequence + ')';
            }
            $scope.seatAttendeeInfo[infoId] = infoHtml;
            // add seat price
            var selectedSeatPrice = seat.price;
            if(isNaN(selectedSeatPrice) && selectedSeatPrice.indexOf('-') > -1) {
                var spl = seat.price.split('-');
                selectedSeatPrice = spl[1];
            }
            $scope.em_selected_seat_price = parseFloat($scope.em_selected_seat_price) + parseFloat(selectedSeatPrice);
        }
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

    $scope.adjustContainerWidth= function(columnMargin,index)
    {
        var width= parseInt($scope.seat_container_width.width);
        if(index==0 && columnMargin>0)
        {  
            width += columnMargin;
            $scope.seat_container_width.width = width + "px";
        }
        
    }
    
    $scope.$watch('booking_quantity',function(newVal,oldVal){
        if(newVal > $scope.attendee_names.length) {
            while($scope.attendee_names.length < newVal) {
                if($scope.event.custom_booking_field_data && $scope.event.custom_booking_field_data.length > 0){
                    var nkey = $scope.attendee_names.length;
                    $scope.attendee_names[nkey] = {};
                    $scope.event.custom_booking_field_data.forEach(function(item, key){
                        if(item){
                            $scope.attendee_names[nkey][key] = {};
                            $scope.attendee_names[nkey][key][item.type] = {};
                            $scope.attendee_names[nkey][key][item.type][item.label] = {};
                            $scope.attendee_names[nkey][key][item.type][item.label].value = '';

                            setTimeout(function(){
                                jQuery(".em-cbf-datepicker").datepicker({
                                    changeMonth: true,
                                    changeYear: true
                                });
                            }, 500);
                        }
                    });
                }
                else{
                    $scope.attendee_names.push('');
                }
                // update attendee info title
                var infoId = $scope.attendee_names.length;
                var infoHtml = 'Attendee ' + infoId + '.';
                $scope.standingAttendeeInfo[infoId] = infoHtml;
            }
        } else if(newVal < $scope.attendee_names.length) {
            while($scope.attendee_names.length > newVal) {
                $scope.attendee_names.splice($scope.attendee_names.length-1,1);
            }
        }
    });
    
    $scope.checkGuestValidations = function () {
        var emgberror = false;
        $scope.formErrors = [];
        if($scope.event.custom_guest_booking_field_data && $scope.event.custom_guest_booking_field_data.length > 1){
            var fieldError = false;
            angular.forEach($scope.event.custom_guest_booking_field_data, function(value, key){
                jQuery(".em-gb-data-"+key).html('');
                if(value && !fieldError){
                    if(value.required == 1){
                        if(!$scope.gbdata[key]){
                            jQuery(".em-gb-data-"+key).html(em_booking_js_vars.error_msg.required_field);
                            let gbField = angular.element('#ep-gbinput-'+$scope.event.venue.type+'-'+key);
                            gbField.focus();
                            fieldError = true;
                            return false;
                        }
                    }
                    if(value.type == 'email' && !fieldError){
                        if($scope.gbdata[key]){
                            let gbemail = $scope.gbdata[key].value;
                            var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                            if(!regex.test(gbemail)) {
                                jQuery(".em-gb-data-"+key).html(em_booking_js_vars.error_msg.invalid_email);
                                let gbField = angular.element('#ep-gbinput-'+$scope.event.venue.type+'-'+key);
                                gbField.focus();
                                fieldError = true;
                                return false;
                            }
                            else{
                                fieldError = false;
                            }
                        }
                    }
                    if(value.type == 'tel' && !fieldError){
                        if($scope.gbdata[key]){
                            let telData = $scope.gbdata[key].value;
                            if(telData){
                                let phoneFr =  telData.replace(/[^0-9]/g, '');
                                if(!jQuery.isNumeric(phoneFr)|| phoneFr.length < 10){
                                    jQuery(".em-gb-data-"+key).html(em_booking_js_vars.error_msg.invalid_phone);
                                    let gbField = angular.element('#ep-gbinput-'+$scope.event.venue.type+'-'+key);
                                    gbField.focus();
                                    fieldError = true;
                                    return false;
                                }
                                $scope.gbdata[key].value = phoneFr.replace(/[^\d\+\-\()]/g, '');
                            }
                        }
                    }
                }
                if(fieldError === true){
                    return false;
                }
            });
            if(fieldError === true){
                return false;
            }
        }
        else{
            jQuery("#em_gb_pi_name_error").html('');
            jQuery("#em_gb_pi_email_error").html('');
            jQuery("#em_gb_pi_phone_error").html('');
            var name = angular.element(".em_gb_pi_name");
            if(name.length < 1){
                return true;
            }
            if(!$scope.gbname){
                jQuery("#em_gb_pi_name_error").html(em_booking_js_vars.error_msg.required_name);
                name.focus();
                return false;
            }
            if(!$scope.gbemail){
                jQuery("#em_gb_pi_email_error").html(em_booking_js_vars.error_msg.required_email);
                var email = angular.element(".em_gb_pi_email");
                email.focus();
                return false;
            } else if($scope.gbemail !== ''){
                var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if(!regex.test($scope.gbemail)) {
                    jQuery("#em_gb_pi_email_error").html(em_booking_js_vars.error_msg.invalid_email);
                    var email = angular.element(".em_gb_pi_email");
                    email.focus();
                    return false;
                }
            }
            if(!$scope.gbphone){
                jQuery("#em_gb_pi_phone_error").html(em_booking_js_vars.error_msg.required_phone);
                var phone = angular.element(".em_gb_pi_phone");
                phone.focus();
                return false; 
            } else if($scope.gbphone !== ''){
                var phoneFr = $scope.gbphone;
                $scope.gbphone =  $scope.gbphone.replace(/[^0-9]/g, '');
                if(!jQuery.isNumeric($scope.gbphone)|| $scope.gbphone.length < 10){
                    jQuery("#em_gb_pi_phone_error").html(em_booking_js_vars.error_msg.invalid_phone);
                    var phone = angular.element(".em_gb_pi_phone");
                    phone.focus();
                    return false;
                }
                $scope.gbphone = phoneFr.replace(/[^\d\+\-\()]/g, '');
            }
        }
        return true;
    }

    $scope.getPaypalFormValuesForGuestUser = function () {
        var pname = jQuery("[name='first_name']");
        var pemail = jQuery("[name='email']");

        if(pname.length && pname.val() == ''){
            pname.val($scope.gbname);
        }

        if(pemail.length && pemail.val() == ''){
            pemail.val($scope.gbemail);
        }
    }

    /* Coupon apply method */
    $scope.applyCoupon = function() {
        if($scope.couponCode){
            $scope.progressStart();
            var cdata = {'couponcode' : $scope.couponCode, 'event_id' : $scope.order.event_id};
            EMRequest.send('em_apply_event_coupon', cdata).then(function(response){
                var responseBody = response.data;
                if(responseBody.success){
                    if(responseBody.data.hasOwnProperty('postdata')){
                        if(responseBody.data.postdata && responseBody.data.postdata.discount){
                            var discountAmt = responseBody.data.postdata.discount;
                            if(responseBody.data.postdata.discount_type){
                                var discountType = responseBody.data.postdata.discount_type;
                                if($scope.price){
                                    /* var pp = $scope.price; */
                                    /* fixed : used order ticket price instead of price */
                                    var pp = $scope.order.order_ticket_price;
                                    var sp = $scope.order.subtotal;
                                    var dis = 0;
                                    if(discountType == 'Percentage'){
                                        ppdis = parseFloat(parseFloat((((pp / 100)) * discountAmt)).toFixed(2));
                                        spdis = parseFloat(parseFloat((((sp / 100)) * discountAmt)).toFixed(2));
                                    }
                                    else{
                                        ppdis = discountAmt;
                                        spdis = discountAmt;
                                    }
                                    var changePP = pp - ppdis;
                                    $scope.price = changePP;
                                    if($scope.price < 0){
                                        $scope.price = 0;
                                    }
                                    var changeSP = sp - spdis;
                                    //$scope.order.subtotal = changeSP;
                                    if($scope.order.subtotal < 0){
                                        $scope.order.subtotal = 0;
                                    }
                                    $scope.order.applied_coupon = 1;
                                    $scope.order.coupon_code = $scope.couponCode;
                                    $scope.order.coupon_amount = discountAmt;
                                    $scope.order.coupon_type = discountType;
                                    /* $scope.order.coupon_discount = spdis; */
                                    $scope.order.coupon_discount = ppdis;
                                    // if discount price greater then subtotal then discount will be subtotal price
                                    if($scope.order.subtotal == 0){
                                        $scope.order.coupon_discount = sp;
                                    }
                                    /* $scope.coupon_code_discount_amount = spdis; */
                                    $scope.coupon_code_discount_amount = ppdis;
                                    $scope.couponAppliedPopup = 1;
                                    $scope.couponCodeApplied = 1;
                                    $scope.couponPopupMsg = responseBody.data.success;
                                    $scope.calculate_price();
                                }
                            }
                        }
                    }
                }
                else{
                    if(responseBody.data.hasOwnProperty('errors')){
                        $scope.couponPopupMsg = responseBody.data.errors;
                        $scope.couponAppliedPopup = 1;
                    }
                }
                $scope.progressStop();
            });
        }
    };

    /* Coupon cancel method */
    $scope.cancelCoupon = function() {
        if($scope.order.applied_coupon){
            var coupon_discount = $scope.order.coupon_discount;
            var quantity = $scope.order.quantity;
            var sub_total = $scope.order.subtotal;
            $scope.price = $scope.order.single_price * quantity;
            $scope.order.subtotal = $scope.price;
            $scope.order.applied_coupon = 0;
            $scope.couponCanceledPopup = 1;
            $scope.couponCodeApplied = 0;
            $scope.order.coupon_amount = 0;
            $scope.order.coupon_code = '';
            $scope.order.coupon_type = '';
            $scope.order.coupon_discount = 0;
            $scope.coupon_code_discount_amount = 0;
            $scope.couponCode = '';
            $scope.calculate_price();
        }
    };

    $scope.hideCouponPopup = function(){
        $scope.couponAppliedPopup = 0;
        $scope.couponCanceledPopup = 0;
    }
    
    // calculate early bird discount
    $scope.calculate_ebd = function(){
        var edata = {'event_id' : $scope.order.event_id};
        $scope.progressStart();
        EMRequest.send('em_apply_event_ebd', edata).then(function(response){
            if(response.data){
                if(response.data.data){
                    var active_rule = response.data.data.active_rule;
                    if(active_rule && active_rule.id){
                        var discountAmt = active_rule.discount;
                        var discountType = active_rule.discount_type;
                        if($scope.price){
                            var pp = $scope.price;
                            if(discountType && discountAmt){
                                if(discountType == 'percentage'){
                                    ppdis = parseFloat(parseFloat((((pp / 100)) * discountAmt)).toFixed(2));
                                }
                                else{
                                    ppdis = discountAmt;
                                    if(discountAmt > pp){
                                        ppdis = pp;
                                    }
                                }
                            }
                            if(active_rule.rule_type == 'seat'){
                                var noOfSeat = active_rule.no_of_seat;
                                var order_seat_qty = $scope.order.quantity;
                                var order_seat_single_price = $scope.order.single_price;
                                if(noOfSeat > order_seat_qty || noOfSeat == 0){
                                    ppdis = parseFloat(order_seat_qty * order_seat_single_price).toFixed(2);
                                }
                                else{
                                    ppdis = parseFloat(noOfSeat * order_seat_single_price).toFixed(2);   
                                }
                            }
                            var changePP = pp - ppdis;
                            $scope.ebd = ppdis;
                            $scope.price = changePP;                            
                            $scope.order.applied_ebd = 1;
                            $scope.order.ebd_id = active_rule.id;
                            $scope.order.ebd_name = active_rule.name;
                            $scope.order.ebd_rule_type = active_rule.rule_type;
                            $scope.order.ebd_discount_type = active_rule.discount_type;
                            $scope.order.ebd_discount = active_rule.discount; 
                            $scope.order.ebd_discount_amount = $scope.ebd; 
                            if($scope.price < 0){
                                $scope.price = 0;
                                $scope.order.ebd_discount_amount = pp;
                            }
                            $scope.order.ebd_disable_if_coupon_code_discount = active_rule.disable_if_coupon_code_discount;
                            $scope.code_message = '';
                            if(active_rule.code_message){
                                $scope.code_message = active_rule.code_message;
                            }
                            $scope.calculate_price();
                        }
                    }
                }
            }
            $scope.progressStop();
        });
    }

    $scope.showAttendeeInfoBlock = function(index){
        if(jQuery("#attendee-info-block-"+index).css('display') == 'none'){
            jQuery("#attendee-info-block-"+index).css('display', 'block');
            jQuery(".ep-attendee-info-head span.ep-attendee-toggle-"+index).text('remove');
        }
        else{
            jQuery("#attendee-info-block-"+index).css('display', 'none');
            jQuery(".ep-attendee-info-head span.ep-attendee-toggle-"+index).text('add');
        }
    }

    // functions for woocommerce
    $scope.removeBookingProduct = function(pid){
        angular.forEach($scope.ep_product_quantity, function(item, index){
            if(index == pid) {
               $scope.ep_product_quantity[index] = 0;
            }
        });
        $('[name="ep_product_quantity['+pid+']"]').closest('tr').remove();
    }

    // get woocommerce state by country code
    $scope.getWoocommerceCountryState = function(address, item, target){
        $scope.progressStart();
        var addval = $scope.order[address][item];
        if(addval){
            $scope.data.country_code = addval;
            EMRequest.send('em_get_woocommerce_state_by_country_code', $scope.data).then(function (response) {
                if(response.data){
                    var statelist = response.data;
                    jQuery("#"+target).empty();
                    jQuery("#"+target).append(new Option("Select an option…", ""));
                    angular.forEach(statelist, function(item, idx){
                       jQuery("#"+target).append(new Option(item, idx));
                    });
                    if(address == "billing_address" && $scope.order.billing_address.billing_state !== ''){
                        jQuery("#"+target).val($scope.order.billing_address.billing_state);
                    }
                }
                $scope.progressStop();
            });
        }
    }

    // reset products on booking page
    $scope.reset_woo_products_on_booking_page = function(){
        $scope.progressStart();
        $scope.request = {};
        $scope.request.event_id = $scope.event_id;
        EMRequest.send('em_reset_woo_products_booking_page', $scope.request).then(function (response) {
            jQuery(".ep-product-info-block").html(response.data.data);
            $scope.is_reset_woo_product = true;
            $scope.progressStop();
        });
    }

    $scope.$watch('is_reset_woo_product',function(val){
        if(val){
            $compile($('.ep-product-info-block'))($scope);
        }
    });

    $scope.updateMultiPriceOption = function(option){
        if(option){
            var mpoption_price = option.price;
            if(option.special_price){
                mpoption_price = option.special_price;
            }
            if(jQuery(".em_event_old_price").length > 0){
                if($scope.event.ebd_active_rule_data && $scope.event.ebd_active_rule_data.active_rule){
                    var active_rule = $scope.event.ebd_active_rule_data.active_rule;
                    if(active_rule.discount){
                        var discountAmt = active_rule.discount;
                        var discountType = active_rule.discount_type;
                        if(discountType && discountAmt){
                            var pp = mpoption_price;
                            if(discountType == 'percentage'){
                                ppdis = parseFloat(parseFloat((((pp / 100)) * discountAmt)).toFixed(2));
                            }
                            else{
                                ppdis = discountAmt;
                                if(discountAmt > pp){
                                    ppdis = pp;
                                }
                            }
                            mpoption_price = pp - ppdis;
                        }
                        var mpoption_price = $filter('currencyPosition')(mpoption_price, $scope.event.currency_position, $scope.currency_symbol);
                    }
                }
                jQuery(".em_event_old_price").hide();
                jQuery(".em_event_special_price").html(mpoption_price);
            }
            else{
                jQuery(".em-booking-ticket-price").html(mpoption_price);
            }
        }
    }

    $scope.validateOrderBillingShipping = function(){
        $scope.billingErrors = [];
        $scope.shippingErrors = [];
        $scope.setFocus = 0;
        angular.forEach($scope.order.billing_address, function(item, index){
            if(!item){
                var itemElem = angular.element("#"+index);
                if(itemElem){
                    var itemReq = itemElem.data('field_required');
                    if(itemReq){
                        $scope.billingErrors.push(itemReq);
                        if($scope.setFocus == 0){
                            itemElem.focus();
                            $scope.setFocus = 1;
                        }
                        return false;
                    }
                }
            }
        });
        if($scope.order.shipping_address.address_option == 'same'){
            var shiAdd = {};
            angular.forEach($scope.order.shipping_address, function(item, index){
                if(index && index !== 'address_option'){
                    shiAdd[index] = '';
                    var sapItem = index.replace('shipping_', '');
                    shiAdd[index] = $scope.order.billing_address['billing_'+sapItem];
                }
            });
            $scope.order.shipping_address = shiAdd;
        }
        angular.forEach($scope.order.shipping_address, function(item, index){
            if(!item){
                var itemElem = angular.element("#"+index);
                if(itemElem){
                    var itemReq = itemElem.data('field_required');
                    if(itemReq){
                        $scope.shippingErrors.push(itemReq);
                        if($scope.setFocus == 0){
                            itemElem.focus();
                            $scope.setFocus = 1;
                        }
                        return false;
                    }
                }
            }
        });
        if($scope.setFocus == 1){
            return false;
        }
    }

    $scope.paypalPaymentOnApprove= function(orderData)
    {
        EMRequest.send('event_magic_pp_sbpr', orderData).then(function (response) {
            $scope.progressStop();
            if (response.data.success) {
                // Or go to another URL:  
                /* actions.redirect('url'); */
                /* actions.redirect(response.data.data.url); */
                window.location = response.data.data.url;
            }else{
                alert("There seems to be a problem. Please refresh the page and try again");
            }
        }); 
    }
    
    $scope.epStringSplit = function(string, nb) {
        var array = string.split(',');
        return array[nb];
    }
});
eventMagicApp.filter('unsafe', function($sce) {
    return function(val) {
        return $sce.trustAsHtml(val);
    };
});
eventMagicApp.filter('currencyPosition', function($sce) {
    return function(val, position, symbol) {
        if(isNaN(parseInt(val)) === true){
            return $sce.trustAsHtml(val);
        }
        if(position == 'before'){
            return symbol + val;
        }
        if(position == 'before_space'){
            return symbol + ' ' + val;
        }
        if(position == 'after'){
            return val + symbol;
        }
        if(position == 'after_space'){
            return val + ' ' + symbol;
        }
    };
});
eventMagicApp.filter('currencyPositionWithHtml', function($sce) {
    return function(val, position, symbol) {
        var currhtml = '';
        if(!val){
            val = 0;
        }
        if(position == 'before'){
            currhtml = symbol + '<span class="em-booking-ticket-price">' + val + '</span>';
        }
        if(position == 'before_space'){
            currhtml = symbol + ' ' + '<span class="em-booking-ticket-price">' + val + '</span>';
        }
        if(position == 'after'){
            currhtml = '<span class="em-booking-ticket-price">' + val + '</span>' + symbol;
        }
        if(position == 'after_space'){
            currhtml = '<span class="em-booking-ticket-price">' + val + '</span>' + ' ' + symbol;
        }

        return $sce.trustAsHtml(currhtml);
    };
});