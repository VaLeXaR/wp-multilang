<?php

if ( class_exists( 'Kint' ) ) {
	Kint::$enabled_mode        = true;
	Kint_Renderer_Rich::$theme = 'aante-light.css';
}

function dump_hook( $tag, $hook ) {
	ksort( $hook );

	echo "<pre>>>>>>\t$tag<br>";

	foreach ( $hook as $priority => $functions ) {

		echo $priority;

		foreach ( $functions as $function ) {
			if ( $function['function'] != 'list_hook_details' ) {

				echo "\t";

				if ( is_string( $function['function'] ) ) {
					echo $function['function'];
				} elseif ( is_string( $function['function'][0] ) ) {
					echo $function['function'][0] . ' -> ' . $function['function'][1];
				} elseif ( is_object( $function['function'][0] ) ) {
					echo "(object) " . get_class( $function['function'][0] ) . ' -> ' . $function['function'][1];
				} else {
					print_r( $function );
				}

				echo ' (' . $function['accepted_args'] . ') <br>';
			}
		}
	}

	echo '</pre>';
}

function list_hooks( $filter = false ) {
	global $wp_filter;

	$hooks = $wp_filter;
	ksort( $hooks );

	foreach ( $hooks as $tag => $hook ) {
		if ( false === $filter || false !== strpos( $tag, $filter ) ) {
			dump_hook( $tag, $hook );
		}
	}
}

function var_error_log( $args = array() ) {
	$args = func_get_args();
	ob_start();                    // start buffer capture
	call_user_func_array( "var_dump", $args );
	$contents = ob_get_contents(); // put the buffer into a variable
	ob_end_clean();                // end capture
	error_log( $contents );        // log contents of the result of var_dump( $object )
}
