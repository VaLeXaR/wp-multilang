<?php
/**
 * Languages
 *
 * @author        VaLeXaR
 * @category      Admin
 * @package       WPM/Admin/Meta Boxes
 */

namespace WPM\Core\Admin\Meta_Boxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Meta_Box_Languages Class.
 */
class WPM_Meta_Box_Languages {

	/**
	 * Output the metabox.
	 *
	 * @param \WP_Post $post
	 */
	public static function output( $post ) {
		$post_languages = get_post_meta( $post->ID, '_languages', true );

		if ( ! is_array( $post_languages ) ) {
			$post_languages = array();
		}

		$languages = wpm_get_options();
		$i = 0;
		?>
		<p>
			<strong><?php _e( 'Show post only in:', 'wpm' ); ?></strong><br>
			<?php foreach ( $languages as $language ) { if ( ! $language['enable'] ) continue; ?>
				<label><input type="checkbox" name="wpm_languages[<?php esc_attr_e( $i ); ?>]" id="wpm-languages-<?php echo $language['slug']; ?>" value="<?php esc_attr_e( $language['slug'] ); ?>"<?php if ( in_array( $language['slug'], $post_languages ) ) { ?> checked="checked"<?php } ?>><?php echo $language['name']; ?></label><br>
				<?php $i++; } ?>
		</p>
		<?php
		wp_nonce_field( 'wpm_save_data', 'wpm_meta_nonce' );
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id
	 */
	public static function save( $post_id ) {
		if ( ! isset( $_POST['wpm_languages'] ) || empty( $_POST['wpm_languages'] ) ) {
			delete_post_meta( $post_id, '_languages');
		} else {
			update_post_meta( $post_id, '_languages', wpm_clean( $_POST['wpm_languages'] ) );
		}
	}
}
