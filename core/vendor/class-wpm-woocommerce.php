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
		add_filter( 'woocommerce_cart_shipping_method_full_label', 'wpm_translate_string' );
		add_filter( 'woocommerce_shipping_instance_form_fields_flat_rate', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_free_shipping', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_flat_rate', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_free_shipping', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_international_delivery', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_local_delivery', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_local_pickup', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_local_pickup', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_free_shipping_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_flat_rate_instance_settings_values', array ($this, 'update_shipping_settings'), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_flat_rate_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_free_shipping_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_international_delivery_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_local_delivery_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_local_pickup_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_local_pickup_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_zone_shipping_methods', array( $this, 'translate_zone_shipping_methods') );
		add_filter( 'woocommerce_gateway_method_title', 'wpm_translate_string' );
		add_filter( 'woocommerce_gateway_method_description', 'wpm_translate_string' );
	}


	/**
	 * Add script for reload cart after change language
	 */
	public function enqueue_js_frontend() {
		if ( did_action( 'wpm_changed_language' ) ) {
			wp_add_inline_script( 'wc-cart-fragments', "
				jQuery( function ( $ ) {
					$( document.body ).trigger( 'wc_fragment_refresh' );
				});
			");
		}
	}

	/**
	 * Set translate in settings
	 *
	 * @param array $settings
	 * @param object $shipping
	 *
	 * @return array
	 */
	public function update_shipping_settings($settings, $shipping) {

		$old_settings = get_option( $shipping->get_instance_option_key(), null );

		$strings = array();

		if ( $old_settings ) {
			$strings  = wpm_value_to_ml_array( $old_settings );
		}

		$setting_config = array(
			'title' => array()
		);

		$new_value    = wpm_set_language_value( $strings, $settings, $setting_config );
		$new_settings = wpm_ml_value_to_string( $new_value );

		return $new_settings;
	}

	/**
	 * Translate methods for zone
	 *
	 * @param array $methods
	 *
	 * @return array
	 */
	public function translate_zone_shipping_methods( $methods ) {

		foreach ( $methods as &$method ) {
			$method->title = wpm_translate_string( $method->title );
		}

		return $methods;
	}
}

new WPM_WooCommerce();
