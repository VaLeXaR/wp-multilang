<?php
/**
 * Class for capability with BuddyPress
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_BuddyPress
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_BuddyPress {

	/**
	 * @var object BP_XProfile_Group
	 */
	private $field_group;

	/**
	 * @var object BP_XProfile_Field
	 */
	private $field;

	/**
	 * WPM_BuddyPress constructor.
	 */
	public function __construct() {
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'change_url_customizer_previewer' ) );
		add_action( 'add_meta_boxes', array( $this, 'remove_post_custom_fields' ), 40 );
		add_action( 'init', array( $this, 'set_user_lang' ) );
		add_action( 'wpm_changed_language', array( $this, 'set_user_lang_on_change' ) );
		add_action( 'bp_send_email', array( $this, 'translate_email' ), 10, 3 );
		add_filter( 'bp_activity_get_meta', array( $this, 'translate_meta_value' ) );
		add_filter( 'bp_get_activity_content_body', 'wpm_translate_string' );
		add_filter( 'bp_get_the_profile_group_name', 'wpm_translate_string' );
		add_filter( 'bp_get_the_profile_group_description', 'wpm_translate_string' );
		add_filter( 'bp_get_the_profile_field_name', 'wpm_translate_string' );
		add_filter( 'bp_get_the_profile_field_description', 'wpm_translate_string' );
		add_filter( 'xprofile_group_name_before_save', array( $this, 'save_group_name' ), 10, 2 );
		add_filter( 'xprofile_group_description_before_save', array( $this, 'save_group_description' ), 10, 2 );
		add_filter( 'xprofile_field_name_before_save', array( $this, 'save_field_name' ), 10, 2 );
		add_filter( 'xprofile_field_description_before_save', array( $this, 'save_field_description' ), 10, 2 );
		add_filter( 'bp_xprofile_field_get_children', array( $this, 'remove_filter' ) );
		add_action( 'bp_xprofile_admin_new_field_additional_settings', 'wpm_show_notice' );
	}

	/**
	 * Action for change customizer url
	 */
	public function change_url_customizer_previewer() {

		if ( bp_is_email_customizer() ) {
			add_filter( 'wpm_customizer_url', function () {
				return rawurldecode( wpm_clean( $_GET['url'] ) );
			} );
		}
	}

	/**
	 * Remove metabox for post type language
	 */
	public function remove_post_custom_fields() {
		remove_meta_box( 'wpm-bp-email-languages', 'bp-email', 'side' );
	}

	/**
	 * Set user lang for emails
	 */
	public function set_user_lang() {
		$user_id   = get_current_user_id();
		$user_lang = get_user_meta( $user_id, 'wpm_lang', true );
		$site_lang = wpm_get_language();


		if ( ! $user_lang ) {
			update_user_meta( $user_id, 'wpm_lang', $site_lang );
		}
	}


	/**
	 * Set user lang on change
	 */
	public function set_user_lang_on_change() {
		update_user_meta( get_current_user_id(), 'wpm_lang', wpm_get_language() );
	}


	/**
	 * Send email for user on his language
	 *
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


	/**
	 * Translate activity meta value
	 *
	 * @param $value
	 *
	 * @return array|mixed|string
	 */
	public function translate_meta_value( $value ) {
		if ( ! is_admin() ) {
			$value = wpm_translate_value( $value );
		}

		return $value;
	}


	/**
	 * Untranslate field group name and set new lang value before saving
	 *
	 * @param string  $name
	 * @param integer $field_group_id
	 *
	 * @return string
	 */
	public function save_group_name( $name, $field_group_id ) {

		if ( ! $this->field_group ) {
			$this->field_group = xprofile_get_field_group( $field_group_id );
		}

		$name = wpm_set_new_value( $this->field_group->name, $name );

		return $name;
	}


	/**
	 * Untranslate field group description and set new lang value before saving
	 *
	 * @param string $description
	 *
	 * @return string
	 */
	public function save_group_description( $description ) {
		$description = wpm_set_new_value( $this->field_group->description, $description );

		return $description;
	}


	/**
	 * Untranslate field name and set new lang value before saving
	 *
	 * @param string  $name
	 * @param integer $field_id
	 *
	 * @return string
	 */
	public function save_field_name( $name, $field_id ) {

		if ( ! $this->field ) {
			$this->field = xprofile_get_field( $field_id );
		}

		$name = wpm_set_new_value( $this->field->name, $name );

		return $name;
	}


	/**
	 * Untranslate field description and set new lang value before saving
	 *
	 * @param string $description
	 *
	 * @return string
	 */
	public function save_field_description( $description ) {
		$description = wpm_set_new_value( $this->field->description, $description );

		return $description;
	}

	/**
	 * Remove translate filter before display options in admin
	 *
	 * @param array $children
	 *
	 * @return array
	 */
	public function remove_filter( $children ) {
		remove_filter( 'attribute_escape', 'wpm_attribute_escape', 5 );

		return $children;
	}
}
