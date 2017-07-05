(function ($) {
  "use strict";

  $(function () {

    if ($('#wpm-language-switcher').length === 0) {
      var language_switcher = _.template($('#tmpl-wpm-ls').text());
      $('#customize-header-actions').append(language_switcher);
    }

  });
})(jQuery);
