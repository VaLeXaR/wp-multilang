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

	private $description = array();


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_filter( 'pre_insert_term', array( $this, 'pre_insert_term' ), 0, 2 );
		add_filter( 'wp_insert_term_data', array( $this, 'insert_term' ), 99, 3 );
		add_action( 'created_term', array( $this, 'insert_description' ), 99, 3 );
		add_filter( 'wp_update_term_data', array( $this, 'update_term' ), 99, 4 );
		add_action( 'edited_term_taxonomy', array( $this, 'update_description' ), 0, 2 );
		add_action( 'created_term', array( $this, 'save_taxonomy_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_taxonomy_fields' ), 10, 3 );
	}


	/**
	 * Add language column to taxonomies list
	 */
	public function init() {
		$config            = wpm_get_config();
		$taxonomies_config = apply_filters( 'wpm_taxonomies_config', $config['taxonomies'] );

		foreach ( $taxonomies_config as $taxonomy => $config ) {
			$taxonomy_config = apply_filters( "wpm_taxonomy_{$taxonomy}_config", $config );
			if ( ! is_null( $taxonomy_config ) ) {
				add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'language_columns' ) );
				add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'render_language_column' ), 0, 3 );
				add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_taxonomy_fields' ) );
				add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_taxonomy_fields' ), 10 );
			}
		}
	}


	/**
	 * Check if isset taxonomies in current language
	 *
	 * @param $term
	 * @param $taxonomy
	 *
	 * @return string
	 */
	public function pre_insert_term( $term, $taxonomy ) {
		global $wpdb;

		$like    = '%' . $wpdb->esc_like( esc_sql( $term ) ) . '%';
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT t.name AS `name` FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s' AND `name` LIKE '%s'", $taxonomy, $like ) );

		foreach ( $results as $result ) {
			$ml_term = wpm_translate_string( $result->name );
			if ( $ml_term === $term ) {
				return '';
			}
		}

		return $term;
	}


	/**
	 * Translate inserted term
	 *
	 * @param $data
	 * @param $taxonomy
	 * @param $args
	 *
	 * @return mixed
	 */
	public function insert_term( $data, $taxonomy, $args ) {

		$config = wpm_get_config();

		$taxonomies_config = $config['taxonomies'];
		$taxonomies_config = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );

		if ( ! isset( $taxonomies_config[ $taxonomy ] ) ) {
			return $data;
		}

		if ( wpm_is_ml_value( $data['name'] ) ) {
			return $data;
		}

		$taxonomy_config = $taxonomies_config[ $taxonomy ];
		$taxonomy_config = apply_filters( "wpm_taxonomy_{$taxonomy}_config", $taxonomy_config );

		if ( ! wpm_is_ml_value( $data['name'] ) ) {
			$data['name'] = wpm_set_language_value( array(), $data['name'], $taxonomy_config );
			$data['name'] = wpm_ml_value_to_string( $data['name'] );
		}

		$this->description = array(
			'new' => $args['description'],
		);

		return $data;
	}


	/**
	 * Update inserted description for term
	 *
	 * @param $term_id
	 * @param $tt_id
	 * @param $taxonomy
	 */
	public function insert_description( $term_id, $tt_id, $taxonomy ) {
		global $wpdb;

		$config            = wpm_get_config();
		$taxonomies_config = $config['taxonomies'];
		$taxonomies_config = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );

		if ( ! isset( $taxonomies_config[ $taxonomy ] ) ) {
			return;
		}

		if ( ! $this->description ) {
			return;
		}

		$value = $this->description['new'];

		if ( wpm_is_ml_value( $value ) ) {
			return;
		}

		$taxonomy_config = $taxonomies_config[ $taxonomy ];
		$taxonomy_config = apply_filters( "wpm_taxonomy_{$taxonomy}_config", $taxonomy_config );
		$value           = wpm_set_language_value( array(), $value, $taxonomy_config );
		$description     = wpm_ml_value_to_string( $value );

		$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_taxonomy_id' => $tt_id ) );
	}


	/**
	 * Save taxonomies with translation
	 *
	 * @param $data
	 * @param $term_id
	 * @param $taxonomy
	 * @param $args
	 *
	 * @return mixed
	 */
	public function update_term( $data, $term_id, $taxonomy, $args ) {
		$config = wpm_get_config();

		$taxonomies_config = $config['taxonomies'];
		$taxonomies_config = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );
		$taxonomies_config[ $taxonomy ] = apply_filters( "wpm_taxonomy_{$taxonomy}_config", isset( $taxonomies_config[ $taxonomy ] ) ? $taxonomies_config[ $taxonomy ] : null );

		if ( ! isset( $taxonomies_config[ $taxonomy ] ) || is_null( $taxonomies_config[ $taxonomy ] ) ) {
			return $data;
		}

		if ( wpm_is_ml_value( $data['name'] ) ) {
			return $data;
		}

		$taxonomy_config = $taxonomies_config[ $taxonomy ];
		remove_filter( 'get_term', 'wpm_translate_object', 0 );
		$old_name        = get_term_field( 'name', $term_id );
		$old_description = get_term_field( 'description', $term_id );
		add_filter( 'get_term', 'wpm_translate_object', 0 );
		$strings      = wpm_value_to_ml_array( $old_name );
		$value        = wpm_set_language_value( $strings, $data['name'], $taxonomy_config );
		$data['name'] = wpm_ml_value_to_string( $value );

		$this->description = array(
			'old' => $old_description,
			'new' => $args['description'],
		);

		return $data;
	}


	/**
	 * Fix for saving taxonomy description
	 *
	 * @param $tt_id
	 * @param $taxonomy
	 */
	public function update_description( $tt_id, $taxonomy ) {
		global $wpdb;

		$config            = wpm_get_config();
		$taxonomies_config = $config['taxonomies'];
		$taxonomies_config = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );

		if ( ! isset( $taxonomies_config[ $taxonomy ] ) ) {
			return;
		}

		if ( ! $this->description ) {
			return;
		}

		$value = $this->description['new'];

		if ( wpm_is_ml_value( $value ) ) {
			return;
		}

		$taxonomy_config = $taxonomies_config[ $taxonomy ];
		$taxonomy_config = apply_filters( "wpm_taxonomy_{$taxonomy}_config", $taxonomy_config );
		$old_value       = $this->description['old'];
		$strings         = wpm_value_to_ml_array( $old_value );
		$value           = wpm_set_language_value( $strings, $value, $taxonomy_config );
		$description     = wpm_ml_value_to_string( $value );

		$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_taxonomy_id' => $tt_id ) );
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

		$insert_after = 'name';

		$i = 0;
		foreach ( $columns as $key => $value ) {
			if ( $key === $insert_after ) {
				break;
			}
			$i ++;
		}

		$columns =
			array_slice( $columns, 0, $i + 1 ) + array( 'languages' => __( 'Languages', 'wpm' ) ) + array_slice( $columns, $i + 1 );

		return $columns;
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

		$taxonomy                       = $screen->taxonomy;
		$config                         = wpm_get_config();
		$taxonomies_config              = $config['taxonomies'];
		$taxonomies_config              = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );
		$taxonomies_config[ $taxonomy ] = apply_filters( "wpm_taxonomy_{$taxonomy}_config", isset( $taxonomies_config[ $taxonomy ] ) ? $taxonomies_config[ $taxonomy ] : null );

		if ( ! isset( $config['taxonomies'][ $taxonomy ] ) || is_null( $config['taxonomies'][ $taxonomy ] ) ) {
			return;
		}

		$languages = wpm_get_options();
		$i = 0;
		?>
		<div class="form-field term-languages">
			<p><?php _e( 'Show term only in:', 'wpm' ); ?></p>
			<?php foreach ( $languages as $language ) { if ( ! $language['enable'] ) continue; ?>
				<label><input type="checkbox" name="wpm_languages[<?php esc_attr_e( $i ); ?>]" id="wpm-languages-<?php echo $language['slug']; ?>" value="<?php esc_attr_e( $language['slug'] ); ?>"><?php echo $language['name']; ?></label>
				<?php $i++; } ?>
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

		$taxonomy                       = $screen->taxonomy;
		$config                         = wpm_get_config();
		$taxonomies_config              = $config['taxonomies'];
		$taxonomies_config              = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );
		$taxonomies_config[ $taxonomy ] = apply_filters( "wpm_taxonomy_{$taxonomy}_config", isset( $taxonomies_config[ $taxonomy ] ) ? $taxonomies_config[ $taxonomy ] : null );

		if ( ! isset( $config['taxonomies'][ $taxonomy ] ) || is_null( $config['taxonomies'][ $taxonomy ] ) ) {
			return;
		}

		$term_languages = get_term_meta( $term->term_id, '_languages', true );

		if ( ! is_array( $term_languages ) ) {
			$term_languages = array();
		}

		$languages = wpm_get_options();
		$i = 0;
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><?php _e( 'Show term only in:', 'wpm' ); ?></th>
			<td>
				<?php foreach ( $languages as $language ) { if ( ! $language['enable'] ) continue; ?>
					<label><input type="checkbox" name="wpm_languages[<?php esc_attr_e( $i ); ?>]" id="wpm-languages-<?php echo $language['slug']; ?>" value="<?php esc_attr_e( $language['slug'] ); ?>"<?php if ( in_array( $language['slug'], $term_languages ) ) { ?> checked="checked"<?php } ?>><?php echo $language['name']; ?></label><br>
					<?php $i++; } ?>
			</td>
		</tr>
		<?php
	}


	/**
	 * save_taxonomy_fields function.
	 *
	 * @param mixed $term_id Term ID being saved
	 * @param mixed $tt_id
	 * @param string $taxonomy
	 */
	public function save_taxonomy_fields( $term_id, $tt_id = '', $taxonomy = '' ) {

		if ( empty( $taxonomy ) ) {
			return;
		}

		$config                         = wpm_get_config();
		$taxonomies_config              = $config['taxonomies'];
		$taxonomies_config              = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );
		$taxonomies_config[ $taxonomy ] = apply_filters( "wpm_taxonomy_{$taxonomy}_config", isset( $taxonomies_config[ $taxonomy ] ) ? $taxonomies_config[ $taxonomy ] : null );

		if ( ! isset( $config['taxonomies'][ $taxonomy ] ) || is_null( $config['taxonomies'][ $taxonomy ] ) ) {
			return;
		}

		if ( isset( $_POST['wpm_languages'] ) ) {
			update_term_meta( $term_id, '_languages', wpm_clean( $_POST['wpm_languages'] ) );
		} else {
			delete_term_meta( $term_id, '_languages' );
		}
	}
}
