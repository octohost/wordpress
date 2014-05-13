<?php

class ITSEC_Four_Oh_Four {

	private
		$settings;

	function run() {

		$this->settings = get_site_option( 'itsec_four_oh_four' );

		add_filter( 'itsec_lockout_modules', array( $this, 'register_lockout' ) );
		add_filter( 'itsec_logger_modules', array( $this, 'register_logger' ) );

		add_action( 'wp_head', array( $this, 'check_404' ) );

	}

	/**
	 * If the page is a WordPress 404 error log it and register for lockout
	 *
	 * @return void
	 */
	public function check_404() {

		global $itsec_logger, $itsec_lockout;

		if ( $this->settings['enabled'] === true && is_404() ) {

			$uri = explode( '?', $_SERVER['REQUEST_URI'] );

			if ( ! is_array( $this->settings['white_list'] ) ) {
				$this->settings['white_list'] = explode( PHP_EOL, $this->settings['white_list'] );
			}

			if ( in_array( $uri[0], $this->settings['white_list'] ) === false ) {

				$itsec_logger->log_event(
				             'four_oh_four',
				             3,
				             array(
					             'query_string' => isset( $uri[1] ) ? esc_sql( $uri[1] ) : '',
				             ),
				             ITSEC_Lib::get_ip(),
				             '',
				             '',
				             esc_sql( $uri[0] ),
				             isset( $_SERVER['HTTP_REFERER'] ) ? esc_sql( $_SERVER['HTTP_REFERER'] ) : ''
				);

				$itsec_lockout->do_lockout( 'four_oh_four' );

			}

		}

	}

	/**
	 * Register 404 detection for lockout
	 *
	 * @param  array $lockout_modules array of lockout modules
	 *
	 * @return array                   array of lockout modules
	 */
	public function register_lockout( $lockout_modules ) {

		if ( $this->settings['enabled'] === true ) {

			$lockout_modules['four_oh_four'] = array(
				'type'   => 'four_oh_four',
				'reason' => __( 'too many attempts to access a file that does not exist', 'it-l10n-better-wp-security' ),
				'host'   => $this->settings['error_threshold'],
				'period' => $this->settings['check_period']
			);

		}

		return $lockout_modules;

	}

	/**
	 * Register 404 and file change detection for logger
	 *
	 * @param  array $logger_modules array of logger modules
	 *
	 * @return array                   array of logger modules
	 */
	public function register_logger( $logger_modules ) {

		if ( $this->settings['enabled'] === true ) {

			$logger_modules['four_oh_four'] = array(
				'type'     => 'four_oh_four',
				'function' => __( '404 Error', 'it-l10n-better-wp-security' ),
			);

		}

		return $logger_modules;

	}

}