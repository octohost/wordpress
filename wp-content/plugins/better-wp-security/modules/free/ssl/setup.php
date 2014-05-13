<?php

if ( ! class_exists( 'ITSEC_SSL_Setup' ) ) {

	class ITSEC_SSL_Setup {

		private
			$defaults;

		public function __construct() {

			global $itsec_setup_action;

			$this->defaults = array(
				'frontend' => 0,
				'admin'    => false,
				'login'    => false,
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

			$options  = get_site_option( 'itsec_ssl' );
			$initials = get_site_option( 'itsec_initials' );

			if ( defined( 'FORCE_SSL_LOGIN' ) && FORCE_SSL_LOGIN === true ) {
				$initials['login'] = true;
			} else {
				$initials['login'] = false;
			}

			if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN === true ) {
				$initials['admin'] = true;
			} else {
				$initials['admin'] = false;
			}

			update_site_option( 'itsec_initials', $initials );

			if ( $options === false ) {

				if ( defined( 'FORCE_SSL_LOGIN' ) && FORCE_SSL_LOGIN === true ) {
					$this->defaults['login'] = true;
				}

				if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN === true ) {
					$this->defaults['admin'] = true;
				}

				add_site_option( 'itsec_ssl', $this->defaults );
				add_site_option( 'itsec_config_changed', true );

			}

		}

		/**
		 * Execute module deactivation
		 *
		 * @return void
		 */
		public function execute_deactivate() {

			global $itsec_files;

			$config_rules[] = ITSEC_SSL_Admin::build_wpconfig_rules( null, true );
			$itsec_files->set_wpconfig( $config_rules );

		}

		/**
		 * Execute module uninstall
		 *
		 * @return void
		 */
		public function execute_uninstall() {

			$this->execute_deactivate();

			delete_site_option( 'itsec_ssl' );

			delete_metadata( 'post', null, 'itsec_enable_ssl', null, true );
			delete_metadata( 'post', null, 'bwps_enable_ssl', null, true );

		}

		/**
		 * Execute module upgrade
		 *
		 * @return void
		 */
		public function execute_upgrade() {

			global $itsec_old_version;

			if ( $itsec_old_version < 4000 ) {

				global $itsec_bwps_options;

				$current_options = get_site_option( 'itsec_ssl' );

				if ( $current_options === false ) {
					$current_options = $this->defaults;
				}

				$current_options['frontend'] = isset( $itsec_bwps_options['ssl_frontend'] ) ? intval( $itsec_bwps_options['ssl_frontend'] ) : 0;

				update_site_option( 'itsec_ssl', $current_options );
				add_site_option( 'itsec_config_changed', true );

			}

		}

	}

}

new ITSEC_SSL_Setup();