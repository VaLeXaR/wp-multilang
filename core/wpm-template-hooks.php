<?php
/**
 * Hooks for html
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       WPM/Hooks
 * @version       1.0.2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Translate page titles
 */
add_filter( 'document_title_parts', 'wpm_translate_value', 0 );

/**
 * Add meta params to 'head'
 */
function wpm_set_meta_languages() {
	$current_url = wpm_get_current_url();
	foreach ( wpm_get_languages() as $locale => $language ) {
		if ( get_locale() != $locale ) {
			printf( '<link rel="alternate" hreflang="%s" href="%s"/>', esc_attr( str_replace( '_', '-', strtolower( $locale ) ) ), esc_url( wpm_translate_url( $current_url, $language ) ) );
		}

		if ( wpm_get_default_locale() == $locale ) {
			printf( '<link rel="alternate" hreflang="x-default" href="%s"/>', esc_url( wpm_translate_url( $current_url, $language ) ) );
		}
	}
}

add_action( 'wp_head', 'wpm_set_meta_languages', 0 );

/**
 * Fix for generation image.
 * use get_post
 *
 * @param $html
 * @param $attr
 * @param $content
 *
 * @return string
 */
function wpm_generate_widget_media_image( $html, $attr, $content ) {

	$atts = shortcode_atts( array(
		'id'      => '',
		'align'   => 'alignnone',
		'width'   => '',
		'caption' => '',
		'class'   => '',
	), $attr, 'caption' );

	$atts['caption'] = wpm_translate_string( $atts['caption'] );
	$atts['width']   = (int) $atts['width'];
	if ( $atts['width'] < 1 || empty( $atts['caption'] ) ) {
		return $content;
	}

	if ( ! empty( $atts['id'] ) ) {
		$atts['id'] = 'id="' . esc_attr( sanitize_html_class( $atts['id'] ) ) . '" ';
	}

	$class = trim( 'wp-caption ' . $atts['align'] . ' ' . $atts['class'] );

	$html5 = current_theme_supports( 'html5', 'caption' );
	// HTML5 captions never added the extra 10px to the image width
	$width = $html5 ? $atts['width'] : ( 10 + $atts['width'] );

	/**
	 * Filters the width of an image's caption.
	 *
	 * By default, the caption is 10 pixels greater than the width of the image,
	 * to prevent post content from running up against a floated image.
	 *
	 * @since 3.7.0
	 *
	 * @see   img_caption_shortcode()
	 *
	 * @param int    $width    Width of the caption in pixels. To remove this inline style,
	 *                         return zero.
	 * @param array  $atts     Attributes of the caption shortcode.
	 * @param string $content  The image element, possibly wrapped in a hyperlink.
	 */
	$caption_width = apply_filters( 'img_caption_shortcode_width', $width, $atts, $content );

	$style = '';
	if ( $caption_width ) {
		$style = 'style="width: ' . (int) $caption_width . 'px" ';
	}

	if ( $html5 ) {
		$html = '<figure ' . $atts['id'] . $style . ' class="' . esc_attr( $class ) . '">'
		        . do_shortcode( $content ) . '<figcaption class="wp-caption-text">' . $atts['caption'] . '</figcaption></figure>';
	} else {
		$html = '<div ' . $atts['id'] . $style . ' class="' . esc_attr( $class ) . '">'
		        . do_shortcode( $content ) . '<p class="wp-caption-text">' . $atts['caption'] . '</p></div>';
	}

	return $html;
}

add_filter( 'img_caption_shortcode', 'wpm_generate_widget_media_image', 10, 3 );
