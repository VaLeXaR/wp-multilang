<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPM_Widgets {

	public function __construct() {
		add_filter( 'widget_display_callback', 'wpm_translate_value', 0 );
	}
}
