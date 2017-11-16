( function( $ ) {
  "use strict";

  // Edit prompt
  $( function() {

    $('#set_default_language').click(function(){
      var button = $(this);
      var data = {
        action: 'wpm_set_default_language',
        locale: locale,
        security: wpm_additional_settings.set_default_language_nonce
      };

      $.ajax({
        url: wpm_additional_settings.ajax_url,
        type: 'post',
        data: data,
        dataType: 'json',
        success: function () {

        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    });

  });
})( jQuery );
