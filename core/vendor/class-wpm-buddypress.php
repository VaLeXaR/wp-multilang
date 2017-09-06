<?php
/**
 * Class for capability with BuddyPress
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BuddyPress' ) ) {
	return;
}

/**
 * Class WPM_BuddyPress
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 */
class WPM_BuddyPress {

	/**
	 * WPM_BuddyPress constructor.
	 */
	public function __construct() {
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'change_url_customizer_previewer' ) );
		add_action( 'add_meta_boxes', array( $this, 'remove_post_custom_fields' ), 40 );
		add_action( 'init', array( $this, 'set_user_lang' ) );
		add_action( 'wpm_changed_language', array( $this, 'set_user_lang_on_change' ) );
		add_action( 'bp_send_email', array( $this, 'translate_email' ), 10, 3 );
		add_filter( 'bp_activity_get_meta', array($this, 'translate_meta_value') );
		add_filter( 'bp_get_activity_content_body', 'wpm_translate_string' );
	}

	public function change_url_customizer_previewer() {

		if ( bp_is_email_customizer() ) {
			add_filter( 'wpm_customizer_url', function () {
				return rawurldecode( wpm_clean( $_GET['url'] ) );
			} );
		}
	}


	public function remove_post_custom_fields() {
		remove_meta_box( 'wpm-bp-email-languages', 'bp-email', 'side' );
	}


	public function set_user_lang() {
		$user_id   = get_current_user_id();
		$user_lang = get_user_meta( $user_id, 'wpm_lang', true );
		$site_lang = wpm_get_language();


		if ( ! $user_lang ) {
			update_user_meta( $user_id, 'wpm_lang', $site_lang );
		}
	}


	public function set_user_lang_on_change() {
		update_user_meta( get_current_user_id(), 'wpm_lang', wpm_get_language() );
	}


	public function translate_meta_value( $value ) {
		if ( ! is_admin() ) {
			$value = wpm_translate_value( $value );
		}

		return $value;
	}

	/**
	 * @param $email object BP_Email
	 * @param $email_type
	 * @param $to
	 *
	 * @return mixed
	 */
	public function translate_email( $email, $email_type, $to ) {
		$post              = $email->get_post_object();
		$untranslated_post = wpm_untranslate_post( $post );
		$lang              = get_user_meta( $to, 'wpm_lang', true );
		$translated_post   = wpm_translate_object( $untranslated_post, $lang );
		$email->set_post_object( $translated_post );

		return $email;
	}
}

new WPM_BuddyPress();
