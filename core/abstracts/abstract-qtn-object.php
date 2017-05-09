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

	public function update_meta_field( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		global $wpdb, $qtn_config;

		if ( ! in_array( $meta_key, $qtn_config->settings[ $this->object_type . '_fields' ] ) ) {
			return $check;
		}

		$table     = $wpdb->{$this->object_table};
		$column    = sanitize_key( $this->object_type . '_id' );
		$id_column = 'user' == $this->object_type ? 'umeta_id' : 'meta_id';

		if ( empty( $prev_value ) ) {
			if ( ! qtn_is_localize_string( $meta_value ) ) {
				$old_value = get_metadata( $this->object_type, $object_id, $meta_key );
				if ( count( $old_value ) == 1 ) {
					if ( $old_value[0] === $meta_value ) {
						return false;
					}
				}
			} else {
				$old_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d LIMIT 1", $meta_key, $object_id ) );
				if ( $old_value === $meta_value ) {
					return false;
				}
			}
		}

		$meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT $id_column FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id ) );
		if ( empty( $meta_ids ) ) {
			return add_metadata( $this->object_type, $object_id, $meta_key, $meta_value );
		}

		if ( ! qtn_is_localize_string( $meta_value ) ) {

			$old_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d LIMIT 1", $meta_key, $object_id ) );

			$strings = qtn_string_to_localize_array( $old_value );

			if ( isset( $_POST['lang'] ) ) {
				$lang             = qtn_clean( $_POST['lang'] );
				$strings[ $lang ] = $meta_value;
			} else {
				$strings[ $qtn_config->languages[ get_locale() ] ] = $meta_value;
			}

			$meta_value = qtn_localize_array_to_string( $strings );
		}

		$meta_value = maybe_serialize( $meta_value );
		$data       = compact( 'meta_value' );
		$where      = array( $column => $object_id, 'meta_key' => $meta_key );

		if ( ! empty( $prev_value ) ) {

			if ( ! qtn_is_localize_string( $prev_value ) ) {
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
