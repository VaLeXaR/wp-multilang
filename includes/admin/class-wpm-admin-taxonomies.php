<?php
/**
 * Edit Taxonomies in Admin
 *
 * @author   Valentyn Riaboshtan
 * @category Admin
 * @package  WPM/Includes/Admin
 * @version  1.0.2
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Admin_Taxonomies Class.
 *
 */
class WPM_Admin_Taxonomies {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'term_link', array( $this, 'translate_term_link' ) );
		add_action( 'created_term', array( $this, 'save_taxonomy_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_taxonomy_fields' ), 10, 3 );
	}

	/**
	 * Add language column to taxonomies list
	 */
	public function init() {

		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			if ( null === wpm_get_taxonomy_config( $taxonomy ) ) {
				continue;
			}

			add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'language_columns' ) );
			add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'render_language_column' ), 10, 3 );
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_taxonomy_fields' ) );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_taxonomy_fields' ), 10 );
		}
	}


	/**
	 * Define custom columns for post_types.
	 *
	 * @param  array $columns
	 *
	 * @return array
	 */
	public function language_columns( $columns ) {
		if ( empty( $columns ) && ! is_array( $columns ) ) {
			$columns = array();
		}

		return wpm_array_insert_after( $columns, 'name', array( 'languages' => __( 'Languages', 'wp-multilang' ) ) );
	}


	/**
	 * Output language columns for taxonomies.
	 *
	 * @param $columns
	 * @param $column
	 * @param $term_id
	 *
	 * @return string
	 */
	public function render_language_column( $columns, $column, $term_id ) {

		if ( 'languages' === $column ) {
			remove_filter( 'get_term', 'wpm_translate_term', 5 );
			$term = get_term( $term_id );
			add_filter( 'get_term', 'wpm_translate_term', 5, 2 );
			$output    = array();
			$text      = $term->name . $term->description;
			$strings   = wpm_value_to_ml_array( $text );
			$languages = wpm_get_lang_option();

			foreach ( $languages as $code => $language ) {
				if ( isset( $strings[ $code ] ) && ! empty( $strings[ $code ] ) ) {
					$output[] = '<img src="' . esc_url( wpm_get_flag_url( $language['flag'] ) ) . '" alt="' . $language['name'] . '" title="' . $language['name'] . '">';
				}
			}

			if ( ! empty( $output ) ) {
				$columns .= implode( ' ', $output );
			}
		}

		return $columns;
	}


	/**
	 * Add languages to insert term form
	 */
	public function add_taxonomy_fields() {

		$languages = wpm_get_languages();
		$i         = 0;
		?>
		<div class="form-field term-languages">
			<p><?php _e( 'Show term only in:', 'wp-multilang' ); ?></p>
			<?php foreach ( $languages as $code => $language ) { ?>
				<label><input type="checkbox" name="wpm_languages[<?php echo esc_attr( $i ); ?>]" id="wpm-languages-<?php echo esc_attr( $code ); ?>" value="<?php echo esc_attr( $code ); ?>"><?php esc_html_e( $language['name'] ); ?></label>
				<?php $i ++;
			} ?>
		</div>
		<?php
	}

	/**
	 * Add languages to edit term form
	 *
	 * @param $term
	 */
	public function edit_taxonomy_fields( $term ) {

		$term_languages = get_term_meta( $term->term_id, '_languages', true );

		if ( ! is_array( $term_languages ) ) {
			$term_languages = array();
		}

		$languages = wpm_get_languages();
		$i         = 0;
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><?php _e( 'Show term only in:', 'wp-multilang' ); ?></th>
			<td>
				<ul class="languagechecklist">
					<?php foreach ( $languages as $code => $language ) { ?>
						<li>
							<label>
								<input type="checkbox" name="wpm_languages[<?php echo esc_attr( $i ); ?>]" id="wpm-languages-<?php echo esc_attr( $code ); ?>" value="<?php echo esc_attr( $code ); ?>"<?php checked( in_array( $code, $term_languages ) ); ?>>
								<?php esc_html_e( $language['name'] ); ?>
							</label>
						</li>
						<?php $i ++;
					} ?>
				</ul>
			</td>
		</tr>
		<?php
	}

	/**
	 * save_taxonomy_fields function.
	 *
	 * @param mixed  $term_id Term ID being saved
	 * @param mixed  $tt_id
	 * @param string $taxonomy
	 */
	public function save_taxonomy_fields( $term_id, $tt_id = '', $taxonomy = '' ) {

		if ( empty( $taxonomy ) || null === wpm_get_taxonomy_config( $taxonomy ) ) {
			return;
		}

		if ( $languages = wpm_get_post_data_by_key( 'wpm_languages' ) ) {
			update_term_meta( $term_id, '_languages', $languages );
		} else {
			delete_term_meta( $term_id, '_languages' );
		}
	}

	/**
	 * Translate taxonomies link
	 *
	 * @param $termlink
	 *
	 * @return string
	 */
	public function translate_term_link( $termlink ) {
		return wpm_translate_url( $termlink, wpm_get_language() );
	}
}
