<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use WPM\Core\WPM_Setup;

function wpm_get_languages(){
	return WPM_Setup::instance()->get_languages();
}


function wpm_get_user_language() {
	return WPM_Setup::instance()->get_user_language();
}


function wpm_get_default_locale() {
	return WPM_Setup::instance()->get_default_locale();
}


function wpm_get_config() {
	return WPM_Setup::instance()->get_config();
}


function wpm_get_options() {
	return WPM_Setup::instance()->get_options();
}


function wpm_get_installed_languages() {
	return WPM_Setup::instance()->get_installed_languages();
}


function wpm_get_translations() {
	return WPM_Setup::instance()->get_translations();
}

function wpm_get_language() {
	if ( is_admin() ) {
		$lang = isset( $_GET['edit_lang'] ) ? wpm_clean( $_GET['edit_lang'] ) : ( isset( $_COOKIE['edit_language'] ) ? wpm_clean( $_COOKIE['edit_language'] ) : wpm_get_user_language() );
	} else {
		$lang = wpm_get_user_language();
	}

	return $lang;
}


function wpm_get_current_url() {
	$url = $_SERVER['REQUEST_SCHEME'] . '://' .  $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	return $url;
}
