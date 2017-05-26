=== WP Multilang ===

Contributors: valexar
Tags: localization, multilanguage, multilingual, translation, multilang
Requires at least: 4.7
Tested up to: 4.7
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Multilingual plugin for WordPress.

== Description ==

WP Multilang is a multilingual plugin for WordPress.

Translations of post types, taxonomies, meta fields, options, text fields in miltimedia files, menus, titles and text field in widgets.

Features of the plugin WP Multilang:

* Translation at PHP.
* Compatible with REST.
* Support configuration files.
* Support configuration for translate multidimensional arrays.
* No dublicate posts
* No subdomens for language version

WP Multilang compatible out of the box with the plugin:

* ACF, ACF Pro (ACF Pro supported in translating `object field`)
* WooCommerce
* Yoast Seo
* Contact Form 7

Supports configuration via json.

Add in the root of your theme or plugin file `wpm-config.json` settings.

Sample configurations can be viewed in a configuration file in the folder configs in the root plugin.

Configuration is updated after switching threads off/on or update any plugins.

It has filters for dynamic application configuration translation add languages.

To disable translation set `null` into the desired configuration.
For example, you must turn off translation for a post type `post`.
There are two ways:

1. After json.
    Create the root of the subject, or the roots of its plugin file wpm-config.json with:

    {
      "post_types": {
        "post": null
      }
    }

2. Through the filter.
    Add in functions.php

    add_filter ( 'wpm_posts_post_config', '__return_null');

To enable translation pass an empty array in php `array()` or empty object in json `{}`.
Supports translation multilevel array of options, custom fields
and post_content

Since localization files nucleus. Supports the removal of established localizations. Has the ability to add your own localizations.

Supports translation via GET parameter. Add in the GET parameter `lang` code desired language.

Supports clean database of translations when removing plugins.

Ideal for developers.

For a switch as add code to this topic

<?php if ( function_exists ( 'wpm_language_switcher' ) ) wpm_language_switcher (); ?>

Available features for translation:

wpm_translate_url ($url, $language = ''); // translate url
wpm_translate_string ($string, $language = ''); // translate multilingual string
wpm_translate_value ($value, $language = ''); // translate multidimensional array with multilingual strings

Standard translates all record types, taxonomies, custom fields. Even if you turn off translation for a particular type of account, you will only see its translation.

Supports automatically redirect to the user's browser language, if he went to the site for the first time.

Update translation occurs at PHP. Therefore plugin has high adaptability, compatibility and easily integrates with other plugins. This is what distinguishes it among similar.

Translation uses the following syntax:

[:en]Donec vitae orci sed dolor[:de]Cras risus ipsum faucibus ut[:]

Supports syntax qTranslate-X, WPGlobus, etc.

Compatible with REST-API.
Supports transfer the required translation through option `lang` in the GET request to REST.
Has the ability to keep recording the target language through the transmission parameter `lang` in the POST request.

Compatible with multisite not tested.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-multilang` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Adjust languages on General Options Page.

== Screenshots ==

1. Settings page
2. Post list page
3. Taxonomy list page
4. Taxonomy edit page
5. Post edit page

== Changelog ==

= 1.0.2 =
* fix save translation for enable languages without deleting translations for disable languages

= 1.0.1 =
* fix update options when deleting language
* fix set edit lang cookie

= 1.0 =
* Initial version.
