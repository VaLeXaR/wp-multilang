<?php
/**
 * Language switcher widget for frontend
 */
namespace WPM\Includes\Widgets;
use WPM\Includes\Abstracts\WPM_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class WPM_Widget_Language_Switcher
 * @package WPM/Includes/Widgets
 * @category Class
 * @author   Valentyn Riaboshtan
 */
class WPM_Widget_Language_Switcher extends WPM_Widget {

	/**
	 * WPM_Widget_Language_Switcher constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wpm widget_language_switcher';
		$this->widget_description = __( 'Display language switcher.', 'wp-multilang' );
		$this->widget_id          = 'wpm_language_switcher';
		$this->widget_name        = __( 'Language Switcher', 'wp-multilang' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Languages', 'wp-multilang' ),
				'label' => __( 'Title', 'wp-multilang' ),
			),
			'show'  => array(
				'type'    => 'select',
				'std'     => 'both',
				'options' => array(
					'both'     => __( 'Both', 'wp-multilang' ),
					'flag'     => __( 'Flag', 'wp-multilang' ),
					'name' => __( 'Name', 'wp-multilang' ),
				),
				'label'   => __( 'Show', 'wp-multilang' ),
			),
			'type'  => array(
				'type'    => 'select',
				'std'     => 'list',
				'options' => array(
					'list'     => __( 'List', 'wp-multilang' ),
					'dropdown' => __( 'Dropdown', 'wp-multilang' ),
					'select'   => __( 'Select', 'wp-multilang' ),
				),
				'label'   => __( 'Switcher Type', 'wp-multilang' ),
			),
		);
		parent::__construct();
	}

	/**
	 * Display language switcher
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		ob_start();

		$this->widget_start( $args, $instance );

		wpm_language_switcher( $instance['type'], $instance['show'] );

		$this->widget_end( $args );

		$content = ob_get_clean();

		echo $content;

	}
}
