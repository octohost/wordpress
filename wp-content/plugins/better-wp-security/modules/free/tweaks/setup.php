<?php

if ( ! class_exists( 'ITSEC_Tweaks_Setup' ) ) {

	class ITSEC_Tweaks_Setup {

		private
			$defaults;

		public function __construct() {

			global $itsec_setup_action;

			$this->defaults = array(
				'protect_files'               => false,
				'directory_browsing'          => false,
				'request_methods'             => false,
				'suspicious_query_strings'    => false,
				'non_english_characters'      => false,
				'long_url_strings'            => false,
				'write_permissions'           => false,
				'generator_tag'               => false,
				'wlwmanifest_header'          => false,
				'edituri_header'              => false,
				'theme_updates'               => false,
				'plugin_updates'              => false,
				'core_updates'                => false,
				'comment_spam'                => false,
				'random_version'              => false,
				'file_editor'                 => false,
				'disable_xmlrpc'              => 0,
				'uploads_php'                 => false,
				'login_errors'                => false,
				'force_unique_nicename'       => false,
				'disable_unused_author_pages' => false,
				'safe_jquery'                 => false,
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

			$options  = get_site_option( 'itsec_tweaks' );
			$initials = get_site_option( 'itsec_initials' );

			if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT === true ) {

				$initials['file_editor']       = true;
				$this->defaults['file_editor'] = true;

			} else {

				$initials['file_editor'] = false;

			}

			update_site_option( 'itsec_initials', $initials );

			if ( $options === false ) {

				add_site_option( 'itsec_tweaks', $this->defaults );

			}

			add_site_option( 'itsec_rewrites_changed', true );
			add_site_option( 'itsec_config_changed', true );

		}

		/**
		 * Execute module deactivation
		 *
		 * @return void
		 */
		public function execute_deactivate() {

			global $itsec_files;

			delete_site_transient( 'itsec_random_version' );

			$config_rules[] = itsec_tweaks_Admin::build_wpconfig_rules( null, true );
			$itsec_files->set_wpconfig( $config_rules );

			//Reset recommended file permissions
			@chmod( ITSEC_Lib::get_htaccess(), 0644 );
			@chmod( ITSEC_Lib::get_config(), 0644 );

		}

		/**
		 * Execute module uninstall
		 *
		 * @return void
		 */
		public function execute_uninstall() {

			$this->execute_deactivate();

			delete_site_option( 'itsec_tweaks' );

		}

		/**
		 * Execute module upgrade
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function execute_upgrade() {

			global $itsec_old_version;

			if ( $itsec_old_version < 4000 ) {

				global $itsec_bwps_options;

				ITSEC_Lib::create_database_tables();

				$current_options = get_site_option( 'itsec_tweaks' );

				if ( $current_options === false ) {
					$current_options = $this->defaults;
				}

				$current_options['protect_files']            = isset( $itsec_bwps_options['st_ht_files'] ) && $itsec_bwps_options['st_ht_files'] == 1 ? true : false;
				$current_options['directory_browsing']       = isset( $itsec_bwps_options['st_ht_browsing'] ) && $itsec_bwps_options['st_ht_browsing'] == 1 ? true : false;
				$current_options['request_methods']          = isset( $itsec_bwps_options['st_ht_request'] ) && $itsec_bwps_options['st_ht_request'] == 1 ? true : false;
				$current_options['suspicious_query_strings'] = isset( $itsec_bwps_options['st_ht_query'] ) && $itsec_bwps_options['st_ht_query'] == 1 ? true : false;
				$current_options['non_english_characters']   = isset( $itsec_bwps_options['st_ht_foreign'] ) && $itsec_bwps_options['st_ht_foreign'] == 1 ? true : false;
				$current_options['long_url_strings']         = isset( $itsec_bwps_options['st_longurl'] ) && $itsec_bwps_options['st_longurl'] == 1 ? true : false;
				$current_options['write_permissions']        = isset( $itsec_bwps_options['st_fileperm'] ) && $itsec_bwps_options['st_fileperm'] == 1 ? true : false;
				$current_options['generator_tag']            = isset( $itsec_bwps_options['st_generator'] ) && $itsec_bwps_options['st_generator'] == 1 ? true : false;
				$current_options['wlwmanifest_header']       = isset( $itsec_bwps_options['st_manifest'] ) && $itsec_bwps_options['st_manifest'] == 1 ? true : false;
				$current_options['edituri_header']           = isset( $itsec_bwps_options['st_edituri'] ) && $itsec_bwps_options['st_edituri'] == 1 ? true : false;
				$current_options['theme_updates']            = isset( $itsec_bwps_options['st_themenot'] ) && $itsec_bwps_options['st_themenot'] == 1 ? true : false;
				$current_options['plugin_updates']           = isset( $itsec_bwps_options['st_pluginnot'] ) && $itsec_bwps_options['st_pluginnot'] == 1 ? true : false;
				$current_options['core_updates']             = isset( $itsec_bwps_options['st_corenot'] ) && $itsec_bwps_options['st_corenot'] == 1 ? true : false;
				$current_options['comment_spam']             = isset( $itsec_bwps_options['st_comment'] ) && $itsec_bwps_options['st_comment'] == 1 ? true : false;
				$current_options['random_version']           = isset( $itsec_bwps_options['st_randomversion'] ) && $itsec_bwps_options['st_randomversion'] == 1 ? true : false;
				$current_options['login_errors']             = isset( $itsec_bwps_options['st_loginerror'] ) && $itsec_bwps_options['st_loginerror'] == 1 ? true : false;

				update_site_option( 'itsec_tweaks', $current_options );
				add_site_option( 'itsec_rewrites_changed', true );
				add_site_option( 'itsec_config_changed', true );

			}

			if ( $itsec_old_version < 4029 ) {
				add_site_option( 'itsec_rewrites_changed', true );
			}

		}

	}

}

new ITSEC_Tweaks_Setup();