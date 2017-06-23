<?php
/**
 * Class for capability with Advanced Custom Fields Plugin
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'acf' ) ) {

	/**
	 * @class    WPM_Acf
	 * @package  WPM\Core\Vendor
	 * @category Vendor
	 * @author   VaLeXaR
	 * @version  1.0.3
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
			add_filter( 'acf/load_field', 'wpm_translate_value', 0 );
			add_filter( 'acf/translate_field_group', 'wpm_translate_string', 0 );
			add_filter( 'acf/update_field', array( $this, 'save_field' ), 99 );
			add_filter( 'acf/update_field/type=text', array( $this, 'save_text_field' ), 99 );
			add_filter( 'acf/update_field/type=textarea', array( $this, 'save_text_field' ), 99 );
			add_filter( 'acf/update_field/type=wysiwyg', array( $this, 'save_text_field' ), 99 );
			add_filter( 'acf/load_value/type=text', 'wpm_translate_value', 0 );
			add_filter( 'acf/load_value/type=textarea', 'wpm_translate_value', 0 );
			add_filter( 'acf/load_value/type=wysiwyg', 'wpm_translate_value', 0 );
			add_filter( 'acf/update_value/type=text', array( $this, 'save_value' ), 99, 3 );
			add_filter( 'acf/update_value/type=textarea', array( $this, 'save_value' ), 99, 3 );
			add_filter( 'acf/update_value/type=wysiwyg', array( $this, 'save_value' ), 99, 3 );
			add_filter( 'wpm_post_acf-field-group_config', array( $this, 'add_config' ) );
			add_action( 'init', array( $this, 'check_pro' ) );
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

			if ( ! $this->pro ) {
				return false;
			}

			$config        = wpm_get_config();

			$old_field = maybe_unserialize( get_post_field( 'post_content', $field['ID'] ) );

			if ( ! $old_field ) {
				return $field;
			}

			$old_field          = wpm_value_to_ml_array( $old_field );
			$field_name         = get_post_field( 'post_title', $field['ID'] );
			$old_field['label'] = wpm_value_to_ml_array( $field_name );

			$default_config = array(
				'label'        => array(),
				'placeholder'  => array(),
				'instructions' => array(),
			);

			$new_field = wpm_set_language_value( $old_field, $field, $default_config );
			$field     = wpm_array_merge_recursive( $field, $new_field );
			$field     = wpm_ml_value_to_string( $field );

			return $field;
		}

		/**
		 * Save params for text field object. Only Pro.
		 *
		 * @param $field
		 *
		 * @return array|mixed|string
		 */
		public function save_text_field( $field ) {

			if ( ! $this->pro ) {
				return $field;
			}

			$old_field = maybe_unserialize( get_post_field( 'post_content', $field['ID'] ) );

			if ( ! $old_field ) {
				return $field;
			}

			$old_field = wpm_value_to_ml_array( $old_field );

			$default_config = array(
				'default_value' => array(),
			);

			$new_field = wpm_set_language_value( $old_field, $field, $default_config );
			$field     = wpm_array_merge_recursive( $field, $new_field );
			$field     = wpm_ml_value_to_string( $field );

			return $field;
		}


		/**
		 * Save value with translation
		 *
		 * @param $value
		 * @param $post_id
		 * @param $field
		 *
		 * @return array|bool|string
		 */
		public function save_value( $value, $post_id, $field ) {

			$info   = acf_get_post_id_info( $post_id );

			switch ( $info['type'] ) {

				case 'post':
				case 'term':
				case 'comment':
				case 'user':

					add_filter( "wpm_{$info['type']}_meta_config", function ( $object_fields_config ) use ( $field ) {

						if ( ! isset( $object_fields_config[ $field['name'] ] ) ) {
							$object_fields_config[ $field['name'] ] = array();
						}

						return $object_fields_config;
					} );
					break;

				case 'option':

					if ( substr( $post_id, 0, 6 ) != 'widget' ) {

						add_filter( 'wpm_options_config', function ( $config_options ) use ( $field ) {

							if ( ! isset( $config_options[ $field['name'] ] ) ) {
								$config_options[ $field['name'] ] = array();
							}

							return $config_options;
						} );

					} else {

						$acf_widget_fields = apply_filters( 'wpm_acf_widget_fields', array() );

						if ( isset( $acf_widget_fields[ $field['name'] ] ) && is_null( $acf_widget_fields[ $field['name'] ] ) ) {
							return $value;
						}
					}

					break;
			}

			return $value;
		}
	}

	new WPM_Acf();

}
