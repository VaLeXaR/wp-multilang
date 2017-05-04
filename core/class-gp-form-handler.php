<?php
/**
 * Handle frontend forms.
 *
 * @class          GP_Form_Handler
 * @version        2.2.0
 * @package        GamePortal/Classes/
 * @category       Class
 * @author         VaLeXaR
 */

namespace GP;
use GP\Shortcodes\GP_Shortcode_My_Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GP_Form_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'save_account_details' ) );
		add_action( 'template_redirect', array( __CLASS__, 'save_game' ), 20 );
		add_action( 'template_redirect', array( __CLASS__, 'save_level' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_login' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_registration' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_lost_password' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_reset_password' ), 20 );
	}

	/**
	 * Save the password/account details and redirect back to the my account page.
	 */
	public static function save_account_details() {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_account_details' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_account_details' ) ) {
			return;
		}

		$errors = new \WP_Error();
		$user   = new \stdClass();

		$user->ID     = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );

		if ( $user->ID <= 0 ) {
			return;
		}

		$account_first_name = ! empty( $_POST['account_first_name'] ) ? gp_clean( $_POST['account_first_name'] ) : '';
		$account_last_name  = ! empty( $_POST['account_last_name'] ) ? gp_clean( $_POST['account_last_name'] ) : '';
		$account_email      = ! empty( $_POST['account_email'] ) ? gp_clean( $_POST['account_email'] ) : '';
		$pass_cur           = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : '';
		$pass1              = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : '';
		$pass2              = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : '';
		$save_pass          = true;

		$user->first_name = $account_first_name;
		$user->last_name  = $account_last_name;

		// Prevent emails being displayed, or leave alone.
		$user->display_name = is_email( $current_user->display_name ) ? $user->first_name : $current_user->display_name;

		// Handle required fields
		$required_fields = array(
			'account_first_name' => __( 'First Name', 'game-portal' ),
			'account_last_name'  => __( 'Last Name', 'game-portal' ),
			'account_email'      => __( 'Email address', 'game-portal' ),
		);

		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $_POST[ $field_key ] ) ) {
				gp_add_notice( '<strong>' . esc_html( $field_name ) . '</strong> ' . __( 'is a required field.', 'game-portal' ), 'error' );
			}
		}

		if ( $account_email ) {
			$account_email = sanitize_email( $account_email );
			if ( ! is_email( $account_email ) ) {
				gp_add_notice( __( 'Please provide a valid email address.', 'game-portal' ), 'error' );
			} elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
				gp_add_notice( __( 'This email address is already registered.', 'game-portal' ), 'error' );
			}
			$user->user_email = $account_email;
		}

		if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			gp_add_notice( __( 'Please fill out all password fields.', 'game-portal' ), 'error' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			gp_add_notice( __( 'Please enter your current password.', 'game-portal' ), 'error' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			gp_add_notice( __( 'Please re-enter your password.', 'game-portal' ), 'error' );
			$save_pass = false;
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			gp_add_notice( __( 'New passwords do not match.', 'game-portal' ), 'error' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			gp_add_notice( __( 'Your current password is incorrect.', 'game-portal' ), 'error' );
			$save_pass = false;
		}

		if ( $pass1 && $save_pass ) {
			$user->user_pass = $pass1;
		}

		if ( $errors->get_error_messages() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				gp_add_notice( $error, 'error' );
			}
		}

		if ( gp_notice_count( 'error' ) === 0 ) {

			wp_update_user( $user );

			gp_add_notice( __( 'Account details changed successfully.', 'game-portal' ) );

			wp_safe_redirect( gp_get_page_permalink( 'myaccount' ) );
			exit;
		}
	}

	/**
	 * Save the game settings.
	 */
	public static function save_game() {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_game' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_game' ) ) {
			return;
		}

		$game_settings = array();


		if ( empty( $_POST['game_name'] ) ) {
			gp_add_notice( '<strong>' . esc_html( __( 'Game Name', 'game-portal' ) ) . '</strong> ' . __( 'is a required field.', 'game-portal' ), 'error' );
		}

		if ( empty( $_POST['date'] ) ) {
			gp_add_notice( '<strong>' . esc_html( __( 'Game Date', 'game-portal' ) ) . '</strong> ' . __( 'is a required field.', 'game-portal' ), 'error' );
		}

		$game_settings['name']                = gp_trim_string( gp_clean( $_POST['game_name'] ), 60, '' );
		$game_settings['date']                = gp_clean( $_POST['date'] );
		$game_settings['map_id']              = isset( $_POST['map_id'] ) ? gp_clean( $_POST['map_id'] ) : 0;
		$game_settings['location']            = isset( $_POST['location'] ) ? 1 : 0;
		$game_settings['ar']                  = isset( $_POST['ar'] ) ? 1 : 0;
		$game_settings['qr']                  = isset( $_POST['qr'] ) ? 1 : 0;
		$game_settings['optional_levels']     = isset( $_POST['optional_levels'] ) ? 1 : 0;
		$game_settings['points_use']          = isset( $_POST['points']['use'] ) ? 1 : 0;
		$game_settings['points_need']         = isset( $_POST['points']['need'] ) ? abs( intval( $_POST['points']['need'] ) ) : 0;
		$game_settings['timer_use']           = isset( $_POST['timer']['use'] ) ? 1 : 0;
		$game_settings['timer_pause']         = isset( $_POST['timer']['pause'] ) ? 1 : 0;
		$game_settings['timer_hours']         = isset( $_POST['timer']['hours'] ) ? abs( intval( $_POST['timer']['hours'] ) ) : 0;
		$game_settings['timer_minutes']       = isset( $_POST['timer']['minutes'] ) ? abs( intval( $_POST['timer']['minutes'] ) ) : 0;
		$game_settings['background_stretch']  = isset( $_POST['background']['stretch'] ) ? 1 : 0;
		$game_settings['background_image_id'] = isset( $_POST['background']['image_id'] ) ? gp_clean( $_POST['background']['image_id'] ) : 0;
		$game_settings['public']              = isset( $_POST['public'] ) ? 1 : 0;

		if ( gp_notice_count( 'error' ) === 0 ) {

			foreach ( $game_settings as $meta_key => $meta_value ) {
				update_term_meta( get_queried_object_id(), '_' . $meta_key, $meta_value );
			}

			gp_add_notice( __( 'Game settings changed successfully.', 'game-portal' ) );

			if ( $_REQUEST['redirect_to'] ) {
				wp_redirect( gp_clean( $_REQUEST['redirect_to'] ) );
			}
		}
	}

	/**
	 * Save the level settings.
	 */
	public static function save_level() {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_level' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_level' ) ) {
			return;
		}

		$right_settings = array();
		$wrong_settings = array();

		//Check level settings
		if ( empty( $_POST['level']['desc'] ) ) {
			gp_add_notice( '<strong>' . esc_html( __( 'Description', 'game-portal' ) ) . '</strong> ' . __( 'is a required field.', 'game-portal' ), 'error' );
		}


		$level_settings = array(
			'desc'            => wp_kses_post( stripslashes_from_strings_only( $_POST['level']['desc'] ) ),
			'video_code'      => gp_clean( $_POST['level']['video']['code'] ),
			'video_auto_play' => isset( $_POST['level']['video']['auto_play'] ) ? 1 : 0,
			'video_mute'      => isset( $_POST['level']['video']['mute'] ) ? 1 : 0,
			'audio_audio_id'  => isset( $_POST['level']['audio']['audio_id'] ) ? gp_clean( $_POST['level']['audio']['audio_id'] ) : 0,
			'audio_auto_play' => isset( $_POST['level']['audio']['auto_play'] ) ? 1 : 0,
			'audio_once'      => isset( $_POST['level']['audio']['once'] ) ? 1 : 0,
			'gallery'         => isset( $_POST['level']['gallery'] ) ? gp_clean( $_POST['level']['gallery'] ) : array(),
			'order'           => gp_clean( $_POST['level']['order'] )
		);

		$level_settings['done']     = isset( $_POST['level']['done'] ) ? 1 : 0;
		$level_settings['optional'] = isset( $_POST['level']['optional'] ) ? 1 : 0;

		//QR settings
		if ( isset( $_POST['level']['qr_code'] ) ) {
			$level_settings['qr_code_type']      = isset( $_POST['level']['qr_code']['type'] ) ? gp_clean( $_POST['level']['qr_code']['type'] ) : 'text';
			$level_settings['qr_code_type_text'] = isset( $_POST['level']['qr_code']['type_text'] ) ? gp_clean( $_POST['level']['qr_code']['type_text'] ) : '';
			$level_settings['qr_code_image_id']  = isset( $_POST['level']['qr_code']['image_id'] ) ? gp_clean( $_POST['level']['qr_code']['image_id'] ) : 0;
			$level_settings['qr_code_text']      = isset( $_POST['level']['qr_code']['text'] ) ? gp_clean( $_POST['level']['qr_code']['text'] ) : '';
		}

		//Location settings
		if ( isset( $_POST['level']['location'] ) ) {
			$level_settings['location_type']        = isset( $_POST['level']['location']['type'] ) ? gp_clean( $_POST['level']['location']['type'] ) : 'address';
			$level_settings['location_coordinates'] = isset( $_POST['level']['location']['coordinates'] ) ? gp_clean( $_POST['level']['location']['coordinates'] ) : '';
		}

		//Augmented Reality settings
		if ( isset( $_POST['level']['ar'] ) ) {
			$level_settings['ar_target_image_id'] = isset( $_POST['level']['ar']['target']['image_id'] ) ? gp_clean( $_POST['level']['ar']['target']['image_id'] ) : 0;
			$level_settings['ar_result_type']     = isset( $_POST['level']['ar']['result']['type'] ) ? gp_clean( $_POST['level']['ar']['result']['type'] ) : 'image';
			$level_settings['ar_result_image_id'] = isset( $_POST['level']['ar']['result']['image_id'] ) ? gp_clean( $_POST['level']['ar']['result']['image_id'] ) : 0;
			$level_settings['ar_result_text']     = isset( $_POST['level']['ar']['result']['text'] ) ? gp_clean( $_POST['level']['ar']['result']['text'] ) : '';
		}

		if ( isset( $_POST['level']['answer'] ) ) {
			$level_settings['answer_text']         = sanitize_textarea_field( $_POST['level']['answer']['text'] );
			$level_settings['answer_type']         = isset( $_POST['level']['answer']['type'] ) ? gp_clean( $_POST['level']['answer']['type'] ) : 'text';
			$level_settings['answer_options']      = isset( $_POST['level']['answer']['options'] ) ? gp_clean( $_POST['level']['answer']['options'] ) : array();
			$level_settings['answer_points']       = isset( $_POST['level']['answer']['points'] ) ? gp_clean( $_POST['level']['answer']['points'] ) : 0;
			$level_settings['hint_text']           = sanitize_textarea_field( $_POST['level']['hint']['text'] );
			$level_settings['hint_time_type']      = isset( $_POST['level']['hint']['time']['type'] ) ? gp_clean( $_POST['level']['hint']['time']['type'] ) : 'show';
			$level_settings['hint_time_minutes']   = isset( $_POST['level']['hint']['time']['minutes'] ) ? gp_clean( $_POST['level']['hint']['time']['minutes'] ) : 0;
			$level_settings['hint_time_seconds']   = isset( $_POST['level']['hint']['time']['seconds'] ) ? gp_clean( $_POST['level']['hint']['time']['seconds'] ) : 0;
			$level_settings['hint_penalty_points'] = isset( $_POST['level']['hint']['penalty_points'] ) ? gp_clean( $_POST['level']['hint']['penalty_points'] ) : 0;
		}

		//Check right settings
		if ( isset( $_POST['right'] ) ) {
			$right_settings = array(
				'desc'            => wp_kses_post( stripslashes_from_strings_only( $_POST['right']['desc'] ) ),
				'video_code'      => gp_clean( $_POST['right']['video']['code'] ),
				'video_auto_play' => isset( $_POST['right']['video']['auto_play'] ) ? 1 : 0,
				'video_mute'      => isset( $_POST['right']['video']['mute'] ) ? 1 : 0,
				'audio_audio_id'  => isset( $_POST['right']['audio']['audio_id'] ) ? gp_clean( $_POST['right']['audio']['audio_id'] ) : 0,
				'audio_auto_play' => isset( $_POST['right']['audio']['auto_play'] ) ? 1 : 0,
				'audio_once'      => isset( $_POST['right']['audio']['once'] ) ? 1 : 0,
				'gallery'         => isset( $_POST['right']['gallery'] ) ? gp_clean( $_POST['right']['gallery'] ) : array(),
				'order'           => gp_clean( $_POST['right']['order'] )
			);
		}

		//Check right settings
		if ( isset( $_POST['wrong'] ) ) {
			$wrong_settings = array(
				'desc'            => wp_kses_post( stripslashes_from_strings_only( $_POST['wrong']['desc'] ) ),
				'video_code'      => gp_clean( $_POST['wrong']['video']['code'] ),
				'video_auto_play' => isset( $_POST['wrong']['video']['auto_play'] ) ? 1 : 0,
				'video_mute'      => isset( $_POST['wrong']['video']['mute'] ) ? 1 : 0,
				'audio_audio_id'  => isset( $_POST['wrong']['audio']['audio_id'] ) ? gp_clean( $_POST['wrong']['audio']['audio_id'] ) : 0,
				'audio_auto_play' => isset( $_POST['wrong']['audio']['auto_play'] ) ? 1 : 0,
				'audio_once'      => isset( $_POST['wrong']['audio']['once'] ) ? 1 : 0,
				'gallery'         => isset( $_POST['wrong']['gallery'] ) ? gp_clean( $_POST['wrong']['gallery'] ) : array(),
				'order'           => gp_clean( $_POST['wrong']['order'] )
			);
		}

		if ( gp_notice_count( 'error' ) === 0 ) {

			foreach ( $level_settings as $meta_key => $meta_value ) {
				update_post_meta( get_the_ID(), '_level_' . $meta_key, $meta_value );
			}

			if ( isset( $right_settings ) ) {
				foreach ( $right_settings as $meta_key => $meta_value ) {
					update_post_meta( get_the_ID(), '_right_' . $meta_key, $meta_value );
				}
			}

			if ( isset( $wrong_settings ) ) {
				foreach ( $wrong_settings as $meta_key => $meta_value ) {
					update_post_meta( get_the_ID(), '_wrong_' . $meta_key, $meta_value );
				}
			}

			gp_add_notice( __( 'Level settings changed successfully.', 'game-portal' ) );

			if ( $_REQUEST['redirect_to'] ) {
				wp_redirect( gp_clean( $_REQUEST['redirect_to'] ) );
			}
		}
	}

	/**
	 * Process the login form.
	 */
	public static function process_login() {
		$nonce_value = $_POST['_wpnonce'] ?? '';
		$nonce_value = $_POST['game-portal-login-nonce'] ?? $nonce_value;

		if ( ! empty( $_POST['login'] ) && wp_verify_nonce( $nonce_value, 'game-portal-login' ) ) {

			try {
				$creds    = array();
				$username = trim( $_POST['username'] );

				$validation_error = new \WP_Error();
				$validation_error = apply_filters( 'game_portal_process_login_errors', $validation_error, $_POST['username'], $_POST['password'] );

				if ( $validation_error->get_error_code() ) {
					throw new \Exception( '<strong>' . __( 'Error', 'game-portal' ) . ':</strong> ' . $validation_error->get_error_message() );
				}

				if ( empty( $username ) ) {
					throw new \Exception( '<strong>' . __( 'Error', 'game-portal' ) . ':</strong> ' . __( 'Username is required.', 'game-portal' ) );
				}

				if ( empty( $_POST['password'] ) ) {
					throw new \Exception( '<strong>' . __( 'Error', 'game-portal' ) . ':</strong> ' . __( 'Password is required.', 'game-portal' ) );
				}

				if ( is_email( $username ) && apply_filters( 'game_portal_get_username_from_email', true ) ) {
					$user = get_user_by( 'email', $username );

					if ( isset( $user->user_login ) ) {
						$creds['user_login'] = $user->user_login;
					} else {
						throw new \Exception( '<strong>' . __( 'Error', 'game-portal' ) . ':</strong> ' . __( 'A user could not be found with this email address.', 'game-portal' ) );
					}

				} else {
					$creds['user_login'] = $username;
				}

				$creds['user_password'] = $_POST['password'];
				$creds['remember']      = isset( $_POST['rememberme'] );
				$secure_cookie          = is_ssl() ? true : false;
				$user                   = wp_signon( $creds, $secure_cookie );

				if ( is_wp_error( $user ) ) {
					$message = $user->get_error_message();
					$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $username ) . '</strong>', $message );
					throw new \Exception( $message );
				} else {

					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = $_POST['redirect'];
					} elseif ( wp_get_referer() ) {
						$redirect = wp_get_referer();
					} else {
						$redirect = gp_get_page_permalink( 'myaccount' );
					}

					wp_redirect( $redirect );
					exit;
				}

			}
			catch ( \Exception $e ) {
				gp_add_notice( apply_filters( 'login_errors', $e->getMessage() ), 'error' );
			}
		}
	}

	/**
	 * Handle lost password form.
	 */
	public static function process_lost_password() {
		if ( isset( $_POST['gp_reset_password'] ) && isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'lost_password' ) ) {
			$success = GP_Shortcode_My_Account::retrieve_password();

			// If successful, redirect to my account with query arg set
			if ( $success ) {
				wp_redirect( add_query_arg( 'reset-link-sent', 'true', remove_query_arg( array( 'key', 'login', 'reset' ) ) ) );
				exit;
			}
		}
	}

	/**
	 * Handle reset password form.
	 */
	public static function process_reset_password() {
		$posted_fields = array( 'gp_reset_password', 'password_1', 'password_2', 'reset_key', 'reset_login', '_wpnonce' );

		foreach ( $posted_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				return;
			}
			$posted_fields[ $field ] = $_POST[ $field ];
		}

		if ( ! wp_verify_nonce( $posted_fields['_wpnonce'], 'reset_password' ) ) {
			return;
		}

		$user = GP_Shortcode_My_Account::check_password_reset_key( $posted_fields['reset_key'], $posted_fields['reset_login'] );

		if ( $user instanceof \WP_User ) {
			if ( empty( $posted_fields['password_1'] ) ) {
				gp_add_notice( __( 'Please enter your password.', 'game-portal' ), 'error' );
			}

			if ( $posted_fields[ 'password_1' ] !== $posted_fields[ 'password_2' ] ) {
				gp_add_notice( __( 'Passwords do not match.', 'game-portal' ), 'error' );
			}

			$errors = new \WP_Error();

			do_action( 'validate_password_reset', $errors, $user );

			gp_add_wp_error_notices( $errors );

			if ( 0 === gp_notice_count( 'error' ) ) {
				GP_Shortcode_My_Account::reset_password( $user, $posted_fields['password_1'] );

				do_action( 'game_portal_player_reset_password', $user );

				wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login', 'reset-link-sent' ) ) ) );
				exit;
			}
		}
	}

	/**
	 * Process the registration form.
	 */
	public static function process_registration() {
		$nonce_value = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$nonce_value = isset( $_POST['game-portal-register-nonce'] ) ? $_POST['game-portal-register-nonce'] : $nonce_value;

		if ( ! empty( $_POST['register'] ) && wp_verify_nonce( $nonce_value, 'game-portal-register' ) ) {
			$account_first_name = ! empty( $_POST['account_first_name'] ) ? gp_clean( $_POST['account_first_name'] ) : '';
			$password = $_POST['password'];
			$email    = $_POST['email'];

			try {
				$validation_error = new \WP_Error();

				if ( $validation_error->get_error_code() ) {
					throw new \Exception( $validation_error->get_error_message() );
				}

				// Anti-spam trap
				if ( ! empty( $_POST['email_2'] ) ) {
					throw new \Exception( __( 'Anti-spam field was filled in.', 'game-portal' ) );
				}

				$new_player = gp_create_new_player( sanitize_email( $email ), '', $password );

				if ( is_wp_error( $new_player ) ) {
					throw new \Exception( $new_player->get_error_message() );
				}

				update_user_meta( $new_player, 'first_name', $account_first_name );

				gp_set_player_auth_cookie( $new_player );

				wp_safe_redirect( wp_get_referer() ? wp_get_referer() : gp_get_page_permalink( 'myaccount' ) );
				exit;

			}
			catch ( \Exception $e ) {
				gp_add_notice( '<strong>' . __( 'Error', 'game-portal' ) . ':</strong> ' . $e->getMessage(), 'error' );
			}
		}
	}
}
