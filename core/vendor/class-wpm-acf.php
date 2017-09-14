<?php
/**
 * Class for capability with Advanced Custom Fields Plugin
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'acf' ) ) {
	return;
}


/**
 * @class    WPM_Acf
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @version  1.1.0
 */
class WPM_Acf {

	/**
	 * Flag ACF is Pro or not
	 *
	 * @var bool
	 */
	private $pro = false;

	/**
	 * WPM_Acf constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'check_pro' ) );

		if ( $this->pro ) {
			add_filter( 'acf/translate_field_group', 'wpm_translate_string', 0 );
			add_filter( 'acf/update_field', array( $this, 'save_field' ), 99 );
			add_filter( 'acf/update_value', array( $this, 'save_value_pro' ), 99, 3 );
			add_filter( 'wpm_acf_field_text_config', array( $this, 'add_text_field_config' ) );
			add_filter( 'wpm_acf_field_textarea_config', array( $this, 'add_text_field_config' ) );
			add_filter( 'wpm_acf_field_wysiwyg_config', array( $this, 'add_text_field_config' ) );
			add_filter( 'wpm_post_acf-field-group_config', array( $this, 'add_config' ) );
		} else {
			remove_class_action( 'acf/update_value', 'acf_field_functions', 'update_value', 5 );
			add_action( 'acf/update_value', array( $this, 'save_value' ), 5, 3 );
			add_filter( 'attribute_escape', array( $this, 'translate_value' ) );
			add_filter( 'esc_textarea', array( $this, 'translate_value' ) );
			add_filter( 'acf_the_editor_content', 'wpm_translate_value', 0 );
		}

		add_filter( 'acf/load_field', 'wpm_translate_value', 0 );
		add_filter( 'acf/load_value', 'wpm_translate_value', 0 );
		add_filter( 'wpm_acf_text_config', '__return_empty_array' );
		add_filter( 'wpm_acf_textarea_config', '__return_empty_array' );
		add_filter( 'wpm_acf_wysiwyg_config', '__return_empty_array' );
	}

	/**
	 * Check Pro version
	 */
	public function check_pro() {
		$post_types = get_post_types( '', 'names' );

		if ( in_array( 'acf-field-group', $post_types, true ) ) {
			$this->pro = true;
		}
	}


	/**
	 * Add config for 'acf-field-group' post types
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
	 * Save field object with translation. Only Pro.
	 *
	 * @param $field
	 *
	 * @return array|bool|string
	 */
	public function save_field( $field ) {

		$old_field = maybe_unserialize( get_post_field( 'post_content', $field['ID'] ) );

		if ( ! $old_field ) {
			return $field;
		}

		$old_field          = wpm_array_merge_recursive( $field, $old_field );
		$old_field          = wpm_value_to_ml_array( $old_field );
		$field_name         = get_post_field( 'post_title', $field['ID'] );
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
	public function save_value_pro( $value, $post_id, $field ) {

		if ( wpm_is_ml_value( $value ) ) {
			return $value;
		}

		$info   = acf_get_post_id_info( $post_id );
		$config = wpm_get_config();

		switch ( $info['type'] ) {

			case 'post':
				$posts_config               = $config['post_types'];
				$posts_config               = apply_filters( 'wpm_posts_config', $posts_config );
				$post_type                  = get_post_type( $info['id'] );
				$posts_config[ $post_type ] = apply_filters( "wpm_post_{$post_type}_config", isset( $posts_config[ $post_type ] ) ? $posts_config[ $post_type ] : null );

				if ( is_null( $posts_config[ $post_type ] ) ) {
					return $value;
				}

				break;

			case 'term':
				$taxonomies_config              = $config['taxonomies'];
				$taxonomies_config              = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );
				$term                           = get_term( $info['id'] );
				$taxonomy                       = $term->taxonomy;
				$taxonomies_config[ $taxonomy ] = apply_filters( "wpm_taxonomy_{$taxonomy}_config", isset( $taxonomies_config[ $taxonomy ] ) ? $taxonomies_config[ $taxonomy ] : null );

				if ( is_null( $taxonomies_config[ $taxonomy ] ) ) {
					return $value;
				}
		}

		$acf_field_config = apply_filters( "wpm_acf_{$info['type']}_config", null, $value, $post_id, $field );
		$acf_field_config = apply_filters( "wpm_acf_{$field['type']}_config", $acf_field_config, $value, $post_id, $field );

		if ( is_null( $acf_field_config ) ) {
			return $value;
		}

		remove_filter( "acf/load_value", 'wpm_translate_value', 0 );
		$old_value = get_field( $field['name'], $post_id, false );
		add_filter( "acf/load_value", 'wpm_translate_value', 0 );
		$old_value = wpm_value_to_ml_array( $old_value );
		$new_value = wpm_set_language_value( $old_value, $value, $acf_field_config );
		$value     = wpm_ml_value_to_string( $new_value );

		return $value;
	}


	/**
	 * Save value with translation
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 *
	 * @return bool
	 */
	public function save_value( $value, $post_id, $field ) {

		if ( wpm_is_ml_value( $value ) ) {
			return false;
		}

		$config = wpm_get_config();

		if ( is_numeric( $post_id ) ) {
			$field_type = 'post';
		} elseif ( strpos( $post_id, 'user_' ) !== false ) {
			$field_type = 'user';
		} else {
			$field_type = 'term';
		}

		switch ( $field_type ) {

			case 'post':
				$posts_config               = $config['post_types'];
				$posts_config               = apply_filters( 'wpm_posts_config', $posts_config );
				$post_type                  = get_post_type( $post_id );
				$posts_config[ $post_type ] = apply_filters( "wpm_post_{$post_type}_config", isset( $posts_config[ $post_type ] ) ? $posts_config[ $post_type ] : null );

				if ( is_null( $posts_config[ $post_type ] ) ) {
					return false;
				}

				break;

			case 'term':
				$term_id                        = substr( $post_id, strripos( $post_id, '_' ) + 1 );
				$taxonomies_config              = $config['taxonomies'];
				$taxonomies_config              = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );
				$term                           = get_term( $term_id );
				$taxonomy                       = $term->taxonomy;
				$taxonomies_config[ $taxonomy ] = apply_filters( "wpm_taxonomy_{$taxonomy}_config", isset( $taxonomies_config[ $taxonomy ] ) ? $taxonomies_config[ $taxonomy ] : null );

				if ( is_null( $taxonomies_config[ $taxonomy ] ) ) {
					return false;
				}
		}

		$acf_field_config = apply_filters( "wpm_acf_{$field_type}_config", null, $value, $post_id, $field );
		$acf_field_config = apply_filters( "wpm_acf_{$field['type']}_config", $acf_field_config, $value, $post_id, $field );

		if ( is_null( $acf_field_config ) ) {
			return false;
		}

		remove_filter( "acf/load_value", 'wpm_translate_value', 0 );
		$old_value = get_field( $field['name'], $post_id, false );
		add_filter( "acf/load_value", 'wpm_translate_value', 0 );
		$old_value = wpm_value_to_ml_array( $old_value );
		$new_value = wpm_set_language_value( $old_value, $value, $acf_field_config );
		$value     = wpm_ml_value_to_string( $new_value );

		$value = stripslashes_deep( $value );

		foreach ( array( 'key', 'name', 'type' ) as $key ) {
			$value = apply_filters( 'acf/update_value/' . $key . '=' . $field[ $key ], $value, $post_id, $field );
		}

		if ( $field_type == 'post' ) {
			update_metadata( 'post', $post_id, $field['name'], $value );
			update_metadata( 'post', $post_id, '_' . $field['name'], $field['key'] );
		} elseif ( $field_type == 'user' ) {
			$user_id = str_replace( 'user_', '', $post_id );
			update_metadata( 'user', $user_id, $field['name'], $value );
			update_metadata( 'user', $user_id, '_' . $field['name'], $field['key'] );
		} else {
			$value = stripslashes_deep( $value );
			update_option( $post_id . '_' . $field['name'], $value );
			update_option( '_' . $post_id . '_' . $field['name'], $field['key'] );
		}

		wp_cache_set( 'load_value/post_id=' . $post_id . '/name=' . $field['name'], $value, 'acf' );

		return true;
	}


	public function translate_value( $string ) {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['action'] ) && $_POST['action'] == 'acf/everything_fields' ) {
			$string = wpm_translate_string( $string );
		}

		return $string;
	}
}

new WPM_Acf();
