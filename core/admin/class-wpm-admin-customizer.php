<?php
/**
 * Created by PhpStorm.
 * User: VaLeXaR
 * Date: 04.07.2017
 * Time: 16:58
 */


class WPM_Admin_Customizer {

	public function __construct() {
		add_action( 'customize_preview_init', array( $this, 'preview_override_loader') );
	}


	public function preview_override_loader(){

	}
}
