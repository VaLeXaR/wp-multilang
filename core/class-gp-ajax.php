<?php

namespace GP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GamePortal GP_AJAX.
 *
 * AJAX Event Handler.
 *
 * @class    GP_AJAX
 * @version  1.0.0
 * @package  GamePortal/Classes
 * @category Class
 * @author   VaLeXaR
 */
class GP_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_gp_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Get GP Ajax Endpoint.
	 *
	 * @param  string $request Optional
	 *
	 * @return string
	 */
	public static function get_endpoint( $request = '' ) {
		return esc_url_raw( add_query_arg( 'gp-ajax', $request ) );
	}

	/**
	 * Set WC AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['gp-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'GP_DOING_AJAX' ) ) {
				define( 'GP_DOING_AJAX', true );
			}
			// Turn off display_errors during AJAX events to prevent malformed JSON
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for GP Ajax Requests
	 */
	private static function gp_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Check for GP Ajax request and fire action.
	 */
	public static function do_gp_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['gp-ajax'] ) ) {
			$wp_query->set( 'gp-ajax', sanitize_text_field( $_GET['gp-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'gp-ajax' ) ) {
			self::gp_ajax_headers();
			do_action( 'gp_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'level_ordering'        => false,
			'audio_upload'          => false,
			'image_upload'          => false,
			'post_delete'           => false,
			'game_delete'           => false,
			'game_copy'             => false,
			'check_new_game'        => false,
			'pay_add_materials'     => false,
			'use_coupon'            => false,
			'paypall_action'        => false,
			'inviting_to_game'      => false,
			'send_answers'          => false,
			'get_qr_code'           => false,
			'json_search_players' => false,
			'generate_invoice'      => false,
			'update_api_key'        => false,
			'get_hint'              => true,
			'get_answer'            => true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_game_portal_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_game_portal_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// GP AJAX can be used for frontend ajax requests
				add_action( 'gp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}


	/**
	 * Ajax request handling for level ordering.
	 */
	public static function level_ordering() {
		global $wpdb;

		parse_str( gp_clean( $_POST['items'] ), $data );

		foreach ( $data as $values ) {
			$count = count( $values );
			for ( $i = 1; $i <= $count; $i ++ ) {
				$wpdb->update( $wpdb->posts, array( 'menu_order' => $i ), array( 'ID' => intval( $values[ $i - 1 ] ) ) );
			}
		}

		$wpdb->flush();

		die();
	}


	public static function audio_upload() {
		check_ajax_referer( 'audio-upload', 'security' );

		$files = array();

		$file_ids = gp_multiple_upload_files( 'audio_upload' );

		foreach ( $file_ids as $file_id ) {
			if ( ! is_wp_error( $file_id ) ) {
				$file_info = get_post_meta( $file_id, '_wp_attachment_metadata', true );
				$files[]   = array(
					'id'     => $file_id,
					'file'   => wp_get_attachment_url( $file_id ),
					'title'  => sprintf( '%s - %s', $file_info['artist'] ?? __( 'Undefined', 'game-portal' ), get_the_title( $file_id ) ),
					'length' => $file_info['length_formatted'] ?? __( 'Undefined', 'idv-game-portal' )
				);
			}
		}

		wp_send_json( $files );
	}


	public static function image_upload() {
		check_ajax_referer( 'image-upload', 'security' );

		$files = array();

		$file_ids = gp_multiple_upload_files( 'image_upload' );

		foreach ( $file_ids as $file_id ) {
			if ( ! is_wp_error( $file_id ) ) {
				$files[] = array(
					'id'   => $file_id,
					'file' => wp_get_attachment_image_url( $file_id, 'game_portal_edit_gallery' )
				);
			}
		}

		wp_send_json( $files );
	}


	public static function post_delete() {
		check_ajax_referer( 'post-delete', 'security' );

		$result = gp_delete_post( gp_clean( $_POST['post_id'] ) );

		if ( is_wp_error( $result ) ) {
			$data = array(
				'message' => $result->get_error_message()
			);
			wp_send_json_error( $data );
		}

		wp_send_json_success();
	}


	public static function game_delete() {
		check_ajax_referer( 'game-delete', 'security', true );

		$result = gp_delete_game( gp_clean( $_POST['game_id'] ) );

		if ( is_wp_error( $result ) ) {
			$data = array(
				'message' => $result->get_error_message()
			);
			wp_send_json_error( $data );
		}

		wp_send_json_success();
	}


	public static function game_copy() {
		check_ajax_referer( 'game-copy', 'security', true );

		$new_game = gp_game_copy( gp_clean( $_POST['game_id'] ) );

		if ( is_wp_error( $new_game ) ) {
			$data = array(
				'message' => $new_game->get_error_message()
			);
			wp_send_json_error( $data );
		}

		$data = array(
			'id'            => $new_game->id,
			'background'    => wp_get_attachment_image_url( $new_game->background_image_id, 'game_portal_game_background' ),
			'thumbnail'     => game_portal_get_game_thumbnail( $new_game->id ),
			'name'          => $new_game->name,
			'done'          => $new_game->get_done(),
			'edit_link'     => $new_game->get_edit_url(),
			'view_link'     => $new_game->get_start()->get_permalink(),
			'purchase_link' => $new_game->get_purchase_url()
		);

		wp_send_json_success( $data );
	}


	public static function check_new_game() {
		check_ajax_referer( 'check-new-game', 'security', true );

		$paypall_in_action = get_user_meta( get_current_user_id(), '_paypall_in_action', true );

		if ( is_numeric( $paypall_in_action ) ) {

			if ( $paypall_in_action ) {
				wp_send_json_error();
			}

			delete_user_meta( get_current_user_id(), '_paypall_in_action' );
			wp_send_json_success();
		}

		wp_send_json_success();
	}


	public static function pay_add_materials() {
		check_ajax_referer( 'pay-add-materials', 'security', true );

		$pay = (int) ( $_POST['pay'] );

		$error = new \WP_Error();

		if ( isset( $_SESSION['simpleCart'] ) ) {

			$game = gp_get_game( $_SESSION['simpleCart'][0]['item_number'] );

			if ( $game && isset( $game->paid_files_price ) ) {

				$add_mat_id = $game->id . '_2';

				if ( $pay ) {

					$product = array(
						'id'    => $add_mat_id,
						'price' => $game->paid_files_price,
						'name'  => __( 'Additional materials ', 'game-portal' )
					);

					$result = gp_add_to_cart( $product );

					if ( is_wp_error( $result ) ) {
						$error->add( 'pay_add_materials_error', $result->get_error_message() );
					} else {

						$data = array(
							'name'        => $product['name'],
							'price'       => $product['price'],
							'item_number' => $product['id']
						);

						wp_send_json_success( $data );
					}
				} else {
					gp_cart_delete_item( $add_mat_id );

					wp_send_json_success();
				}

			} else {
				$error->add( 'pay_add_materials_error', __( 'Error game ID', 'game-portal' ) );
			}

		} else {
			$error->add( 'pay_add_materials_error', __( 'Session error', 'game-portal' ) );
		}

		if ( $error ) {
			$data = array( 'message' => $error->get_error_message() );
			wp_send_json_error( $data );
		}
	}


	public static function use_coupon() {
		check_ajax_referer( 'use-coupon', 'security', true );

		if ( isset( $_POST['form'] ) ) {
			$form_value = array();
			wp_parse_str( esc_attr( $_POST['form'] ), $form_value );

			$coupon_code = esc_attr( $form_value['wpspsc_coupon_code'] );
			if ( $coupon_code ) {
				wpspsc_apply_cart_discount( $coupon_code );
				if ( isset( $_SESSION['simple_cart_id'] ) && ! empty( $_SESSION['simple_cart_id'] ) ) {
					wpspc_update_cart_items_record();
				}

				$add_mat  = array();
				$products = array_values( $_SESSION['simpleCart'] );

				$game = $products[0];
				if ( isset( $products[1] ) ) {
					$add_mat = $products[1];
				}

				$products_data['game_price'] = gp_price( $game['price'] );

				if ( isset( $products[1] ) ) {
					$products_data['add_mat_price'] = gp_price( $add_mat['price'] );
				}

				$data = array(
					'message'                    => $_SESSION['wpspsc_cart_action_msg'],
					'products_data'              => $products_data,
					'products'                   => $products,
					'wpspsc_applied_coupon_code' => isset( $_SESSION['wpspsc_applied_coupon_code'] ) ? $_SESSION['wpspsc_applied_coupon_code'] : ''
				);
				wp_send_json_success( $data );

			} else {
				$data = array( 'message' => __( 'Coupon used failed!', 'idv-game-portal' ) );
				wp_send_json_error( $data );
			}
		}
	}


	public static function paypall_action() {
		check_ajax_referer( 'paypall-action', 'security', true );

		if ( $_POST['paypall_action'] ) {
			update_user_meta( get_current_user_id(), '_paypall_in_action', 1 );
		} else {
			$post_id = $_SESSION['simple_cart_id'];
			$items   = array_values( get_post_meta( $post_id, 'wpsc_cart_items', true ) );
			$total   = 0;
			$data    = array();
			foreach ( $items as $item ) {
				$total += $item['price'];
			}
			if ( $total == 0 ) {
				$game_id = $items[0]['item_number'];
				gp_paying_game( $game_id );
				if ( isset( $_SESSION['wpspsc_applied_coupon_code'] ) ) {
					update_term_meta( $game_id, '_applied_coupon_code', $_SESSION['wpspsc_applied_coupon_code'] );
				}
				reset_wp_cart();
				$data['return_url'] = gp_get_page_permalink( 'myaccount' );
				wp_send_json_success( $data );
			}
		}

		wp_send_json_success();
	}


	public static function inviting_to_game() {
		check_ajax_referer( 'inviting-to-game', 'security', true );

		$receivers = gp_clean( $_POST['receivers'] );
		$message   = gp_clean( $_POST['message'] );
		$game      = get_term( gp_clean( $_POST['game_id'] ) );
		$qr_code   = gp_clean( $_POST['qr_code'] );

		if ( empty( $receivers ) || empty( $message ) ) {
			wp_send_json_error( array( 'message' => __( 'Mail sent failed.', 'game-portal' ) ) );
		}

		if ( is_null( $game ) || is_wp_error( $game ) ) {
			wp_send_json_error( array( 'message' => __( 'Mail sent failed.', 'game-portal' ) ) );

		}

		do_action( 'game_portal_invite_to_game', $receivers, $message, $game->term_id, $qr_code );

		wp_send_json_success( array( 'message' => sprintf( __( 'The invitation was sent to the e-mail. If the email has not come into the Inbox, check the "Spam" folder. If you can not find the confirmation email, please contact us via the feedback form on the page <a href="%s">%s</a>.', 'idv-game-portal' ), get_the_permalink( get_option( 'game_portal_feedback_page_id' ) ), get_the_title( get_option( 'game_portal_feedback_page_id' ) ) ) ) );
	}


	public static function send_answers() {
		check_ajax_referer( 'send-answers', 'security', true );

		$receivers = gp_clean( $_POST['receivers'] );

		if ( empty( $receiver ) ) {
			wp_send_json_error( array( 'message' => __( 'Mail sent failed.', 'game-portal' ) ) );
		}

		$game = get_term( gp_clean( $_POST['game_id'] ) );

		if ( is_null( $game ) || is_wp_error( $game ) ) {
			wp_send_json_error( array( 'message' => __( 'Mail sent failed.', 'game-portal' ) ) );
		}

		do_action( 'game_portal_send_answers', $receivers, $game->term_id );

		wp_send_json_success( array( 'message' => sprintf( __( 'All answers has been sent to the e-mail. If the letter has not come into the Inbox, check the "Spam" folder. If you can not find the confirmation email, please contact us via the feedback form on the page <a href="%s">%s</a>.', 'idv-game-portal' ), get_the_permalink( get_option( 'game_portal_feedback_page_id' ) ), get_the_title( get_option( 'game_portal_feedback_page_id' ) ) ) ) );
	}


	public static function get_qr_code() {
		check_ajax_referer( 'get-qr-code', 'security', true );

		$text = gp_clean( $_POST['text'] );
		if ( $text ) {
			wp_send_json_success( gp_qr_code( gp_clean( $_POST['text'] ) ) );

		}
		wp_send_json_error();
	}


	public static function get_hint() {
		check_ajax_referer( 'get-hint', 'security', true );

		$level   = gp_get_level( gp_clean( $_POST['level_id'] ) );
		$index   = gp_clean( $_POST['index'] );
		$message = '';

		if ( ! is_wp_error( $level ) && isset( $level->level_hint_text ) ) {
			$index = $index + 1;
			$hints = explode( PHP_EOL, $level->level_hint_text );
			if ( ! isset( $hints[ $index ] ) ) {
				$index = 0;
			}
			$message = $hints[ $index ];
		}

		wp_send_json( array(
			'index'   => $index,
			'message' => $message
		) );
	}


	public static function get_answer() {
		check_ajax_referer( 'get-answer', 'security', true );

		$level       = gp_get_level( gp_clean( $_POST['level_id'] ) );
		$user_answer = gp_clean( $_POST['answer'] );

		if ( $user_answer && ! is_wp_error( $level ) ) {
			//Clean string from html and set to lovercase
			$user_answer = mb_strtolower( trim( wp_strip_all_tags( $user_answer ) ) );

			//Replace all non alphanumeric characters
			$user_answer = preg_replace( "~[^a-zÀ-ÖØ-öÿŸА-Яа-я\d]++~ui", '', $user_answer );

			$answers = array_filter( explode( PHP_EOL, $level->level_answer_text ) );

			if ( $answers ) {

				$answers = array_map( function ( $item ) {
					$item = wp_strip_all_tags( $item );
					$item = preg_replace( "~[^a-zÀ-ÖØ-öÿŸА-Яа-я\d]++~ui", '', $item );
					$item = mb_strtolower( trim( $item ) );

					return $item;
				}, $answers );

				//Split user answer by letter and number group
				$user_answer_symb = preg_split( "/(,?\s+)|((?<=[a-zÀ-ÖØ-öÿŸА-Яа-я])(?=\d))|((?<=\d)(?=[a-zÀ-ÖØ-öÿŸА-Яа-я]))/i", $user_answer );

				$correct = false;

				foreach ( $answers as $answer ) {
					//Split right answer by letter and number group
					$answer_symb = preg_split( "/(,?\s+)|((?<=[a-zÀ-ÖØ-öÿŸА-Яа-я])(?=\d))|((?<=\d)(?=[a-zÀ-ÖØ-öÿŸА-Яа-я]))/i", $answer );

					$count_answer_symb = count( $answer_symb );

					if ( count( $user_answer_symb ) >= $count_answer_symb ) {
						foreach ( $answer_symb as $key => $value ) {
							if ( is_numeric( $user_answer_symb[ $key ] ) && is_numeric( $value ) ) {
								if ( (int) $user_answer_symb[ $key ] !== (int) $value ) {
									continue 2;
								}
							} else {
								$pres = 0;
								similar_text( (string) $user_answer_symb[ $key ], (string) $value, $pres );
								if ( $pres < 85 ) {
									continue 2;
								}
							}
						}
					} else {
						continue;
					}
					$correct = true;
					break;
				}

				$game = gp_get_game($level->get_game_id());
				$next_level = '';
				if ( $game->payed || ( $game->author == get_current_user_id() ) || ( $game->public && ( isset( $game->demo_levels ) && ( $level->post->menu_order <= $game->demo_limit ) ) ) ) {
					$next_level = get_permalink( $level->get_next_level() );
				}

				if ( $correct ) {

					$session_levels = GP()->session->get('levels', array());
					foreach ( $session_levels as $k => $item ) {
						if ( $item['id'] == $level->id ) {
							$session_levels[ $k ]['done'] = 1;
							break;
						}
					}
					GP()->session->set('levels', $session_levels);

					$data = array(
						'desc'       => $level->right_desc,
						'video'      => array(
							'code'      => $level->right_video_code,
							'auto_play' => $level->right_video_auto_play,
							'mute'      => $level->right_video_mute,
						),
						'audio'      => array(
							'file'      => wp_attachment_is( 'audio', $level->right_audio_audio_id ) ? wp_get_attachment_url( $level->right_audio_audio_id ) : '',
							'auto_play' => $level->right_audio_auto_play,
							'once'      => $level->right_audio_once
						),
						'info_order' => $level->right_order,
						'next_level' => $next_level
					);

					foreach ( $level->right_gallery as $item ) {
						$data['gallery'][] = array(
							'thumb' => wp_get_attachment_image_url( $item, 'game_portal_game_thumbnail' ),
							'full'  => wp_get_attachment_image_url( $item, 'game_portal_gallery' )
						);
					}

					wp_send_json_success( $data );

				} else {

					$data = array(
						'desc'       => $level->wrong_desc,
						'video'      => array(
							'code'      => $level->wrong_video_code,
							'auto_play' => $level->wrong_video_auto_play,
							'mute'      => $level->wrong_video_mute,
						),
						'audio'      => array(
							'file'      => wp_attachment_is( 'audio', $level->wrong_audio_audio_id ) ? wp_get_attachment_url( $level->wrong_audio_audio_id ) : '',
							'auto_play' => $level->wrong_audio_auto_play,
							'once'      => $level->wrong_audio_once
						),
						'info_order' => $level->wrong_order,
						'next_level' => $next_level
					);

					foreach ( $level->wrong_gallery as $item ) {
						$data['gallery'][] = array(
							'thumb' => wp_get_attachment_image_url( $item, 'game_portal_game_thumbnail' ),
							'full'  => wp_get_attachment_image_url( $item, 'game_portal_gallery' )
						);
					}

					wp_send_json_error( $data );
				}
			}
		}
	}


	/**
	 * When searching using the WP_User_Query, search names (user meta) too.
	 *
	 * @param  object $query
	 *
	 * @return object
	 */
	public static function json_search_player_name( $query ) {
		global $wpdb;

		$term = gp_clean( stripslashes( $_GET['term'] ) );
		$term = $wpdb->esc_like( $term );

		$query->query_from  .= " INNER JOIN {$wpdb->usermeta} AS user_name ON {$wpdb->users}.ID = user_name.user_id AND ( user_name.meta_key = 'first_name' OR user_name.meta_key = 'last_name' ) ";
		$query->query_where .= $wpdb->prepare( " OR user_name.meta_value LIKE %s ", '%' . $term . '%' );
	}


	/**
	 * Search for customers and return json.
	 */
	public static function json_search_players() {
		ob_start();

		check_ajax_referer( 'search-players', 'security' );

		if ( ! current_user_can( 'edit_levels' ) ) {
			die( - 1 );
		}

		$term    = gp_clean( stripslashes( $_GET['term'] ) );
		$exclude = array();

		if ( empty( $term ) ) {
			die();
		}

		if ( ! empty( $_GET['exclude'] ) ) {
			$exclude = array_map( 'intval', explode( ',', $_GET['exclude'] ) );
		}

		$found_customers = array();

		add_action( 'pre_user_query', array( __CLASS__, 'json_search_player_name' ) );

		$customers_query = new \WP_User_Query( array(
			'fields'         => 'all',
			'orderby'        => 'display_name',
			'search'         => '*' . $term . '*',
			'search_columns' => array( 'ID', 'user_login', 'user_email', 'user_nicename' )
		) );

		remove_action( 'pre_user_query', array( __CLASS__, 'json_search_player_name' ) );

		$customers = $customers_query->get_results();

		if ( ! empty( $customers ) ) {
			foreach ( $customers as $customer ) {
				if ( ! in_array( $customer->ID, $exclude ) ) {
					$found_customers[ $customer->ID ] = $customer->display_name . ' (#' . $customer->ID . ' &ndash; ' . sanitize_email( $customer->user_email ) . ')';
				}
			}
		}

		wp_send_json( $found_customers );
	}

	/**
	 * Create/Update API key.
	 */
	public static function update_api_key() {
		ob_start();

		global $wpdb;

		check_ajax_referer( 'update-api-key', 'security' );

		if ( ! current_user_can( 'manage_game_portal' ) ) {
			die( - 1 );
		}

		try {
			if ( empty( $_POST['description'] ) ) {
				throw new \Exception( __( 'Description is missing.', 'game-portal' ) );
			}
			if ( empty( $_POST['user'] ) ) {
				throw new \Exception( __( 'User is missing.', 'game-portal' ) );
			}
			if ( empty( $_POST['permissions'] ) ) {
				throw new \Exception( __( 'Permissions is missing.', 'game-portal' ) );
			}

			$key_id      = absint( $_POST['key_id'] );
			$description = sanitize_text_field( wp_unslash( $_POST['description'] ) );
			$permissions = ( in_array( $_POST['permissions'], array(
				'read',
				'write',
				'read_write'
			) ) ) ? sanitize_text_field( $_POST['permissions'] ) : 'read';
			$user_id     = absint( $_POST['user'] );

			if ( 0 < $key_id ) {
				$data = array(
					'user_id'     => $user_id,
					'description' => $description,
					'permissions' => $permissions
				);

				$wpdb->update(
					$wpdb->prefix . 'game_portal_api_keys',
					$data,
					array( 'key_id' => $key_id ),
					array(
						'%d',
						'%s',
						'%s'
					),
					array( '%d' )
				);

				$data['consumer_key']    = '';
				$data['consumer_secret'] = '';
				$data['message']         = __( 'API Key updated successfully.', 'game-portal' );
			} else {
				$consumer_key    = 'ck_' . gp_rand_hash();
				$consumer_secret = 'cs_' . gp_rand_hash();

				$data = array(
					'user_id'         => $user_id,
					'description'     => $description,
					'permissions'     => $permissions,
					'consumer_key'    => gp_api_hash( $consumer_key ),
					'consumer_secret' => $consumer_secret,
					'truncated_key'   => substr( $consumer_key, - 7 )
				);

				$wpdb->insert(
					$wpdb->prefix . 'game_portal_api_keys',
					$data,
					array(
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s'
					)
				);

				$key_id                  = $wpdb->insert_id;
				$data['consumer_key']    = $consumer_key;
				$data['consumer_secret'] = $consumer_secret;
				$data['message']         = __( 'API Key generated successfully. Make sure to copy your new API keys now. You won\'t be able to see it again!', 'game-portal' );
				$data['revoke_url']      = '<a style="color: #a00; text-decoration: none;" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'revoke-key' => $key_id ), admin_url( 'admin.php?page=gp-settings&tab=api' ) ), 'revoke' ) ) . '">' . __( 'Revoke Key', 'game-portal' ) . '</a>';
			}

			wp_send_json_success( $data );
		}
		catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}


	public static function generate_invoice() {
		check_ajax_referer( 'generate-invoice', 'security' );

		$order_id = gp_clean( $_POST['order_id'] );

		$invoice = new GP_Invoice_Document( $order_id );

		if ( ! $invoice->exists() ) {
			$invoice->save( "F" );
		}
		echo '<a href="' . wp_nonce_url( admin_url( '?preview_game_portal_invoice=true&order_id=' . $order_id ), 'preview-invoice' )  . '" class="button">' . __( 'Download', 'game-portal' ) . '</a>';
		die();
	}
}
