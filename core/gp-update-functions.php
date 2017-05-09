<?php
/**
 * qTranslateNext Updates
 *
 * Functions for updating data, used by the background updater.
 *
 * @author   VaLeXaR
 * @category Core
 * @package  qTranslateNext/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gp_update_100_backgrounds() {
	$image_term = get_term_by( 'name', 'idvadmin images', 'media_category' );
	if ( is_object( $image_term ) ) {
		wp_delete_term( $image_term->term_id, 'media_category' );
	}

	$media = get_attached_media( 'image', 865 );

	$images = array();

	foreach ( $media as $image ) {
		$images[] = $image->ID;
	}

	add_option( 'options_game_portal_backgrounds', $images );
	add_option( '_options_game_portal_backgrounds', 'field_game_portal_backgrounds' );
	wp_delete_post( 865 );
}

function gp_update_100_options() {
	delete_option( 'widget_idvam_widget' );
	delete_option( 'widget_idvls_widget' );
	$options = get_option( 'idv_options' );
	update_option( 'game_portal_default_price', $options['game_price'] );
	update_option( 'game_portal_terms_page_id', $options['agreement_page_id'] );
	delete_option( 'idv_options' );
}

function gp_update_100_previews() {
	global $wpdb;

	$previews = get_posts( array(
		'numberposts' => - 1,
		'post_type'   => 'game_preview',
		'post_status' => 'any'
	) );

	$acf_options = array(
		'fon',
		'single_image_preview',
		'price',
		'add_mat_price',
		'add_mat_archive',
		'data_vyhoda',
		'demo_lev_qty',
		'additional_materials',
		'material_item',
	);

	foreach ( $acf_options as $option ) {
		$acf_option = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='acf-field' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_excerpt LIKE %s LIMIT 1;", "%{$option}%" ) );
		wp_delete_post( $acf_option );
	}

	foreach ( $previews as $preview ) {

		$price            = get_post_meta( $preview->ID, 'price', true );
		$paid_files_price = get_post_meta( $preview->ID, 'add_mat_price', true );
		$demo_limit       = get_post_meta( $preview->ID, 'demo_lev_qty', true );
		$game_files_qtt   = get_post_meta( $preview->ID, 'additional_materials', true );
		$paid_file        = get_post_meta( $preview->ID, 'add_mat_archive', true );
		$date_release     = get_post_meta( $preview->ID, 'data_vyhoda', true );

		$main_game = 0;
		$games     = wp_get_post_terms( $preview->ID, 'game', array( "fields" => "ids" ) );

		foreach ( $games as $game ) {
			$game_author = get_term_meta( $game, 'game_author', true );
			$user        = get_userdata( $game_author );
			if ( $user ) {
				if ( in_array( 'administrator', $user->roles ) || in_array( 'gameadmin', $user->roles ) ) {
					$main_game = $game;
					break;
				}
			}
		}

		if ( $main_game ) {

			foreach ( $games as $game ) {
				if ( $game == $main_game ) {
					continue;
				}

				update_term_meta( $game, '_donor', $main_game );
				wp_remove_object_terms( $preview->ID, $game, 'game' );
			}

			if ( $game_files_qtt ) {
				for ( $i = 0; $i < $game_files_qtt; $i ++ ) {
					$meta_value = get_post_meta( $preview->ID, 'additional_materials_' . $i . '_material_item', true );
					update_term_meta( $main_game, 'game_files_' . $i . '_file', $meta_value );
					update_term_meta( $main_game, '_game_files_' . $i . '_file', 'field_file' );
					delete_post_meta( $preview->ID, 'additional_materials_' . $i . '_material_item' );
					delete_post_meta( $preview->ID, '_additional_materials_' . $i . '_material_item' );
				}
			}

			if ( is_numeric( $price ) ) {
				update_term_meta( $main_game, '_price', $price );
			}

			if ( is_numeric( $paid_files_price ) ) {
				update_term_meta( $main_game, '_paid_files_price', $paid_files_price );
			}

			if ( is_numeric( $demo_limit ) ) {
				update_term_meta( $main_game, '_demo_limit', $demo_limit );
			}

			update_term_meta( $main_game, '_public', 1 );
			update_term_meta( $main_game, 'game_files', $game_files_qtt );
			update_term_meta( $main_game, '_game_files', 'field_game_files' );
			update_term_meta( $main_game, '_paid_file', $paid_file );
		}

		update_post_meta( $preview->ID, '_date_release', $date_release );

		foreach ( $acf_options as $option ) {
			delete_post_meta( $preview->ID, $option );
			delete_post_meta( $preview->ID, '_' . $option );
		}

		$wpdb->query( "
		UPDATE {$wpdb->posts}
		SET post_type = 'preview'
		WHERE ID = {$preview->ID};
		" );
	}
}

function gp_update_100_games() {

	$games = get_terms( array(
		'taxonomy'   => 'game',
		'hide_empty' => false,
	) );

	foreach ( $games as $game ) {

		$game_old_settings   = get_term_meta( $game->term_id, 'game_settings', true );
		$game_name           = get_term_meta( $game->term_id, 'game_name', true );
		$game_author         = get_term_meta( $game->term_id, 'game_author', true );
		$game_payed          = get_term_meta( $game->term_id, 'game_payed', true );
		$game_date           = date_create_from_format( 'Y-m-d', $game_old_settings['game_date'] ?? date( 'Y-m-d' ) );
		$count_play          = get_term_meta( $game->term_id, 'count_play', true );
		$order_id            = get_term_meta( $game->term_id, 'order_id', true );
		$applied_coupon_code = get_term_meta( $game->term_id, 'applied_coupon_code', true );

		$img_maps = get_posts( array(
			'numberposts' => 1,
			'post_type'   => 'attachment',
			'post_status' => 'any',
			'tax_query'   => array(
				array(
					'taxonomy' => 'game',
					'terms'    => $game->term_id
				)
			)
		) );

		$img_map = ! empty( $img_maps ) ? current( $img_maps ) : array();

		$game_settings = array(
			'name'                => $game_name,
			'date'                => date_format( $game_date, get_option( 'date_format' ) ),
			'author'              => $game_author,
			'map_id'              => ! empty( $img_map ) ? $img_map->ID : '',
			'background_stretch'  => $game_old_settings['game_image']['full_screen'] ?? 0,
			'background_image_id' => $game_old_settings['game_image']['image_id'] ?? 0,
			'payed'               => $game_payed,
			'played'              => $count_play,
			'order_id'            => $order_id,
			'applied_coupon_code' => $applied_coupon_code
		);

		foreach ( $game_settings as $key => $setting ) {
			if ( ! empty( $setting ) || is_numeric( $setting ) ) {
				update_term_meta( $game->term_id, '_' . $key, $setting );
			}
		}

		if ( $img_map ) {
			wp_remove_object_terms( $img_map->ID, $game->term_id, 'game' );
		}

		delete_term_meta( $game->term_id, 'game_settings' );
		delete_term_meta( $game->term_id, 'game_name' );
		delete_term_meta( $game->term_id, 'game_author' );
		delete_term_meta( $game->term_id, 'game_payed' );
		delete_term_meta( $game->term_id, 'count_play' );
		delete_term_meta( $game->term_id, 'order_id' );
		delete_term_meta( $game->term_id, 'applied_coupon_code' );
	}
}

function gp_update_100_levels() {
	global $wpdb;

	$levels = $wpdb->get_results( "SELECT ID, post_type, post_content FROM {$wpdb->posts} WHERE post_type IN ( 'game_start', 'game_level', 'game_end' )" );

	foreach ( $levels as $level ) {

		$old_level_settings = get_post_meta( $level->ID, 'level_settings', true );
		$level_done         = get_post_meta( $level->ID, 'level_done', true );
		$level_answer       = get_post_meta( $level->ID, 'level_answer', true );
		$right_answer       = get_post_meta( $level->ID, 'right_answer', true );
		$wrong_answer       = get_post_meta( $level->ID, 'wrong_answer', true );

		$default_order = array( 'text', 'images', 'video' );

		$gallery_files = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image\/%' AND post_parent = {$level->ID}" );

		$gallery = array();
		foreach ( $gallery_files as $file ) {
			$gallery[] = $file->ID;
		}

		if ( $gallery ) {
			$gallery_string = implode( ',', $gallery );
			$wpdb->query( "
			UPDATE {$wpdb->posts}
			SET post_parent = 0
			WHERE ID IN ({$gallery_string});
			" );
		}

		$level_settings = array(
			'desc'            => $level->post_content,
			'video_code'      => $old_level_settings['level_video']['code'] ?? '',
			'video_auto_play' => $old_level_settings['level_video']['auto_play'] ?? 0,
			'video_mute'      => $old_level_settings['level_video']['mute'] ?? 0,
			'audio_audio_id'  => $old_level_settings['level_audio']['audio_id'] ?? 0,
			'audio_auto_play' => $old_level_settings['level_audio']['auto_play'] ?? 0,
			'audio_once'      => $old_level_settings['level_audio']['once'] ?? 0,
			'gallery'         => $gallery,
			'order'           => $old_level_settings['level_info_order'] ?? $default_order,
			'done'            => $level_done
		);

		delete_post_meta( $level->ID, 'level_settings' );
		delete_post_meta( $level->ID, 'level_done' );

		if ( $level_answer ) {
			$level_settings['answer_text'] = $level_answer['answer'];
			$level_settings['hint_text']   = $level_answer['tip'];

			delete_post_meta( $level->ID, 'level_answer' );
		}

		if ( 'game_start' == $level->post_type ) {
			$level_settings['type'] = 'start';
		}

		if ( 'game_level' == $level->post_type ) {
			$level_settings['type'] = 'level';
		}

		if ( 'game_end' == $level->post_type ) {
			$level_settings['type'] = 'finish';
		}

		foreach ( $level_settings as $key => $setting ) {
			if ( ! empty( $setting ) || is_numeric( $setting ) ) {
				update_post_meta( $level->ID, '_level_' . $key, $setting );
			}
		}

		if ( $right_answer ) {

			$right_settings = array(
				'desc'            => $right_answer['right_answer_descr'],
				'video_code'      => $right_answer['right_answer_video']['code'] ?? '',
				'video_auto_play' => $right_answer['right_answer_video']['auto_play'] ?? 0,
				'video_mute'      => $right_answer['right_answer_video']['mute'] ?? 0,
				'audio_audio_id'  => $right_answer['right_answer_audio']['audio_id'] ?? 0,
				'audio_auto_play' => $right_answer['right_answer_audio']['auto_play'] ?? 0,
				'audio_once'      => $right_answer['right_answer_audio']['once'] ?? 0,
				'gallery'         => $right_answer['right_answer_gallery_item'] ?? array(),
				'order'           => $right_answer['right_answer_info_order'] ?? $default_order,
			);

			delete_post_meta( $level->ID, 'right_answer' );

			foreach ( $right_settings as $key => $setting ) {
				if ( ! empty( $setting ) || is_numeric( $setting ) ) {
					update_post_meta( $level->ID, '_right_' . $key, $setting );
				}
			}
		}

		if ( $wrong_answer ) {

			$wrong_settings = array(
				'desc'            => $wrong_answer['wrong_answer_descr'],
				'video_code'      => $wrong_answer['wrong_answer_video']['code'] ?? '',
				'video_auto_play' => $wrong_answer['wrong_answer_video']['auto_play'] ?? 0,
				'video_mute'      => $wrong_answer['wrong_answer_video']['mute'] ?? 0,
				'audio_audio_id'  => $wrong_answer['wrong_answer_audio']['audio_id'] ?? 0,
				'audio_auto_play' => $wrong_answer['wrong_answer_audio']['auto_play'] ?? 0,
				'audio_once'      => $wrong_answer['wrong_answer_audio']['once'] ?? 0,
				'gallery'         => $wrong_answer['wrong_answer_gallery_item'] ?? array(),
				'order'           => $wrong_answer['wrong_answer_info_order'] ?? $default_order,
			);

			delete_post_meta( $level->ID, 'wrong_answer' );

			foreach ( $wrong_settings as $key => $setting ) {
				if ( ! empty( $setting ) || is_numeric( $setting ) ) {
					update_post_meta( $level->ID, '_wrong_' . $key, $setting );
				}
			}
		}

		wp_update_post( array(
			'ID'           => $level->ID,
			'post_type'    => 'level',
			'post_title'   => '',
			'post_content' => ''
		) );
	}
}

function gp_update_100_orders() {
	global $wpdb;
	$wpdb->query( "
	UPDATE {$wpdb->postmeta}
	SET meta_key = REPLACE(meta_key, '_idv_', '_gp_')
	WHERE meta_key IN ('_idv_formatted_invoice_number', '_idv_invoice_number', '_idv_invoice_year', '_idv_invoice_date');
	" );
}

function gp_update_100_users() {
	$users = get_users();
	foreach ( $users as $user ) {
		$paypal_action = get_user_meta( $user->ID, 'paypall_finish' );
		if ( is_numeric( $paypal_action ) ) {
			update_user_meta( $user->ID, '_paypall_in_action', ( $paypal_action === 0 ) ? 1 : 0 );
			delete_user_meta( $user->ID, 'paypall_finish' );
		}

		if ( in_array( 'author', $user->roles ) ) {
			$user->remove_role( 'author' );
			$user->add_role( 'player' );
		}

		if ( in_array( 'gameadmin', $user->roles ) ) {
			$user->remove_role( 'gameadmin' );
			$user->add_role( 'game_manager' );
		}
	}
}

function gp_update_100_clear_db() {
	global $wpdb;
	$wpdb->query( "
		SELECT * FROM {$wpdb->posts} as p
		  LEFT JOIN {$wpdb->term_relationships} as tr
		    ON p.ID = tr.object_id
		WHERE tr.object_id IS NULL
		  AND p.post_type = 'level';
	" );
}

function gp_update_100_update_invoices() {
	$orders = get_posts( array(
		'numberposts' => - 1,
		'post_type'   => 'wpsc_cart_orders',
		'post_status' => 'any'
	) );

	foreach ( $orders as $order ) {
		$old_date = get_post_meta( $order->ID, '_gp_invoice_date', true );
		if ( $old_date ) {
			$date = date_create_from_format( 'd-m-Y', $old_date );
			update_post_meta( $order->ID, '_gp_invoice_date', date_format( $date, 'Y-m-d H:i:s' ) );
		}
		delete_post_meta( $order->ID, '_gp_invoice_year' );
	}
}

function gp_update_100_move_invoices() {
	$upload_dir = wp_upload_dir();
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
	require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
	$file_system = new \WP_Filesystem_Direct( '' );
	foreach ( glob( $upload_dir['basedir'] . '/idv-invoices/*' ) as $dir ) {
		$file_system->move( $dir, $upload_dir['basedir'] . '/gp-invoices/' . basename( $dir ), true );
	}
	$file_system->delete( $upload_dir['basedir'] . '/idv-invoices' );
}

function gp_update_100_db_version() {
	GP\GP_Install::update_db_version( '1.0.0' );
}
