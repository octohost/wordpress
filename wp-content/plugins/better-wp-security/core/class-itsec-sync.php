<?php

/**
 * Handles the abstraction of sync integration
 *
 * @package iThemes-Security
 * @since   4.1
 */
final class ITSEC_Sync {

	private
		$sync_modules;

	function __construct() {

		$this->sync_modules = array(); //array to hold information on modules using this feature

		add_action( 'plugins_loaded', array( $this, 'register_modules' ), 20 );
		add_action( 'ithemes_sync_register_verbs', array( $this, 'ithemes_sync_register_verbs' ) );

	}

	/**
	 * Returns all moodules registered with Sync
	 *
	 * @since 4.1
	 *
	 * @return array sync module registrations
	 */
	public function get_modules() {

		return $this->sync_modules;

	}

	/**
	 * Register verbs for iThemes Sync
	 *
	 * @Since 4.1
	 *
	 * @param object $api iThemes Sync Object
	 *
	 * @return void
	 */
	public function ithemes_sync_register_verbs( $api ) {

		foreach( $this->sync_modules as $module ) {

			if ( isset( $module['verbs'] ) && isset( $module['path'] ) ) {

				foreach( $module['verbs'] as $name => $class ) {

					$api->register( $name, $class, trailingslashit( $module['path'] ) . 'class-ithemes-sync-verb-' . $name . '.php' );

				}

			}

		}

		$api->register( 'itsec-get-everything', 'Ithemes_Sync_Verb_ITSEC_Get_Everything', dirname( __FILE__ ) . '/class-ithemes-sync-verb-itsec-get-everything.php' );

	}

	/**
	 * Register modules that will use the sync service
	 *
	 * @since 4.1
	 *
	 * @return void
	 */
	public function register_modules() {

		$this->sync_modules = apply_filters( 'itsec_sync_modules', $this->sync_modules );

	}

}