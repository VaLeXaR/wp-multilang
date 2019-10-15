<?php
namespace WPM\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * ShortCodes
 *
 * Class WPM_Shortcodes
 * @package WPM\Includes
 */
class WPM_Shortcodes {

	/**
	 * WPM_Shortcodes constructor.
	 */
	public function __construct() {
		add_shortcode( 'wpm_lang_switcher', array( $this, 'language_switcher' ) );
		add_shortcode( 'wpm_translate', array( $this,'translate_via_shortcode' ));
	}

	/**
	 * Language switcher html
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	public function language_switcher( $atts ) {

		$atts = shortcode_atts( array(
			'type' => 'list',
			'show' => 'both'
		), $atts );

		return wpm_get_language_switcher( $atts['type'], $atts['show'] );
	}

	/**
	 * Shorcode for using with uncompatible plugins
	 *
	 * @param array $atts
	 * @param string $content
	 *
	 * @return string
	 */

	public function translate_via_shortcode( $atts, $content ) {
		$atts = shortcode_atts( array(
			'lang' => wpm_get_language()
		), $atts );

		return wpm_translate_string($content, $atts['lang']);
	}
}
