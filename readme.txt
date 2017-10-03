=== WP Multilang ===

Contributors: valexar
Tags: localization, multilanguage, multilingual, translation, multilang
Requires at least: 4.7
Tested up to: 4.8.1
Stable tag: 1.7.3
Requires PHP: 5.6
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
* Support multisite
* Support WordPress in subfolder

WP Multilang compatible with plugins:

* ACF, ACF Pro
* WooCommerce
* Yoast Seo
* Contact Form 7 (added mail tag [_language] for send user lang in mail)
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
* BuddyPress
* Meta Slider
* TablePress
* WordPress MU Domain Mapping

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

    `add_filter ( 'wpm_post_post_config', '__return_null');`

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

`[:en]Donec vitae orci sed dolor[:de]Cras risus ipsum faucibus ut`

Supports syntax qTranslate, qTranslate-X, WPGlobus, etc.

Compatible with REST-API.
Supports transfer the required translation through option `lang` in the GET request to REST.
Has the ability to keep recording the target language through the transmission parameter `lang` in the POST request.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-multilang` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Adjust languages on General Options Page.

== Frequently Asked Questions ==

= I saved post and translation for language by default is disappeared. =

For saving post which do not translated never before, you need save post on default language. And only then on different language.

= I add new translation, but it rewrite another translation on different language. =

If you have opened several browser tabs for editing this post in different languages, translation will be saved for the language that you opened last.

== Screenshots ==

1. Settings page
2. Post list page
3. Taxonomy list page
4. Taxonomy edit page
5. Post edit page

== Changelog ==

= 1.7.3 =
+ add required param to locale input
+ optimized uninstall function
* fix set lang cookie for different sitepath
* fix update term and posts

= 1.7.2 =
+ add adding language from admin
+ load config from theme dynamically
+ add translating for WooCommerce shipping and payment default methods
* fix translating ACF
* fix translating in customizer
* fix delete translation during uninstall WP Multilang

= 1.7.1 =
* fix apply config filters

= 1.7.0 =
+ add support multisite
+ add support site in subfolders
+ add support WordPress MU Domain Mapping
+ add filters for widgets config
+ add mail tag [_language] for CF7
+ change syntax for translate. Now without last brackets.
* fix deleting translations when uninstalling plugin
* fix post filters config
* fix REST url
* fix compatibility with CF7

= 1.6.4 =
+ add dependence check ACF PRO from version
* fix ACF save field
* fix filter for post type config
* fix uninstalling function

= 1.6.3 =
* fix applying filter for post config

= 1.6.2 =
+ add translation for ACF(not PRO) field object
* fix error in ACF(not PRO)
* fix apply taxonomy config

= 1.6.1 =
+ add info notice if need use strings with ml syntax
* fix emails templates in Newsletter plugin

= 1.6.0 =
+ add support BuddyPress(translate emails, activity stream, custom user fields)
+ add support TablePress
+ add support Meta Slider
+ add filter for customizer url
+ add param 'lang' for use in post and term query
* small fixes

= 1.5.5 =
* fix update cart when switched language in WooCommerce
* add action on switched language

= 1.5.4 =
* fix save acf fields
* fix translate strings with zero
* fix save string with zero

= 1.5.3 =
* fix switch language url for ssl
* fix edit email in newsletter
* fix escaping string

= 1.5.2 =
+ add filters for escaping functions
+ add filter for translate attachment link

= 1.5.1 =
* fix save ml string for translate
* change priority for lang meta boxes
* fix error in JS if enabled one language
+ add support Gutenberg
+ add lang indicator for editing post

= 1.5.0 =
+ add language items in menu
+ add filters in vendor scripts
* fix language for AJAX requests in set user locale

= 1.4.12 =
* fix install language settings
* fix setup AJAX request if set user locale

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
