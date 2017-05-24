(function ($) {
  "use strict";

  $(function () {

    $('.delete-language').click(function () {

      var button = $(this);

      if (confirm(wpm_params.confirm_question)) {

        var data = {
          action: 'wpm_delete_lang',
          locale: button.data('locale'),
          security: wpm_params.delete_lang_nonce
        };
        $.ajax({
          url: wpm_params.ajax_url,
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
      var select = $(this);
      if (select.val()) {
        var flag = wpm_params.plugin_url + '/flags/' + select.val() + '.png';
        if (select.next().length) {
          select.next().attr('src', flag);
        } else {
          select.parent().append('<img src="' + flag + '">');
        }
      } else {
        select.next().remove();
      }
    });

  });
}(jQuery));
