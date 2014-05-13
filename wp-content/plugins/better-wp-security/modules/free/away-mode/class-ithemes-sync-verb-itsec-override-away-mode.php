<?php

class Ithemes_Sync_Verb_ITSEC_Override_Away_Mode extends Ithemes_Sync_Verb {

	public static $name = 'itsec-override-away-mode';
	public static $description = 'Override current away mode status.';

	public $default_arguments = array(
		'id' => '', //lockout id to release
	);

	public function run( $arguments ) {

		global $itsec_globals;

		$current_status  = ITSEC_Away_Mode::check_away( NULL, true );
		$intention       = sanitize_text_field( $arguments['intention'] );
		$saved_options   = get_site_option( 'itsec_away_mode_sync_override' );
		$saved_intention = isset( $saved_options['intention'] ) ? $saved_options['intention'] : false;

		switch ( $intention ) {

			case 'activate': //process activation

				if ( $saved_intention === false && $current_status < 0 ) { //option doesn't already exist

					$success = add_site_option( 'itsec_away_mode_sync_override', array(
						'intention' => $intention,
						'expires'   => ( $itsec_globals['current_time'] + absint( $current_status ) )
					) );

				} elseif ( $saved_intention == 'deactivate' ) { //allready tried to override

					$success = delete_site_option( 'itsec_away_mode_sync_override' );

				}

				break;

			case 'deactivate': //process deactivation

				if ( $saved_intention === false && $current_status > 0 ) {

					$success = add_site_option( 'itsec_away_mode_sync_override', array(
						'intention' => $intention,
						'expires'   => ( $itsec_globals['current_time'] + absint( $current_status ) )
					) );

				} elseif ( $saved_intention == 'activate' ) {

					$success = delete_site_option( 'itsec_away_mode_sync_override' );

				}

				break;

			default: //invalid intention

				$success = false;

				break;

		}

		if ( $success === false ) {
			$status = 'error';
		} else {
			$status = $intention . 'd';
		}

		return array(
			'api'    => '0',
			'status' => $status
		);

	}

}