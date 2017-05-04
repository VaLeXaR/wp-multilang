<?php
/**
 * Created by PhpStorm.
 * User: VaLeXaR
 * Date: 28.01.2017
 * Time: 17:37
 */

namespace QtNext\Helpers;

/**
 * Get paths for assets
 */
class JsonManifest {
	private $manifest;

	public function __construct( $manifest_path ) {
		if ( file_exists( $manifest_path ) ) {
			$this->manifest = json_decode( file_get_contents( $manifest_path ), true );
		} else {
			$this->manifest = [];
		}
	}

	public function get() {
		return $this->manifest;
	}

	public function get_path( $key = '', $default = null ) {
		$collection = $this->manifest;
		if ( is_null( $key ) ) {
			return $collection;
		}
		if ( isset( $collection[ $key ] ) ) {
			return $collection[ $key ];
		}
		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! isset( $collection[ $segment ] ) ) {
				return $default;
			} else {
				$collection = $collection[ $segment ];
			}
		}

		return $collection;
	}
}
