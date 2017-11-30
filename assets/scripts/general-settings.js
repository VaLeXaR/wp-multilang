(function ($) {
  "use strict";

  $(function () {
    $('.wpm_url_mode').change(function(){
      var mode = Number($(this).val());

      if (mode === 3) {
        $('.wpm_language_domains').show();
        $('#wpm_use_prefix').closest('tr').hide();
      } else {
        $('.wpm_language_domains').hide();
        $('#wpm_use_prefix').closest('tr').show();
      }
    });

  });
})(jQuery);
