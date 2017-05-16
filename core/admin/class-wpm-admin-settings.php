<?php
/**
 * WPMPlugin Admin Settings Class
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  WPMPlugin/Admin
 * @version  1.0.0
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPM_Admin_Settings' ) ) :

	/**
	 * WPM_Admin_Settings Class.
	 */
	class WPM_Admin_Settings {

		public function __construct() {
			add_action( 'admin_init', array( $this, 'add_section' ) );

		}

		public function add_section() {

			add_settings_field(
				'wpm_switch_locale',
				'',
				array( $this, 'switch_locale' ),
				'general'
			);

			add_settings_section( 'wpm_options', __( 'Multilingual Settings', 'wpm' ), array(
				$this,
				'view_settings'
			), 'general' );

			register_setting( 'general', 'wpm_languages', array(
				'type'              => 'array',
				'group'             => 'general',
				'description'       => '',
				'sanitize_callback' => array( $this, 'save_options' ),
				'show_in_rest'      => true,
			) );
		}


		public function switch_locale() {
			switch_to_locale( wpm_get_default_locale() );
		}


		public function view_settings() {

			$_languages = array_flip( wpm_get_languages() );
			switch_to_locale( $_languages[ wpm_get_user_language() ] );

			$options              = wpm_get_options();
			$_installed_languages = array();
			$installed_languages  = wpm_get_installed_languages();
			$translations         = wpm_get_translations();

			foreach ( $installed_languages as $language ) {
				$_installed_languages[ $language ] = array(
					'name' => $translations[ $language ]['native_name'],
					'slug' => current( $translations[ $language ]['iso'] ),
					'flag' => current( $translations[ $language ]['iso'] )
				);
			}

			$languages = $_installed_languages;

			if ( $options ) {
				$languages = array_merge( $languages, $options );
			}

			$languages = apply_filters( 'wpm_languages', $languages );
			$languages = array_map( array( $this, 'set_default_settings' ), $languages );

			$flags    = array();
			$flag_dir = WPM()->plugin_path() . '/flags/';
			if ( $dir_handle = @opendir( $flag_dir ) ) {
				while ( false !== ( $file = readdir( $dir_handle ) ) ) {
					if ( preg_match( "/\.(jpeg|jpg|gif|png|svg)$/i", $file ) ) {
						$flags[] = $file;
					}
				}
				sort( $flags );
			}
			?>
			<table id="wpm-languages" class="wpm-languages widefat">
				<thead>
				<tr>
					<th><?php esc_attr_e( 'Enable', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Locale', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Slug *', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Name', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Flag', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Delete', 'wpm' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $languages as $key => $language ) { ?>
					<?php if ( ! is_string( $key ) ) {
						continue;
					} ?>
					<tr>
						<td><input type="hidden" name="wpm_languages[<?php echo $key; ?>][enable]" value="1">
							<input name="wpm_languages[<?php echo $key; ?>][enable]" type="checkbox"
						           value="1"<?php checked( $language['enable'] ); ?>
						           title="<?php esc_attr_e( 'Enable', 'wpm' ); ?>"<?php if ( $key == wpm_get_default_locale() ) { ?> disabled="disabled"<?php } ?>>
							<?php if ( $key == wpm_get_default_locale() ) { ?><input type="hidden" name="wpm_languages[<?php echo $key; ?>][enable]" value="1"><?php } ?>
						</td>
						<td>
							<?php if ( in_array( $key, $installed_languages ) ) { ?>
								<?php esc_attr_e( $key ); ?>
							<?php } else { ?>
								<input type="text" name="wpm_languages[<?php echo $key; ?>][locale]"
								       value="<?php esc_attr_e( $key ); ?>"
								       title="<?php esc_attr_e( 'Locale', 'wpm' ); ?>"
								       placeholder="<?php esc_attr_e( 'Locale', 'wpm' ); ?>">
							<?php } ?>
						</td>
						<td><input type="text" name="wpm_languages[<?php echo $key; ?>][slug]"
						           value="<?php esc_attr_e( $language['slug'] ); ?>"
						           title="<?php esc_attr_e( 'Slug *', 'wpm' ); ?>"
						           placeholder="<?php esc_attr_e( 'Slug *', 'wpm' ); ?>" required></td>
						<td><input type="text" name="wpm_languages[<?php echo $key; ?>][name]"
						           value="<?php esc_attr_e( $language['name'] ); ?>"
						           title="<?php esc_attr_e( 'Name', 'wpm' ); ?>"
						           placeholder="<?php esc_attr_e( 'Name', 'wpm' ); ?>"></td>
						<td>
							<select name="wpm_languages[<?php echo $key; ?>][flag]"
							        title="<?php esc_attr_e( 'Flag', 'wpm' ); ?>">
								<option value=""><?php _e( '&mdash; Select &mdash;' ); ?></option>
								<?php foreach ( $flags as $flag ) { ?>
									<option
										value="<?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?>"<?php selected( $language['flag'], pathinfo( $flag, PATHINFO_FILENAME ) ); ?>><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
								<?php } ?>
							</select>
							<?php if ( ( $language['flag'] ) ) { ?>
								<img src="<?php echo WPM()->flag_dir() . $language['flag'] . '.png'; ?>"
								     alt="<?php esc_attr_e( $language['name'] ); ?>">
							<?php } ?>
						</td>
						<td>
							<?php if ( 'en_US' == $key ) { ?>
								Built-in
							<?php } elseif ( $key == get_locale() ) { ?>
								Current
							<?php } elseif ( $key == wpm_get_default_locale() ) { ?>
								Default
							<?php } else { ?>
								<button type="button" class="button button-link delete-language"
								        data-locale="<?php echo $key; ?>"><?php esc_attr_e( 'Delete', 'wpm' ); ?></button>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<tr>
					<th><?php esc_attr_e( 'Enable', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Locale', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Slug *', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Name', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Flag', 'wpm' ); ?></th>
					<th><?php esc_attr_e( 'Delete', 'wpm' ); ?></th>
				</tr>
				</tfoot>
			</table>
			<?php
		}

		public function save_options( $value ) {

			$option_name = 'wpm_languages';
			$type        = '';

			$languages = array();

			foreach ( $value as $key => $item ) {

				$locale = $key;

				if ( isset( $item['locale'] ) ) {
					$locale = wpm_clean( $item['locale'] );
				}

				if ( empty( $item['slug'] ) ) {
					$type = 'error';
					break;
				}

				$languages[ $locale ] = array(
					'enable' => $_POST['wpm_languages'][ $key ]['enable'] ? 1 : 0,
					'slug'   => wpm_clean( $item['slug'] ),
					'name'   => wpm_clean( $item['name'] ),
					'flag'   => wpm_clean( $item['flag'] )
				);

				$translations        = wpm_get_translations();
				$installed_languages = wpm_get_installed_languages();

				foreach ( $installed_languages as $installed_language ) {
					unset( $translations[ $installed_language ] );
				}

				if ( in_array( $locale, $translations ) ) {
					if ( wp_can_install_language_pack() ) {
						wp_download_language_pack( $locale );
						$type = 'updated';
					}
				}
			}

			if ( 'updated' == $type ) {
				add_settings_error( $option_name, '', __( 'New language package installed', 'wpm' ), $type );
			}

			if ( 'error' == $type ) {
				add_settings_error( $option_name, '', __( 'Language slug is required', 'wpm' ), $type );

				return get_option( $option_name );

			}

			return $languages;
		}

		private function set_default_settings( $language ) {

			$default = array(
				'name'   => '',
				'slug'   => '',
				'flag'   => '',
				'enable' => 1
			);

			return array_merge( $default, $language );
		}
	}

endif;
