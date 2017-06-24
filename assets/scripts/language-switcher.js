(function ($) {
  "use strict";

  $(function () {

    if ($('#wpm-language-switcher').length === 0) {
      var language_switcher = _.template($('#tmpl-wpm-ls').text());
      $('#wpbody-content .wrap').first().prepend(language_switcher);
    }

  });
}(jQuery));
