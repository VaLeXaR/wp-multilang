<?php

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'acf' ) ) {

	class WPM_Acf {

		public function __construct() {
			add_filter( "acf/load_field", 'wpm_translate_value' );
			add_filter( "acf/translate_field_group", 'wpm_translate_string' );
			add_filter( "acf/update_field", array( $this, 'save_field' ) );
			add_filter( "acf/update_field/type=text", array( $this, 'save_text_field' ) );
			add_filter( "acf/update_field/type=textarea", array( $this, 'save_text_field' ) );
			add_filter( "acf/update_field/type=wysiwyg", array( $this, 'save_text_field' ) );
			add_filter( "acf/update_field/type=select", array( $this, 'save_select_field' ) );
			add_filter( "acf/update_field/type=checkbox", array( $this, 'save_select_field' ) );
		}


		public function save_field( $field ) {

			$old_field          = maybe_unserialize( get_post_field( 'post_content', $field['ID'] ) );
			$old_field          = wpm_value_to_ml_array( $old_field );
			$field_name         = get_post_field( 'post_title', $field['ID'] );
			$old_field['label'] = wpm_value_to_ml_array( $field_name );

			$default_config = array(
				'label'        => array(),
				'placeholder'  => array(),
				'prepend'      => array(),
				'append'       => array(),
				'instructions' => array()
			);

			$new_field = wpm_set_language_value( $old_field, $field, $default_config );
			$field     = wpm_array_merge_recursive( $field, $new_field );
			$field     = wpm_ml_value_to_string( $field );
			var_error_log( $field );

			return $field;
		}


		public function save_text_field( $field ) {

			$old_field = maybe_unserialize( get_post_field( 'post_content', $field['ID'] ) );
			$old_field = wpm_value_to_ml_array( $old_field );

			$default_config = array(
				'default_value' => array()
			);

			$field     = wpm_value_to_ml_array( $field );
			$new_field = wpm_set_language_value( $old_field, $field, $default_config );
			$field     = wpm_array_merge_recursive( $field, $new_field );
			$field     = wpm_ml_value_to_string( $field );

			return $field;
		}


		public function save_select_field( $field ) {

			$old_field = maybe_unserialize( get_post_field( 'post_content', $field['ID'] ) );
			$old_field = wpm_value_to_ml_array( $old_field );

			$default_config = array(
				'choices' => array(),
				'default_value' => array()
			);

			$field     = wpm_value_to_ml_array( $field );
			$new_field = wpm_set_language_value( $old_field, $field, $default_config );
			$field     = wpm_array_merge_recursive( $field, $new_field );
			$field     = wpm_ml_value_to_string( $field );

			return $field;
		}
	}

	new WPM_Acf();

}

//$field = apply_filters( "acf/update_field", $field);
//$field = apply_filters( "acf/update_field/type={$field['type']}", $field );
//$field = apply_filters( "acf/update_field/name={$field['name']}", $field );
//$field = apply_filters( "acf/update_field/key={$field['key']}", $field );
//$field = apply_filters( "acf/translate_field", $field );
//$field = apply_filters( "acf/translate_field/type={$field['type']}", $field );
//$fields = apply_filters('acf/get_fields', $fields, $parent);
//$field = apply_filters( "acf/load_field", $field);
//$field = apply_filters( "acf/load_field/type={$field['type']}", $field );
//$field = apply_filters( "acf/load_field/name={$field['name']}", $field );
//$field = apply_filters( "acf/load_field/key={$field['key']}", $field );
//$sub_field = apply_filters( "acf/get_sub_field", $sub_field, $selector, $field );
//$sub_field = apply_filters( "acf/get_sub_field/type={$field['type']}", $sub_field, $selector, $field );
//$post_id = apply_filters('acf/pre_save_post', $post_id, $GLOBALS['acf_form']);
//$value = apply_filters( "acf/load_value", $value, $post_id, $field );
//$value = apply_filters( "acf/load_value/type={$field['type']}", $value, $post_id, $field );
//$value = apply_filters( "acf/load_value/name={$field['_name']}", $value, $post_id, $field );
//$value = apply_filters( "acf/load_value/key={$field['key']}", $value, $post_id, $field );
//$value = apply_filters( "acf/format_value", $value, $post_id, $field );
//$value = apply_filters( "acf/format_value/type={$field['type']}", $value, $post_id, $field );
//$value = apply_filters( "acf/format_value/name={$field['_name']}", $value, $post_id, $field );
//$value = apply_filters( "acf/format_value/key={$field['key']}", $value, $post_id, $field );
//$value = apply_filters( "acf/update_value", $value, $post_id, $field );
//$value = apply_filters( "acf/update_value/type={$field['type']}", $value, $post_id, $field );
//$value = apply_filters( "acf/update_value/name={$field['name']}", $value, $post_id, $field );
//$value = apply_filters( "acf/update_value/key={$field['key']}", $value, $post_id, $field );
//$value = apply_filters( "acf/preview_value", $value, $post_id, $field );
//$value = apply_filters( "acf/preview_value/type={$field['type']}", $value, $post_id, $field );
//$value = apply_filters( "acf/preview_value/name={$field['_name']}", $value, $post_id, $field );
//$value = apply_filters( "acf/preview_value/key={$field['key']}", $value, $post_id, $field );
//$field_group = apply_filters('acf/get_field_group', $field_group);
//$label = apply_filters("acf/get_field_label", $label, $field);
