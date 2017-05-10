<?php
/**
 * qTranslateNext Admin Settings Class
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  qTranslateNext/Admin
 * @version  1.0.0
 */

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'QtN_Admin_Settings' ) ) :

/**
 * QtN_Admin_Settings Class.
 */
class QtN_Admin_Settings {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'add_section' ) );

	}

	public function add_section() {

		add_settings_field(
			'qtn_switch_locale',
			'',
			array( $this, 'switch_locale' ),
			'general'
		);

		add_settings_section( 'qtn_options', __( 'Multilingual Settings', 'qtranslate-next' ), array(
			$this,
			'view_settings'
		), 'general' );

		register_setting( 'general', 'qtn_languages', array(
			'type'              => 'array',
			'group'             => 'general',
			'description'       => '',
			'sanitize_callback' => array( $this, 'save_options' ),
			'show_in_rest'      => true,
		) );
	}


	public function switch_locale() {
		global $qtn_config;
		switch_to_locale( $qtn_config->default_locale );
	}


	public function view_settings(){
		global $qtn_config;

		$_languages = array_flip( $qtn_config->languages );
		switch_to_locale( $_languages[ $qtn_config->user_language ] );

		$options = get_option('qtn_languages');

		$installed_languages = array();

		foreach ($qtn_config->installed_languages as $language ) {
			$installed_languages[ $language ] = array(
				'name' => $qtn_config->translations[ $language ]['native_name'],
				'slug' => current( $qtn_config->translations[ $language ]['iso'] ),
				'flag' => current( $qtn_config->translations[ $language ]['iso'] )
			);
		}

		$languages = $installed_languages;

		if ( $options ) {
			$languages = array_merge( $languages, $options );
		}

		$languages = apply_filters( 'qtn_languages', $languages );
		$languages = array_map( array( $this, 'set_default_settings' ), $languages );

		$flags    = array();
		$flag_dir = QN()->plugin_path() . '/flags/';
		if ( $dir_handle = @opendir( $flag_dir ) ) {
			while ( false !== ( $file = readdir( $dir_handle ) ) ) {
				if ( preg_match( "/\.(jpeg|jpg|gif|png|svg)$/i", $file ) ) {
					$flags[] = $file;
				}
			}
			sort( $flags );
		}
		?>
		<table class="widefat">
			<thead>
			<tr>
				<th><?php esc_attr_e( 'Enable', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Locale', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Slug *', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Name', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Flag', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Delete', 'qtranslate-next' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $languages as $key => $language ) { ?>
				<?php if ( ! is_string( $key ) ) {
					continue;
				} ?>
				<tr>
					<td><input name="qtn_languages[<?php echo $key; ?>][enable]" type="checkbox" value="1"<?php checked( $language['enable'] ); ?> title="<?php esc_attr_e( 'Enable', 'qtranslate-next' ); ?>"></td>
					<td>
						<?php if ( in_array( $key, $qtn_config->installed_languages ) ) { ?>
							<?php esc_attr_e ( $key ); ?>
						<?php } else { ?>
							<input type="text" name="qtn_languages[<?php echo $key; ?>][locale]" value="<?php esc_attr_e ( $key ); ?>" title="<?php esc_attr_e( 'Locale', 'qtranslate-next' ); ?>" placeholder="<?php esc_attr_e( 'Locale', 'qtranslate-next' ); ?>">
						<?php } ?>
					</td>
					<td><input type="text" name="qtn_languages[<?php echo $key; ?>][slug]" value="<?php esc_attr_e ( $language['slug'] ); ?>" title="<?php esc_attr_e( 'Slug *', 'qtranslate-next' ); ?>" placeholder="<?php esc_attr_e( 'Slug *', 'qtranslate-next' ); ?>" required></td>
					<td><input type="text" name="qtn_languages[<?php echo $key; ?>][name]" value="<?php esc_attr_e ( $language['name'] ); ?>" title="<?php esc_attr_e( 'Name', 'qtranslate-next' ); ?>" placeholder="<?php esc_attr_e( 'Name', 'qtranslate-next' ); ?>"></td>
					<td>
						<select name="qtn_languages[<?php echo $key; ?>][flag]" title="<?php esc_attr_e( 'Flag', 'qtranslate-next' ); ?>">
							<option value=""><?php _e( '&mdash; Select &mdash;' ); ?></option>
							<?php foreach ( $flags as $flag ) { ?>
								<option value="<?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ) ; ?>"<?php selected( $language['flag'], pathinfo( $flag, PATHINFO_FILENAME ) ); ?>><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
							<?php } ?>
						</select>
						<?php if ( ( $language['flag'] ) ) { ?>
							<img src="<?php echo QN()->flag_dir() . $language['flag'] . '.png'; ?>" alt="<?php esc_attr_e ( $language['name'] ); ?>">
						<?php } ?>
					</td>
					<td>
						<?php if ( 'en_US' != $key ) { ?>
							<button type="button" class="button button-link delete-language" data-locale="<?php echo $key; ?>"><?php esc_attr_e( 'Delete', 'qtranslate-next' ); ?></button>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
			<tfoot>
			<tr>
				<th><?php esc_attr_e( 'Enable', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Locale', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Slug *', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Name', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Flag', 'qtranslate-next' ); ?></th>
				<th><?php esc_attr_e( 'Delete', 'qtranslate-next' ); ?></th>
			</tr>
			</tfoot>
		</table>
		<?php
	}

	public function save_options( $value ) {

		$option_name = 'qtn_languages';
		$error = false;

		$languages = array();

		foreach ( $value as $key => $item ) {

			$locale = $key;

			if ( isset( $item['locale'] ) ) {
				$locale = qtn_clean( $item['locale'] );
			}

			if (empty($item['slug'])) {
				$error = true;
				break;
			}

			$languages[ $locale ] = array(
				'enable' => isset( $_POST['qtn_languages'][ $key ]['enable'] ) ? 1 : 0,
				'slug'   => qtn_clean( $item['slug'] ),
				'name'   => qtn_clean( $item['name'] ),
				'flag'   => qtn_clean( $item['flag'] )
			);
		}

		if ( $error  ) {

			add_settings_error( $option_name, '', __('Language slug is required', 'qtranslate-next'), 'error' );

			return get_option( $option_name );

		} else {
			return $languages;
		}
	}

	private function set_default_settings( $language ) {

		$default = array(
			'name' => '',
			'slug' => '',
			'flag' => '',
			'enable' => 1
		);

		return array_merge( $default, $language );
	}
}

endif;
