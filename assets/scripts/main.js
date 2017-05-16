(function ($) {
  "use strict";

  $(function () {

    $('.delete-language').click(function () {

      var button = $(this);

      if (confirm(wpm_main_params.confirm_question)) {

        var data = {
          action: 'wpm_delete_lang',
          locale: button.data('locale'),
          security: wpm_main_params.delete_lang_nonce
        };
        $.ajax({
          url: wpm_main_params.ajax_url,
          type: 'post',
          data: data,
          dataType: 'json',
          complete: function () {
            button.parents('tr').fadeOut('slow', function () {
              $(this).remove();
            });
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
      }
    });

    $("#wpm-languages tbody").sortable({
      handle: 'td:first-child'
    });

    $('.wpm-flags').on('change', function () {

      if ($(this).val() ) {
        var flag = wpm_main_params.plugin_url + '/flags/' + $(this).val() + '.png';
        if ($(this).next().length) {
          $(this).next().attr('src', flag);
        } else {
          $(this).before().html('<img src="' + flag + '">');
        }
      } else {
        $(this).next().remove();
      }
    });

  });
}(jQuery));
