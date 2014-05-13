<?php

class ITSEC_Tweaks {

	private $settings;

	function run() {

		$this->settings = get_site_option( 'itsec_tweaks' );

		//remove wp-generator meta tag
		if ( isset( $this->settings['generator_tag'] ) && $this->settings['generator_tag'] == true ) {
			remove_action( 'wp_head', 'wp_generator' );
		}

		//remove wlmanifest link if turned on
		if ( isset( $this->settings['wlwmanifest_header'] ) && $this->settings['wlwmanifest_header'] == true ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		//remove rsd link from header if turned on
		if ( isset( $this->settings['edituri_header'] ) && $this->settings['edituri_header'] == true ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		//Disable XML-RPC
		if ( isset( $this->settings['disable_xmlrpc'] ) && $this->settings['disable_xmlrpc'] == 2 ) {

			add_filter( 'xmlrpc_enabled', array( $this, 'empty_return_function' ) );
			add_filter( 'bloginfo_url', array( $this, 'remove_pingback_url' ), 10, 2 );

		}

		if ( isset( $this->settings['disable_xmlrpc'] ) && $this->settings['disable_xmlrpc'] == 1 ) {
			add_filter( 'xmlrpc_methods', array( $this, 'xmlrpc_methods' ) );
		}

		//ban extra-long urls if turned on
		if ( ( ! isset( $itsec_globals['is_iwp_call'] ) || $itsec_globals['is_iwp_call'] === false ) && isset( $this->settings['long_url_strings'] ) && $this->settings['long_url_strings'] == true && ! is_admin() ) {

			if ( ! strpos( $_SERVER['REQUEST_URI'], 'infinity=scrolling&action=infinite_scroll' ) && ( strlen( $_SERVER['REQUEST_URI'] ) > 255 || strpos( $_SERVER['REQUEST_URI'], 'eval(' ) || strpos( $_SERVER['REQUEST_URI'], 'CONCAT' ) || strpos( $_SERVER['REQUEST_URI'], 'UNION+SELECT' ) || strpos( $_SERVER['REQUEST_URI'], 'base64' ) )

			) {
				@header( 'HTTP/1.1 414 Request-URI Too Long' );
				@header( 'Status: 414 Request-URI Too Long' );
				@header( 'Cache-Control: no-cache, must-revalidate' );
				@header( 'Expires: Thu, 22 Jun 1978 00:28:00 GMT' );
				@header( 'Connection: Close' );
				@exit;

			}

		}

		//display random number for wordpress version if turned on
		if ( ( ! isset( $itsec_globals['is_iwp_call'] ) || $itsec_globals['is_iwp_call'] === false ) && isset( $this->settings['random_version'] ) && $this->settings['random_version'] == true ) {
			add_action( 'plugins_loaded', array( $this, 'random_version' ) );
		}

		//remove theme update notifications if turned on
		if ( ( ! isset( $itsec_globals['is_iwp_call'] ) || $itsec_globals['is_iwp_call'] === false ) && isset( $this->settings['theme_updates'] ) && $this->settings['theme_updates'] == true ) {
			add_action( 'plugins_loaded', array( $this, 'theme_updates' ) );
		}

		//remove plugin update notifications if turned on
		if ( ( ! isset( $itsec_globals['is_iwp_call'] ) || $itsec_globals['is_iwp_call'] === false ) && isset( $this->settings['plugin_updates'] ) && $this->settings['plugin_updates'] == true ) {
			add_action( 'plugins_loaded', array( $this, 'public_updates' ) );
		}

		//remove core update notifications if turned on
		if ( ( ! isset( $itsec_globals['is_iwp_call'] ) || $itsec_globals['is_iwp_call'] === false ) && isset( $this->settings['core_updates'] ) && $this->settings['core_updates'] == true ) {
			add_action( 'plugins_loaded', array( $this, 'core_updates' ) );
		}

		//Execute jQuery check
		add_action( 'wp_print_scripts', array( $this, 'get_jquery_version' ) );

		if ( isset( $this->settings['safe_jquery'] ) && $this->settings['safe_jquery'] == true ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'current_jquery' ) );
		}

		//Process remove login errors
		if ( isset( $this->settings['login_errors'] ) && $this->settings['login_errors'] === true ) {
			add_filter( 'login_errors', array( $this, 'empty_return_function' ) );
		}

		//Process remove extra author archives
		if ( isset( $this->settings['disable_unused_author_pages'] ) && $this->settings['disable_unused_author_pages'] === true ) {
			add_action( 'template_redirect', array( $this, 'disable_unused_author_pages' ) );
		}

		//Process require unique nicename
		if ( isset( $this->settings['force_unique_nicename'] ) && $this->settings['force_unique_nicename'] === true ) {
			add_action( 'user_profile_update_errors', array( $this, 'force_unique_nicename' ), 10, 3 );
		}

	}

	public function current_jquery() {

		global $itsec_is_old_admin;

		if ( ! is_admin() && ! $itsec_is_old_admin ) {

			wp_deregister_script( 'jquery' );
			wp_deregister_script( 'jquery-core' );

			wp_register_script( 'jquery', false, array( 'jquery-core', 'jquery-migrate' ), '1.10.2' );
			wp_register_script( 'jquery-core', '/wp-includes/js/jquery/jquery.js', false, '1.10.2' );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-core' );

		}

	}

	/**
	 * Prevent non-admin users from seeing core updates
	 *
	 * @return void
	 */
	function core_updates() {

		if ( ! current_user_can( 'manage_options' ) ) {

			remove_action( 'admin_notices', 'update_nag', 3 );
			add_filter( 'pre_site_transient_update_core', array( $this, 'empty_return_function' ) );
			wp_clear_scheduled_hook( 'wp_version_check' );

		}

	}

	/**
	 * Redirects to 404 page if the requested author has 0 posts.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function disable_unused_author_pages() {

		global $wp_query;

		if ( is_author() && $wp_query->post_count < 1 ) {

			ITSEC_Lib::set_404();

		}

	}

	/**
	 * Returns null
	 *
	 * @return null
	 */
	public function empty_return_function() {

		return null;

	}

	/**
	 * Requires a unique nicename on profile update or activate.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function force_unique_nicename( &$errors, $update, &$user ) {

		$display_name = isset( $user->display_name ) ? $user->display_name : ITSEC_Lib::get_random( 14 );

		if ( ! empty( $user->nickname ) ) {

			if ( $user->nickname == $user->user_login ) {

				$errors->add( 'user_error', __( 'Your Nickname must be different than your login name. Please choose a different Nickname.', 'it-l10n-better-wp-security' ) );

			} else {

				$user->user_nicename = sanitize_title( $user->nickname, $display_name );

			}

		} elseif ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {

			$full_name = $user->first_name . ' ' . $user->last_name;

			$user->nickname = $full_name;

			$user->user_nicename = sanitize_title( $full_name, $display_name );

		} else {

			$errors->add( 'user_error', __( 'A Nickname is required. Please choose a nickname or fill out your first and last name.', 'it-l10n-better-wp-security' ) );

		}

	}

	/**
	 * Gets the version of jQuery enqueued
	 *
	 * @return string|array versions of jQuery used ( array if multiple )
	 */
	function get_jquery_version() {

		global $wp_scripts;

		if ( ( is_home() || is_front_page() ) && is_user_logged_in() ) {

			// Get the WP built-in version
			$jquery_ver = $wp_scripts->registered['jquery']->ver;

			update_site_option( 'itsec_jquery_version', $jquery_ver );

		}

	}

	/**
	 * Removes plugin update notification for non-admin users
	 *
	 * @return void
	 */
	function public_updates() {

		if ( ! current_user_can( 'manage_options' ) ) {

			remove_action( 'load-update-core.php', 'wp_update_plugins' );
			add_filter( 'pre_site_transient_update_plugins', array( $this, 'empty_return_function' ) );
			wp_clear_scheduled_hook( 'wp_update_plugins' );

		}

	}

	/**
	 * Display random WordPress version
	 *
	 * @return void
	 */
	function random_version() {

		global $wp_version;

		$new_version = get_site_transient( 'itsec_random_version' );

		if ( $new_version === false ) {

			$new_version = mt_rand( 100, 500 );
			set_site_transient( 'itsec_random_version', $new_version, 86400 );

		}

		//always show real version to site administrators
		if ( ! current_user_can( 'manage_options' ) ) {

			$wp_version = $new_version;
			add_filter( 'script_loader_src', array( $this, 'remove_script_version' ), 15, 1 );
			add_filter( 'style_loader_src', array( $this, 'remove_script_version' ), 15, 1 );

		}

	}

	/**
	 * Removes the pingback header
	 *
	 * @param string $output
	 * @param string $show
	 *
	 * @return array
	 */
	function remove_pingback_url( $output, $show ) {

		if ( $show == 'pingback_url' ) {
			$output = '';
		}

		return $output;
	}

	/**
	 * removes version number on header scripts
	 *
	 * @param string $src script source link
	 *
	 * @return string script source link without version
	 */
	function remove_script_version( $src ) {

		if ( strpos( $src, 'ver=' ) ) {
			return substr( $src, 0, strpos( $src, 'ver=' ) - 1 );
		} else {
			return $src;
		}

	}

	/**
	 * Remove option to update themes for non admins
	 *
	 * @return void
	 */
	function theme_updates() {

		if ( ! current_user_can( 'manage_options' ) ) {

			remove_action( 'load-update-core.php', 'wp_update_themes' );
			add_filter( 'pre_site_transient_update_themes', array( $this, 'empty_return_function' ) );
			wp_clear_scheduled_hook( 'wp_update_themes' );

		}

	}

	/**
	 * Removes the pingback ability from XMLRPC
	 *
	 * @since 4.0.20
	 *
	 * @param array $methods XMLRPC methods
	 *
	 * @return array XMLRPC methods
	 */
	public function xmlrpc_methods( $methods ) {

		if ( isset( $methods['pingback.ping'] ) ) {
			unset( $methods['pingback.ping'] );
		}

		if ( isset( $methods['pingback.extensions.getPingbacks'] ) ) {
			unset( $methods['pingback.extensions.getPingbacks'] );
		}

		return $methods;

	}

}