<?php

class ITSEC_Strong_Passwords_Admin {

	private
		$settings,
		$core,
		$module_path;

	function run( $core ) {

		if ( is_admin() ) {

			$this->initialize( $core );

		}

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 * @return void
	 */
	public function add_admin_meta_boxes() {

		$id    = 'strong_passwords_options';
		$title = __( 'Strong Passwords', 'it-l10n-better-wp-security' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_strong_passwords_settings' ),
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

			wp_enqueue_script( 'itsec_strong_passwords_js', $this->module_path . 'js/admin-strong-passwords.js', array( 'jquery' ), $itsec_globals['plugin_build'] );

		}

	}

	/**
	 * Sets the status in the plugin dashboard
	 *
	 * @since 4.0
	 *
	 * @return array array of statuses
	 */
	public function dashboard_status( $statuses ) {

		if ( $this->settings['enabled'] === true && $this->settings['roll'] == 'subscriber' ) {

			$status_array = 'safe-high';
			$status       = array( 'text' => __( 'You are enforcing strong passwords for all users.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_strong_passwords_enabled', );

		} elseif ( $this->settings['enabled'] === true ) {

			$status_array = 'low';
			$status       = array( 'text' => __( 'You are enforcing strong passwords, but not for all users.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_strong_passwords_enabled', );

		} else {

			$status_array = 'high';
			$status       = array( 'text' => __( 'You are not enforcing strong passwords for any users.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_strong_passwords_enabled', );

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
	private function initialize( $core ) {

		$this->core        = $core;
		$this->settings    = get_site_option( 'itsec_strong_passwords' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) ); //enqueue scripts for admin page
		add_filter( 'itsec_add_dashboard_status', array( $this, 'dashboard_status' ) ); //add information for plugin status
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
			'strong_passwords-enabled',
			__( 'Enforce Strong Passwords', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		add_settings_section(
			'strong_passwords-settings',
			__( 'Enforce Strong Passwords', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		//Strong Passwords Fields
		add_settings_field(
			'itsec_strong_passwords[enabled]',
			__( 'Strong Passwords', 'it-l10n-better-wp-security' ),
			array( $this, 'strong_passwords_enabled' ),
			'security_page_toplevel_page_itsec_settings',
			'strong_passwords-enabled'
		);

		add_settings_field(
			'itsec_strong_passwords[roll]',
			__( 'Select Role for Strong Passwords', 'it-l10n-better-wp-security' ),
			array( $this, 'strong_passwords_role' ),
			'security_page_toplevel_page_itsec_settings',
			'strong_passwords-settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_settings',
			'itsec_strong_passwords',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Render the settings metabox
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_strong_passwords_settings() {

		echo '<p>' . __( 'Force users to use strong passwords as rated by the WordPress password meter.', 'it-l10n-better-wp-security' ) . '</p>';

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'strong_passwords-enabled', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'strong_passwords-settings', false );

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

		$one_click_settings['itsec_strong_passwords'][] = array(
			'option' => 'enabled',
			'value'  => 1,
		);

		return $one_click_settings;

	}

	/**
	 * Sanitize and validate input
	 *
	 * @param  Array $input array of input fields
	 *
	 * @return Array         Sanitized array
	 */
	public function sanitize_module_input( $input ) {

		//process strong passwords settings
		$input['enabled'] = ( isset( $input['enabled'] ) && intval( $input['enabled'] == 1 ) ? true : false );
		if ( isset( $input['roll'] ) && ctype_alpha( wp_strip_all_tags( $input['roll'] ) ) ) {
			$input['roll'] = wp_strip_all_tags( $input['roll'] );
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

		if ( isset( $_POST['itsec_strong_passwords'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_settings-options' ) ) {
				die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
			}

			update_site_option( 'itsec_strong_passwords', $_POST['itsec_strong_passwords'] ); //we must manually save network options

		}

	}

	/**
	 * echos Enable Strong Passwords Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function strong_passwords_enabled() {

		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		$content = '<input type="checkbox" id="itsec_strong_passwords_enabled" name="itsec_strong_passwords[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		$content .= '<label for="itsec_strong_passwords_enabled"> ' . __( 'Enable strong password enforcement.', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

	}

	/**
	 * echos Strong Passwords Role Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function strong_passwords_role() {

		if ( isset( $this->settings['roll'] ) ) {
			$roll = $this->settings['roll'];
		} else {
			$roll = 'administrator';
		}

		$content = '<select name="itsec_strong_passwords[roll]" id="itsec_strong_passwords_roll">';
		$content .= '<option value="administrator" ' . selected( $roll, 'administrator', false ) . '>' . translate_user_role( 'Administrator' ) . '</option>';
		$content .= '<option value="editor" ' . selected( $roll, 'editor', false ) . '>' . translate_user_role( 'Editor' ) . '</option>';
		$content .= '<option value="author" ' . selected( $roll, 'author', false ) . '>' . translate_user_role( 'Author' ) . '</option>';
		$content .= '<option value="contributor" ' . selected( $roll, 'contributor', false ) . '>' . translate_user_role( 'Contributor' ) . '</option>';
		$content .= '<option value="subscriber" ' . selected( $roll, 'subscriber', false ) . '>' . translate_user_role( 'Subscriber' ) . '</option>';
		$content .= '</select><br>';
		$content .= '<label for="itsec_strong_passwords_roll"> ' . __( 'Minimum role at which a user must choose a strong password.' ) . '</label>';

		$content .= '<p class="description"> ' . __( 'For more information on WordPress roles and capabilities please see', 'it-l10n-better-wp-security' ) . ' <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">http://codex.wordpress.org/Roles_and_Capabilities</a>.</p>';
		$content .= '<p class="warningtext description">' . __( 'Warning: If your site invites public registrations setting the role too low may annoy your members.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

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

		$vars['itsec_strong_passwords'] = array(
			'enabled' => '0:b',
			'roll'    => 'administrator:s',
		);

		return $vars;

	}

}