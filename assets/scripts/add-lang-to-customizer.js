/**
 * Code from https://github.com/xwp/wp-customizer-blank-slate
 *
 * Learn more at: https://make.xwp.co/2016/09/11/resetting-the-customizer-to-a-blank-slate/
 * Copyright (c) 2016 XWP (https://make.xwp.co/)
 */
/* global wp, jQuery */
/* exported PluginCustomizer */
var WPMLang = (function (api, $) {
  'use strict';

  var component = {
    data: {
      url: null
    }
  };

  /**
   * Initialize functionality.
   *
   * @param {object} home Args.
   * @param {string} home.url  Preview URL.
   * @returns {void}
   */
  component.init = function init(home) {
    _.extend(component.data, home);
    if (!home || !home.url) {
      throw new Error('Missing args');
    }

    api.bind('ready', function () {
      api.previewer.previewUrl.set(home.url);
    });
  };
  return component;
}(wp.customize, jQuery) );
