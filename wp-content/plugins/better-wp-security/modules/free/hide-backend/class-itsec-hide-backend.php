<?php

class ITSEC_Hide_Backend {

	private
		$settings,
		$auth_cookie_expired;

	function run() {

		$this->settings = get_site_option( 'itsec_hide_backend' );

		//Execute module functions on frontend init
		if ( $this->settings['enabled'] === true ) {

			$this->auth_cookie_expired = false;

			add_action( 'auth_cookie_expired', array( $this, 'auth_cookie_expired' ) );
			add_action( 'init', array( $this, 'execute_hide_backend' ), 1000 );
			add_action( 'login_init', array( $this, 'execute_hide_backend_login' ) );
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

			add_filter( 'body_class', array( $this, 'remove_admin_bar' ) );
			add_filter( 'wp_redirect', array( $this, 'filter_login_url' ), 10, 2 );
			add_filter( 'site_url', array( $this, 'filter_login_url' ), 10, 2 );
			add_filter( 'retrieve_password_message', array( $this, 'retrieve_password_message' ) );

			remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

		}

	}

	/**
	 * Lets the module know that this is a reauthorization
	 *
	 * @since 4.1
	 *
	 * @return void
	 */
	public function auth_cookie_expired() {

		$this->auth_cookie_expired = true;
		wp_clear_auth_cookie();

	}

	/**
	 * Execute hide backend functionality
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function execute_hide_backend() {

		if ( get_site_option( 'users_can_register' ) == 1 && isset( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] == ITSEC_Lib::get_home_root() . $this->settings['register'] ) {

			wp_redirect( wp_login_url() . '?action=register' );
			exit;

		}

		//redirect wp-admin and wp-register.php to 404 when not logged in
		if (
			(
				(
					get_site_option( 'users_can_register' ) == false &&
					(
						isset( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] == ITSEC_Lib::get_home_root() . 'wp-register.php' ||
						isset( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] == ITSEC_Lib::get_home_root() . 'wp-signup.php'
					)
				) ||
				(
					isset( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] == ITSEC_Lib::get_home_root() . 'wp-login.php' && is_user_logged_in() !== true
				) ||
				( is_admin() && is_user_logged_in() !== true ) ||
				(
					$this->settings['register'] != 'wp-register.php' &&
					strpos( $_SERVER['REQUEST_URI'], 'wp-register.php' ) !== false ||
					strpos( $_SERVER['REQUEST_URI'], 'wp-signup.php' ) !== false ||
					(
						isset( $_REQUEST['redirect_to'] ) &&
						strpos( $_REQUEST['redirect_to'], 'wp-admin/customize.php' ) !== false

					)
				)
			) &&
			strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) === false
			&& $this->auth_cookie_expired === false
		) {

			global $itsec_is_old_admin;

			$itsec_is_old_admin = true;

			if ( isset( $this->settings['theme_compat'] ) && $this->settings['theme_compat'] === true ) { //theme compat (process theme and redirect to a 404)

				wp_redirect( ITSEC_Lib::get_home_root() . sanitize_title( isset( $this->settings['theme_compat_slug'] ) ? $this->settings['theme_compat_slug'] : 'not_found' ), 302 );
				exit;

			} else { //just set the current page as a 404

				add_action( 'wp_loaded', array( $this, 'set_404' ) );

			}

		}

		$url_info                  = parse_url( $_SERVER['REQUEST_URI'] );
		$login_path                = site_url( $this->settings['slug'], 'relative' );
		$login_path_trailing_slash = site_url( $this->settings['slug'] . '/', 'relative' );

		if ( $url_info['path'] === $login_path || $url_info['path'] === $login_path_trailing_slash ) {

			if ( ! is_user_logged_in() ) {
				//Add the login form

				if ( isset( $this->settings['post_logout_slug'] ) && strlen( trim( $this->settings['post_logout_slug'] ) ) > 0 && isset( $_GET['action'] ) && sanitize_text_field( $_GET['action'] ) == trim( $this->settings['post_logout_slug'] ) ) {
					do_action( 'itsec_custom_login_slug' ); //add hook here for custom users
				}

				//suppress error messages due to timing
				error_reporting( 0 );
				@ini_set( 'display_errors', 0 );

				status_header( 200 );

				//don't allow domain mapping to redirect
				if ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING == 1 ) {
					remove_action( 'login_head', 'redirect_login_to_orig' );
				}

				if ( ! function_exists( 'login_header' ) ) {

					include( ABSPATH . 'wp-login.php' );
					exit;

				}

			} elseif ( ! isset( $_GET['action'] ) || ( sanitize_text_field( $_GET['action'] ) != 'logout' && sanitize_text_field( $_GET['action'] ) != 'postpass' && ( isset( $this->settings['post_logout_slug'] ) && strlen( trim( $this->settings['post_logout_slug'] ) ) > 0 && sanitize_text_field( $_GET['action'] ) != trim( $this->settings['post_logout_slug'] ) ) ) ) {
				//Just redirect them to the dashboard (for logged in users)

				if ( $this->auth_cookie_expired === false ) {

					wp_redirect( get_admin_url() );
					exit();

				}

			} elseif ( isset( $_GET['action'] ) && ( sanitize_text_field( $_GET['action'] ) == 'postpass' || ( isset( $this->settings['post_logout_slug'] ) && strlen( trim( $this->settings['post_logout_slug'] ) ) > 0 && sanitize_text_field( $_GET['action'] ) == trim( $this->settings['post_logout_slug'] ) ) ) ) {
				//handle private posts for

				if ( isset( $this->settings['post_logout_slug'] ) && strlen( trim( $this->settings['post_logout_slug'] ) ) > 0 && sanitize_text_field( $_GET['action'] ) == trim( $this->settings['post_logout_slug'] ) ) {
					do_action( 'itsec_custom_login_slug' ); //add hook here for custom users
				}

				//suppress error messages due to timing
				error_reporting( 0 );
				@ini_set( 'display_errors', 0 );

				status_header( 200 ); //its a good login page. make sure we say so

				//include the login page where we need it
				if ( ! function_exists( 'login_header' ) ) {
					include( ABSPATH . '/wp-login.php' );
					exit;
				}

				//Take them back to the page if we need to
				if ( isset( $_SERVER['HTTP_REFERRER'] ) ) {
					wp_redirect( sanitize_text_field( $_SERVER['HTTP_REFERRER'] ) );
					exit();
				}

			}

		}

	}

	/**
	 * Filter the old login page out
	 *
	 * @return void
	 */
	public
	function execute_hide_backend_login() {

		if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) ) { //are we on the login page

			global $itsec_is_old_admin;

			$itsec_is_old_admin = true;

			ITSEC_Lib::set_404();

		}

	}

	/**
	 * Filters redirects for correct login URL
	 *
	 * @param  string $url URL redirecting to
	 *
	 * @return string       Correct redirect URL
	 */
	public
	function filter_login_url( $url ) {

		return str_replace( 'wp-login.php', $this->settings['slug'], $url );

	}

	/**
	 * Actions for plugins loaded.
	 *
	 * Makes certain logout is processed on NGINX.
	 *
	 * @return void
	 */
	public
	function plugins_loaded() {

		if ( is_user_logged_in() && isset( $_GET['action'] ) && sanitize_text_field( $_GET['action'] ) == 'logout' ) {

			check_admin_referer( 'log-out' );
			wp_logout();

			$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : 'wp-login.php?loggedout=true';
			wp_safe_redirect( $redirect_to );
			exit();

		}

	}

	/**
	 * Removes the admin bar class from the body tag
	 *
	 * @param  array $classes body tag classes
	 *
	 * @return array          body tag classes
	 */
	function remove_admin_bar( $classes ) {

		if ( is_admin() && is_user_logged_in() !== true ) {

			foreach ( $classes as $key => $value ) {

				if ( $value == 'admin-bar' ) {
					unset( $classes[$key] );
				}

			}

		}

		return $classes;

	}

	public function retrieve_password_message( $message ) {

		return str_replace( 'wp-login.php', $this->settings['slug'], $message );

		return $message;

	}

	/**
	 * Sets 404 error at later time.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	public
	function set_404() {

		ITSEC_Lib::set_404();

	}

}
