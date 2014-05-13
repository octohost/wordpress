<?php

class ITSEC_Backup {

	private
		$core,
		$settings;

	function run( $core ) {

		global $itsec_globals;

		$this->core     = $core;
		$this->settings = get_site_option( 'itsec_backup' );

		add_filter( 'itsec_logger_modules', array( $this, 'register_logger' ) );

		if ( ( ! defined( 'DOING_AJAX' ) || DOING_AJAX === false ) && $this->settings['enabled'] === true && ! class_exists( 'pb_backupbuddy' ) && ( ( $itsec_globals['current_time_gmt'] - $this->settings['interval'] * 24 * 60 * 60 ) ) > $this->settings['last_run'] ) {

			add_action( 'init', array( $this, 'do_backup' ), 10, 0 );

		}

	}

	/**
	 * Public function to get lock and call backup
	 *
	 * @param  boolean $one_time whether this is a one time backup
	 *
	 * @return mixed false on error or nothing
	 */
	public function do_backup( $one_time = false ) {

		global $itsec_files;

		ITSEC_Lib::set_minimum_memory_limit( '128M' );

		if ( $itsec_files->get_file_lock( 'backup' ) ) {

			$this->execute_backup( $one_time );

			$itsec_files->release_file_lock( 'backup' );

			if ( $one_time === true ) {

				switch ( $this->settings['method'] ) {

					case 0:
						$details = __( 'emailed to backup recipients and saved locally.', 'it-l10n-better-wp-security' );
						break;
					case 1:
						$details = __( 'emailed to backup recipients.', 'it-l10n-better-wp-security' );
						break;
					default:
						$details = __( 'saved locally.', 'it-l10n-better-wp-security' );
						break;

				}

				$type    = 'updated';
				$message = __( 'Backup Completed and ' . $details, 'it-l10n-better-wp-security' );

			}

			$success = true;

		} else {

			if ( $one_time === true ) {

				$type    = 'error';
				$message = __( 'Something went wrong with your backup. It looks like another process might already be trying to backup your database. Please try again in a few minutes. If the problem persists please contact support.', 'it-l10n-better-wp-security' );

			}

			$success = false;

		}

		if ( $one_time === true ) {

			if ( is_multisite() ) {

				$error_handler = new WP_Error();

				$error_handler->add( $type, $message );

				$this->core->show_network_admin_notice( $error_handler );

			} else {

				add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

			}

		}

		return $success;

	}

	/**
	 * Executes backup function
	 *
	 * @param bool $one_time whether this is a one-time backup
	 *
	 * @return void
	 */
	private function execute_backup( $one_time = false ) {

		global $wpdb, $itsec_globals, $itsec_logger;

		//get all of the tables
		if ( isset( $this->settings['all_sites'] ) && $this->settings['all_sites'] === true ) {
			$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N ); //retrieve a list of all tables in the DB
		} else {
			$tables = $wpdb->get_results( 'SHOW TABLES LIKE "' . $wpdb->base_prefix . '%"', ARRAY_N ); //retrieve a list of all tables for this WordPress installation
		}

		$return = '';

		//cycle through each table
		foreach ( $tables as $table ) {

			$num_fields = sizeof( $wpdb->get_results( 'DESCRIBE `' . $table[0] . '`;' ) );

			$return .= 'DROP TABLE IF EXISTS `' . $table[0] . '`;';

			$row2 = $wpdb->get_row( 'SHOW CREATE TABLE `' . $table[0] . '`;', ARRAY_N );

			$return .= PHP_EOL . PHP_EOL . $row2[1] . ";" . PHP_EOL . PHP_EOL;

			if ( in_array( substr( $table[0], strlen( $wpdb->prefix ) ), $this->settings['exclude'] ) === false ) {

				$result = $wpdb->get_results( 'SELECT * FROM `' . $table[0] . '`;', ARRAY_N );

				foreach ( $result as $row ) {

					$return .= 'INSERT INTO `' . $table[0] . '` VALUES(';

					for ( $j = 0; $j < $num_fields; $j ++ ) {

						$row[$j] = addslashes( $row[$j] );
						$row[$j] = preg_replace( '#' . PHP_EOL . '#', "\n", $row[$j] );

						if ( isset( $row[$j] ) ) {
							$return .= '"' . $row[$j] . '"';
						} else {
							$return .= '""';
						}

						if ( $j < ( $num_fields - 1 ) ) {
							$return .= ',';
						}

					}

					$return .= ");" . PHP_EOL;

				}

			}

			$return .= PHP_EOL . PHP_EOL;

		}

		$return .= PHP_EOL . PHP_EOL;

		$current_time = current_time( 'timestamp' );

		//save file
		$file = 'backup-' . substr( sanitize_title( get_bloginfo( 'name' ) ), 0, 20 ) . '-' . $current_time . '-' . ITSEC_Lib::get_random( mt_rand( 5, 10 ) );
		if ( ! is_dir( $itsec_globals['ithemes_backup_dir'] ) ) {
			@mkdir( trailingslashit( $itsec_globals['ithemes_dir'] ) . 'backups' );
		}
		$handle = @fopen( $itsec_globals['ithemes_backup_dir'] . '/' . $file . '.sql', 'w+' );
		@fwrite( $handle, $return );
		@fclose( $handle );

		//zip the file
		if ( $this->settings['zip'] === true && class_exists( 'ZipArchive' ) ) {

			$zip = new ZipArchive();
			$zip->open( $itsec_globals['ithemes_backup_dir'] . '/' . $file . '.zip', ZipArchive::CREATE );
			$zip->addFile( $itsec_globals['ithemes_backup_dir'] . '/' . $file . '.sql', $file . '.sql' );
			$zip->close();

			//delete .sql and keep zip
			@unlink( $itsec_globals['ithemes_backup_dir'] . '/' . $file . '.sql' );

			$fileext = '.zip';

		} else {

			$fileext = '.sql';

		}

		if ( $this->settings['method'] !== 2 || $one_time === true ) {

			$option = get_site_option( 'itsec_global' );

			$attachment = array( $itsec_globals['ithemes_backup_dir'] . '/' . $file . $fileext );
			$body       = __( 'Attached is the backup file for the database powering', 'it-l10n-better-wp-security' ) . ' ' . get_option( 'siteurl' ) . __( ' taken', 'it-l10n-better-wp-security' ) . ' ' . date( 'l, F jS, Y \a\\t g:i a', $itsec_globals['current_time'] );

			//Setup the remainder of the email
			$recipients = $option['backup_email'];
			$subject    = __( 'Site Database Backup', 'it-l10n-better-wp-security' ) . ' ' . date( 'l, F jS, Y \a\\t g:i a', $itsec_globals['current_time'] );
			$subject    = apply_filters( 'itsec_backup_email_subject', $subject );
			$headers    = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";

			//Use HTML Content type
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

			//Send emails to all recipients
			foreach ( $recipients as $recipient ) {

				if ( is_email( trim( $recipient ) ) ) {
					wp_mail( trim( $recipient ), $subject, $body, $headers, $attachment );
				}

			}

			//Remove HTML Content type
			remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

		}

		if ( $this->settings['method'] === 1 ) {

			@unlink( $itsec_globals['ithemes_backup_dir'] . '/' . $file . $fileext );

		} else {

			$retain = isset( $this->settings['retain'] ) ?  absint( $this->settings['retain'] ) : 0;

			//delete extra files
			if ( $retain > 0 ) {

				$files = scandir( $itsec_globals['ithemes_backup_dir'], 1 );

				$count = 0;

				if ( is_array( $files ) && count( $files ) > 0 ) {

					foreach ( $files as $file ) {

						if ( strstr( $file, 'backup' ) ) {

							if ( $count >= $retain ) {
								@unlink( trailingslashit( $itsec_globals['ithemes_backup_dir'] ) . $file );
							}

							$count ++;
						}

					}

				}

			}

		}

		if ( $one_time === false ) {

			$this->settings['last_run'] = $itsec_globals['current_time_gmt'];

			update_site_option( 'itsec_backup', $this->settings );

		}

		switch ( $this->settings['method'] ) {

			case 0:
				$status = array(
					'status'  => __( 'Success', 'it-l10n-better-wp-security' ),
					'details' => __( 'emailed to backup recipients and saved locally', 'it-l10n-better-wp-security' ),
				);

				break;
			case 1:
				$status = array(
					'status'  => __( 'Success', 'it-l10n-better-wp-security' ),
					'details' => __( 'emailed to backup recipients', 'it-l10n-better-wp-security' ),
				);
				break;
			default:
				$status = array(
					'status'  => __( 'Success', 'it-l10n-better-wp-security' ),
					'details' => __( 'saved locally', 'it-l10n-better-wp-security' ),
				);
				break;

		}

		$itsec_logger->log_event( 'backup', 3, array( $status ) );

	}

	/**
	 * Register backups for logger
	 *
	 * @param  array $logger_modules array of logger modules
	 *
	 * @return array                   array of logger modules
	 */
	public function register_logger( $logger_modules ) {

		$logger_modules['backup'] = array(
			'type'     => 'backup',
			'function' => __( 'Database Backup Executed', 'it-l10n-better-wp-security' ),
		);

		return $logger_modules;

	}

	/**
	 * Set HTML content type for email
	 *
	 * @return string html content type
	 */
	public function set_html_content_type() {

		return 'text/html';

	}

}