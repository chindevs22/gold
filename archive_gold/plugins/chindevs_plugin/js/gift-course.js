// we completely deleted the enterprise version of assets/js/enterprise-course.js
//masterstudy-lms-learning-management-system/_core/assets/js/gift-course.js

"use strict";

(function ($) {
  var emails = [];
  var $body = $('body');
  $(document).ready(function () {
    var $price_btn = $('.add-to-cart');
    var group_ids = [];


	$body.on('click', '.add-to-cart', function (e) {
		console.log("adding to cart!");
		alert("hi");
		console.log(emails);
      e.preventDefault();
	  if ($('.stm_lms_popup_add_users__inner').find('.group-emails').children().length < 2) {
        $('.stm_lms_popup_add_users__inner').find('.heading_font').children().removeClass('warning');
      }
      var $this = $(this);
      if (!emails.length) return false;
      $.ajax({
        url: stm_lms_ajaxurl,
        dataType: 'json',
        context: this,
        data: {
          action: 'stm_lms_add_to_cart_gc',
          emails: emails,
          course_id: $this.data('course-id'),
          //nonce: stm_lms_nonces['stm_lms_add_to_cart_enterprise']
        },
        beforeSend: function beforeSend() {
          $this.addClass('loading');
        },
        complete: function complete(data) {
		  console.log(data);
		  console.log("data");
          data = data['responseJSON'];
          $this.removeClass('loading');
          if (data.redirect) {
            window.location.replace(data.cart_url);
          } else {
            $this.html(data.text).attr('href', data.cart_url).removeClass('add-to-cart');
          }
        }
      });
    });

	// Create Emails

    var $gc_email = $('#gc_email');

    $body.on('keyup change', '#gc_email', function (e) {
      var $this = $gc_email = $(this);
      var email = $this.val();
      if (validEmail(email)) $this.removeClass('invalid').addClass('valid');
      if (!validEmail(email)) $this.removeClass('valid').addClass('invalid');
      if (!email.length) $this.removeClass('invalid valid');
    });

    $body.on('click', '.add_email_gc', function () {
		console.log("adding an email");
      var email = $gc_email.val();
      if (!validEmail(email) || emails.includes(email) || emails.length == 1) return true;

      if ($(this).parents('.stm_lms_popup_add_users__inner').find('.gc-emails').children().length > 1) {
        $(this).parents('.stm_lms_popup_add_users__inner').find('.heading_font').children().addClass('warning');
        return true;
      } else {
        $(this).parents('.stm_lms_popup_add_users__inner').find('.heading_font').children().removeClass('warning');
      }

      emails.push(email);
	  $gc_email.val('').addClass('disable');
      $gc_email.val('').removeClass('invalid valid');
      listEmails();
	  calculatePrice();
	  disableButton();

    });

    $body.on('click', '.lnricons-cross', function () {
      var email = $(this).parent().find('span').text();
      var index = emails.indexOf(email);
      emails.splice(index, 1);
      $(this).parent().remove();
	  $gc_email.val('').removeClass('disable');
	  calculatePrice();
      disableButton();
    });
  });

 function calculatePrice() {
	 var $price_btn = $('.add-to-cart');
	 var price = $price_btn.data('price');
	 $price_btn.find('span').html(stm_lms_price_format(price * emails.length));
  }

  function disableButton() {
	  var $price_btn = $('.add-to-cart');
	  console.log("in disable");
	  if (emails.length == 0) {
		  $price_btn.addClass('disabled');
	  } else {
		  $price_btn.removeClass('disabled');
	  }
  }

  function listEmails() {
    var $gc_emails = $('.gc-emails');
    $gc_emails.html('');
    emails.forEach(function (value, index) {
      $gc_emails.append("<div class='gc-emails-container'><span data-index='" + index + "'>" + value + "</span><i class='lnricons-cross'></i></div>");
    });
  }


  function validEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  }
})(jQuery);