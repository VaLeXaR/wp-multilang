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
 * Get flags dir path
 *
 * @return string
 */
function wpm_get_flags_path() {
	return apply_filters( 'wpm_flags_path', wpm()->flags_path() );
}


/**
 * Get flags dir url
 *
 * @return string
 */
function wpm_get_flags_dir() {
	return apply_filters( 'wpm_flags_dir', wpm()->flags_dir() );
}


/**
 * Get flag url
 *
 * @param string $filename
 *
 * @return string
 */
function wpm_get_flag_url( $filename = '' ) {
	return wpm_get_flags_dir() . $filename;
}


/**
 * Display language switcher in templates
 *
 * @param string $type
 * @param string $show
 *
 * @return string
 * @internal param array $args
 *
 */
function wpm_get_language_switcher( $type = 'list', $show = 'both' ) {

	$languages = wpm_get_languages();

	if ( count( $languages ) <= 1 ) {
		return '';
	}

	$vars = array(
		'languages'   => wpm_get_languages(),
		'lang'        => wpm_get_language(),
		'type'        => $type,
		'show'        => $show,
	);

	switch ( $type ) {

		case 'dropdown':
			$template = wpm_get_template( 'language-switcher', $type, '', $vars );
			break;

		case 'select':
			$template = wpm_get_template( 'language-switcher', $type, '', $vars );
			break;

		default:
			$template = wpm_get_template( 'language-switcher', $type, '', $vars );
	}

	return $template;
}


/**
 * Display language switcher
 *
 * @param string $type
 * @param string $show
 *
 * @internal param array $args
 */
function wpm_language_switcher( $type = 'list', $show = 'both' ) {
	echo wpm_get_language_switcher( $type, $show );
}


/**
 * Translate page titles
 */
add_filter( 'document_title_parts', 'wpm_translate_value', 5 );


/**
 * Add meta params to 'head'
 */
function wpm_set_alternate_links() {
	$languages   = array();

	if ( is_single() ) {
		$languages = get_post_meta( get_the_ID(), '_languages', true );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$languages = get_term_meta( get_queried_object_id(), '_languages', true );
	}

	$hreflangs = array();

	foreach ( wpm_get_languages() as $code => $language ) {

		if ( $languages && ! isset( $languages[ $code ] ) ) {
			continue;
		}

		if ( wpm_get_default_language() === $code ) {
			$hreflangs['x-default'] = sprintf( "<link rel=\"alternate\" hreflang=\"x-default\" href=\"%s\"/>\n", esc_url( wpm_translate_current_url( $code ) ) );
		}

		$hreflangs[ $code ] = sprintf( "<link rel=\"alternate\" hreflang=\"%s\" href=\"%s\"/>\n", esc_attr( wpm_sanitize_lang_slug( $language['locale'] ) ), esc_url( wpm_translate_current_url( $code ) ) );
	}

	$hreflangs = apply_filters( 'wpm_alternate_links', $hreflangs, wpm_get_current_url() );

	echo implode( '', $hreflangs );
}

add_action( 'wp_head', 'wpm_set_alternate_links' );


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
function wpm_media_image( $html, $attr, $content ) {

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
		$html .= '<figure ' . $atts['id'] . $style . ' class="' . esc_attr( $class ) . '">' . do_shortcode( $content ) . '<figcaption class="wp-caption-text">' . $atts['caption'] . '</figcaption></figure>';
	} else {
		$html .= '<div ' . $atts['id'] . $style . ' class="' . esc_attr( $class ) . '">' . do_shortcode( $content ) . '<p class="wp-caption-text">' . $atts['caption'] . '</p></div>';
	}

	return $html;
}

add_filter( 'img_caption_shortcode', 'wpm_media_image', 10, 3 );


/**
 * Redeclare gallery code for translate caption text
 *
 * @param string $html
 * @param array $attr
 * @param $instance
 *
 * @return string
 */
function wpm_media_gallery( $html, $attr, $instance ) {
	$post  = get_post();
	$html5 = current_theme_supports( 'html5', 'gallery' );
	$atts  = shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post ? $post->ID : 0,
		'itemtag'    => $html5 ? 'figure' : 'dl',
		'icontag'    => $html5 ? 'div' : 'dt',
		'captiontag' => $html5 ? 'figcaption' : 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => '',
		'link'       => ''
	), $attr, 'gallery' );

	$id = (int)$atts['id'];

	if ( ! empty( $atts['include'] ) ) {
		$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = wpm_translate_post( $_attachments[$key] );
		}
	} elseif ( ! empty( $atts['exclude'] ) ) {
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	} else {
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	}

	if ( empty( $attachments ) ) {
		return '';
	}

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";
		}
		return $output;
	}

	$itemtag = tag_escape( $atts['itemtag'] );
	$captiontag = tag_escape( $atts['captiontag'] );
	$icontag = tag_escape( $atts['icontag'] );
	$valid_tags = wp_kses_allowed_html( 'post' );
	if ( ! isset( $valid_tags[ $itemtag ] ) ) {
		$itemtag = 'dl';
	}
	if ( ! isset( $valid_tags[ $captiontag ] ) ) {
		$captiontag = 'dd';
	}
	if ( ! isset( $valid_tags[ $icontag ] ) ) {
		$icontag = 'dt';
	}

	$columns = (int)$atts['columns'];
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	$float = is_rtl() ? 'right' : 'left';

	$selector = "gallery-{$instance}";

	$gallery_style = '';

	/**
	 * Filters whether to print default gallery styles.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $print Whether to print default gallery styles.
	 *                    Defaults to false if the theme supports HTML5 galleries.
	 *                    Otherwise, defaults to true.
	 */
	if ( apply_filters( 'use_default_gallery_style', ! $html5 ) ) {
		$gallery_style = "
		<style type='text/css'>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				float: {$float};
				margin-top: 10px;
				text-align: center;
				width: {$itemwidth}%;
			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
			/* see gallery_shortcode() in wp-includes/media.php */
		</style>\n\t\t";
	}

	$size_class = sanitize_html_class( $atts['size'] );
	$gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

	/**
	 * Filters the default gallery shortcode CSS styles.
	 *
	 * @since 2.5.0
	 *
	 * @param string $gallery_style Default CSS styles and opening HTML div container
	 *                              for the gallery shortcode output.
	 */
	$output = apply_filters( 'gallery_style', $gallery_style . $gallery_div );

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {

		$attr = ( trim( $attachment->post_excerpt ) ) ? array( 'aria-describedby' => "$selector-$id" ) : '';
		if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
			$image_output = wp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
		} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
			$image_output = wp_get_attachment_image( $id, $atts['size'], false, $attr );
		} else {
			$image_output = wp_get_attachment_link( $id, $atts['size'], true, false, false, $attr );
		}
		$image_meta  = wp_get_attachment_metadata( $id );

		$orientation = '';
		if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
			$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
		}
		$output .= "<{$itemtag} class='gallery-item'>";
		$output .= "
			<{$icontag} class='gallery-icon {$orientation}'>
				$image_output
			</{$icontag}>";
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "
				<{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
				" . wptexturize($attachment->post_excerpt) . "
				</{$captiontag}>";
		}
		$output .= "</{$itemtag}>";
		if ( ! $html5 && $columns > 0 && ++$i % $columns == 0 ) {
			$output .= '<br style="clear: both" />';
		}
	}

	if ( ! $html5 && $columns > 0 && $i % $columns !== 0 ) {
		$output .= '<br style="clear: both" />';
	}

	$output .= "</div>\n";

	return $output;
}

add_filter( 'post_gallery', 'wpm_media_gallery', 10, 3 );


/**
 * Add lang class to body
 *
 * @param array $classes
 *
 * @return array
 */
function wpm_add_body_class( $classes ) {
	$classes[] = 'language-' . wpm_get_language();

	return $classes;
}

add_filter( 'body_class', 'wpm_add_body_class' );


/**
 * Display language switcher in admin
 */
function wpm_admin_language_switcher() {

	$args = array(
		'languages' => wpm_get_languages(),
		'lang'      => wpm_get_language(),
	);

	echo wpm_get_template( 'admin-language-switcher', '', '', $args );
}


/**
 * Display language switcher in customizer
 */
function wpm_admin_language_switcher_customizer() {

	$args = array(
		'languages' => wpm_get_languages(),
		'lang'      => wpm_get_language(),
	);

	echo wpm_get_template( 'admin-language-switcher', 'customizer', '', $args );
}


/**
 * Get flag list
 *
 * @since  2.0.0
 *
 * @return array
 */
function wpm_get_flags() {
	$flags      = array();
	$flags_path = wpm_get_flags_path();
	if ( $dir_handle = @opendir( $flags_path ) ) {
		while ( false !== ( $file = readdir( $dir_handle ) ) ) {
			if ( preg_match( "/\.(jpeg|jpg|gif|png|svg)$/i", $file ) ) {
				$flags[] = $file;
			}
		}
		sort( $flags );
	}

	return $flags;
}


/**
 * Show notice for strings that cant`t be translated for displaying in admin.
 *
 * @param bool $echo
 *
 * @return string
 */
function wpm_show_notice( $echo = true ) {
	$notise = '<div class="notice notice-info inline"><p>' . sprintf( esc_attr__( 'For multilingual string, use syntax like %s.', 'wp-multilang' ), '<code>[:en]Text on english[:de]Text auf Deutsch[:]</code>' ) . '</p></div>';
	if ( $echo ) {
		echo $notise;
	} else {
		return $notise;
	}
}
