<?php
/**
 * Handles migration of qTranslate / qTranslate-X stuff.
 *
 * @author   Soft79
 * @category Admin
 * @package  WPM/Includes/Admin
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Admin_Qtranslate {

	private $notices = array();
	private $qtranslate_terms = array();

	const OPTION_IMPORTING = 'wpm_qtranslate_importing';
	const OPTION_QTRANSLATE_TERM_NAME = 'qtranslate_term_name';

	/**
	 * WPM_Admin_Qtx constructor.
	 */
	public function __construct() {
		if ( current_user_can( 'manage_translations' ) ) {
			add_action( 'wp_loaded', array( $this, 'handle_qtranslate' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	//ACTIONS

	/**
	 * Handle qTranslate admin stuff
	 */
	public function handle_qtranslate() {
		//qTranslate must be disabled
		if ( $qtranslate = $this->detect_qtranslate() ) {
			$this->enqueue_notice( sprintf( __( '%s is active. Please deactivate it.', 'wp-multilang' ), $qtranslate ), 'notice-error' );

			return;
		}

		//If there are no translations available, skip everything
		if ( ! $this->get_qtranslate_terms() ) {
			return;
		}

		if ( isset( $_GET['wpm-qtranslate-import'] ) && check_admin_referer( 'wpm-qtranslate-import' ) ) {
			$this->execute_import();

			return;
		}

		if ( get_option( self::OPTION_IMPORTING ) ) {
			return;
		}

		WPM_Admin_Notices::add_custom_notice( 'qtranslate_import', sprintf( __( 'qTranslate term translations found. Please click <a href="%s">here</a> to migrate them to WP Multilang.', 'wp-multilang' ), wp_nonce_url( add_query_arg( 'wpm-qtranslate-import', 'true' ), 'wpm-qtranslate-import' ) ) );
	}


	/**
	 * Display enqueued notices
	 */
	public function admin_notices() {
		foreach( $this->notices as $notice ) {
			echo '<div class="notice ' . $notice['class'] . ' is-dismissible"><p>';
			echo sprintf( '<strong>%s</strong> &#8211; %s', __( 'WP Multilang', 'wp-multilang' ), wp_kses_post( $notice['html'] ) );
			echo '</p></div>';
		}
		$this->notices = array();
	}

	//LOGIC

	/**
	 * Read term translations from qTranslate / qTranslate-X and save them wp-multilang-style
	 * @return void
	 */
	private function execute_import() {
		$n_errors = 0;
		$n_ok = 0;

		update_option( self::OPTION_IMPORTING, true, false );

		$qtranslate_terms = $this->get_qtranslate_terms();

		$taxonomies = get_taxonomies();

		$terms = get_terms( $taxonomies );
		foreach ( $terms as $term ) {
			$original = $term->name;

			//Translation available?
			if ( ! isset( $qtranslate_terms[ $original ] ) ) {
				continue;
			}

			//Translate the name
			$strings = wpm_value_to_ml_array( $original );
			foreach ( $qtranslate_terms[ $original ] as $lang => $translation ) {
				$strings = wpm_set_language_value( $strings, $translation, array(), $lang );
			}

			//Update the name
			$term->name = wpm_ml_value_to_string( $strings );
			if ( $term->name !== $original ) {
				$result = wp_update_term( $term->term_id, $term->taxonomy, array( 'name' => $term->name ) );
				if ( is_wp_error( $result ) ) {
					error_log( sprintf( "Error updating term %s: %s", $original, $result->get_error_message() ) );
					$n_errors++;
				} else {
					$n_ok++;
				}
			}
		}

		if ( $n_errors ) {
			$msg = __( 'Something went while importing qTranslate term translations.', 'wp-multilang' );
			$this->enqueue_notice( $msg, 'notice-error' );
		}

		if ( $n_ok ) {
			$this->enqueue_notice( sprintf( __( '%d terms were imported succesfully.', 'wp-multilang' ), $n_ok ), 'notice-info' );

			//Hide the notice
			delete_option( self::OPTION_QTRANSLATE_TERM_NAME );
		}

		delete_option( self::OPTION_IMPORTING );
	}


	/**
	 * Detects whether qTranslate or qTranslate-X is active.
	 * Returns the name of the plugin if it's detected, false otherwise.
	 *
	 * @return bool|string Either false or the plugin name
	 */
	private function detect_qtranslate() {
		if ( defined( 'QTX_VERSION' ) ) {
			return 'qTranslate-X';
		}
		if ( defined( 'QT_SUPPORTED_WP_VERSION' ) ) {
			return 'qTranslate';
		}

		return false;
	}

	/**
	 * Gets the term translations as stored by qTranslate / qTranslate-X
	 * @return array
	 */
	private function get_qtranslate_terms() {
		if ( ! isset( $this->qtranslate_terms ) ) {
			$this->qtranslate_terms = get_option( self::OPTION_QTRANSLATE_TERM_NAME, array() );
		}
		return $this->qtranslate_terms;
	}

	/**
	 * Enqueue a notice to display on the admin page
	 * @param string $html
	 * @param string $class
	 */
	private function enqueue_notice( $html, $class = 'notice-info' ) {
		$this->notices[] = array( 'class' => $class, 'html' => $html );
	}

}
