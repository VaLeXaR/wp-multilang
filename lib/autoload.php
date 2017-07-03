<?php
/**
 * Automatically loads the specified file.
 *
 * Examines the fully qualified class name, separates it into components, then creates
 * a string that represents where the file is loaded on disk.
 *
 * @package WPM
 */
spl_autoload_register(function( $filename ) {

	// First, separate the components of the incoming file.
	$file_path = explode( '\\', $filename );

	/**
	 * - The first index will always be the namespace since it's part of the plugin.
	 * - All but the last index will be the path to the file.
	 * - The final index will be the filename. If it doesn't begin with 'I' then it's a class.
	 */

	// Get the last index of the array. This is the class we're loading.
	$class_file = '';
	if ( isset( $file_path[ count( $file_path ) - 1 ] ) ) {

		$class_file = strtolower(
			$file_path[ count( $file_path ) - 1 ]
		);
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

