(function ($) {
  "use strict";

  function formatState (item) {
    if (!item.id) {
      return item.text;
    }
    return $('<img src="' + $(item.element).data('flag') + '" /> ' + item.text + '</span>');
  }

  $(function () {

    $(document).on('click', '.delete-language', function () {

      var button = $(this);

      if (confirm(wpm_languages_params.confirm_question)) {

        var data = {
          action: 'wpm_delete_lang',
          language: button.data('language'),
          security: wpm_languages_params.delete_lang_nonce
        };

        $.ajax({
          url: wpm_languages_params.ajax_url,
          type: 'post',
          data: data,
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

    $('#add_lang').click(function () {
      var t_language = wp.template('wpm-add-lang');
      var data = {
        count: wpm_lang_count
      };
      $('#wpm-languages').append(t_language(data));
      $(".wpm-flags").trigger('init_select2');
      wpm_lang_count++;
    });


    $(document).on('click', '.handlediv', function(){
      $(this).parent().toggleClass('closed');
    });

    $(document).on('keypress, keyup', '.postbox input[name$="[name]"]', function(){
      var text = $(this).val();
      $(this).parents('.postbox').find('h2 .language-order+span').text(text);
    });

    $(document).on('init_select2', '.wpm-flags', function(){
      $(this).select2({
        templateResult: formatState,
        templateSelection: formatState
      });
    });

    $(".wpm-flags").trigger('init_select2');

  });
}(jQuery));
