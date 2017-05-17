<?php

namespace WPM\Core\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Widget_Language_Switcher extends \WPM_Widget {

	public function __construct() {
		$this->widget_cssclass    = 'wpm widget_language_switcher';
		$this->widget_description = '';
		$this->widget_id          = 'wpm_widget_language_switcher';
		$this->widget_name        = __( 'Language Switcher', 'wpm' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Languages', 'wpm' ),
				'label' => __( 'Title', 'wpm' )
			),
			'flags' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Enable Flags', 'wpm' )
			),
			'name'  => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Enable Names', 'wpm' )
			),
			'type'  => array(
				'type'    => 'select',
				'std'     => 'list',
				'options' => array(
					'list'     => __( 'List', 'wpm' ),
					'dropdown' => __( 'Dropdown', 'wpm' ),
				),
				'label'   => __( 'Switcher Type', 'wpm' )
			)
		);
		parent::__construct();
	}


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
