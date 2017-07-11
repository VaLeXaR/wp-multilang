=== WP Multilang ===

Contributors: valexar
Tags: localization, multilanguage, multilingual, translation, multilang
Requires at least: 4.7
Tested up to: 4.8
Stable tag: 1.4.11
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Multilingual plugin for WordPress.

== Description ==

WP Multilang is a multilingual plugin for WordPress.

Translations of post types, taxonomies, meta fields, options, text fields in miltimedia files, menus, titles and text fields in widgets.

Features of the plugin WP Multilang:

* 100% free.
* Translation at PHP.
* Compatible with REST.
* Support configuration files.
* Support configuration for translate multidimensional arrays.
* Separate menu items by language
* Separate widgets by language
* Separate posts by language
* Many filters for dynamic applying translation settings
* No duplicate posts
* No subdomains for language version

WP Multilang compatible with plugins:

* ACF, ACF Pro (in ACF Pro support translate `object field`)
* WooCommerce
* Yoast Seo
* Contact Form 7
* WPBakery Visual Composer
* Page Builder by SiteOrigin
* NextGEN Gallery
* All in One SEO Pack
* MailChimp for WordPress
* Newsletter
* Maps Builder
* Max Mega Menu
* MasterSlider
* WP-PageNavi

Supports configuration via json.

Add in the root of your theme or plugin file `wpm-config.json`.

Sample configurations can be viewed in config files in folder 'configs' in root the plugin.

Configuration is updated after switching theme, enable or update any plugins.

The plugin has filters for dynamic application configuration for translate.

For turn off translation, set `null` into the desired configuration.
For example, you must turn off translation for a post type `post`.
There are two ways:

1. In json.
    Create in root of a theme or a plugin file `wpm-config.json` with:

    `{
       "post_types": {
         "post": null
       }
     }`


2. Through the filter.
    Add in functions.php

    `add_filter ( 'wpm_posts_post_config', '__return_null');`

To enable translation pass an empty array in php `array()` or empty object in json `{}`.
Supports translation multilevel array of options, custom fields and post_content.

Supports the removal of established localizations. Has the ability to add your own localizations.

Supports translation via GET parameter. Add in the GET parameter `lang` code desired language.

Supports clean database of translations when removing the plugin.

Ideal for developers.

For a switch as add code to your template

`if ( function_exists ( 'wpm_language_switcher' ) ) wpm_language_switcher ();`

Function accepts two parameters:

$args - array
  'type' - 'list', 'dropdown'. Default - 'list'.
  'show' - 'flag', 'name', 'both'. Default - 'both'.

$echo - bool

Available features for translation:

`wpm_translate_url ($url, $language = '');` - translate url
`wpm_translate_string ($string, $language = '');` - translate multilingual string
`wpm_translate_value ($value, $language = '');` - translate multidimensional array with multilingual strings

Standard translates all record types, taxonomies, custom fields. Even if you turn off translation for a particular post type, display only translated text.

Supports automatically redirect to the user's browser language, if he visits for the first time.

Update translation occurs at PHP. Therefore plugin has high adaptability, compatibility and easily integrates with other plugins. This is what distinguishes it among similar.

Translation uses the following syntax:

`[:en]Donec vitae orci sed dolor[:de]Cras risus ipsum faucibus ut[:]`

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

= 1.4.11 =
+ add support editing by REST-API
+ add blank for support Gutenberg Editor
* fix set language by AJAX request

= 1.4.10 =
+ add filer by config for separating posts
+ add separating settings for taxonomies
+ add checking set user locale
* fix disabling for separating by language in post

= 1.4.9 =
* add 'lang' url param to iframe url in Customizer
* fix query for separating posts

= 1.4.8 =
* fix redirect in REST

= 1.4.7 =
* fix set lang in main query

= 1.4.6 =
+ add separating settings for menu item
+ add separating settings for widgets
+ add separating settings for posts
* fixes in saving translation

= 1.4.5 =
+ add translation for custom internal menu link
* fix admin styles
* fix ACF check

= 1.4.4 =
+ add support all fields in ACF
* fix translate function in js

= 1.4.3 =
* fix acf save value
+ add filter for translate acf field by config

= 1.4.2 =
* fix translate user meta

= 1.4.1 =
+ add mobile styles in admin for language switcher
* fix save acf option field

= 1.4.0 =
+ add support comments and user fields
+ add support ACF widget fields
+ add check for ACF fields. Field translate only if object type is in config. Support all object types.
+ add filter for disable load vendor scripts
+ add check for meta fields
* add filter for disable ACF field
* fix redirect for disable browser cookie

= 1.3.8 =
* fix WP-CLI error
* removed unnecessary variables

= 1.3.7 =
* add option for snow not translated texts
* add support WP-PageNavi
* fix load plugins localizations
* fix empty menu title
* fix translation new term
* fix update widgets

= 1.3.6 =
* fix load main static page

= 1.3.5 =
* hide wp language settings
* fix load theme localization files

= 1.3.4 =
* add 404 error for not available language
* fix translate option in init

= 1.3.3 =
* fix setting for default site language
* fix redirect to browser language

= 1.3.2 =
* add setting for default site language

= 1.3.1 =
* fix many redirect for first load

= 1.3.0 =
+ add support Max Mega Menu
+ add support MasterSlider

= 1.2.1 =
* fix save ACF value

= 1.2.0 =
+ add support Page Builder by SiteOrigin
+ add support NextGEN Gallery
+ add support All in One SEO Pack
+ add support MailChimp for WordPress
+ add support Newsletter
+ add support Maps Builder
+ add filters for config
* small fixes

= 1.1.2 =
* fix view image

= 1.1.1 =
* change load vendor action
* fix saving empty meta for Yoast SEO

= 1.1.0 =
+ add support media_image widget from WP4.8
+ add support WPBakery Visual Composer

= 1.0.2 =
* fix save translation for enable languages without deleting translations for disable languages

= 1.0.1 =
* fix update options when deleting language
* fix set edit lang cookie

= 1.0 =
* Initial version.
