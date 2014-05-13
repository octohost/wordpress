<?php

class ITSEC_Hide_Backend_Admin {

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

		$id    = 'hide_backend_options';
		$title = __( 'Hide Login Area', 'it-l10n-better-wp-security' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_hide_backend_settings' ),
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

			$new_slug = get_site_option( 'itsec_hide_backend_new_slug' );

			if ( $new_slug !== false ) {

				delete_site_option( 'itsec_hide_backend_new_slug' );

				$new_slug = get_site_url() . '/' . $new_slug;

				$slug_text = sprintf(
					'%s%s%s%s%s',
					__( 'Warning: Your admin URL has changed. Use the following URL to login to your site', 'it-l10n-better-wp-security' ),
					PHP_EOL . PHP_EOL,
					$new_slug,
					PHP_EOL . PHP_EOL,
					__( 'Please note this may be different than what you sent as the URL was sanitized to meet various requirements. A reminder has also been sent to the notification email(s) set in this plugins global settings.', 'it-l10n-better-wp-security' )
				);

				$this->send_new_slug( $new_slug );

			} else {
				$slug_text = false;
			}

			sprintf(
				'%s %s %s',
				__( 'Warning: Your admin URL has changed. Use the following URL to login to your site', 'it-l10n-better-wp-security' ),
				get_site_url() . '/' . $new_slug,
				__( 'Please note this may be different than what you sent as the URL was sanitized to meet various requirements.', 'it-l10n-better-wp-security' )
			);

			wp_enqueue_script( 'itsec_hide_backend_js', $this->module_path . 'js/admin-hide-backend.js', array( 'jquery' ), $itsec_globals['plugin_build'] );
			wp_localize_script(
				'itsec_hide_backend_js',
				'itsec_hide_backend',
				array(
					'new_slug' => $slug_text,
				)
			);

		}

	}

	/**
	 * Build rewrite rules
	 *
	 * @since 4.0
	 *
	 * @param  array $input options to build rules from
	 *
	 * @return array         rules to write
	 */
	public static function build_rewrite_rules( $input = null ) {

		$home_root = ITSEC_Lib::get_home_root();

		$server_type = ITSEC_Lib::get_server(); //Get the server type to build the right rules

		//Get the rules from the database if input wasn't sent
		if ( $input === null ) {
			$input = get_site_option( 'itsec_hide_backend' );
		}

		$rules = ''; //initialize all rules to blank string

		//don't add any rules if the module hasn't been enabled
		if ( $input['enabled'] == true ) {

			if ( $server_type == 'nginx' ) {

				$rules .= "\t# " . __( 'Rules to hide the dashboard', 'it-l10n-better-wp-security' ) . PHP_EOL . "\trewrite ^" . $home_root . $input['slug'] . "/?$ /wp-login.php?\$query_string break;" . PHP_EOL;

			} else {

				$rules .= "\t# " . __( 'Rules to hide the dashboard', 'it-l10n-better-wp-security' ) . PHP_EOL . "\tRewriteRule ^" . $home_root . $input['slug'] . "/?$ /wp-login.php [QSA,L]" . PHP_EOL;

			}

			if ( $input['register'] != 'wp-register.php' ) {

				if ( $server_type == 'nginx' ) {

					$rules .= "\trewrite ^" . $home_root . $input['register'] . "/?$ " . $home_root . $input['slug'] . "?action=register break;" . PHP_EOL;

				} else {

					$rules .= "\tRewriteRule ^" . $home_root . $input['register'] . "/?$ /wplogin?action=register [QSA,L]" . PHP_EOL;

				}

			}

		}

		if ( strlen( $rules ) > 0 ) {
			$rules = explode( PHP_EOL, $rules );
		} else {
			$rules = false;
		}

		//create a proper array for writing
		return array( 'type' => 'htaccess', 'priority' => 9, 'name' => 'Hide Backend', 'rules' => $rules, );

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
			$status       = array( 'text' => __( 'Your WordPress Dashboard is hidden.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_hide_backend_enabled', );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'Your WordPress Dashboard is using the default addresses. This can make a brute force attack much easier.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_hide_backend_enabled', );

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
	 * echos Hide Backend  Enabled Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function hide_backend_enabled() {

		if ( ( get_option( 'permalink_structure' ) == '' || get_option( 'permalink_structure' ) == false ) && ! is_multisite() ) {

			$adminurl = is_multisite() ? admin_url() . 'network/' : admin_url();

			$content = sprintf( '<p class="noPermalinks">%s <a href="%soptions-permalink.php">%s</a> %s</p>', __( 'You must turn on', 'it-l10n-better-wp-security' ), $adminurl, __( 'WordPress permalinks', 'it-l10n-better-wp-security' ), __( 'to use this feature.', 'it-l10n-better-wp-security' ) );

		} else {

			if ( isset( $this->settings['enabled'] ) && $this->settings['enabled'] === true ) {
				$enabled = 1;
			} else {
				$enabled = 0;
			}

			$content = '<input type="checkbox" id="itsec_hide_backend_enabled" name="itsec_hide_backend[enabled]" value="1" ' . checked( 1, $enabled, false ) . '/>';
			$content .= '<label for="itsec_hide_backend_enabled"> ' . __( 'Enable the hide backend feature.', 'it-l10n-better-wp-security' ) . '</label>';

		}

		echo $content;

	}

	/**
	 * echos Hide Backend Slug  Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function hide_backend_slug() {

		if ( ( get_option( 'permalink_structure' ) == '' || get_option( 'permalink_structure' ) == false ) && ! is_multisite() ) {

			$content = '';

		} else {

			$content = '<input name="itsec_hide_backend[slug]" id="itsec_hide_backend_strong_passwords_slug" value="' . sanitize_title( $this->settings['slug'] ) . '" type="text"><br />';
			$content .= '<label for="itsec_hide_backend_strong_passwords_slug">' . __( 'Login URL:', 'it-l10n-better-wp-security' ) . trailingslashit( get_option( 'siteurl' ) ) . '<span style="color: #4AA02C">' . sanitize_title( $this->settings['slug'] ) . '</span></label>';
			$content .= '<p class="description">' . __( 'The login url slug cannot be "login," "admin," "dashboard," or "wp-login.php" as these are use by default in WordPress.', 'it-l10n-better-wp-security' ) . '</p>';
			$content .= '<p class="description"><em>' . __( 'Note: The output is limited to alphanumeric characters, underscore (_) and dash (-). Special characters such as "." and "/" are not allowed and will be converted in the same manner as a post title. Please review your selection before logging out.', 'it-l10n-better-wp-security' ) . '</em></p>';

		}

		echo $content;

	}

	/**
	 * echos Hide Backend Slug  Field
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	public function hide_backend_theme_compat_slug() {

		if ( ( get_option( 'permalink_structure' ) == '' || get_option( 'permalink_structure' ) == false ) && ! is_multisite() ) {

			$content = '';

		} else {

			$slug = sanitize_title( isset( $this->settings['theme_compat_slug'] ) ? $this->settings['theme_compat_slug'] : 'not_found' );

			$content = '<input name="itsec_hide_backend[theme_compat_slug]" id="itsec_hide_backend_strong_passwords_theme_compat_slug" value="' . $slug . '" type="text"><br />';
			$content .= '<label for="itsec_hide_backend_strong_passwords_theme_compat_slug">' . __( '404 Slug:', 'it-l10n-better-wp-security' ) . trailingslashit( get_option( 'siteurl' ) ) . '<span style="color: #4AA02C">' . $slug . '</span></label>';
			$content .= '<p class="description">' . __( 'The slug to redirect folks to when theme compatibility mode is enabled (just make sure it does not exist in your site).', 'it-l10n-better-wp-security' ) . '</p>';

		}

		echo $content;

	}

	/**
	 * echos Hide Backend Slug  Field
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	public function hide_backend_post_logout_slug() {

		if ( ( get_option( 'permalink_structure' ) == '' || get_option( 'permalink_structure' ) == false ) && ! is_multisite() ) {

			$content = '';

		} else {

			$slug = sanitize_title( isset( $this->settings['post_logout_slug'] ) ? $this->settings['post_logout_slug'] : '' );

			$content = '<input name="itsec_hide_backend[post_logout_slug]" id="itsec_hide_backend_strong_passwords_post_logout_slug" value="' . $slug . '" type="text"><br />';
			$content .= '<label for="itsec_hide_backend_strong_passwords_post_logout_slug">' . __( 'Custom Action:', 'it-l10n-better-wp-security' ) . '</label>';
			$content .= '<p class="description">' . __( 'WordPress uses the "action" variable to handle many login and logout functions. By default this plugin can handle the normal ones but some plugins and themes may utilize a custom action (such as logging out of a private post). If you need a custom action please enter it here.', 'it-l10n-better-wp-security' ) . '</p>';

		}

		echo $content;

	}

	/**
	 * echos Hide Backend  theme compatibility Field
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	public function hide_backend_theme_compat() {

		if ( ( get_option( 'permalink_structure' ) == '' || get_option( 'permalink_structure' ) == false ) && ! is_multisite() ) {

			$adminurl = is_multisite() ? admin_url() . 'network/' : admin_url();

			$content = sprintf( '<p class="noPermalinks">%s <a href="%soptions-permalink.php">%s</a> %s</p>', __( 'You must turn on', 'it-l10n-better-wp-security' ), $adminurl, __( 'WordPress permalinks', 'it-l10n-better-wp-security' ), __( 'to use this feature.', 'it-l10n-better-wp-security' ) );

		} else {

			if ( isset( $this->settings['theme_compat'] ) && $this->settings['theme_compat'] === true ) {
				$enabled = 1;
			} else {
				$enabled = 0;
			}

			$content = '<input type="checkbox" id="itsec_hide_backend_theme_compat" name="itsec_hide_backend[theme_compat]" value="1" ' . checked( 1, $enabled, false ) . '/>';
			$content .= '<label for="itsec_hide_backend_theme_compat"> ' . __( 'Enable theme compatibility. If  you see errors in your theme when using hide backend, in particular when going to wp-admin while not logged in, turn this on to fix them.', 'it-l10n-better-wp-security' ) . '</label>';

		}

		echo $content;

	}

	/**
	 * echos Register Slug  Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function hide_backend_register() {

		if ( ( get_option( 'permalink_structure' ) == '' || get_option( 'permalink_structure' ) == false ) && ! is_multisite() ) {

			$content = '';

		} else {

			$content = '<input name="itsec_hide_backend[register]" id="itsec_hide_backend_strong_passwords_register" value="' . ( $this->settings['register'] !== 'wp-register.php' ? sanitize_title( $this->settings['register'] ) : 'wp-register.php' ) . '" type="text"><br />';
			$content .= '<label for="itsec_hide_backend_strong_passwords_register">' . __( 'Registration URL:', 'it-l10n-better-wp-security' ) . trailingslashit( get_option( 'siteurl' ) ) . '<span style="color: #4AA02C">' . sanitize_title( $this->settings['register'] ) . '</span></label>';

		}

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
	private function initialize( $core ) {

		$this->core        = $core;
		$this->settings    = get_site_option( 'itsec_hide_backend' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_filter( 'itsec_file_modules', array( $this, 'register_file' ) ); //register tooltip action
		add_filter( 'itsec_tooltip_modules', array( $this, 'register_tooltip' ) ); //register tooltip action
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
			'hide_backend-enabled',
			__( 'Hide Login and Admin', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		add_settings_section(
			'hide_backend-settings',
			__( 'Hide Login and Admin', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		//Hide Backend Fields
		add_settings_field(
			'itsec_hide_backend[enabled]',
			__( 'Hide Backend', 'it-l10n-better-wp-security' ),
			array( $this, 'hide_backend_enabled' ),
			'security_page_toplevel_page_itsec_settings',
			'hide_backend-enabled'
		);

		add_settings_field(
			'itsec_hide_backend[slug]',
			__( 'Login Slug', 'it-l10n-better-wp-security' ),
			array( $this, 'hide_backend_slug' ),
			'security_page_toplevel_page_itsec_settings',
			'hide_backend-settings'
		);

		if ( get_site_option( 'users_can_register' ) ) {

			add_settings_field(
				'itsec_hide_backend[register]',
				__( 'Register Slug', 'it-l10n-better-wp-security' ),
				array( $this, 'hide_backend_register' ),
				'security_page_toplevel_page_itsec_settings',
				'hide_backend-settings'
			);

		}

		add_settings_field(
			'itsec_hide_backend[theme_compat]',
			__( 'Enable Theme Compatibility', 'it-l10n-better-wp-security' ),
			array( $this, 'hide_backend_theme_compat' ),
			'security_page_toplevel_page_itsec_settings',
			'hide_backend-settings'
		);

		add_settings_field(
			'itsec_hide_backend[theme_compat_slug]',
			__( 'Theme Compatibility Slug', 'it-l10n-better-wp-security' ),
			array( $this, 'hide_backend_theme_compat_slug' ),
			'security_page_toplevel_page_itsec_settings',
			'hide_backend-settings'
		);

		add_settings_field(
			'itsec_hide_backend[post_logout_slug]',
			__( 'Custom Login Action', 'it-l10n-better-wp-security' ),
			array( $this, 'hide_backend_post_logout_slug' ),
			'security_page_toplevel_page_itsec_settings',
			'hide_backend-settings'
		);

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_settings',
			'itsec_hide_backend',
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
	public function metabox_hide_backend_settings() {

		echo '<p>' . __( 'Hides the login page (wp-login.php, wp-admin, admin and login) making it harder to find by automated attacks and making it easier for users unfamiliar with the WordPress platform.', 'it-l10n-better-wp-security' ) . '</p>';

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'hide_backend-enabled', false );
		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'hide_backend-settings', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_settings' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-better-wp-security' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Register ban users for file writer
	 *
	 * @param  array $file_modules array of file writer modules
	 *
	 * @return array                   array of file writer modules
	 */
	public function register_file( $file_modules ) {

		$file_modules['hide-backend'] = array(
			'rewrite' => array( $this, 'save_rewrite_rules' ),
		);

		return $file_modules;

	}

	/**
	 * Register backups for tooltips
	 *
	 * @param  array $tooltip_modules array of tooltip modules
	 *
	 * @return array                   array of tooltip modules
	 */
	public function register_tooltip( $tooltip_modules ) {

		if ( get_site_transient( 'ITSEC_SHOW_HIDE_BACKEND_TOOLTIP' ) || ( isset( $this->settings['show-tooltip'] ) && $this->settings['show-tooltip'] === true ) ) {

			$tooltip_modules['hide-backend'] = array(
				'priority'  => 0,
				'class'     => 'itsec_tooltip_hide_backend',
				'heading'   => __( 'Review Hide Backend Settings', 'it-l10n-better-wp-security' ),
				'text'      => __( 'The hide backend system has been rewritten. You must re-activate the feature to continue using the feature.', 'it-l10n-better-wp-security' ),
				'link_text' => __( 'Review Settings', 'it-l10n-better-wp-security' ),
				'link'      => '?page=toplevel_page_itsec_settings#itsec_hide_backend_enabled',
				'success'   => '',
				'failure'   => '',
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

		//Process hide backend settings
		$input['enabled']      = ( isset( $input['enabled'] ) && intval( $input['enabled'] == 1 ) ? true : false );
		$input['theme_compat'] = ( isset( $input['theme_compat'] ) && intval( $input['theme_compat'] == 1 ) ? true : false );
		$input['show-tooltip'] = ( isset( $this->settings['show-tooltip'] ) ? $this->settings['show-tooltip'] : false );

		if ( isset( $input['slug'] ) ) {

			$input['slug'] = sanitize_title( $input['slug'] );

		} else {

			$input['slug'] = 'wplogin';

		}

		if ( isset( $input['post_logout_slug'] ) ) {

			$input['post_logout_slug'] = sanitize_title( $input['post_logout_slug'] );

		} else {

			$input['post_logout_slug'] = '';

		}

		if ( $input['slug'] != $this->settings['slug'] && $input['enabled'] === true ) {
			add_site_option( 'itsec_hide_backend_new_slug', $input['slug'] );
		}

		if ( isset( $input['register'] ) && $input['register'] !== 'wp-register.php' ) {
			$input['register'] = sanitize_title( $input['register'] );
		} else {
			$input['register'] = 'wp-register.php';
		}

		if ( isset( $input['theme_compat_slug'] ) ) {
			$input['theme_compat_slug'] = sanitize_title( $input['theme_compat_slug'] );
		} else {
			$input['theme_compat_slug'] = 'not_found';
		}

		$forbidden_slugs = array( 'admin', 'login', 'wp-login.php', 'dashboard', 'wp-admin', '' );

		if ( in_array( trim( $input['slug'] ), $forbidden_slugs ) && $input['enabled'] === true ) {

			$invalid_login_slug = true;

			$type    = 'error';
			$message = __( 'Invalid hide login slug used. The login url slug cannot be "login," "admin," "dashboard," or "wp-login.php" ob "" (blank) as these are use by default in WordPress.', 'it-l10n-better-wp-security' );

			add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

		} else {

			$invalid_login_slug = false;

		}

		if ( $invalid_login_slug === false ) {

			if (
				! isset( $type ) &&
				(
					$input['slug'] !== $this->settings['slug'] ||
					$input['register'] !== $this->settings['register'] ||
					$input['enabled'] !== $this->settings['enabled']
				) ||
				isset( $itsec_globals['settings']['write_files'] ) && $itsec_globals['settings']['write_files'] === true
			) {

				add_site_option( 'itsec_rewrites_changed', true );

			}

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

		if ( isset( $_POST['itsec_hide_backend'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_settings-options' ) ) {
				die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
			}

			update_site_option( 'itsec_hide_backend', $_POST['itsec_hide_backend'] ); //we must manually save network options

		}

	}

	/**
	 * Saves rewrite rules to file writer.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	public function save_rewrite_rules() {

		global $itsec_files;

		$rewrite_rules = $itsec_files->get_rewrite_rules();

		foreach ( $rewrite_rules as $key => $rule ) {

			if ( isset( $rule['name'] ) && $rule['name'] == 'Hide Backend' ) {
				unset ( $rewrite_rules[$key] );
			}

		}

		$rewrite_rules[] = $this->build_rewrite_rules();

		$itsec_files->set_rewrite_rules( $rewrite_rules );

	}

	/**
	 * Sends an email to notify site admins of the new login url
	 *
	 * @param  string $new_slug the new login url
	 *
	 * @return void
	 */
	private function send_new_slug( $new_slug ) {

		global $itsec_globals;

		//Put the copy all together
		$body = sprintf(
			'<p>%s,</p><p>%s <a href="%s">%s</a>. %s <a href="%s">%s</a> %s.</p>',
			__( 'Dear Site Admin', 'it-l10n-better-wp-security' ),
			__( 'This friendly email is just a reminder that you have changed the dashboard login address on', 'it-l10n-better-wp-security' ),
			get_site_url(),
			get_site_url(),
			__( 'You must now use', 'it-l10n-better-wp-security' ),
			$new_slug,
			$new_slug,
			__( 'to login to your WordPress website', 'it-l10n-better-wp-security' )
		);

		//Setup the remainder of the email
		$recipients = $itsec_globals['settings']['notification_email'];
		$subject    = '[' . get_option( 'siteurl' ) . '] ' . __( 'WordPress Login Email Changed', 'it-l10n-better-wp-security' );
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
	 * Adds fields that will be tracked for Google Analytics
	 *
	 * @since 4.0
	 *
	 * @param array $vars tracking vars
	 *
	 * @return array tracking vars
	 */
	public function tracking_vars( $vars ) {

		$vars['itsec_hide_backend'] = array(
			'enabled' => '0:b',
		);

		return $vars;

	}

}