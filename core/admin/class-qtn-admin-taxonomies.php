<?php
/**
 * Taxonomies Admin
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  qTranslateNext/Admin
 */

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'QtN_Admin_Taxonomies' ) ) :

	/**
	 * QtN_Admin_Taxonomies Class.
	 *
	 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
	 */
	class QtN_Admin_Taxonomies {

		private $description = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init' ) );
			add_filter( 'pre_insert_term', array( $this, 'pre_insert_term' ), 0, 2 );
			add_filter( 'wp_update_term_data', array( $this, 'save_term' ), 0, 4 );
			add_action( 'edited_term_taxonomy', array( $this, 'update_description' ), 0, 2 );
		}


		public function init() {
			$settings = qtn_get_settings();

			foreach ( $settings['taxonomies'] as $taxonomy ) {
				add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'language_columns' ) );
				add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'render_language_column' ), 0, 3 );
			}
		}


		public function pre_insert_term( $term, $taxonomy ) {
			global $wpdb;

			$to_locale = '';
			$languages = array_flip( qtn_get_languages() );
			if ( isset( $_POST['lang'] ) && isset( $languages[ qtn_clean( $_POST['lang'] ) ] ) ) {
				$to_locale = $languages[ qtn_clean( $_POST['lang'] ) ];
			}

			$like    = '%' . $wpdb->esc_like( esc_sql( $term ) ) . '%';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT t.name AS `name` FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s' AND `name` LIKE '%s'", $taxonomy, $like ) );

			foreach ( $results as $result ) {
				$ml_term = qtn_translate_string( $result->name, $to_locale );
				if ( $ml_term == $term ) {
					return '';
				}
			}

			return $term;
		}


		public function save_term( $data, $term_id, $taxonomy, $args ) {
			$settings = qtn_get_settings();

			if ( ! in_array( $taxonomy, $settings['taxonomies'] ) ) {
				return $data;
			}

			if ( qtn_is_ml_value( $data['name'] ) ) {
				return $data;
			}

			remove_filter( 'get_term', 'qtn_translate_object', 0 );
			$old_name        = get_term_field( 'name', $term_id );
			$old_description = get_term_field( 'description', $term_id );
			add_filter( 'get_term', 'qtn_translate_object', 0 );
			$strings      = qtn_value_to_ml_array( $old_name );
			$value        = qtn_set_language_value( $strings, $data['name'] );
			$data['name'] = qtn_ml_value_to_string( $value );

			$this->description = array(
				'old' => $old_description,
				'new' => $args['description']
			);

			return $data;
		}


		public function update_description( $tt_id, $taxonomy ) {
			global $wpdb;
			$settings = qtn_get_settings();
			if ( ! in_array( $taxonomy, $settings['taxonomies'] ) ) {
				return;
			}

			if ( ! $this->description ) {
				return;
			}

			$value = $this->description['new'];

			if ( qtn_is_ml_value( $value ) ) {
				return;
			}

			$old_value   = $this->description['old'];
			$strings     = qtn_value_to_ml_array( $old_value );
			$value       = qtn_set_language_value( $strings, $value );
			$description = qtn_ml_value_to_string( $value );

			$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_taxonomy_id' => $tt_id ) );
		}

		/**
		 * Define custom columns for post_types.
		 *
		 * @param  array $existing_columns
		 *
		 * @return array
		 */
		public function language_columns( $columns ) {
			if ( empty( $columns ) && ! is_array( $columns ) ) {
				$columns = array();
			}

			$insert_after = 'name';

			$i = 0;
			foreach ( $columns as $key => $value ) {
				if ( $key == $insert_after ) {
					break;
				}
				$i ++;
			}

			$columns =
				array_slice( $columns, 0, $i + 1 ) + array( 'languages' => __( 'Languages', 'qtranslate-next' ) ) + array_slice( $columns, $i + 1 );

			return $columns;
		}

		/**
		 * Ouput custom columns for products.
		 *
		 * @param string $column
		 */
		public function render_language_column( $columns, $column, $term_id ) {

			if ( 'languages' == $column ) {
				remove_filter( 'get_term', 'qtn_translate_object', 0 );
				$term = get_term( $term_id );
				add_filter( 'get_term', 'qtn_translate_object', 0 );
				$output  = array();
				$text    = $term->name . $term->description;
				$strings = qtn_value_to_ml_array( $text );
				$options = qtn_get_options();
				$languages = qtn_get_languages();

				foreach ( $languages as $locale => $language ) {
					if ( isset( $strings[ $language ] ) && ! empty( $strings[ $language ] ) ) {
						$output[] = '<img src="' . QN()->flag_dir() . $options[ $locale ]['flag'] . '.png" alt="' . $options[ $locale ]['name'] . '" title="' . $options[ $locale ]['name'] . '">';
					}
				}

				if ( ! empty( $output ) ) {
					$columns .= implode( '<br />', $output );
				}
			}

			return $columns;
		}
	}

endif;
