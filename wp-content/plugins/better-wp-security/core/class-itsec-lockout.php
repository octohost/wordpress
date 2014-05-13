<?php

/**
 * Handles lockouts for modules and core
 *
 * @package iThemes-Security
 * @since   4.0
 */
final class ITSEC_Lockout {

	private
		$lockout_modules;

	function __construct() {

		$this->lockout_modules = array(); //array to hold information on modules using this feature

		//Run database cleanup daily with cron
		if ( ! wp_next_scheduled( 'itsec_purge_lockouts' ) ) {
			wp_schedule_event( time(), 'daily', 'itsec_purge_lockouts' );
		}

		add_action( 'itsec_purge_lockouts', array( $this, 'purge_lockouts' ) );

		//Check for host lockouts
		add_action( 'init', array( $this, 'check_lockout' ) );

		//Register all plugin modules
		add_action( 'plugins_loaded', array( $this, 'register_modules' ) );

		//Set an error message on improper logout
		add_action( 'login_head', array( $this, 'set_lockout_error' ) );

		//Add the metabox
		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) );

		//Process clear lockout form
		add_action( 'itsec_admin_init', array( $this, 'release_lockout' ) );

		//Register Logger
		add_filter( 'itsec_logger_modules', array( $this, 'register_logger' ) );

		//Register Sync
		add_filter( 'itsec_sync_modules', array( $this, 'register_sync' ) );

	}

	/**
	 * Add meta boxes to primary options pages.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	function add_admin_meta_boxes() {

		add_meta_box(
			'itsec_lockouts',
			__( 'Active Lockouts', 'it-l10n-better-wp-security' ),
			array( $this, 'lockout_metabox' ),
			'toplevel_page_itsec',
			'bottom',
			'core'
		);

	}

	/**
	 * Checks if the host or user is locked out and executes lockout
	 *
	 * @since 4.0
	 *
	 * @param mixed $user     WordPress user object or false
	 * @param mixed $username the username to check
	 *
	 * @return void
	 */
	public function check_lockout( $user = false, $username = false ) {

		global $wpdb, $itsec_globals;

		$host           = ITSEC_Lib::get_ip();
		$username       = sanitize_text_field( trim( $username ) );
		$username_check = false;
		$user_check     = false;
		$host_check     = false;

		if ( $user !== false && $user !== '' && $user !== NULL ) {

			$user    = get_userdata( intval( $user ) );
			$user_id = $user->ID;

		} else {

			$user    = wp_get_current_user();
			$user_id = $user->ID;

			if ( $username !== false && $username != '' ) {
				$username_check = $wpdb->get_var( "SELECT `lockout_username` FROM `" . $wpdb->base_prefix . "itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > '" . date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ) . "' AND `lockout_username`='" . $username . "';" );
			}

			$host_check = $wpdb->get_var( "SELECT `lockout_host` FROM `" . $wpdb->base_prefix . "itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > '" . date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ) . "' AND `lockout_host`='" . $host . "';" );

		}

		if ( $user_id !== 0 && $user_id !== NULL ) {

			$user_check = $wpdb->get_var( "SELECT `lockout_user` FROM `" . $wpdb->base_prefix . "itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > '" . date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ) . "' AND `lockout_user`=" . intval( $user_id ) . ";" );

		}

		if ( $host_check !== NULL && $host_check !== false ) {
			$this->execute_lock();
		} elseif ( ( $user_check !== false && $user_check !== NULL ) || ( $username_check !== false && $username_check !== NULL ) ) {
			$this->execute_lock( true );
		}

	}

	/**
	 * Executes lockout and logging for modules
	 *
	 * @since 4.0
	 *
	 * @param string $module string name of the calling module
	 * @param string $user   username of user
	 *
	 * @return void
	 */
	public function do_lockout( $module, $user = NULL ) {

		global $wpdb, $itsec_globals;

		$lock_host     = NULL;
		$lock_user     = NULL;
		$lock_username = NULL;
		$options       = $this->lockout_modules[$module];

		$host = ITSEC_Lib::get_ip();

		if ( isset( $options['host'] ) && $options['host'] > 0 ) {

			$wpdb->insert(
			     $wpdb->base_prefix . 'itsec_temp',
			     array(
				     'temp_type'     => $options['type'],
				     'temp_date'     => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
				     'temp_date_gmt' => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
				     'temp_host'     => $host,
			     )
			);

			$host_count = $wpdb->get_var(
			                   $wpdb->prepare(
			                        "SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "itsec_temp` WHERE `temp_date_gmt` > '%s' AND `temp_host`='%s';",
			                        date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] - ( $options['period'] * 60 ) ),
			                        $host
			                   )
			);

			if ( $host_count >= $options['host'] ) {

				$lock_host = $host;

			}

		}

		if ( $user !== NULL && isset( $options['user'] ) && $options['user'] > 0 ) {

			$user_id = username_exists( sanitize_text_field( $user ) );

			if ( $user_id !== NULL ) {

				$wpdb->insert(
				     $wpdb->base_prefix . 'itsec_temp',
				     array(
					     'temp_type'     => $options['type'],
					     'temp_date'     => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
					     'temp_date_gmt' => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
					     'temp_user'     => intval( $user_id ),
					     'temp_username' => sanitize_text_field( $user ),
				     )
				);

				$user_count = $wpdb->get_var(
				                   $wpdb->prepare(
				                        "SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "itsec_temp` WHERE `temp_date_gmt` > '%s' AND `temp_username`='%s' OR `temp_user`=%s;",
				                        date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] - ( $options['period'] * 60 ) ),
				                        sanitize_text_field( $user ),
				                        intval( $user_id )
				                   )
				);

				if ( $user_count >= $options['user'] ) {

					$lock_user = $user_id;

				}

			} else {

				$user = sanitize_text_field( $user );

				$wpdb->insert(
				     $wpdb->base_prefix . 'itsec_temp',
				     array(
					     'temp_type'     => $options['type'],
					     'temp_date'     => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
					     'temp_date_gmt' => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
					     'temp_username' => $user,
				     )
				);

				$user_count = $wpdb->get_var(
				                   $wpdb->prepare(
				                        "SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "itsec_temp` WHERE `temp_date_gmt` > '%s' AND `temp_username`='%s';",
				                        date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] - ( $options['period'] * 60 ) ),
				                        $user
				                   )
				);

				if ( $user_count >= $options['user'] ) {

					$lock_username = $user;

				}

			}

		}

		if ( ! $this->is_ip_whitelisted( $host ) && ( $lock_host !== NULL || $lock_user !== NULL || $lock_username !== NULL ) ) {

			$this->lockout( $options['type'], $options['reason'], $lock_host, $lock_user, $lock_username );

		} elseif ( $lock_host !== NULL || $lock_user !== NULL ) {

			global $itsec_logger;

			$itsec_logger->log_event( __( 'lockout', 'it-l10n-better-wp-security' ), 10, array( __( 'A whitelisted host has triggered a lockout condition but was not locked out.', 'it-l10n-better-wp-security' ) ), sanitize_text_field( $host ) );

		}

	}

	/**
	 * Executes lockout (locks user out)
	 *
	 * @param boolean $user if we're locking out a user or not
	 *
	 * @return void
	 */
	private function execute_lock( $user = false ) {

		global $itsec_globals;

		wp_logout();
		@header( 'HTTP/1.0 403 Forbidden' );
		@header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
		@header( 'Expires: Thu, 22 Jun 1978 00:28:00 GMT' );
		header( 'Pragma: no-cache' );

		if ( $user === false ) { //lockout the host entirely

			die( $itsec_globals['settings']['lockout_message'] );

		} else { //just lockout the user

			die( $itsec_globals['settings']['user_lockout_message'] );

		}

	}

	/**
	 * Provides a description of lockout configuration for use in module settings.
	 *
	 * @since 4.0
	 *
	 * @return string the description of settings.
	 */
	public function get_lockout_description() {

		global $itsec_globals;

		$settings = $itsec_globals['settings'];

		$description = sprintf(
			'<h4>%s</h4><p>%s <a href="#global_options">%s</a>.<br /> %s</p><ul><li><strong>%s:</strong> %s</li><li><strong>%s:</strong> %s</li><li><strong>%s:</strong> %s</li><li><strong>%s:</strong> %s</li><li><strong>%s:</strong> %s</li><li><strong>%s:</strong> %s</li></ul>',
			__( 'About Lockouts', 'it-l10n-better-wp-security' ),
			__( 'Your lockout settings can be configured in', 'it-l10n-better-wp-security' ),
			__( 'Global Settings', 'it-l10n-better-wp-security' ),
			__( 'Your current settings are configured as follows:', 'it-l10n-better-wp-security' ),
			__( 'Permanently ban', 'it-l10n-better-wp-security' ),
			( $settings['blacklist'] === true ? __( 'yes', 'it-l10n-better-wp-security' ) : __( 'no', 'it-l10n-better-wp-security' ) ),
			__( 'Number of lockouts before permanent ban', 'it-l10n-better-wp-security' ),
			$settings['blacklist_count'],
			__( 'How long lockouts will be remembered for ban', 'it-l10n-better-wp-security' ),
			$settings['blacklist_period'],
			__( 'Host lockout message', 'it-l10n-better-wp-security' ),
			$settings['lockout_message'],
			__( 'User lockout message', 'it-l10n-better-wp-security' ),
			$settings['user_lockout_message'],
			__( 'Is this computer white-listed', 'it-l10n-better-wp-security' ),
			( $this->is_ip_whitelisted( ITSEC_Lib::get_ip() === true ) ? __( 'yes', 'it-l10n-better-wp-security' ) : __( 'no', 'it-l10n-better-wp-security' ) )
		);

		return $description;

	}

	/**
	 * Shows all lockouts currently in the database.
	 *
	 * @since 4.0
	 *
	 * @param string $type    'all', 'host', or 'user'
	 * @param bool   $current true for all lockouts, false for current lockouts
	 *
	 * @return array all lockouts in the system
	 */
	public function get_lockouts( $type = 'all', $current = false ) {

		global $wpdb, $itsec_globals;

		if ( $type !== 'all' || $current === true ) {
			$where = " WHERE ";
		} else {
			$where = '';
		}

		switch ( $type ) {

			case 'host':
				$type_statement = "`lockout_host` IS NOT NULL && `lockout_host` != ''";
				break;
			case 'user':
				$type_statement = "`lockout_user` != 0";
				break;
			case 'username':
				$type_statement = "`lockout_username` IS NOT NULL && `lockout_username` != ''";
				break;
			default:
				$type_statement = '';
				break;

		}

		if ( $current === true ) {

			if ( $type_statement !== '' ) {
				$and = ' AND ';
			} else {
				$and = '';
			}

			$active = $and . " `lockout_active`=1 AND `lockout_expire_gmt` > '" . date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ) . "'";

		} else {

			$active = '';

		}

		return $wpdb->get_results( "SELECT * FROM `" . $wpdb->base_prefix . "itsec_lockouts`" . $where . $type_statement . $active . ";", ARRAY_A );

	}

	/**
	 * Determines whether a given IP address is whitelisted.
	 *
	 * @since  4.0
	 *
	 * @access private
	 *
	 * @param  string $ip_to_check ip to check
	 *
	 * @return boolean               true if whitelisted or false
	 */
	private function is_ip_whitelisted( $ip_to_check, $current = false ) {

		global $itsec_globals;

		$white_ips = $itsec_globals['settings']['lockout_white_list'];

		if ( ! is_array( $white_ips ) ) {
			$white_ips = explode( PHP_EOL, $white_ips );
		}

		if ( $current === true ) {
			$white_ips[] = ITSEC_Lib::get_ip(); //add current user ip to whitelist to check automatically
		}

		if ( is_array( $white_ips ) && sizeof( $white_ips > 0 ) ) {

			foreach ( $white_ips as $white_ip ) {

				$converted_white_ip = ITSEC_Lib::ip_wild_to_mask( $white_ip );

				$check_range = ITSEC_Lib::cidr_to_range( $converted_white_ip );
				$ip_range    = ITSEC_Lib::cidr_to_range( $ip_to_check );

				if ( sizeof( $check_range ) === 2 ) { //range to check

					$check_min = ip2long( $check_range[0] );
					$check_max = ip2long( $check_range[1] );

					if ( sizeof( $ip_range ) === 2 ) {

						$ip_min = ip2long( $ip_range[0] );
						$ip_max = ip2long( $ip_range[1] );

						if ( ( $check_min < $ip_min && $ip_min < $check_max ) || ( $check_min < $ip_max && $ip_max < $check_max ) ) {
							return true;
						}

					} else {

						$ip = ip2long( $ip_range[0] );

						if ( $check_min < $ip && $ip < $check_max ) {
							return true;
						}

					}

				} else { //single ip to check

					$check = ip2long( $check_range[0] );

					if ( sizeof( $ip_range ) === 2 ) {

						$ip_min = ip2long( $ip_range[0] );
						$ip_max = ip2long( $ip_range[1] );

						if ( $ip_min < $check && $check < $ip_max ) {
							return true;
						}

					} else {

						$ip = ip2long( $ip_range[0] );

						if ( $check == $ip ) {
							return true;
						}

					}

				}

			}

		}

		return false;

	}

	/**
	 * Locks out given user or host
	 *
	 * @since 4.0
	 *
	 * @param  string $type     The type of lockout (for user reference)
	 * @param  string $reason   Reason for lockout, for notifications
	 * @param  string $host     Host to lock out
	 * @param  int    $user     user id to lockout
	 * @param string  $username username to lockout
	 *
	 * @return void
	 */
	private function lockout( $type, $reason, $host = NULL, $user = NULL, $username = NULL ) {

		global $wpdb, $itsec_logger, $itsec_globals, $itsec_files;

		$host_expiration = NULL;
		$user_expiration = NULL;
		$username        = sanitize_text_field( trim( $username ) );

		if ( $itsec_files->get_file_lock( 'lockout_' . $host . $user . $username ) ) {

			//Do we have a good host to lock out or not
			if ( $host != NULL && $this->is_ip_whitelisted( sanitize_text_field( $host ) ) === false && ITSEC_Lib::validates_ip_address( $host ) === true ) {
				$good_host = sanitize_text_field( $host );
			} else {
				$good_host = false;
			}

			//Do we have a valid user to lockout or not
			if ( $user !== NULL && ITSEC_Lib::user_id_exists( intval( $user ) ) === true ) {
				$good_user = intval( $user );
			} else {
				$good_user = false;
			}

			//Do we have a valid username to lockout or not
			if ( $username !== NULL && $username != '' ) {
				$good_username = $username;
			} else {
				$good_username = false;
			}

			$blacklist_host = false; //assume we're not permanently blcking the host

			//Sanitize the data for later
			$type   = sanitize_text_field( $type );
			$reason = sanitize_text_field( $reason );

			//handle a permanent host ban (if needed)
			if ( $itsec_globals['settings']['blacklist'] === true && $good_host !== false ) { //permanent blacklist

				$blacklist_period = isset( $itsec_globals['settings']['blacklist_period'] ) ? $itsec_globals['settings']['blacklist_period'] * 24 * 60 * 60 : 604800;

				$host_count = 1 + $wpdb->get_var(
				                       $wpdb->prepare(
				                            "SELECT COUNT(*) FROM `" . $wpdb->base_prefix . "itsec_lockouts` WHERE `lockout_expire_gmt` > '%s' AND `lockout_host`='%s';",
				                            date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] + $blacklist_period ),
				                            $host
				                       )
					);

				if ( $host_count >= $itsec_globals['settings']['blacklist_count'] && isset( $itsec_globals['settings']['write_files'] ) && $itsec_globals['settings']['write_files'] === true ) {

					$host_expiration = false;

					if ( ! class_exists( 'ITSEC_Ban_Users' ) ) {
						require( trailingslashit( $itsec_globals['plugin_dir'] ) . 'modules/free/ban-users/class-itsec-ban-users.php' );
					}

					ITSEC_Ban_Users::insert_ip( sanitize_text_field( $host ) ); //Send it to the Ban Users module for banning

					$blacklist_host = true; //flag it so we don't do a temp ban as well

				}

			}

			//We have temp bans to perform
			if ( $good_host !== false || $good_user !== false || $good_username || $good_username !== false ) {

				if ( $this->is_ip_whitelisted( sanitize_text_field( $host ) ) ) {

					$whitelisted    = true;
					$expiration     = date( 'Y-m-d H:i:s', 1 );
					$expiration_gmt = date( 'Y-m-d H:i:s', 1 );

				} else {

					$whitelisted    = false;
					$exp_seconds    = ( intval( $itsec_globals['settings']['lockout_period'] ) * 60 );
					$expiration     = date( 'Y-m-d H:i:s', $itsec_globals['current_time'] + $exp_seconds );
					$expiration_gmt = date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] + $exp_seconds );

				}

				if ( $good_host !== false && $blacklist_host === false ) { //temp lockout host

					$host_expiration = $expiration;

					$wpdb->insert(
					     $wpdb->base_prefix . 'itsec_lockouts',
					     array(
						     'lockout_type'      => $type,
						     'lockout_start'     => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
						     'lockout_start_gmt' => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
						     'lockout_expire'    => $expiration, 'lockout_expire_gmt' => $expiration_gmt,
						     'lockout_host'      => sanitize_text_field( $host ),
						     'lockout_user'      => '',
					     )
					);

					$itsec_logger->log_event( __( 'lockout', 'it-l10n-better-wp-security' ), 10, array(
						'expires' => $expiration, 'expires_gmt' => $expiration_gmt, 'type' => $type
					), sanitize_text_field( $host ) );

				}

				if ( $good_user !== false ) { //blacklist host and temp lockout user

					$user_expiration = $expiration;

					$wpdb->insert(
					     $wpdb->base_prefix . 'itsec_lockouts',
					     array(
						     'lockout_type'       => $type,
						     'lockout_start'      => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
						     'lockout_start_gmt'  => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
						     'lockout_expire'     => $expiration,
						     'lockout_expire_gmt' => $expiration_gmt,
						     'lockout_host'       => '',
						     'lockout_user'       => intval( $user ),
					     )
					);

					if ( $whitelisted === false ) {
						$itsec_logger->log_event( 'lockout', 10, array(
							'expires' => $expiration, 'expires_gmt' => $expiration_gmt, 'type' => $type
						), '', '', intval( $user ) );
					} else {
						$itsec_logger->log_event( 'lockout', 10, array(
							__( 'White Listed', 'it-l10n-better-wp-security' ), 'type' => $type
						), '', '', intval( $user ) );
					}

				}

				if ( $good_username !== false ) { //blacklist host and temp lockout username

					$user_expiration = $expiration;

					$wpdb->insert(
					     $wpdb->base_prefix . 'itsec_lockouts',
					     array(
						     'lockout_type'       => $type,
						     'lockout_start'      => date( 'Y-m-d H:i:s', $itsec_globals['current_time'] ),
						     'lockout_start_gmt'  => date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] ),
						     'lockout_expire'     => $expiration,
						     'lockout_expire_gmt' => $expiration_gmt,
						     'lockout_host'       => '',
						     'lockout_username'   => $username,
					     )
					);

					if ( $whitelisted === false ) {
						$itsec_logger->log_event( 'lockout', 10, array(
							'expires' => $expiration, 'expires_gmt' => $expiration_gmt, 'type' => $type
						), '', '', $username );
					} else {
						$itsec_logger->log_event( 'lockout', 10, array(
							__( 'White Listed', 'it-l10n-better-wp-security' ), 'type' => $type
						), '', '', $username );
					}

				}

				if ( $whitelisted === false ) {

					if ( $itsec_globals['settings']['email_notifications'] === true ) { //send email notifications
						$this->send_lockout_email( $good_host, $good_user, $good_username, $host_expiration, $user_expiration, $reason );
					}

					if ( $good_host !== false ) {

						$itsec_files->release_file_lock( 'lockout_' . $host . $user . $username );
						$this->execute_lock();

					} else {

						$itsec_files->release_file_lock( 'lockout_' . $host . $user . $username );
						$this->execute_lock( true );

					}

				}

			}

			$itsec_files->release_file_lock( 'lockout_' . $host . $user . $username );

		}

	}

	/**
	 * Active lockouts table and form for dashboard.
	 *
	 * @Since 4.0
	 *
	 * @return void
	 */
	public function lockout_metabox() {

		?>
		<form method="post" action="" id="itsec_release_lockout_form">
			<?php wp_nonce_field( 'itsec_release_lockout', 'wp_nonce' ); ?>
			<input type="hidden" name="itsec_release_lockout" value="true"/>
			<?php //get locked out hosts and users from database
			$host_locks = $this->get_lockouts( 'host', true );
			$user_locks = $this->get_lockouts( 'user', true );
			$username_locks = $this->get_lockouts( 'username', true );
			?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row" class="settinglabel">
						<?php _e( 'Locked out hosts', 'it-l10n-better-wp-security' ); ?>
					</th>
					<td class="settingfield">
						<?php if ( sizeof( $host_locks ) > 0 ) { ?>
							<ul>
								<?php foreach ( $host_locks as $host ) { ?>
									<li style="list-style: none;"><input type="checkbox"
									                                     name="lo_<?php echo $host['lockout_id']; ?>"
									                                     id="lo_<?php echo $host['lockout_id']; ?>"
									                                     value="<?php echo $host['lockout_id']; ?>"/>
										<label
											for="lo_<?php echo $host['lockout_id']; ?>"><strong><?php echo filter_var( $host['lockout_host'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );; ?></strong>
											- Expires
											<em><?php echo $host['lockout_expire'] ?></em></label>
									</li>
								<?php } ?>
							</ul>
						<?php } else { //no host is locked out ?>
							<ul>
								<li style="list-style: none;">
									<p><?php _e( 'Currently no hosts are locked out of this website.', 'it-l10n-better-wp-security' ); ?></p>
								</li>
							</ul>
						<?php } ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="settinglabel">
						<?php _e( 'Locked out users', 'it-l10n-better-wp-security' ); ?>
					</th>
					<td class="settingfield">
						<?php if ( sizeof( $user_locks ) > 0 ) { ?>
							<ul>
								<?php foreach ( $user_locks as $user ) { ?>
									<?php $userdata = get_userdata( $user['lockout_user'] ); ?>
									<li style="list-style: none;"><input type="checkbox"
									                                     name="lo_<?php echo $user['lockout_id']; ?>"
									                                     id="lo_<?php echo $user['lockout_id']; ?>"
									                                     value="<?php echo $user['lockout_id']; ?>"/>
										<label
											for="lo_<?php echo $user['lockout_id']; ?>"><strong><?php echo $userdata->user_login; ?></strong>
											- Expires
											<em><?php echo $user['lockout_expire']; ?></em></label>
									</li>
								<?php } ?>
							</ul>
						<?php } else { //no user is locked out ?>
							<ul>
								<li style="list-style: none;">
									<p><?php _e( 'Currently no users are locked out of this website.', 'it-l10n-better-wp-security' ); ?></p>
								</li>
							</ul>
						<?php } ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="settinglabel">
						<?php _e( 'Locked out usernames (not real users)', 'it-l10n-better-wp-security' ); ?>
					</th>
					<td class="settingfield">
						<?php if ( sizeof( $username_locks ) > 0 ) { ?>
							<ul>
								<?php foreach ( $username_locks as $user ) { ?>
									<li style="list-style: none;"><input type="checkbox"
									                                     name="lo_<?php echo $user['lockout_id']; ?>"
									                                     id="lo_<?php echo $user['lockout_id']; ?>"
									                                     value="<?php echo $user['lockout_id']; ?>"/>
										<label
											for="lo_<?php echo $user['lockout_id']; ?>"><strong><?php echo sanitize_text_field( $user['lockout_username'] ); ?></strong>
											- Expires
											<em><?php echo $user['lockout_expire']; ?></em></label>
									</li>
								<?php } ?>
							</ul>
						<?php } else { //no user is locked out ?>
							<ul>
								<li style="list-style: none;">
									<p><?php _e( 'Currently no usernames are locked out of this website.', 'it-l10n-better-wp-security' ); ?></p>
								</li>
							</ul>
						<?php } ?>
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" class="button-primary"
			                         value="<?php _e( 'Release Lockout', 'it-l10n-better-wp-security' ); ?>"/></p>
		</form>
	<?php
	}

	/**
	 * Purges lockouts more than 7 days old from the database
	 *
	 * @return void
	 */
	public function purge_lockouts() {

		global $wpdb, $itsec_globals;

		$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "itsec_lockouts` WHERE `lockout_expire_gmt` < '" . date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] - ( ( $itsec_globals['settings']['blacklist_period'] + 1 ) * 24 * 60 * 60 ) ) . "';" );
		$wpdb->query( "DELETE FROM `" . $wpdb->base_prefix . "itsec_temp` WHERE `temp_date_gmt` < '" . date( 'Y-m-d H:i:s', $itsec_globals['current_time_gmt'] - 86400 ) . "';" );

	}

	/**
	 * Register 404 and file change detection for logger
	 *
	 * @param  array $logger_modules array of logger modules
	 *
	 * @return array                   array of logger modules
	 */
	public function register_logger( $logger_modules ) {

		$logger_modules['lockout'] = array(
			'type'     => 'lockout',
			'function' => __( 'Host or User Lockout', 'it-l10n-better-wp-security' ),
		);

		return $logger_modules;

	}

	/**
	 * Register Lockouts for Sync
	 *
	 * @param  array $sync_modules array of logger modules
	 *
	 * @return array                   array of logger modules
	 */
	public function register_sync( $sync_modules ) {

		$sync_modules['lockout'] = array(
			'verbs'      => array(
				'itsec-get-lockouts'    => 'Ithemes_Sync_Verb_ITSEC_Get_Lockouts',
				'itsec-release-lockout' => 'Ithemes_Sync_Verb_ITSEC_Release_Lockout'
			),
			'everything' => 'itsec-get-lockouts',
			'path'       => dirname( __FILE__ ),
		);

		return $sync_modules;

	}

	/**
	 * Register modules that will use the lockout service
	 *
	 * @return void
	 */
	public function register_modules() {

		$this->lockout_modules = apply_filters( 'itsec_lockout_modules', $this->lockout_modules );

	}

	/**
	 * Process clearing lockouts on view log page
	 *
	 * @since 4.0
	 *
	 * @return bool true on success or false
	 */
	public function release_lockout( $id = NULL ) {

		global $wpdb;

		if ( $id !== NULL && trim( $id ) !== '' ) {

			$sanitized_id = intval( $id );

			$lockout = $wpdb->get_results( "SELECT * FROM `" . $wpdb->base_prefix . "itsec_lockouts` WHERE lockout_id = " . $sanitized_id . ";", ARRAY_A );

			if ( is_array( $lockout ) && sizeof( $lockout ) >= 1 ) {

				return $wpdb->update(
				            $wpdb->base_prefix . 'itsec_lockouts',
				            array(
					            'lockout_active' => 0,
				            ),
				            array(
					            'lockout_id' => $sanitized_id,
				            )
				);

			} else {

				return false;

			}

		} elseif ( isset( $_POST['itsec_release_lockout'] ) && $_POST['itsec_release_lockout'] == 'true' ) {

			if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'itsec_release_lockout' ) ) {
				die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
			}

			$type    = 'updated';
			$message = __( 'The selected lockouts have been cleared.', 'it-l10n-better-wp-security' );

			foreach ( $_POST as $key => $value ) {

				if ( strstr( $key, "lo_" ) ) { //see if it's a lockout to avoid processing extra post fields

					$wpdb->update(
					     $wpdb->base_prefix . 'itsec_lockouts',
					     array(
						     'lockout_active' => 0,
					     ),
					     array(
						     'lockout_id' => intval( $value ),
					     )
					);

				}

			}

			ITSEC_Lib::clear_caches();

			if ( is_multisite() ) {

				$error_handler = new WP_Error();

				$error_handler->add( $type, $message );

				$this->core->show_network_admin_notice( $error_handler );

			} else {

				add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

			}

		}

	}

	/**
	 * Sends an email to notify site admins of lockouts
	 *
	 * @since 4.0
	 *
	 * @param  string $host            the host to lockout
	 * @param  int    $user            the user id to lockout
	 * @param string  $username        the username to lockout
	 * @param  string $host_expiration when the host login expires
	 * @param  string $user_expiration when the user lockout expires
	 * @param  string $reason          the reason for the lockout to show to the user
	 *
	 * @return void
	 */
	private function send_lockout_email( $host, $user, $username, $host_expiration, $user_expiration, $reason ) {

		global $itsec_globals;

		$plural_text = __( 'has', 'it-l10n-better-wp-security' );

		//Tell which host was locked out
		if ( $host !== false ) {

			$host_text = sprintf( '%s, <a href="http://ip-adress.com/ip_tracer/%s"><strong>%s</strong></a>, ', __( 'host', 'it-l10n-better-wp-security' ), sanitize_text_field( $host ), sanitize_text_field( $host ) );

			$host_expiration_text = __( 'The host has been locked out ', 'it-l10n-better-wp-security' );

			if ( $host_expiration === false ) {

				$host_expiration_text .= '<strong>' . __( 'permanently', 'it-l10n-better-wp-security' ) . '</strong>';
				$release_text = sprintf( '%s <a href="%s">%s</a>.', __( 'To release the host lockout you can remove the host from the', 'it-l10n-better-wp-security' ), get_Admin_url( '', 'admin.php?page=toplevel_page_itsec-ban_users' ), __( 'host list', 'it-l10n-better-wp-security' ) );

			} else {

				$host_expiration_text .= sprintf( '<strong>%s %s</strong>', __( 'until', 'it-l10n-better-wp-security' ), sanitize_text_field( $host_expiration ) );
				$release_text = sprintf( '%s <a href="%s">%s</a>.', __( 'To release the lockout please visit', 'it-l10n-better-wp-security' ), get_Admin_url( '', 'admin.php?page=toplevel_page_itsec-ban_users' ), __( 'the admin area', 'it-l10n-better-wp-security' ) );

			}

		} else {

			$host_expiration_text = '';
			$host_text            = '';
			$release_text         = '';

		}

		$user_object = get_userdata( $user ); //try to get and actual user object

		//Tell them which user was locked out and setup the expiration copy
		if ( $user_object !== false || $username !== NULL ) {

			if ( $user_object !== false ) {
				$login = $user_object->user_login;
			} else {
				$login = sanitize_text_field( $username );
			}

			if ( $host_text === '' ) {

				$user_expiration_text = sprintf( '%s <strong>%s %s</strong>.', __( 'The user has been locked out', 'it-l10n-better-wp-security' ), __( 'until', 'it-l10n-better-wp-security' ), sanitize_text_field( $user_expiration ) );

				$user_text = sprintf( '%s, <strong>%s</strong>, ', __( 'user', 'it-l10n-better-wp-security' ), $login );

				$release_text = sprintf( '%s <a href="%s">%s</a>.', __( 'To release the lockout please visit', 'it-l10n-better-wp-security' ), get_Admin_url( '', 'admin.php?page=toplevel_page_itsec-ban_users' ), __( 'the lockouts page', 'it-l10n-better-wp-security' ) );

			} else {

				$user_expiration_text = sprintf( '%s <strong>%s %s</strong>.', __( 'and the user has been locked out', 'it-l10n-better-wp-security' ), __( 'until', 'it-l10n-better-wp-security' ), sanitize_text_field( $user_expiration ) );
				$plural_text          = __( 'have', 'it-l10n-better-wp-security' );
				$user_text            = sprintf( '%s, <strong>%s</strong>, ', __( 'and a user', 'it-l10n-better-wp-security' ), $login );

				if ( $host_expiration === false ) {

					$release_text .= sprintf( '%s <a href="%s">%s</a>.', __( 'To release the user lockout please visit', 'it-l10n-better-wp-security' ), get_Admin_url( '', 'admin.php?page=toplevel_page_itsec-ban_users' ), __( 'the lockouts page', 'it-l10n-better-wp-security' ) );

				} else {

					$release_text = sprintf( '%s <a href="%s">%s</a>.', __( 'To release the lockouts please visit', 'it-l10n-better-wp-security' ), get_Admin_url( '', 'admin.php?page=toplevel_page_itsec-ban_users' ), __( 'the lockouts page', 'it-l10n-better-wp-security' ) );

				}

			}

		} else {

			$user_expiration_text = '.';
			$user_text            = '';
			$release_text         = '';

		}

		//Put the copy all together
		$body = sprintf(
			'<p>%s,</p><p>%s %s %s %s %s <a href="%s">%s</a> %s <strong>%s</strong>.</p><p>%s %s</p><p>%s</p><p><em>*%s %s. %s <a href="%s">%s</a>.</em></p>',
			__( 'Dear Site Admin', 'it-l10n-better-wp-security' ),
			__( 'A', 'it-l10n-better-wp-security' ),
			$host_text,
			$user_text,
			$plural_text,
			__( ' been locked out of the WordPress site at', 'it-l10n-better-wp-security' ),
			get_option( 'siteurl' ),
			get_option( 'siteurl' ),
			__( 'due to', 'it-l10n-better-wp-security' ),
			sanitize_text_field( $reason ),
			$host_expiration_text,
			$user_expiration_text,
			$release_text,
			__( 'This email was generated automatically by' ),
			$itsec_globals['plugin_name'],
			__( 'To change your email preferences please visit', 'it-l10n-better-wp-security' ),
			get_Admin_url( '', 'admin.php?page=toplevel_page_itsec_settings' ),
			__( 'the plugin settings', 'it-l10n-better-wp-security' ) );

		//Setup the remainder of the email
		$recipients = $itsec_globals['settings']['notification_email'];
		$subject    = '[' . get_option( 'siteurl' ) . '] ' . __( 'Site Lockout Notification', 'it-l10n-better-wp-security' );
		$subject    = apply_filters( 'itsec_lockout_email_subject', $subject );
		$headers    = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";

		//Use HTML Content type
		add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

		//Send emails to all recipients
		foreach ( $recipients as $recipient ) {

			if ( is_email( trim( $recipient ) ) ) {
				wp_mail( trim( $recipient ), $subject, $body, $headers );
			}

		}

		//Remove HTML Content type
		remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

	}

	/**
	 * Set HTML content type for email
	 *
	 * @return string html content type
	 */
	public function set_html_content_type() {

		return 'text/html';

	}

	/**
	 * Sets an error message when a user has been forcibly logged out due to lockout
	 *
	 * @return string
	 */
	public function set_lockout_error() {

		global $itsec_globals;

		//check to see if it's the logout screen
		if ( isset( $_GET['itsec'] ) && $_GET['itsec'] == true ) {
			return '<div id="login_error">' . $itsec_globals['settings']['user_lockout_message'] . '</div>' . PHP_EOL;
		}

	}

}
