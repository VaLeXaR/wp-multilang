(function ($) {
  "use strict";

  $(function () {

    if ($('#wpm-language-switcher').length === 0) {
      var language_switcher = _.template(wpm_language_switcher_params.switcher);
      $('#wpbody-content > .wrap').prepend(language_switcher);
    }

  });
}(jQuery));
