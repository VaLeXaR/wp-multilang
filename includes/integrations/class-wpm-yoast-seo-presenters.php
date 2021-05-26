<?php
/**
 * Creates an Opengraph alternate locale meta tag to be consumed by Yoast SEO
 * Requires Yoast SEO 14.0 or newer.
 */
namespace WPM\Includes\Integrations;
use Yoast\WP\SEO\Presenters\Abstract_Indexable_Presenter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class    WPM_Yoast_Seo_Presenters
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Damir Calusic
 */
class WPM_Yoast_Seo_Presenters extends Abstract_Indexable_Presenter {

	/**
	 * Facebook locale
	 *
	 * @var string $locale
	 */
	private $locale;

	/**
	 * Constructor
	 *
	 * @param string $locale Facebook locale.
	 * @since 2.4.2
	 */
	public function __construct( $locale ) {
		$this->locale = $locale;
	}

	/**
	 * Returns the meta Opengraph alternate locale meta tag
	 *
	 * @return string
	 * @since 2.4.2
	 */
	public function present() {
		return sprintf( '<meta property="og:locale:alternate" content="%s" />', esc_attr( $this->get() ) );
	}

	/**
	 * Returns the alternate locale
	 *
	 * @return string
	 * @since 2.4.2
	 */
	public function get() {
		return $this->locale;
	}
}
