<?php
/**
 * Taxonomies Admin
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  WPMPlugin/Admin
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPM_Admin_Taxonomies' ) ) :

	/**
	 * WPM_Admin_Taxonomies Class.
	 *
	 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
	 */
	class WPM_Admin_Taxonomies {

		private $description = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init' ) );
			add_filter( 'pre_insert_term', array( $this, 'pre_insert_term' ), 0, 2 );
			add_filter( 'wp_update_term_data', array( $this, 'save_term' ), 99, 4 );
			add_action( 'edited_term_taxonomy', array( $this, 'update_description' ), 0, 2 );
		}


		public function init() {
			$config = wpm_get_config();

			foreach ( $config['taxonomies'] as $taxonomy => $config ) {
				add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'language_columns' ) );
				add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'render_language_column' ), 0, 3 );
			}
		}


		public function pre_insert_term( $term, $taxonomy ) {
			global $wpdb;

			$to_locale = '';
			$languages = array_flip( wpm_get_languages() );
			if ( isset( $_POST['lang'] ) && isset( $languages[ wpm_clean( $_POST['lang'] ) ] ) ) {
				$to_locale = $languages[ wpm_clean( $_POST['lang'] ) ];
			}

			$like    = '%' . $wpdb->esc_like( esc_sql( $term ) ) . '%';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT t.name AS `name` FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s' AND `name` LIKE '%s'", $taxonomy, $like ) );

			foreach ( $results as $result ) {
				$ml_term = wpm_translate_string( $result->name, $to_locale );
				if ( $ml_term == $term ) {
					return '';
				}
			}

			return $term;
		}


		public function save_term( $data, $term_id, $taxonomy, $args ) {
			$config = wpm_get_config();

			if ( ! isset( $config['taxonomies'][ $taxonomy ] ) ) {
				return $data;
			}

			if ( wpm_is_ml_value( $data['name'] ) ) {
				return $data;
			}

			remove_filter( 'get_term', 'wpm_translate_object', 0 );
			$old_name        = get_term_field( 'name', $term_id );
			$old_description = get_term_field( 'description', $term_id );
			add_filter( 'get_term', 'wpm_translate_object', 0 );
			$strings      = wpm_value_to_ml_array( $old_name );
			$value        = wpm_set_language_value( $strings, $data['name'], array() );
			$data['name'] = wpm_ml_value_to_string( $value );

			$this->description = array(
				'old' => $old_description,
				'new' => $args['description']
			);

			return $data;
		}


		public function update_description( $tt_id, $taxonomy ) {
			global $wpdb;

			$config = wpm_get_config();
			$tax_config = $config['taxonomies'][ $taxonomy ];

			if ( ! isset( $tax_config ) ) {
				return;
			}

			if ( ! $this->description ) {
				return;
			}

			$value = $this->description['new'];

			if ( wpm_is_ml_value( $value ) ) {
				return;
			}

			$old_value   = $this->description['old'];
			$strings     = wpm_value_to_ml_array( $old_value );
			$value       = wpm_set_language_value( $strings, $value, $tax_config );
			$description = wpm_ml_value_to_string( $value );

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
				array_slice( $columns, 0, $i + 1 ) + array( 'languages' => __( 'Languages', 'wpm' ) ) + array_slice( $columns, $i + 1 );

			return $columns;
		}

		/**
		 * Ouput custom columns for products.
		 *
		 * @param string $column
		 */
		public function render_language_column( $columns, $column, $term_id ) {

			if ( 'languages' == $column ) {
				remove_filter( 'get_term', 'wpm_translate_object', 0 );
				$term = get_term( $term_id );
				add_filter( 'get_term', 'wpm_translate_object', 0 );
				$output    = array();
				$text      = $term->name . $term->description;
				$strings   = wpm_value_to_ml_array( $text );
				$options   = wpm_get_options();
				$languages = wpm_get_languages();

				foreach ( $languages as $locale => $language ) {
					if ( isset( $strings[ $language ] ) && ! empty( $strings[ $language ] ) ) {
						$output[] = '<img src="' . WPM()->flag_dir() . $options[ $locale ]['flag'] . '.png" alt="' . $options[ $locale ]['name'] . '" title="' . $options[ $locale ]['name'] . '">';
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
