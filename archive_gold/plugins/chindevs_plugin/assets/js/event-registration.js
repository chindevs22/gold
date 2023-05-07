"use strict";

(function ($) {
  var selectedPrice = 0;
  var $body = $('body');
  $(document).ready(function () {
    var $price_btn = $('.event-add-to-cart');

	$body.on('click', '.event-add-to-cart', function (e) {
	  console.log("hiiii");
      e.preventDefault();
      var $this = $(this);
      $.ajax({
        url: stm_lms_ajaxurl,
        dataType: 'json',
        context: this,
        data: {
          action: 'stm_lms_add_to_cart_reg_event',
          course_id: $this.data('course-id'),
		  price: selectedPrice,
        },
        beforeSend: function beforeSend() {
          $this.addClass('loading');
        },
        complete: function complete(data) {
          data = data['responseJSON'];
          $this.removeClass('loading');
          if (data.redirect) {
            window.location.replace(data.cart_url);
          } else {
            $this.html(data.text).attr('href', data.cart_url).removeClass('event-add-to-cart');
          }
        }
      });
    });

	  // Listen to change events on the radio buttons
	  $body.on('change', 'input[name=price]', function(e) {
		  console.log("yooo");
		  var addToCartButton = $('.event-add-to-cart');
		  var radioButtons = $('input[name=price]');
		  // Get the selected radio button and its price data
		  var selectedRadioButton = $(this);
		  selectedPrice = selectedRadioButton.data('price');

		  // Update the Add to Cart button price and enable it
		  addToCartButton.attr('data-price', selectedPrice);
		  addToCartButton.find('span').html(stm_lms_price_format(selectedPrice));
		  addToCartButton.removeClass('disabled');
	  });

	// Create Emails
  });


})(jQuery);