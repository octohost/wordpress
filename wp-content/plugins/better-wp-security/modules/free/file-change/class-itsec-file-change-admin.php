<?php

class ITSEC_File_Change_Admin {

	private
		$settings,
		$core,
		$module,
		$module_path,
		$module_path_relative;

	function run( $core, $module ) {

		if ( is_admin() ) {

			$this->initialize( $core, $module );

		}

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 * @return void
	 */
	public function add_admin_meta_boxes() {

		$id    = 'file_change_options';
		$title = __( 'File Change Detection', 'it-l10n-better-wp-security' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_advanced_file_change_settings' ),
			'security_page_toplevel_page_itsec_settings',
			'advanced',
			'core'
		);

		$this->core->add_toc_item(
		           array(
			           'id'    => $id,
			           'title' => $title,
		           )
		);

	}

	/**
	 * Add Files Admin Javascript
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function admin_script() {

		global $itsec_globals;

		wp_enqueue_script( 'itsec_file_change_warning_js', $this->module_path . 'js/admin-file-change-warning.js', array( 'jquery' ), $itsec_globals['plugin_build'] );
		wp_localize_script(
			'itsec_file_change_warning_js',
			'itsec_file_change_warning',
			array(
				'nonce' => wp_create_nonce( 'itsec_file_change_warning' ),
				'url'   => admin_url() . 'admin.php?page=toplevel_page_itsec_logs',
			)
		);

		if ( isset( get_current_screen()->id ) && ( strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_settings' ) !== false || strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_logs' ) !== false ) ) {

			wp_enqueue_script( 'itsec_file_change_js', $this->module_path . 'js/admin-file-change.js', array( 'jquery' ), $itsec_globals['plugin_build'] );
			wp_localize_script(
				'itsec_file_change_js',
				'itsec_file_change',
				array(
					'mem_limit'            => ITSEC_Lib::get_memory_limit(),
					'text'                 => __( 'Warning: Your server has less than 128MB of RAM dedicated to PHP. If you have many files in your installation or a lot of active plugins activating this feature may result in your site becoming disabled with a memory error. See the plugin homepage for more information.', 'it-l10n-better-wp-security' ),
					'module_path'          => $this->module_path,
					'button_text'          => isset( $this->settings['split'] ) && $this->settings['split'] === true ? __( 'Scan Next File Chunk', 'it-l10n-better-wp-security' ) : __( 'Scan Files Now', 'it-l10n-better-wp-security' ),
					'scanning_button_text' => __( 'Scanning... (this could take a while on a large site)', 'it-l10n-better-wp-security' ),
					'no_changes'           => __( 'No changes were detected.', 'it-l10n-better-wp-security' ),
					'changes'              => __( 'Changes were detected. Please check the log page for details.', 'it-l10n-better-wp-security' ),
					'error'                => __( 'An error occured. Please try again later', 'it-l10n-better-wp-security' ),
					'ABSPATH'              => ABSPATH,
					'nonce'                => wp_create_nonce( 'itsec_do_file_check' ),
				)
			);

			wp_enqueue_script( 'itsec_jquery_filetree', $this->module_path . 'filetree/jqueryFileTree.js', array( 'jquery' ), '1.01' );
			wp_localize_script(
				'itsec_jquery_filetree',
				'itsec_jquery_filetree',
				array(
					'nonce' => wp_create_nonce( 'itsec_jquery_filetree' ),
				)
			);

			wp_register_style( 'itsec_jquery_filetree_style', $this->module_path . 'filetree/jqueryFileTree.css' ); //add multi-select css
			wp_enqueue_style( 'itsec_jquery_filetree_style' );

			wp_register_style( 'itsec_file_change_css', $this->module_path . 'css/admin-file-change.css' ); //add multi-select css
			wp_enqueue_style( 'itsec_file_change_css' );

		}

	}

	/**
	 * Sets the status in the plugin dashboard
	 *
	 * @since 4.0
	 *
	 * @return array statuses
	 */
	public function dashboard_status( $statuses ) {

		if ( $this->settings['enabled'] === true ) {

			$status_array = 'safe-medium';
			$status       = array(
				'text' => __( 'Your site will detect changes to your files.', 'it-l10n-better-wp-security' ),
				'link' => '#itsec_file_change_enabled',
			);

		} else {

			$status_array = 'medium';
			$status       = array(
				'text' => __( 'Your website is not looking for changed files. Consider turning on file change detections.', 'it-l10n-better-wp-security' ),
				'link' => '#itsec_file_change_enabled',
			);

		}

		array_push( $statuses[$status_array], $status );

		return $statuses;

	}

	/**
	 * Display admin warning
	 *
	 * Displays a warning to adminstrators when file changes have been detected
	 *
	 **/
	function dashboard_warning() {

		global $blog_id; //get the current blog id

		if ( ( is_multisite() && ( $blog_id != 1 || ! current_user_can( 'manage_network_options' ) ) ) || ! current_user_can( 'activate_plugins' ) ) { //only display to network admin if in multisite
			return;
		}

		//if there is a warning to display
		if ( get_site_option( 'itsec_file_change_warning' ) == '1' ) {

			if ( ! function_exists( 'itsec_intrusion_warning' ) ) {

				function itsec_intrusion_warning() {

					global $itsec_globals;

					printf(
						'<div id="itsec_file_change_warning_dialog" class="error"><p>%s %s</p> <p><input type="button" id="itsec_go_to_logs" class="button-primary" value="%s">&nbsp;<input type="button" id="itsec_dismiss_file_change_warning" class="button-secondary" value="%s"></p></div>',
						$itsec_globals['plugin_name'],
						__( 'has noticed a change to some files in your WordPress site. Please review the logs to make sure your system has not been compromised.', 'it-l10n-better-wp-security' ),
						__( 'View Logs', 'it-l10n-better-wp-security' ),
						__( 'Dismiss Warning', 'it-l10n-better-wp-security' )

					);

				}

			}

			//put the warning in the right spot
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', 'itsec_intrusion_warning' ); //register notification
			} else {
				add_action( 'admin_notices', 'itsec_intrusion_warning' ); //register notification
			}

		}

		//if they've clicked a button hide the notice
		if ( ( isset( $_GET['bit51_view_logs'] ) || isset( $_GET['bit51_dismiss_warning'] ) ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'bit51-nag' ) ) {

			//Get the options
			if ( is_multisite() ) {

				switch_to_blog( 1 );

				delete_option( 'bwps_intrusion_warning' );

				restore_current_blog();

			} else {

				delete_option( 'bwps_intrusion_warning' );

			}

			//take them back to where they started
			if ( isset( $_GET['bit51_dismiss_warning'] ) ) {
				wp_redirect( $_SERVER['HTTP_REFERER'], 302 );
			}

			//take them to the correct logs page
			if ( isset( $_GET['bit51_view_logs'] ) ) {
				if ( is_multisite() ) {
					wp_redirect( admin_url() . 'network/admin.php?page=better-wp-security-logs#file-change', 302 );
				} else {
					wp_redirect( admin_url() . 'admin.php?page=better-wp-security-logs#file-change', 302 );
				}
			}

		}

	}

	/**
	 * Empty callback function
	 */
	public function empty_callback_function() {
	}

	/**
	 * echos Email File Change Notifications Field
	 *
	 * @param  array $args field arguments
	 *
	 * @return void
	 */
	public function email() {

		if ( isset( $this->settings['email'] ) && $this->settings['email'] === false ) {
			$email = 0;
		} else {
			$email = 1;
		}

		$content = '<input type="checkbox" id="itsec_file_change_email" name="itsec_file_change[email]" value="1" ' . checked( 1, $email, false ) . '/>';
		$content .= '<label for="itsec_file_change_email"> ' . __( 'Email file change notifications', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Notifications will be sent to all emails set to receive notifications on the global settings page.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Enable File Change Detection Field
	 *
	 * @param  array $args field arguments
	 *
	 * @return void
	 */
	public function enabled() {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		$content = '<input type="checkbox" id="itsec_file_change_enabled" name="itsec_file_change[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		$content .= '<label for="itsec_file_change_enabled"> ' . __( 'Enable File Change detection', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

	}

	/**
	 * echos split file checks Field
	 *
	 * @param  array $args field arguments
	 *
	 * @return void
	 */
	public function split() {

		if ( isset( $this->settings['split'] ) && $this->settings['split'] === true ) {
			$split = 1;
		} else {
			$split = 0;
		}

		echo '<input type="checkbox" id="itsec_file_change_split" name="itsec_file_change[split]" value="1" ' . checked( 1, $split, false ) . '/>';
		echo '<label for="itsec_file_change_split"> ' . __( 'Split file checking into chunks.', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description"> ' . __( 'Splits file checking into 7 chunks (plugins, themes, wp-admin, wp-includes, uploads, the rest of wp-content and everything that is left over) and divides the checks evenly over the course of a day. This feature may result in more notifications but will allow for the scanning of bigger sites to continue even on a lower-end web host.', 'it-l10n-better-wp-security' ) . '</p>';

	}

	/**
	 * Echos the one-time file change scan form
	 *
	 * @since 4.0
	 *
	 * @param string $origin the origin
	 *
	 * @return void
	 */
	public function file_change_form( $origin ) {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {

			echo '<form id="itsec_one_time_file_check" method="post" action="">';
			echo wp_nonce_field( 'itsec_do_file_check', 'wp_nonce' );
			echo '<input type="hidden" name="itsec_file_change_origin" value="' . sanitize_text_field( $origin ) . '"><div id="itsec_file_change_status"></div>';
			echo '<p>' . __( "Press the button below to scan your site's files for changes. Note that if changes are found this will take you to the logs page for details.", 'it-l10n-better-wp-security' ) . '</p>';
			echo '<p><input type="submit" id="itsec_one_time_file_check_submit" class="button-primary" value="' . ( isset( $this->settings['split'] ) && $this->settings['split'] === true ? __( 'Scan Next File Chunk', 'it-l10n-better-wp-security' ) : __( 'Scan Files Now', 'it-l10n-better-wp-security' ) ) . '" /></p>';
			echo '</form>';

		}

	}

	/**
	 * Executes one-time backup.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function file_change_warning_ajax() {

		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_file_change_warning' ) ) {
			die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
		}

		die( delete_site_option( 'itsec_file_change_warning' ) );

	}

	/**
	 * echos Enable File Change List Field
	 *
	 * @param  array $args field arguments
	 *
	 * @return void
	 */
	public function file_list() {

		if ( isset( $this->settings['file_list'] ) && is_array( $this->settings['file_list'] ) ) {
			$file_list = implode( PHP_EOL, $this->settings['file_list'] );
		} else {
			$file_list = '';
		}

		$content = '<p class="description">' . __( 'Exclude files or folders by clicking the red minus next to the file or folder name.', 'it-l10n-better-wp-security' ) . '</p>';
		$content .= '<div class="file_list">';
		$content .= '<div class="file_chooser"><div class="jquery_file_tree"></div></div>';
		$content .= '<div class="list_field">';
		$content .= '<textarea id="itsec_file_change_file_list" name="itsec_file_change[file_list]" wrap="off">' . $file_list . '</textarea>';
		$content .= '</div></div>';

		echo $content;

	}

	/**
	 * Render the file change log metabox
	 *
	 * @return void
	 */
	public function logs_metabox() {

		global $itsec_globals;

		$this->file_change_form( 'settings' );

		if ( ! class_exists( 'ITSEC_File_Change_Log' ) ) {
			require( dirname( __FILE__ ) . '/class-itsec-file-change-log.php' );
		}

		echo __( 'Below is a summary log of all the file changes recorded for your WordPress site. To get details on a particular item click the title. To adjust logging options visit the global settings page.', 'it-l10n-better-wp-security' );

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {

			// If we're splitting the file check run it every 6 hours. Else daily.
			if ( isset( $this->settings['split'] ) && $this->settings['split'] === true ) {
				$interval = 12342;
			} else {
				$interval = 86400;
			}

			$next_run_raw = $this->settings['last_run'] + $interval;

			if ( date( 'j', $next_run_raw ) == date( 'j', $itsec_globals['current_time'] ) ) {
				$next_run_day = __( 'Today', 'it-l10n-better-wp-security' );
			} else {
				$next_run_day = __( 'Tomorrow', 'it-l10n-better-wp-security' );
			}

			$next_run = $next_run_day . ' at ' . date( 'g:i a', $next_run_raw );

			echo '<p>' . __( 'Next automatic scan at: ', 'it-l10n-better-wp-security' ) . '<strong>' . $next_run . '*</strong></p>';
			echo '<p><em>*' . __( 'Automatic file change scanning is triggered by a user visiting your page and may not happen exactly at the time listed.', 'it-l10n-better-wp-security' ) . '</em>';

		}

		$log_display = new ITSEC_File_Change_Log();

		$log_display->prepare_items();
		$log_display->display();

	}

	/**
	 * echos method Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function method() {

		if ( isset( $this->settings['method'] ) ) {
			$method = $this->settings['method'] === true ? 1 : 0;
		} else {
			$method = 1;
		}

		echo '<select id="itsec_file_change_method" name="itsec_file_change[method]">';

		echo '<option value="1" ' . selected( $method, '1' ) . '>' . __( 'Exclude Selected', 'it-l10n-better-wp-security' ) . '</option>';
		echo '<option value="0" ' . selected( $method, '0' ) . '>' . __( 'Include Selected', 'it-l10n-better-wp-security' ) . '</option>';
		echo '</select><br />';
		echo '<label for="itsec_file_change_method"> ' . __( 'Include/Exclude Files', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description">' . __( 'Select what we should exclude files and folders selected or whether the scan should only include files and folders selected.' ) . '</p>';

	}

	/**
	 * echos Email File Change Notifications Field
	 *
	 * @param  array $args field arguments
	 *
	 * @return void
	 */
	public function notify_admin() {

		if ( isset( $this->settings['notify_admin'] ) && $this->settings['notify_admin'] === false ) {
			$notify_admin = 0;
		} else {
			$notify_admin = 1;
		}

		$content = '<input type="checkbox" id="itsec_file_change_notify_admin" name="itsec_file_change[notify_admin]" value="1" ' . checked( 1, $notify_admin, false ) . '/>';
		$content .= '<label for="itsec_file_change_notify_admin"> ' . __( 'Display file change admin warning', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Disabling this feature will prevent the file change warning from displaying to the site administrator in the WordPress Dashboard. Note that disabling both the error message and the email notification will result in no notifications of file changes. The only way you will be able to tell is by manually checking the log files.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos file change types Field
	 *
	 * @param  array $args field arguments
	 *
	 * @return void
	 */
	public function types() {

		if ( isset( $this->settings['types'] ) && is_array( $this->settings['types'] ) ) {
			$types = implode( PHP_EOL, $this->settings['types'] );
		} else {
			$types = implode( PHP_EOL, array( '.jpg', '.jpeg', '.png' ) );
		}

		$content = '<textarea id="itsec_file_change_types" name="itsec_file_change[types]" wrap="off" cols="20" rows="10">' . $types . '</textarea><br />';
		$content .= '<label for="itsec_file_change_types"> ' . __( 'File types listed here will not be checked for changes. While it is possible to change files such as images it is quite rare and nearly all known WordPress attacks exploit php, js and other text files.', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

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
	private function initialize( $core, $module ) {

		$this->core                 = $core;
		$this->module               = $module;
		$this->settings             = get_site_option( 'itsec_file_change' );
		$this->module_path          = ITSEC_Lib::get_module_path( __FILE__ );
		$this->module_path_relative = ITSEC_Lib::get_module_path( __FILE__, true );

		add_action( 'itsec_add_admin_meta_boxes', array(
			$this, 'add_admin_meta_boxes'
		) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) ); //enqueue scripts for admin page
		add_filter( 'itsec_add_dashboard_status', array(
			$this, 'dashboard_status'
		) ); //add information for plugin status
		add_filter( 'itsec_metaboxes', array( $this, 'register_logger_metaboxes' ) ); //adds logs metaboxes
		add_filter( 'itsec_tracking_vars', array( $this, 'tracking_vars' ) );

		//manually save options on multisite
		if ( is_multisite() ) {
			add_action( 'itsec_admin_init', array( $this, 'save_network_options' ) ); //save multisite options
		}

		add_action( 'wp_ajax_itsec_file_change_ajax', array( $this, 'one_time_file_check_ajax' ) );
		add_action( 'wp_ajax_itsec_file_change_warning_ajax', array( $this, 'file_change_warning_ajax' ) );
		add_action( 'wp_ajax_itsec_jquery_filetree_ajax', array( $this, 'jquery_filetree_ajax' ) );

	}

	/**
	 * Execute admin initializations
	 *
	 * @return void
	 */
	public function initialize_admin() {

		$this->dashboard_warning();

		//Add Settings sections
		add_settings_section(
			'file_change-enabled',
			__( 'File Change Detection', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		add_settings_section(
			'file_change-settings',
			__( 'File Change Detection Settings', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		//File Change Detection Fields
		add_settings_field(
			'itsec_file_change[enabled]',
			__( 'File Change Detection', 'it-l10n-better-wp-security' ),
			array( $this, 'enabled' ),
			'security_page_toplevel_page_itsec_settings',
			'file_change-enabled'
		);

		add_settings_field(
			'itsec_file_change[split]',
			__( 'Split File Scanning', 'it-l10n-better-wp-security' ),
			array( $this, 'split' ),
			'security_page_toplevel_page_itsec_settings',
			'file_change-settings'
		);

		add_settings_field(
			'itsec_file_change[method]',
			__( 'Include/Exclude Files and Folders', 'it-l10n-better-wp-security' ),
			array( $this, 'method' ),
			'security_page_toplevel_page_itsec_settings',
			'file_change-settings'
		);

		add_settings_field(
			'itsec_file_change[file_list]',
			__( 'Files and Folders List', 'it-l10n-better-wp-security' ),
			array( $this, 'file_list' ),
			'security_page_toplevel_page_itsec_settings',
			'file_change-settings'
		);

		add_settings_field(
			'itsec_file_change[types]',
			__( 'Ignore File Types', 'it-l10n-better-wp-security' ),
			array( $this, 'types' ),
			'security_page_toplevel_page_itsec_settings',
			'file_change-settings'
		);

		add_settings_field(
			'itsec_file_change[email]',
			__( 'Email File Change Notifications', 'it-l10n-better-wp-security' ),
			array( $this, 'email' ),
			'security_page_toplevel_page_itsec_settings',
			'file_change-settings'
		);

		add_settings_field(
			'itsec_file_change[notify_admin]',
			__( 'Display file change admin warning', 'it-l10n-better-wp-security' ),
			array( $this, 'notify_admin' ),
			'security_page_toplevel_page_itsec_settings',
			'file_change-settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_settings',
			'itsec_file_change',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Render the settings metabox
	 *
	 * @return void
	 */
	public function metabox_advanced_file_change_settings() {

		echo '<p>' . __( 'Even the best security solutions can fail. How do you know if someone gets into your site? You will know because they will change something. File Change detection will tell you what files have changed in your WordPress installation alerting you to changes not made by yourself. Unlike other solutions this plugin will look only at your installation and compare files to the last check instead of comparing them with a remote installation thereby taking into account whether or not you modify the files yourself.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $this->file_change_form( 'logs' );

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'file_change-enabled', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'file_change-settings', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_settings' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-better-wp-security' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Executes one-time backup.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function jquery_filetree_ajax() {

		global $itsec_globals;

		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_jquery_filetree' ) || ! current_user_can( $itsec_globals['plugin_access_lvl'] ) ) {
			die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
		}

		$directory = sanitize_text_field( $_POST['dir'] );

		$directory = urldecode( $directory );

		$path = $this->module_path_relative;

		if ( file_exists( $directory ) ) {

			$files = scandir( $directory );

			natcasesort( $files );

			if ( count( $files ) > 2 ) { /* The 2 accounts for . and .. */

				echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";

				// All dirs
				foreach ( $files as $file ) {

					if ( file_exists( $directory . $file ) && $file != '.' && $file != '..' && is_dir( $directory . $file ) ) {
						echo '<li class="directory collapsed"><a href="#" rel="' . htmlentities( $directory . $file ) . '/">' . htmlentities( $file ) . '<div class="itsec_treeselect_control"><img src="' . $path . 'images/redminus.png" style="vertical-align: -3px;" title="Add to exclusions..." class="itsec_filetree_exclude"></div></a></li>';
					}

				}

				// All files
				foreach ( $files as $file ) {

					if ( file_exists( $directory . $file ) && $file != '.' && $file != '..' && ! is_dir( $directory . $file ) ) {

						$ext = preg_replace( '/^.*\./', '', $file );
						echo '<li class="file ext_' . $ext . '"><a href="#" rel="' . htmlentities( $directory . $file ) . '">' . htmlentities( $file ) . '<div class="itsec_treeselect_control"><img src="' . $path . 'images/redminus.png" style="vertical-align: -3px;" title="Add to exclusions..." class="itsec_filetree_exclude"></div></a></li>';

					}

				}

				echo "</ul>";

			}

		}

		exit;

	}

	/**
	 * Executes one-time backup.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function one_time_file_check_ajax() {

		if ( ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_do_file_check' ) ) {
			die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
		}

		die( $this->module->execute_file_check( false ) );

	}

	/**
	 * Array of metaboxes for the logs screen
	 *
	 * @since 4.0
	 *
	 * @param array $metaboxes metabox array
	 *
	 * @return array metabox array
	 */
	public function register_logger_metaboxes( $metaboxes ) {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {

			$metaboxes[] = array(
				'module'   => 'file_change',
				'title'    => __( 'File Change History', 'it-l10n-better-wp-security' ),
				'callback' => array( $this, 'logs_metabox' )
			);

		}

		return $metaboxes;

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

		//File Change Detection Fields
		$input['enabled']      = ( isset( $input['enabled'] ) && intval( $input['enabled'] == 1 ) ? true : false );
		$input['split']        = ( isset( $input['split'] ) && intval( $input['split'] == 1 ) ? true : false );
		$input['method']       = ( isset( $input['method'] ) && intval( $input['method'] == 1 ) ? true : false );
		$input['email']        = ( isset( $input['email'] ) && intval( $input['email'] == 1 ) ? true : false );
		$input['notify_admin'] = ( isset( $input['notify_admin'] ) && intval( $input['notify_admin'] == 1 ) ? true : false );
		$input['last_chunk']   = ( isset( $input['last_chunk'] ) ? $input['last_chunk'] : false );

		if ( ! is_array( $input['file_list'] ) ) {
			$file_list = explode( PHP_EOL, $input['file_list'] );
		} else {
			$file_list = $input['file_list'];
		}

		$good_files = array();

		foreach ( $file_list as $file ) {
			$good_files[] = sanitize_text_field( trim( $file ) );
		}

		$input['file_list'] = $good_files;

		if ( ! is_array( $input['types'] ) ) {
			$file_types = explode( PHP_EOL, $input['types'] );
		} else {
			$file_types = $input['types'];
		}

		$good_types = array();

		foreach ( $file_types as $file_type ) {

			$file_type = trim( $file_type );

			if ( strlen( $file_type ) > 0 && $file_type != '.' ) {

				$good_type = sanitize_text_field( '.' . str_replace( '.', '', $file_type ) );

				$good_types[] = sanitize_text_field( trim( $good_type ) );

			}
		}

		$input['types'] = $good_types;

		if ( $input['split'] === true ) {
			$interval = 12282;
		} else {
			$interval = 86340;
		}

		if ( defined( 'ITSEC_DOING_FILE_CHECK' ) && ITSEC_DOING_FILE_CHECK === true ) {
			$input['last_run'] = $itsec_globals['current_time'];
		} else {
			$input['last_run'] = isset( $this->settings['last_run'] ) && $this->settings['last_run'] > $itsec_globals['current_time'] - $interval ? $this->settings['last_run'] : ( $itsec_globals['current_time'] - $interval + 120 );
		}

		if ( is_multisite() ) {

			$this->core->show_network_admin_notice( false );

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

		if ( isset( $_POST['itsec_file_change'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_settings-options' ) ) {
				die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
			}

			update_site_option( 'itsec_file_change', $_POST['itsec_file_change'] ); //we must manually save network options

		}

	}

	/**
	 * Adds fields that will be tracked for Google Analytics
	 *
	 * @since 4.0
	 *
	 * @param array $vars tracking vars
	 *
	 * @return array tracking vars
	 */
	public function tracking_vars( $vars ) {

		$vars['itsec_file_change'] = array(
			'enabled' => '0:b',
			'method'  => '1:b',
			'email'   => '1:b',
		);

		return $vars;

	}

}