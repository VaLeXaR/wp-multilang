<?php

namespace WPM\Includes;
use WPM\Includes\Abstracts\WPM_Object;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Users
 * @package  WPM/Includes
 * @author   Valentyn Riaboshtan
 */
class WPM_Users extends WPM_Object {

	/**
	 * Object name
	 *
	 * @var string
	 */
	public $object_type = 'user';

	/**
	 *Table name for meta
	 *
	 * @var string
	 */
	public $object_table = 'usermeta';

	/**
	 * WPM_Taxonomies constructor.
	 */
	public function __construct() {
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 5, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );
		add_filter( "add_{$this->object_type}_metadata", array( $this, 'add_meta_field' ), 99, 5 );
		add_action( "delete_{$this->object_type}_metadata", array( $this, 'delete_meta_field' ), 99, 3 );
	}
}
