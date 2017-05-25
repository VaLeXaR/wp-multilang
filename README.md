# WP Multilang

Multilingual plugin for WordPress.

## == Description ==

#### English

**WP Multilang** is a multilingual plugin for WordPress.

Translations of post types, taxonomies, meta fields, options, text fields in miltimedia files, menus, titles and text field in widgets.

**WP Multilang** compatible out of the box with the plugin:
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
    ```
    {
      "post_types": {
        "post": null
      }
    }
    ```

2. Through the filter.   
    Add in functions.php
    ```php
    add_filter ( 'wpm_posts_post_config', '__return_null');
    ```
To enable translation pass an empty array in php `array()` or empty object in json `{}`.
Supports translation multilevel array of options, custom fields
and post_content

Since localization files nucleus. Supports the removal of established localizations. Has the ability to add your own localizations.

Supports translation via GET parameter. Add in the GET parameter `lang` code desired language.

Supports clean database of translations when removing plugins.

Ideal for developers.

For a switch as add code to this topic
```php
<?php if ( function_exists ( 'wpm_language_switcher' ) ) wpm_language_switcher (); ?>
```
Available features for translation:
```php
wpm_translate_url ($url, $language = ''); // translate url
wpm_translate_string ($string, $language = ''); // translate multilingual string
wpm_translate_value ($value, $language = ''); // translate multidimensional array with multilingual strings   
```
Standard translates all record types, taxonomies, custom fields. Even if you turn off translation for a particular type of account, you will only see its translation.

Supports automatically redirect to the user's browser language, if he went to the site for the first time.

Update translation occurs at PHP. Therefore plugin has high adaptability, compatibility and easily integrates with other plugins. This is what distinguishes it among similar.

Translation uses the following syntax:
```
[:en]Donec vitae orci sed dolor[:de]Cras risus ipsum faucibus ut[:]
```
Supports syntax qTranslate-X, WPGlobus, etc.

Compatible with REST-API.   
Supports transfer the required translation through option `lang` in the GET request to REST.   
Has the ability to keep recording the target language through the transmission parameter `lang` in the POST request.

Compatible with multisite not tested.

Features of the plugin **WP Multilang**:
* Translation at PHP.
* Compatible with REST.
* Support configuration files.
* Support configuration for translate multidimensional arrays.
* No dublicate posts
* No subdomens for language version

#### Українська

**WP Multilang** це плаґін багатомовності для WordPress.

Доступний переклад текстових полів мільтімедіа файлів, меню, заголовків та текстів віджетів.

**WP Multilang** сумісний з коробки з плаґінами:
* ACF, ACF Pro (у ACF Pro підтримується переклад `object field`)
* WooCommerce
* Yoast Seo
* Contact Form 7

Підтримує налаштування через json.

Додайте у корінь своєї теми або плаґіна файл wpm-config.json з налаштуваннями.

Приклади конфігурацій можна подивитися у файлах конфігурацій в теці configs у корені плаґіна.

Конфігурація оновлюється після перемикання теми, вимкнення/увімкнення/оновлення будь-якого плаґіна.

Має фільтри для динамічного застосування конфігурації перекладу, додавання мов.

Для вимкнення перекладу передайте `null` у потрібну конфігурацію.
Наприклад, потрібно вимкнути переклад для типу запису `post`.

Для цього є два шляхи:
   
1. Через json.   
    Створіть у корені своєї теми, або у корені свого плаґіна файл wpm-config.json з такими даними:
    ```
    {
      "post_types": {
        "post": null
      }
    }
    ```
2. Через фільтр.   
    Додайте у functions.php
    ```
    add_filter('wpm_posts_post_config', '__return_null');
    ```
Для увімкнення перекладу передайте пустий масив у PHP або пустий об'єкт у json `{}`.

Підтримує переклад багаторівневих масивів опцій,  користувацьких полів
та post_content

Працює з файлами локалізації ядра. Підтримує видалення, встановлених локалізацій. Має можливість додавати власні локалізації.

Підтримує переклад через GET параметр. Додайте у  GET параметр `lang` з кодом потрібної мови.

Підтримує очищення бази даних від перекладів при видаленні плаґіна.

Ідеально підходить для розробників.
   
Для виводу перемикача мов додайте код у тему
```php
<?php if ( function_exists( 'wpm_language_switcher' ) ) wpm_language_switcher(); ?>
```
Доступні функції для перекладу:
```php
wpm_translate_url( $url, $language = '' ); // translate url
wpm_translate_string( $string, $language = '' ); // translate multilingual string
wpm_translate_value( $value, $language = '' ); // translate multidimensional array with multilingual strings
```
Стандартно перекладає всі типи записів, таксономій, користувацьких полів. Навіть якщо ви вимкнете переклад для певного типу запису, ви будете бачити лише його переклад.

Підтримує автоматичну переадресацію на мову браузера користувача, якщо він зайшов на сайт вперше.

Оновлення перекладів відбувається на рівні PHP. Тому плаґін має високу адаптивність, сумісність та легко інтегрується з іншими плаґінами. Саме це вирізняє його серед подібних.

Використовує такий синтаксис перекладу:
```
[:en]Donec vitae orci sed dolor[:de]Cras risus ipsum faucibus ut[:]
```
Підтримує синтаксису qTranslate-X, WPGlobus та подібних.

*Сумісний з REST-API.*   
Підтримує передачу потрібного перекладу через параметр `lang` у GET запиті до REST.   
Має можливість зберігати запис потрібною мовою через передачу параметру `lang` у POST запиті.

Сумісність з багатосайтовістю не тестувалася.

Особливості плаґіна *WP Multilang*:
* Переклад на рівні PHP.
* Сумісність з REST.
* Підтримка файлів конфігурації.
* Підтримка перекладів багаторівневих масивів налаштувань.
* Без дублювання записів
* Без субдоменів для мовних версій
