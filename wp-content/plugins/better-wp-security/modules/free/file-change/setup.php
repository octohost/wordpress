<?php

if ( ! class_exists( 'ITSEC_File_Change_Setup' ) ) {

	class ITSEC_File_Change_Setup {

		private
			$defaults;

		public function __construct() {

			global $itsec_setup_action;

			$this->defaults = array(
				'enabled'      => false,
				'split'        => false,
				'file_list'    => array(),
				'method'       => true,
				'types'        => array(
					'.jpg',
					'.jpeg',
					'.png',
					'.log',
					'.mo',
					'.po'
				),
				'email'        => true,
				'last_run'     => 0,
				'notify_admin' => true,
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

			$options = get_site_option( 'itsec_file_change' );

			if ( $options === false ) {

				add_site_option( 'itsec_file_change', $this->defaults );

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

			delete_site_option( 'itsec_file_change' );
			delete_site_option( 'itsec_local_file_list' );
			delete_site_option( 'itsec_local_file_list_0' );
			delete_site_option( 'itsec_local_file_list_1' );
			delete_site_option( 'itsec_local_file_list_2' );
			delete_site_option( 'itsec_local_file_list_3' );
			delete_site_option( 'itsec_local_file_list_4' );
			delete_site_option( 'itsec_local_file_list_5' );
			delete_site_option( 'itsec_local_file_list_6' );
			delete_site_option( 'itsec_file_change_warning' );

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

				$current_options = get_site_option( 'itsec_file_change' );

				if ( $current_options === false ) {
					$current_options = $this->defaults;
				}

				$current_options['enabled']      = isset( $itsec_bwps_options['id_fileenabled'] ) && $itsec_bwps_options['id_fileenabled'] == 1 ? true : false;
				$current_options['email']        = isset( $itsec_bwps_options['id_fileemailnotify'] ) && $itsec_bwps_options['id_fileemailnotify'] == 0 ? false : true;
				$current_options['notify_admin'] = isset( $itsec_bwps_options['id_filedisplayerror'] ) && $itsec_bwps_options['id_filedisplayerror'] == 0 ? false : true;
				$current_options['method']       = isset( $itsec_bwps_options['id_fileincex'] ) && $itsec_bwps_options['id_fileincex'] == 0 ? false : true;

				if ( isset( $itsec_bwps_options['id_specialfile'] ) && ! is_array( $itsec_bwps_options['id_specialfile'] ) && strlen( $itsec_bwps_options['id_specialfile'] ) > 1 ) {

					$current_options['file_list'] .= explode( PHP_EOL, $itsec_bwps_options['id_specialfile'] );

				}

				update_site_option( 'itsec_file_change', $current_options );

			}

			if ( $itsec_old_version < 4028 ) {

				if ( ! is_multisite() ) {

					$options = array(
						'itsec_local_file_list',
						'itsec_local_file_list_0',
						'itsec_local_file_list_1',
						'itsec_local_file_list_2',
						'itsec_local_file_list_3',
						'itsec_local_file_list_4',
						'itsec_local_file_list_5',
						'itsec_local_file_list_6',
					);

					foreach ( $options as $option ) {

						$list = get_site_option( $option );

						if ( $list !== false ) {

							delete_site_option( $option );
							add_option( $option, $list, '', 'no' );

						}

					}

				}

			}

		}

	}

}

new ITSEC_File_Change_Setup();