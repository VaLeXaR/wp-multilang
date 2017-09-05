<?php
/**
 * Class for capability with WooCommerce
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WC_VERSION' ) ) {
	return;
}

/**
 * Class WPM_WooCommerce
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 */
class WPM_WooCommerce {

	/**
	 * WPM_WooCommerce constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_frontend' ) );
	}

	public function enqueue_js_frontend() {
		if ( did_action( 'wpm_changed_language' ) ) {
			wp_add_inline_script( 'wc-cart-fragments', "
				jQuery( function ( $ ) {
					$( document.body ).trigger( 'wc_fragment_refresh' );
				});
			");
		}
	}
}

new WPM_WooCommerce();
