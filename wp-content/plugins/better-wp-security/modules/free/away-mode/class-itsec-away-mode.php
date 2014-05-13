<?php

class ITSEC_Away_Mode {

	function run() {

		//Execute away mode functions on admin init
		add_filter( 'itsec_logger_modules', array( $this, 'register_logger' ) );
		add_action( 'itsec_admin_init', array( $this, 'execute_away_mode' ) );
		add_action( 'login_init', array( $this, 'execute_away_mode' ) );

		//Register Sync
		add_filter( 'itsec_sync_modules', array( $this, 'register_sync' ) );

	}

	/**
	 * Check if away mode is active
	 *
	 * @since 4.4
	 *
	 * @param array $input     [NULL] Input of options to check if calling from form
	 * @param bool  $remaining will return the number of seconds remaining
	 * @param bool  $override  Whether or not we're calculating override values
	 *
	 * @return mixed true if locked out else false or times until next condition (negative until lockout, positive until release)
	 */
	public static function check_away( $input = NULL, $remaining = false, $override = false ) {

		global $itsec_globals;

		ITSEC_Lib::clear_caches(); //lets try to make sure nothing is storing a bad time

		$form          = true;
		$has_away_file = @file_exists( $itsec_globals['ithemes_dir'] . '/itsec_away.confg' );
		$status        = false; //assume they're not locked out to start

		//Normal usage check
		if ( $input === NULL ) { //if we didn't provide input to check we need to get it

			$form  = false;
			$input = get_site_option( 'itsec_away_mode' );

		}

		if ( ( $form === false && ! isset( $input['enabled'] ) ) || ! isset( $input['type'] ) || ! isset( $input['start'] ) || ! isset( $input['end'] ) || ! $has_away_file ) {
			return false; //if we don't have complete settings don't lock them out
		}

		$current_time = $itsec_globals['current_time']; //use current time
		$enabled      = isset( $input['enabled'] ) ? $input['enabled'] : $form;
		$test_type    = $input['type'];
		$test_start   = $input['start'];
		$test_end     = $input['end'];

		if ( $test_type === 1 ) { //daily

			$test_start -= strtotime( date( 'Y-m-d', $test_start ) );
			$test_end -= strtotime( date( 'Y-m-d', $test_end ) );
			$day_seconds = $current_time - strtotime( date( 'Y-m-d', $current_time ) );

			if ( $test_start === $test_end ) {
				$status = false;
			}

			if ( $test_start < $test_end ) { //same day

				if ( $test_start <= $day_seconds && $test_end >= $day_seconds && $enabled === true ) {
					$status = $test_end - $day_seconds;
				}

			} else { //overnight

				if ( ( $test_start < $day_seconds || $test_end > $day_seconds ) && $enabled === true ) {

					if ( $day_seconds >= $test_start ) {

						$status = ( 86400 - $day_seconds ) + $test_end;

					} else {

						$status = $test_end - $day_seconds;

					}

				}

			}

		} else if ( $test_start !== $test_end && $test_start <= $current_time && $test_end >= $current_time && $enabled === true ) { //one time

			$status = $test_end - $current_time;

		}

		//they are allowed to log in
		if ( $status === false ) {

			if ( $test_type === 1 ) {

				if ( $day_seconds > $test_start ) { //actually starts tomorrow

					$status = - ( ( 86400 + $test_start ) - $day_seconds );

				} else { //starts today

					$status = - ( $test_start - $day_seconds );

				}

			} else {

				$status = - ( $test_start - $current_time );

				if ( $status > 0 ) {

					$status = 0;

				}

			}

		}

		if ( $override === false ) {

			//work in an override from sync
			$override_option = get_site_option( 'itsec_away_mode_sync_override' );
			$override        = $override_option['intention'];
			$expires         = $override_option['expires'];

			if ( $expires < $itsec_globals['current_time'] ) {

				delete_site_option( 'itsec_away_mode_sync_override' );

			} else {

				if ( $override === 'activate' ) {

					if ( $status <= 0 ) { //not currently locked out

						$input['start'] = $current_time - 1;

						$status = ITSEC_Away_Mode::check_away( $input, true, true );

					} else {

						delete_site_option( 'itsec_away_mode_sync_override' );

					}

				} elseif ( $override === 'deactivate' ) {

					if ( $status > 0 ) { //currently locked out

						$input['end'] = $current_time - 1;

						$status = ITSEC_Away_Mode::check_away( $input, true, true );

					} else {

						delete_site_option( 'itsec_away_mode_sync_override' );

					}

				}

			}

		}

		if ( $remaining === true ) {

			return $status;

		} else {

			if ( $status > 0 && $status !== false ) {
				return true;
			}

		}

		return false; //always default to NOT locking folks out

	}

	/**
	 * Execute away mode functionality
	 *
	 * @return void
	 */
	public function execute_away_mode() {

		global $itsec_logger;

		//execute lockout if applicable
		if ( $this->check_away() ) {

			$itsec_logger->log_event(
			             'away_mode',
			             5,
			             array(
				             __( 'A host was prevented from accessing the dashboard due to away-mode restrictions being in effect',
				                 'it-l10n-better-wp-security' ),
			             ),
			             ITSEC_Lib::get_ip(),
			             '',
			             '',
			             '',
			             ''
			);

			wp_redirect( get_option( 'siteurl' ) );
			wp_clear_auth_cookie();

		}

	}

	/**
	 * Register 404 and file change detection for logger
	 *
	 * @param  array $logger_modules array of logger modules
	 *
	 * @return array                   array of logger modules
	 */
	public function register_logger( $logger_modules ) {

		$logger_modules['away_mode'] = array(
			'type'     => 'away_mode',
			'function' => __( 'Away Mode Triggered', 'it-l10n-better-wp-security' ),
		);

		return $logger_modules;

	}

	/**
	 * Register Lockouts for Sync
	 *
	 * @param  array $sync_modules array of logger modules
	 *
	 * @return array                   array of logger modules
	 */
	public function register_sync( $sync_modules ) {

		$sync_modules['away_mode'] = array(
			'verbs'      => array(
				'itsec-get-away-mode'      => 'Ithemes_Sync_Verb_ITSEC_Get_Away_Mode',
				'itsec-override-away-mode' => 'Ithemes_Sync_Verb_ITSEC_Override_Away_Mode'
			),
			'everything' => 'itsec-get-away-mode',
			'path'       => dirname( __FILE__ ),
		);

		return $sync_modules;

	}

}
