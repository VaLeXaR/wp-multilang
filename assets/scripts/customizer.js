(function ($) {
  "use strict";

  $(function () {

    if ($('#wpm-language-switcher').length === 0) {
      var language_switcher = wp.template('wpm-ls-customizer');
      $('#customize-header-actions').append(language_switcher);
    }

  });
})(jQuery, wp);
