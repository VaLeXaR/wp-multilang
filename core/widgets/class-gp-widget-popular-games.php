<?php

namespace GP\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GP_Widget_Popular_Games extends \GP_Widget {

	public function __construct() {
		$this->widget_cssclass    = 'game_portal widget_popular_games';
		$this->widget_description = '';
		$this->widget_id          = 'game_portal_widget_popular_games';
		$this->widget_name        = __( 'Popular Games', 'game-portal' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Popular Games', 'game-portal' ),
				'label' => __( 'Title', 'game-portal' )
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

		gp_get_template( 'widgets/widget-popular-games.php' );

		$this->widget_end( $args );

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );

	}
}
