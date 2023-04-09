eventMagicApp.controller('emRegisterCtrl',function($scope,$http,EMRequest){
    $scope.data = {};
    $scope.data.showLogin = true;
    $scope.recaptcha_enabled = 0;
    $scope.requestInProgress = false;
    $scope.chkFormSubmitted = false;
    $scope.progressStart = function() {
        $scope.requestInProgress = true;
    }
    $scope.progressStop = function() {
        $scope.requestInProgress = false;
    }
    $scope.initialize = function(dataurl,recaptcha_enabled,defaultForm){ 
        $scope.recaptcha_enabled =  recaptcha_enabled;
        if(defaultForm == 'registration'){
            $scope.data.showLogin = false;     
        }
        var guest_paypal_booking = localStorage.getItem("guest_paypal_booking");
        if(guest_paypal_booking == 1){
            var guest_paypal_orderid = localStorage.getItem("guest_paypal_orderid");
            localStorage.removeItem("guest_paypal_booking");
            localStorage.removeItem("guest_paypal_orderid");
            if(guest_paypal_orderid){
                $scope.data.gbid = guest_paypal_orderid;
                $scope.data.redirect_url = dataurl;
                location.href = dataurl;
                /*EMRequest.send('em_guest_booking_show_order_detail', $scope.data).then(function (response) {
                    jQuery(".em_reg_form").append(response.data);
                });*/
            }
        }
    }
     
    $scope.register = function(isValid){
        $scope.chkFormSubmitted = true;
        if (!isValid) {
            return;
        }
        $scope.requestInProgress = true;
        var password = $scope.data.password;
        /*check password length */
        if(password.trim().length >= 5){ 
           if($scope.recaptcha_enabled == 1){
               $scope.data.captchaResponse = grecaptcha.getResponse();  //send g-captcah-response to our server
               $scope.data.recaptcha_enabled = $scope.recaptcha_enabled;
           }      
           EMRequest.send('em_register_user',$scope.data).then(function(tmp){
                $scope.requestInProgress = false;
                $scope.chkFormSubmitted = false;
                var response = tmp.data;
                if(response.success){
                    $scope.data.showLogin= true;
                    $scope.data.newRegistration= true;
                    if($scope.recaptcha_enabled == 1){
                        grecaptcha.reset(); // reset the reCaptcha
                    }
                    if(response.data.redirect){
                        window.location = response.data.redirect;
                    }else{
                        window.location.reload();
                    }
                } else {
                    $scope.data.registerError= response.data.msg;
                    if($scope.recaptcha_enabled == 1){
                        $scope.data.captchaError = response.data.captcha_msg;  
                        grecaptcha.reset(); // reset the reCaptcha
                    }
                }
            });
        }
        else{
            alert("Password length must be greater than or equal to 5");
        }
    }
     
    $scope.login = function(isValid){
        $scope.chkFormSubmitted = true;
        if (!isValid) {
            return;
        }
        $scope.requestInProgress = true;
        $scope.data.registerError = "";
        // check for event_id in url
        var params = new URLSearchParams(window.location.search);
        if(params.get('event_id')){
            $scope.data.event_id = params.get('event_id');
        }
        EMRequest.send('em_login_user',$scope.data).then(function(tmp){
            $scope.requestInProgress = false;
            $scope.chkFormSubmitted = false;
            var response = tmp.data;                
            if(response.errors){
                if(response.errors.user_not_exists){
                    $scope.data.loginError= response.errors.user_not_exists[0];
                }

                if(response.errors.invalid_user){
                    $scope.data.loginError= response.errors.invalid_user[0];
                }
            }                
            if(response.success){
                if(response.redirect){
                    location.href = response.redirect;
                }
                else{
                    location.reload();
                }
            }
        });
    }
});