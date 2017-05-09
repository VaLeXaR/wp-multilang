<?php

abstract class QtN_Object {

	public $object_type;
	public $object_table;

	public function get_meta_field( $value, $object_id, $meta_key ) {
		global $wpdb, $qtn_config;

		if ( ! in_array( $meta_key, $qtn_config->settings[ $this->object_type . '_fields' ] ) ) {
			return $value;
		}

		$column    = sanitize_key( $this->object_type . '_id' );
		$id_column = 'user' == $this->object_type ? 'umeta_id' : 'meta_id';

		$meta_values = wp_cache_get( $object_id . '_' . $meta_key, $this->object_type . '_qtn_meta' );
		$values      = array();

		if ( ! $meta_values ) {

			$meta_values = $wpdb->get_results( $wpdb->prepare(
				"SELECT {$id_column}, meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d;",
				$meta_key, $object_id ), ARRAY_A );

			wp_cache_set( $object_id . '_' . $meta_key, $meta_values, $this->object_type . '_qtn_meta' );
		}

		if ( $meta_values ) {

			$meta_values = maybe_unserialize( $meta_values );

			foreach ( $meta_values as $meta_field ) {
				if ( is_array( $meta_field['meta_value'] ) ) {
					array_walk_recursive( $meta_field['meta_value'], 'qtn_localize_text' );
					$values[] = $meta_field['meta_value'];
				}

				if ( is_string( $meta_field['meta_value'] ) ) {
					$values[] = qtn_localize_text( $meta_field['meta_value'] );
				}
			}
		}

		if ( $values ) {
			return $values;
		}

		return null;

	}

}
