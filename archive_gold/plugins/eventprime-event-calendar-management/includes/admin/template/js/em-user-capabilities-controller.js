eventMagicApp.controller('eventUserCapabilitiesCtrl',function($scope, $http, EMRequest){
    $scope.data = {};
    $scope.requestInProgress = false;
    $scope.formErrors = [];
    $scope.selections = [];
    $scope.response = {};

    /* Show/Hide element while performing HTTP request */
    $scope.progressStart = function() {
        $scope.requestInProgress = true;
    }
    
    $scope.progressStop = function() {
        $scope.requestInProgress = false;
    }
    
     /*
     * Initializa page data on init
     */
    $scope.initialize = function(type){
        $scope.data.em_request_context = 'admin_custom_user_caps';
        $scope.data.em_user_cap_nonce = em_user_cap_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function(response){
            $scope.progressStop();
            var responseBody = response.data;
            $scope.data = responseBody.data;
        });
    }
    
    /*
     * Save information
     */
    $scope.saveUserCaps = function (isValid) {
        // Return if form invalid
        if (!isValid){
            return;
        }
        $scope.data.em_user_cap_nonce = em_user_cap_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_save_user_custom_caps', $scope.data).then(function(response){
            $scope.progressStop();
            var responseBody = response.data;
            if(responseBody.success){
                $scope.response = responseBody.data;
                setTimeout(function(){
                    $scope.response = {};
                }, 3000);
            } else{
                if(responseBody.data.hasOwnProperty('errors')){
                    $scope.formErrors = responseBody.data.errors;
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }
            }
        });
    }
    
});