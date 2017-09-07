<?php
/**
 * Class for capability with NextGEN Gallery
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'NGG_PLUGIN' ) ) {
	return;
}

/**
 * Class WPM_NGG
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @since    1.2.0
 */
class WPM_NGG {

	/**
	 * WPM_NGG constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_admin_pages', array( $this, 'add_admin_pages' ) );
		add_filter( 'ngg_manage_gallery_fields', array( $this, 'filter_fields' ), 11 );
		add_filter( 'ngg_manage_images_row', array( $this, 'translate_gallery_object' ) );
		add_action( 'admin_init', array( $this, 'save_gallery' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_translator_script' ), 11 );
	}


	/**
	 * Transform POST value to multilingual value before save
	 */
	public function save_gallery() {

		if ( isset( $_POST['update_album'] ) ) {

			$fields = array( 'album_name' => 'name', 'album_desc' => 'albumdesc' );
			$data   = array();

			foreach ( $fields as $key => $field ) {
				$data[ $key ] = $_POST[ $key ];
			}

			if ( $album = \C_Album_Mapper::get_instance()->find( wpm_clean( $_POST['act_album'] ) ) ) {
				foreach ( $data as $key => $value ) {
					if ( ! wpm_is_ml_string( $value ) ) {
						$old_value     = wpm_string_to_ml_array( $album->{$fields[ $key ]} );
						$value         = wpm_set_language_value( $old_value, $value );
						$_POST[ $key ] = wpm_ml_value_to_string( $value );
					}
				}
			}
		}

		if ( isset( $_POST['page'] ) && 'manage-images' === $_POST['page'] ) {

			if ( isset( $_POST['updatepictures'] ) ) {

				check_admin_referer( 'ngg_updategallery' );

				if ( ! isset( $_GET['s'] ) ) {

					$fields = array( 'title', 'galdesc' );
					$data   = array();

					foreach ( $fields as $field ) {
						$data[ $field ] = $_POST[ $field ];
					}

					// Update the gallery
					$mapper = \C_Gallery_Mapper::get_instance();
					if ( $entity = $mapper->find( wpm_clean( $_GET['gid'] ) ) ) {
						foreach ( $data as $key => $value ) {
							if ( ! wpm_is_ml_string( $value ) ) {
								$old_value     = wpm_string_to_ml_array( $entity->$key );
								$value         = wpm_set_language_value( $old_value, $value );
								$_POST[ $key ] = wpm_ml_value_to_string( $value );
							}
						}
					}
				}

				$image_mapper = \C_Image_Mapper::get_instance();

				foreach ( $_POST['images'] as $pid => $image ) {

					$data = array();

					if ( isset( $image['description'] ) ) {
						$data['description'] = stripslashes( $image['description'] );
					}
					if ( isset( $image['alttext'] ) ) {
						$data['alttext'] = stripslashes( $image['alttext'] );
					}

					$old_image = $image_mapper->find( $pid );

					// Update all fields
					foreach ( $data as $key => $value ) {
						if ( ! wpm_is_ml_string( $value ) ) {
							$old_value                       = wpm_string_to_ml_array( $old_image->$key );
							$value                           = wpm_set_language_value( $old_value, $value );
							$_POST['images'][ $pid ][ $key ] = wpm_ml_value_to_string( $value );
						}
					}
				}
			}// End if().
		}// End if().

	}


	/**
	 * Add pages for display language switcher
	 *
	 * @param $pages_config
	 *
	 * @return array
	 */
	public function add_admin_pages( $pages_config ) {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		$admin_pages = array(
			'_page_nggallery-manage-gallery',
			'_page_nggallery-manage-album',
		);

		foreach ( $admin_pages as $admin_page ) {
			if ( strpos( $screen_id, $admin_page ) !== false ) {
				$pages_config[] = $screen_id;
			}
		}

		return $pages_config;
	}


	/**
	 * Translate gallery fields before display
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	public function filter_fields( $fields ) {

		$translate_fields = array(
			'title',
			'description',
		);

		foreach ( $translate_fields as $field ) {
			if ( isset( $fields['left'][ $field ] ) ) {
				$fields['left'][ $field ]['callback'][0]->gallery = $this->translate_gallery_object( $fields['left'][ $field ]['callback'][0]->gallery );
			}
		}

		return $fields;
	}


	/**
	 * Translate gallery object for displaying
	 *
	 * @param $object
	 *
	 * @return mixed
	 */
	public function translate_gallery_object( $object ) {

		foreach ( get_object_vars( $object ) as $key => $content ) {
			switch ( $key ) {
				case 'title':
				case 'galdesc':
				case 'alttext':
				case 'description':
					$object->$key = wpm_translate_string( $content );
					break;
			}
		}

		return $object;
	}


	/**
	 * Translate some field without PHP filters by javascript for displaying
	 */
	public function add_translator_script() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		$admin_pages = array(
			'_page_nggallery-manage-gallery',
			'_page_nggallery-manage-album',
		);

		foreach ( $admin_pages as $admin_page ) {
			if ( strpos( $screen_id, $admin_page ) !== false ) {

				if ( '_page_nggallery-manage-album' === $admin_page ) {
					if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['act_album'] ) ) {
						wp_enqueue_script( 'wpm_translator' );
						wpm_enqueue_js( "
							(function ( $ ) {
								$( '#album_name, #album_desc' ).each( function () {
									var text = wpm_translator.translate_string($(this).val());
									$(this).val(text);
								} );
							})( window.jQuery );
						" );
					}
				}

				if ( '_page_nggallery-manage-gallery' === $admin_page ) {
					if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ( isset( $_POST['page'] ) && 'manage-images' === $_POST['page'] ) && isset( $_POST['updatepictures'] ) ) {
						wp_enqueue_script( 'wpm_translator' );
						wpm_enqueue_js( "
							(function ( $ ) {
								$( '#gallery_title, #gallery_description' ).each( function () {
									var text = wpm_translator.translate_string($(this).val());
									$(this).val(text);
								} );
							})( window.jQuery );
						" );
					}
				}
			}
		}

	}
}

new WPM_NGG();
