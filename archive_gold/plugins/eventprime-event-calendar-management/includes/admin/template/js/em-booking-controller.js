/************************************* Booking Controller ***********************************/

eventMagicApp.controller('bookingCtrl',function($scope, $http,MediaUploader,PostUtility,EMRequest){
    $scope.data={};
    
    $scope.requestInProgress= false;
    $scope.formErrors='';
    $scope.paged=1;
    $scope.selections= [];
    $scope.showDates= false;
    $scope.resetPagination= false;
    
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
     * Controller intitialization for data load 
     */
    $scope.initialize= function(type){
        if(type=="edit"){
            $scope.preparePageData();
        }
        
        if(type=="list"){
            $scope.resetListPage();
            var eventId= em_get('event');
            if(eventId){
                $scope.data.event= eventId;
            }
            $scope.prepareListPage();
        }
    }
    
     /*
     * 
     * Loading Event data on init
     */ 
    $scope.preparePageData = function () {
        $scope.data.em_request_context= 'admin_booking';
        $scope.data.post_id= em_get('post_id');
        
        $scope.progressStart();
        EMRequest.send('em_load_strings',$scope.data).then(function(response){   
            $scope.progressStop();
            var responseBody= response.data;
            if(responseBody.success){
                $scope.data= responseBody.data;
            }
            $scope.calculateDiscount(); 
        });   
        
        var request= {};
        request.post_id=$scope.data.post_id
        $scope.progressStart();
        // Fetches RM data in any
        EMRequest.send('em_rm_custom_datas',request).then(function(res){
                    $scope.progressStop();      
                    return jQuery("#em_rm_custom_data").html(res.data);
        }); 
    };
    
    
    /***************************** Single Booking Page ****************
     /*
     * Save Booking information
     */
    $scope.savePost = function () {
        // If form is valid against all the angular validations
        $scope.progressStart();
       
        EMRequest.send('em_save_booking',$scope.data.post).then(function(response){
            $scope.progressStop();
            var responseBody= response.data;
            if(responseBody.success){
                $scope.data= responseBody.data;
            }
        });
        
    }
    
    
    /*
     * Calculating discount on individual booking page
     */
    $scope.calculateDiscount= function(){
        $scope.data.price= $scope.data.post.order_info.quantity*$scope.data.post.order_info.item_price;
        $scope.data.final_price=  $scope.data.price-$scope.data.post.order_info.discount;
    }

   
    /*
     * Initiating refund from admin 
     */
    $scope.cancelBooking= function()
    {
        var flag= confirm("Are you sure you want to refund this transaction");
        if(flag)
        {   $scope.progressStart();
            EMRequest.send('em_cancel_booking',$scope.data.post).then(function(response){
            $scope.progressStop();
            $scope.preparePageData();
            $scope.refund_status= response.data.msg;
            }); 
        }
    }
    
    /*
     * Requesting reset password mail
     */
    $scope.reset_password_mail= function(){
       var request= {};
       request.post_id=$scope.data.post.id;
       $scope.progressStart();
       EMRequest.send('em_resend_mail',request).then(function(response){
            $scope.progressStop();   
            alert("Reset Password Mail has been sent successfully");
        });
    }
    
    /*
     * Requesting cancelation mail
     */
    $scope.cancellation_mail= function(){
       var request= {};
     
        request.post_id=$scope.data.post.id;
        $scope.progressStart();
        EMRequest.send('em_booking_cancellation_mail',request).then(function(response){
            $scope.progressStop(); 
            alert("Cancellation Mail has been sent successfully");
        });
    }
    
    /*
     * Requesting confirm mail
     */
    $scope.confirm_mail= function(){
       var request= {};
        request.post_id=$scope.data.post.id;
        $scope.progressStart();
        EMRequest.send('em_booking_confirm_mail',request).then(function(response){
            $scope.progressStop(); 
            alert("Confirm Mail has been sent successfully");
        });
    }
    
    /*
     * Requesting refund mail
     */
    $scope.refund_mail= function(){
       var request= {};
       request.post_id=$scope.data.post.id;
       $scope.progressStart();
       EMRequest.send('em_booking_refund_mail',request).then(function(response){
            $scope.progressStop(); 
            alert("Refund Mail has been sent successfully");
        });
    }
    
    /*
     * Requesting pending mail
     */
    $scope.pending_mail= function(){
       var request= {};
       request.post_id=$scope.data.post.id;
       $scope.progressStart();
       EMRequest.send('em_booking_pending_mail',request).then(function(response){
            $scope.progressStop(); 
            alert("Pending Mail has been sent successfully");
          
        });
    }
    
    
    
    /******************* Booking List Page **************************/
    /*
     * 
     * Loading list data on init
    */
    $scope.prepareListPage= function(){
      $scope.data.em_request_context= 'admin_bookings';
      $scope.progressStart();

      EMRequest.send('em_load_strings',$scope.data).then(function(response){
        $scope.progressStop();
        var responseBody= response.data;
        if(responseBody.success){
          $scope.data= responseBody.data;
          if(!$scope.data.show_no_of_booking){
            $scope.data.show_no_of_booking = 10;
          }
        }
      });
    }
    
    /*
     * Reseting pagination
     */
    $scope.resetListPage= function()
    {
        $scope.data.paged=$scope.paged;
        $scope.data.em_request_context= 'admin_bookings';
        $scope.data.date_from= jQuery("#em_date_from").val();
        $scope.data.date_to= jQuery("#em_date_to").val();
        $scope.data.show_no_of_booking = 10;
    }
    
    /*
     * Deleting booking
     */
    $scope.deletePosts= function(){
        var confirmed = confirm("This will delete all data associated with this booking .");
        if(confirmed){
            $scope.progressStart();
            PostUtility.delete($scope.selections, em_booking_object.nonce, 'bookings').then(function(data){
                if(data.data.error == true){
                    $scope.progressStop();
                    alert(data.data.message);
                }
                else{
                    location.reload();
                }
            });
        }
    }
    
    $scope.cancelPosts= function(isDelete){
        if($scope.selections){
            if(isDelete){
                var confirmed = confirm("This will update the booking status to Cancelled and delete all associated with selected bookings.");    
            }
            else{
                var confirmed = confirm("This will update the booking status to Cancelled.");
            }
            if(confirmed){
                $scope.data.em_request_context = 'admin_cancel_bookings';
                $scope.data.delete_booking = 0;
                if(isDelete){
                    $scope.data.delete_booking = 1;
                }
                $scope.data.ids = $scope.selections;
                $scope.progressStart();
                EMRequest.send('em_load_strings',$scope.data).then(function(response){
                    $scope.progressStop();
                    var responseBody= response.data;
                    if(responseBody.success){
                        location.reload();
                    }
                    else{
                        $scope.progressStop();
                        alert(responseBody.message);
                    }
                });
            }
        }
    }
    
    /*
     * Chaining filter params
     */
    $scope.filter= function(){
       
        if($scope.data.filter_between=="range")
        {   
            $scope.data.date_from= jQuery("#em_date_from").val();
            $scope.data.date_to= jQuery("#em_date_to").val();
        }
        
        $scope.data.paged=1;
        $scope.prepareListPage();
       
    }
    
    /*
     * Called when pagination item clicked
     */
    $scope.pageChanged = function(newPage) {
            $scope.data.paged= newPage;
            $scope.prepareListPage();
            $scope.selectedAll=false;
    };
    
    /*
     * Select all events
    */
   $scope.markAll= function(){ 

//       if(jQuery("#em_bulk_selector").prop('checked'))
//       {
//           angular.forEach($scope.data.posts,function(post,key){
//              
//             $scope.selections.push(post.id);
//            });
//           jQuery(".em_card_check").prop('checked', true);
//         
//       }
//       else
//       {
//            angular.forEach($scope.data.posts,function(post,key){
//            $scope.selections.splice(post.id,1);
//            });
//           jQuery(".em_card_check").prop('checked', false);
//            
//           $scope.selections= [];
//       }

  angular.forEach($scope.data.posts, function (post) {
               if ($scope.selectedAll) { 
                 $scope.selections.push(post.id);
               // console.log($scope.selections.push(post.id));
                  post.selected = $scope.selectedAll ? post.id : 0; 
                  console.log( post.selected);
                   }
                   else{
                        $scope.selections= [];
                        post.selected = 0;
                   }
            });

    };
    
    /*
     * Adding or removing items on selection/deselection
     */
    $scope.updateSelection= function(post_id){
        if($scope.selections.indexOf(post_id)>=0){
            $scope.selections= em_remove_from_array($scope.selections,post_id);
        }
        else{
            $scope.selections.push(post_id);
        }
    }
    
    /*
     * Watching date fields for fitler
     */
    $scope.$watch('data.filter_between',function(newVal,oldVal){
        if(newVal=="range")
            $scope.showDates= true;
        else
            $scope.showDates= false;
    });
    
    $scope.prepare_export_link = function(submitStatus){
        $scope.progressStart();
        $scope.data.selections=$scope.selections; 
        EMRequest.send('em_export_bookings',$scope.data).then(function(response){
            if(response.status==200){
                $scope.progressStop();
                var link = document.createElement('a');
                link.download = "export.csv";
                link.href = 'data:application/csv;charset=utf-8,' + encodeURIComponent(response.data);
                link.click();
            }
        });
    }
});
eventMagicApp.filter('currencyPosition', function($sce) {
    return function(val, position, symbol) {
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