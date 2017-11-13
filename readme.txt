=== WP Multilang ===

Contributors: valexar
Tags: localization, multilanguage, multilingual, translation, multilang
Requires at least: 4.7
Tested up to: 4.9
Stable tag: 2.0.3
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
* Possibility set many languages with one localization. For example, for localization in the region.
* Possibility to set custom locale for html(If installed locale is en_US, you can set locale like: en, en-UK, en-AU etc. without installation another localization)
* Possibility for add new languages for any user with capability `manage options`
* Exist the role "Translator" for editing posts, terms. It can not publish or delete

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
* Download Monitor (Redefine templates for links in your theme and translate link texts)
* Better Search

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

For display language switcher in any place add code to your template

`if ( function_exists ( 'wpm_language_switcher' ) ) wpm_language_switcher ();`

Function accepts two parameters:

$args - array
  `type` - `list`, `dropdown`, `select`. Default - `list`.
  `show` - `flag`, `name`, `both`. Default - `both`.

$echo - bool

Available features for translation:

`wpm_translate_url ($url, $language = '');` - translate url
`wpm_translate_string ($string, $language = '');` - translate multilingual string
`wpm_translate_value ($value, $language = '');` - translate multidimensional array with multilingual strings

Supports automatically redirect to the user's browser language, if he visits for the first time.

Update translation occurs at PHP. Therefore plugin has high adaptability, compatibility and easily integrates with other plugins. This is what distinguishes it among similar.

Available translation html tags by JS for strings what do not have WP filters before output.

Add your tags in config:
`
"admin_html_tags": {
    "admin_screen_id": {
      "attribute": [
        "selector"
      ]
    }
}
`
Where:
`admin_screen_id` - admin screen id.
`attribute` - attribute what need to translate. Available `text` - for translate text node, `value` - for translate form values. Or other tag attribute, like `title`, `alt`.
`selector` - javascript selector for search needed tag. Each selector is a new array item.

Translation uses the syntax:

`[:en]Donec vitae orci sed dolor[:de]Cras risus ipsum faucibus ut`

Supports syntax qTranslate, qTranslate-X, WPGlobus, etc.

Compatible with REST-API.
Supports transfer the required translation through option `lang` in the GET request to REST.
Has the ability to keep recording the target language through the transmission parameter `lang` in the request.


== Warning ==
Not compatible with:
- WP Maintenance

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-multilang` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Adjust languages on WP Multilang Settings page.

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

= 2.0.3 =
+ add flag select autocomplete
+ add setting for set Yoast SEO Opengraph locale
+ add support WooCommerce attributes
+ add compatibility with Better Search
* fix add $_GET 'lang' param
* Other fixes and improvements

= 2.0.2 =
* fix separate posts by language

= 2.0.1 =
* fix set 'lang' GET param for home page
* fix redirect when exist GET 'lang' param
* translate url

= 2.0.0 =
+ remake logic for set language and translation
+ add possibility fo set custom locale
+ add possibility for set many languages for one localization
+ set new design for language settings
+ add option for redirect to browser language in first time
+ add option for use prefix for default language
+ add option for deleting unused language packs
+ admin localization use only installed languages
+ add user role `Translator`
+ Move WP Multilang settings to separate page
+ add config for Download Monitor
+ add config for new widgets in Page Builder by SiteOrigin
+ add filters for customizing language settings
+ add Background Updater
* fix separate posts by languages
* fix save title settings in Yoast SEO
* fix switch user language in admin dashboard

= 1.8.1 =
+ add filters for alternate links
+ add namespace for alternate links in Yoas SEO sitemap
* fix set alternate links
* fix error for getting date & time format for current language

= 1.8.0 =
+ added filters for flags directory for customization
+ added possibility for set own templates for language switcher
+ added wpm settings to WP REST settings endpoint
+ changed plugin text domain to 'wp-multilang'
+ change plugin structure
+ add 'any' post type to filter posts by lang
- remove uk localization from plugin. It available on wordpress.org
* fix switching language in customizer
* fix display date and time formats on settings page
* fix compatibility with latest version of Gutenberg
* fix 404 error for paged pages if main page is post archive
* fix translate preview links for custom posts
* fix translate media data for ajax
* fix translate url in admin
* fix translate page title in Yoast SEO

= 1.7.8 =
+ add possibility set time and date format for each language
+ add translation emails in Newsletter for each subscriber in his language
+ add translating post and term links in admin for preview
* fix uninstalling
* fix compatibility with Gutenberg 1.5.1

= 1.7.7 =
+ add translation for network name
* fix save posts with status 'auto-draft'
* fix switch language in customizer
* fix uninstalling
* fix set lang for ajax requests from admin
* fix compatibility with Gutenberg
* fix compatibility with Max MegaMenu
* fix translation Newsletter options

= 1.7.6 =
+ add check for alternate metalinks in head for separated term and posts
+ add separating posts and terms by language in Yoast Sitemap
+ added compatibility with Newsletter free extensions
* fix save terms translation

= 1.7.5 =
+ add links on other languages to Yoast Sitemap
+ add translating gallery widget from WP4.9
+ add dropdown language switcher type
* fix deleting cache on delete mata
* fix translate products shortcodes in WooCommerce
* fix translate items in cart in WooCommerce
* fix compatibility with Newsletter

= 1.7.4 =
+ add config for translate html tags by js
+ add class for current language to body
* fix NGG support
* fix add admin pages
* update flags
* fix translation post content
* fix config for any post type

= 1.7.3 =
+ add required param to locale input
+ optimized uninstall function
* fix set lang cookie for different sitepath
* fix update term and posts
* fix lang column for WC in admin

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
