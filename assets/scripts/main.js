(function ($) {
  "use strict";

  $(function () {

    $('#WPLANG').parents('tr').hide();

    $(document).on('click', '.delete-language', function () {

      var button = $(this);

      if (confirm(wpm_params.confirm_question)) {

        var data = {
          action: 'wpm_delete_lang',
          language: button.data('language'),
          security: wpm_params.delete_lang_nonce
        };
        $.ajax({
          url: wpm_params.ajax_url,
          type: 'post',
          data: data,
          dataType: 'json',
          complete: function () {
            button.parent().parents('.postbox').fadeOut('slow', function () {
              $(this).remove();
            });
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
      }
    });

    $("#wpm-languages").sortable();

    $(document).on('change', '.wpm-flags', function () {
      var select = $(this);
      if (select.val()) {
        var flag = wpm_params.flags_dir + select.val();
        if (select.next().length) {
          select.next().attr('src', flag);
        } else {
          select.parent().append('<img src="' + flag + '">');
        }
      } else {
        select.next().remove();
      }
    });

    $('#add_lang').click(function () {
      var t_language = wp.template('wpm-add-lang');
      var data = {
        count: wpm_lang_count
      };
      $('#wpm-languages').append(t_language(data)).sortable();
      wpm_lang_count++;
    });


    $(document).on('click', '.handlediv', function(){
      $(this).parent().toggleClass('closed');
    });

  });
}(jQuery));
