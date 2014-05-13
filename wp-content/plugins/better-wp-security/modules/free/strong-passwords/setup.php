<?php

if ( ! class_exists( 'ITSEC_Strong_Passwords_Setup' ) ) {

	class ITSEC_Strong_Passwords_Setup {

		private
			$defaults;

		public function __construct() {

			global $itsec_setup_action;

			$this->defaults = array(
				'enabled' => false,
				'roll'    => 'administrator',
			);

			if ( isset( $itsec_setup_action ) ) {

				switch ( $itsec_setup_action ) {

					case 'activate':
						$this->execute_activate();
						break;
					case 'upgrade':
						$this->execute_upgrade();
						break;
					case 'deactivate':
						$this->execute_deactivate();
						break;
					case 'uninstall':
						$this->execute_uninstall();
						break;

				}

			} else {
				wp_die( 'error' );
			}

		}

		/**
		 * Execute module activation.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function execute_activate() {

			$options = get_site_option( 'itsec_strong_passwords' );

			if ( $options === false ) {

				add_site_option( 'itsec_strong_passwords', $this->defaults );

			}

		}

		/**
		 * Execute module deactivation
		 *
		 * @return void
		 */
		public function execute_deactivate() {
		}

		/**
		 * Execute module uninstall
		 *
		 * @return void
		 */
		public function execute_uninstall() {

			$this->execute_deactivate();

			delete_site_option( 'itsec_strong_passwords' );

		}

		/**
		 * Execute module upgrade
		 *
		 * @return void
		 */
		public function execute_upgrade() {

			global $itsec_old_version;

			if ( $itsec_old_version < 4000 ) {

				global $itsec_bwps_options, $itsec_globals;

				$current_options = get_site_option( 'itsec_strong_passwords' );

				if ( $current_options === false ) {
					$current_options = $this->defaults;
				}

				$current_options['enabled'] = isset( $itsec_bwps_options['st_enablepassword'] ) && $itsec_bwps_options['st_enablepassword'] == 1 ? true : false;
				$current_options['roll']    = isset( $itsec_bwps_options['st_passrole'] ) ? $itsec_bwps_options['st_passrole'] : 'administrator';

				update_site_option( 'itsec_strong_passwords', $current_options );

			}

		}

	}

}

new ITSEC_Strong_Passwords_Setup();