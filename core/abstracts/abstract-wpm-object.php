<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Object
 */
abstract class WPM_Object {

	/**
	 * Object type
	 * @var
	 */
	public $object_type;

	/**
	 * Object meta table
	 * @var
	 */
	public $object_table;


	/**
	 * Translate meta
	 *
	 * @param $value
	 * @param $object_id
	 * @param $meta_key
	 *
	 * @return array|mixed|null|string
	 */
	public function get_meta_field( $value, $object_id, $meta_key ) {
		global $wpdb;

		if ( ! $meta_key ) {

			$meta_cache = wp_cache_get( $object_id, $this->object_type . '_meta' );

			if ( ! $meta_cache ) {
				$meta_cache = update_meta_cache( $this->object_type, array( $object_id ) );
				$meta_cache = $meta_cache[ $object_id ];
			}

			return wpm_translate_value( $meta_cache );
		}

		$config               = wpm_get_config();
		$object_fields_config = $config[ $this->object_type . '_fields' ];
		$object_fields_config = apply_filters( "wpm_{$this->object_type}_meta_config", $object_fields_config );

		if ( ! isset( $object_fields_config[ $meta_key ] ) ) {
			return $value;
		}

		$meta_config = apply_filters( "wpm_{$meta_key}_meta_config", $object_fields_config[ $meta_key ], $object_id );
		$meta_config = apply_filters( "wpm_{$this->object_type}_meta_{$meta_key}_config", $meta_config, $object_id );

		if ( is_null( $meta_config ) ) {
			return $value;
		}

		$column    = sanitize_key( $this->object_type . '_id' );
		$id_column = 'user' == $this->object_type ? 'umeta_id' : 'meta_id';

		$meta_values = wp_cache_get( $object_id . '_' . $meta_key, $this->object_type . '_wpm_meta' );
		$values      = array();

		if ( ! $meta_values ) {

			$meta_values = $wpdb->get_results( $wpdb->prepare(
				"SELECT {$id_column}, meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d;",
				$meta_key, $object_id ), ARRAY_A );

			wp_cache_set( $object_id . '_' . $meta_key, $meta_values, $this->object_type . '_wpm_meta' );
		}

		if ( $meta_values ) {

			foreach ( $meta_values as $meta_field ) {
				$meta_field['meta_value'] = maybe_unserialize( $meta_field['meta_value'] );
				if ( wpm_is_ml_value( $meta_field['meta_value'] ) ) {
					$value = wpm_translate_value( $meta_field['meta_value'] );
				} else {
					$value = $meta_field['meta_value'];
				}
				$values[] = $value;
			}
		}

		if ( $values ) {
			return $values;
		}

		return null;

	}

	/**
	 * Save meta with translations
	 *
	 * @param $check
	 * @param $object_id
	 * @param $meta_key
	 * @param $meta_value
	 * @param $prev_value
	 *
	 * @return bool
	 */
	public function update_meta_field( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		global $wpdb;

		$config               = wpm_get_config();
		$object_fields_config = $config[ $this->object_type . '_fields' ];
		$object_fields_config = apply_filters( "wpm_{$this->object_type}_meta_config", $object_fields_config );

		if ( ! isset( $object_fields_config[ $meta_key ] ) ) {
			return $check;
		}

		$meta_config = apply_filters( "wpm_{$meta_key}_meta_config", $object_fields_config[ $meta_key ], $meta_value, $object_id );
		$meta_config = apply_filters( "wpm_{$this->object_type}_meta_{$meta_key}_config", $meta_config, $meta_value, $object_id );

		if ( is_null( $meta_config ) ) {
			return $check;
		}

		$table       = $wpdb->{$this->object_table};
		$column      = sanitize_key( $this->object_type . '_id' );
		$id_column   = 'user' == $this->object_type ? 'umeta_id' : 'meta_id';

		if ( empty( $prev_value ) ) {

			if ( wpm_is_ml_value( $meta_value ) ) {
				$old_value   = array();
				$old_results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d;", $meta_key, $object_id ), ARRAY_A );
				if ( $old_results ) {
					$old_value[0] = maybe_unserialize( $old_results[0]['meta_value'] );
				}
			} else {
				$old_value = get_metadata( $this->object_type, $object_id, $meta_key );
			}

			if ( count( $old_value ) == 1 ) {
				if ( $old_value[0] === $meta_value ) {
					return false;
				}
			}
		}

		$meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT $id_column FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id ) );
		if ( empty( $meta_ids ) ) {
			$meta_value = wpm_set_language_value( array(), $meta_value, $meta_config );
			$meta_value = wpm_ml_value_to_string( $meta_value );
			return add_metadata( $this->object_type, $object_id, $meta_key, $meta_value );
		}

		if ( ! wpm_is_ml_value( $meta_value ) ) {
			$old_value  = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d LIMIT 1;", $meta_key, $object_id ) );
			$old_value  = maybe_unserialize( $old_value );
			$old_value  = wpm_value_to_ml_array( $old_value );
			$meta_value = wpm_set_language_value( $old_value, $meta_value, $meta_config );
			$meta_value = wpm_ml_value_to_string( $meta_value );
		}

		$meta_value = maybe_serialize( $meta_value );
		$data       = compact( 'meta_value' );
		$where      = array( $column => $object_id, 'meta_key' => $meta_key );

		if ( ! empty( $prev_value ) ) {

			if ( ! wpm_is_ml_value( $prev_value ) ) {
				$like       = '%' . $wpdb->esc_like( esc_sql( $prev_value ) ) . '%';
				$prev_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d AND meta_value LIKE '%s' LIMIT 1", $meta_key, $object_id, $like ) );
			}

			$prev_value          = maybe_serialize( $prev_value );
			$where['meta_value'] = $prev_value;
		}

		$result = $wpdb->update( $table, $data, $where );

		if ( ! $result ) {
			return false;
		}

		wp_cache_delete( $object_id . '_' . $meta_key, $this->object_type . '_wpm_meta' );

		return true;
	}

}
