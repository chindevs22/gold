/************************************* EventType Controller ***********************************/

eventMagicApp.controller('eventTypeCtrl',function($scope, $http, TermUtility, EMRequest, MediaUploader){
    $scope.data={};
    $scope.requestInProgress= false;
    $scope.formErrors=[];
    $scope.selections = [];
    $scope.paged=1;
    $scope.pagedS=1;
    $scope.searchKeyword;
    $scope.data.sort_option = "name";
    $scope.order = 'ASC';
    $=jQuery;
    
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
     * Setting background color
    */
    $scope.bgColor= function(term)
    {   
        var style= {"background-color": "#" + term.color};
        return style;
    }

    $scope.textColor= function(term)
    {   
        var style= {"background-color": "#" + term.type_text_color};
        return style;
    }
    
    /*
     * Loading Event type page data
     */
    $scope.preparePageData = function () {
        $scope.data.em_request_context= 'admin_event_type';
        $scope.data.term_id= em_get('term_id');
        $scope.data.em_event_type_nonce = em_event_type_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function(response){
            $scope.progressStop();
            var responseBody = response.data;
            if(responseBody.data.hasOwnProperty('errors')){
                $scope.formErrors = responseBody.data.errors;
                jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
            } else{
                $scope.data= responseBody.data;
                if($scope.data.term.color)
                    $("#em_color_picker").css("background-color","#" + $scope.data.term.color);

                if($scope.data.term.type_text_color)
                    $("#em_text_color_picker").css("background-color","#" + $scope.data.term.type_text_color);
                $scope.setupSlider();
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
                    $scope.data.term.image = attachment.sizes.thumbnail === undefined ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;
                    $scope.data.term.image_id = attachment.id;
                    $scope.$apply();
                }
            });
        });
        // Open the uploader dialog
        mediaUploader.open();
    }
    
    $scope.setupSlider= function(){
        var ranges= [21,28];
        if($scope.data.term.custom_group){
            if($scope.data.term.custom_group.indexOf("-")>=0){
                rangeArr= $scope.data.term.custom_group.split("-");
                ranges[0]=rangeArr[0];
                ranges[1]= rangeArr[1];
            }
        }
        
        if($scope.data.term.custom_group==null){
            $scope.data.term.custom_group=ranges[0]+ "-" + ranges[1];
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
                             $scope.data.term.custom_group=ui.values[0]+ "-" + ui.values[1]; 
                             $scope.$apply();
                     },
                 });		
          }		
        });		
    }
     
     /*
     * Save information
     */
    $scope.saveEventType = function (isValid) {
        // Return if form invalid
        if (!isValid)
            return;
        $scope.formErrors= [];
        if( $('#description').is(':visible') ) 
            $scope.data.term.description= $('#description').val();
        else 
            $scope.data.term.description= tinymce.get('description').getContent();   
        
        $scope.data.term.em_event_type_nonce = em_event_type_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_save_event_type',$scope.data.term).then(function(response){
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
     * Initializa page data on init
     */
    $scope.initialize= function(type){

        if(type=="edit")
            $scope.preparePageData();
        
        if(type=="list")  
            $scope.prepareListPage();   
    }
    
    /*
     * Fetch list page data
     */
    $scope.prepareListPage= function(){ 
        if ($scope.data.sort_option == "term_id")
        $scope.order = "DESC";
        else
        $scope.order = "ASC";
        $scope.request_in_progress= true;
        $scope.data.em_event_type_nonce = em_event_type_object.nonce;
        $scope.data.em_request_context = 'admin_event_types';
        $scope.data.paged = $scope.paged;
        $scope.data.order = $scope.order;
        $scope.progressStart();
        EMRequest.send('em_load_strings',$scope.data).then(function(response){
            $scope.progressStop();
            var responseBody= response.data;
            if(!responseBody.success)
                return;
            $scope.data= responseBody.data;
            $scope.request_in_progress= false;
        });
    }
     /*
     * Fetch list page data with search
     */
     $scope.prepareListPageWithSearch= function(){
        if ($scope.data.sort_option == "term_id")
        $scope.order = "DESC";
        else
        $scope.order = "ASC"; 
        $scope.request_in_progress= true;
        $scope.data.em_request_context='admin_event_types_search';
        $scope.data.pagedS= $scope.pagedS;
        $scope.data.order = $scope.order;
        if($scope.searchKeyword)
        $scope.data.searchKeyword= $scope.searchKeyword;
        $scope.progressStart();
        EMRequest.send('em_load_strings',$scope.data).then(function(response){
            $scope.progressStop();
            var responseBody= response.data;
            if(!responseBody.success)
                return;
            $scope.data= responseBody.data;
            $scope.request_in_progress= false;
        });
    }
    /*
     * Select item
     */
    $scope.selectTerm= function(term_id){
        if($scope.selections.indexOf(term_id)>=0)
           $scope.selections= em_remove_from_array($scope.selections,term_id);
        else
            $scope.selections.push(term_id);
    }
    
    /*
     * Called when pagination changeds
     */
    $scope.pageChanged= function(pageNumber){
       
        $scope.paged= pageNumber;
        $scope.prepareListPage();
         $scope.selectedAll=false;
    }
    /*
    * Called when pagination changes in searched results
    */
    $scope.searchPageChanged= function(pageNumber){
        $scope.pagedS= pageNumber;
        $scope.prepareListPageWithSearch();
        $scope.selectedAll=false;
    }

    $scope.reloadPage = function(){
        window.location.reload();
     }
    
    /*
     * Request for Event Type deletion
     */
    $scope.deleteTerms= function(){
        var confirmed = confirm(em_event_type_object.delete_confirm);
        if(confirmed){
            $scope.progressStart();
            TermUtility.delete($scope.selections, $scope.data.tax_type, em_event_type_object.nonce, 'event_type').then(function(response) {
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
            $(".em-event-type-terms").each(function(){
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
});