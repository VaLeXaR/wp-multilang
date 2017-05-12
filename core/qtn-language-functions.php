<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


function qtn_get_languages(){
	return QN()->config->get_languages();
}


function qtn_get_user_language() {
	return QN()->config->get_user_language();
}


function qtn_get_default_locale() {
	return QN()->config->get_default_locale();
}


function qtn_get_settings() {
	return QN()->config->get_settings();
}


function qtn_get_options() {
	return QN()->config->get_options();
}


function qtn_get_installed_languages() {
	return QN()->config->get_installed_languages();
}


function qtn_get_translations() {
	return QN()->config->get_translations();
}


function qtn_installed_languages() {
	return QN()->config->get_installed_languages();
}
