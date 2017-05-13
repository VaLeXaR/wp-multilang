<?php
/**
 * Contains the query functions for WPMPlugin which alter the front-end post queries and loops
 *
 * @class          GP_Query
 * @package        WPMPlugin/Classes
 * @category       Class
 * @author         VaLeXaR
 */

namespace GP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GP_Query Class.
 */
class GP_Query {

	/** @public array Query vars to add to wp */
	public $query_vars = array();

	/**
	 * Constructor for the query class. Hooks in methods.
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ) );
		if ( ! is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'get_errors' ), 20 );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
			add_action( 'wp', array( $this, 'remove_preview_query' ) );
		}
		$this->init_query_vars();
	}

	/**
	 * Get any errors from querystring.
	 */
	public function get_errors() {
		if ( ! empty( $_GET['gp_error'] ) && ( $error = sanitize_text_field( $_GET['gp_error'] ) ) && ! gp_has_notice( $error, 'error' ) ) {
			gp_add_notice( $error, 'error' );
		}
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = array(
			'orders',
			'edit-account',
			'player-logout',
			'lost-password',
			'edit-level',
			'new-level',
			'new-game',
			'edit-game',
			'share-game',
			'purchase-game'
		);
	}

	/**
	 * Get page title for an endpoint.
	 *
	 * @param  string
	 *
	 * @return string
	 */
	public function get_endpoint_title( $endpoint ) {
		global $wp;

		switch ( $endpoint ) {
			case 'orders' :
				if ( ! empty( $wp->query_vars['orders'] ) ) {
					$title = sprintf( __( 'Orders (page %d)', 'game-portal' ), intval( $wp->query_vars['orders'] ) );
				} else {
					$title = __( 'Orders', 'game-portal' );
				}
				break;
			case 'edit-account' :
				$title = __( 'Edit Account', 'game-portal' );
				break;
			case 'lost-password' :
				$title = __( 'Lost Password', 'game-portal' );
				break;
			default :
				$title = apply_filters( 'game_portal_endpoint_' . $endpoint . '_title', '' );
				break;
		}

		return $title;
	}

	/**
	 * Endpoint mask describing the places the endpoint should be added.
	 *
	 * @return int
	 */
	protected function get_endpoints_mask() {
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$page_on_front     = get_option( 'page_on_front' );
			$myaccount_page_id = get_option( 'game_portal_myaccount_page_id' );

			if ( in_array( $page_on_front, array( $myaccount_page_id ) ) ) {
				return EP_ROOT | EP_PAGES;
			}
		}

		return EP_PAGES;
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		$mask = $this->get_endpoints_mask();

		$query_vars = array(
			'orders',
			'edit-account',
			'player-logout',
			'lost-password',
			'new-game',
		);

		foreach ( $query_vars as $var ) {
			if ( ! empty( $var ) ) {
				add_rewrite_endpoint( $var, $mask );
			}
		}
	}

	/**
	 * Add query vars.
	 *
	 * @access public
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->query_vars as $var ) {
			$vars[] = $var;
		}

		return $vars;
	}

	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return $this->query_vars;
	}

	/**
	 * Get query current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;

		foreach ( $this->get_query_vars() as $value ) {
			if ( isset( $wp->query_vars[ $value ] ) ) {
				return $value;
			}
		}

		return '';
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// Map query vars to their keys, or get them if endpoints are not supported
		foreach ( $this->query_vars as $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $var ] = $_GET[ $var ];
			}
		}
	}

	/**
	 * Hook into pre_get_posts to do the main preview query.
	 *
	 * @param mixed $q query object
	 */
	public function pre_get_posts( $q ) {
		// We only want to affect the main query
		if ( ! $q->is_main_query() ) {
			return;
		}

		// Fix for verbose page rules
		if ( $GLOBALS['wp_rewrite']->use_verbose_page_rules && isset( $q->queried_object->ID ) && $q->queried_object->ID === gp_get_page_id( 'games' ) ) {
			$q->set( 'post_type', 'preview' );
			$q->set( 'page', '' );
			$q->set( 'pagename', '' );

			// Fix conditional Functions
			$q->is_archive           = true;
			$q->is_post_type_archive = true;
			$q->is_singular          = false;
			$q->is_page              = false;
		}

		// Fix for endpoints on the homepage
		if ( $q->is_home() && 'page' === get_option( 'show_on_front' ) && absint( get_option( 'page_on_front' ) ) !== absint( $q->get( 'page_id' ) ) ) {
			$_query = wp_parse_args( $q->query );
			if ( ! empty( $_query ) && array_intersect( array_keys( $_query ), array_keys( $this->query_vars ) ) ) {
				$q->is_page     = true;
				$q->is_home     = false;
				$q->is_singular = true;
				$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
				add_filter( 'redirect_canonical', '__return_false' );
			}
		}

		// When orderby is set, WordPress shows posts. Get around that here.
		if ( $q->is_home() && 'page' === get_option( 'show_on_front' ) && absint( get_option( 'page_on_front' ) ) === gp_get_page_id( 'games' ) ) {
			$_query = wp_parse_args( $q->query );
			if ( empty( $_query ) || ! array_diff( array_keys( $_query ), array(
					'preview',
					'page',
					'paged',
					'cpage',
					'orderby'
				) )
			) {
				$q->is_page = true;
				$q->is_home = false;
				$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
				$q->set( 'post_type', 'preview' );
			}
		}

		// Fix product feeds
		if ( $q->is_feed() && $q->is_post_type_archive( 'games' ) ) {
			$q->is_comment_feed = false;
		}

		// Special check for shops with the product archive on front
		if ( $q->is_page() && 'page' === get_option( 'show_on_front' ) && absint( $q->get( 'page_id' ) ) === gp_get_page_id( 'games' ) ) {

			// This is a front-page shop
			$q->set( 'post_type', 'preview' );
			$q->set( 'page_id', '' );

			if ( isset( $q->query['paged'] ) ) {
				$q->set( 'paged', $q->query['paged'] );
			}

			// Define a variable so we know this is the front page shop later on
			define( 'PORTAL_IS_ON_FRONT', true );

			// Get the actual WP page to avoid errors and let us use is_front_page()
			// This is hacky but works. Awaiting https://core.trac.wordpress.org/ticket/21096
			global $wp_post_types;

			$portal_page = get_post( gp_get_page_id( 'games' ) );

			$wp_post_types['preview']->ID         = $portal_page->ID;
			$wp_post_types['preview']->post_title = $portal_page->post_title;
			$wp_post_types['preview']->post_name  = $portal_page->post_name;
			$wp_post_types['preview']->post_type  = $portal_page->post_type;
			$wp_post_types['preview']->ancestors  = get_ancestors( $portal_page->ID, $portal_page->post_type );

			// Fix conditional Functions like is_front_page
			$q->is_singular          = false;
			$q->is_post_type_archive = true;
			$q->is_archive           = true;
			$q->is_page              = true;

			// Remove post type archive name from front page title tag
			add_filter( 'post_type_archive_title', '__return_empty_string', 5 );

			// Fix WP SEO
			if ( class_exists( 'WPSEO_Meta' ) ) {
				add_filter( 'wpseo_metadesc', array( $this, 'wpseo_metadesc' ) );
				add_filter( 'wpseo_metakey', array( $this, 'wpseo_metakey' ) );
			}

			// Only apply to product categories, the product post archive, the shop page, product tags, and product attribute taxonomies
		} elseif ( ! $q->is_post_type_archive( 'preview' ) && ! $q->is_tax( get_object_taxonomies( 'preview' ) ) ) {
			return;
		}

		if (isset($q->query_vars['post_type']) && $q->query_vars['post_type'] == 'preview') {
			$q->set('tax_query', array(
				array(
					'taxonomy' => 'game',
					'operator' => 'EXISTS'
				)
			));
			$q->set('meta_query', array(
				array(
					'key' => 'lang',
					'value' => get_locale()
				)
			));
		}

		$this->preview_query( $q );

		if ( is_search() ) {
			add_filter( 'posts_where', array( $this, 'search_post_excerpt' ) );
			add_filter( 'wp', array( $this, 'remove_posts_where' ) );
		}

		// And remove the pre_get_posts hook
		$this->remove_preview_query();
	}

	/**
	 * Search post excerpt.
	 *
	 * @access public
	 *
	 * @param string $where (default: '')
	 *
	 * @return string (modified where clause)
	 */
	public function search_post_excerpt( $where = '' ) {
		global $wp_the_query;

		// If this is not a WC Query, do not modify the query
		if ( empty( $wp_the_query->query_vars['gp_query'] ) || empty( $wp_the_query->query_vars['s'] ) ) {
			return $where;
		}

		$where = preg_replace(
			"/post_title\s+LIKE\s*(\'\%[^\%]+\%\')/",
			"post_title LIKE $1) OR (post_excerpt LIKE $1", $where );

		return $where;
	}

	/**
	 * WP SEO meta description.
	 *
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @access public
	 * @return string
	 */
	public function wpseo_metadesc() {
		return \WPSEO_Meta::get_value( 'metadesc', gp_get_page_id( 'games' ) );
	}

	/**
	 * WP SEO meta key.
	 *
	 * Hooked into wpseo_ hook already, so no need for function_exist.
	 *
	 * @access public
	 * @return string
	 */
	public function wpseo_metakey() {
		return \WPSEO_Meta::get_value( 'metakey', gp_get_page_id( 'games' ) );
	}

	/**
	 * Query the products, applying sorting/ordering etc. This applies to the main wordpress loop.
	 *
	 * @param mixed $q
	 */
	public function preview_query( $q ) {
		// Ordering query vars
		$ordering = $this->get_catalog_ordering_args();
		$q->set( 'orderby', $ordering['orderby'] );
		$q->set( 'order', $ordering['order'] );
		if ( isset( $ordering['meta_key'] ) ) {
			$q->set( 'meta_key', $ordering['meta_key'] );
		}

		// Query vars that affect posts shown
		$q->set( 'posts_per_page', $q->get( 'posts_per_page' ) ? $q->get( 'posts_per_page' ) : get_option( 'posts_per_page' ) );
		$q->set( 'gp_query', 'preview_query' );
	}


	/**
	 * Remove the query.
	 */
	public function remove_preview_query() {
		remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	/**
	 * Remove the posts_where filter.
	 */
	public function remove_posts_where() {
		remove_filter( 'posts_where', array( $this, 'search_post_excerpt' ) );
	}

	/**
	 * Returns an array of arguments for ordering products based on the selected values.
	 *
	 * @access public
	 * @return array
	 */
	public function get_catalog_ordering_args( $orderby = '', $order = '' ) {

		$orderby = strtolower( $orderby );
		$order   = strtoupper( $order );
		$args    = array();

		// default - menu_order
		$args['orderby']  = 'menu_order title';
		$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
		$args['meta_key'] = '';

		switch ( $orderby ) {
			case 'rand' :
				$args['orderby'] = 'rand';
				break;
			case 'date' :
				$args['orderby'] = 'date ID';
				$args['order']   = $order == 'ASC' ? 'ASC' : 'DESC';
				break;
			case 'title' :
				$args['orderby'] = 'title';
				$args['order']   = $order == 'DESC' ? 'DESC' : 'ASC';
				break;
		}

		return $args;
	}

	/**
	 * Get the meta query which was used by the main query.
	 * @return array
	 */
	public static function get_main_meta_query() {
		global $wp_the_query;

		$args       = $wp_the_query->query_vars;
		$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

		return $meta_query;
	}
}
