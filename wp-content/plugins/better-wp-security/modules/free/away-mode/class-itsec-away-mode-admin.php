<?php

class ITSEC_Away_Mode_Admin {

	private
		$settings,
		$core,
		$module,
		$away_file,
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

		$id    = 'away_mode_options';
		$title = __( 'Away Mode', 'it-l10n-better-wp-security' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_away_mode_settings' ),
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

			wp_enqueue_script( 'itsec_away_mode_js', $this->module_path . 'js/admin-away-mode.js', array( 'jquery' ), $itsec_globals['plugin_build'] );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-datepicker-style', $this->module_path . 'css/smoothness/jquery-ui-1.10.4.custom.css' );

		}

	}

	/**
	 * echos Enable Away Mode Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function away_mode_enabled() {

		//disable the option if away mode is in the past
		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true && ( $this->settings['type'] == 1 || ( $this->settings['end'] > current_time( 'timestamp' ) || $this->settings['type'] === 2 ) ) ) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		$content = '<input type="checkbox" id="itsec_away_mode_enabled" name="itsec_away_mode[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		$content .= '<label for="itsec_away_mode_enabled"> ' . __( 'Enable away mode', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

	}

	/**
	 * echos End date field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function away_mode_end_date() {

		$current = current_time( 'timestamp' ); //The current time

		//if saved date is in the past update it to something in the future
		if ( isset( $this->settings['end'] ) && isset( $this->settings['enabled'] ) && $current < $this->settings['end'] ) {
			$end = $this->settings['end'];
		} else {
			$end = strtotime( date( 'n/j/y 12:00 \a\m', ( current_time( 'timestamp' ) + ( 86400 * 2 ) ) ) );
		}

		//Date Field
		$content = '<input class="end_date_field" type="text" id="itsec_away_mode_end_date" name="itsec_away_mode[away_end][date]" value="' . date( 'm/d/y', $end ) . '"/><br>';
		$content .= '<label class="end_date_field" for="itsec_away_mode_end_date"> ' . __( 'Set the date at which the admin dashboard should become available', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

	}

	/**
	 * echos End time field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function away_mode_end_time() {

		$current = current_time( 'timestamp' ); //The current time

		//if saved date is in the past update it to something in the future
		if ( isset( $this->settings['end'] ) && isset( $this->settings['enabled'] ) && $current < $this->settings['end'] ) {
			$end = $this->settings['end'];
		} else {
			$end = strtotime( date( 'n/j/y 6:00 \a\m', ( current_time( 'timestamp' ) + ( 86400 * 2 ) ) ) );
		}

		//Hour Field
		$content = '<select name="itsec_away_mode[away_end][hour]" id="itsec_away_mode_away_mod_end_time">';

		for ( $i = 1; $i <= 12; $i ++ ) {
			$content .= '<option value="' . sprintf( '%02d', $i ) . '" ' . selected( date( 'g', $end ), $i, false ) . '>' . $i . '</option>';
		}

		$content .= '</select>';

		//Minute Field
		$content .= '<select name="itsec_away_mode[away_end][minute]" id="itsec_away_mode_away_mod_end_time">';

		for ( $i = 0; $i <= 59; $i ++ ) {

			$content .= '<option value="' . sprintf( '%02d', $i ) . '" ' . selected( date( 'i', $end ), sprintf( '%02d', $i ), false ) . '>' . sprintf( '%02d', $i ) . '</option>';
		}

		$content .= '</select>';

		//AM/PM Field
		$content .= '<select name="itsec_away_mode[away_end][sel]" id="itsec_away_mode">';
		$content .= '<option value="am" ' . selected( date( 'a', $end ), 'am', false ) . '>' . __( 'am', 'it-l10n-better-wp-security' ) . '</option>';
		$content .= '<option value="pm" ' . selected( date( 'a', $end ), 'pm', false ) . '>' . __( 'pm', 'it-l10n-better-wp-security' ) . '</option>';
		$content .= '</select><br>';
		$content .= '<label for="itsec_away_mode_away_mod_end_time"> ' . __( 'Set the time at which the admin dashboard should become available again.', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

	}

	/**
	 * echos Start date field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function away_mode_start_date() {

		$current = current_time( 'timestamp' ); //The current time

		//if saved date is in the past update it to something in the future
		if ( isset( $this->settings['start'] ) && isset( $this->settings['enabled'] ) && $current < $this->settings['end'] ) {
			$start = $this->settings['start'];
		} else {
			$start = strtotime( date( 'n/j/y 12:00 \a\m', ( current_time( 'timestamp' ) + ( 86400 ) ) ) );
		}

		//Date Field
		$content = '<input class="start_date_field" type="text" id="itsec_away_mode_start_date" name="itsec_away_mode[away_start][date]" value="' . date( 'm/d/y', $start ) . '"/><br>';
		$content .= '<label class="start_date_field" for="itsec_away_mode_start_date"> ' . __( 'Set the date at which the admin dashboard should become unavailable', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

	}

	/**
	 * echos Start time field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function away_mode_start_time() {

		$current = current_time( 'timestamp' ); //The current time

		//if saved date is in the past update it to something in the future
		if ( isset( $this->settings['start'] ) && isset( $this->settings['enabled'] ) && $current < $this->settings['end'] ) {
			$start = $this->settings['start'];
		} else {
			$start = strtotime( date( 'n/j/y 12:00 \a\m', ( current_time( 'timestamp' ) + ( 86400 ) ) ) );
		}

		//Hour Field
		$content = '<select name="itsec_away_mode[away_start][hour]" id="itsec_away_mode_away_mod_start_time">';

		for ( $i = 1; $i <= 12; $i ++ ) {
			$content .= '<option value="' . sprintf( '%02d', $i ) . '" ' . selected( date( 'g', $start ), $i, false ) . '>' . $i . '</option>';
		}

		$content .= '</select>';

		//Minute Field
		$content .= '<select name="itsec_away_mode[away_start][minute]" id="itsec_away_mode_away_mod_start_time">';

		for ( $i = 0; $i <= 59; $i ++ ) {

			$content .= '<option value="' . sprintf( '%02d', $i ) . '" ' . selected( date( 'i', $start ), sprintf( '%02d', $i ), false ) . '>' . sprintf( '%02d', $i ) . '</option>';
		}

		$content .= '</select>';

		//AM/PM Field
		$content .= '<select name="itsec_away_mode[away_start][sel]" id="itsec_away_mode_away_mod_start_time">';
		$content .= '<option value="am" ' . selected( date( 'a', $start ), 'am', false ) . '>' . __( 'am', 'it-l10n-better-wp-security' ) . '</option>';
		$content .= '<option value="pm" ' . selected( date( 'a', $start ), 'pm', false ) . '>' . __( 'pm', 'it-l10n-better-wp-security' ) . '</option>';
		$content .= '</select><br>';
		$content .= '<label for="itsec_away_mode_away_mod_start_time"> ' . __( 'Set the time at which the admin dashboard should become unavailable.', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

	}

	/**
	 * echos type Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function away_mode_type() {

		$content = '<select name="itsec_away_mode[type]" id="itsec_away_mode_type">';
		$content .= '<option value="1" ' . selected( $this->settings['type'], 1, false ) . '>' . __( 'Daily', 'it-l10n-better-wp-security' ) . '</option>';
		$content .= '<option value="2" ' . selected( $this->settings['type'], 2, false ) . '>' . __( 'One Time', 'it-l10n-better-wp-security' ) . '</option>';
		$content .= '</select><br>';
		$content .= '<label for="itsec_away_mode_type"> ' . __( 'Select the type of restriction you would like to enable', 'it-l10n-better-wp-security' ) . '</label>';

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

			$status_array = 'safe-medium';
			$status       = array( 'text' => __( 'Away Mode is enabled and your WordPress Dashboard is not available when you will not be needing it.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_away_mode_enabled', );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'Your WordPress Dashboard is available 24/7. Do you really update 24 hours a day? Consider using Away Mode.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_away_mode_enabled', );

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

		global $itsec_globals;

		$this->core        = $core;
		$this->module      = $module;
		$this->settings    = get_site_option( 'itsec_away_mode' );
		$this->away_file   = $itsec_globals['ithemes_dir'] . '/itsec_away.confg'; //override file
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) ); //enqueue scripts for admin page
		add_filter( 'itsec_add_dashboard_status', array( $this, 'dashboard_status' ) ); //add information for plugin status
		add_filter( 'itsec_tracking_vars', array( $this, 'tracking_vars' ) );

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
			'away_mode-enabled',
			__( 'Away Mode', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		add_settings_section(
			'away_mode-settings',
			__( 'Away Mode', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		//Away Mode Fields
		add_settings_field(
			'itsec_away_mode[enabled]',
			__( 'Away Mode', 'it-l10n-better-wp-security' ),
			array( $this, 'away_mode_enabled' ),
			'security_page_toplevel_page_itsec_settings',
			'away_mode-enabled'
		);

		add_settings_field(
			'itsec_away_mode[type]',
			__( 'Type of Restriction', 'it-l10n-better-wp-security' ),
			array( $this, 'away_mode_type' ),
			'security_page_toplevel_page_itsec_settings',
			'away_mode-settings'
		);

		add_settings_field(
			'itsec_away_mode[start_date]', __( 'Start Date', 'it-l10n-better-wp-security' ),
			array( $this, 'away_mode_start_date' ),
			'security_page_toplevel_page_itsec_settings',
			'away_mode-settings'
		);

		add_settings_field(
			'itsec_away_mode[start_time]', __( 'Start Time', 'it-l10n-better-wp-security' ),
			array( $this, 'away_mode_start_time' ),
			'security_page_toplevel_page_itsec_settings',
			'away_mode-settings'
		);

		add_settings_field(
			'itsec_away_mode[end_date]',
			__( 'End Date', 'it-l10n-better-wp-security' ),
			array( $this, 'away_mode_end_date' ),
			'security_page_toplevel_page_itsec_settings',
			'away_mode-settings'
		);

		add_settings_field(
			'itsec_away_mode[end_time]',
			__( 'End Time', 'it-l10n-better-wp-security' ),
			array( $this, 'away_mode_end_time' ),
			'security_page_toplevel_page_itsec_settings',
			'away_mode-settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_settings',
			'itsec_away_mode',
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
	public function metabox_away_mode_settings() {

		$content = '<p>' . __( 'As most sites are only updated at certain times of the day it is not always necessary to provide access to the WordPress dashboard 24 hours a day, 7 days a week. The options below will allow you to disable access to the WordPress Dashboard for the specified period. In addition to limiting exposure to attackers this could also be useful to disable site access based on a schedule for classroom or other reasons.', 'it-l10n-better-wp-security' ) . '</p>';

		if ( preg_match( "/^(G|H)(:| \\h)/", get_option( 'time_format' ) ) ) {
			$currdate = date_i18n( 'l, d F Y' . ' ' . get_option( 'time_format' ), current_time( 'timestamp' ) );
		} else {
			$currdate = date( 'g:i a \o\n l F jS, Y', current_time( 'timestamp' ) );
		}

		$content .= '<p>' . sprintf( __( 'Please note that according to your %sWordPress timezone settings%s your current time is:', 'it-l10n-better-wp-security' ), '<a href="options-general.php">', '</a>' );
		$content .= '<div class="current-time-date">' . $currdate . '</div>';
		$content .= '<p>' . sprintf( __( 'If this is incorrect please correct it on the %sWordPress general settings page%s by setting the appropriate time zone. Failure to set the correct timezone may result in unintended lockouts.', 'it-l10n-better-wp-security' ), '<a href="options-general.php">', '</a>' ) . '</p>';

		echo $content;

		//set information explaining away mode is enabled
		if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === 1 && ( $this->settings['type'] === 1 || ( $this->settings['end'] > current_time( 'timestamp' ) ) ) ) {

			$content = '<hr />';

			$content .= sprintf( '<p><strong>%s</strong></p>', __( 'Away mode is currently enabled.', 'it-l10n-better-wp-security' ) );

			//Create the appropriate notification based on daily or one time use
			if ( $this->settings['type'] === 1 ) {

				$content .= sprintf( '<p>' . __( 'The dashboard of this website will become unavailable %s%s%s from %s%s%s until %s%s%s.', 'it-l10n-better-wp-security' ) . '</p>', '<strong>', __( 'every day', 'it-l10n-better-wp-security' ), '</strong>', '<strong>', date_i18n( get_option( 'time_format' ), $this->settings['start'] ), '</strong>', '<strong>', date_i18n( get_option( 'time_format' ), $this->settings['end'] ), '</strong>' );

			} else {

				$content .= sprintf( '<p>' . __( 'The dashboard of this website will become unavailable from %s%s%s on %s%s%s until %s%s%s on %s%s%s.', 'it-l10n-better-wp-security' ) . '</p>', '<strong>', date_i18n( get_option( 'time_format' ), $this->settings['start'] ), '</strong>', '<strong>', date_i18n( get_option( 'date_format' ), $this->settings['start'] ), '</strong>', '<strong>', date_i18n( get_option( 'time_format' ), $this->settings['end'] ), '</strong>', '<strong>', date_i18n( get_option( 'date_format' ), $this->settings['end'] ), '</strong>' );

			}

			$content .= '<p>' . __( 'You will not be able to log into this website when the site is unavailable.', 'it-l10n-better-wp-security' ) . '</p>';

			echo $content;
		}

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'away_mode-enabled', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'away_mode-settings', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_settings' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-better-wp-security' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Sanitize and validate input
	 *
	 * @param Array $input array of input fields
	 *
	 * @return Array         Sanitized array
	 */
	public function sanitize_module_input( $input ) {

		//process away mode settings
		$input['enabled'] = ( isset( $input['enabled'] ) && intval( $input['enabled'] == 1 ) ? true : false );
		$input['type']    = ( isset( $input['type'] ) && intval( $input['type'] == 1 ) ? 1 : 2 );

		if ( ! isset( $input['away_start'] ) ) {

			$input['start'] = $this->settings['start'];

		} else {

			$input['start'] = strtotime( $input['away_start']['date'] . ' ' . $input['away_start']['hour'] . ':' . $input['away_start']['minute'] . ' ' . $input['away_start']['sel'] );
			unset( $input['away_start'] );

		}

		if ( ! isset( $input['away_end'] ) ) {

			$input['end'] = $this->settings['end'];

		} else {

			$input['end'] = strtotime( $input['away_end']['date'] . ' ' . $input['away_end']['hour'] . ':' . $input['away_end']['minute'] . ' ' . $input['away_end']['sel'] );
			unset( $input['away_end'] );

		}

		if ( $input['enabled'] === true && $this->module->check_away( $input ) === true ) {

			$input['enabled'] = false; //disable away mode

			$type    = 'error';
			$message = __( 'Invalid  away mode time listed. The time entered would lock you out of your site now. Please try again.', 'it-l10n-better-wp-security' );

			add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

		}

		if ( $input['enabled'] === true && $input['type'] === 2 && $input['end'] < $input['start'] ) {

			$input['enabled'] = false; //disable away mode

			$type    = 'error';
			$message = __( 'Invalid  away mode time listed. The start time selected is after the end time selected.', 'it-l10n-better-wp-security' );

			add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

		}

		if ( $input['enabled'] === true && $input['type'] === 2 && $input['end'] < current_time( 'timestamp' ) ) {

			$input['enabled'] = false; //disable away mode

			$type    = 'error';
			$message = __( 'Invalid away mode time listed. The period selected already ended.', 'it-l10n-better-wp-security' );

			add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

		}

		if ( $input['enabled'] === true && ! file_exists( $this->away_file ) ) {

			@file_put_contents( $this->away_file, 'true' );

		} elseif ( $input['enabled'] === false ) {

			@unlink( $this->away_file );

		}

		//process other settings

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

		if ( isset( $_POST['itsec_away_mode'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_settings-options' ) ) {
				die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
			}

			update_site_option( 'itsec_away_mode', $_POST['itsec_away_mode'] ); //we must manually save network options

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

		$vars['itsec_away_mode'] = array(
			'enabled' => '0:b',
			'type'    => '0:b',
		);

		return $vars;

	}

}
