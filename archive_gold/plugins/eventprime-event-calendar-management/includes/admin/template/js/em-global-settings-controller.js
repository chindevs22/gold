/**
 * 
 * Global Settings controller
 */
eventMagicApp.controller('globalSettingsCtrl', function ($scope, $http,EMRequest, $compile, MediaUploader) {
    $scope.data = {};
    $scope.requestInProgress= false;
    $scope.gs_services = ['showExternalIntegration', 'showPageIntegration', 'showPayments', 'showNotification', 'showPageGSettings', 'showCustomCSS', 'showUserEventSubmit', 'showOptions', 'configure_paypal', 'showCustomBookingFields', 'showUserEventMailPoet', 'showButtonSettings', 'showSeoSettings', 'showPerformerSettings', 'showEventTypeSettings', 'showOrganizerSettings', 'showEventSiteSettings'];
    
    $= jQuery;
    $scope.buttonKey = 1;
    $scope.customFieldData = '';
    $scope.someValue = false;
    $scope.fieldKey = '';
    $scope.fieldAction = '';
    $scope.enabledAnyGlobalService = 0;
   
    $scope.progressStart= function()
    {
        $scope.requestInProgress = true;
    }
    
    $scope.progressStop= function()
    {
        $scope.requestInProgress = false;
    }
    
    //Loads Pre saved setting data
    $scope.preparePageData = function () {
        $scope.data.em_request_context='admin_global_settings';
        $scope.progressStart();
        EMRequest.send('em_load_strings',$scope.data).then(function(response){
            $scope.progressStop();
            var responseBody= response.data;
            if(!responseBody.success)
                return;
            $scope.data= responseBody.data;
            if ($scope.data.options.event_submit_form == 0) {
                $scope.data.options.event_submit_form = $scope.data.options.pages[0].id;
            }
            if($scope.data.options.custom_booking_field_data && $scope.data.options.custom_booking_field_data.length > 0){
                var existingFields = $scope.data.options.custom_booking_field_data;
                if(existingFields.length > 0){
                    $scope.prepareFieldData(existingFields);
                }
            }
            // initialize the global setting services
            $scope.setDefaultServiceToFalse();
            // brodcast event for child controller
            $scope.$broadcast ('initilizeSettingChild');
        });
        $('input[name=default_calendar_date]').datepicker();
    }


    /*
     * Save Settings
     */
    $scope.saveSettings = function (isValid) {
        $scope.formErrors='';
        // If form is valid against all the angular validations
        if (isValid) {
            $scope.loadContentFromEditors();
            $scope.progressStart();
            EMRequest.send('em_save_global_settings',$scope.data.options).then(function(response){
                $scope.progressStop();
                var responseBody= response.data;
                if(responseBody.success){
                    $scope.showSettingOptions();
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

    $scope.initialize = function () {
         // Loading all the required data before form load
         $scope.preparePageData();
    };
    
    $scope.loadContentFromEditors= function() {
      
        if(typeof tinymce !== 'undefined' && tinymce.get('registration_email_content')!=null)
            $scope.data.options.registration_email_content= tinymce.get('registration_email_content').getContent();  
        else
           $scope.data.options.registration_email_content= jQuery('#registration_email_content').val();   

        if(typeof tinymce !== 'undefined' && tinymce.get('booking_pending_email')!=null)
            $scope.data.options.booking_pending_email= tinymce.get('booking_pending_email').getContent(); 
        else
            $scope.data.options.booking_pending_email= jQuery('#booking_pending_email').val(); 
        
        if(typeof tinymce !== 'undefined' && tinymce.get('booking_confirmed_email')!=null)
            $scope.data.options.booking_confirmed_email= tinymce.get('booking_confirmed_email').getContent();  
        else
            $scope.data.options.booking_confirmed_email= jQuery('#booking_confirmed_email').val(); 
        
        if(typeof tinymce !== 'undefined' && tinymce.get('booking_cancelation_email')!=null)
             $scope.data.options.booking_cancelation_email= tinymce.get('booking_cancelation_email').getContent();  
        else
             $scope.data.options.booking_cancelation_email= jQuery('#booking_cancelation_email').val();
        
        if(typeof tinymce !== 'undefined' && tinymce.get('booking_refund_email')!=null)
            $scope.data.options.booking_refund_email= tinymce.get('booking_refund_email').getContent();   
        else
            $scope.data.options.booking_refund_email= jQuery('#booking_refund_email').val(); 
        
        if(typeof tinymce !== 'undefined' && tinymce.get('reset_password_mail')!=null)
            $scope.data.options.reset_password_mail= tinymce.get('reset_password_mail').getContent();   
        else
            $scope.data.options.reset_password_mail= jQuery('#reset_password_mail').val();
        
        if(typeof tinymce !== 'undefined' && tinymce.get('event_submitted_email')!=null)
            $scope.data.options.event_submitted_email= tinymce.get('event_submitted_email').getContent();   
        else
            $scope.data.options.event_submitted_email= jQuery('#event_submitted_email').val();
        
        if(typeof tinymce !== 'undefined' && tinymce.get('event_approved_email')!=null)
            $scope.data.options.event_approved_email= tinymce.get('event_approved_email').getContent();   
        else
            $scope.data.options.event_approved_email= jQuery('#event_approved_email').val();

        if(typeof tinymce !== 'undefined' && tinymce.get('ei_footer_invoice_secion')!=null)
            $scope.data.options.ei_footer_invoice_secion= tinymce.get('ei_footer_invoice_secion').getContent();   
        else
            $scope.data.options.ei_footer_invoice_secion= jQuery('#ei_footer_invoice_secion').val();
        
        if(typeof tinymce !== 'undefined' && tinymce.get('admin_booking_confirmed_email')!=null)
            $scope.data.options.admin_booking_confirmed_email= tinymce.get('admin_booking_confirmed_email').getContent();  
        else
            $scope.data.options.admin_booking_confirmed_email= jQuery('#admin_booking_confirmed_email').val();
        if(typeof tinymce !== 'undefined' && tinymce.get('payment_confirmed_email')!=null)
            $scope.data.options.payment_confirmed_email= tinymce.get('payment_confirmed_email').getContent();  
        else
            $scope.data.options.payment_confirmed_email= jQuery('#payment_confirmed_email').val();    

    }
    
    $scope.showSettingOptions= function()
    {
        $scope.setDefaultServiceToFalse()
        
        // ng-hide thickboxes
        $scope.configure_stripe = false;
        $scope.enabledAnyGlobalService = 0;
    }
    $scope.show_configure_paypal=function(value){
        if(value==true){
            $scope.showPayments= true;
            $scope.configure_paypal=true;
        }
    }
    
    $scope.show_payments=function(value){       
        if(value==true){         
            $scope.showPayments= true;
        }            
    }  
    
    $scope.addFieldInCustomizer = function(key) {
        let newField = $scope.data.options.custom_field_data[key];
        let keyReplace = newField.replace(/:em:/g, $scope.buttonKey);
        $scope.customFieldData += keyReplace;
        $scope.fieldKey = key;
        //$scope.buttonKey++;
        $scope.someValue = true;
        $scope.fieldAction = 'add';
    }

    $scope.removeFieldFromCustomizer = function(key) {
        $scope.fieldAction = 'remove';
        if($scope.data.options.custom_booking_field_data[key]){
            $scope.data.options.custom_booking_field_data[key].required = 0;
            $scope.data.options.custom_booking_field_data[key].label = '';            
        }
        var sp = $scope.customFieldData.split('<li');
        sp.splice(key, 1);
        $scope.data.options.custom_booking_field_data.splice(key, 1);
        var keyReplace = '';
        var j = 1;
        for(var i = 0; i < sp.length; i++){
            var splc = sp[i].split(' data-keyval="');
            if(splc[1]){
                var rsplc = splc[1].split('">');
                var spsdata = sp[i].replaceAll(rsplc[0], j);
                sp[i] = spsdata;
                j++;
            }
        }
        $scope.buttonKey = j;
        $scope.customFieldData = '';
        $scope.someValue = true;
        var jo = sp.join('<li');
        $scope.customFieldData = jo;
        $scope.someValue = true;

        if($scope.data.options.custom_booking_field_data.length == 1){
            $scope.data.options.custom_booking_field_data = [];
        }
    }

    $scope.prepareFieldData = function(existingFields) {
        let totalFields = 0;
        existingFields.forEach(function(item, key){
            if(item){
                let newField = $scope.data.options.custom_field_data[item.type];
                let keyReplace = newField.replace(/:em:/g, key);
                $scope.customFieldData += keyReplace;
                $scope.fieldKey = item.type;
                $scope.data.options.custom_booking_field_data[key] = item;
                totalFields++;
            }
        });
        $scope.buttonKey = totalFields;
        $scope.someValue = true;
        $scope.fieldAction = 'edit';
    }

    $scope.setDefaultServiceToFalse = function(){
        if($scope.data.options.load_extension_services && $scope.data.options.load_extension_services.length > 0){
            angular.forEach($scope.data.options.load_extension_services, function(value, key){
                $scope.gs_services.push(value);
            });          
        }
        angular.forEach($scope.gs_services, function(value, key){
            $scope[value] = false;
        });
    }

    $scope.enableGlobalService = function(ser) {
        $scope[ser] = true;
        $scope.enabledAnyGlobalService = 1;
    }

    /*
     * 
     * WordPress default media uploader to choose image
     */
    $scope.mediaUploader = function (multiImage, gsModel) {
        var mediaUploader = MediaUploader.openUploader(multiImage);
        mediaUploader.on('select', function () {
            attachments = mediaUploader.state().get('selection');
            attachments.map(function (attachment) {
                attachment = attachment.toJSON();
                if (multiImage) {
                    // For gallery images
                    var imageObj = attachment.sizes.thumbnail===undefined ? {src: [attachment.sizes.full.url], id: attachment.id} : {src: [attachment.sizes.thumbnail.url], id: attachment.id};
                    $scope.data.options.images.push(imageObj);
                    $scope.data.options.gallery_image_ids.push(attachment.id);
                } else {
                    // For cover image
                    $scope.data.options[gsModel] = attachment.id;
                    $scope.data.options[gsModel+'_url'] = attachment.sizes.full ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;
                    $scope.data.options[gsModel+'_width'] = attachment.width;
                }
                $scope.$apply();
            });
        });
        // Open the uploader dialog
        mediaUploader.open();
    }

    $scope.change_box_color = function(index) {
        var newcol = $scope.data.options.performer_box_color[index];
        setTimeout(function(){
            jQuery(".jscolor_"+index).css("background-color", "#" + newcol);
            $scope.$apply();
        }, 500);
    }

    $scope.change_type_box_color = function(index) {
        var newcol = $scope.data.options.type_box_color[index];
        setTimeout(function(){
            jQuery(".jscolor_"+index).css("background-color", "#" + newcol);
            $scope.$apply();
        }, 500);
    }

    $scope.change_organizer_box_color = function(index) {
        var newcol = $scope.data.options.organizer_box_color[index];
        setTimeout(function(){
            jQuery(".jscolor_"+index).css("background-color", "#" + newcol);
            $scope.$apply();
        }, 500);
    }

    $scope.change_venue_box_color = function(index) {
        var newcol = $scope.data.options.venue_box_color[index];
        setTimeout(function(){
            jQuery(".jscolor_"+index).css("background-color", "#" + newcol);
            $scope.$apply();
        }, 500);
    }

})
.directive("customFieldData", function($compile) {
    return { 
        scope: true,
        link: function($scope, element, attrs, ngModel) {
            $scope.$watch('someValue', function (val) {
                if (val) {
                    $(element).html($scope.$parent.customFieldData).show();
                    $compile($('.em_custom_booking_field_html'))($scope);
                    if($scope.$parent.fieldAction == 'add'){
                        if(!$scope.$parent.data.options.custom_booking_field_data[$scope.$parent.buttonKey]){
                            $scope.$parent.data.options.custom_booking_field_data[$scope.$parent.buttonKey] = {required: 0, type: $scope.$parent.fieldKey, label: ''};
                        }
                        $scope.$parent.buttonKey++;
                    }
                    if($scope.$parent.fieldAction == 'edit'){
                        $scope.$parent.buttonKey++;
                    }
                    $scope.$parent.someValue = false;
                }
            });
        },
    }
});