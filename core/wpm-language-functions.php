<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use WPM\Core\WPM_Config;

function wpm_get_languages(){
	return WPM_Config::instance()->get_languages();
}


function wpm_get_user_language() {
	return WPM_Config::instance()->get_user_language();
}


function wpm_get_default_locale() {
	return WPM_Config::instance()->get_default_locale();
}


function wpm_get_settings() {
	return WPM_Config::instance()->get_settings();
}


function wpm_get_options() {
	return WPM_Config::instance()->get_options();
}


function wpm_get_installed_languages() {
	return WPM_Config::instance()->get_installed_languages();
}


function wpm_get_translations() {
	return WPM_Config::instance()->get_translations();
}


function wpm_installed_languages() {
	return WPM_Config::instance()->get_installed_languages();
}

function wpm_get_edit_lang() {
	$languages = wpm_get_languages();
	if ( is_admin() ) {
		$lang = isset( $_GET['edit_lang'] ) ? wpm_clean( $_GET['edit_lang'] ) : ( isset( $_COOKIE['edit_language'] ) ? wpm_clean( $_COOKIE['edit_language'] ) : $languages[ get_locale() ] );
	} else {
		$lang = $languages[ get_locale() ];
	}

	return $lang;
}
