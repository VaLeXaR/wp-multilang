<?php
/**
 * Comment Languages
 *
 * @author   Valentyn Riaboshtan
 * @category Admin
 * @package  WPM/Includes/Admin
 * @version  1.0.3
 */

namespace WPM\Includes\Admin\Meta_Boxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Meta_Box_Comment_Languages Class.
 */
class WPM_Meta_Box_Comment_Languages {

	/**
	 * Output the metabox.
	 *
	 * @param object $comment
	 */
	public static function output( $comment ) {
		$comment_languages = get_comment_meta( $comment->comment_ID, '_languages', true );

		if ( ! is_array( $comment_languages ) ) {
			$comment_languages = array();
		}

		$languages = wpm_get_languages();
		$i         = 0;
		?>
		<h4><?php _e( 'Show comment only in:', 'wp-multilang' ); ?></h4>
		<ul class="languagechecklist">
			<?php foreach ( $languages as $code => $language ) { ?>
				<li>
					<label>
						<input type="checkbox" name="wpm_languages[<?php echo esc_attr( $i ); ?>]" id="wpm-languages-<?php echo esc_attr( $code ); ?>" value="<?php echo esc_attr( $code ); ?>"<?php checked( in_array( $code, $comment_languages ) ); ?>>
						<?php esc_html_e( $language['name'] ); ?>
					</label>
				</li>
				<?php $i++; } ?>
		</ul>
		<?php
		wp_nonce_field( 'wpm_save_data', 'wpm_meta_nonce' );
	}

	/**
	 * Save meta box data.
	 *
	 * @param $location
	 * @param int $comment_id
	 *
	 * @return mixed
	 */
	public static function save( $location, $comment_id ) {

		if ( ! wp_verify_nonce( $_POST['wpm_meta_nonce'], 'wpm_save_data' ) ) {
			return $location;
		}

		if ( $languages = wpm_get_post_data_by_key( 'wpm_languages' ) ) {
			update_comment_meta( $comment_id, '_languages', $languages );
		} else {
			delete_comment_meta( $comment_id, '_languages' );
		}

		$comment = get_comments( $comment_id );
		wp_cache_delete( $comment->comment_post_ID, 'wpm_comment_count' );

		// Return regular value after updating
		return $location;
	}
}
