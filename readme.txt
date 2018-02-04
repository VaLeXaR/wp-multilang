=== WP Multilang ===

Contributors: valexar
Tags: localization, multilanguage, multilingual, translation, multilang
Requires at least: 4.7
Tested up to: 4.9
Stable tag: 2.1.12
Requires PHP: 5.6+
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Multilingual plugin for WordPress.

== Description ==

WP Multilang is a multilingual plugin for WordPress.

Translations of post types, taxonomies, meta fields, options, text fields in miltimedia files, menus, titles and text fields in widgets.

== Features of the plugin WP Multilang ==

* 100% free.
* Translation at PHP.
* Compatible with REST.
* Support configuration for translate multidimensional arrays in options, meta fields, post content.
* Support multisite.
* Support WordPress in sub-folder.
* Separate menu items, posts, terms, widgets, comments per language.
* Many filters for dynamic applying translation settings.
* No duplicate posts, terms, menus, widgets.
* No sub-domain for each language version.
* No additional tables in database.
* Possibility set many languages with one localization. For example, for localization in the region.
* Possibility to set custom locale for html(If installed locale is en_US, you can set locale like: en, en-UK, en-AU etc. Without installation another localization)
* Possibility for add new languages for any user with capability `manage_options`.
* Exist the role "Translator" for editing posts, terms. It can not publish or delete.
* No limits by languages or by possibilities.

== WP Multilang compatible with plugins ==

* ACF, ACF Pro
* WooCommerce
* WooCommerce Customizer
* Gutenberg
* Yoast Seo
* Contact Form 7 (added mail tag [_language] for send user language in mail)
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

Manage translation settings via json.

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
Supports translation multidimensional array of options, meta fields and post_content.

Supports the removal of established localizations.

Supports translation via GET parameter. Add in the GET parameter `lang` code desired language.

Supports clean database of translations when removing the plugin. Translations are only removed from the built-in tables.

Supports import term translations from qTranslate(by Soft79).

Ideal for developers.

For display language switcher in any place add the code to your template

`if ( function_exists ( 'wpm_language_switcher' ) ) wpm_language_switcher ();`

Function accepts two parameters:
$type - 'list', 'dropdown', 'select'. Default - 'list'.
$show - 'flag', 'name', 'both'. Default - 'both'.

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
`attribute` - attribute what need to translate. Available 'text' - for translate text node, 'value' - for translate form values. Or other tag attribute, like 'title', 'alt'.
`selector` - css selector for search needed tag. Each selector is a new array item.

For set translation uses the syntax:

`[:en]Donec vitae orci sed dolor[:de]Cras risus ipsum faucibus ut[:]`

Supports translating from syntax qTranslate, qTranslate-X, WPGlobus etc.

Compatible with REST-API.
Supports transfer the required translation through option `lang` in the GET request to REST.
Has the ability to keep recording the target language through the transmission parameter `lang` in the request.

== Migration from qTranslate-X ==

1. Before installing/uninstalling, make database backup.
2. Deactivate qTranslate-X.
3. Install and activate WP Multilang.
4. Create in root of your theme file ‘wpm-config.json’.
5. Add all needed post types, taxonomies, options, fields to ‘wpm-config.json’. Setting from qTranslate-X not importing.
6. Import term names from qTranslate.
7. Check that everything is okay.
8. If everything is okay, remove qTranslate-X. If not, make screenshots of errors, restore database from backup and add support issue with your screenshots and description of errors.

== Warning ==

Do not support different slug for each language(Yet).

Not compatible with:
- WP Maintenance

== Known issues ==

Function 'get_page_by_title' not working, because in title field are stored titles for all languages. Use function 'wpm_get_page_by_title( $title )' as solution.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-multilang` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Adjust languages on WP Multilang Settings page.

== Upgrade Notice ==

Before installing or uninstalling make the site database backup before.

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

= 2.1.12 =
* add translation object data for REST request in WC
* fix compatibility with SiteOrigin
* fix translate queried object
* fix set language value
* fix JS translator

= 2.1.11 =
* fixed filters priority on removing in ACF integration

= 2.1.9 =
* added WC setting 'woocommerce_bacs_accounts' in translate config
* added support region languages
* fixed set new translate notice
* fixed translate ACF groups
* fixed MegaMenu compatibility
* fixed product duplication in WC
* fixed get_term for taxonomy hierarchy query
* fixed check for translate posts
* fixed Gutenberg compatibility

= 2.1.8 =
* added types for language switcher in menu
* removed current language link from language switcher(for SEO)
* fixed ACF integration
* added check for merge new value in translating value
* fix css rules for .wrap (tnx @Soft79)

= 2.1.7 =
* fix Siteorigin integration
* add filters in add, update and get functions for meta fields

= 2.1.6 =
* fix deleting

= 2.1.5 =
* add language tag indicator
* add separating comments per language
* fix many redirect on home page when using ssl and lang param in url
* fix updating errors

= 2.1.4 =
* change syntax for more accurate search in database
* fix support Gutenberg
* fix translating post and term fields fields
* fix translating html widget
* fix add new taxonomy

All changelog available on [GitHub](https://github.com/VaLeXaR/wp-multilang/releases).
