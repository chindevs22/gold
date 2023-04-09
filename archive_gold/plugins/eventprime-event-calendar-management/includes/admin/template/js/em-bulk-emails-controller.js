/************************************* Booking Controller ***********************************/

eventMagicApp.controller( 'bulkEmailsCtrl', function( $scope, $http, PostUtility, EMRequest ) {
    $scope.data = {};
    $scope.requestInProgress = false;
    $scope.formErrors = '';
    $scope.showEventDrop = false;
    $scope.selectedBulkEvent = '';
    $scope.attendee_no_found_error = '';
    
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
    $scope.initialize = function() {
        $scope.data.em_request_context = 'admin_bulk_emails';
        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function(response){   
            $scope.progressStop();
            var responseBody = response.data;
            if(responseBody.success){
                $scope.data = responseBody.data;
            }
        });
    }

    /**
     * get attendees email id on change of event
     */
    $scope.fetchAttendees = function(event_id){
        $scope.selectedBulkEvent = event_id;
        $scope.data.event_id = event_id;
        $scope.progressStart();
        EMRequest.send('em_get_attendees_email_by_event_id', $scope.data).then(function(response){   
            $scope.progressStop();
            var responseBody = response.data;
            if(responseBody.success){
                $scope.email_address = responseBody.data;
                $scope.attendee_no_found_error = '';
            }
            if(responseBody.data.hasOwnProperty('errors')){
                $scope.email_address = '';
                $scope.attendee_no_found_error = responseBody.data.errors;
            }
        });
    }

    $scope.sendEmails = function() {
        $scope.progressStart();
        if( jQuery('#content').is(':visible') ) {
            $scope.data.content= jQuery('#content').val();
        } 
        else {
            if( typeof tinymce != "undefined" ) {
                if( tinymce.get( 'content' ) ) {
                    $scope.data.content = tinymce.get( 'content' ).getContent();
                }
            }
        }
        $scope.data.email_address = $scope.email_address;
        $scope.data.email_subject = $scope.email_subject;
        $scope.data.cc_email_address = $scope.cc_email_address;
        EMRequest.send('em_send_bulk_emails', $scope.data).then(function(response){   
            $scope.progressStop();
            var responseBody = response.data;
            if(responseBody.data.hasOwnProperty('errors')){
                $scope.formErrors = responseBody.data.errors;
                jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
            }
            if(responseBody.success){
                alert(responseBody.data.message);
                location.reload();
            }
        });
    }

    $scope.checkEventDisplay = function(){
        $scope.showEventDrop = !$scope.showEventDrop;
    }
    
});