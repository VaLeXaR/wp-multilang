( function( $ ) {
  "use strict";

  $( function() {

    $('#wpm_installed_localizations').on('init_localizations', function(){
      if ($(this).val()) {
        $('#delete_localization').prop('disabled', false);
      } else {
        $('#delete_localization').prop('disabled', true);
      }
    });

    $('#delete_localization').click(function(){

      if (confirm(wpm_additional_settings_params.confirm_question)) {

        var locale = $('#wpm_installed_localizations').val();
        var button = $(this);

        var data = {
          action: 'wpm_delete_localization',
          locale: locale,
          security: wpm_additional_settings_params.delete_localization_nonce
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
            if (json.success) {
              button.after('<span class="success">' + json.data + '</span>');
              $('#wpm_installed_localizations option[value="' + locale + '"]').remove();
            } else {
              button.after('<span class="error">' + json.data + '</span>');
            }
          },
          complete: function() {
            $('#wpm_installed_localizations').trigger('init_localizations');
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
      }
    });

    $('#qtx_import').click(function(){
      var button = $(this);

      var data = {
        action: 'wpm_qtx_import',
        security: wpm_additional_settings_params.qtx_import_nonce
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
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    });

  });
})( jQuery );
