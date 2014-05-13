<?php

/**
 * Handles the writing, maintenance and display of log files
 *
 * @package iThemes-Security
 * @since   4.0
 */
final class ITSEC_Logger {

	private
		$log_file,
		$logger_modules,
		$metaboxes,
		$module_path;

	function __construct() {

		global $itsec_globals;

		//make sure the log file info is there or generate it. This should only affect beta users.
		if ( ! isset( $itsec_globals['settings']['log_info'] ) ) {

			$itsec_globals['settings']['log_info'] = substr( sanitize_title( get_bloginfo( 'name' ) ), 0, 20 ) . '-' . ITSEC_Lib::get_random( mt_rand( 0, 10 ) );

			update_site_option( 'itsec_global', $itsec_globals['settings'] );

		}

		//Make sure the logs directory was created
		if ( ! is_dir( $itsec_globals['ithemes_log_dir'] ) ) {
			@mkdir( trailingslashit( $itsec_globals['ithemes_dir'] ) . 'logs' );
		}

		//don't create a log file if we don't need it.
		if ( isset( $itsec_globals['settings']['log_type'] ) && $itsec_globals['settings']['log_type'] !== 0 ) {

			$this->log_file = $itsec_globals['ithemes_log_dir'] . '/event-log-' . $itsec_globals['settings']['log_info'] . '.log';
			$this->start_log(); //create a log file if we don't have one

		}

		$this->logger_modules = array(); //array to hold information on modules using this feature
		$this->metaboxes      = array(); //array to hold metabox information
		$this->module_path    = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'plugins_loaded', array( $this, 'register_modules' ) );

		//Run database cleanup daily with cron
		if ( ! wp_next_scheduled( 'itsec_purge_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'itsec_purge_logs' );
		}

		add_action( 'itsec_purge_logs', array( $this, 'purge_logs' ) );

		if ( is_admin() ) {

			require( $itsec_globals['plugin_dir'] . 'core/lib/class-itsec-wp-list-table.php' ); //used for generating log tables

			add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) ); //add log meta boxes

		}

		if ( isset( $_POST['itsec_clear_logs'] ) && $_POST['itsec_clear_logs'] === 'clear_logs' ) {

			global $itsec_clear_all_logs;

			$itsec_clear_all_logs = true;

			add_action( 'plugins_loaded', array( $this, 'purge_logs' ) );

		}

	}

	/**
	 * Adds a log meta box only if logging is active. Overrides WP Core add_meta_box
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function add_admin_meta_boxes() {

		global $itsec_globals;

		add_meta_box(
			'itsec_log_header',
			__( 'Security Logs', 'it-l10n-better-wp-security' ),
			array( $this, 'metabox_logs_header' ),
			'security_page_toplevel_page_itsec_logs',
			'top',
			'core'
		);

		if ( isset( $itsec_globals['settings']['log_type'] ) && ( $itsec_globals['settings']['log_type'] === 0 || $itsec_globals['settings']['log_type'] === 2 ) ) {

			if ( sizeof( $this->metaboxes ) > 0 ) {

				foreach ( $this->metaboxes as $metabox ) {

					add_meta_box(
						'log-' . sanitize_text_field( $metabox['module'] ),
						$metabox['title'],
						$metabox['callback'],
						'security_page_toplevel_page_itsec_logs',
						'normal',
						'core'
					);

				}

			}

		}

		add_meta_box(
			'itsec_log_all',
			__( 'All Logged Items', 'it-l10n-better-wp-security' ),
			array( $this, 'metabox_all_logs' ),
			'security_page_toplevel_page_itsec_logs',
			'advanced',
			'core'
		);

	}

	/**
	 * Gets events from the logs for a specified module
	 *
	 * @param string $module module or type of events to fetch
	 * @param array  $params array of extra query parameters
	 *
	 * @return bool|mixed false on error, null if no events or array of events
	 */
	public function get_events( $module, $params = array() ) {

		global $wpdb;

		if ( isset( $module ) !== true || strlen( $module ) < 1 ) {
			return false;
		}

		if ( sizeof( $params ) > 0 || $module != 'all' ) {
			$where = " WHERE ";
		} else {
			$where = '';
		}

		$param_search = '';

		if ( $module == 'all' ) {

			$module_sql = '';
			$and        = '';

		} else {

			$module_sql = "`log_type` = '" . esc_sql( $module ) . "'";
			$and        = ' AND ';

		}

		if ( sizeof( $params ) > 0 ) {

			foreach ( $params as $field => $value ) {

				if ( gettype( $value ) != 'integer' ) {
					$param_search .= $and . "`" . esc_sql( $field ) . "`='" . esc_sql( $value ) . "'";
				} else {
					$param_search .= $and . "`" . esc_sql( $field ) . "`=" . esc_sql( $value ) . "";
				}

			}

		}

		$items = $wpdb->get_results( "SELECT * FROM `" . $wpdb->base_prefix . "itsec_log`" . $where . $module_sql . $param_search . ";", ARRAY_A );

		return $items;

	}

	/**
	 * Logs events sent by other modules or systems
	 *
	 * @param string $module   the module requesting the log entry
	 * @param int    $priority the priority of the log entry (1-10)
	 * @param array  $data     extra data to log (non-indexed data would be good here)
	 * @param string $host     the remote host triggering the event
	 * @param string $username the username triggering the event
	 * @param string $user     the user id triggering the event
	 * @param string $url      the url triggering the event
	 * @param string $referrer the referrer to the url (if applicable)
	 *
	 * @return void
	 */
	public function log_event( $module, $priority = 5, $data = array(), $host = '', $username = '', $user = '', $url = '', $referrer = '' ) {

		global $wpdb, $itsec_globals;

		if ( isset( $this->logger_modules[$module] ) ) {

			$options = $this->logger_modules[$module];

			$file_data = $this->sanitize_array( $data, true );

			$sanitized_data = $this->sanitize_array( $data ); //array of sanitized data

			if ( $itsec_globals['settings']['log_type'] === 0 || $itsec_globals['settings']['log_type'] == 2 ) {

				$wpdb->insert(
				     $wpdb->base_prefix . 'itsec_log',
				     array(
					     'log_type'     => $options['type'],
					     'log_priority' => intval( $priority ),
					     'log_function' => $options['function'],
					     'log_date'     => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
					     'log_date_gmt' => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
					     'log_host'     => sanitize_text_field( $host ),
					     'log_username' => sanitize_text_field( $username ),
					     'log_user'     => intval( $user ),
					     'log_url'      => esc_sql( $url ),
					     'log_referrer' => esc_sql( $referrer ),
					     'log_data'     => serialize( $sanitized_data ),
				     )
				);

			}

			if ( $itsec_globals['settings']['log_type'] === 1 || $itsec_globals['settings']['log_type'] == 2 ) {

				$message =
					$options['type'] . ',' .
					intval( $priority ) . ',' .
					$options['function'] . ',' .
					date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ) . ',' .
					date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ) . ',' .
					sanitize_text_field( $host ) . ',' .
					sanitize_text_field( $username ) . ',' .
					( intval( $user ) === 0 ? '' : intval( $user ) ) . ',' .
					esc_sql( $url ) . ',' .
					esc_sql( $referrer ) . ',' .
					$file_data;

				error_log( $message . PHP_EOL, 3, $this->log_file );

			}

		}

	}

	/**
	 * Displays into box for logs page
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_logs_header() {

		global $itsec_globals;

		printf(
			'<p>%s %s. %s</p>',
			__( 'Below are various logs of information collected by', 'it-l10n-better-wp-security' ),
			$itsec_globals['plugin_name'],
			__( 'This information can help you get a picture of what is happening with your site and the level of success you have achieved in your security efforts.', 'it-l10n-better-wp-security' )
		);

	}

	/**
	 * Displays into box for logs page
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_all_logs() {

		global $wpdb;

		require( dirname( __FILE__ ) . '/class-itsec-logger-all-logs.php' );

		echo __( 'Below is the log of all the log items in your WordPress Database. To adjust logging options visit the global settings page.', 'it-l10n-better-wp-security' );

		$log_display = new ITSEC_Logger_All_Logs();
		$log_display->prepare_items();
		$log_display->display();

		$log_count = $wpdb->get_var( "SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "itsec_log`;" );

		?>
		<form method="post" action="">
			<?php wp_nonce_field( 'itsec_clear_logs', 'wp_nonce' ); ?>
			<input type="hidden" name="itsec_clear_logs" value="clear_logs"/>
			<table class="form-table">
				<tr valign="top">
					<th scope="row" class="settinglabel">
						<?php _e( 'Log Summary', 'it-l10n-better-wp-security' ); ?>
					</th>
					<td class="settingfield">

						<p><?php _e( 'Your database contains', 'it-l10n-better-wp-security' ); ?>
							<strong><?php echo $log_count; ?></strong> <?php _e( 'log entries.', 'it-l10n-better-wp-security' ); ?>
						</p>

						<p><?php _e( 'Use the button below to purge the log table in your database. Please note this will purge all log entries in the database including 404s.', 'it-l10n-better-wp-security' ); ?></p>

						<p class="submit"><input type="submit" class="button-primary"
						                         value="<?php _e( 'Clear Logs', 'it-l10n-better-wp-security' ); ?>"/></p>
					</td>
				</tr>
			</table>
		</form>
	<?php

	}

	/**
	 * A better print array function to display array data in the logs
	 *
	 * @since 4.2
	 *
	 * @param array $array_items array to print or return
	 * @param bool  $return      true to return the data false to echo it
	 */
	public function print_array( $array_items, $return = true ) {

		$items = '';

		//make sure we're working with an array
		if ( ! is_array( $array_items ) ) {
			return false;
		}

		if ( sizeof( $array_items ) > 0 ) {

			$items .= '<ul>';

			foreach ( $array_items as $key => $item ) {

				if ( is_array( $item ) ) {

					$items .= '<li>';

						if ( ! is_numeric( $key ) ) {
							$items .= '<h3>' . $key . '</h3>';
						}
	
						$items .= $this->print_array( $item, true ) . PHP_EOL;

					$items .= '</li>';

				} else {

					if ( strlen( trim( $item ) ) > 0 ) {
						$items .= '<li><h3>' . $key . ' = ' . $item . '</h3></li>' . PHP_EOL;
					}

				}

			}

			$items .= '</ul>';

		}

		return $items;

	}

	/**
	 * Purges database logs and rotates file logs (when needed)
	 *
	 * @return void
	 */
	public function purge_logs() {

		global $wpdb, $itsec_globals, $itsec_clear_all_logs;

		if ( isset( $itsec_clear_all_logs ) && $itsec_clear_all_logs === true ) {

			if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'itsec_clear_logs' ) ) {
				return;
			}

			$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "itsec_log`;" );

		} else {

			//Clean up the database log first
			if ( $itsec_globals['settings']['log_type'] === 0 || $itsec_globals['settings']['log_type'] == 2 ) {

				$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "itsec_log` WHERE `log_date_gmt` < '" . date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] - ( $itsec_globals['settings']['log_rotation'] * 24 * 60 * 60 ) ) . "';" );

			} else {

				$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "itsec_log`;" );

			}

			if ( ( @file_exists( $this->log_file ) && @filesize( $this->log_file ) >= 10485760 ) ) {
				$this->rotate_log();
			}

		}

	}

	/**
	 * Register modules that will use the logger service
	 *
	 * @return void
	 */
	public function register_modules() {

		$this->logger_modules = apply_filters( 'itsec_logger_modules', $this->logger_modules );
		$this->metaboxes      = apply_filters( 'itsec_metaboxes', $this->metaboxes );

	}

	/**
	 * Rotates the event-log.log file when called
	 *
	 * Adapted from http://www.phpclasses.org/browse/file/49471.html
	 *
	 * @return void
	 */
	private function rotate_log() {

		// rotate
		$path_info      = pathinfo( $this->log_file );
		$base_directory = $path_info['dirname'];
		$base_name      = $path_info['basename'];
		$num_map        = array();

		foreach ( new DirectoryIterator( $base_directory ) as $fInfo ) {

			if ( $fInfo->isDot() || ! $fInfo->isFile() ) {
				continue;
			}

			if ( preg_match( '/^' . $base_name . '\.?([0-9]*)$/', $fInfo->getFilename(), $matches ) ) {

				$num      = $matches[1];
				$old_file = $fInfo->getFilename();

				if ( $num == '' ) {
					$num = - 1;
				}

				$num_map[$num] = $old_file;

			}

		}

		krsort( $num_map );

		foreach ( $num_map as $num => $old_file ) {

			$new_file = $num + 1;
			@rename( $base_directory . DIRECTORY_SEPARATOR . $old_file, $this->log_file . '.' . $new_file );

		}

		$this->start_log();

	}

	/**
	 * Sanitizes strings in a given array recursively
	 *
	 * @param  array $array     array to sanitize
	 * @param  bool  $to_string true if output should be string or false for array output
	 *
	 * @return mixed             sanitized array or string
	 */
	private function sanitize_array( $array, $to_string = false ) {

		$sanitized_array = array();
		$string          = '';

		//Loop to sanitize each piece of data
		foreach ( $array as $key => $value ) {

			if ( is_array( $value ) ) {

				if ( $to_string === false ) {
					$sanitized_array[esc_sql( $key )] = $this->sanitize_array( $value );
				} else {
					$string .= esc_sql( $key ) . '=' . $this->sanitize_array( $value, true );
				}

			} else {

				$sanitized_array[esc_sql( $key )] = esc_sql( $value );

				$string .= esc_sql( $key ) . '=' . esc_sql( $value );

			}

		}

		if ( $to_string === false ) {
			return $sanitized_array;
		} else {
			return $string;
		}

	}

	/**
	 * Creates a new log file and adds header information (if needed)
	 *
	 * @return void
	 */
	private function start_log() {

		if ( file_exists( $this->log_file ) !== true ) { //only if current log file doesn't exist

			$header = 'log_type,log_priority,log_function,log_date,log_date_gmt,log_host,log_username,log_user,log_url,log_referrer,log_data' . PHP_EOL;

			@error_log( $header, 3, $this->log_file );

		}

	}

}