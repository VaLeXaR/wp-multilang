<?php
/**
* Class for capability with Elementor
*/

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
* @class    WPM_Elementor
* @package  WPM/Includes/Integrations
* @category Integrations
* @author   Auban le Grelle
*/
class WPM_Elementor {

	const ELEMENTOR_DATA_META_KEY = '_elementor_data';
	const ELEMENTOR_CSS_META_KEY = '_elementor_css';
	const ELEMENTOR_CONTROLS_META_KEY = '_elementor_controls_usage';

	private $object_id = 0;

	/**
	 * WPM_Elementor constructor.
	 */
	public function __construct() {

		$meta_keys = array(
			self::ELEMENTOR_DATA_META_KEY => array(
				'set_data_value',
				'get_data_value'
			),
			self::ELEMENTOR_CSS_META_KEY => array(
				'set_css_value',
				'get_css_value'
			),
			self::ELEMENTOR_CONTROLS_META_KEY => array(
				'set_controls_value',
				'get_controls_value'
			),
		);

		//Install meta Filters
		foreach ($meta_keys as $meta_key => $callbacks) {

			add_filter( "wpm_{$meta_key}_meta_config", 			array($this, 'config'), 10, 3 );
			add_filter( "wpm_add_{$meta_key}_meta_value", 		array($this, $callbacks[0]), 10, 1 );
			add_filter( "wpm_update_{$meta_key}_meta_value", 	array($this, $callbacks[0]), 10, 1 );
			add_filter( "wpm_get_{$meta_key}_meta_value", 		array($this, $callbacks[1]), 10, 1 );
		}

		add_filter( 'elementor/files/file_name', array($this, 'format_css_filename'), 10, 2);
	}

	/**
	 * Set meta translate in base64
	 *
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	private function set_value($key, $value) {

		if (!$this->object_id) {
			return $value;
		}

		$current_value = get_post_meta($this->object_id, "{$key}_translate", true);

		update_post_meta($this->object_id, "{$key}_translate", wpm_set_new_value($current_value, base64_encode($value)));

		$this->object_id = 0;

		return $value;
	}

	/**
	 * Get meta translate from base64
	 *
	 * @param $key
	 * @param $value
	 * @return false|string
	 */
	private function get_value($key, $value) {

		if (!$this->object_id) {
			return $value;
		}

		$tr_value = base64_decode(wpm_translate_value(get_post_meta($this->object_id, "{$key}_translate", true)), true);

		$this->object_id = 0;

		return ($tr_value === false ? $value : $tr_value);
	}

	/**
	 * Config meta keys
	 *
	 * @param $config
	 * @param $meta_value
	 * @param $object_id
	 * @return mixed
	 */
	public function config($config, $meta_value, $object_id) {

		$this->object_id = $object_id;

		return $config;
	}

	/**
	 * Set meta value data
	 *
	 * @param $value
	 * @return mixed
	 */
	public function set_data_value($value) {

		$key = self::ELEMENTOR_DATA_META_KEY;

		return $this->set_value($key, $value);
	}

	/**
	 * Get meta value data
	 *
	 * @param $value
	 * @return false|string
	 */
	public function get_data_value($value) {

		$key = self::ELEMENTOR_DATA_META_KEY;

		return $this->get_value($key, $value);
	}

	/**
	 * Set meta value css
	 *
	 * @param $value
	 * @return mixed
	 */
	public function set_css_value($value) {

		$key = self::ELEMENTOR_CSS_META_KEY;

		$this->set_value($key, maybe_serialize($value));

		return $value;
	}

	/**
	 * Get meta value css
	 *
	 * @param $value
	 * @return mixed
	 */
	public function get_css_value($value) {

		$key = self::ELEMENTOR_CSS_META_KEY;

		return maybe_unserialize($this->get_value($key, $value));
	}

	/**
	 * Set meta value controls
	 *
	 * @param $value
	 * @return mixed
	 */
	public function set_controls_value($value) {

		$key = self::ELEMENTOR_CONTROLS_META_KEY;

		$this->set_value($key, maybe_serialize($value));

		return $value;
	}

	/**
	 * Get meta value controls
	 *
	 * @param $value
	 * @return mixed
	 */
	public function get_controls_value($value) {

		$key = self::ELEMENTOR_CONTROLS_META_KEY;

		return maybe_unserialize($this->get_value($key, $value));
	}

	/**
	 * Format the css file name with the current language.
	 *
	 * @param $filename
	 * @param $css
	 * @return mixed
	 */
	public function format_css_filename ($filename, $css) {

		$current_lang = wpm_get_language();

		if (strpos($filename, 'post') === 0) {

			return str_replace('.css', "-{$current_lang}.css", $filename);
		}

		return $filename;
	}
}
