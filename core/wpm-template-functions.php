<?php
/**
 * WPM Template functions
 *
 * Functions for using in template.
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       WPM/Functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display language switcher in templates
 *
 * @param array $args
 * @param bool  $echo
 *
 * @return string
 */
function wpm_language_switcher( $args = array(), $echo = true ) {
	$default = array(
		'type' => 'list',
		'show' => 'both',
	);
	$args    = wp_parse_args( $args, $default );

	$languages = wpm_get_languages();

	if ( count( $languages ) <= 1 ) {
		return '';
	}

	$options     = wpm_get_options();
	$current_url = wpm_get_current_url();
	$locale      = get_locale();
	ob_start();
	if ( 'list' === $args['type'] ) { ?>
		<ul class="wpm-language-switcher switcher-<?php esc_attr_e( $args['type'] ); ?>">
			<?php foreach ( $languages as $key => $language ) { ?>
				<li class="item-language-<?php esc_attr_e( $options[ $key ]['slug'] );?><?php if ( $key === $locale ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( wpm_translate_url( $current_url, $language ) ); ?>">
						<?php if ( ( ( 'flag' === $args['show'] ) || ( 'both' === $args['show'] ) ) && ( $options[ $key ]['flag'] ) ) { ?>
							<img src="<?php echo esc_url( WPM()->flag_dir() . $options[ $key ]['flag'] . '.png' ); ?>"
							    alt="<?php esc_attr_e( $options[ $key ]['name'] ); ?>">
						<?php } ?>
						<?php if ( ( 'name' === $args['show'] ) || ( 'both' === $args['show'] ) ) { ?>
							<span><?php esc_attr_e( $options[ $key ]['name'] ); ?></span>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	<?php }

	if ( 'dropdown' === $args['type'] ) { ?>
		<select class="wpm-language-switcher switcher-<?php esc_attr_e( $args['type'] ); ?>" onchange="location = this.value;" title="<?php esc_html_e( __( 'Language Switcher', 'wpm' ) ); ?>">
			<?php foreach ( $languages as $key => $language ) { ?>
				<option value="<?php echo esc_url( wpm_translate_url( $current_url, $language ) ); ?>"<?php if ( $key === $locale ) { ?> selected="selected"<?php } ?>>
					<?php echo $options[ $key ]['name']; ?>
				</option>
			<?php } ?>
		</select>
	<?php }

	$content = ob_get_contents();
	ob_end_clean();

	if ( $echo ) {
		echo $content;
	} else {
		return $content;
	}
}


add_filter( 'localization', 'wpm_translate_string' );
add_filter( 'gettext', 'wpm_translate_string' );

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
		$html = '<figure ' . $atts['id'] . $style . ' class="' . esc_attr( $class ) . '">' . do_shortcode( $content ) . '<figcaption class="wp-caption-text">' . $atts['caption'] . '</figcaption></figure>';
	} else {
		$html = '<div ' . $atts['id'] . $style . ' class="' . esc_attr( $class ) . '">' . do_shortcode( $content ) . '<p class="wp-caption-text">' . $atts['caption'] . '</p></div>';
	}

	return $html;
}

add_filter( 'img_caption_shortcode', 'wpm_generate_widget_media_image', 10, 3 );

function wpm_add_body_class( $classes ) {
	$classes[] = 'language-' . wpm_get_language();

	return $classes;
}

add_filter( 'body_class', 'wpm_add_body_class' );
