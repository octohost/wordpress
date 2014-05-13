<?php

if ( ! class_exists( 'ITSEC_Four_Oh_Four_Setup' ) ) {

	class ITSEC_Four_Oh_Four_Setup {

		private
			$defaults;

		public function __construct() {

			global $itsec_setup_action;

			$this->defaults = array(
				'enabled'         => false,
				'check_period'    => 5,
				'error_threshold' => 20,
				'white_list'      => array(
					'/favicon.ico',
					'/robots.txt',
					'/apple-touch-icon.png',
					'/apple-touch-icon-precomposed.png',
					'/wp-content/cache',
					'/browserconfig.xml',
					'/crossdomain.xml',
					'/labels.rdf',
					'/trafficbasedsspsitemap.xml',
				),
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

			$options = get_site_option( 'itsec_four_oh_four' );

			if ( $options === false ) {

				add_site_option( 'itsec_four_oh_four', $this->defaults );

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

			delete_site_option( 'itsec_four_oh_four' );

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

				$current_options = get_site_option( 'itsec_four_oh_four' );

				if ( $current_options === false ) {
					$current_options = $this->defaults;
				}

				$current_options['enabled']         = isset( $itsec_bwps_options['id_enabled'] ) && $itsec_bwps_options['id_enabled'] == 1 ? true : false;
				$current_options['check_period']    = isset( $itsec_bwps_options['id_checkinterval'] ) ? intval( $itsec_bwps_options['id_checkinterval'] ) : 5;
				$current_options['error_threshold'] = isset( $itsec_bwps_options['id_threshold'] ) ? intval( $itsec_bwps_options['id_threshold'] ) : 20;

				if ( isset( $itsec_bwps_options['id_whitelist'] ) && ! is_array( $itsec_bwps_options['id_whitelist'] ) && strlen( $itsec_bwps_options['id_whitelist'] ) > 1 ) {

					$current_options['white_list'] .= explode( PHP_EOL, $itsec_bwps_options['id_whitelist'] );

				}

				update_site_option( 'itsec_four_oh_four', $current_options );

			}

		}

	}

}

new ITSEC_Four_Oh_Four_Setup();