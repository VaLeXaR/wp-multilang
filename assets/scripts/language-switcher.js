(function ($) {
  "use strict";

  $(function () {

    if (typeof qtn_params != 'undefined') {

      if ($('#qtn-language-switcher').length === 0) {
        var language_switcher = _.template(qtn_params.switcher);
        $('h1').after(language_switcher);
      }

      if ($('.qtn-edit-lang').length === 0) {
        $('form').each(function () {
          $(this).append('<input class="qtn-edit-lang" name="lang" type="hidden" value="' + qtn_params.lang + '">');
        });
      }
    }

  });
}(jQuery));
