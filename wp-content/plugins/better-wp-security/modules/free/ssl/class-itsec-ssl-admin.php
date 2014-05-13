<?php

class ITSEC_SSL_Admin {

	private
		$settings,
		$core,
		$module,
		$module_path,
		$has_ssl;

	function run( $core, $module ) {

		if ( is_admin() ) {

			$this->initialize( $core, $module );

		}

	}

	/**
	 * Add checkbox to post meta for SSL
	 *
	 * @return void
	 */
	function ssl_enable_per_content() {

		global $post;

		wp_nonce_field( 'ITSEC_Admin_Save', 'wp_nonce' );

		$enabled = false;

		if ( $post->ID ) {
			$enabled = get_post_meta( $post->ID, 'itsec_enable_ssl', true );
		}

		$content = '<div id="itsec" class="misc-pub-section">';
		$content .= '<label for="enable_ssl">Enable SSL:</label> ';
		$content .= '<input type="checkbox" value="1" name="enable_ssl" id="enable_ssl"' . checked( 1, $enabled, false ) . ' />';
		$content .= '</div>';

		echo $content;

	}

	/**
	 * Save post meta for SSL selection
	 *
	 * @param  int $id post id
	 *
	 * @return bool        value of itsec_enable_ssl
	 */
	function save_post( $id ) {

		if ( isset( $_POST['wp_nonce'] ) ) {

			if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'ITSEC_Admin_Save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( $_POST['post_type'] == 'page' && ! current_user_can( 'edit_page', $id ) ) || ( $_POST['post_type'] == 'post' && ! current_user_can( 'edit_post', $id ) ) ) {
				return $id;
			}

			$itsec_enable_ssl = ( ( isset( $_POST['enable_ssl'] ) && $_POST['enable_ssl'] == true ) ? true : false );

			if ( $itsec_enable_ssl ) {
				update_post_meta( $id, 'itsec_enable_ssl', true );
			} else {
				delete_post_meta( $id, 'itsec_enable_ssl' );
			}

			return $itsec_enable_ssl;

		}

		return false;

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 * @param array $available_pages array of available page_hooks
	 */
	public function add_admin_meta_boxes() {

		$id    = 'ssl_options';
		$title = __( 'Secure Socket Layers (SSL)', 'it-l10n-better-wp-security' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_advanced_settings' ),
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
	 * Add SSL Javascript
	 *
	 * @return void
	 */
	public function admin_script() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_settings' ) !== false ) {

			wp_enqueue_script( 'itsec_ssl_js', $this->module_path . 'js/admin-ssl.js', array( 'jquery' ), $itsec_globals['plugin_build'] );

			//make sure the text of the warning is translatable
			wp_localize_script( 'itsec_ssl_js', 'ssl_warning_text', array( 'text' => __( 'Are you sure you want to enable SSL? If your server does not support SSL you will be locked out of your WordPress Dashboard.', 'it-l10n-better-wp-security' ) ) );

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

		if ( FORCE_SSL_LOGIN === true && FORCE_SSL_ADMIN === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'You are requiring a secure connection for logins and the admin area.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_ssl_login', );

		} elseif ( FORCE_SSL_LOGIN === true || FORCE_SSL_ADMIN === true ) {

			$status_array = 'low';
			$status       = array( 'text' => __( 'You are requiring a secure connection for logins or the admin area but not both.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_ssl_login', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'You are not requiring a secure connection for logins or for the admin area.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_ssl_login', );

		}

		array_push( $statuses[$status_array], $status );

		return $statuses;

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
		$this->settings    = get_site_option( 'itsec_ssl' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_filter( 'itsec_file_modules', array( $this, 'register_file' ) ); //register tooltip action
		add_action( 'current_screen', array( $this, 'plugin_init' ) );
		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area
		add_filter( 'itsec_add_dashboard_status', array( $this, 'dashboard_status' ) ); //add information for plugin status
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) ); //enqueue scripts for admin page
		add_filter( 'itsec_tracking_vars', array( $this, 'tracking_vars' ) );

		//manually save options on multisite
		if ( is_multisite() ) {
			add_action( 'itsec_admin_init', array( $this, 'save_network_options' ) ); //save multisite options
		}

		if ( isset( $this->settings['frontend'] ) && $this->settings['frontend'] == 1 ) {

			add_action( 'post_submitbox_misc_actions', array( $this, 'ssl_enable_per_content' ) );
			add_action( 'save_post', array( $this, 'save_post' ) );

		}

	}

	/**
	 * Execute admin initializations.
	 *
	 * Adds settings fields and tries to determine whether SSL is even possible.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function initialize_admin() {

		//primary settings section
		add_settings_section(
			'ssl_settings',
			__( 'Configure SSL', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		//enabled field
		add_settings_field(
			'itsec_ssl[frontend]',
			__( 'Front End SSL Mode', 'it-l10n-better-wp-security' ),
			array( $this, 'ssl_frontend' ),
			'security_page_toplevel_page_itsec_settings',
			'ssl_settings'
		);

		//enabled field
		add_settings_field(
			'itsec_ssl[login]',
			__( 'SSL for Login', 'it-l10n-better-wp-security' ),
			array( $this, 'ssl_login' ),
			'security_page_toplevel_page_itsec_settings',
			'ssl_settings'
		);

		//enabled field
		add_settings_field(
			'itsec_ssl[admin]',
			__( 'SSL for Dashboard', 'it-l10n-better-wp-security' ),
			array( $this, 'ssl_admin' ),
			'security_page_toplevel_page_itsec_settings',
			'ssl_settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_settings',
			'itsec_ssl',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Empty callback function
	 */
	public function empty_callback_function() {
	}

	/**
	 * echos front end Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function ssl_frontend() {

		if ( isset( $this->settings['frontend'] ) ) {
			$frontend = $this->settings['frontend'];
		} else {
			$frontend = 0;
		}

		echo '<select id="itsec_ssl_frontend" name="itsec_ssl[frontend]">';

		echo '<option value="0" ' . selected( $frontend, '0' ) . '>' . __( 'Off', 'it-l10n-better-wp-security' ) . '</option>';
		echo '<option value="1" ' . selected( $frontend, '1' ) . '>' . __( 'Per Content', 'it-l10n-better-wp-security' ) . '</option>';
		echo '<option value="2" ' . selected( $frontend, '2' ) . '>' . __( 'Whole Site', 'it-l10n-better-wp-security' ) . '</option>';
		echo '</select><br />';
		echo '<label for="itsec_ssl_frontend"> ' . __( 'Front End SSL Mode', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description">' . __( 'Enables secure SSL connection for the front-end (public parts of your site). Turning this off will disable front-end SSL control, turning this on "Per Content" will place a checkbox on the edit page for all posts and pages (near the publish settings) allowing you to turn on SSL for selected pages or posts, and selecting "Whole Site" will force the whole site to use SSL (not recommended unless you have a really good reason to use it' ) . '</p>';

	}

	/**
	 * echos login Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function ssl_login() {

		if ( isset( $this->settings['login'] ) && $this->settings['login'] === true ) {
			$login = 1;
		} else {
			$login = 0;
		}

		$content = '<input onchange="forcessl()" type="checkbox" id="itsec_ssl_login" name="itsec_ssl[login]" value="1" ' . checked( 1, $login, false ) . '/>';
		$content .= '<label for="itsec_ssl_login">' . __( 'Force SSL for Login', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Forces all logins to be served only over a secure SSL connection.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos admin Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function ssl_admin() {

		if ( isset( $this->settings['admin'] ) && $this->settings['admin'] === true ) {
			$admin = 1;
		} else {
			$admin = 0;
		}

		$content = '<input onchange="forcessl()" type="checkbox" id="itsec_ssl_admin" name="itsec_ssl[admin]" value="1" ' . checked( 1, $admin, false ) . '/>';
		$content .= '<label for="itsec_ssl_admin">' . __( 'Force SSL for Dashboard', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Forces all logins to be served only over a secure SSL connection.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * Render the settings metabox
	 *
	 * @return void
	 */
	public function metabox_advanced_settings() {

		$content = '<p>' . __( 'Secure Socket Layers (SSL) is a technology that is used to encrypt the data sent between your server or host and a visitor to your web page. When SSL is activated, it makes it almost impossible for an attacker to intercept data in transit, therefore making the transmission of form, password or other encrypted data much safer.', 'it-l10n-better-wp-security' ) . '</p>';
		$content .= '<p>' . __( 'This plugin gives you the option of turning on SSL (if your server or host supports it) for all or part of your site. The options below allow you to automatically use SSL for major parts of your site such as the login page, the admin dashboard or the site as a whole. You can also turn on SSL for any post or page by editing the content and selecting "Enable SSL" in the publishing options of the content in question.', 'it-l10n-better-wp-security' ) . '</p>';
		$content .= '<p>' . __( 'Note: While this plugin does give you the option of encrypting everything, SSL may not be for you. SSL does add overhead to your site which will increase download times slightly. Therefore we recommend you enable SSL at a minimum on the login page, then on the whole admin section and finally on individual pages or posts with forms that require sensitive information.', 'it-l10n-better-wp-security' ) . '</p>';

		if ( $this->has_ssl === false ) {

			$content .= sprintf( '<div class="itsec-warning-message"><span>%s: </span>%s</div>', __( 'WARNING', 'it-l10n-better-wp-security' ), __( 'Your server does not appear to support SSL. Your server MUST support SSL to use these features. Using these features without SSL support on your server or host will cause some or all of your site to become unavailable.', 'it-l10n-better-wp-security' ) );

		} else {

			$content .= sprintf( '<div class="itsec-notice-message"><span>%s: </span>%s</div>', __( 'WARNING', 'it-l10n-better-wp-security' ), __( 'Your server does appear to support SSL. Using these features without SSL support on your server or host will cause some or all of your site to become unavailable.', 'it-l10n-better-wp-security' ) );

		}

		$content .= '<p>' . __( 'Note: When turning SSL on you will be logged out and you will have to log back in. This is to prevent possible cookie conflicts that could make it more difficult to get in otherwise.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'ssl_settings', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_settings' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-better-wp-security' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Build wp-config.php rules
	 *
	 * @since 4.0
	 *
	 * @param  array $input        options to build rules from
	 * @param bool   $deactivation whether or not we're deactivating
	 *
	 * @return array         rules to write
	 */
	public static function build_wpconfig_rules( $input = null, $deactivation = false ) {

		//Return options to default on deactivation
		if ( $deactivation === true || ( isset( $_GET['action'] ) && $_GET['action'] == 'deactivate' ) ) {

			$input        = array();
			$deactivating = true;
			$initials     = get_site_option( 'itsec_initials' );

			if ( isset( $initials['login'] ) && $initials['login'] === false && defined( 'FORCE_SSL_LOGIN' ) && FORCE_SSL_LOGIN === true ) { //initially off, now on

				$input['login'] = false;

			} elseif ( isset( $initials['login'] ) && $initials['login'] === true && ( ! defined( 'FORCE_SSL_LOGIN' ) || FORCE_SSL_LOGIN === false ) ) { //initially on, now off

				$input['login'] = true;

			} elseif ( defined( 'FORCE_SSL_LOGIN' ) && FORCE_SSL_LOGIN === true ) { //no initial state, now on

				$input['login'] = true;

			} else { //no initial state or other info. Set off

				$input['login'] = false;

			}

			if ( isset( $initials['admin'] ) && $initials['admin'] === false && defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN === true ) { //initially off, now on

				$input['admin'] = false;

			} elseif ( isset( $initials['admin'] ) && $initials['admin'] === true && ( ! defined( 'FORCE_SSL_ADMIN' ) || FORCE_SSL_ADMIN === false ) ) { //initially on, now off

				$input['admin'] = true;

			} elseif ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN === true ) { //no initial state, now on

				$input['admin'] = true;

			} else { //no initial state or other info. Set off

				$input['admin'] = false;

			}

		} else {

			$deactivating = false;

			//Get the rules from the database if input wasn't sent
			if ( $input === null ) {
				$input = get_site_option( 'itsec_ssl' );
			}

		}

		if ( $input['login'] == true ) {

			$rules[] = array( 'type' => 'add', 'search_text' => 'FORCE_SSL_LOGIN', 'rule' => "define( 'FORCE_SSL_LOGIN', true );", );

			$has_login = true;

		} else {

			$rules[] = array( 'type' => 'delete', 'search_text' => 'FORCE_SSL_LOGIN', 'rule' => false, );

			$has_login = false;

		}

		if ( $input['admin'] == true ) {

			$rules[] = array( 'type' => 'add', 'search_text' => 'FORCE_SSL_ADMIN', 'rule' => "define( 'FORCE_SSL_ADMIN', true );", );

			$has_admin = true;

		} else {

			$rules[] = array( 'type' => 'delete', 'search_text' => 'FORCE_SSL_ADMIN', 'rule' => false, );

			$has_admin = false;

		}

		if ( ( $has_login === false && $has_admin == false ) || $deactivating === true ) {

			$comment = array( 'type' => 'delete', 'search_text' => '//The entries below were created by iThemes Security to enforce SSL', 'rule' => false, );

		} else {

			$comment = array( 'type' => 'add', 'search_text' => '//The entries below were created by iThemes Security to enforce SSL', 'rule' => '//The entries below were created by iThemes Security to enforce SSL', );

		}

		array_unshift( $rules, $comment );

		return array( 'type' => 'wpconfig', 'name' => 'SSL', 'rules' => $rules, );

	}

	/**
	 * Calls lib function to determine whether ssl is available.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function plugin_init() {

		if ( isset( get_current_screen()->id ) && strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_settings' ) !== false ) {

			$this->has_ssl = ITSEC_Lib::get_ssl();

		}

	}

	/**
	 * Register ban users for file writer
	 *
	 * @param  array $file_modules array of file writer modules
	 *
	 * @return array                   array of file writer modules
	 */
	public function register_file( $file_modules ) {

		$file_modules['ssl'] = array(
			'config' => array( $this, 'save_config_rules' ),
		);

		return $file_modules;

	}

	/**
	 * Sanitize and validate input
	 *
	 * @param  Array $input array of input fields
	 *
	 * @return Array         Sanitized array
	 */
	public function sanitize_module_input( $input ) {

		$input['frontend'] = isset( $input['frontend'] ) ? intval( $input['frontend'] ) : 0;
		$input['login']    = ( isset( $input['login'] ) && intval( $input['login'] == 1 ) ? true : false );
		$input['admin']    = ( isset( $input['admin'] ) && intval( $input['admin'] == 1 ) ? true : false );

		if ( $input['login'] !== $this->settings['login'] || $input['admin'] !== $this->settings['admin'] ) {

			add_site_option( 'itsec_config_changed', true );

			if ( $input['admin'] === true || $input['admin'] === true ) {
				add_site_option( 'itsec_clear_login', true );
			}

		}

		if ( is_multisite() ) {

			$this->settings = $input;

		}

		return $input;

	}

	/**
	 * Saves rewrite rules to file writer.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	public function save_config_rules() {

		global $itsec_files;

		$config_rules = $itsec_files->get_config_rules();

		foreach ( $config_rules as $key => $rule ) {

			if ( isset( $rule['name'] ) && $rule['name'] == 'SSL' ) {
				unset ( $config_rules[$key] );
			}

		}

		$config_rules[] = $this->build_wpconfig_rules();

		$itsec_files->set_config_rules( $config_rules );

	}

	/**
	 * Prepare and save options in network settings
	 *
	 * @return void
	 */
	public function save_network_options() {

		if ( isset( $_POST['itsec_ssl'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_settings-options' ) ) {
				die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
			}

			update_site_option( 'itsec_ssl', $_POST['itsec_ssl'] ); //we must manually save network options

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

		$vars['itsec_ssl'] = array(
			'login'    => '0:b',
			'admin'    => '0:b',
			'frontend' => '0:s',
		);

		return $vars;

	}

}