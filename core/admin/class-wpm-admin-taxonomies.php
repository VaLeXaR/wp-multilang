<?php
/**
 * Edit Taxonomies in Admin
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  WPM/Core/Admin
 * @version  1.0.2
 */

namespace WPM\Core\Admin;

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
		add_action( 'created_term', array( $this, 'save_taxonomy_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_taxonomy_fields' ), 10, 3 );
	}


	/**
	 * Add language column to taxonomies list
	 */
	public function init() {
		$config = wpm_get_config();

		foreach ( $config['taxonomies'] as $taxonomy => $taxonomy_config ) {

			if ( is_null( $taxonomy_config ) ) {
				continue;
			}

			add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'language_columns' ) );
			add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'render_language_column' ), 0, 3 );
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

		return wpm_array_insert_after( $columns, 'name', array( 'languages' => __( 'Languages', 'wpm' ) ) );
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
			remove_filter( 'get_term', 'wpm_translate_object', 0 );
			$term = get_term( $term_id );
			add_filter( 'get_term', 'wpm_translate_object', 0 );
			$output    = array();
			$text      = $term->name . $term->description;
			$strings   = wpm_value_to_ml_array( $text );
			$options   = wpm_get_options();
			$languages = wpm_get_all_languages();

			foreach ( $languages as $locale => $language ) {
				if ( isset( $strings[ $language ] ) && ! empty( $strings[ $language ] ) ) {
					$output[] = '<img src="' . WPM()->flag_dir() . $options[ $locale ]['flag'] . '.png" alt="' . $options[ $locale ]['name'] . '" title="' . $options[ $locale ]['name'] . '">';
				}
			}

			if ( ! empty( $output ) ) {
				$columns .= implode( '<br />', $output );
			}
		}

		return $columns;
	}


	/**
	 * Add languages to insert term form
	 */
	public function add_taxonomy_fields() {

		$screen = get_current_screen();

		if ( empty( $screen->taxonomy ) ) {
			return;
		}

		$config            = wpm_get_config();
		$taxonomies_config = $config['taxonomies'];

		if ( is_null( $taxonomies_config[ $screen->taxonomy ] ) ) {
			return;
		}

		$languages = wpm_get_options();
		$i         = 0;
		?>
		<div class="form-field term-languages">
			<p><?php _e( 'Show term only in:', 'wpm' ); ?></p>
			<?php foreach ( $languages as $language ) {
				if ( ! $language['enable'] ) {
					continue;
				} ?>
				<label><input type="checkbox" name="wpm_languages[<?php esc_attr_e( $i ); ?>]"
				              id="wpm-languages-<?php echo $language['slug']; ?>"
				              value="<?php esc_attr_e( $language['slug'] ); ?>"><?php echo $language['name']; ?></label>
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

		$screen = get_current_screen();

		if ( empty( $screen->taxonomy ) ) {
			return;
		}

		$config            = wpm_get_config();
		$taxonomies_config = $config['taxonomies'];

		if ( is_null( $taxonomies_config[ $screen->taxonomy ] ) ) {
			return;
		}

		$term_languages = get_term_meta( $term->term_id, '_languages', true );

		if ( ! is_array( $term_languages ) ) {
			$term_languages = array();
		}

		$languages = wpm_get_options();
		$i         = 0;
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><?php _e( 'Show term only in:', 'wpm' ); ?></th>
			<td>
				<?php foreach ( $languages as $language ) {
					if ( ! $language['enable'] ) {
						continue;
					} ?>
					<label><input type="checkbox" name="wpm_languages[<?php esc_attr_e( $i ); ?>]"
					              id="wpm-languages-<?php echo $language['slug']; ?>"
					              value="<?php esc_attr_e( $language['slug'] ); ?>"<?php if ( in_array( $language['slug'], $term_languages ) ) { ?> checked="checked"<?php } ?>><?php echo $language['name']; ?>
					</label><br>
					<?php $i ++;
				} ?>
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

		if ( empty( $taxonomy ) ) {
			return;
		}

		$config            = wpm_get_config();
		$taxonomies_config = $config['taxonomies'];

		if ( is_null( $taxonomies_config[ $taxonomy ] ) ) {
			return;
		}

		if ( isset( $_POST['wpm_languages'] ) ) {
			update_term_meta( $term_id, '_languages', wpm_clean( $_POST['wpm_languages'] ) );
		} else {
			delete_term_meta( $term_id, '_languages' );
		}
	}
}
