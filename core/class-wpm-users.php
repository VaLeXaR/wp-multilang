<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once( 'abstracts/abstract-wpm-object.php' );

/**
 * Class WPM_Users
 * @package  WPM\Core
 * @author   VaLeXaR
 * @since    1.4.0
 */
class WPM_Users extends \WPM_Object {

	public $object_type = 'user';
	public $object_table = 'usermeta';

	/**
	 * WPM_Taxonomies constructor.
	 */
	public function __construct() {
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );
		add_filter( "add_{$this->object_type}_metadata", array( $this, 'add_meta_field' ), 99, 5 );
	}
}
