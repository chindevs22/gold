/************************************* EventType Controller ***********************************/

eventMagicApp.controller('eventOrganizerCtrl',function($scope, $http, TermUtility, EMRequest, MediaUploader){
    $scope.data = {};
    $scope.requestInProgress = false;
    $scope.formErrors = [];
    $scope.selections = [];
    $scope.paged = 1;
    $scope.pagedS = 1;
    $scope.searchKeyword;
    $scope.data.sort_option = "name";
    $scope.order = 'ASC';
    $ = jQuery;
    $scope.regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    /* Show/Hide element while performing HTTP request */
    $scope.progressStart= function()
    {
        $scope.requestInProgress = true;
    }
    
    $scope.progressStop= function()
    {
        $scope.requestInProgress = false;
    }
    
     /*
     * Initializa page data on init
     */
    $scope.initialize= function(type){
        if(type=="edit")
            $scope.preparePageData();
        
        if(type=="list")  
            $scope.prepareListPage();   
    }
    /*
     * Loading Event Organizer page data
     */
    $scope.preparePageData = function () {
        $scope.data.em_request_context= 'admin_event_organizer';
        $scope.data.term_id= em_get('term_id');
        $scope.data.em_organizer_nonce = em_organizer_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_strings',$scope.data).then(function(response){
            $scope.progressStop();
            var responseBody = response.data;
            if(responseBody.data.hasOwnProperty('errors')){
                $scope.formErrors = responseBody.data.errors;
                jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
            } else{
                $scope.data = responseBody.data;
            }
        });
    };
    
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
                    // Event Type Image
                    $scope.data.post.image = attachment.sizes.thumbnail === undefined ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;
                    $scope.data.post.image_id = attachment.id;
                    $scope.$apply();
                }
            });
        });
        // Open the uploader dialog
        mediaUploader.open();
    }
    
    /*
     * Save information
     */
    $scope.saveEventOrganizer = function (isValid) {
        // Return if form invalid
        if (!isValid)
            return;
        $scope.formErrors= [];
        if( $('#description').is(':visible') ) {
            $scope.data.post.description= $('#description').val();
        }
        else {
            $scope.data.post.description= tinymce.get('description').getContent();   
        }

        var phone_num = $scope.data.post.organizer_phones;
        if(phone_num.length > 0){
            $scope.data.post.organizer_phones = [];
            angular.forEach(phone_num, function(value, key){
                let phone_val = value.replace(/[^\d\+\-\()]/g, '');
                if(phone_val){
                    $scope.data.post.organizer_phones.push(phone_val);
                }
            });
        }
        $scope.data.post.em_organizer_nonce = em_organizer_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_save_event_organizer',$scope.data.post).then(function(response){
            $scope.progressStop();
            var responseBody= response.data;
            if(responseBody.success){
                if(responseBody.data.hasOwnProperty('redirect')){
                    location.href=responseBody.data.redirect;
                }
            }
            else
            {
                if(responseBody.data.hasOwnProperty('errors')){
                    $scope.formErrors= responseBody.data.errors;
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }
            }
        });
    }
    
    /*
     * Fetch list page data
     */
    $scope.prepareListPage= function(){ 
        if ($scope.data.sort_option == "term_id"){
            $scope.order = "DESC";
        } else{
            $scope.order = "ASC";
        }
        $scope.request_in_progress = true;
        $scope.data.em_request_context ='admin_event_organizers';
        $scope.data.paged = $scope.paged;
        $scope.data.order = $scope.order;
        $scope.data.em_organizer_nonce = em_organizer_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_strings',$scope.data).then(function(response){
            $scope.progressStop();
            var responseBody = response.data;
            if(!responseBody.success)
                return;
            $scope.data = responseBody.data;
            $scope.request_in_progress = false;
        });
    }
    
    /*
     * Select item
     */
    $scope.selectTerm = function(term_id){
        if($scope.selections.indexOf(term_id) >= 0)
           $scope.selections = em_remove_from_array($scope.selections,term_id);
        else
            $scope.selections.push(term_id);
    }
    
    /*
     * Called when pagination changeds
     */
    $scope.pageChanged= function(pageNumber){
       
        $scope.paged = pageNumber;
        $scope.prepareListPage();
         $scope.selectedAll = false;
    }
    
    /*
     * Request for Event Organizer deletion
     */
    $scope.deleteTerms = function(){
        var confirmed = confirm(em_organizer_object.delete_confirm);
        if(confirmed){
            $scope.progressStart();
            TermUtility.delete($scope.selections, $scope.data.tax_type, em_organizer_object.nonce, 'event_organizer').then(function(response) {
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
     * Selection in bulk
     */
    $scope.checkAll = function () { 
        $scope.selections = [];
        if ($scope.selectedAll) { 
            $(".em-event-organizer-terms").each(function(){
                var termIds = this.id;
                var newTid = termIds.replace("em-evt-term-", "");
                $scope.selectTerm(Number(newTid));
            });
        }

        angular.forEach($scope.data.terms, function (term) {
            term.Selected = 0;
            if($scope.selections.indexOf(term.id) > -1){
                term.Selected = $scope.selectedAll ? term.id : 0; 
            }
        });
    };
    
    angular.forEach($scope.data.terms, function (term) {
        if ($scope.selectedAll) { 
            $scope.selections.push(term.id);
            // console.log($scope.selections.push(post.id));
            term.Selected = $scope.selectedAll ? term.id : 0; 
        }else{
            $scope.selections= [];
            term.Selected = 0;
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
     * Fetch list page data with search result
     */
    $scope.prepareListPageWithSearch = function(){
        if ($scope.data.sort_option == "term_id"){
            $scope.order = "DESC";
        } else{
            $scope.order = "ASC";
        }
        $scope.request_in_progress = true;
        $scope.data.em_request_context = 'admin_event_organizers_search';
        $scope.data.pagedS = $scope.pagedS;
        $scope.data.order = $scope.order;
        $scope.data.em_organizer_nonce = em_organizer_object.nonce;
        if($scope.searchKeyword){
            $scope.data.searchKeyword = $scope.searchKeyword;
        }
        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function(response){
            $scope.progressStop();
            var responseBody = response.data;
            if(!responseBody.success)
                return;
            $scope.data = responseBody.data;
            $scope.request_in_progress = false;
        });
    }

    /*
     * Called when pagination changes in searched results
     */
    $scope.searchPageChanged = function(pageNumber){
        $scope.pagedS = pageNumber;
        $scope.prepareListPageWithSearch();
        $scope.selectedAll = false;
    }

    $scope.reloadPage = function(){
        window.location.reload();
    }
    
});

eventMagicApp.filter('capitalize', function() {
    return function(token) {
        return token.charAt(0).toUpperCase() + token.slice(1);
     }
});