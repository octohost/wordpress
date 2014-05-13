<?php

/**
 * Plugin-wide settings for logs, email and more
 *
 * @package iThemes_Security
 *
 * @since   4.0
 */
class ITSEC_Global_Settings {

	private
		$settings,
		$core,
		$allowed_tags;

	function __construct( $core ) {

		if ( is_admin() ) {

			$this->initialize( $core );

		}

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 * @param array $available_pages array of available page_hooks
	 */
	public function add_admin_meta_boxes() {

		add_meta_box(
			'global_table_of_contents',
			__( 'Quick Links', 'it-l10n-better-wp-security' ),
			array( $this, 'add_module_intro' ),
			'security_page_toplevel_page_itsec_settings',
			'normal',
			'core'
		);

		add_meta_box(
			'advanced_intro',
			__( 'Welcome', 'it-l10n-better-wp-security' ),
			array( $this, 'add_module_advanced_intro' ),
			'security_page_toplevel_page_itsec_advanced',
			'normal',
			'core'
		);

		add_meta_box(
			'global_options',
			__( 'Global Settings', 'it-l10n-better-wp-security' ),
			array( $this, 'metabox_advanced_settings' ),
			'security_page_toplevel_page_itsec_settings',
			'advanced',
			'core'
		);

	}

	/**
	 * Build and echo the away mode description
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function add_module_intro() {

		?>

		<label class="itsec_toc_label" for="itsec_toc_select">Go to</label>
		<select class="itsec_toc" onchange="itsec_toc_select(this.value)">
			<option value="#global_table_of_contents">Choose a section...</option>
			<?php foreach ( $this->core->get_toc_items() as $box ) { ?>
				<?php echo '<option value="#' . $box['id'] . '" >' . $box['title'] . '</option>'; ?>
			<?php } ?>
		</select>

		<!--<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label class="itsec_toc_label" for="itsec_toc_select" >Choose a section</label></th>
					<td>
						<select class="itsec_toc" onchange="itsec_toc_select(this.value)">
							<option value="#global_table_of_contents">Choose a section...</option>
							<?php foreach ( $this->core->get_toc_items() as $box ) { ?>
								<?php echo '<option value="#' . $box['id'] . '" >' . $box['title'] . '</option>'; ?>
							<?php } ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		
		-->
	<?php
	}

	/**
	 * Build and echo the advanced page description.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function add_module_advanced_intro() {

		global $itsec_globals;

		if ( array_key_exists( 'backup', $itsec_globals['free_modules'] ) ) {
			$backup_link_open  = '<strong><a href="?page=toplevel_page_itsec_backups">';
			$backup_link_close = '</a></strong>';
		} else {
			$backup_link_open  = '';
			$backup_link_close = '';
		}

		printf(
			'<p>%s %s%s%s %s</p>',
			__( 'The settings below are more advanced settings that should be done with caution on an existing site.',
			    'it-l10n-better-wp-security' ),
			$backup_link_open,
			__( 'Make sure you have a good backup before changing any setting on this page.', 'it-l10n-better-wp-security' ),
			$backup_link_close,
			__( 'In addition, these settings will not be reversed if you remove this plugin. That said, all settings on this page use methods recommended by WordPress.org itself and will help in improving the security of your site.',
			    'it-l10n-better-wp-security' )
		);

	}

	/**
	 * echos allow tracking Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function allow_tracking() {

		if ( isset( $this->settings['allow_tracking'] ) && $this->settings['allow_tracking'] === true ) {
			$allow_tracking = 1;
		} else {
			$allow_tracking = 0;
		}

		echo '<input type="checkbox" id="itsec_global_allow_tracking" name="itsec_global[allow_tracking]" value="1" ' . checked( 1,
		                                                                                                                         $allow_tracking,
		                                                                                                                         false ) . '/>';
		echo '<label for="itsec_global_allow_tracking">' . __( 'Allow iThemes to track plugin usage via anonymous data. ',
		                                                       'it-l10n-better-wp-security' ) . '</label>';

	}

	/**
	 * echos Backup email Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function backup_email() {

		if ( isset( $this->settings['backup_email'] ) && is_array( $this->settings['backup_email'] ) ) {
			$emails = implode( PHP_EOL, $this->settings['backup_email'] );
		} else {
			$emails = get_option( 'admin_email' );
		}

		echo '<textarea id="itsec_global_backup_email" name="itsec_global[backup_email]">' . $emails . '</textarea>';
		echo '<p class="description">' . __( 'The email address(es) all database backups will be sent to. One address per line.',
		                                     'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * echos Blacklist Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function blacklist() {

		if ( isset( $this->settings['blacklist'] ) && $this->settings['blacklist'] === false ) {
			$blacklist = 0;
		} else {
			$blacklist = 1;
		}

		echo '<input type="checkbox" id="itsec_global_blacklist" name="itsec_global[blacklist]" value="1" ' . checked( 1,
		                                                                                                               $blacklist,
		                                                                                                               false ) . '/>';
		echo '<label for="itsec_global_blacklist"> ' . __( 'Enable Blacklist Repeat Offender', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description"> ' . __( 'If this box is checked the IP address of the offending computer will be added to the "Ban Users" blacklist after reaching the number of lockouts listed below.',
		                                      'it-l10n-better-wp-security' ) . '</p>';

		if ( ITSEC_Lib::get_server() == 'nginx' ) {

			echo '<p class="description"> ' . __( 'Note that as you are on NGINX you will still need to manually restart the server even though the users will be added to the banned users list.',
			                                      'it-l10n-better-wp-security' ) . '</p>';

		}

	}

	/**
	 * echos Blacklist Threshold Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function blacklist_count() {

		if ( isset( $this->settings['blacklist_count'] ) ) {
			$blacklist_count = absint( $this->settings['blacklist_count'] );
		} else {
			$blacklist_count = 3;
		}

		echo '<input class="small-text" name="itsec_global[blacklist_count]" id="itsec_global_blacklist_count" value="' . $blacklist_count . '" type="text">';
		echo '<label for="itsec_global_blacklist_count"> ' . __( 'Lockouts', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description"> ' . __( 'The number of lockouts per IP before the host is banned permanently from this site.',
		                                      'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * echos Blacklist Lookback Period Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function blacklist_period() {

		if ( isset( $this->settings['blacklist_period'] ) ) {
			$blacklist_period = absint( $this->settings['blacklist_period'] );
		} else {
			$blacklist_period = 7;
		}

		echo '<input class="small-text" name="itsec_global[blacklist_period]" id="itsec_global_blacklist_period" value="' . $blacklist_period . '" type="text">';
		echo '<label for="itsec_global_blacklist_period"> ' . __( 'Days', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description"> ' . __( 'How many days should a lockout be remembered to meet the blacklist count above.',
		                                      'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * echos Lockout Email Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function email_notifications() {

		if ( isset( $this->settings['email_notifications'] ) && $this->settings['email_notifications'] === false ) {
			$email_notifications = 0;
		} else {
			$email_notifications = 1;
		}

		echo '<input type="checkbox" id="itsec_global_email_notifications" name="itsec_global[email_notifications]" value="1" ' . checked( 1,
		                                                                                                                                   $email_notifications,
		                                                                                                                                   false ) . '/>';
		echo '<label for="itsec_global_email_notifications">' . __( 'Enable Email Lockout Notifications',
		                                                            'it-l10n-better-wp-security' ) . '</label>';
		printf( '<p class="description">%s<a href="admin.php?page=toplevel_page_itsec_settings">%s</a>%s</p>',
		        __( 'This feature will trigger an email to be sent to the ', 'it-l10n-better-wp-security' ),
		        __( 'notifications email address', 'it-l10n-better-wp-security' ),
		        __( ' whenever a host or user is locked out of the system.', 'it-l10n-better-wp-security' ) );

	}

	/**
	 * Empty callback function
	 */
	public function empty_callback_function() {
	}

	/**
	 * echos Lockout Email Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function infinitewp_compatibility() {

		if ( isset( $this->settings['infinitewp_compatibility'] ) && $this->settings['infinitewp_compatibility'] === true ) {
			$infinitewp_compatibility = 1;
		} else {
			$infinitewp_compatibility = 0;
		}

		echo '<input type="checkbox" id="itsec_global_infinitewp_compatibility" name="itsec_global[infinitewp_compatibility]" value="1" ' . checked( 1,
		                                                                                                                                             $infinitewp_compatibility,
		                                                                                                                                             false ) . '/>';
		echo '<label for="itsec_global_infinitewp_compatibility">' . __( 'Enable InfiniteWP Compatibility',
		                                                                 'it-l10n-better-wp-security' ) . '</label>';
		printf(
			'<p class="description">%s <a href="http://infinitewp.com" target=""_blank">%s</a> %s</p>',
			__( 'Turning this feature on will enable compatibility with', 'it-l10n-better-wp-security' ),
			__( 'InfiniteWP.', 'it-l10n-better-wp-security' ),
			__( 'Do not turn it on unless you use the InfiniteWP service.', 'it-l10n-better-wp-security' )
		);

	}

	/**
	 * Initializes all admin functionality.
	 *
	 * @since 4.0
	 *
	 * @param ITSEC_Core $core The $itsec_core instance
	 *
	 * @return void
	 */
	private function initialize( $core ) {

		$this->core     = $core;
		$this->settings = get_site_option( 'itsec_global' );

		$this->allowed_tags = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'h1'     => array(),
			'h2'     => array(),
			'h3'     => array(),
			'h4'     => array(),
			'h5'     => array(),
			'h6'     => array(),
			'div'    => array(
				'style' => array(),
			),
		);

		add_filter( 'itsec_tooltip_modules', array( $this, 'register_tooltip' ) ); //register tooltip action
		add_action( 'itsec_add_admin_meta_boxes',
		            array( $this, 'add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area

		//manually save options on multisite
		if ( is_multisite() ) {
			add_action( 'itsec_admin_init', array( $this, 'save_network_options' ) ); //save multisite options
		}

	}

	/**
	 * Execute admin initializations
	 *
	 * @return void
	 */
	public function initialize_admin() {

		//Add Settings sections
		add_settings_section(
			'global',
			__( 'Global Settings', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		//Settings Fields
		add_settings_field(
			'itsec_global[write_files]',
			__( 'Write to Files', 'it-l10n-better-wp-security' ),
			array( $this, 'write_files' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		add_settings_field(
			'itsec_global[notification_email]',
			__( 'Notification Email', 'it-l10n-better-wp-security' ),
			array( $this, 'notification_email' ),
			'security_page_toplevel_page_itsec_settings',
			'global',
			array( 'label_for' => 'itsec_global_notification_email' )
		);

		add_settings_field(
			'itsec_global[backup_email]',
			__( 'Backup Delivery Email', 'it-l10n-better-wp-security' ),
			array( $this, 'backup_email' ),
			'security_page_toplevel_page_itsec_settings',
			'global',
			array( 'label_for' => 'itsec_global_backup_email' )
		);

		add_settings_field(
			'itsec_global[lockout_message]',
			__( 'Host Lockout Message', 'it-l10n-better-wp-security' ),
			array( $this, 'lockout_message' ),
			'security_page_toplevel_page_itsec_settings',
			'global',
			array( 'label_for' => 'itsec_global_lockout_message' )
		);

		add_settings_field(
			'itsec_global[user_lockout_message]',
			__( 'User Lockout Message', 'it-l10n-better-wp-security' ),
			array( $this, 'user_lockout_message' ),
			'security_page_toplevel_page_itsec_settings',
			'global',
			array( 'label_for' => 'itsec_global_user_lockout_message' )
		);

		add_settings_field(
			'itsec_global[blacklist]',
			__( 'Blacklist Repeat Offender', 'it-l10n-better-wp-security' ),
			array( $this, 'blacklist' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		add_settings_field(
			'itsec_global[blacklist_count]',
			__( 'Blacklist Threshold', 'it-l10n-better-wp-security' ),
			array( $this, 'blacklist_count' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		add_settings_field(
			'itsec_global[blacklist_period]',
			__( 'Blacklist Lookback Period', 'it-l10n-better-wp-security' ),
			array( $this, 'blacklist_period' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		add_settings_field(
			'itsec_global[lockout_period]',
			__( 'Lockout Period', 'it-l10n-better-wp-security' ),
			array( $this, 'lockout_period' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		add_settings_field(
			'itsec_global[lockout_white_list]',
			__( 'Lockout White List', 'it-l10n-better-wp-security' ),
			array( $this, 'lockout_white_list' ),
			'security_page_toplevel_page_itsec_settings',
			'global',
			array( 'label_for' => 'itsec_global_lockout_white_list' )
		);

		add_settings_field(
			'itsec_global[email_notifications]',
			__( 'Email Lockout Notifications', 'it-l10n-better-wp-security' ),
			array( $this, 'email_notifications' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		add_settings_field(
			'itsec_global[log_type]',
			__( 'Log Type', 'it-l10n-better-wp-security' ),
			array( $this, 'log_type' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		add_settings_field(
			'itsec_global[log_rotation]',
			__( 'Days to Keep Database Logs', 'it-l10n-better-wp-security' ),
			array( $this, 'log_rotation' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		add_settings_field(
			'itsec_global[log_location]',
			__( 'Path to Log Files', 'it-l10n-better-wp-security' ),
			array( $this, 'log_location' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		if ( is_dir( WP_PLUGIN_DIR . '/iwp-client' ) ) {

			add_settings_field(
				'itsec_global[infinitewp_compatibility]',
				__( 'Add InfiniteWP Compatibility', 'it-l10n-better-wp-security' ),
				array( $this, 'infinitewp_compatibility' ),
				'security_page_toplevel_page_itsec_settings',
				'global'
			);

		}

		add_settings_field(
			'itsec_global[allow_tracking]',
			__( 'Allow Data Tracking', 'it-l10n-better-wp-security' ),
			array( $this, 'allow_tracking' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		if ( ITSEC_Lib::get_server() == 'nginx' ) {

			add_settings_field(
				'itsec_global[nginx_file]',
				__( 'NGINX Conf File', 'it-l10n-better-wp-security' ),
				array( $this, 'nginx_file' ),
				'security_page_toplevel_page_itsec_settings',
				'global'
			);

		}

		add_settings_field(
			'itsec_global[lock_file]',
			__( 'Disable File Locking', 'it-l10n-better-wp-security' ),
			array( $this, 'lock_file' ),
			'security_page_toplevel_page_itsec_settings',
			'global'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_settings',
			'itsec_global',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * echos Lock File Field
	 *
	 * @since 4.0.20
	 *
	 * @return void
	 */
	public function lock_file() {

		if ( isset( $this->settings['lock_file'] ) && $this->settings['lock_file'] === true ) {
			$lock_file = 1;
		} else {
			$lock_file = 0;
		}

		echo '<input type="checkbox" id="itsec_global_lock_file" name="itsec_global[lock_file]" value="1" ' . checked( 1,
		                                                                                                               $lock_file,
		                                                                                                               false ) . '/>';
		echo '<label for="itsec_global_lock_file">' . __( 'Disable File Locking', 'it-l10n-better-wp-security' ) . '</label>';
		printf(
			'<p class="description">%s</p>',
			__( 'Turning this option on will prevent errors related to file locking however might result in operations being executed twice. We do not recommend turning this off unless your host prevents the file locking feature from working correctly.',
			    'it-l10n-better-wp-security' )
		);

	}

	/**
	 * echos Admin User Username Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function lockout_message() {

		if ( isset( $this->settings['lockout_message'] ) ) {
			$lockout_message = wp_kses( $this->settings['lockout_message'], $this->allowed_tags );
		} else {
			$lockout_message = __( 'error', 'it-l10n-better-wp-security' );
		}

		echo '<textarea class="widefat" name="itsec_global[lockout_message]" id="itsec_global_lockout_message" rows="5" >' . $lockout_message . '</textarea>';
		echo '<p class="description">' . __( 'The message to display when a computer (host) has been locked out.',
		                                     'it-l10n-better-wp-security' ) . '</p>';
		echo '<p class="description">' . __( 'You can use HTML in your message. Allowed tags include: a, br, em, strong, h1, h2, h3, h4, h5, h6, div',
		                                     'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * echos Lockout Period Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function lockout_period() {

		if ( isset( $this->settings['lockout_period'] ) ) {
			$lockout_period = absint( $this->settings['lockout_period'] );
		} else {
			$lockout_period = 15;
		}

		echo '<input class="small-text" name="itsec_global[lockout_period]" id="itsec_global_lockout_period" value="' . $lockout_period . '" type="text">';
		echo '<label for="itsec_global_lockout_period"> ' . __( 'Minutes', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description"> ' . __( 'The length of time a host or user will be banned from this site after hitting the limit of bad logins.',
		                                      'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * echos Lockout White List Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function lockout_white_list() {

		$white_list = '';

		//Convert and show the agent list
		if ( isset( $this->settings['lockout_white_list'] ) && is_array( $this->settings['lockout_white_list'] ) && sizeof( $this->settings['lockout_white_list'] ) >= 1 ) {

			$white_list = implode( PHP_EOL, $this->settings['lockout_white_list'] );

		} elseif ( isset( $this->settings['lockout_white_list'] ) && ! is_array( $this->settings['lockout_white_list'] ) && strlen( $this->settings['lockout_white_list'] ) > 1 ) {

			$white_list = $this->settings['lockout_white_list'];

		}

		echo '<textarea id="itsec_global_lockout_white_list" name="itsec_global[lockout_white_list]" rows="10" cols="50">' . $white_list . '</textarea>';
		echo '<p class="description">' . __( 'Use the guidelines below to enter hosts that will not be locked out from your site. This will keep you from locking yourself out of any features if you should trigger a lockout. Please note this does not override away mode and will only prevent a temporary ban. Should a permanent ban be triggered you will still be added to the "Ban Users" list unless the IP address is also white listed in that section.',
		                                     'it-l10n-better-wp-security' ) . '</p>';
		echo '<ul>';
		echo '<li>' . __( 'You may white list users by individual IP address or IP address range.', 'it-l10n-better-wp-security' ) . '</li>';
		echo '<li>' . __( 'Individual IP addesses must be in IPV4 standard format (i.e. ###.###.###.### or ###.###.###.###/##). Wildcards (*) or a netmask is allowed to specify a range of ip addresses.',
		                  'it-l10n-better-wp-security' ) . '</li>';
		echo '<li>' . __( 'If using a wildcard (*) you must start with the right-most number in the ip field. For example ###.###.###.* and ###.###.*.* are permitted but ###.###.*.### is not.',
		                  'it-l10n-better-wp-security' ) . '</li>';
		echo '<li><a href="http://ip-lookup.net/domain-lookup.php" target="_blank">' . __( 'Lookup IP Address.',
		                                                                                   'it-l10n-better-wp-security' ) . '</a></li>';
		echo '<li>' . __( 'Enter only 1 IP address or 1 IP address range per line.', 'it-l10n-better-wp-security' ) . '</li>';
		echo '</ul>';
		echo '<p class="description"><strong>' . __( 'This white list will prevent any ip listed from triggering an automatic lockout. You can still block the IP address manually in the banned users settings.',
		                                             'it-l10n-better-wp-security' ) . '</strong></p>';

	}

	/**
	 * echos Log Location Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function log_location() {

		global $itsec_globals;

		if ( isset( $this->settings['log_location'] ) ) {
			$log_location = sanitize_text_field( $this->settings['log_location'] );
		} else {
			$log_location = $itsec_globals['ithemes_log_dir'];
		}

		echo '<input class="large-text" name="itsec_global[log_location]" id="itsec_global_log_location" value="' . $log_location . '" type="text">';
		echo '<label for="itsec_global_log_location"> ' . __( 'The path on your server where log files should be stored.',
		                                                      'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description"> ' . __( 'This path must be writable by your website. For added security it is recommended you do not include it in your website root folder.',
		                                      'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * echos Log Rotation Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function log_rotation() {

		if ( isset( $this->settings['log_rotation'] ) ) {
			$log_rotation = absint( $this->settings['log_rotation'] );
		} else {
			$log_rotation = 30;
		}

		echo '<input class="small-text" name="itsec_global[log_rotation]" id="itsec_global_log_rotation" value="' . $log_rotation . '" type="text">';
		echo '<label for="itsec_global_log_rotation"> ' . __( 'Days', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description"> ' . __( 'The number of days database logs should be kept. File logs will be kept indefinitely but will be rotated once the file hits 10MB.',
		                                      'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * echos Log type Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function log_type() {

		global $itsec_globals;

		if ( isset( $this->settings['log_type'] ) ) {
			$log_type = $this->settings['log_type'];
		} else {
			$log_type = 0;
		}

		echo '<select id="itsec_global_log_type" name="itsec_global[log_type]">';

		echo '<option value="0" ' . selected( $log_type, '0' ) . '>' . __( 'Database Only', 'it-l10n-better-wp-security' ) . '</option>';
		echo '<option value="1" ' . selected( $log_type, '1' ) . '>' . __( 'File Only', 'it-l10n-better-wp-security' ) . '</option>';
		echo '<option value="2" ' . selected( $log_type, '2' ) . '>' . __( 'Both', 'it-l10n-better-wp-security' ) . '</option>';
		echo '</select>';
		echo '<label for="itsec_global_log_type"> ' . __( 'How should event logs be kept', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description">' . $itsec_globals['plugin_name'] . __( ' can log events in multiple ways, each with advantages and disadvantages. Database Only puts all events in the database with your posts and other WordPress data. This makes it easy to retrieve and process but can be slower if the database table gets very large. File Only is very fast but the plugin does not process the logs itself as that would take far more resources. For most users or smaller sites Database Only should be fine. If you have a very large site or a log processing software then File Only might be a better option.' ) . '</p>';

	}

	/**
	 * Render the settings metabox
	 *
	 * @return void
	 */
	public function metabox_advanced_settings() {

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'global', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_settings' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes',
		                                                                               'it-l10n-better-wp-security' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * echos NGINX conf file Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function nginx_file() {

		if ( isset( $this->settings['nginx_file'] ) ) {
			$nginx_file = sanitize_text_field( $this->settings['nginx_file'] );
		} else {
			$nginx_file = ABSPATH . 'nginx.conf';
		}

		echo '<input class="large-text" name="itsec_global[nginx_file]" id="itsec_backup_nginx_file" value="' . $nginx_file . '" type="text">';
		echo '<label for="itsec_backup_nginx_file"> ' . __( 'The path on your server where backup files should be stored.',
		                                                    'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description"> ' . __( 'This path must be writable by your website. For added security, it is recommended you do not include it in your website root folder.',
		                                      'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * echos Admin User Username Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function notification_email() {

		if ( isset( $this->settings['notification_email'] ) && is_array( $this->settings['notification_email'] ) ) {
			$emails = implode( PHP_EOL, $this->settings['notification_email'] );
		} else {
			$emails = get_option( 'admin_email' );
		}

		echo '<textarea id="itsec_global_notification_email" name="itsec_global[notification_email]">' . $emails . '</textarea>';
		echo '<p class="description">' . __( 'The email address(es) all security notifications will be sent to. One address per line.',
		                                     'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * Register backups for tooltips
	 *
	 * @param  array $tooltip_modules array of tooltip modules
	 *
	 * @return array                   array of tooltip modules
	 */
	public function register_tooltip( $tooltip_modules ) {

		global $itsec_globals;

		if ( get_site_transient( 'ITSEC_SHOW_WRITE_FILES_TOOLTIP' ) || ( ! isset ( $this->settings['write_files'] ) || $this->settings['write_files'] === false ) ) {

			$tooltip_modules['writing'] = array(
				'priority'  => 5,
				'class'     => 'itsec_tooltip_writing',
				'heading'   => __( 'Allow File Updates', 'it-l10n-better-wp-security' ),
				'text'      => __( 'Many of the functions of this plugin require editing your wp-config.php or .htaccess files. Would you like to allow us to safely update these files for you automatically?',
				                   'it-l10n-better-wp-security' ),
				'link_text' => __( 'Allow file updates', 'it-l10n-better-wp-security' ),
				'callback'  => array( $this, 'tooltip_ajax_writing' ),
				'success'   => __( 'Setting Saved. File updates allowed.', 'it-l10n-better-wp-security' ),
				'failure'   => __( 'Whoops. Something went wrong. Check the "Global Settings" section on the settings page (it is the first setting) to make sure your option was saved or contact support.',
				                   'it-l10n-better-wp-security' ),
			);

		}

		if ( ! isset ( $this->settings['allow_tracking'] ) || $this->settings['allow_tracking'] === false ) {

			$tooltip_modules['tracking'] = array(
				'priority'  => 1,
				'class'     => 'itsec_tooltip_tracking',
				'heading'   => __( 'Help Us Improve', 'it-l10n-better-wp-security' ),
				'text'      => sprintf( '%s %s', $itsec_globals['plugin_name'],
				                        __( 'would like to collect anonymous data about features you use to help improve this plugin. Absolutely no information that can identify you will be collected.',
				                            'it-l10n-better-wp-security' ) ),
				'link_text' => __( 'Yes, I\'d like to help', 'it-l10n-better-wp-security' ),
				'callback'  => array( $this, 'tooltip_ajax_tracking' ),
				'success'   => __( 'Setting Saved. Thanks for helping us make this plugin better.', 'it-l10n-better-wp-security' ),
				'failure'   => __( 'Whoops. Something went wrong. Check the global settings page or contact support.',
				                   'it-l10n-better-wp-security' ),
			);

		}

		return $tooltip_modules;

	}

	/**
	 * Sanitize and validate input
	 *
	 * @param  Array $input array of input fields
	 *
	 * @return Array         Sanitized array
	 */
	public function sanitize_module_input( $input ) {

		global $itsec_globals;

		$input['did_upgrade'] = isset( $this->settings['did_upgrade'] ) ? $this->settings['did_upgrade'] : false;

		if ( isset( $input['backup_email'] ) ) {

			$bad_emails     = array();
			$emails_to_save = array();

			if ( isset( $input['backup_email'] ) && ! is_array( $input['backup_email'] ) ) {
				$emails = explode( PHP_EOL, $input['backup_email'] );
			} elseif ( isset( $input['backup_email'] ) ) {
				$emails = $input['backup_email'];
			}

			foreach ( $emails as $email ) {

				$email = sanitize_text_field( trim( $email ) );

				if ( is_email( $email ) === false ) {
					$bad_emails[] = $email;
				}

				$emails_to_save[] = $email;

			}

			if ( sizeof( $bad_emails ) > 0 ) {

				$bad_addresses = implode( ', ', $bad_emails );
				$type          = 'error';
				$message       = __( 'The following backup email address(es) do not appear to be valid: ',
				                     'it-l10n-better-wp-security' ) . $bad_addresses;

				add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

			}

			$input['backup_email'] = $emails_to_save;
		}

		if ( isset( $input['notification_email'] ) ) {

			$bad_emails     = array();
			$emails_to_save = array();

			if ( isset( $input['notification_email'] ) && ! is_array( $input['notification_email'] ) ) {
				$emails = explode( PHP_EOL, $input['notification_email'] );
			} else {
				$emails = $input['notification_email'];
			}

			foreach ( $emails as $email ) {

				$email = sanitize_text_field( trim( $email ) );

				if ( is_email( $email ) === false ) {
					$bad_emails[] = $email;
				}

				$emails_to_save[] = $email;

			}

			if ( sizeof( $bad_emails ) > 0 ) {

				$bad_addresses = implode( ', ', $bad_emails );
				$type          = 'error';
				$message       = __( 'The following notification email address(es) do not appear to be valid: ',
				                     'it-l10n-better-wp-security' ) . $bad_addresses;

				add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

			}

			$input['notification_email'] = $emails_to_save;
		}

		$input['lockout_message']          = isset( $input['lockout_message'] ) ? wp_kses( $input['lockout_message'],
		                                                                                   $this->allowed_tags ) : '';
		$input['user_lockout_message']     = isset( $input['user_lockout_message'] ) ? wp_kses( $input['user_lockout_message'],
		                                                                                        $this->allowed_tags ) : '';
		$input['blacklist']                = ( isset( $input['blacklist'] ) && intval( $input['blacklist'] == 1 ) ? true : false );
		$input['blacklist_count']          = isset( $input['blacklist_count'] ) ? absint( $input['blacklist_count'] ) : 3;
		$input['blacklist_period']         = isset( $input['blacklist_period'] ) ? absint( $input['blacklist_period'] ) : 7;
		$input['email_notifications']      = ( isset( $input['email_notifications'] ) && intval( $input['email_notifications'] == 1 ) ? true : false );
		$input['lockout_period']           = isset( $input['lockout_period'] ) ? absint( $input['lockout_period'] ) : 15;
		$input['log_rotation']             = isset( $input['log_rotation'] ) ? absint( $input['log_rotation'] ) : 30;
		$input['allow_tracking']           = ( isset( $input['allow_tracking'] ) && intval( $input['allow_tracking'] == 1 ) ? true : false );
		$input['write_files']              = ( isset( $input['write_files'] ) && intval( $input['write_files'] == 1 ) ? true : false );
		$input['nginx_file']               = isset( $input['nginx_file'] ) ? sanitize_text_field( $input['nginx_file'] ) : ABSPATH . 'nginx.conf';
		$input['infinitewp_compatibility'] = ( isset( $input['infinitewp_compatibility'] ) && intval( $input['infinitewp_compatibility'] == 1 ) ? true : false );
		$input['log_info']                 = $itsec_globals['settings']['log_info'];
		$input['lock_file']                = ( isset( $input['lock_file'] ) && intval( $input['lock_file'] == 1 ) ? true : false );

		$input['log_location'] = isset( $input['log_location'] ) ? sanitize_text_field( $input['log_location'] ) : $itsec_globals['ithemes_log_dir'];

		//Process white list
		if ( isset( $input['lockout_white_list'] ) && ! is_array( $input['lockout_white_list'] ) ) {
			$white_listed_addresses = explode( PHP_EOL, $input['lockout_white_list'] );
		} else {
			$white_listed_addresses = array();
		}

		$bad_white_listed_ips = array();
		$raw_white_listed_ips = array();

		foreach ( $white_listed_addresses as $index => $address ) {

			if ( strlen( trim( $address ) ) > 0 ) {

				if ( ITSEC_Lib::validates_ip_address( $address ) === false ) {

					$bad_white_listed_ips[] = filter_var( $address, FILTER_SANITIZE_STRING );

				}

				$raw_white_listed_ips[] = filter_var( $address, FILTER_SANITIZE_STRING );

			} else {
				unset( $white_listed_addresses[$index] );
			}

		}

		$raw_white_listed_ips = array_unique( $raw_white_listed_ips );

		if ( sizeof( $bad_white_listed_ips ) > 0 ) {

			$type    = 'error';
			$message = '';

			$message .= sprintf( '%s<br /><br />',
			                     __( 'There is a problem with an IP address in the white list:', 'it-l10n-better-wp-security' ) );

			foreach ( $bad_white_listed_ips as $bad_ip ) {
				$message .= sprintf( '%s %s<br />', $bad_ip,
				                     __( 'is not a valid address in the white list users box.', 'it-l10n-better-wp-security' ) );
			}

			add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

		}

		$input['lockout_white_list'] = $raw_white_listed_ips;

		if ( $input['log_location'] != $itsec_globals['ithemes_log_dir'] ) {
			$good_path = ITSEC_Lib::validate_path( $input['log_location'] );
		} else {
			$good_path = true;
		}

		if ( $good_path !== true ) {

			$type              = 'error';
			$message           = __( 'The file path entered does not appear to be valid. Please ensure it exists and that WordPress can write to it. ',
			                         'it-l10n-better-wp-security' );
			$input['log_type'] = 0;

			add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

		} else {
			$input['log_type'] = isset( $input['log_type'] ) ? intval( $input['log_type'] ) : 0;
		}

		if ( ! isset( $type ) && $input['write_files'] === true && $this->settings['write_files'] === false ) {

			add_site_option( 'itsec_rewrites_changed', true );

		}

		if ( is_multisite() ) {

			if ( isset( $type ) ) {

				$error_handler = new WP_Error();

				$error_handler->add( $type, $message );

				$this->core->show_network_admin_notice( $error_handler );

			} else {

				$this->core->show_network_admin_notice( false );

			}

			$this->settings = $input;

		}

		return $input;

	}

	/**
	 * Prepare and save options in network settings
	 *
	 * @return void
	 */
	public function save_network_options() {

		if ( isset( $_POST['itsec_global'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_settings-options' ) ) {
				die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
			}

			update_site_option( 'itsec_global', $_POST['itsec_global'] ); //we must manually save network options

		}

	}

	/**
	 * Performs actions for tracking tooltip function.
	 *
	 * @since 4.0
	 *
	 * return void
	 */
	public function tooltip_ajax_tracking() {

		$this->settings['allow_tracking'] = true;

		$result = update_site_option( 'itsec_global', $this->settings );

		if ( $result === true ) {
			echo 'true';
		} else {
			echo 'false';
		}

	}

	/**
	 * Performs actions for writing tooltip function.
	 *
	 * @since 4.0
	 *
	 * return void
	 */
	public function tooltip_ajax_writing() {

		$this->settings['write_files'] = true;

		$result = update_site_option( 'itsec_global', $this->settings );

		if ( $result === true ) {
			echo 'true';
		} else {
			echo 'false';
		}

	}

	/**
	 * echos Admin User Username Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function user_lockout_message() {

		if ( isset( $this->settings['user_lockout_message'] ) ) {
			$user_lockout_message = wp_kses( $this->settings['user_lockout_message'], $this->allowed_tags );
		} else {
			$user_lockout_message = __( 'You have been locked out due to too many login attempts.', 'it-l10n-better-wp-security' );
		}

		echo '<textarea class="widefat" name="itsec_global[user_lockout_message]" id="itsec_global_user_lockout_message" rows="5" >' . $user_lockout_message . '</textarea><br />';
		echo '<p class="description">' . __( 'The message to display to a user when their account has been locked out.',
		                                     'it-l10n-better-wp-security' ) . '</p>';
		echo '<p class="description">' . __( 'You can use HTML in your message. Allowed tags include: a, br, em, strong, h1, h2, h3, h4, h5, h6, div',
		                                     'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * echos write files Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function write_files() {

		global $itsec_globals;

		if ( isset( $this->settings['write_files'] ) && $this->settings['write_files'] === true ) {
			$write_files = 1;
		} else {
			$write_files = 0;
		}

		if ( ITSEC_Lib::get_server() == 'nginx' ) {
			$server_file = '';
		} else {
			$server_file = ' and .htaccess';
		}

		echo '<input type="checkbox" id="itsec_global_write_files" name="itsec_global[write_files]" value="1" ' . checked( 1,
		                                                                                                                   $write_files,
		                                                                                                                   false ) . '/>';
		printf(
			'<label for="itsec_global_write_files">%s %s %s%s.</label>',
			__( 'Allow', 'it-l10n-better-wp-security' ),
			$itsec_globals['plugin_name'],
			__( 'to write to wp-config.php', 'it-l10n-better-wp-security' ),
			$server_file
		);
		printf(
			'<p class="description">%s %s %s%s%s</p>',
			__( 'Whether or not', 'it-l10n-better-wp-security' ),
			$itsec_globals['plugin_name'],
			__( 'should be allowed to write to wp-config.php', 'it-l10n-better-wp-security' ),
			$server_file,
			__( ' automatically. If disabled you will need to manually place configuration options in those files.',
			    'it-l10n-better-wp-security' )
		);

	}

}