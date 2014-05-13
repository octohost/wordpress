<?php

class ITSEC_Brute_Force_Admin {

	private
		$settings,
		$core,
		$module,
		$module_path;

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

		$id    = 'brute_force_options';
		$title = __( 'Brute Force Protection', 'it-l10n-better-wp-security' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_brute_force_settings' ),
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
	 * Add Away mode Javascript
	 *
	 * @return void
	 */
	public function admin_script() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_settings' ) !== false ) {

			wp_enqueue_script( 'itsec_brute_force_js', $this->module_path . 'js/admin-brute-force.js', array( 'jquery' ), $itsec_globals['plugin_build'] );

		}

	}

	/**
	 * echos Check Period Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function brute_force_check_period() {

		if ( isset( $this->settings['check_period'] ) ) {
			$check_period = absint( $this->settings['check_period'] );
		} else {
			$check_period = 5;
		}

		$content = '<input class="small-text" name="itsec_brute_force[check_period]" id="itsec_brute_force_check_period" value="' . $check_period . '" type="text"> ';
		$content .= '<label for="itsec_brute_force_check_period"> ' . __( 'Minutes', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description"> ' . __( 'The number of minutes in which bad logins should be remembered.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Enable Brute Force Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function brute_force_enabled() {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		$content = '<input type="checkbox" id="itsec_brute_force_enabled" name="itsec_brute_force[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		$content .= '<label for="itsec_brute_force_enabled"> ' . __( 'Enable brute force protection.', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

	}

	/**
	 * echos Max Attempts per host  Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function brute_force_max_attempts_host() {

		if ( isset( $this->settings['max_attempts_host'] ) ) {
			$max_attempts_host = absint( $this->settings['max_attempts_host'] );
		} else {
			$max_attempts_host = 5;
		}

		$content = '<input class="small-text" name="itsec_brute_force[max_attempts_host]" id="itsec_brute_force_max_attempts_host" value="' . $max_attempts_host . '" type="text"> ';
		$content .= '<label for="itsec_brute_force_max_attempts_host"> ' . __( 'Attempts', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description"> ' . __( 'The number of login attempts a user has before their host or computer is locked out of the system. Set to 0 to record bad login attempts without locking out the host.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Max Attempts per user  Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function brute_force_max_attempts_user() {

		if ( isset( $this->settings['max_attempts_user'] ) ) {
			$max_attempts_user = absint( $this->settings['max_attempts_user'] );
		} else {
			$max_attempts_user = 10;
		}

		$content = '<input class="small-text" name="itsec_brute_force[max_attempts_user]" id="itsec_brute_force_max_attempts_user" value="' . $max_attempts_user . '" type="text"> ';
		$content .= '<label for="itsec_brute_force_max_attempts_user"> ' . __( 'Attempts', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description"> ' . __( 'The number of login attempts a user has before their username is locked out of the system. Note that this is different from hosts in case an attacker is using multiple computers. In addition, if they are using your login name you could be locked out yourself. Set to zero to log bad login attempts per user without ever locking the user out (this is not recommended)', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * Sets the status in the plugin dashboard
	 *
	 * @since 4.0
	 *
	 * @return array array of statuses
	 */
	public function dashboard_status( $statuses ) {

		if ( $this->settings['enabled'] === true ) {

			$status_array = 'safe-high';
			$status       = array( 'text' => __( 'Your login area is protected from brute force attacks.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_brute_force_enabled', );

		} else {

			$status_array = 'high';
			$status       = array( 'text' => __( 'Your login area is not protected from brute force attacks.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_brute_force_enabled', );

		}

		array_push( $statuses[$status_array], $status );

		return $statuses;

	}

	/**
	 * Empty callback function
	 */
	public function empty_callback_function() {
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

		$this->core        = $core;
		$this->module      = $module;
		$this->settings    = get_site_option( 'itsec_brute_force' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) ); //enqueue scripts for admin page
		add_filter( 'itsec_add_dashboard_status', array( $this, 'dashboard_status' ) ); //add information for plugin status
		add_filter( 'itsec_metaboxes', array( $this, 'register_logger_metaboxes' ) ); //adds logs metaboxes
		add_filter( 'itsec_tracking_vars', array( $this, 'tracking_vars' ) );
		add_filter( 'itsec_one_click_settings', array( $this, 'one_click_settings' ) );

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
			'brute_force-enabled',
			__( 'Enable Brute Force Protection', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		add_settings_section(
			'brute_force-settings',
			__( 'Brute Force Protection Settings', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		//Brute Force Protection Fields
		add_settings_field(
			'itsec_brute_force[enabled]',
			__( 'Brute Force Protection', 'it-l10n-better-wp-security' ),
			array( $this, 'brute_force_enabled' ),
			'security_page_toplevel_page_itsec_settings', 'brute_force-enabled'
		);

		add_settings_field(
			'itsec_brute_force[max_attempts_host]',
			__( 'Max Login Attempts Per Host', 'it-l10n-better-wp-security' ),
			array( $this, 'brute_force_max_attempts_host' ),
			'security_page_toplevel_page_itsec_settings', 'brute_force-settings'
		);

		add_settings_field(
			'itsec_brute_force[max_attempts_user]',
			__( 'Max Login Attempts Per User', 'it-l10n-better-wp-security' ),
			array( $this, 'brute_force_max_attempts_user' ),
			'security_page_toplevel_page_itsec_settings', 'brute_force-settings'
		);

		add_settings_field(
			'itsec_brute_force[check_period]',
			__( 'Minutes to Remember Bad Login (check period)', 'it-l10n-better-wp-security' ),
			array( $this, 'brute_force_check_period' ),
			'security_page_toplevel_page_itsec_settings', 'brute_force-settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_settings',
			'itsec_brute_force',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Render the settings metabox
	 *
	 * @return void
	 */
	public function logs_metabox() {

		if ( ! class_exists( 'ITSEC_Brute_Force_Log' ) ) {
			require( dirname( __FILE__ ) . '/class-itsec-brute-force-log.php' );
		}

		echo __( 'Below is the log of all the invalid login attempts in the WordPress Database. To adjust logging options visit the global settings page.', 'it-l10n-better-wp-security' );

		$log_display = new ITSEC_Brute_Force_Log();
		$log_display->prepare_items();
		$log_display->display();

	}

	/**
	 * Render the settings metabox
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_brute_force_settings() {

		global $itsec_lockout;

		echo '<p>' . __( 'If one had unlimited time and wanted to try an unlimited number of password combinations to get into your site they eventually would, right? This method of attack, known as a brute force attack, is something that WordPress is acutely susceptible by default as the system doesn\'t care how many attempts a user makes to login. It will always let you try again. Enabling login limits will ban the host user from attempting to login again after the specified bad login threshold has been reached.', 'it-l10n-better-wp-security' ) . '</p>';
		echo $itsec_lockout->get_lockout_description();

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'brute_force-enabled', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'brute_force-settings', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_settings' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-better-wp-security' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Register one-click settings
	 *
	 * @since 4.0
	 *
	 * @param array $one_click_settings array of one-click settings
	 *
	 * @return array array of one-click settings
	 */
	public function one_click_settings( $one_click_settings ) {

		$one_click_settings['itsec_brute_force'][] = array(
			'option' => 'enabled',
			'value'  => 1,
		);

		return $one_click_settings;

	}

	/**
	 * Array of metaboxes for the logs screen
	 *
	 * @since 4.0
	 *
	 * @param object $metaboxes metabox array
	 *
	 * @return array metabox array
	 */
	public function register_logger_metaboxes( $metaboxes ) {

		//Don't attempt to display logs if brute force isn't enabled
		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {

			$metaboxes[] = array(
				'module'   => 'brute_force',
				'title'    => __( 'Invalid Login Attempts', 'it-l10n-better-wp-security' ),
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

		//process brute force settings
		$input['enabled']           = ( isset( $input['enabled'] ) && intval( $input['enabled'] == 1 ) ? true : false );
		$input['max_attempts_host'] = isset( $input['max_attempts_host'] ) ? absint( $input['max_attempts_host'] ) : 5;
		$input['max_attempts_user'] = isset( $input['max_attempts_user'] ) ? absint( $input['max_attempts_user'] ) : 10;
		$input['check_period']      = isset( $input['check_period'] ) ? absint( $input['check_period'] ) : 5;

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

		if ( isset( $_POST['itsec_brute_force'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_settings-options' ) ) {
				die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
			}

			update_site_option( 'itsec_brute_force', $_POST['itsec_brute_force'] ); //we must manually save network options

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

		$vars['itsec_brute_force'] = array(
			'enabled' => '0:b',
		);

		return $vars;

	}

}