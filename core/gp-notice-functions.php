<?php
/**
 * GamePortal Message Functions
 *
 * Functions for error/message handling and display.
 *
 * @author 		VaLeXaR
 * @category 	Core
 * @package 	GamePortal/Functions
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get the count of notices added, either for all notices (default) or for one.
 * particular notice type specified by $notice_type.
 *
 * @since 2.1
 * @param string $notice_type The name of the notice type - either error, success or notice. [optional]
 * @return int
 */
function gp_notice_count( $notice_type = '' ) {
	if ( ! did_action( 'game_portal_init' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before game_portal_init.', 'game-portal' ), '1.0.0' );
		return;
	}

	$notice_count = 0;
	$all_notices  = GP()->session->get( 'gp_notices', array() );

	if ( isset( $all_notices[$notice_type] ) ) {

		$notice_count = absint( sizeof( $all_notices[$notice_type] ) );

	} elseif ( empty( $notice_type ) ) {

		foreach ( $all_notices as $notices ) {
			$notice_count += absint( sizeof( $all_notices ) );
		}

	}

	return $notice_count;
}

/**
 * Check if a notice has already been added.
 *
 * @since 2.1
 * @param string $message The text to display in the notice.
 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
 * @return bool
 */
function gp_has_notice( $message, $notice_type = 'success' ) {
	if ( ! did_action( 'game_portal_init' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before game_portal_init.', 'game-portal' ), '1.0.0' );
		return false;
	}

	$notices = GP()->session->get( 'gp_notices', array() );
	$notices = isset( $notices[ $notice_type ] ) ? $notices[ $notice_type ] : array();
	return array_search( $message, $notices ) !== false;
}

/**
 * Add and store a notice.
 *
 * @param string $message The text to display in the notice.
 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
 */

function gp_add_notice( $message, $notice_type = 'success' ) {
	if ( ! did_action( 'game_portal_init' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before game_portal_init.', 'game-portal' ), '2.3' );
		return;
	}

	$notices = GP()->session->get( 'gp_notices', array() );

	$notices[$notice_type][] = apply_filters( 'game_portal_add_' . $notice_type, $message );

	GP()->session->set( 'gp_notices', $notices );
}

/**
 * Set all notices at once.
 * @since 2.6.0
 */
function gp_set_notices( $notices ) {
	if ( ! did_action( 'game_portal_init' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before game_portal_init.', 'game-portal' ), '2.6' );
		return;
	}
	GP()->session->set( 'gp_notices', $notices );
}


/**
 * Unset all notices.
 *
 * @since 2.1
 */
function gp_clear_notices() {
	if ( ! did_action( 'game_portal_init' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before game_portal_init.', 'game-portal' ), '2.3' );
		return;
	}
	GP()->session->set( 'gp_notices', null );
}

/**
 * Prints messages and errors which are stored in the session, then clears them.
 *
 * @since 2.1
 */
function gp_print_notices() {
	if ( ! did_action( 'game_portal_init' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before game_portal_init.', 'game-portal' ), '2.3' );
		return;
	}

	$all_notices  = GP()->session->get( 'gp_notices', array() );
	$notice_types = apply_filters( 'game_portal_notice_types', array( 'error', 'success', 'notice' ) );

	foreach ( $notice_types as $notice_type ) {
		if ( gp_notice_count( $notice_type ) > 0 ) {
			gp_get_template( "notices/{$notice_type}.php", array(
				'messages' => array_filter( $all_notices[ $notice_type ] )
			) );
		}
	}

	gp_clear_notices();
}

/**
 * Print a single notice immediately.
 *
 * @param string $message The text to display in the notice.
 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
 */
function gp_print_notice( $message, $notice_type = 'success' ) {
	if ( 'success' === $notice_type ) {
		$message = apply_filters( 'game_portal_add_message', $message );
	}

	gp_get_template( "notices/{$notice_type}.php", array(
		'messages' => array( apply_filters( 'game_portal_add_' . $notice_type, $message ) )
	) );
}

/**
 * Returns all queued notices, optionally filtered by a notice type.
 *
 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
 * @return array|mixed
 */
function gp_get_notices( $notice_type = '' ) {
	if ( ! did_action( 'game_portal_init' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before game_portal_init.', 'game-portal' ), '2.3' );
		return;
	}

	$all_notices = GP()->session->get( 'gp_notices', array() );

	if ( empty( $notice_type ) ) {
		$notices = $all_notices;
	} elseif ( isset( $all_notices[ $notice_type ] ) ) {
		$notices = $all_notices[ $notice_type ];
	} else {
		$notices = array();
	}

	return $notices;
}

/**
 * Add notices for WP Errors.
 * @param  WP_Error $errors
 */
function gp_add_wp_error_notices( $errors ) {
	if ( is_wp_error( $errors ) && $errors->get_error_messages() ) {
		foreach ( $errors->get_error_messages() as $error ) {
			gp_add_notice( $error, 'error');
		}
	}
}
