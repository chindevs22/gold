
/************************************* Event Controller ***********************************/

eventMagicApp.controller('eventCtrl', function ($scope, $http, MediaUploader, Seat, TermUtility, EMRequest, PostUtility, $location) {
    // Model for Event data
    $scope.data = {};
    $scope.screen='';
    $scope.data.manager_nav= "event";
    $scope.requestInProgress= false;
    $scope.selectedSeats = [];
    $scope.post_id = 0;
    $scope.paged=1;
    $scope.selections= [];
    $scope.post_edit= false;
    $scope.seat_container_width='';
    $scope.progressBarStyle='';
    $scope.scheme_popup =  false;
    $scope.seatPopup= false;
    $scope.IsVisible = false;
    $scope.data.full_load= true;
    $scope.mapConfigured=false;
    $scope.editingAddress=false;
    $= jQuery;
    
    angular.element(document).ready(function () {
        em_set_date_defaults();
    });
    /*
     * Controller intitialization for data load 
     */
    $scope.initialize = function (screen) {
        if (screen == "list") {
            $scope.prepareEventListPage();
            $scope.screen='list';
        }
        else
        {
            $scope.screen=screen;
            $scope.preparePageData();
            // Adding Jquery Sortable function for Gallery
            jQuery("#em_draggable").sortable({
                stop: function (event, ui) {
                    $scope.data.post.gallery_image_ids = jQuery("#em_draggable").sortable('toArray');
                    $scope.$apply();
                }
            });
        }
    }
    
    angular.element(document).ready(function () {
        em_set_date_defaults();
    });
    
    /* Show/Hide element while performing HTTP request */
    $scope.progressStart= function()
    {
        $scope.requestInProgress = true;
    }
    
    $scope.progressStop= function()
    {
        $scope.requestInProgress = false;
    }
    
    /********************** Add/Edit event functions*******************************/
    /*
    * 
    * Loading seat container width as per initial seating structure
    */ 
    $scope.setSeatContainerWidth= function()
    {
        if ($scope.data.post.seats!== undefined && $scope.data.post.seats.length>0) {

                 var seat_container_width= ($scope.data.post.seats[0].length*35) + 80 + "px";
                 $scope.seat_container_width={ "width" : seat_container_width };

         }
    }
   
    /*
     * 
     * WordPress default media uploader to choose image
     */
    $scope.mediaUploader = function (multiImage, model_name = false) {
        var mediaUploader = MediaUploader.openUploader(multiImage);
        mediaUploader.on('select', function () {
            attachments = mediaUploader.state().get('selection');
            attachments.map(function (attachment) {
                attachment = attachment.toJSON();
                if (multiImage) {
                    // For gallery images
                   // var imageObj = {src: [attachment.sizes.thumbnail.url], id: attachment.id};
                  var imageObj = attachment.sizes.thumbnail===undefined ? {src: [attachment.sizes.full.url], id: attachment.id} : {src: [attachment.sizes.thumbnail.url], id: attachment.id};
                    $scope.data.post.images.push(imageObj);

                    $scope.data.post.gallery_image_ids.push(attachment.id);

                } else {
                    // For cover image
                    if( model_name ){
                        $scope[model_name].cover_image_id = attachment.id;
                        $scope[model_name].cover_image_url = attachment.sizes.thumbnail===undefined ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;
                    } else{
                        $scope.data.post.cover_image_id = attachment.id;
                        $scope.data.post.cover_image_url = attachment.sizes.thumbnail===undefined ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;
                    }
                }

                $scope.$apply();
            });
        });
        // Open the uploader dialog
        mediaUploader.open();
    }

    /*
     * Empty gallery images
     */  
    $scope.deleteGalleryImage = function (image_id, index, image_model, ids) { 
        image_model.splice(index, 1);
        ids = em_remove_from_array(ids, image_id);
    }
    
    $scope.$watch('data.post.allow_discount', function (newVal, oldVal) {
        if (parseInt(newVal) == 1)
            jQuery("#em_volume_discount").show("slow");
        else
            jQuery("#em_volume_discount").hide("slow");
    });

    /*
     * Verifying event capacity with venue capcity 
     */
    $scope.verify_capacity= function(newVal)
    {
        var data= {};
        data.venue_id= $scope.data.post.venue;
        data.event_id= $scope.data.post.id;
         
        $scope.progressStart();
        EMRequest.send('em_get_venue_capcity',data).then(function(response){
        //    alert(response.data.capacity);  
                $scope.data.venue_capacity= response.data.capacity;
                if($scope.data.venue_capacity>0 && newVal>$scope.data.venue_capacity)
                    $scope.postForm.seating_capacity.$setValidity("capacityExceeded", false);
                else
                    $scope.postForm.seating_capacity.$setValidity("capacityExceeded", true);
                $scope.progressStop();
            });
    }
    
    /*
     * 
     * Adding new performer
     */
    $scope.addPerformer = function () {
        var performer = {role: "", name: ""};

        // Add custom performer fields only once
        if ($scope.data.post.custom_performers.length == 0)
            $scope.data.post.custom_performers.push(performer);
    }

    /* Adding date in calendar */
    $scope.addDate = function (date,element) {
        if (jQuery.inArray(date, element) < 0){
            element.push(date);
        }
    }
    
    /* 
     * Removing date from calendar
     */
    $scope.removeDate = function (index,element) {
        if(element===undefined)
            return;
        element.splice(index, 1);
        $scope.$apply();
    }

    // Adds a date if we don't have it yet, else remove it
    $scope.addOrRemoveDate = function (date,element) {
        var index = jQuery.inArray(date, element);
        if (index >= 0)
            $scope.removeDate(index,element);
        else           
            $scope.addDate(date,element);
        
            
    }
    // Takes a 1-digit number and inserts a zero before it
    $scope.padNumber = function (number) {
        var ret = new String(number);
        if (ret.length == 1)
            ret = "0" + ret;
        return ret;
    }
    
    /*
     * 
     * Save Add/Edit event from main event/dashboard pages.
     */
    $scope.savePost = function (isValid) { 
        $scope.resetSelections();
        // If form is valid against all the angular validations
        if (isValid) {
            if( jQuery('#description').is(':visible') ) {
                $scope.data.post.description= jQuery('#description').val();
            } 
            else 
            {
                if(typeof tinymce!="undefined"){
                    if(tinymce.get('description')){
                        $scope.data.post.description= tinymce.get('description').getContent();
                    }
                }
                   
            }
            if($('#custom_booking_confirmation_email_body').is(':visible')){
                $scope.data.post.custom_booking_confirmation_email_body = $('#custom_booking_confirmation_email_body').val();
            } else {
                if(typeof tinymce != "undefined" && tinymce.get('custom_booking_confirmation_email_body')){
                    $scope.data.post.custom_booking_confirmation_email_body = tinymce.get('custom_booking_confirmation_email_body').getContent();
                }
            }
            $scope.data.post.id= $scope.post_id;
            $scope.data.post.screen= $scope.screen;
            $scope.progressStart();
            $newVenueAddressInp= $("#em-pac-input");
            if($newVenueAddressInp.length>0){
                $scope.data.post.new_venue_address=$newVenueAddressInp.val();
            }
            $scope.data.post.em_event_nonce = em_event_object.nonce;
            EMRequest.send('em_save_event',$scope.data.post).then(function(response){
                $scope.progressStop();
                var responseBody= response.data;
                if(responseBody.success){
                    if(responseBody.data.status_changed) {
                        EMRequest.send('em_event_approved_mail',responseBody.data.event).then(function(response){});
                    }
                    if(responseBody.data.hasOwnProperty('redirect')){
                        location.href=responseBody.data.redirect;
                    }
                }
                else
                {
                    if(responseBody.data.hasOwnProperty('errors')){
                        $scope.formErrors= responseBody.data.errors;
                        jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
                    }
                }
            });
        }        
    }
    
    /*
     * 
     * Loading Event data on init
     */
    $scope.preparePageData = function () {
        $scope.progressStart();
        
        $scope.data.em_request_context = 'admin_event';
        $scope.data.em_event_nonce = em_event_object.nonce;
        // If "Edit" page
        if (em_get('post_id') > 0) {
            $scope.post_id = em_get('post_id');
            $scope.data.post_id= $scope.post_id;
            $scope.post_edit= true;
        } 
        
        // HTTP request to load data
        EMRequest.send('em_load_strings',$scope.data).then(function(response){
            var responseBody= response.data;
            if(responseBody.success){
                $scope.data= responseBody.data;
                $scope.initializeDateTimePickers($scope.data.post.datepicker_format);
                $scope.setSeatContainerWidth();
                $scope.data.full_load= false;
                if($scope.data.post.event_text_color)
                    $("#em_color_picker").css("background-color","#" + $scope.data.post.event_text_color);
            }
            $scope.progressStop();
        });
    };
    
    $scope.initializeDateTimePickers= function(datepicker_format)
    {
        var minDate= new Date();
        var maxDate;
        //$('#event_start_date').datetimepicker({controlType: 'select',oneLine: true,changeYear: true,minDate: new Date()});
        //$("#event_end_date").datetimepicker({controlType: 'select',changeYear: true,oneLine: true,timeFormat: 'HH:mm',minDate: new Date()});
        $('#event_start_date').datetimepicker({controlType: 'select',oneLine: true,changeYear: true, timeInput: true, dateFormat: datepicker_format});
        $("#event_end_date").datetimepicker({controlType: 'select',changeYear: true,oneLine: true,timeFormat: 'HH:mm', timeInput: true, dateFormat: datepicker_format});
        $("#event_start_booking_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: datepicker_format});
        $("#event_last_booking_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: datepicker_format});
    }
     
     $scope.update_start_booking_date=function(){   
        var maxDate=document.getElementById("event_end_date").value;
        $("#event_start_booking_date").datetimepicker('change', {controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true,maxDate:maxDate, timeInput: true});
     }
     
    /*
     * Compare dates
     */
    $scope.compareDate= function(start_date,end_date,pattern){
        return (jQuery.datepicker.parseDate(pattern,start_date) >  jQuery.datepicker.parseDate(pattern,end_date));
    }
    /*
     * Fetch capacity and seating structure from venue.
     */
    $scope.getCapacity = function() {
        var data = {};
        data.venue_id = $scope.data.post.venue; 
        if(data.venue_id==0)
            return;
        data.event_id = $scope.data.post.id;
        $scope.progressStart();
        EMRequest.send('em_get_venue_capcity',data).then(function(response){
            $scope.progressStop();
            $scope.data.post.seating_capacity = parseInt(response.data.capacity);
            $scope.data.post.seats = response.data.seats;
            $scope.data.post.venue_type = response.data.venue_type;
            $scope.data.post.standing_capacity  = parseInt(response.data.stand_capacity);
            if(data.venue_id == 'new_venue'){
                $scope.data.post.standing_capacity = 999;
            }
            $scope.setSeatContainerWidth();
        });
    }
    
    /*
     * Select seat column for bulk opeartion (like: Reserver or reset)
     */
    $scope.selectColumn = function (index) {
      $scope.resetSelections();
      for (var i = 0; i < $scope.data.post.seats.length; i++) {

          if($scope.data.post.seats[i][index].type == 'general' || $scope.data.post.seats[i][index].type =='selected'){
              $scope.data.post.seats[i][index].type = 'selected';
              $scope.selectedSeats.push($scope.data.post.seats[i][index]);
          }
      }
      $scope.currentSelection = 'col'
      $scope.currentSelectionIndex = index;
    };

    /*
     * Select individual seat
     */
    $scope.selectSeat = function (seat, row, col, scolor, sel_color) {
        if ($scope.currentSelection == 'row' || $scope.currentSelection == 'col') {
            $scope.resetSelections();
        }
        if (seat.type == 'selected' && seat.type != 'general')
        {
            var index = $scope.selectedSeats.indexOf(seat);
            if (index >= 0) {
                $scope.selectedSeats.splice(index, 1);
            }
            seat.type = 'general';
            seat.seatColor = '#'+scolor;
            seat.seatBorderColor = '3px solid #'+scolor;
        } else {
            seat.type = 'selected';
            seat.seatColor = '#'+sel_color;
            seat.seatBorderColor = '3px solid #'+sel_color;
            $scope.selectedSeats.push(seat);
        }
        $scope.currentSelection = 'seat';
        $scope.em_call_popup("#pm-change-password");
    }
    
    /* 
     * Select seats row for bulk operation(like: Reserve or reset)
     */
    $scope.selectRow = function (index) {

        $scope.resetSelections();
         for (var i = 0; i < $scope.data.post.seats[index].length; i++) {
            if($scope.data.post.seats[index][i].type == 'general' || $scope.data.post.seats[index][i].type =='selected')
            {
                 $scope.data.post.seats[index][i].type = 'selected';
                 $scope.selectedSeats.push($scope.data.post.seats[index][i]);
            } 
        }
        $scope.currentSelection = 'row';
        $scope.currentSelectionIndex = index;
    };
    
    // Resetting current selections for seats
    $scope.resetSelections = function (scolor) {
        for (var i = 0; i < $scope.selectedSeats.length; i++) {
            $scope.selectedSeats[i].type = 'general';
            if(scolor){
                $scope.selectedSeats[i].seatColor = '#'+scolor;
                $scope.selectedSeats[i].seatBorderColor = '3px solid #'+scolor;
            }
        }
        $scope.selectedSeats = [];
        $scope.currentSelection = '';
        $scope.currentSelectionIndex = '';
    }
    
    /*
     * Reserving seat. Changing selected seat status to "reserve"
     */
    $scope.reserveSeat = function (rcolor) {
        var type = 'reserve';
        for (var i = 0; i < $scope.selectedSeats.length; i++) {
            $scope.selectedSeats[i].type = type;
            $scope.selectedSeats[i].seatColor = '#'+rcolor;
            $scope.selectedSeats[i].seatBorderColor = '3px solid #'+rcolor;
        }
        $scope.selectedSeats = [];
        $scope.currentSelection = '';
    }
    
    /*
     * Calculating margins(aisles) for seat container width
     */
    $scope.adjustContainerWidth= function(columnMargin,index)
    {
        var width= parseInt($scope.seat_container_width.width);
        if(index==0 && columnMargin>0)
        {  
            width += columnMargin;
            $scope.seat_container_width.width = width + "px";
        }
    }
    $scope.$watch('data.post.venue',function(newVal, oldVal){
        if(newVal=='new_venue' && !$scope.mapConfigured){
            $scope.setupMap();
        }
    });
    
    /*
     * Watch for performers dropdown
     */
    $scope.$watch('data.post.performer', function (newVal, oldVal) {
       if(newVal && $scope.postForm && $scope.postForm.performer)
       {
            if(newVal!==undefined && newVal.length>2 && $scope.data.post.match==1)
               $scope.postForm.performer.$setValidity("invalidPerForMatch", false);
            else
            $scope.postForm.performer.$setValidity("invalidPerForMatch", true);
       }
    });
    
    /*
     * Watch for match field
     */
    $scope.$watch('data.post.match', function (newVal, oldVal) {
        if(newVal && $scope.postForm.performer)
        {
            if(newVal==1 && $scope.data.post.performer.length>2)
            $scope.postForm.performer.$setValidity("invalidPerForMatch", false);
            else
            $scope.postForm.performer.$setValidity("invalidPerForMatch", true); 
        }
    });

    /*
     * Add functions for adding new contact fields for Organizers
     */
    $scope.addPhone = function() {
        $scope.data.post.organizer_phones.push('');
    }
    
    $scope.addEmail = function() {
        $scope.data.post.organizer_emails.push('');
    }
    
    $scope.addWebsite = function() {
        $scope.data.post.organizer_websites.push('');
    }
    
    /*
     * Remove functions for removing contact fields for Organizers
     */
    $scope.removePhone = function(phone) {
       var index = $scope.data.post.organizer_phones.indexOf(phone);
       $scope.data.post.organizer_phones.splice(index,1);
    }
    
    $scope.removeEmail = function(email) {
       var index = $scope.data.post.organizer_emails.indexOf(email);
       $scope.data.post.organizer_emails.splice(index,1);
    }
    
    $scope.removeWebsites = function(website) {
       var index = $scope.data.post.organizer_websites.indexOf(website);
       $scope.data.post.organizer_websites.splice(index,1);
    }
    
    /*
     * Watch Dicount No. of tickets
     */
    $scope.$watch('data.post.discount_no_tickets',function(newVal,oldVal)
    {   // Return in case event/post data is not loaded.
        if(!$scope.data.hasOwnProperty('post'))
            return;
        if(!$scope.postForm)
            return;
        
        // No. of tickets should not be excedded seating capacity
        if($scope.postForm.discount_no_tickets){
            if(newVal>0 && $scope.data.post.seating_capacity>0 && newVal>$scope.data.post.seating_capacity)
                $scope.postForm.discount_no_tickets.$setValidity("exceededCapacity", false);
            else
            {
                $scope.postForm.discount_no_tickets.$setValidity("exceededCapacity", true);
            }
        }
    });
    
    /*
     * Watch Maximum ticket per person
     */
    $scope.$watch('data.post.max_tickets_per_person',function(newVal,oldVal)
    { 
        if(!$scope.postForm || !$scope.data.hasOwnProperty('post'))
            return;
        
        if(!$scope.postForm.max_tickets_per_person)
            return;
        
        if(newVal>0 && $scope.data.post.seating_capacity>0 && newVal>$scope.data.post.seating_capacity)
           $scope.postForm.max_tickets_per_person.$setValidity("exceededCapacity", false);
        else
        {
            // Event/post data must be loaded.
            if($scope.data.hasOwnProperty('post'))
                $scope.postForm.max_tickets_per_person.$setValidity("exceededCapacity", true);
        }
    });
    
    /****************** Add/Edit event functions ends here ***************/
    
    
    /*
    * 
    * Add/Edit event popup in calendar view.
    */
    $scope.resetCalendarNewEventPopup= function(){
       $scope.calendarNewEventPopup = {id:0, title: '', start_date:'', end_date:'', start_booking_date:'', last_booking_date:'', all_day:0, enable_booking:0, ticket_price:0, performer:[], organizer:[], cover_image_id:0, cover_image_url:'', venue:0, event_type:0, status:'publish'};
    } 
    
    /************************** Event List page functions *****************/
    $scope.calendarObject = null;
    $scope.default_view = 'dayGridMonth'; 
    $scope.addNewClicked = false;
    $scope.calRenderedEvents = [];
    $scope.data.hideExpired = 0;
    $scope.calendarNewEventPopup = {};
    $scope.eventView = em_get_url_param('view','month');
    var viewName = {
        "month"     : "dayGridMonth",
        "basicWeek" : "dayGridWeek",
        "basicDay"  : "dayGridDay",
        "listWeek"  : "listWeek"
    };
    $scope.default_view = viewName[$scope.eventView];
    /*
     * 
     * Loading data for list page
     */
    $scope.prepareEventListPage = function () {
        $scope.data.paged = $scope.paged;
        $scope.data.order = $scope.order;
        $scope.data.view = $scope.eventView;
        //$scope.calendarObject = $('#em_calendar');
        if($scope.eventView == "cards"){
            $scope.default_view = "cards";
        }
        $scope.data.em_event_nonce = em_event_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_admin_events', $scope.data).then(function(response){
            var responseBody = response.data;
            if(responseBody.success){
                $scope.data = responseBody.data;
                $scope.deleteSelections = responseBody.data.total_posts;
            }
            $scope.progressStop();
            if($scope.eventView != 'cards'){
                $scope.renderCalendar();
                if($scope.eventView != 'month'){
                    setTimeout(function(){
                        $scope.calendarObject.changeView($scope.default_view);
                    }, 1000);
                }
            }
        });
    }
    /*
     * 
     * Rendering calendar
     */
    $scope.renderCalendar = function(){
        let eventDashLinkClicked = false;
        var dayClickAllowed = em_calendar_data.dayClickAllowed;
        var date_format_setting = em_calendar_data.datepicker_format.toUpperCase();
        date_format_setting = date_format_setting.replace('YY', 'YYYY');
        let hour12 = true;
        if(em_calendar_data.time_format == 'HH:mm'){
            hour12 = false;
        }
        var calendarEl = document.getElementById('em_calendar');
        $scope.calendarObject = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'interaction', 'dayGrid', 'momentPlugin', 'list' ],
            header: {
                left: '',
                center: 'prev,title,next',
                right: 'today'
            },
            timeZone: 'UTC',
            editable : true,
            defaultView: $scope.default_view,
            nextDayThreshold: '00:00:00',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: hour12,
                meridiem: 'narrow'
            },
            firstDay: parseInt(em_calendar_data.week_start, 10),
            locale: em_calendar_data.locale,
            events: $scope.data.events,
            eventRender: function(info) {
                // Keeping visible event ids for bulk operations. (For example: Delete)
                if(!$scope.calRenderedEvents.includes(info.event.id)){
                    $scope.calRenderedEvents.push(info.event.id);
                }
                let elementHtml = info.el.innerHTML;
                elementHtml += info.event.extendedProps.popup;
                info.el.innerHTML = elementHtml;
                var pop_dash_link = info.el.querySelector('.ep-dashboard-link .ep-event-delete a'); 
                if(pop_dash_link) {
                    pop_dash_link.onclick = function(e){
                        eventDashLinkClicked = true;
                        if ($(this).data('id')) {
                            $scope.selections.push($(this).data('id'));
                            $scope.deletePost();
                        }
                    }
                }

                var pop_setting_link = info.el.querySelector('.ep-dashboard-link .ep-event-setting a');
                if(pop_setting_link) {
                    pop_setting_link.onclick = function(e){
                        eventDashLinkClicked = true;
                    }
                }

                var pop_dashboard_link = info.el.querySelector('.ep-dashboard-link .ep-event-dash a');
                if(pop_dashboard_link) {
                    pop_dashboard_link.onclick = function(e){
                        eventDashLinkClicked = true;
                    }
                }

                if (info.event.extendedProps.hasOwnProperty('bg_color')) {
                    info.el.style.backgroundColor = info.event.extendedProps.bg_color;
                }
                var textColor = '';
                if (info.event.extendedProps.hasOwnProperty('type_text_color')) {
                    textColor = info.event.extendedProps.type_text_color;
                }
                if (info.event.extendedProps.hasOwnProperty('event_text_color')) {
                    textColor = info.event.extendedProps.event_text_color;
                }                
                if(textColor){
                    var fc_time = info.el.querySelector('.fc-time');
                    if(fc_time){
                        fc_time.style.color = textColor;
                    }
                    var fc_title = info.el.querySelector('.fc-title');
                    if(fc_title){
                        fc_title.style.color = textColor;
                    }
                    var fc_list_time = info.el.querySelector('.fc-list-item-time');
                    if(fc_list_time){
                        fc_list_time.style.color = textColor;
                    }
                    var fc_list_title = info.el.querySelector('.fc-list-item-title');
                    if(fc_list_title){
                        fc_list_title.style.color = textColor;
                    }
                }
            },
            eventMouseEnter: function (info) {
                let pop_block = info.el.querySelector('.em_event_detail_popup');
                pop_block.style.display = 'block';
            },
            eventMouseLeave: function(info){
                let pop_block = info.el.querySelector('.em_event_detail_popup');
                pop_block.style.display = 'none';
            },
            eventClick: function(info) {
                if( eventDashLinkClicked || !dayClickAllowed )
                    return;

                // Event selection/deselection
                if($scope.selections.includes(info.event.id)){
                    var index = $scope.selections.indexOf(info.event.id);
                    $scope.selections.splice(index, 1);
                }
                else
                {
                    $scope.selections.push(info.event.id);
                }
                for(let i = 0; i < $scope.selections.length; i++){
                    let allEvents = $scope.calendarObject.getEventById($scope.selections[i]);
                    if(allEvents.id == info.event.id){
                        allEvents.setProp('classNames', ['selected']);
                    }
                    else{
                        allEvents.setProp('classNames', ['']);
                    }
                }
                if(info.event.id > 0){ // Fill existing event data in popup
                    $scope.resetCalendarNewEventPopup();
                    $scope.calendarDayClicked = true;
                    var start_date_moment = FullCalendarMoment.toMoment(info.event.start, $scope.calendarObject);
                    var end_date_moment = FullCalendarMoment.toMoment(info.event.end, $scope.calendarObject);
                    var start_date = start_date_moment.format('MM/DD/YYYY HH:mm');
                    var end_date = end_date_moment.format('MM/DD/YYYY HH:mm');
                    if(date_format_setting){
                        start_date = start_date_moment.format(date_format_setting + ' HH:mm');
                        end_date = end_date_moment.format(date_format_setting + ' HH:mm');
                    }
                    $("#calendar_start_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: em_calendar_data.datepicker_format});
                    $("#calendar_end_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: em_calendar_data.datepicker_format});
                    $scope.calendarNewEventPopup.start_date         = start_date;
                    $scope.calendarNewEventPopup.end_date           = end_date;
                    $scope.calendarNewEventPopup.id                 = info.event.id;
                    $scope.calendarNewEventPopup.all_day            = info.event.allDay;
                    $scope.calendarNewEventPopup.enable_booking     = info.event.extendedProps.enable_booking;
                    $scope.calendarNewEventPopup.ticket_price       = info.event.extendedProps.ticket_price;
                    $scope.calendarNewEventPopup.start_booking_date = info.event.extendedProps.start_booking_date;
                    $scope.calendarNewEventPopup.last_booking_date  = info.event.extendedProps.last_booking_date;
                    $scope.calendarNewEventPopup.title              = info.event.title;
                    $scope.calendarNewEventPopup.performer          = info.event.extendedProps.performer;
                    $scope.calendarNewEventPopup.venue              = info.event.extendedProps.venue;
                    $scope.calendarNewEventPopup.performers         = $scope.data.performers;
                    $scope.calendarNewEventPopup.venues             = $scope.data.venues;
                    $scope.calendarNewEventPopup.event_types        = $scope.data.event_types;
                    $scope.calendarNewEventPopup.event_type         =  info.event.extendedProps.event_type;
                    $scope.calendarNewEventPopup.status             =  info.event.extendedProps.status;
                    $scope.calendarNewEventPopup.status_list        = $scope.data.status_list;
                    $scope.calendarNewEventPopup.is_featured        = info.event.extendedProps.is_featured;
                    $scope.calendarNewEventPopup.is_zoom_meetings   = info.event.extendedProps.is_zoom_meetings;
                    $scope.calendarNewEventPopup.organizer          = info.event.extendedProps.organizer;
                    $scope.calendarNewEventPopup.organizers         = $scope.data.organizers;
                    $scope.calendarNewEventPopup.cover_image_id     = info.event.extendedProps.cover_image_id;
                    $scope.calendarNewEventPopup.cover_image_url    = info.event.extendedProps.cover_image_url;
                    $(document).trigger('new_event_popup', [$scope.calendarNewEventPopup, info.event]);
                    $("#em_edit_event_title").html(info.event.title);
                    $scope.formErrors = [];
                    $scope.$apply();
                    topY = info.jsEvent.pageY-20;
                    leftX = info.jsEvent.pageX-500;
                    if(info.jsEvent.pageX < 400){
                        leftX += 200;
                    }
                    $("#calendarPopup").prop('style',"left:" + leftX + "px;top:" + topY + "px;");
                    $("#calendarPopup").addClass('em_edit_pop');
                }
                
            },
            dateClick: function(info) {
                if(dayClickAllowed){
                    $scope.calPopup(info);
                }
            },
            eventDragStart: function(info){
                let pop_block = info.el.querySelector('.em_event_detail_popup');
                pop_block.style.display = 'none';
            },
            eventDrop: function(info) {
                let event = info.event;
                if (event.extendedProps.enable_booking == 1) {
                    var confirmed = confirm("Moving this event to a new date will disable its bookings. You can enable bookings again once the date change is complete. Are you sure you want to continue?");
                    if (confirmed) {
                        event.extendedProps.enable_booking = 0;
                        event.extendedProps.start_booking_date = '';
                        event.extendedProps.last_booking_date = '';
                    } else {
                        info.revert();
                        return;
                    }
                }
                // Saving updated event details.
                $scope.resetCalendarNewEventPopup();
                var start_date_moment = event.start;
                var end_date_moment = event.end;
                start_date_moment = FullCalendarMoment.toMoment(start_date_moment, $scope.calendarObject);
                end_date_moment = FullCalendarMoment.toMoment(end_date_moment, $scope.calendarObject);
                var start_date = start_date_moment.format('MM/DD/YYYY HH:mm');
                var end_date = end_date_moment.format('MM/DD/YYYY HH:mm');
                if(date_format_setting){
                    start_date = start_date_moment.format(date_format_setting + ' HH:mm');
                    end_date = end_date_moment.format(date_format_setting + ' HH:mm');
                }
                $scope.calendarNewEventPopup.start_date = start_date;
                $scope.calendarNewEventPopup.end_date           = end_date;
                $scope.calendarNewEventPopup.id                 = event.id;
                $scope.calendarNewEventPopup.all_day            = event.allDay;
                $scope.calendarNewEventPopup.enable_booking     = event.extendedProps.enable_booking;
                $scope.calendarNewEventPopup.ticket_price       = event.extendedProps.ticket_price;
                $scope.calendarNewEventPopup.start_booking_date = event.extendedProps.start_booking_date;
                $scope.calendarNewEventPopup.last_booking_date  = event.extendedProps.last_booking_date;
                $scope.calendarNewEventPopup.title              = event.title;
                $scope.calendarNewEventPopup.performer          = event.extendedProps.performer;
                $scope.calendarNewEventPopup.venue              = event.extendedProps.venue;
                $scope.calendarNewEventPopup.event_type         = event.extendedProps.event_type;
                $scope.calendarNewEventPopup.status             =  event.extendedProps.status;
                $scope.calendarNewEventPopup.is_featured        = event.extendedProps.is_featured;
                $scope.calendarNewEventPopup.is_zoom_meetings   = event.extendedProps.is_zoom_meetings;
                $scope.calendarNewEventPopup.organizer          = event.extendedProps.organizer;
                $scope.calendarNewEventPopup.cover_image_id     = event.extendedProps.cover_image_id;
                $scope.calendarNewEventPopup.cover_image_url    = event.extendedProps.cover_image_url;
                $(document).trigger('new_event_popup', [$scope.calendarNewEventPopup, event]);
                $scope.calendarEventDropped(event);
            },
        });
        $scope.calendarObject.render();
    }
    
    /*
     * 
     * Dynamically changes calendar view between 'Month,Week,Day,List' views.
     */
    $scope.changeEventView = function(){
        if($scope.eventView == 'cards'){
            em_redirect('?page=event_magic&view=cards')
            return;
        }
        else if($scope.default_view == 'cards'){
            em_redirect('?page=event_magic&view=' + $scope.eventView);
            return;
        }
        var viewName = {
            "month": "dayGridMonth",
            "basicWeek" : "dayGridWeek",
            "basicDay"  : "dayGridDay",
            "listWeek"  : "listWeek"
        };
        let updateViewName = viewName[$scope.eventView];
        $scope.calendarObject.changeView(updateViewName);
    }
    
    $scope.calendarEventDropped = function(similarEvents){
        $scope.progressStart();
        $scope.calendarNewEventPopup.em_event_nonce = em_event_object.nonce;
        EMRequest.send('em_save_dropped_event', $scope.calendarNewEventPopup).then(function(response){
            $scope.progressStop();
            var responseBody = response.data;
            if (responseBody.success && responseBody.data.reload) {
                location.reload();
            }
            if (responseBody.success) {
                var ev = responseBody.data.event;
                if($scope.eventView != 'cards'){
                    if($scope.calendarNewEventPopup.id > 0) { // Update event
                        var similarEvents = $scope.calendarObject.getEventById(ev.id);
                        similarEvents.setExtendedProp('enable_booking', ev.enable_booking);
                        similarEvents.setExtendedProp('ticket_price', ev.ticket_price);
                        similarEvents.setExtendedProp('start_booking_date', ev.start_booking_date);
                        similarEvents.setExtendedProp('last_booking_date', ev.last_booking_date);
                        similarEvents.setExtendedProp('performer', ev.performer);
                        similarEvents.setExtendedProp('venue', ev.venue);
                        similarEvents.setExtendedProp('event_type', ev.event_type);
                        similarEvents.setExtendedProp('status', ev.status);
                        similarEvents.setExtendedProp('popup', ev.popup);
                        similarEvents.setExtendedProp('is_featured', ev.is_featured);
                        similarEvents.setExtendedProp('is_zoom_meetings', ev.is_zoom_meetings);
                        similarEvents.setExtendedProp('organizer', ev.organizer);
                        similarEvents.setExtendedProp('cover_image_id', ev.cover_image_id);
                        similarEvents.setExtendedProp('cover_image_url', ev.cover_image_url);
                        if($scope.data.colors.hasOwnProperty(ev.event_type)){
                            similarEvents.setExtendedProp('bg_color', $scope.data.colors[ev.event_type]);
                            similarEvents.setProp('backgroundColor', '#' + $scope.data.colors[ev.event_type]);
                        }
                        $(document).trigger('after_dropping_event', [similarEvents, ev]);
                    }
                    else {   // Add new event
                        $scope.calendarObject.addEvent(ev);
                    }
                }
                else {
                    location.href = responseBody.data.redirect;
                }
                $scope.calendarDayClicked = false;
            }
            if(responseBody.data.errors){
                alert(responseBody.data.errors[0]);
                location.reload();
            }
        });
    }
    /*
     * 
     * Save/Edit event directly from calendar.
     */
    $scope.savePopupEvent= function(isValid){
        if(!isValid)
            return;
        $scope.formErrors = [];
        $scope.progressStart();
        if($scope.calendarDayClicked){
            $scope.calendarNewEventPopup.title = $("#em_edit_event_title").html();
        }
        $scope.calendarNewEventPopup.em_event_nonce = em_event_object.nonce;
        EMRequest.send('em_save_popup_event',$scope.calendarNewEventPopup).then(function(response){
            $scope.progressStop();
            $("#em_edit_event_title").html('');
            var responseBody = response.data;
            if (responseBody.success && responseBody.data.reload) {
                location.reload();
            }
            if(responseBody.success) {
                if(responseBody.data.status_changed) {
                    EMRequest.send('em_event_approved_mail',responseBody.data.event).then(function(response){});
                }
                var ev = responseBody.data.event;
                if($scope.eventView != 'cards'){
                    if($scope.calendarNewEventPopup.id > 0){ // Update event
                        location.reload();
                        var similarEvents = $scope.calendarObject.getEventById(ev.id);
                        similarEvents.setProp('title', ev.title);
                        var start_date = new Date(ev.start);
                        var end_date = new Date(ev.end);
                        similarEvents.setStart(start_date);
                        similarEvents.setEnd(end_date);
                        similarEvents.setAllDay(ev.all_day);
                        similarEvents.setProp('id', ev.id);
                        similarEvents.setExtendedProp('all_day', ev.all_day);
                        similarEvents.setExtendedProp('enable_booking', ev.enable_booking);
                        similarEvents.setExtendedProp('ticket_price', ev.ticket_price);
                        similarEvents.setExtendedProp('start_booking_date', ev.start_booking_date);
                        similarEvents.setExtendedProp('last_booking_date', ev.last_booking_date);
                        similarEvents.setExtendedProp('performer', ev.performer);
                        similarEvents.setExtendedProp('venue', ev.venue);
                        similarEvents.setExtendedProp('event_type', ev.event_type);
                        similarEvents.setExtendedProp('status', ev.status);
                        similarEvents.setExtendedProp('popup', ev.popup);
                        similarEvents.setExtendedProp('is_featured', ev.is_featured);
                        similarEvents.setExtendedProp('is_zoom_meetings', ev.is_zoom_meetings);
                        similarEvents.setExtendedProp('organizer', ev.organizer);
                        similarEvents.setExtendedProp('cover_image_id', ev.cover_image_id);
                        similarEvents.setExtendedProp('cover_image_url', ev.cover_image_url);
                        
                        if($scope.data.colors.hasOwnProperty(ev.event_type)){
                            similarEvents.setExtendedProp('bg_color', $scope.data.colors[ev.event_type]);
                            similarEvents.setProp('backgroundColor', '#' + $scope.data.colors[ev.event_type]);
                        }
                        $(document).trigger('after_saving_popup_event',[similarEvents, ev]);
                    }
                    else 
                    {   // Add new event
                        if($scope.data.colors.hasOwnProperty(ev.event_type)){
                            ev.backgroundColor = "#" + $scope.data.colors[ev.event_type];
                        }
                        $scope.calendarObject.addEvent(ev);
                    }
                }
                else
                {
                    location.href = responseBody.data.redirect;
                }
                
                $scope.addNewClicked = false;
                $scope.calendarDayClicked = false;
            }
            else{
                if(responseBody.data.hasOwnProperty('errors')){
                    $scope.formErrors = responseBody.data.errors;
                    $('.ep-add-newevent').animate({ scrollTop: $(".form_errors").offset().top }, 'slow');
                }
            }
        });
    }
    
    /*
     * Booking fill bar for event list page
     */
    $scope.getProgressStyle= function(post)
    {   
        var style= {"height":"10px","background-color": "#80c9ff", "width": (post.sum/post.capacity) * 100 + "%"}
        return style;
    }
    
    /*
     * Select event
     */
    $scope.selectPost= function(post_id){
        if($scope.selections.indexOf(post_id)>=0){
            em_remove_from_array($scope.selections,post_id);
        }
        else{
            $scope.selections.push(post_id);
        }
        
    }
    
    /*
     * Delete event
     */
    $scope.deletePost = function () {
        $scope.isSelectAll = jQuery('#em_select_all').is(':checked');
        if ($scope.isSelectAll) {
            $scope.deleteAllPost();
        } else {
            if ($scope.eventView != 'cards') {
                var confirmed = confirm("Are you sure you want to permanently delete this event?");
            } else {
                var confirmed = confirm("Are you sure you want to permanently delete the selected " + $scope.selections.length + " event(s)?");
            }
            if (confirmed) {
                $scope.progressStart();
                PostUtility.delete($scope.selections, em_event_object.nonce, 'events').then(function (response) {
                    $scope.progressStop();
                    if( response.data.success == false ) {
                        alert(response.data.data.errors[0]);
                    } else{
                        location.reload();
                    }
                });
            }
        }
    }
    
     $scope.deleteAllPost = function(){
       var confirmed = confirm("Are you sure you want to delete "+ $scope.selections.length +" event(s). Please confirm");
        if(confirmed){
            $scope.progressStart(); 
            PostUtility.delete($scope.selections, em_event_object.nonce, 'events').then(function(response){
                $scope.deleteSelections=[];
                $scope.progressStop();
                if( response.data.success == false ) {
                    alert(response.data.data.errors[0]);
                } else{
                    location.reload();
                }
           });
        }
    }
    
    /*
     * Duplicate event(s)
     */
    $scope.duplicatePosts= function(){
        $scope.progressStart();
        PostUtility.duplicate($scope.selections, em_event_object.nonce, 'events').then(function(response){
            $scope.progressStop(); 
            if( response.data.success == false ) {
                alert(response.data.data.errors[0]);
            } else{
                location.reload();
            }
        });
    }
    
    /*
     * Pagination navigation
     */
    $scope.pageChanged = function(newPage) {
        $scope.selectedAll=false;
        $scope.paged= newPage;
        $scope.prepareEventListPage();
    };
    
    /*
     * Select all events
     */
    $scope.checkAll = function () {
        if($scope.eventView == 'cal'){ // Select all the events
            //var allEvents = $scope.calendarObject.fullCalendar('clientEvents', function(e) { return $scope.calRenderedEvents.includes(e.id)});
            var allEvents = $scope.calendarObject.getEvents();
            for (var i = 0; allEvents.length > i ; i++){
                if($scope.calRenderedEvents.includes(allEvents[i].id)){
                    let singleEvent = $scope.calendarObject.getEventById(allEvents[i].id);
                    if($scope.selectedAll){
                        singleEvent.setProp('classNames', ['selected']);
                        $scope.selections.push(singleEvent.id);
                    }
                    else{
                        singleEvent.setProp('classNames', ['']);
                        var index = $scope.selections.indexOf(singleEvent.id);
                        if(index > -1){
                            $scope.selections.splice(index, 1);
                        }
                        $scope.selections.push(singleEvent.id);
                    }
                }
            }
        }    
        else {
            angular.forEach($scope.data.posts, function (post) {
                if ($scope.selectedAll) {
                    if($("#em-evt-"+post.id).length > 0) {
                        $scope.selections.push(post.id);
                        post.Selected = $scope.selectedAll ? post.id : 0; 
                    }
                }
                else{
                    $scope.selections= [];
                    post.Selected = 0;
                }
            });
        }
    };
   
    /********************** Generate Alphabet seat sequence ****************/
    $scope.getRowAlphabet = function (rowIndex) {
        var indexNumber = "";
        if (rowIndex > 25) {
            indexNumber = parseInt(rowIndex / 26);
            rowIndex = rowIndex % 26;
        }

        return String.fromCharCode(65 + parseInt(rowIndex)) + indexNumber;
    }
    
    $scope.add_event_tourCompleted= function() {
        EMRequest.send('em_add_event_tour_completed',$scope.data.post).then(function(response) {
           
        });
    }
    
    $scope.events_tourCompleted= function() {
        EMRequest.send('em_event_tour_completed',$scope.data.post).then(function(response) {
        
        });
    }
    
    var event_tour_status = jQuery("#em_tour-status").val();
    
    $scope.event_tour = function() {
        if(event_tour_status == 0) {
            jQuery("#em-events-joytips").joyride({
                tipLocation: 'bottom',
                autoStart: true,
                nubPosition: 'auto',
                tipAnimation: 'fade',
                next_button: true,
                prev_button: true,
                scrollSpeed: 500,
                tipAdjustmentY: -30,
                postRideCallback: $scope.events_tourCompleted
            });
        } else {
            $scope.another_tour();
        }
    };
    
    $scope.another_tour = function() {
      event_tour_status=0;
        $scope.event_tour();
        
    }
    
    
    $scope.updateSeatSequence= function(seat) {
        if(!seat.seatSequence){
            var seatColInd = parseInt(seat.col) + parseInt(1);
            seat.seatSequence= $scope.getRowAlphabet(seat.row) + "-" + seatColInd;    
        }
    }
    
     $scope.em_call_popup= function(dialog) {
        var pmId = dialog + "-dialog";
        $(pmId).siblings('.pm-popup-mask').show();
        $(pmId).show();
        $('.pm-popup-container').css("animation", "pm-popup-in 0.3s ease-out 1");
    }
    
    $scope.em_call_scheme_popup= function(dialog) {
        var selectedSeatSeq= [];    
        for (var i = 0; i < $scope.selectedSeats.length; i++) {
            selectedSeatSeq[i]= $scope.selectedSeats[i].seatSequence;
        }
        $("#custom_seat_sequences").val(selectedSeatSeq.join());
        var pmId = dialog + "-dialog";
        $(pmId).siblings('.pm-popup-mask').show();
        $(pmId).show();
        $('.pm-popup-container').css("animation", "pm-popup-in 0.3s ease-out 1");
        $scope.scheme_popup = $scope.scheme_popup ? false : true;
    }
    
    $('.pm-popup-close, .pm-popup-mask').click(function (){
          $('.pm-popup-mask').hide();
          $('.pm-popup-mask').next().hide();
    });
    
    
    $scope.showSeatOptions= function(seat)
    {
       $scope.currentSeat= seat;
    }
    
    $scope.updateCurrentSeat= function()
    {
       $scope.currentSeat.seatSequence=  $("#custom_seat_seq").val();
    }
    
    $scope.updateCurrentSeatScheme= function()
    {

        var str = $("#custom_seat_sequences").val();
        var strval = str.split(',');

        if (strval.length == $scope.selectedSeats.length && strval.length > 0) {
            for (var i = 0; i < $scope.selectedSeats.length; i++) {
                if (strval[i].trim() != "")
                    $scope.selectedSeats[i].seatSequence = strval[i];
            }
        } else {
            alert('Please verify seating arrangement.');
        }
        $scope.scheme_popup= false;
    }
    
    $scope.addNewPopup= function(){ 
        $scope.formErrors=[];
        date= moment();
        $scope.resetCalendarNewEventPopup();
        $scope.addNewClicked = true;
        date.set({h:12,m:00});
        var date_format_setting = em_calendar_data.datepicker_format.toUpperCase();
        date_format_setting = date_format_setting.replace('YY', 'YYYY');
        var startDate= date.format(date_format_setting + ' HH:mm');
        var endDate= date.add('1','day').format(date_format_setting + ' HH:mm');
        $("#new_calendar_start_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: em_calendar_data.datepicker_format});
        $("#new_calendar_end_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: em_calendar_data.datepicker_format});
        $scope.calendarNewEventPopup.start_date      = startDate;
        $scope.calendarNewEventPopup.end_date        = endDate;
        $scope.calendarNewEventPopup.enable_booking  = 0;
        $scope.calendarNewEventPopup.ticket_price    = 0;
        $scope.calendarNewEventPopup.performers      = $scope.data.performers;
        $scope.calendarNewEventPopup.venues          = $scope.data.venues;
        $scope.calendarNewEventPopup.event_types     = $scope.data.event_types;
        $scope.calendarNewEventPopup.status_list     = $scope.data.status_list;
        $scope.calendarNewEventPopup.organizers      = $scope.data.organizers;
        $scope.calendarNewEventPopup.cover_image_id  = 0;
        $scope.calendarNewEventPopup.cover_image_url = '';
        setTimeout(function(){
            $scope.$apply();
        },500);
    }
    
    $scope.calPopup = function(info){
        let date = FullCalendarMoment.toMoment(info.date, $scope.calendarObject);
        let position = info.jsEvent;
        $scope.formErrors = [];
        $scope.resetCalendarNewEventPopup();
        $("#em_edit_event_title").html('');
        $scope.calendarDayClicked = true;
        var date_format_setting = em_calendar_data.datepicker_format.toUpperCase();
        date_format_setting = date_format_setting.replace('YY', 'YYYY');
        var startDate = date.format(date_format_setting + ' HH:mm');
        var endDate = date.add('1','day').format(date_format_setting + ' HH:mm');
        $("#calendar_start_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: em_calendar_data.datepicker_format});
        $("#calendar_end_date").datetimepicker({controlType: 'select',oneLine: true,timeFormat: 'HH:mm',changeYear: true, timeInput: true, dateFormat: em_calendar_data.datepicker_format});
        $scope.calendarNewEventPopup.start_date      = startDate;
        $scope.calendarNewEventPopup.end_date        = endDate;
        $scope.calendarNewEventPopup.enable_booking  = 0;
        $scope.calendarNewEventPopup.ticket_price    = 0;
        $scope.calendarNewEventPopup.performers      = $scope.data.performers;
        $scope.calendarNewEventPopup.venues          = $scope.data.venues;
        $scope.calendarNewEventPopup.event_types     = $scope.data.event_types;
        $scope.calendarNewEventPopup.status_list     = $scope.data.status_list;
        $scope.calendarNewEventPopup.organizers      = $scope.data.organizers;
        $scope.calendarNewEventPopup.cover_image_id  = 0;
        $scope.calendarNewEventPopup.cover_image_url = '';
        setTimeout(function(){
            $scope.$apply();
        },500);
        
        topY = position.pageY-20;
        leftX = position.pageX-500;
        if(position.pageX<400){
            leftX += 200;
        }
        $("#calendarPopup").prop('style',"left:" + leftX + "px;top:" + topY + "px;");
        $("#calendarPopup").removeClass('em_edit_pop');
        
    }
    
    $scope.closeEventPopup = function() {
        $("#new_event_popup").hide();
    }
    
    $scope.syncWithVenue=function(){
        var cnf=confirm("Syncing with the Event Site will cancel all Bookings with the older seating arrangement. Are you sure you want to continue?");
        if(cnf){
            $scope.getCapacity();
        }
    }
    
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
        
        resetMarkers=function(){
            for (i = 0; i < gmarkers.length; i++) {
                gmarkers[i].setMap(null);
            }
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

    $scope.checkForAllDay = function() {
        if($scope.calendarNewEventPopup && $scope.calendarNewEventPopup.all_day == 1){
            var start_date = $scope.calendarNewEventPopup.start_date;
            var no_start_date_time = start_date.split(' ');
            var end_date = $scope.calendarNewEventPopup.end_date;
            var no_end_date_time = end_date.split(' ');
            no_start_date_time = no_start_date_time[0] + " 00:00";
            no_end_date_time = no_end_date_time[0] + " 00:00";
            $scope.calendarNewEventPopup.start_date = no_start_date_time;
            $scope.calendarNewEventPopup.end_date = no_end_date_time;
        }
    }

    $scope.bannerNewEventPopup = function(){
        console.log('bannerNewEventPopup');
    }

    $scope.getOrganizers=function() {
        var data = {};
        data.term_id = $scope.data.post.organizer; 
        if(data.term_id == 0)
            return;
        data.event_id = $scope.data.post.id;
        $scope.progressStart();
        EMRequest.send('em_get_organizer_data', data).then(function(response){
            $scope.progressStop();
            $scope.data.post.organizer_name = response.data.post.name;
            $scope.data.post.organizer_phones = response.data.post.organizer_phones;
            $scope.data.post.organizer_emails = response.data.post.organizer_emails;
            $scope.data.post.organizer_websites = response.data.post.organizer_websites;
        }); 
    }

    /*
     * Remove feature image
     */  
    $scope.deleteFeatureImage = function ( event_id ) { 
        var data= {};
        data.event_id = event_id;
        $scope.progressStart();
        EMRequest.send('em_remove_post_featured_image',data).then(function(response){
            window.location.reload();
        }); 
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

eventMagicApp.filter('unsafe', function($sce) {
    return function(val) {
        return $sce.trustAsHtml(val);
    };
});