<?php
/**
 * iThemes Security Core.
 *
 * Core class for iThemes Security sets up globals and other items and dispatches modules.
 *
 * @package iThemes_Security
 *
 * @since   4.0
 *
 * @global array  $itsec_globals Global variables for use throughout iThemes Security.
 * @global object $itsec_files   iThemes Security file writer.
 * @global object $itsec_logger  iThemes Security logging class.
 * @global object $itsec_lockout Class for handling lockouts.
 *
 */
if ( ! class_exists( 'ITSEC_Core' ) ) {

	final class ITSEC_Core {

		private
			$tooltip_modules,
			$one_click,
			$pages,
			$pro_toc_items,
			$tracking_vars,
			$toc_items;

		public
			$available_pages;

		/**
		 * Loads core functionality across both admin and frontend.
		 *
		 * Creates all plugin globals, registers activation and related hooks,
		 * loads the text domain and loads all plugin modules
		 *
		 * @since  4.0
		 *
		 * @access private
		 *
		 * @param string $plugin_file the main plugin file
		 * @param        string       @plugin_name The plugin name
		 *
		 */
		function __construct( $plugin_file, $plugin_name ) {

			global $itsec_globals, $itsec_files, $itsec_logger, $itsec_lockout, $itsec_sync;

			$this->tooltip_modules = array(); //initialize tooltip modules.
			$this->one_click       = array(); //initialize one-click settings

			if ( get_site_transient( 'itsec_upload_dir' ) === false ) {

				if ( is_multisite() ) {

					switch_to_blog( 1 );
					$upload_dir = wp_upload_dir(); //get the full upload directory array so we can grab the base directory.
					restore_current_blog();

				} else {

					$upload_dir = wp_upload_dir(); //get the full upload directory array so we can grab the base directory.

				}

				set_site_transient( 'itsec_upload_dir', $upload_dir, 86400 );

			} else {

				$upload_dir = get_site_transient( 'itsec_upload_dir' );

			}

			//Set plugin defaults
			$itsec_globals = array(
				'plugin_build'       => 4030, //plugin build number - used to trigger updates
				'plugin_access_lvl'  => 'manage_options', //Access level required to access plugin options
				'plugin_name'        => sanitize_text_field( $plugin_name ), //the name of the plugin
				'plugin_base'        => str_replace( WP_PLUGIN_DIR . '/', '', $plugin_file ),
				'plugin_file'        => $plugin_file, //the main plugin file
				'plugin_dir'         => plugin_dir_path( $plugin_file ), //the path of the plugin directory
				'plugin_url'         => plugin_dir_url( $plugin_file ), //the URL of the plugin directory
				'is_iwp_call'        => false,
				'ithemes_dir'        => $upload_dir['basedir'] . '/ithemes-security',
				//folder for saving iThemes Security files
				'ithemes_log_dir'    => $upload_dir['basedir'] . '/ithemes-security/logs',
				//folder for saving iThemes Security logs
				'ithemes_backup_dir' => $upload_dir['basedir'] . '/ithemes-security/backups',
				//folder for saving iThemes Backup files
				'current_time'       => current_time( 'timestamp' ), //the current local time in unix timestamp format
				'current_time_gmt'   => current_time( 'timestamp', 1 ), //the current gmt time in unix timestamp format
				'settings'           => get_site_option( 'itsec_global' ),
				'free_modules'       => array(
					'four-oh-four'      => array(
						'has_front' => true,
						'option'    => 'itsec_four_oh_four',
						'setting'   => 'enabled',
						'value'     => true,
						'class_id'  => 'Four_Oh_Four',
					),
					'admin-user'        => array(
						'has_front' => false,
						'class_id'  => 'Admin_User',
					),
					'away-mode'         => array(
						'has_front' => true,
						'option'    => 'itsec_away_mode',
						'setting'   => 'enabled',
						'value'     => true,
						'class_id'  => 'Away_Mode',
					),
					'ban-users'         => array(
						'has_front' => true,
						'option'    => 'itsec_global',
						'setting'   => 'blacklist',
						'value'     => true,
						'class_id'  => 'Ban_Users',
					),
					'brute-force'       => array(
						'has_front' => true,
						'option'    => 'itsec_brute_force',
						'setting'   => 'enabled',
						'value'     => true,
						'class_id'  => 'Brute_Force',
					),
					'backup'            => array(
						'has_front' => true,
						'option'    => 'itsec_backup',
						'setting'   => 'enabled',
						'value'     => true,
						'class_id'  => 'Backup',
					),
					'file-change'       => array(
						'has_front' => true,
						'option'    => 'itsec_file_change',
						'setting'   => 'enabled',
						'value'     => true,
						'class_id'  => 'File_Change',
					),
					'hide-backend'      => array(
						'has_front' => true,
						'option'    => 'itsec_hide_backend',
						'setting'   => 'enabled',
						'value'     => true,
						'class_id'  => 'Hide_Backend',
					),
					'ssl'               => array(
						'has_front' => true,
						'option'    => 'itsec_ssl',
						'setting'   => 'frontend',
						'value'     => array( 1, 2 ),
						'class_id'  => 'SSL',
					),
					'strong-passwords'  => array(
						'has_front' => true,
						'option'    => 'itsec_strong_passwords',
						'setting'   => 'enabled',
						'value'     => true,
						'class_id'  => 'Strong_Passwords',
					),
					'tweaks'            => array(
						'has_front' => true,
						'class_id'  => 'Tweaks',
					),
					'content-directory' => array(
						'has_front' => false,
						'class_id'  => 'Content_Directory',
					),
					'database-prefix'   => array(
						'has_front' => false,
						'class_id'  => 'Database_Prefix',
					),
					'help'              => array(
						'has_front' => false,
						'class_id'  => 'Help',
					),
					'core'              => array(
						'has_front' => false,
						'class_id'  => 'Core',
					),
				),
				'pro_modules'        => array(
					'help' => array(
						'has_front' => false,
						'class_id'  => 'Help',
					),
					'core' => array(
						'has_front' => false,
						'class_id'  => 'Core',
					),
				),
			);

			$free_modules_folder = $itsec_globals['plugin_dir'] . 'modules/free';
			$pro_modules_folder  = $itsec_globals['plugin_dir'] . 'modules/pro';

			$itsec_globals['has_pro'] = is_dir( $pro_modules_folder );

			$this->pages = array(
				array(
					'priority'  => 1,
					'title'     => __( 'Settings', 'it-l10n-better-wp-security' ),
					'slug'      => 'settings',
					'has_tab'   => true,
					'admin_bar' => false,
				),
				array(
					'priority'  => 5,
					'title'     => __( 'Advanced', 'it-l10n-better-wp-security' ),
					'slug'      => 'advanced',
					'has_tab'   => true,
					'admin_bar' => false,
				),
				array(
					'priority'  => 15,
					'title'     => __( 'Logs', 'it-l10n-better-wp-security' ),
					'slug'      => 'logs',
					'has_tab'   => true,
					'admin_bar' => true,
				),
				array(
					'priority'  => 20,
					'title'     => __( 'Help', 'it-l10n-better-wp-security' ),
					'slug'      => 'help',
					'has_tab'   => true,
					'admin_bar' => false,
				),
			);

			if ( isset( $itsec_globals['has_pro'] ) && $itsec_globals['has_pro'] === true && sizeof( $itsec_globals['pro_modules'] ) > 2 ) {

				$this->pages[] = array(
					'priority'  => 8,
					'title'     => __( 'Pro', 'it-l10n-better-wp-security' ),
					'slug'      => 'pro',
					'has_tab'   => true,
					'admin_bar' => false,
				);

			}

			if ( class_exists( 'backupbuddy_api' ) ) {

				$this->pages[] = array(
					'priority'  => 10,
					'title'     => __( 'Backups', 'it-l10n-better-wp-security' ),
					'link'      => 'pb_backupbuddy_backup',
					'slug'      => 'backups',
					'has_tab'   => true,
					'admin_bar' => true,
				);

			} else {

				$this->pages[] = array(
					'priority'  => 10,
					'title'     => __( 'Backups', 'it-l10n-better-wp-security' ),
					'slug'      => 'backups',
					'has_tab'   => true,
					'admin_bar' => true,
				);

			}

			//Determine if we need to run upgrade scripts
			$plugin_data = get_site_option( 'itsec_data' );

			if ( $plugin_data === false ) { //if plugin data does exist
				$plugin_data = $this->save_plugin_data();
			}

			$itsec_globals['data'] = $plugin_data; //adds plugin data to $itsec_globals

			//Add Javascripts script
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) ); //enqueue scripts for admin page

			//load utility functions
			if ( ! class_exists( 'ITSEC_Lib' ) ) {
				require( $itsec_globals['plugin_dir'] . 'core/class-itsec-lib.php' );
			}

			//load logging functions
			if ( ! class_exists( 'ITSEC_Logger' ) ) {

				require( $itsec_globals['plugin_dir'] . 'core/class-itsec-logger.php' );
				$itsec_logger = new ITSEC_Logger();

			}

			//load lockout functions
			if ( ! class_exists( 'ITSEC_Lockout' ) ) {

				require( $itsec_globals['plugin_dir'] . 'core/class-itsec-lockout.php' );
				$itsec_lockout = new ITSEC_Lockout();

			}

			//load file utility functions
			if ( ! class_exists( 'ITSEC_Files' ) ) {

				require( $itsec_globals['plugin_dir'] . 'core/class-itsec-files.php' );
				$itsec_files = new ITSEC_Files();

			}

			//Load Sync integration
			if ( get_site_option( 'ithemes-sync-authenticated' ) !== false && ! class_exists( 'ITSEC_Sync' ) ) {

				require( $itsec_globals['plugin_dir'] . 'core/class-itsec-sync.php' );
				$itsec_sync = new ITSEC_Sync();

			}

			//load the text domain
			load_plugin_textdomain( 'it-l10n-better-wp-security', false, $itsec_globals['plugin_dir'] . '/lang' );

			//builds admin menus after modules are loaded
			if ( is_admin() ) {

				//load logging functions
				if ( ! class_exists( 'ITSEC_Dashboard_Admin' ) ) {

					require( $itsec_globals['plugin_dir'] . 'core/class-itsec-dashboard-admin.php' );
					new ITSEC_Dashboard_Admin( $this );

				}

				//load logging functions
				if ( ! class_exists( 'ITSEC_Global_Settings' ) ) {

					require( $itsec_globals['plugin_dir'] . 'core/class-itsec-global-settings.php' );
					new ITSEC_Global_Settings( $this );

				}

				//Process support plugin nag
				add_action( 'itsec_admin_init', array( $this, 'upgrade_nag' ) );

				//add action link
				add_filter( 'plugin_action_links', array( $this, 'add_action_link' ), 10, 2 );

				//add plugin meta links
				add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 4 );

				//Register all plugin modules and register sync
				add_action( 'plugins_loaded', array( $this, 'register_modules' ) );

				//register one-click tooltip
				add_filter( 'itsec_tooltip_modules', array( $this, 'register_tooltip' ) ); //register tooltip action

				//Run ajax for tooltips
				add_action( 'wp_ajax_itsec_tooltip_ajax', array( $this, 'admin_tooltip_ajax' ) );
				add_action( 'wp_ajax_itsec_tracking_ajax', array( $this, 'admin_tracking_ajax' ) );

				$this->build_admin(); //call before function

			}

			//Admin bar links
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_links' ), 99 );

			//require plugin setup information
			if ( ! class_exists( 'ITSEC_Setup' ) ) {
				require( $itsec_globals['plugin_dir'] . 'core/class-itsec-setup.php' );
			}

			register_activation_hook( $itsec_globals['plugin_file'], array( 'ITSEC_Setup', 'on_activate' ) );
			register_deactivation_hook( $itsec_globals['plugin_file'], array( 'ITSEC_Setup', 'on_deactivate' ) );
			register_uninstall_hook( $itsec_globals['plugin_file'], array( 'ITSEC_Setup', 'on_uninstall' ) );

			if ( isset( $itsec_globals['settings']['infinitewp_compatibility'] ) && $itsec_globals['settings']['infinitewp_compatibility'] === true ) {

				$HTTP_RAW_POST_DATA = @file_get_contents( 'php://input' );

				if ( $HTTP_RAW_POST_DATA !== false && strlen( $HTTP_RAW_POST_DATA ) > 0 ) {

					$data = base64_decode( $HTTP_RAW_POST_DATA );

					if ( strpos( $data, 's:10:"iwp_action";' ) !== false ) {
						$itsec_globals['is_iwp_call'] = true;
					}

				}

			}

			//load all present modules
			$this->load_modules( $free_modules_folder, $pro_modules_folder, $itsec_globals['has_pro'] );

			//see if the saved build version is older than the current build version
			if ( isset( $plugin_data['build'] ) && $plugin_data['build'] !== $itsec_globals['plugin_build'] ) {
				add_action( 'plugins_loaded', array( $this, 'execute_upgrade' ) );
			}

			//See if they're upgrade from Better WP Security
			if ( is_multisite() && isset( $itsec_globals['settings']['did_upgrade'] ) && $itsec_globals['settings']['did_upgrade'] === true ) {

				switch_to_blog( 1 );

				$bwps_options = get_option( 'bit51_bwps' );

				restore_current_blog();

			} else {

				$bwps_options = get_option( 'bit51_bwps' );

			}

			if ( $bwps_options !== false ) {
				add_action( 'plugins_loaded', array( $this, 'do_upgrade' ) );
			}

			add_action( 'itsec_wpconfig_metabox', array( $itsec_files, 'config_metabox_contents' ) );
			add_action( 'itsec_rewrite_metabox', array( $itsec_files, 'rewrite_metabox_contents' ) );

		}

		/**
		 * Add action link to plugin page.
		 *
		 * Adds plugin settings link to plugin page in WordPress admin area.
		 *
		 * @since 4.0
		 *
		 * @param object $links Array of WordPress links
		 * @param string $file  String name of current file
		 *
		 * @return object Array of WordPress links
		 *
		 */
		function add_action_link( $links, $file ) {

			static $this_plugin;

			global $itsec_globals;

			if ( empty( $this_plugin ) ) {
				$this_plugin = $itsec_globals['plugin_base'];
			}

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=itsec">' . __( 'Dashboard', 'it-l10n-better-wp-security' ) . '</a>';
				array_unshift( $links, $settings_link );
			}

			return $links;
		}

		/**
		 * Adds links to the plugin row meta
		 *
		 * @since 4.0
		 *
		 * @param array  $meta        Existing meta
		 * @param string $plugin_file the wp plugin slug (path)
		 *
		 * @return array
		 */
		public function add_plugin_meta_links( $meta, $plugin_file ) {

			global $itsec_globals;

			if ( $itsec_globals['plugin_base'] == $plugin_file ) {

				$meta = apply_filters( 'itsec_meta_links', $meta );

			}

			return $meta;
		}

		/**
		 * Add items to the table of contents
		 *
		 * @since 4.0
		 *
		 * @param array $item the item to add to the table of content
		 *
		 * @return void
		 */
		public function add_toc_item( $item ) {

			$this->toc_items[] = $item;

		}

		/**
		 * Add items to the table of contents
		 *
		 * @since 4.1
		 *
		 * @param array $item the item to add to the table of content
		 *
		 * @return void
		 */
		public function add_pro_toc_item( $item ) {

			$this->pro_toc_items[] = $item;

		}

		/**
		 * Add admin bar item
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function admin_bar_links() {

			global $wp_admin_bar, $itsec_globals;

			if ( ( ! is_multisite() && ! current_user_can( $itsec_globals['plugin_access_lvl'] ) ) || ( is_multisite() && ! current_user_can( 'manage_network' ) ) ) {
				return;
			}

			if ( is_multisite() ) {
				$network = 'network/';
			} else {
				$network = '';
			}

			// Add the Parent link.
			$wp_admin_bar->add_menu(
			             array(
				             'title' => __( 'Security', 'it-l10n-better-wp-security' ),
				             'href'  => admin_url( $network . 'admin.php?page=itsec' ),
				             'id'    => 'itsec_admin_bar_menu',
			             )
			);

			$wp_admin_bar->add_menu(
			             array(
				             'id'     => 'itsec_admin_bar_dashboard',
				             'title'  => __( 'Dashboard', 'it-l10n-better-wp-security' ),
				             'href'   => admin_url( $network . 'admin.php?page=itsec' ),
				             'parent' => 'itsec_admin_bar_menu',
			             )
			);

			/**
			 * Add the submenu links.
			 */
			foreach ( $this->pages as $key => $page ) {

				if ( isset( $page['admin_bar'] ) && $page['admin_bar'] === true ) {

					if ( isset( $page['link'] ) ) {

						$wp_admin_bar->add_menu(
						             array(
							             'id'     => 'test_' . $page['slug'],
							             'title'  => $page['title'],
							             'href'   => admin_url( $network . 'admin.phpadmin.php?page=' . $page['link'] ),
							             'parent' => 'itsec_admin_bar_menu',
						             )
						);

					} else {

						$wp_admin_bar->add_menu(
						             array(
							             'id'     => 'test_' . $page['slug'],
							             'title'  => $page['title'],
							             'href'   => admin_url( $network . 'admin.php?page=toplevel_page_itsec_' . $page['slug'] ),
							             'parent' => 'itsec_admin_bar_menu',
						             )
						);

					}

				}

			}

		}

		/**
		 * Process the ajax call for the tooltip.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function admin_tooltip_ajax() {

			global $itsec_globals;

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_tooltip_nonce' ) ) {
				die ();
			}

			if ( sanitize_text_field( $_POST['module'] ) == 'close' ) {

				$data                       = $itsec_globals['data'];
				$data['tooltips_dismissed'] = true;
				update_site_option( 'itsec_data', $data );

			} else {

				call_user_func_array( $this->tooltip_modules[sanitize_text_field( $_POST['module'] )]['callback'], array() );

			}

			die(); // this is required to return a proper result

		}

		/**
		 * Process the ajax call for the tracking script.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function admin_tracking_ajax() {

			global $itsec_globals;

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'itsec_tracking_nonce' ) ) {
				die ();
			}

			if ( sanitize_text_field( $_POST['module'] ) == 'close' ) {

				$data                       = $itsec_globals['data'];
				$data['tooltips_dismissed'] = true;
				update_site_option( 'itsec_data', $data );

			} else {

				call_user_func_array( $this->tooltip_modules[sanitize_text_field( $_POST['module'] )]['callback'], array() );

			}

			die(); // this is required to return a proper result

		}

		/**
		 * Echos intro modal box.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		private function admin_modal() {

			echo '<ol id="itsec_intro_modal" style="display:none;">';

			if ( sizeof( $this->tooltip_modules ) > 0 ) {

				uasort( $this->tooltip_modules, array( $this, 'sort_tooltips' ) );

				foreach ( $this->tooltip_modules as $module => $tip ) {

					echo '<li class="tooltip_' . $module . '" id="' . $tip['class'] . '">';

					if ( isset( $tip['link'] ) ) {

						echo '<h4>' . $tip['heading'] . '</h4><p>' . $tip['text'] . '</p><a href="' . $tip['link'] . '" class="button-primary">' . $tip['link_text'] . '</a>';

					} else {

						echo '<h4>' . $tip['heading'] . '</h4><p>' . $tip['text'] . '</p><a href="' . $module . '" class="itsec_tooltip_ajax button-primary">' . $tip['link_text'] . '</a>';

					}

					echo '</li>';

				}

			}

			echo '<a href="javascript:void(0);" class="itsec-intro-close">' . __( 'Dismiss', 'it-l10n-better-wp-security' ) . '</a>';

			echo '</ol>';

		}

		/**
		 * Displays plugin admin notices.
		 *
		 * @since 4.0
		 *
		 * @return  void
		 */
		public function admin_notices() {

			if ( isset( get_current_screen()->id ) && ( strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_settings' ) !== false || strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_advanced' ) !== false || strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_pro' ) !== false ) ) {

				$errors = get_settings_errors( 'itsec' );

				$updated = '';

				if ( get_site_option( 'itsec_manual_update' ) == true ) {

					delete_site_option( 'itsec_manual_update' );

					if ( ITSEC_Lib::get_server() == 'nginx' ) {

						$server = __( 'NGINX conf file and/or restart your NGINX server', 'it-l10n-better-wp-security' );

					} else {

						$server = __( '.htaccess file', 'it-l10n-better-wp-security' );

					}

					$updated = sprintf(
						'<br />%s %s %s <a href="%s">%s</a> %s',
						__( 'As you have not allowed this plugin to update system files you must update your', 'it-l10n-better-wp-security' ),
						$server,
						__( 'as well as your wp-config.php file manually. Rules to insert in both files can be found on the Dashboard page.', 'it-l10n-better-wp-security' ),
						'?page=toplevel_page_itsec_settings#itsec_global_write_files',
						__( 'Click here', 'it-l10n-better-wp-security' ),
						__( 'to allow this plugin to write to these files.', 'it-l10n-better-wp-security' )
					);

				}

				if ( sizeof( $errors ) === 0 && isset ( $_GET['settings-updated'] ) && sanitize_text_field( $_GET['settings-updated'] ) == 'true' ) {

					add_settings_error( 'itsec', esc_attr( 'settings_updated' ), __( 'Settings Updated', 'it-l10n-better-wp-security' ) . $updated, 'updated' );

				}

			}

			settings_errors( 'itsec' );

		}

		/**
		 * Add Tracking Javascript.
		 *
		 * Adds javascript for tracking settings to all itsec admin pages
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function admin_script() {

			global $itsec_globals;

			$messages = array();

			if ( sizeof( $this->tooltip_modules ) > 0 ) {

				uasort( $this->tooltip_modules, array( $this, 'sort_tooltips' ) );

				foreach ( $this->tooltip_modules as $module => $tip ) {

					$messages[$module] = array(
						'success' => $tip['success'],
						'failure' => $tip['failure'],
					);

				}

			}

			wp_register_style( 'itsec_notice_css', $itsec_globals['plugin_url'] . 'core/css/itsec_notice.css' ); //add multi-select css
			wp_enqueue_style( 'itsec_notice_css' );

			//scripts for all itsec pages
			if ( isset( get_current_screen()->id ) && strpos( get_current_screen()->id, 'itsec' ) !== false ) {

				if ( ( isset( $itsec_globals['settings']['allow_tracking'] ) && $itsec_globals['settings']['allow_tracking'] === true && strpos( get_current_screen()->id, 'itsec' ) !== false ) || get_option( 'bit51_bwps' ) !== false ) {

					wp_enqueue_script( 'itsec_tracking', $itsec_globals['plugin_url'] . 'core/js/tracking.js', array( 'jquery' ), $itsec_globals['plugin_build'] );
					wp_localize_script( 'itsec_tracking', 'itsec_tracking_vars', array(
						'vars'  => $this->tracking_vars,
						'nonce' => wp_create_nonce( 'itsec_tracking_nonce' )
					) );

				}

				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_style( 'jquery-ui-tabs' );
				wp_enqueue_style( 'wp-jquery-ui-dialog' );
				wp_enqueue_script( 'itsec_dashboard_js', $itsec_globals['plugin_url'] . 'core/js/admin-dashboard.js', array( 'jquery' ) );
				wp_localize_script( 'itsec_dashboard_js', 'itsec_dashboard', array(
					'text' => __( 'Show Intro', 'it-l10n-better-wp-security' ),
				) );
				wp_enqueue_script( 'itsec_footer', $itsec_globals['plugin_url'] . 'core/js/admin-dashboard-footer.js', array( 'jquery' ), $itsec_globals['plugin_build'], true );

				if ( ! isset( $itsec_globals['data']['tooltips_dismissed'] ) || $itsec_globals['data']['tooltips_dismissed'] === false || ( isset( $_GET['show_admin_modal'] ) && $_GET['show_admin_modal'] == 'true' ) ) {

					wp_enqueue_script( 'itsec_modal', $itsec_globals['plugin_url'] . 'core/js/admin-modal.js', array( 'jquery' ), $itsec_globals['plugin_build'], true );
					wp_localize_script( 'itsec_modal', 'itsec_tooltip_text', array(
						'nonce'    => wp_create_nonce( 'itsec_tooltip_nonce' ),
						'messages' => $messages,
						'title'    => __( 'Important First Steps', 'it-l10n-better-wp-security' ),
					) );

				}

			}

		}

		/**
		 * Creates admin tabs.
		 *
		 * Used to display module tabs across all iThemes Security admin pages.
		 *
		 * @since 4.0
		 *
		 * @param  string $current current tab id
		 *
		 * @return void
		 */
		public function admin_tabs( $current = NULL ) {

			if ( $current == NULL ) {
				$current = 'itsec';
			}

			echo '<div id="icon-themes" class="icon32"><br></div>';
			echo '<h2 class="nav-tab-wrapper">';

			$class = ( $current == 'itsec' ) ? ' nav-tab-active' : '';
			echo '<a class="nav-tab' . $class . '" href="?page=itsec">' . __( 'Dashboard', 'it-l10n-better-wp-security' ) . '</a>';

			foreach ( $this->pages as $page ) {

				if ( $page['has_tab'] === true ) {

					if ( isset( $page['link'] ) ) {
						$link = $page['link'];
					} else {
						$link = 'toplevel_page_itsec_' . $page['slug'];
					}

					$class = ( $current == 'toplevel_page_itsec_' . $page['slug'] ) ? ' nav-tab-active' : '';
					echo '<a class="nav-tab' . $class . '" href="?page=' . $link . '">' . $page['title'] . '</a>';

				}

			}

			echo '</h2>';

		}

		/**
		 * Enqueue actions to build the admin pages.
		 *
		 * Calls all the needed actions to build any given admin page.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function build_admin() {

			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			add_action( 'admin_init', array( $this, 'execute_admin_init' ) );

			if ( is_multisite() ) { //must be network admin in multisite
				add_action( 'network_admin_menu', array( $this, 'setup_primary_admin' ) );
			} else {
				add_action( 'admin_menu', array( $this, 'setup_primary_admin' ) );
			}

		}

		/**
		 * Prints out all settings sections added to a particular settings page.
		 *
		 * adapted from core function for better styling within meta_box.
		 *
		 * @since 4.0
		 *
		 * @param string  $page       The slug name of the page whos settings sections you want to output
		 * @param string  $section    the section to show
		 * @param boolean $show_title Whether or not the title of the section should display: default true.
		 *
		 * @return void
		 */
		public function do_settings_section( $page, $section, $show_title = true ) {

			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[$page] ) || ! isset( $wp_settings_sections[$page][$section] ) ) {
				return;
			}

			$section = $wp_settings_sections[$page][$section];

			if ( $section['title'] && $show_title === true ) {
				echo "<h4>{$section['title']}</h4>\n";
			}

			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[$page] ) || ! isset( $wp_settings_fields[$page][$section['id']] ) ) {
				return;
			}

			echo '<table class="form-table" id="' . $section['id'] . '">';
			do_settings_fields( $page, $section['id'] );
			echo '</table>';

		}

		/**
		 * Calls upgrade script for older versions (pre 4.x).
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function do_upgrade() {

			new ITSEC_Setup( 'upgrade', 3064 ); //run upgrade scripts

		}

		/**
		 * Empty callback function
		 */
		public function empty_callback_function() {
		}

		/**
		 * Enqueue the styles for the admin area so WordPress can load them.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function enqueue_admin_styles() {

			global $itsec_globals;

			wp_enqueue_style( 'itsec_admin_styles' );
			do_action( $itsec_globals['plugin_url'] . 'enqueue_admin_styles' );

		}

		/**
		 * Registers admin styles and handles other items required at admin_init
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function execute_admin_init() {

			global $itsec_globals;

			wp_register_style( 'itsec_admin_styles', $itsec_globals['plugin_url'] . 'core/css/ithemes.css' );
			do_action( 'itsec_admin_init' ); //execute modules init scripts

		}

		/**
		 * Execute upgrade for version after 4.0
		 *
		 * @since 4.0.6
		 *
		 * @return void
		 */
		public function execute_upgrade() {

			global $itsec_globals;

			new ITSEC_Setup( 'upgrade', $itsec_globals['data']['build'] ); //run upgrade scripts

		}

		/**
		 * Getter for Table of Contents items.
		 *
		 * @since 4.0
		 *
		 * @return mixed array of toc items
		 */
		public function get_toc_items() {

			//Make sure global settings are in toc
			$global_toc = array(
				'id'    => 'global_options',
				'title' => 'Global Settings',
			);

			array_unshift( $this->toc_items, $global_toc );

			return $this->toc_items;

		}

		/**
		 * Loads required plugin modules.
		 *
		 *
		 * Recursively loads all modules in the modules/ folder by calling their index.php.
		 * Note: Do not modify this area other than to specify modules to load.
		 * Build all functionality into the appropriate module.
		 *
		 * @since 4.0
		 *
		 * @param string $free_modules_folder location of free modules
		 * @param string $pro_modules_folder  location of pro modules
		 * @param bool   $has_pro             whether or not pro is present
		 *
		 * @return void
		 */
		public function load_modules( $free_modules_folder, $pro_modules_folder, $has_pro ) {

			global $itsec_globals;

			$modules = $itsec_globals['free_modules'];

			if ( $has_pro ) {

				$modules = array_merge( $modules, $itsec_globals['pro_modules'] );

			}

			foreach ( $modules as $module => $info ) {

				if ( $has_pro === false || ! array_key_exists( $module, $itsec_globals['pro_modules'] ) ) { //don't duplicate module if pro version already loaded

					$this->module_loader( $free_modules_folder, $module, $info );

				} else {

					$this->module_loader( $pro_modules_folder, $module, $info );

				}

			}

		}

		/**
		 * Actually does the module loading
		 *
		 * @since 4.0.27
		 *
		 * @param string $module_folder the location of the module
		 * @param string $module        the name of the module
		 * @param        $info          array of module info for loading
		 *
		 * @return void
		 */
		private function module_loader( $module_folder, $module, $info ) {

			$run_front = false;
			$run_admin = false;
			$front     = NULL;

			if ( is_admin() ) {

				if ( isset( $info['has_front'] ) && $info['has_front'] === true ) { //load front-end classes in admin regardless

					$run_front = true;

				}

				$run_admin = true;

			} else {

				//Front end class loading
				if ( isset( $info['has_front'] ) && $info['has_front'] === true ) {

					$option = isset( $info['option'] ) ? get_site_option( $info['option'] ) : false;

					//If there is a setting to check and it is write then load it
					if ( isset( $info['setting'] ) && ! is_array( $info['value'] ) && isset( $option[$info['setting']] ) && $option[$info['setting']] == $info['value'] ) {

						$run_front = true;

						//check an array of settings
					} elseif ( isset( $info['setting'] ) && is_array( $info['value'] ) ) {

						foreach ( $info['value'] as $value ) {

							if ( isset( $option[$info['setting']] ) && $option[$info['setting']] == $value ) {

								$run_front = true;

							}

						}

						//Always load front-end class, not setting dependent
					} elseif ( ! isset( $info['setting'] ) ) {

						$run_front = true;

					}

				}

			}

			if ( $run_front === true ) { //load the front end class

				$front_file = $module_folder . '/' . $module . '/class-itsec-' . $module . '.php';

				if ( file_exists( $front_file ) ) {

					$front_class = 'ITSEC_' . $info['class_id'];

					if ( ! class_exists( $front_class ) ) {
						require( $front_file );
					}

					$front = new $front_class;

					if ( method_exists( $front, 'run' ) ) {
						$front->run( $this );
					}

				}

			}

			if ( $run_admin === true ) { //load the admin class

				$admin_file = $module_folder . '/' . $module . '/class-itsec-' . $module . '-admin.php';

				if ( file_exists( $admin_file ) ) {

					//Always load the admin class
					$admin_class = 'ITSEC_' . $info['class_id'] . '_Admin';

					if ( ! class_exists( $admin_class ) ) {
						require( $admin_file );
					}

					$admin = new $admin_class;

					if ( method_exists( $admin, 'run' ) ) {
						$admin->run( $this, $front );
					}

				}

			}

		}

		/**
		 * Enqueue JavaScripts for admin page rendering amd execute calls to add further meta_boxes.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function page_actions() {

			do_action( 'itsec_add_admin_meta_boxes', $this->available_pages );

			//Set two columns for all plugins using this framework
			add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );

			//Enqueue common scripts and try to keep it simple
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );

		}

		/**
		 * Prints network admin notices.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function print_network_admin_notice() {

			global $itsec_saved_network_notices;

			echo $itsec_saved_network_notices;

			unset( $itsec_saved_network_notices ); //delete any saved messages

		}

		/**
		 * Register modules that will use the lockout service
		 *
		 * @return void
		 */
		public function register_modules() {

			global $itsec_globals;

			$this->tooltip_modules = apply_filters( 'itsec_tooltip_modules', $this->tooltip_modules );
			$this->tracking_vars   = apply_filters( 'itsec_tracking_vars', $this->tracking_vars );
			$this->one_click       = apply_filters( 'itsec_one_click_settings', $this->one_click );
			$this->pages           = apply_filters( 'itsec_pages', $this->pages );

			uasort( $this->pages, array( $this, 'sort_pages' ) );

		}

		/**
		 * Register backups for tooltips
		 *
		 * @param  array $tooltip_modules array of tooltip modules
		 *
		 * @return array                   array of tooltip modules
		 */
		public function register_tooltip( $tooltip_modules ) {

			$tooltip_modules['one-click'] = array(
				'priority'  => 1,
				'class'     => 'itsec_tooltip_one-click',
				'heading'   => __( 'Secure Your Site', 'it-l10n-better-wp-security' ),
				'text'      => __( 'Use the button below to enable default settings. This feature will enable all settings that cannot conflict with other plugins or themes.', 'it-l10n-better-wp-security' ),
				'link_text' => __( 'One-Click Secure', 'it-l10n-better-wp-security' ),
				'callback'  => array( $this, 'tooltip_ajax' ),
				'success'   => __( 'Site Secured. Check the dashboard for further suggestions on securing your site.', 'it-l10n-better-wp-security' ),
				'failure'   => __( 'Whoops. Something went wrong. Please contact support if the problem persists.', 'it-l10n-better-wp-security' ),
			);

			return $tooltip_modules;

		}

		/**
		 * Render basic structure of the settings page.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function render_page() {

			global $itsec_globals;

			if ( is_multisite() ) {
				$screen = substr( get_current_screen()->id, 0, strpos( get_current_screen()->id, '-network' ) );
			} else {
				$screen = get_current_screen()->id; //the current screen id
			}

			?>

			<div class="wrap">

				<?php
				if ( ! isset( $itsec_globals['data']['tooltips_dismissed'] ) || $itsec_globals['data']['tooltips_dismissed'] === false || ( isset( $_GET['show_admin_modal'] ) && $_GET['show_admin_modal'] == 'true' ) ) {
					$this->admin_modal();
				}
				?>

				<h2><?php echo $itsec_globals['plugin_name'] . ' - ' . get_admin_page_title(); ?></h2>
				<?php
				if ( isset ( $_GET['page'] ) ) {
					$this->admin_tabs( $_GET['page'] );
				} else {
					$this->admin_tabs();
				}
				?>

				<?php
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				?>

				<div id="poststuff">

					<?php
					//set appropriate action for multisite or standard site
					if ( is_multisite() ) {
						$action = '';
					} else {
						$action = 'options.php';
					}
					?>
					<?php if ($screen == 'security_page_toplevel_page_itsec_settings' || $screen == 'security_page_toplevel_page_itsec_pro') { ?>
					<form name="<?php echo $screen; ?>" method="post"
					      action="<?php echo $action; ?>" class="itsec-settings-form">
						<?php } ?>

						<div id="post-body"
						     class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">

							<div id="postbox-container-2" class="postbox-container">
								<?php do_action( 'itsec_page_top', $screen ); ?>
								<?php do_meta_boxes( $screen, 'top', NULL ); ?>
								<?php do_meta_boxes( $screen, 'normal', NULL ); ?>
								<?php do_action( 'itsec_page_middle', $screen ); ?>
								<?php do_meta_boxes( $screen, 'advanced', NULL ); ?>
								<?php do_meta_boxes( $screen, 'bottom', NULL ); ?>
								<?php do_action( 'itsec_page_bottom', $screen ); ?>
							</div>

							<div id="postbox-container-1" class="postbox-container">
								<?php do_meta_boxes( $screen, 'priority_side', NULL ); ?>
								<?php do_meta_boxes( $screen, 'side', NULL ); ?>
								<?php if ( $screen == 'security_page_toplevel_page_itsec_settings' || $screen == 'security_page_toplevel_page_itsec_pro' ) { ?>
									<a href="#"
									   class="itsec_return_to_top"><?php _e( 'Return to top', 'it-l10n-better-wp-security' ); ?></a>
								<?php } ?>
							</div>


						</div>

						<?php if ($screen == 'security_page_toplevel_page_itsec_settings' || $screen == 'security_page_toplevel_page_itsec_pro') { ?>
					</form>
				<?php } ?>
					<!-- #post-body -->

				</div>
				<!-- #poststuff -->

			</div><!-- .wrap -->

		<?php
		}

		/**
		 * Saves general plugin data to determine global items.
		 *
		 * Sets up general plugin data such as build, and others.
		 *
		 * @since 4.0
		 *
		 * @return array plugin data
		 */
		public function save_plugin_data() {

			global $itsec_globals;

			$save_data = false; //flag to avoid saving data if we don't have to

			$plugin_data = get_site_option( 'itsec_data' );

			//Update the build number if we need to
			if ( ! isset( $plugin_data['build'] ) || ( isset( $plugin_data['build'] ) && $plugin_data['build'] !== $itsec_globals['plugin_build'] ) ) {
				$plugin_data['build'] = $itsec_globals['plugin_build'];
				$save_data            = true;
			}

			//update the activated time if we need to in order to tell when the plugin was installed
			if ( ! isset( $plugin_data['activation_timestamp'] ) ) {
				$plugin_data['activation_timestamp'] = $itsec_globals['current_time_gmt'];
				$save_data                           = true;
			}

			//update the activated time if we need to in order to tell when the plugin was installed
			if ( ! isset( $plugin_data['already_supported'] ) ) {
				$plugin_data['already_supported'] = false;
				$save_data                        = true;
			}

			//update the activated time if we need to in order to tell when the plugin was installed
			if ( ! isset( $plugin_data['setup_completed'] ) ) {
				$plugin_data['setup_completed'] = false;
				$save_data                      = true;
			}

			//update the tooltips dismissed
			if ( ! isset( $plugin_data['tooltips_dismissed'] ) ) {
				$plugin_data['tooltips_dismissed'] = false;
				$save_data                         = true;
			}

			//update the options table if we have to
			if ( $save_data === true ) {
				update_site_option( 'itsec_data', $plugin_data );
			}

			return $plugin_data;

		}

		/**
		 * Handles the building of admin menus and calls required functions to render admin pages.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function setup_primary_admin() {

			global $itsec_globals;

			$this->available_pages[] = add_menu_page(
				__( 'Dashboard', 'it-l10n-better-wp-security' ),
				__( 'Security', 'it-l10n-better-wp-security' ),
				$itsec_globals['plugin_access_lvl'],
				'itsec',
				array( $this, 'render_page' )
			);

			foreach ( $this->pages as $page ) {

				if ( isset( $page['link'] ) ) {

					$this->available_pages[] = add_submenu_page(
						'itsec',
						$page['title'],
						$page['title'],
						$itsec_globals['plugin_access_lvl'],
						$page['link'],
						array( $this, 'empty_callback_function' )
					);

				} else {

					$this->available_pages[] = add_submenu_page(
						'itsec',
						$page['title'],
						$page['title'],
						$itsec_globals['plugin_access_lvl'],
						$this->available_pages[0] . '_' . $page['slug'],
						array( $this, 'render_page' )
					);

				}

			}

			//Make the dashboard is named correctly
			global $submenu;

			if ( isset( $submenu['itsec'] ) ) {
				$submenu['itsec'][0][0] = __( 'Dashboard', 'it-l10n-better-wp-security' );
			}

			foreach ( $this->available_pages as $page ) {

				add_action( 'load-' . $page, array( $this, 'page_actions' ) ); //Load page structure
				add_action( 'admin_print_styles-' . $page, array( $this, 'enqueue_admin_styles' ) ); //Load admin styles

			}

		}

		/**
		 * Setup and call admin messages.
		 *
		 * Sets up messages and registers actions for WordPress admin messages.
		 *
		 * @since 4.0
		 *
		 * @param object $messages WordPress error object or string of message to display
		 *
		 * @return void
		 */
		public function show_network_admin_notice( $errors ) {

			global $itsec_saved_network_notices; //use global to transfer to add_action callback

			$itsec_saved_network_notices = ''; //initialize so we can get multiple error messages (if needed)

			if ( function_exists( 'apc_store' ) ) {
				apc_clear_cache(); //Let's clear APC (if it exists) when big stuff is saved.
			}

			if ( ( ! defined( 'DOING_AJAX' ) || DOING_AJAX == false ) && function_exists( 'get_current_screen' ) && isset( get_current_screen()->id ) && ( strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_settings' ) !== false || strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_advanced' ) !== false || strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_pro' ) !== false ) ) {

				if ( $errors === false && isset ( $_GET['settings-updated'] ) && sanitize_text_field( $_GET['settings-updated'] ) == 'true' ) {

					$updated = '';

					if ( get_site_option( 'itsec_manual_update' ) == true ) {

						delete_site_option( 'itsec_manual_update' );

						if ( ITSEC_Lib::get_server() == 'nginx' ) {

							$server = __( 'NGINX conf file and/or restart your NGINX server', 'it-l10n-better-wp-security' );

						} else {

							$server = __( '.htaccess file', 'it-l10n-better-wp-security' );

						}

						$updated = sprintf(
							'<br />%s %s %s',
							__( 'As you have not allowed this plugin to update system files you must update your', 'it-l10n-better-wp-security' ),
							$server,
							__( 'as well as your wp-config.php file manually. Rules to insert in both files can be found on the Dashboard page.', 'it-l10n-better-wp-security' )
						);

					}

					$itsec_saved_network_notices = '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>' . __( 'Settings Updated', 'it-l10n-better-wp-security' ) . $updated . '</strong></p></div>';

				} elseif ( is_wp_error( $errors ) ) { //see if object is even an error

					$error_messages = $errors->get_error_messages(); //get all errors if it is

					$type = key( $errors->errors );

					foreach ( $error_messages as $error ) {

						$itsec_saved_network_notices = '<div id="setting-error-settings_updated" class="' . sanitize_text_field( $type ) . ' settings-error"><p><strong>' . sanitize_text_field( $error ) . '</strong></p></div>';
					}

				}

				//register appropriate message actions
				add_action( 'admin_notices', array( $this, 'print_network_admin_notice' ) );
				add_action( 'network_admin_notices', array( $this, 'print_network_admin_notice' ) );

			}

		}

		/**
		 * Sorts pages from lowest priority to highest.
		 *
		 * @since 4.0
		 *
		 * @param array $a page
		 * @param array $b page
		 *
		 * @return int 1 if a is a lower priority, -1 if b is a lower priority, 0 if equal
		 */
		public function sort_pages( $a, $b ) {

			if ( $a['priority'] == $b['priority'] ) {
				return 0;
			}

			return ( $a['priority'] > $b['priority'] ? 1 : - 1 );

		}

		/**
		 * Sorts tooltips from highest priority to lowest.
		 *
		 * @since 4.0
		 *
		 * @param array $a tooltip
		 * @param array $b tooltip
		 *
		 * @return int 1 if a is a lower priority, -1 if b is a lower priority, 0 if equal
		 */
		public function sort_tooltips( $a, $b ) {

			if ( $a['priority'] == $b['priority'] ) {
				return 0;
			}

			return ( $a['priority'] < $b['priority'] ? 1 : - 1 );

		}

		/**
		 * Performs actions for tooltip function.
		 *
		 * @since 4.0
		 *
		 * return void
		 */
		public function tooltip_ajax() {

			foreach ( $this->one_click as $setting => $option_pair ) {

				$saved_setting = get_site_option( $setting );

				foreach ( $option_pair as $option ) {
					$saved_setting[$option['option']] = $option['value'];
				}

				update_site_option( $setting, $saved_setting );

			}

			echo 'true';

		}

		/**
		 * Display (and hide) upgrade reminder.
		 *
		 * This will display a notice to the admin of the site only asking them to support
		 * the plugin after they have used it for 30 days.
		 *
		 * @since 4.0
		 *
		 * @return void
		 */
		public function upgrade_nag() {

			global $blog_id;

			if ( is_multisite() && ( $blog_id != 1 || ! current_user_can( 'manage_network_options' ) ) ) { //only display to network admin if in multisite
				return;
			}

			//display the notifcation if they haven't turned it off and they've been using the plugin at least 30 days
			if ( get_site_option( 'itsec_had_other_version' ) !== false ) {

				if ( ! function_exists( 'ithemes_plugin_upgrade_notice' ) ) {

					function ithemes_plugin_upgrade_notice() {

						global $itsec_globals;

						echo '<div class="updated" id="itsec_upgrade_notice">
						<span>' . __( 'Thank you for activating', 'it-l10n-better-wp-security' ) . ' ' . $itsec_globals['plugin_name'] . '. ' . __( 'It looks like you had another version of this plugin activated. To avoid conflicts the extra version has been deactivated and we recommend you delete it.', 'it-l10n-better-wp-security' ) . '</span><a class="itsec-notice-hide" onclick="document.location.href=\'?itsec_no_upgrade_nag=off&_wpnonce=' . wp_create_nonce( 'itsec-nag' ) . '\';">&times;</a>
						</div>';

					}

				}

				if ( is_multisite() ) {
					add_action( 'network_admin_notices', 'ithemes_plugin_upgrade_notice' ); //register notification
				} else {
					add_action( 'admin_notices', 'ithemes_plugin_upgrade_notice' ); //register notification
				}

			}

			//if they've clicked a button hide the notice
			if ( isset( $_GET['itsec_no_upgrade_nag'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'itsec-nag' ) ) {

				delete_site_option( 'itsec_had_other_version' );

				$options = get_site_option( 'itsec_data' );

				$options['already_supported'] = true;

				update_site_option( 'itsec_data', $options );

				if ( is_multisite() ) {
					remove_action( 'network_admin_notices', 'ithemes_plugin_upgrade_notice' );
				} else {
					remove_action( 'admin_notices', 'ithemes_plugin_upgrade_notice' );
				}

				if ( sanitize_text_field( $_GET['itsec_no_upgrade_nag'] ) == 'off' && isset( $_SERVER['HTTP_REFERER'] ) ) {

					wp_redirect( $_SERVER['HTTP_REFERER'], '302' );

				} else {

					wp_redirect( 'admin.php?page=itsec', '302' );

				}

			}

		}

	}

}
