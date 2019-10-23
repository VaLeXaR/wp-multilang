(function ($) {
  "use strict";

  $(function () {
    if ($('#wpm-language-switcher').length === 0) {
      var language_switcher = wp.template('wpm-ls');
      $('#wpbody-content > .wrap').prepend(language_switcher);
    }

    /*$('form').each(function(){
      var form = $(this);
      var input = $('<input type="hidden" id="lang" name="lang" value="' + wpm_translator_params.language + '">');
      if (form.find('input#lang').length === 0) {
        form.append(input);
      }
    });*/
  });
})(jQuery, wp);
