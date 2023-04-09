eventMagicApp = angular.module('eventMagicApp', ['angularUtils.directives.dirPagination']);

eventMagicApp.service('EMRequest',function($http){ 
    return {
        send : function(action,data){
            return $http({
            method: 'POST',
            url: em_ajax_object.ajax_url + "?action=" + action,
            data: JSON.stringify(data),
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
            }).then(function successCallback(response) {
                return response;
            }, function(error){
                console.log(error);
                alert("It seems either server is not responding or internet connection is lost");
            });
        }
    }
});

eventMagicApp.directive('convertToNumber', function() {
  return {
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      ngModel.$parsers.push(function(val) {
        return val != null ? parseInt(val, 10) : null;
      });
      ngModel.$formatters.push(function(val) {
        return val != null ? '' + val : null;
      });
    }
  };
});

/**
 * 
 * 
 * @return {MediaUploader} WordPress default MediaUploader object
 */
eventMagicApp.service('MediaUploader', function () {

    this.openUploader = function (multiImage) {
        var mediaUploader;
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            }, multiple: multiImage});

        return mediaUploader;

    }
});

/**
 * Post utility service
 */
eventMagicApp.service('PostUtility', function ($http) {
    
    /*
     * Function to delete posts
     */
    this.delete = function (ids, em_delete_nonce = undefined, request_type = undefined) {
        ids = ids || [];
        var request= {
            'ids'             : ids,
            'em_delete_nonce' : em_delete_nonce,
            'request_type'    : request_type
        }
        return $http({
            method: 'POST',
            url:              em_ajax_object.ajax_url + "?action=em_delete_posts",
            data:             JSON.stringify(request),
            transformRequest: angular.identity,
            headers:          {'Content-Type': undefined}
        }).success(function (data) {
            //location.reload();
        }).error(function (error) {

        });
    };
    
    /*
     * Function to delete posts
     */
    this.duplicate = function (ids, em_duplicate_nonce = undefined, request_type = undefined) {
        ids = ids || [];
        var request= {
            'ids'                : ids,
            'em_duplicate_nonce' : em_duplicate_nonce,
            'request_type'       : request_type
        }
        return $http({
            method: 'POST',
            url: em_ajax_object.ajax_url + "?action=em_duplicate_posts",
            data: JSON.stringify(request),
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
        }).success(function (data) {
              
        }).error(function (error) {

        });
    };
    
});


/**
 * Term utility service
 */
eventMagicApp.service('TermUtility', function ($http) {

    /*
     * Function to delete Terms
     */
    this.delete = function ( ids, tax_type, em_delete_nonce = undefined, request_type = undefined ) {
        ids = ids || [];
        tax_type= tax_type || 'category';
        
        var request = {
            'ids':             ids,
            'tax_type':        tax_type,
            'em_delete_nonce': em_delete_nonce,
            'request_type':    request_type,
        }
        
        return $http({
            method:           'POST',
            url:              em_ajax_object.ajax_url + "?action=em_delete_terms",
            data:             JSON.stringify(request),
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
        }).success(function (data) {
                
        }).error(function (error) {

        });
    };
    
});


/* Background Image Directive */
eventMagicApp.directive('backImg', function(){
    return function(scope, element, attrs){
        attrs.$observe('backImg', function(value) {
            element.css({
                'background-image': "url(" + value + ")",
                'background-size' : 'cover'
            });
        });
    };
});

eventMagicApp.factory('Seat', function () {
    function Seat(type, price, row, col, seat_color) {
        this.type = type;
        this.price = price;
        this.row = row;
        this.col = col;
        this.columnMargin = 0;
        this.rowMargin = 0;
        this.uniqueIndex = row + "-" + col;
        this.seatSequence;
        this.seatColor = '#'+seat_color;
        this.seatBorderColor = '3px solid #'+seat_color;
        
    }
    Seat.prototype.getType = function () {
        return this.type;
    };
    Seat.prototype.setType = function (type) {
        this.type = type;
    };
    Seat.prototype.setPrice = function (price) {
        this.price = price;
    };
    Seat.prototype.getPrice = function () {
        return this.price;
    };
    Seat.prototype.getUniqueIndex = function () {
        return this.uniqueIndex;
    };
    Seat.prototype.getSeatSequence = function () {
        return this.seatSequence;
    };
    Seat.prototype.setSeatSequence = function (seatSequence) {
        return this.seatSequence = seatSequence;
    };
    Seat.prototype.getSeatColor = function () {
        return this.seatColor;
    };
    Seat.prototype.setSeatColor = function (seatColor) {
        return this.seatColor = '#'+seatColor;
    };
    Seat.prototype.getSeatBorderColor = function () {
        return this.seatBorderColor;
    };
    Seat.prototype.setSeatBorderColor = function (seatBorderColor) {
        return this.seatBorderColor = '3px solid #'+seatBorderColor;
    };
    
    return Seat;
});

eventMagicApp.directive('ieSelectFix', ['$document',
        function($document) {

            return {
                restrict: 'A',
                require: 'ngModel',
                link: function(scope, element, attributes, ngModelCtrl) {
                   // var isIE = $document[0] && $document[0].attachEvent;
                  //  if (!isIE) return;

                    var control = element[0];
                    //to fix IE8 issue with parent and detail controller, we need to depend on the parent controller
                    scope.$watch(attributes.ieSelectFix, function() {
                        // setTimeout is needed starting from angular 1.3+
                        setTimeout(function() {
                            //this will add and remove the options to trigger the rendering in IE8
                            var option = document.createElement("option");
                            control.add(option,null);
                            control.remove(control.options.length-1);
                        }, 0);
                    });
                }
            }
        }
    ]);