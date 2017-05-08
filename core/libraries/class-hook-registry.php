<?php

namespace QtNext\Core\Libraries;

class Hook_Registry {

	private $registry;

	public function __construct() {
		$this->registery = array();
	}

	public function add_hook( $id, $type, $name, $object, $method ) {

		$type = strtolower( $type );
		if ( 'filter' !== $type || 'action' !== $type ) {
		return new \WP_Error( '1', 'No proper hook type defined.' );
		}

		if ( 'filter' === $type ) {
			$this->add_filter( $name, $object, $method );
		} else {
			$this->add_action( $name, $object, $method );
		}

		$hook_info = array(
			$type,
			$name,
			$object,
			$method,
		);
		$this->registry[ $id ] = $hook_info;
	}

	private function add_filter( $name, $object, $method ) {
		add_filter( $name, array( $object, $method ) );
	}

	private function add_action( $name, $object, $method ) {
		add_action( $name, array( $object, $method ) );
	}
}
