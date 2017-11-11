<?php

use WPM\Includes\WPM_Setup;

/**
 * Get config
 *
 * @see WPM_Setup::get_config()
 *
 * @return array
 */
function wpm_get_config() {
	return WPM_Setup::instance()->get_config();
}

/**
 * Get post type config
 *
 * @param $post_type
 *
 * @return array
 */
function wpm_get_post_config( $post_type ) {
	$config       = wpm_get_config();
	$posts_config = apply_filters( 'wpm_posts_config', $config['post_types'] );

	return apply_filters( "wpm_post_{$post_type}_config", isset( $posts_config[ $post_type ] ) ? $posts_config[ $post_type ] : null );
}

/**
 * Get taxonomy config
 *
 * @param $taxonomy
 *
 * @return array
 */
function wpm_get_taxonomy_config( $taxonomy ) {
	$config            = wpm_get_config();
	$taxonomies_config = apply_filters( 'wpm_taxonomies_config', $config['taxonomies'] );

	return apply_filters( "wpm_taxonomy_{$taxonomy}_config", isset( $taxonomies_config[ $taxonomy ] ) ? $taxonomies_config[ $taxonomy ] : null );
}

/**
 * Get widget congi
 *
 * @param $widget_id
 *
 * @return mixed|void
 */
function wpm_get_widget_config( $widget_id ) {
	$config         = wpm_get_config();
	$widgets_config = apply_filters( 'wpm_widgets_config', $config['widgets'] );

	return apply_filters( "wpm_widget_{$widget_id}_config", isset( $widgets_config[ $widget_id ] ) ? $widgets_config[ $widget_id ] : null );
}
