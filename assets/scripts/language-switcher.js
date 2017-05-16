(function ($) {
  "use strict";

  $(function () {

    if ($('#wpm-language-switcher').length === 0) {
      var language_switcher = _.template(wpm_params.switcher);

      $('h1').before(language_switcher);
    }

  });
}(jQuery));
