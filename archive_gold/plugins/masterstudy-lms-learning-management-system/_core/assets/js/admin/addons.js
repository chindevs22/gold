"use strict";

(function ($) {
  "use strict";

  $(document).ready(function () {
    var _stm_lms_addons;

    var addons = JSON.parse((_stm_lms_addons = stm_lms_addons) === null || _stm_lms_addons === void 0 ? void 0 : _stm_lms_addons.enabled_addons),
        $addons_search = $('#addons-search');
    $('.addon-install .toggle-addon').on('click', function () {
      var addon = $(this).data('key');
      var addon_item = $(this);
      addons[addon] = typeof addons[addon] === 'undefined' || addons[addon] === '' ? 'on' : '';
      $.ajax({
        url: stm_lms_ajaxurl,
        method: 'POST',
        data: {
          action: 'stm_lms_pro_save_addons',
          nonce: stm_lms_pro_nonces['stm_lms_pro_save_addons'],
          addons: JSON.stringify(addons)
        },
        beforeSend: function beforeSend() {
          $(addon_item).parents('.stm-lms-addon').addClass('loading');
        },
        success: function success() {
          $(addon_item).find('.is_toggle').toggleClass('active');
          $(addon_item).parent().siblings('.addon-settings').toggleClass('active');
        },
        complete: function complete() {
          $(addon_item).parents('.stm-lms-addon').removeClass('loading');
        }
      });
    });
    $addons_search.on('input', function (e) {
      filter_addons(e.target.value.toLowerCase());
    });

    if ($addons_search.val().length > 0) {
      filter_addons($addons_search.val().toLowerCase());
    }

    function filter_addons(search) {
      $('.stm-lms-addon').each(function (i, item) {
        $(item).toggle($(item).find('.addon-name').text().toLowerCase().indexOf(search) !== -1);
      });
      $('#wpbody').resize();
    }
  });
})(jQuery);