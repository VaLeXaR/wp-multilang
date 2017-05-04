<?php

namespace GP\Libraries;

class GP_Gitlab_Updater {

	private $api_version = 4;

	private $host = 'https://gitlab.com';

	private $file;

	private $plugin;

	private $basename;

	private $active;

	private $username;

	private $repository;

	private $authorize_token;

	private $gitlab_tag;

	private $archive_url;

	public function __construct( $file ) {

		$this->file = $file;

		add_action( 'admin_init', array( $this, 'set_plugin_properties' ) );

		return $this;
	}

	public function set_plugin_properties() {
		$this->plugin = get_plugin_data( $this->file );
		$this->basename = plugin_basename( $this->file );
		$this->active   = is_plugin_active( $this->basename );
	}

	public function set_host( $host ) {
		$this->host = $host;
	}

	public function set_api_version( $api_version ) {
		$this->api_version = $api_version;
	}

	public function set_username( $username ) {
		$this->username = $username;
	}

	public function set_repository( $repository ) {
		$this->repository = $repository;
	}

	public function authorize( $token ) {
		$this->authorize_token = $token;
	}

	private function get_repository_info() {
		if ( is_null( $this->gitlab_tag ) ) { // Do we have a response?
			$request_uri = trailingslashit( $this->host) . 'api/v' . $this->api_version . '/projects/'. $this->username . '%2F'. $this->repository . '/repository/tags'; // Build URI

			if ( $this->authorize_token ) { // Is there an access token?
				$request_uri = add_query_arg( 'private_token', $this->authorize_token, $request_uri ); // Append it
			}

			$response = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_uri ) ), true ); // Get JSON and parse it

			if ( ! is_array( $response) || empty( $response ) ) {
				return;
			}

			$tags = array();

			foreach ( $response as $tag ) {
				if ( ! is_null( $tag['release'] ) ) {
					$tags[] = $tag;
				}
			}

			$tag = current( $tags );

			$this->gitlab_tag = $tag;

			$archive_url = trailingslashit( $this->host) . 'api/v' . $this->api_version . '/projects/'. $this->username . '%2F'. $this->repository . '/repository/archive.zip';

			$archive_url = add_query_arg('sha', $this->gitlab_tag['name'], $archive_url);

			if ( $this->authorize_token ) {
				$archive_url = add_query_arg( 'private_token', $this->authorize_token, $archive_url );
			}

			$this->archive_url = $archive_url;
		}
	}

	public function initialize() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	public function modify_transient( $transient ) {

		if ( property_exists( $transient, 'checked' ) ) { // Check if transient has a checked property

			if ( $checked = $transient->checked ) { // Did Wordpress check for updates?

				$this->get_repository_info(); // Get the repo info

				if ( $this->gitlab_tag ) {

					$out_of_date = version_compare( $this->gitlab_tag['name'], $checked[ $this->basename ], '>' ); // Check if we're out of date

					if ( $out_of_date ) {

						$new_files = $this->archive_url; // Get the ZIP

						$slug = current( explode( '/', $this->basename ) ); // Create valid slug

						$plugin = array( // setup our plugin info
							'url'         => $this->plugin["PluginURI"],
							'slug'        => $slug,
							'package'     => $new_files,
							'new_version' => $this->gitlab_tag['name']
						);

						$transient->response[ $this->basename ] = (object) $plugin; // Return it in response
					}
				}
			}
		}

		return $transient; // Return filtered transient
	}

	public function plugin_popup( $result, $action, $args ) {

		if ( ! empty( $args->slug ) ) { // If there is a slug

			if ( $args->slug == current( explode( '/', $this->basename ) ) ) { // And it's our slug

				$this->get_repository_info(); // Get our repo info

				// Set it to an array
				$plugin = array(
					'name'              => $this->plugin["Name"],
					'slug'              => $this->basename,
					'requires'          => '4.7',
					'tested'            => get_bloginfo('version'),
					'rating'            => 0,
					'num_ratings'       => 0,
					'downloaded'        => 0,
					'added'             => '2016-01-05',
					'version'           => $this->gitlab_tag['name'],
					'author'            => $this->plugin["AuthorName"],
					'author_profile'    => $this->plugin["AuthorURI"],
					'last_updated'      => $this->gitlab_tag['commit']['committed_date'],
					'homepage'          => $this->plugin["PluginURI"],
					'short_description' => $this->plugin["Description"],
					'sections'          => array(
						'Description' => $this->plugin["Description"],
						'Updates'     => $this->gitlab_tag['release']['description'],
					),
					'download_link'     => $this->archive_url
				);

				return (object) $plugin; // Return the data
			}

		}

		return $result; // Otherwise return default
	}

	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem; // Get global FS object

		$install_directory = plugin_dir_path( $this->file ); // Our plugin directory
		$wp_filesystem->move( $result['destination'], $install_directory ); // Move files to the plugin dir
		$result['destination'] = $install_directory; // Set the destination for the rest of the stack

		if ( $this->active ) { // If it was active
			activate_plugin( $this->basename ); // Reactivate
		}

		return $result;
	}
}
