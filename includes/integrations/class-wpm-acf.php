<?php
/**
 * Class for capability with Advanced Custom Fields Plugin
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class    WPM_Acf
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_Acf {

	/**
	 * WPM_Acf constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_filters' ), 5 );
		add_filter( 'acf/load_field', 'wpm_translate_value', 6 );
		add_filter( 'acf/load_value', 'wpm_translate_value', 6 );
		add_filter( 'wpm_acf_field_text_config', array( $this, 'add_text_field_config' ) );
		add_filter( 'wpm_acf_field_textarea_config', array( $this, 'add_text_field_config' ) );
		add_filter( 'wpm_acf_field_wysiwyg_config', array( $this, 'add_text_field_config' ) );
		add_filter( 'wpm_acf_text_config', '__return_empty_array' );
		add_filter( 'wpm_acf_textarea_config', '__return_empty_array' );
		add_filter( 'wpm_acf_wysiwyg_config', '__return_empty_array' );
	}

	/**
	 * Init filters for different versions
	 */
	public function init_filters() {
		if ( version_compare( acf()->settings['version'], 5, 'ge' ) ) {
			add_filter( 'wpm_post_acf-field-group_config', array( $this, 'add_config' ) );
			add_filter( 'acf/get_field_group', 'wpm_translate_value', 6 );
			add_filter( 'acf/get_field_label', 'wpm_translate_string', 6 );
			add_filter( 'acf/update_field', array( $this, 'update_field_pro' ), 99 );
			add_filter( 'acf/update_value', array( $this, 'update_value_pro' ), 99, 3 );
		} else {
			add_filter( 'wpm_post_acf_config', array( $this, 'add_config' ) );
			add_filter( 'acf/field_group/get_fields', 'wpm_translate_value', 6 );
			remove_class_action( 'acf/update_field', 'acf_field_functions', 'update_field', 5 );
			add_action( 'acf/update_field', array( $this, 'update_field' ), 5, 2 );
			remove_class_action( 'acf/update_value', 'acf_field_functions', 'update_value', 5 );
			add_action( 'acf/update_value', array( $this, 'update_value' ), 5, 3 );
			add_filter( 'attribute_escape', array( $this, 'translate_value' ), 5 );
			add_filter( 'esc_textarea', array( $this, 'translate_value' ), 5 );
			add_filter( 'acf_the_editor_content', 'wpm_translate_value', 5 );
		}
	}


	/**
	 * Add config for 'acf' post types
	 *
	 * @param $config
	 *
	 * @return mixed
	 */
	public function add_config( $config ) {

		if ( ! isset( $_GET['page'] ) ) {
			$config = array(
				'post_content' => null,
				'post_excerpt' => null,
			);
		}

		return $config;
	}


	/**
	 * Save field object with translation for Pro.
	 *
	 * @param $field
	 *
	 * @return array|bool|string
	 */
	public function update_field_pro( $field ) {

		$old_field = maybe_unserialize( get_post_field( 'post_content', $field['ID'], 'edit' ) );

		if ( ! $old_field ) {
			return $field;
		}

		$old_field          = wpm_array_merge_recursive( $field, $old_field );
		$old_field          = wpm_value_to_ml_array( $old_field );
		$field_name         = get_post_field( 'post_title', $field['ID'], 'edit' );
		$old_field['label'] = wpm_value_to_ml_array( $field_name );

		$default_config = array(
			'label'        => array(),
			'placeholder'  => array(),
			'instructions' => array(),
		);

		$acf_field_config = apply_filters( "wpm_acf_field_{$field['type']}_config", $default_config );

		$new_field = wpm_set_language_value( $old_field, $field, $acf_field_config );
		$field     = wpm_array_merge_recursive( $field, $new_field );
		$field     = wpm_ml_value_to_string( $field );

		return $field;
	}


	/**
	 * Save field object with translation.
	 *
	 * @param array   $field
	 * @param integer $post_id
	 */
	public function update_field( $field, $post_id ) {

		$old_field = get_post_meta( $post_id, $field['key'], true );

		if ( ! $old_field ) {
			$old_field = array();
		}

		$default_config = array(
			'label'        => array(),
			'placeholder'  => array(),
			'instructions' => array(),
		);

		$acf_field_config = apply_filters( "wpm_acf_field_{$field['type']}_config", $default_config );
		$field            = apply_filters( 'acf/update_field/type=' . $field['type'], $field, $post_id );
		$field            = wpm_set_new_value( $old_field, $field, $acf_field_config );

		wp_cache_delete( 'load_field/key=' . $field['key'], 'acf' );

		update_post_meta( $post_id, $field['key'], $field );
	}

	/**
	 * Add translate config for text fields.
	 *
	 * @param $config
	 *
	 * @return array
	 */
	public function add_text_field_config( $config ) {
		$config['default_value'] = array();

		return $config;
	}


	/**
	 * Save value with translation for Pro version
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 *
	 * @return array|bool|string
	 */
	public function update_value_pro( $value, $post_id, $field ) {

		if ( wpm_is_ml_value( $value ) ) {
			return $value;
		}

		$info = acf_get_post_id_info( $post_id );

		switch ( $info['type'] ) {

			case 'post':
				$post_type = get_post_type( $info['id'] );
				if ( ! $post_type || is_null( wpm_get_post_config( $post_type ) ) ) {
					return $value;
				}

				break;

			case 'term':
				$term = get_term( $info['id'] );
				if ( ! $term || is_wp_error( $term ) || is_null( wpm_get_taxonomy_config( $term->taxonomy ) ) ) {
					return $value;
				}
		}

		$acf_field_config = apply_filters( "wpm_acf_{$info['type']}_config", null, $value, $post_id, $field );
		$acf_field_config = apply_filters( "wpm_acf_{$field['type']}_config", $acf_field_config, $value, $post_id, $field );

		if ( null === $acf_field_config ) {
			return $value;
		}

		remove_filter( 'acf/load_value', 'wpm_translate_value', 6 );
		$old_value = get_field( $field['name'], $post_id, false );
		add_filter( 'acf/load_value', 'wpm_translate_value', 6 );

		$value = wpm_set_new_value( $old_value, $value, $acf_field_config );

		return $value;
	}


	/**
	 * Save value with translation
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 */
	public function update_value( $value, $post_id, $field ) {

		if ( is_numeric( $post_id ) ) {
			$field_type = 'post';
		} elseif ( strpos( $post_id, 'user_' ) !== false ) {
			$field_type = 'user';
		} else {
			$field_type = 'term';
		}

		$translate = true;

		switch ( $field_type ) {

			case 'post':
				$post_type = get_post_type( $post_id );
				if ( ! $post_type || null === wpm_get_post_config( $post_type ) ) {
					$translate = false;
				}

				break;

			case 'term':
				$term_id = substr( $post_id, strripos( $post_id, '_' ) + 1 );
				$term    = get_term( $term_id );

				if ( ! $term || is_wp_error( $term ) || null === wpm_get_taxonomy_config( $term->taxonomy ) ) {
					$translate = false;
				}
		}

		$acf_field_config = apply_filters( "wpm_acf_{$field_type}_config", null, $value, $post_id, $field );
		$acf_field_config = apply_filters( "wpm_acf_{$field['type']}_config", $acf_field_config, $value, $post_id, $field );

		if ( null === $acf_field_config ) {
			$translate = false;
		}

		$value = stripslashes_deep( $value );

		foreach ( array( 'key', 'name', 'type' ) as $key ) {
			$value = apply_filters( 'acf/update_value/' . $key . '=' . $field[ $key ], $value, $post_id, $field );
		}

		if ( $translate ) {
			remove_filter( 'acf/load_value', 'wpm_translate_value', 6 );
			$old_value = get_field( $field['name'], $post_id, false );
			add_filter( 'acf/load_value', 'wpm_translate_value', 6 );

			if ( ! wpm_is_ml_value( $value ) ) {
				$value = wpm_set_new_value( $old_value, $value, $acf_field_config );
			}
		}


		if ( 'post' === $field_type ) {
			update_metadata( 'post', $post_id, $field['name'], $value );
			update_metadata( 'post', $post_id, '_' . $field['name'], $field['key'] );
		} elseif ( 'user' === $field_type ) {
			$user_id = str_replace( 'user_', '', $post_id );
			update_metadata( 'user', $user_id, $field['name'], $value );
			update_metadata( 'user', $user_id, '_' . $field['name'], $field['key'] );
		} else {
			$value = stripslashes_deep( $value );
			update_option( $post_id . '_' . $field['name'], $value );
			update_option( '_' . $post_id . '_' . $field['name'], $field['key'] );
		}

		wp_cache_set( 'load_value/post_id=' . $post_id . '/name=' . $field['name'], $value, 'acf' );
	}


	public function translate_value( $string ) {
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && wpm_get_post_data_by_key( 'action' ) === 'acf/everything_fields' ) {
			$string = wpm_translate_string( $string );
		}

		return $string;
	}
}
