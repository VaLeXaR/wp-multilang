(function ($) {
  "use strict";

  $(function () {

    if ($('#wpm-language-switcher').length === 0) {
      var language_switcher = _.template($('#tmpl-wpm-ls').text());
      $('#customize-header-actions').append(language_switcher);
    }

    /*$.post(wpm_customizer_params.plugin_url, {
      current_url: wp.customize.previewer.previewUrl(),
      security: wpm_customizer_params.get_translated_url_nonce
    }, function (data) {
      wp.customize.previewer.previewUrl(data);
      wp.customize.previewer.refresh();
    });*/

  });
})(jQuery);
