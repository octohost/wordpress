<?php

class Ithemes_Sync_Verb_ITSEC_Get_Away_Mode extends Ithemes_Sync_Verb {

	public static $name = 'itsec-get-away-mode';
	public static $description = 'Retrieve current away mode status.';

	public $default_arguments = array();

	public function run( $arguments ) {

		$away = ITSEC_Away_Mode::check_away( NULL, true );

		if ( $away !== false && $away > 0 ) {

			$away_enabled = true;
			$next    = $away;

		} elseif ( $away !== false ) {

			$away_enabled = false;
			$next    = absint( $away );

		}

		return array(
			'api'       => '0',
			'enabled'   => $away_enabled,
			'next'      => $next,
		);

	}

}
