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
		$id_column = 'user' === $this->object_type ? 'umeta_id' : 'meta_id';

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

		$config = wpm_get_config();

		switch ( $this->object_type ) {

			case 'post':
				if ( is_null( $config['post_types'][ get_post_type( $object_id ) ] ) ) {
					return $check;
				}

				break;

			case 'term':
				$term = get_term( $object_id );
				if ( is_null( $config['taxonomies'][ $term->taxonomy ] ) ) {
					return $check;
				}
		}

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
		$table     = $wpdb->{$this->object_table};
		$column    = sanitize_key( $this->object_type . '_id' );
		$id_column = 'user' === $this->object_type ? 'umeta_id' : 'meta_id';

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

			if ( count( $old_value ) === 1 ) {
				if ( $old_value[0] === $meta_value ) {
					return false;
				}
			}
		}

		$meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT $id_column FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id ) );
		if ( empty( $meta_ids ) ) {
			return add_metadata( $this->object_type, $object_id, $meta_key, $meta_value );
		}

		$_meta_value = $meta_value;

		if ( ! wpm_is_ml_value( $meta_value ) ) {
			$old_value  = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->{$this->object_table}} WHERE meta_key = %s AND {$column} = %d LIMIT 1;", $meta_key, $object_id ) );
			$old_value  = maybe_unserialize( $old_value );
			$old_value  = apply_filters( "wpm_filter_old_{$meta_key}_meta_value", $old_value, $meta_value );
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

		foreach ( $meta_ids as $meta_id ) {
			/**
			 * Fires immediately before updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook, `$meta_type`, refers to the meta
			 * object type (comment, post, or user).
			 *
			 * @since 2.9.0
			 *
			 * @param int    $meta_id    ID of the metadata entry to update.
			 * @param int    $object_id  Object ID.
			 * @param string $meta_key   Meta key.
			 * @param mixed  $meta_value Meta value.
			 */
			do_action( "update_{$this->object_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );

			if ( 'post' === $this->object_type ) {
				/**
				 * Fires immediately before updating a post's metadata.
				 *
				 * @since 2.9.0
				 *
				 * @param int    $meta_id    ID of metadata entry to update.
				 * @param int    $object_id  Object ID.
				 * @param string $meta_key   Meta key.
				 * @param mixed  $meta_value Meta value.
				 */
				do_action( 'update_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
			}
		}

		$result = $wpdb->update( $table, $data, $where );

		if ( ! $result ) {
			return false;
		}

		wp_cache_delete( $object_id . '_' . $meta_key, $this->object_type . '_wpm_meta' );

		foreach ( $meta_ids as $meta_id ) {
			/**
			 * Fires immediately after updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook, `$meta_type`, refers to the meta
			 * object type (comment, post, or user).
			 *
			 * @since 2.9.0
			 *
			 * @param int    $meta_id    ID of updated metadata entry.
			 * @param int    $object_id  Object ID.
			 * @param string $meta_key   Meta key.
			 * @param mixed  $meta_value Meta value.
			 */
			do_action( "updated_{$this->object_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );

			if ( 'post' === $this->object_type ) {
				/**
				 * Fires immediately after updating a post's metadata.
				 *
				 * @since 2.9.0
				 *
				 * @param int    $meta_id    ID of updated metadata entry.
				 * @param int    $object_id  Object ID.
				 * @param string $meta_key   Meta key.
				 * @param mixed  $meta_value Meta value.
				 */
				do_action( 'updated_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
			}
		}

		return true;
	}


	public function add_meta_field( $check, $object_id, $meta_key, $meta_value, $unique ) {
		global $wpdb;

		if ( null !== $check ) {
			return $check;
		}

		$config = wpm_get_config();

		switch ( $this->object_type ) {

			case 'post':
				if ( is_null( $config['post_types'][ get_post_type( $object_id ) ] ) ) {
					return $check;
				}
				break;

			case 'term':
				$term = get_term( $object_id );
				if ( is_null( $config['taxonomies'][ $term->taxonomy ] ) ) {
					return $check;
				}
		}

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

		$table  = $wpdb->{$this->object_table};
		$column = sanitize_key( $this->object_type . '_id' );

		if ( ! wpm_is_ml_value( $meta_value ) ) {
			$meta_value = wpm_set_language_value( array(), $meta_value, $meta_config );
			$meta_value = wpm_ml_value_to_string( $meta_value );
		}


		if ( $unique && $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE meta_key = %s AND $column = %d",
				$meta_key, $object_id ) )
		) {
			return false;
		}

		$_meta_value = $meta_value;

		$meta_value = maybe_serialize( $meta_value );

		/**
		 * Fires immediately before meta of a specific type is added.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (comment, post, or user).
		 *
		 * @since 3.1.0
		 *
		 * @param int    $object_id  Object ID.
		 * @param string $meta_key   Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( "add_{$this->object_type}_meta", $object_id, $meta_key, $_meta_value );

		$result = $wpdb->insert( $table, array(
			$column      => $object_id,
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value,
		) );

		if ( ! $result ) {
			return false;
		}

		$mid = (int) $wpdb->insert_id;

		wp_cache_delete( $object_id, $this->object_type . '_meta' );

		/**
		 * Fires immediately after meta of a specific type is added.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (comment, post, or user).
		 *
		 * @since 2.9.0
		 *
		 * @param int    $mid        The meta ID after successful update.
		 * @param int    $object_id  Object ID.
		 * @param string $meta_key   Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( "added_{$this->object_type}_meta", $mid, $object_id, $meta_key, $_meta_value );

		return $mid;
	}

}
