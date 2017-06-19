<?php
/**
 * Language switcher widget for frontend
 */
namespace WPM\Core\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class WPM_Widget_Language_Switcher
 * @package WPM\Core\Widgets
 * @category Class
 * @author   VaLeXaR
 */
class WPM_Widget_Language_Switcher extends \WPM_Widget {

	/**
	 * WPM_Widget_Language_Switcher constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wpm widget_language_switcher';
		$this->widget_description = '';
		$this->widget_id          = 'wpm_language_switcher';
		$this->widget_name        = __( 'Language Switcher', 'wpm' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Languages', 'wpm' ),
				'label' => __( 'Title', 'wpm' ),
			),
			'show'  => array(
				'type'    => 'select',
				'std'     => 'both',
				'options' => array(
					'both'     => __( 'Both', 'wpm' ),
					'flag'     => __( 'Flag', 'wpm' ),
					'name' => __( 'Name', 'wpm' ),
				),
				'label'   => __( 'Show', 'wpm' ),
			),
			'type'  => array(
				'type'    => 'select',
				'std'     => 'list',
				'options' => array(
					'list'     => __( 'List', 'wpm' ),
					'dropdown' => __( 'Dropdown', 'wpm' ),
				),
				'label'   => __( 'Switcher Type', 'wpm' ),
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

		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		$this->widget_start( $args, $instance );

		wpm_language_switcher( $instance );

		$this->widget_end( $args );

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );

	}
}
