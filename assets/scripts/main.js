(function ($) {
  "use strict";

  $(function () {

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
              $('.language-order').each(function(i){
                $(this).text(i+1);
              });
              wpm_lang_count--;
            });
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
      }
    });

    $("#wpm-languages").sortable({
      update: function(){
        $('.language-order').each(function(i){
          $(this).text(i+1);
        });
      }
    });

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
      $('#wpm-languages').append(t_language(data));
      wpm_lang_count++;
    });


    $(document).on('click', '.handlediv', function(){
      $(this).parent().toggleClass('closed');
    });

    $(document).on('keypress, keyup', '.postbox input[name$="[name]"]', function(){
      var text = $(this).val();
      $(this).parents('.postbox').find('h2 .language-order+span').text(text);
    });

    $('#wpm_installed_translations').change(function(){
      if ($(this).val()) {
        $('#delete_translation').prop('disabled', false);
      } else {
        $('#delete_translation').prop('disabled', true);
      }
    });

    $('#wpm_installed_translations').trigger('change');


    $('#delete_translation').click(function(){

      if (confirm(wpm_params.confirm_question)) {

        var locale = $('#wpm_installed_translations').val();

        var data = {
          action: 'wpm_delete_translation',
          locale: locale,
          security: wpm_params.delete_translation_nonce
        };
        $.ajax({
          url: wpm_params.ajax_url,
          type: 'post',
          data: data,
          dataType: 'json',
          success: function () {
            $('#wpm_installed_translations option[value="' + locale + '"]').remove();
            $('#wpm_installed_translations').trigger('change');
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
      }
    });

  });
}(jQuery));
