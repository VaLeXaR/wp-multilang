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
}
