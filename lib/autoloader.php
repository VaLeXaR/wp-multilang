<?php
/**
 * Automatically loads the specified file.
 *
 * @package WACATL\Lib
 */

namespace WCATL\Lib;

/**
 * Automatically loads the specified file.
 *
 * Examines the fully qualified class name, separates it into components, then creates
 * a string that represents where the file is loaded on disk.
 *
 * @package WACATL\Lib
 */
spl_autoload_register(function( $filename ) {

	// First, separate the components of the incoming file.
	$file_path = explode( '\\', $filename );
	$class_file = '';

	/**
	 * - The first index will always be WCATL since it's part of the plugin.
	 * - All but the last index will be the path to the file.
	 */

	// Get the last index of the array. This is the class we're loading.
	if ( isset( $file_path[ count( $file_path ) - 1 ] ) ) {

		$class_file = strtolower(
			$file_path[ count( $file_path ) - 1 ]
		);

		// The classname has an underscore, so we need to replace it with a hyphen for the file name.
		$class_file = str_ireplace( '_', '-', $class_file );
		$class_file = "class-$class_file.php";
	}

	/**
	 * Find the fully qualified path to the class file by iterating through the $file_path array.
	 * We ignore the first index since it's always the top-level package. The last index is always
	 * the file so we append that at the end.
	 */
	$fully_qualified_path = trailingslashit(
		dirname(
			dirname( __FILE__ )
		)
	);

	for ( $i = 1; $i < count( $file_path ) - 1; $i++ ) {

		$dir = strtolower( $file_path[ $i ] );
		$dir = str_ireplace( '_', '-', $dir );
		$fully_qualified_path .= trailingslashit( $dir );
	}
	$fully_qualified_path .= $class_file;

	// Now we include the file.
	if ( file_exists( $fully_qualified_path ) ) {
		include_once( $fully_qualified_path );
	}
});
