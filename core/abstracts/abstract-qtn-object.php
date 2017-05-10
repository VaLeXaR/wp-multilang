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
				if ( qtn_is_localize_value( $meta_field['meta_value'] ) ) {
					$value = qtn_translate_value( $meta_field['meta_value'] );
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

	public function update_meta_field( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		global $wpdb, $qtn_config;

		if ( ! in_array( $meta_key, $qtn_config->settings[ $this->object_type . '_fields' ] ) ) {
			return $check;
		}

		$table     = $wpdb->{$this->object_table};
		$column    = sanitize_key( $this->object_type . '_id' );
		$id_column = 'user' == $this->object_type ? 'umeta_id' : 'meta_id';

		//TODO зневадити функцію
		if ( empty( $prev_value ) ) {

			if ( qtn_is_localize_value( $meta_value ) ) {
				$old_value  = array();
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
			return add_metadata( $this->object_type, $object_id, $meta_key, $meta_value );
		}

		if ( ! qtn_is_localize_value( $meta_value ) ) {

			$old_results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d;", $meta_key, $object_id ), ARRAY_A );

			if ($old_results ) {
				foreach ($old_results as $old_value) {
					$old_value = maybe_unserialize( $old_value );
				}
			}

			if ( is_array( $old_value ) ) {
				array_walk_recursive( $old_value, 'qtn_value_to_localize_array' );
			} else {
				$old_value = qtn_value_to_localize_array( $old_value );
			}

			$meta_value = qtn_set_language_value( $old_value, $meta_value );
			array_walk_recursive( $meta_value, 'qtn_localize_value_to_string' );
		}

		$meta_value = maybe_serialize( $meta_value );
		$data       = compact( 'meta_value' );
		$where      = array( $column => $object_id, 'meta_key' => $meta_key );

		if ( ! empty( $prev_value ) ) {

			if ( ! qtn_is_localize_value( $prev_value ) ) {
				$like       = '%' . $wpdb->esc_like( $prev_value ) . '%';
				$prev_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d AND meta_value LIKE %s LIMIT 1", $meta_key, $object_id, $like ) );
			}

			$prev_value          = maybe_serialize( $prev_value );
			$where['meta_value'] = $prev_value;
		}

		$result = $wpdb->update( $table, $data, $where );
		if ( ! $result ) {
			return false;
		}

		wp_cache_delete( $object_id . '_' . $meta_key, $this->object_type . '_qtn_meta' );

		return true;
	}

}
