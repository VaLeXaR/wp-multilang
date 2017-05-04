<?php
/**
 * Plugin Name:     qTranslate-Next
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Valentyn Riaboshtan
 * Author URI:      YOUR SITE HERE
 * Text Domain:     qtranslate-next
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package  Qtranslate_Next
 * @category Core
 * @author   Valentyn Riaboshtan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/lib/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

use QtNext\Core\Qtranslate_Next;

/*add_filter('posts_where', function ($where, $query) {
	if (isset($query->query)) {
		global $wpdb;
		$lang = $query->query['lang'];
		$like = '%{:' . $wpdb->esc_like($lang) . '}%';
		$where .= $wpdb->prepare( " AND ({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_content LIKE %s)", $like, $like );
	}
	return $where;
}, 10, 2);*/

add_action('wp_head', function(){
	echo '<link rel="alternate" hreflang="%s" href="%s"/>';
});

function QN() {
	return Qtranslate_Next::instance();
}

QN();
