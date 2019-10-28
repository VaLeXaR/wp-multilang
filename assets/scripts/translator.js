String.prototype.xsplit = function (_regEx) {
  // Most browsers can do this properly, so let them work, they'll do it faster
  if ('a~b'.split(/(~)/).length === 3) {
    return this.split(_regEx);
  }

  if (!_regEx.global) {
    _regEx = new RegExp(_regEx.source, 'g' + (_regEx.ignoreCase ? 'i' : ''));
  }

  // IE (and any other browser that can't capture the delimiter)
  // will, unfortunately, have to be slowed down
  var start = 0, arr = [];
  var result;
  while ((result = _regEx.exec(this)) !== null) {
    arr.push(this.slice(start, result.index));
    if (result.length > 1) arr.push(result[1]);
    start = _regEx.lastIndex;
  }
  if (start < this.length) arr.push(this.slice(start));
  if (start === this.length) arr.push(''); //delim at the end
  return arr;
};

var wpm_translator = {

  string_to_ml_array: function (text) {

    if (Object.prototype.toString.call(text) !== '[object String]') {
      return text;
    }

    var split_regex = /(\[:[a-z-]+\]|\[:\])/gi;
    var blocks = text.xsplit(split_regex);

    if (typeof blocks !== 'object' || !Object.keys(blocks).length)
      return text;

    if (Object.keys(blocks).length === 1) {
      return blocks[0];
    }

    var results = {},
      languages = wpm_translator_params.languages;

    languages.forEach(function(item){
      results[item] = '';
    });

    var lang = blocks.length === 1 ? wpm_translator_params.default_language : '';

    blocks.forEach(function(block, index) {
      if (index % 2 === 1) {
        lang = block;
      } else if (!!results[lang]) {
        results[lang] += block.trim();
      }
    });

    return results;
  },


  translate_string: function (string, language) {

    var strings = wpm_translator.string_to_ml_array(string);

    if (typeof strings !== 'object' || !Object.keys(strings).length) {
      return string;
    }

    var languages = wpm_translator_params.languages;

    if (language) {
      if (!!languages[language]) {
        return strings[language];
      }

      return '';
    }

    language = wpm_translator_params.language;

    if (!strings[language].length && wpm_translator_params.show_untranslated_strings === "yes") {
      return strings[wpm_translator_params.default_language];
    }

    if (!!strings[language]) {
      return strings[language];
    }

    return '';
  }
};
