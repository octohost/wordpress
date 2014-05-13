<?php

class ITSEC_Database_Prefix_Admin {

	private $settings, $core;

	function run( $core ) {

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

		//add metaboxes
		add_meta_box(
			'database_prefix_options',
			__( 'Change Database Prefix', 'it-l10n-better-wp-security' ),
			array( $this, 'metabox_advanced_settings' ),
			'security_page_toplevel_page_itsec_advanced',
			'advanced',
			'core'
		);

	}

	/**
	 * Sets the status in the plugin dashboard
	 *
	 * @since 4.0
	 *
	 * @return array statuses
	 */
	public function dashboard_status( $statuses ) {

		if ( $this->settings !== true ) {

			$status_array = 'safe-medium';
			$status       = array( 'text' => sprintf( '%s wp_.', __( 'Your database table prefix is not using', 'it-l10n-better-wp-security' ) ), 'link' => '#itsec_change_table_prefix', 'advanced' => true, );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => sprintf( '%s wp_.', __( 'Your database table prefix should not be', 'it-l10n-better-wp-security' ) ), 'link' => '#itsec_change_table_prefix', 'advanced' => true, );

		}

		array_push( $statuses[$status_array], $status );

		return $statuses;

	}

	/**
	 * Execute admin initializations
	 *
	 * @return void
	 */
	public function initialize_admin() {

		if ( isset( $_POST['itsec_change_table_prefix'] ) && $_POST['itsec_change_table_prefix'] == 'true' ) {

			if ( ! wp_verify_nonce( $_POST['wp_nonce'], 'ITSEC_admin_save' ) ) {

				die( __( 'Security check', 'it-l10n-better-wp-security' ) );

			}

			$this->process_database_prefix();

		}

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

		global $wpdb;

		$this->core = $core;

		if ( $wpdb->base_prefix === 'wp_' ) {
			$this->settings = true;
		} else {
			$this->settings = false;
		}

		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area
		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_filter( 'itsec_add_dashboard_status', array( $this, 'dashboard_status' ) ); //add information for plugin status
		add_filter( 'itsec_tracking_vars', array( $this, 'tracking_vars' ) );

	}

	/**
	 * Render the settings metabox
	 *
	 * @return void
	 */
	public function metabox_advanced_settings() {

		$content = '<p>' . __( 'By default, WordPress assigns the prefix "wp" to all tables in the database where your content, users, and objects exist. For potential attackers, this means it is easier to write scripts that can target WordPress databases as all the important table names for 95% of sites are already known. Changing the "wp" prefix makes it more difficult for tools that are trying to take advantage of vulnerabilities in other places to affect the database of your site.', 'it-l10n-better-wp-security' ) . '<strong>' . __( 'Before using this tool, we strongly recommend running a backup of your database.', 'it-l10n-better-wp-security' ) . '</strong></p>';
		$content .= '<p>' . __( 'Note: The use of this tool requires quite a bit of system memory which may be more than some hosts can handle. If you back your database up you can\'t do any permanent damage but without a proper backup you risk breaking your site and having to perform a rather difficult fix.', 'it-l10n-better-wp-security' ) . '</p>';
		$content .= sprintf( '<div class="itsec-warning-message"><span>%s: </span><a href="?page=toplevel_page_itsec_backups">%s</a> %s</div>', __( 'WARNING', 'it-l10n-better-wp-security' ), __( 'Backup your database', 'it-l10n-better-wp-security' ), __( 'before using this tool.', 'it-l10n-better-wp-security' ) );

		echo $content;

		global $itsec_globals;

		if ( isset( $itsec_globals['settings']['write_files'] ) && $itsec_globals['settings']['write_files'] === true ) {

			global $wpdb;

			if ( $this->settings === true ) { //Show the correct info

				?>
				<p><strong><?php _e( 'Your database is using the default table prefix', 'it-l10n-better-wp-security' ); ?>
						<em>wp_</em>. <?php _e( 'You should change this.', 'it-l10n-better-wp-security' ); ?></strong></p>
			<?php

			} else {

				$prefix = $this->settings === false ? $wpdb->base_prefix : $this->settings;

				?>
				<p><?php _e( 'Your current database table prefix is', 'it-l10n-better-wp-security' ); ?>
					<strong><em><?php echo $prefix; ?></em></strong></p>
			<?php

			}

			?>
			<form method="post" action="?page=toplevel_page_itsec_advanced&settings-updated=true" class="itsec-form">

				<?php wp_nonce_field( 'ITSEC_admin_save', 'wp_nonce' ); ?>

				<table class="form-table">
					<tbody>
					<tr valign="top">
						<th scope="row" class="settinglabel">
							<label
								for="itsec_change_table_prefix"><?php _e( 'Change Table Prefix', 'it-l10n-better-wp-security' ); ?></label>
						</th>
						<td class="settingfield">

							<input type="checkbox" id="itsec_change_table_prefix" name="itsec_change_table_prefix"
							       value="true"/>

							<p class="description"><?php _e( 'Check this box to generate a new database table prefix.', 'it-l10n-better-wp-security' ); ?></p>
						</td>
					</tr>
					</tbody>
				</table>

				<p class="submit">
					<input type="submit" class="button-primary"
					       value="<?php _e( 'Change Database Prefix', 'it-l10n-better-wp-security' ); ?>"/>
				</p>
			</form>

		<?php

		} else {

			$content = sprintf(
				'<p>%s <a href="?page=toplevel_page_itsec_settings">%s</a> %s',
				__( 'You must allow this plugin to write to the wp-config.php file on the', 'it-l10n-better-wp-security' ),
				__( 'Settings', 'it-l10n-better-wp-security' ),
				__( 'page to use this feature.', 'it-l10n-better-wp-security' )
			);

			echo $content;

		}

	}

	/**
	 * Sanitize and validate input
	 *
	 */
	public function process_database_prefix() {

		global $wpdb, $itsec_files;

		//suppress error messages due to timing
		error_reporting( 0 );
		@ini_set( 'display_errors', 0 );

		$check_prefix = true; //Assume the first prefix we generate is unique

		//generate a new table prefix that doesn't conflict with any other in use in the database
		while ( $check_prefix ) {

			$avail = 'abcdefghijklmnopqrstuvwxyz0123456789';

			//first character should be alpha
			$new_prefix = $avail[mt_rand( 0, 25 )];

			//length of new prefix
			$prelength = mt_rand( 4, 9 );

			//generate remaning characters
			for ( $i = 0; $i < $prelength; $i ++ ) {
				$new_prefix .= $avail[mt_rand( 0, 35 )];
			}

			//complete with underscore
			$new_prefix .= '_';

			$new_prefix = esc_sql( $new_prefix ); //just be safe

			$check_prefix = $wpdb->get_results( 'SHOW TABLES LIKE "' . $new_prefix . '%";', ARRAY_N ); //if there are no tables with that prefix in the database set checkPrefix to false

		}

		//assume this will work
		$type    = 'updated';
		$message = __( 'Settings Updated', 'it-l10n-better-wp-security' );

		$tables = $wpdb->get_results( 'SHOW TABLES LIKE "' . $wpdb->base_prefix . '%"', ARRAY_N ); //retrieve a list of all tables in the DB

		//Rename each table
		foreach ( $tables as $table ) {

			$table = substr( $table[0], strlen( $wpdb->base_prefix ), strlen( $table[0] ) ); //Get the table name without the old prefix

			//rename the table and generate an error if there is a problem
			if ( $wpdb->query( 'RENAME TABLE `' . $wpdb->base_prefix . $table . '` TO `' . $new_prefix . $table . '`;' ) === false ) {

				$type    = 'error';
				$message = sprintf( '%s %s%s. %s', __( 'Error: Could not rename table', 'it-l10n-better-wp-security' ), $wpdb->base_prefix, $table, __( 'You may have to rename the table manually.', 'it-l10n-better-wp-security' ) );

				add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

			}

		}

		if ( is_multisite() ) { //multisite requires us to rename each blogs' options

			$blogs = $wpdb->get_col( "SELECT blog_id FROM `" . $new_prefix . "blogs` WHERE public = '1' AND archived = '0' AND mature = '0' AND spam = '0' ORDER BY blog_id DESC" ); //get list of blog id's

			if ( is_array( $blogs ) ) { //make sure there are other blogs to update

				//update each blog's user_roles option
				foreach ( $blogs as $blog ) {

					$wpdb->query( 'UPDATE `' . $new_prefix . $blog . '_options` SET option_name = "' . $new_prefix . $blog . '_user_roles" WHERE option_name = "' . $wpdb->base_prefix . $blog . '_user_roles" LIMIT 1;' );

				}

			}

		}

		$upOpts = $wpdb->query( 'UPDATE `' . $new_prefix . 'options` SET option_name = "' . $new_prefix . 'user_roles" WHERE option_name = "' . $wpdb->base_prefix . 'user_roles" LIMIT 1;' ); //update options table and set flag to false if there's an error

		if ( $upOpts === false ) { //set an error

			$type    = 'error';
			$message = __( 'Could not update prefix references in options table.', 'it-l10n-better-wp-security' );;

			add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

		}

		$rows = $wpdb->get_results( 'SELECT * FROM `' . $new_prefix . 'usermeta`' ); //get all rows in usermeta

		//update all prefixes in usermeta
		foreach ( $rows as $row ) {

			if ( substr( $row->meta_key, 0, strlen( $wpdb->base_prefix ) ) == $wpdb->base_prefix ) {

				$pos = $new_prefix . substr( $row->meta_key, strlen( $wpdb->base_prefix ), strlen( $row->meta_key ) );

				$result = $wpdb->query( 'UPDATE `' . $new_prefix . 'usermeta` SET meta_key="' . $pos . '" WHERE meta_key= "' . $row->meta_key . '" LIMIT 1;' );

				if ( $result == false ) {

					$type    = 'error';
					$message = __( 'Could not update prefix references in usermeta table.', 'it-l10n-better-wp-security' );

					add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

				}

			}

		}

		$rules[] = array( 'type' => 'wpconfig', 'name' => 'Database Prefix', 'rules' => array( array( 'type' => 'replace', 'search_text' => 'table_prefix', 'rule' => "\$table_prefix = '" . $new_prefix . "';", ), ), );

		$itsec_files->set_wpconfig( $rules );

		$configs = $itsec_files->save_wpconfig();

		if ( is_array( $configs ) ) {

			if ( $configs['success'] === false ) {

				$type    = 'error';
				$message = $configs['text'];

				add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

			}

		} else {

			add_site_option( 'itsec_manual_update', true );

		}

		$this->settings = $new_prefix; //this tells the form field that all went well.

		if ( is_multisite() ) {

			if ( isset( $type ) ) {

				$error_handler = new WP_Error();

				$error_handler->add( $type, $message );

				$this->core->show_network_admin_notice( $error_handler );

			} else {

				$this->core->show_network_admin_notice( false );

			}

			$this->settings = false;

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

		$vars['database_prefix'] = array(
			'enabled' => '0:b',
		);

		return $vars;

	}

}