/**
 * 
 * Deactivation Feedback controller
 */
eventMagicApp.controller('feedbackFormCtrl', function($scope, $http, EMRequest) {
    $scope.data = {};
    $ = jQuery;
    
    /*jQuery('#the-list').find('[data-slug="eventprime-event-calendar-management"] span.deactivate a').click( function(event) {
        jQuery("#ep-deactivate-feedback-dialog-wrapper, .ep-modal-overlay ").show();
        epDeactivateLocation = jQuery(this).attr('href');
        event.preventDefault();
    });*/
    
    jQuery("input[name='ep_feedback_key']").change(function() {
        var ep_selectedVal= jQuery(this).val();
        var ep_reasonElement= jQuery("#ep_reason_" + ep_selectedVal);
        jQuery(".ep-deactivate-feedback-dialog-input-wrapper .epinput").hide();
        if(ep_reasonElement!==undefined) {
            ep_reasonElement.show();
        }
    });
    
    $scope.cancelForm = function() {
        jQuery("#ep-deactivate-feedback-dialog-wrapper, .ep-modal-overlay ").hide();
    }
        
    $scope.submitForm = function() {
        var selectedVal= jQuery("input[name='ep_feedback_key']:checked").val();
        
        if (selectedVal === undefined) {
            location.href = epDeactivateLocation;
            return;
        }
        
        var ep_feedbackInput= jQuery("input[name='ep_reason_"+ selectedVal + "']");
        $scope.data = {
            'feedback': jQuery("input[name='ep_feedback_key']:checked").val(),
            'msg': ep_feedbackInput.val()
        };
        
        jQuery(".ep-ajax-loader").show();
        
        EMRequest.send('em_deactivation_feedback',$scope.data).then(function(response) {
            var responseBody= response.data;
            jQuery(".ep-ajax-loader").hide();
            location.href = epDeactivateLocation;
        });
    }
});