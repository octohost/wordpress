<?php

class ITSEC_Brute_Force {

	private
		$settings;

	function run() {

		$this->settings = get_site_option( 'itsec_brute_force' );

		//execute login limits
		if ( $this->settings['enabled'] === true ) {

			add_filter( 'authenticate', array( $this, 'execute_brute_force_no_password' ), 30, 3 );
			add_action( 'wp_login_failed', array( $this, 'execute_brute_force' ), 1, 1 );
			add_action( 'wp_login', array( $this, 'execute_brute_force_login_successful' ), 10, 2 );
			add_filter( 'itsec_lockout_modules', array( $this, 'register_lockout' ) );
			add_filter( 'itsec_logger_modules', array( $this, 'register_logger' ) );

		}

	}

	/**
	 * Sends to lockout class when username and password are filled out and wrong
	 *
	 * @param string $username the username attempted
	 */
	public function execute_brute_force( $username ) {

		global $itsec_lockout, $itsec_logger;

		if ( isset( $_POST['log'] ) && $_POST['log'] != '' && isset( $_POST['pwd'] ) && $_POST['pwd'] != '' ) {

			$user_id = username_exists( sanitize_text_field( $username ) );

			if ( $user_id === false || $user_id === NULL ) {

				$itsec_lockout->check_lockout( false, $username );

			} else {

				$itsec_lockout->check_lockout( $user_id );

			};

			$itsec_logger->log_event( 'brute_force', 5, array(), ITSEC_Lib::get_ip(), sanitize_text_field( $username ), intval( $user_id ) );

			$itsec_lockout->do_lockout( 'brute_force', sanitize_text_field( $username ) );

		}

	}

	/**
	 * Sends to lockout class when username and password are filled out and wrong
	 *
	 * @param string $username the username attempted
	 * @param        object    wp_user the user
	 */
	public function execute_brute_force_login_successful( $username, $user = NULL ) {

		global $itsec_lockout;

		if ( ! $user === NULL ) {

			$itsec_lockout->check_lockout( $user );

		} elseif ( is_user_logged_in() ) {

			$current_user = wp_get_current_user();

			$itsec_lockout->check_lockout( $current_user->ID );

		}

	}

	/**
	 * Sends to lockout class when login form isn't completely filled out
	 *
	 * @param object $user     user or wordpress error
	 * @param string $username username attempted
	 * @param string $password password attempted
	 *
	 * @return user object or WordPress error
	 */
	public function execute_brute_force_no_password( $user, $username = '', $password = '' ) {

		global $itsec_lockout, $itsec_logger;

		if ( isset( $_POST['wp-submit'] ) && ( empty( $username ) || empty( $password ) ) ) {

			$user_id = username_exists( sanitize_text_field( $username ) );

			if ( $user_id === false || $user_id === NULL ) {

				$itsec_lockout->check_lockout( false, $username );

			} else {

				$itsec_lockout->check_lockout( $user_id );

			}

			$itsec_logger->log_event( 'brute_force', 5, array(), ITSEC_Lib::get_ip(), sanitize_text_field( $username ), intval( $user_id ) );

			$itsec_lockout->do_lockout( 'brute_force', sanitize_text_field( $username ) );

		}

		return $user;

	}

	/**
	 * Register Brute Force for lockout
	 *
	 * @param  array $lockout_modules array of lockout modules
	 *
	 * @return array                   array of lockout modules
	 */
	public function register_lockout( $lockout_modules ) {

		if ( $this->settings['enabled'] === true ) {

			$lockout_modules['brute_force'] = array(
				'type'   => 'brute_force',
				'reason' => __( 'too many bad login attempts', 'it-l10n-better-wp-security' ),
				'host'   => $this->settings['max_attempts_host'],
				'user'   => $this->settings['max_attempts_user'],
				'period' => $this->settings['check_period']
			);

		}

		return $lockout_modules;

	}

	/**
	 * Register Brute Force for logger
	 *
	 * @param  array $logger_modules array of logger modules
	 *
	 * @return array                   array of logger modules
	 */
	public function register_logger( $logger_modules ) {

		if ( $this->settings['enabled'] === true ) {

			$logger_modules['brute_force'] = array(
				'type'     => 'brute_force',
				'function' => __( 'Invalid Login Attempt', 'it-l10n-better-wp-security' ),
			);

		}

		return $logger_modules;

	}

}