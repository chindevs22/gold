"use strict";

(function ($) {

    function send_request(action, _this, extra_data = {}){
        let data = {
            action: 'slms_' + action,
            post_id: quiz_data_vars.post_id,
            item_id: quiz_data_vars.item_id,
            nonce: stm_lms_vars.wp_rest_nonce
        }

        if(extra_data) {
            delete extra_data.action;
            delete extra_data.quiz_id;
            delete extra_data.course_id;
            delete extra_data.source;
            delete extra_data.nonce;
            Object.keys(extra_data).forEach(function (key) {
                data[key] = extra_data[key];
            });
        }

        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: data,
            beforeSend: function beforeSend() {
                _this.addClass('active');
            },
            success: function success(data) {
                _this.removeClass('active');
                get_callback(action, data);
            }
        });
    }

    function get_callback(action, data){
        if(action === 'reset_quiz') {
            if(data.success) {
                if(typeof data.return_url !== 'undefined' && data.return_url !== '') {
                    window.location = data.return_url;
                } else {
                    location.reload();
                }
            }
        }
        if(action === 'save_quiz') {
            if(data.success) {
                // location.reload();
            }
        }
    }

    $(document).ready(function () {

        // $('body').on('click', '.stm-lms-course__sidebar_refresh', function () {
        //     let return_url = $(this).data('reload');
        //     send_request('reset_quiz', $(this), {'return_url' : return_url});
        // });

        $('body').on('click', '.stm-lms-course__sidebar_save', function () {
            let form = $('.stm-lms-single_quiz');
            var data = {};
            form.serializeArray().forEach(function (item) {
                if (item.name.includes('[]')) {
                    var key = item.name.replace('[]', '');

                    if (typeof data[key] === 'undefined') {
                        data[key] = [item.value];
                    } else {
                        data[key].push(item.value);
                    }
                } else {
                    data[item.name] = item.value;
                }
            });

            send_request('save_quiz', $(this), data);
        });

    });
})(jQuery);