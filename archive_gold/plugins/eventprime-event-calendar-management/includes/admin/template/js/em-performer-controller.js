
/*
 * Performer Controller
 */
eventMagicApp.controller('performerCtrl', function ($scope, $http, $filter, MediaUploader, PostUtility, EMRequest) {
    $scope.data = {};
    var post_id = 0;
    $scope.data.sort_option = "name";
    $scope.paged = 1;
    $scope.pagedS = 1;
    $scope.searchKeyword;
    $scope.selections = [];
    $scope.post_edit = false;
    $scope.requestInProgress = false;

    $scope.progressStart = function ()
    {
        $scope.requestInProgress = true;
    }

    $scope.progressStop = function ()
    {
        $scope.requestInProgress = false;
    }

    //Loads page data for Add/Edit page 
    $scope.preparePageData = function () {
        $scope.data.em_request_context = 'admin_performer';
        // If "Edit" page
        if (em_get('post_id') > 0) {
            $scope.data.post_id = em_get('post_id');
            $scope.post_edit = true;
        }
        $scope.data.em_performer_nonce = em_performer_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function (response) {
            var responseBody= response.data;
            if(responseBody.data.hasOwnProperty('errors')){
                $scope.formErrors = responseBody.data.errors;
                jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
            }
            $scope.data = responseBody.data;
            $scope.progressStop();
        })
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
                    // Performer Image
                    $scope.data.post.feature_image = attachment.sizes.thumbnail === undefined ? attachment.sizes.full.url : attachment.sizes.thumbnail.url;
                    $scope.data.post.feature_image_id = attachment.id;
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
    $scope.savePerformer = function (isValid) {
        // If form is valid against all the angular validations
        if (isValid) {
            if (jQuery('#description').length > 0) {
                if (jQuery('#description').is(':visible')) {
                    $scope.data.post.description = jQuery('#description').val();
                } else
                {
                    if (tinymce) {
                        $scope.data.post.description = tinymce.get('description').getContent();
                    }

                }
            }
            $scope.data.post.em_performer_nonce = em_performer_object.nonce;
            $scope.progressStart();
            EMRequest.send('em_save_performer', $scope.data.post).then(function (response) {
                $scope.progressStop();
                var responseBody= response.data;
                if(responseBody.success){
                    if(responseBody.data.hasOwnProperty('redirect')){
                        location.href = responseBody.data.redirect;
                    }
                }
                else
                {
                    if(responseBody.data.hasOwnProperty('errors')){
                        $scope.formErrors = responseBody.data.errors;
                        jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
                    }
                }
            });
        }
    }


    $scope.initialize = function (task) {
        if (task == "edit") {
            $scope.preparePageData();
        }

        if (task == "list") {
            $scope.preparePerformerListPage();
        }
    }

    $scope.preparePerformerListPage = function () {
        var sortBy = $scope.data.sort_option;
        $scope.data.em_request_context = 'admin_performers';
        $scope.data.paged = $scope.paged;
        $scope.data.em_performer_nonce = em_performer_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function (response) {
            var responseBody= response.data;
            if(!responseBody.success)
                return;
            $scope.data = responseBody.data;
            if(sortBy == "count")
               $scope.data.posts = $filter('orderBy')($scope.data.posts,'events',true);
            else if(sortBy == "name")
               $scope.data.posts = $filter('orderBy')($scope.data.posts,'name',false);
            $scope.data.posts = $filter('limitTo')($scope.data.posts,$scope.data.pagination_limit,$scope.data.offset);
            $scope.progressStop();
        });
    }

    /*
     * Fetch list page data with search result
     */
    $scope.preparePerformerListPageWithSearch = function () {
        var sortBy = $scope.data.sort_option;
        $scope.data.em_request_context = 'admin_performers_search';
        $scope.data.pagedS = $scope.pagedS;
        if($scope.searchKeyword)
        $scope.data.searchKeyword = $scope.searchKeyword;
        $scope.data.em_performer_nonce = em_performer_object.nonce;
        $scope.progressStart();
        EMRequest.send('em_load_strings', $scope.data).then(function (response) {
            var responseBody= response.data;
            if(!responseBody.success)
                return;
            $scope.data = responseBody.data;
            if(sortBy == "count")
               $scope.data.posts = $filter('orderBy')($scope.data.posts,'events',true);
            else if(sortBy == "name")
               $scope.data.posts = $filter('orderBy')($scope.data.posts,'name',false);
            $scope.data.posts = $filter('limitTo')($scope.data.posts,$scope.data.pagination_limit,$scope.data.offset);
            $scope.progressStop();
        });
    }

    $scope.pageChanged = function (pageNumber) {
        $scope.selectedAll = false;
        $scope.paged = pageNumber;
        $scope.preparePerformerListPage();
    }

     /*
     * Called when pagination changes in searched results
     */
     $scope.searchPageChanged= function(pageNumber){
        $scope.pagedS= pageNumber;
        $scope.preparePerformerListPageWithSearch();
        $scope.selectedAll=false;
    }

    $scope.deletePosts = function () {
        var confirmed = confirm("Are you sure you want to delete. Please confirm");
        if (confirmed) {
            $scope.progressStart();
            PostUtility.delete($scope.selections, em_performer_object.nonce, 'performers').then(function(data){
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

    $scope.duplicatePosts = function () {
        $scope.progressStart();
        PostUtility.duplicate($scope.selections).then(function (data) {
            location.reload();
        });
    }

    $scope.selectPost = function (post_id) {
        if ($scope.selections.indexOf(post_id) >= 0) {
            em_remove_from_array($scope.selections, post_id)
        } else {
            $scope.selections.push(post_id);
        }

    }

    $scope.checkAll = function () {
        angular.forEach($scope.data.posts, function (post) {
            if ($scope.selectedAll) {
                $scope.selections.push(post.id);
                // console.log($scope.selections.push(post.id));
                post.Selected = $scope.selectedAll ? post.id : 0;
            } else {
                $scope.selections = [];
                post.Selected = 0;
            }
        });

    }

    $scope.reloadPage = function(){
        window.location.reload();
     }
    
    /*
     * Add functions for adding new contact fields for performer
     */
    $scope.addPhone = function() {
        $scope.data.post.performer_phones.push('');
    }
    
    $scope.addEmail = function() {
        $scope.data.post.performer_emails.push('');
    }
    
    $scope.addWebsite = function() {
        $scope.data.post.performer_websites.push('');
    }
    
    /*
     * Remove functions for removing contact fields for performer
     */
    $scope.removePhone = function(phone) {
       var index = $scope.data.post.performer_phones.indexOf(phone);
       $scope.data.post.performer_phones.splice(index,1);
    }
    
    $scope.removeEmail = function(email) {
       var index = $scope.data.post.performer_emails.indexOf(email);
       $scope.data.post.performer_emails.splice(index,1);
    }
    
    $scope.removeWebsites = function(website) {
       var index = $scope.data.post.performer_websites.indexOf(website);
       $scope.data.post.performer_websites.splice(index,1);
    } 
     
});

eventMagicApp.filter('capitalize', function() {
    return function(token) {
        return token.charAt(0).toUpperCase() + token.slice(1);
     }
});

