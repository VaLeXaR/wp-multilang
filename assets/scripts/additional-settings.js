( function( $ ) {
  "use strict";

  // Edit prompt
  $( function() {

    $('#set_default_language').click(function(){
      var button = $(this);
      var data = {
        action: 'wpm_set_default_language',
        security: wpm_additional_settings_params.set_default_language_nonce
      };

      $.ajax({
        url: wpm_additional_settings_params.ajax_url,
        type: 'post',
        data: data,
        dataType: 'json',
        beforeSend: function() {
          button.prop('disabled', true).after('<span class="spinner is-active"></span>');
        },
        success: function (json) {
          button.next().remove();
          button.after('<span class="success">' + json + '</span>');
        },
        complete: function() {
          button.prop('disabled', false);
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    });

  });
})( jQuery );
