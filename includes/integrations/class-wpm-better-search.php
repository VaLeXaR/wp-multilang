<?php
/**
* Class for capability with Better Search
*/

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
* @class    WPM_Better_Search
* @package  WPM/Includes/Integrations
* @category Integrations
*/
class WPM_Better_Search {

	/**
	 * WPM_Better_Search constructor.
	 */
	public function __construct() {
		add_filter( 'get_bsearch_excerpt', array( $this, 'translate_excerpt' ), 10, 4 );
	}


	public function translate_excerpt( $output, $id, $excerpt_length, $use_excerpt ) {
		$content = $excerpt = '';
		if ( $use_excerpt ) {
			$content = get_post( $id )->post_excerpt;
		}
		if ( '' == $content ) {
			$content = get_post( $id )->post_content;
		}

		$content = wpm_translate_string( $content );
		$output  = strip_tags( strip_shortcodes( $content ) );

		if ( $excerpt_length > 0 ) {
			$output = wp_trim_words( $output, $excerpt_length );
		}

		return $output;
	}
}
