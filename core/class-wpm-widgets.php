<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Widgets
 * @package WPM\Core
 * @author   VaLeXaR
 */
class WPM_Widgets {

	/**
	 * WPM_Widgets constructor.
	 */
	public function __construct() {
		add_filter( 'widget_display_callback', 'wpm_translate_value', 0 );
	}
}
