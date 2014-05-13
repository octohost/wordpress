<?php

class ITSEC_Admin_User_Admin {

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

		$id    = 'admin_user_options';
		$title = __( 'Admin User', 'it-l10n-better-wp-security' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_admin_user_settings' ),
			'security_page_toplevel_page_itsec_advanced',
			'advanced',
			'core'
		);

	}

	/**
	 * Add Away mode Javascript
	 *
	 * @return void
	 */
	public function admin_script() {

		global $itsec_globals;

		if ( isset( get_current_screen()->id ) && strpos( get_current_screen()->id, 'security_page_toplevel_page_itsec_advanced' ) !== false ) {

			wp_enqueue_script( 'itsec_admin_user_js', $this->module_path . 'js/admin-admin-user.js', array( 'jquery' ), $itsec_globals['plugin_build'] );

		}

	}

	/**
	 * Changes Admin User
	 *
	 * Changes the username and id of the 1st user
	 *
	 * @param string $username the username to change if changing at the same time
	 * @param bool   $id       whether to change the id as well
	 *
	 * @return bool success or failure
	 *
	 **/
	private function change_admin_user( $username = null, $id = false ) {

		global $itsec_files, $wpdb;

		if ( $itsec_files->get_file_lock( 'admin_user' ) ) { //make sure it isn't already running

			//sanitize the username
			$new_user = sanitize_text_field( $username );

			//Get the full user object
			$user_object = get_user_by( 'id', '1' );

			if ( $username !== null && validate_username( $new_user ) && username_exists( $new_user ) === null ) { //there is a valid username to change

				if ( $id === true ) { //we're changing the id too so we'll set the username

					$user_login = $new_user;

				} else { // we're only changing the username

					//query main user table
					$wpdb->query( "UPDATE `" . $wpdb->users . "` SET user_login = '" . esc_sql( $new_user ) . "' WHERE user_login='admin';" );

					if ( is_multisite() ) { //process sitemeta if we're in a multi-site situation

						$oldAdmins = $wpdb->get_var( "SELECT meta_value FROM `" . $wpdb->sitemeta . "` WHERE meta_key = 'site_admins'" );
						$newAdmins = str_replace( '5:"admin"', strlen( $new_user ) . ':"' . esc_sql( $new_user ) . '"', $oldAdmins );
						$wpdb->query( "UPDATE `" . $wpdb->sitemeta . "` SET meta_value = '" . esc_sql( $newAdmins ) . "' WHERE meta_key = 'site_admins'" );

					}

					wp_clear_auth_cookie();
					$itsec_files->release_file_lock( 'admin_user' );

					return true;

				}

			} elseif ( $username !== null ) { //username didn't validate

				$itsec_files->release_file_lock( 'admin_user' );

				return false;

			} else { //only changing the id

				$user_login = $user_object->user_login;

			}

			if ( $id === true ) { //change the user id

				$wpdb->query( "DELETE FROM `" . $wpdb->users . "` WHERE ID = 1;" );

				$wpdb->insert( $wpdb->users, array( 'user_login' => $user_login, 'user_pass' => $user_object->user_pass, 'user_nicename' => $user_object->user_nicename, 'user_email' => $user_object->user_email, 'user_url' => $user_object->user_url, 'user_registered' => $user_object->user_registered, 'user_activation_key' => $user_object->user_activation_key, 'user_status' => $user_object->user_status, 'display_name' => $user_object->display_name ) );

				if ( is_multisite() && $username !== null && validate_username( $new_user ) ) { //process sitemeta if we're in a multi-site situation

					$oldAdmins = $wpdb->get_var( "SELECT meta_value FROM `" . $wpdb->sitemeta . "` WHERE meta_key = 'site_admins'" );
					$newAdmins = str_replace( '5:"admin"', strlen( $new_user ) . ':"' . esc_sql( $new_user ) . '"', $oldAdmins );
					$wpdb->query( "UPDATE `" . $wpdb->sitemeta . "` SET meta_value = '" . esc_sql( $newAdmins ) . "' WHERE meta_key = 'site_admins'" );

				}

				$new_user = $wpdb->insert_id;

				$wpdb->query( "UPDATE `" . $wpdb->posts . "` SET post_author = '" . $new_user . "' WHERE post_author = 1;" );
				$wpdb->query( "UPDATE `" . $wpdb->usermeta . "` SET user_id = '" . $new_user . "' WHERE user_id = 1;" );
				$wpdb->query( "UPDATE `" . $wpdb->comments . "` SET user_id = '" . $new_user . "' WHERE user_id = 1;" );
				$wpdb->query( "UPDATE `" . $wpdb->links . "` SET link_owner = '" . $new_user . "' WHERE link_owner = 1;" );

				wp_clear_auth_cookie();
				$itsec_files->release_file_lock( 'admin_user' );

				return true;

			}

		}

		return false;

	}

	/**
	 * Sets the status in the plugin dashboard
	 *
	 * @since 4.0
	 *
	 * @return array array of statuses
	 */
	public function dashboard_status( $statuses ) {

		if ( ! username_exists( 'admin' ) ) {

			$status_array = 'safe-high';
			$status       = array( 'text' => __( 'The <em>admin</em> user has been removed or renamed.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_authentication_admin_user_username', 'advanced' => true, );

		} else {

			$status_array = 'high';
			$status       = array( 'text' => __( 'The <em>admin</em> user still exists.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_authentication_admin_user_username', 'advanced' => true, );

		}

		array_push( $statuses[$status_array], $status );

		if ( ! ITSEC_Lib::user_id_exists( 1 ) ) {

			$status_array = 'safe-medium';
			$status       = array( 'text' => __( 'The user with id 1 has been removed.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_authentication_admin_user_userid', 'advanced' => true, );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'A user with id 1 still exists.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_authentication_admin_user_userid', 'advanced' => true, );

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

		$this->core = $core;

		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) ); //enqueue scripts for admin page
		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area
		add_filter( 'itsec_add_dashboard_status', array( $this, 'dashboard_status' ) ); //add information for plugin status

	}

	/**
	 * Execute admin initializations
	 *
	 * @return void
	 */
	public function initialize_admin() {

		if ( ITSEC_Lib::user_id_exists( 1 ) || username_exists( 'admin' ) ) {
			$this->settings = false;
		} else {
			$this->settings = true;
		}

		if ( ! $this->settings === true && isset( $_POST['itsec_enable_admin_user'] ) && $_POST['itsec_enable_admin_user'] == 'true' ) {

			//Process admin user
			$username    = isset( $_POST['itsec_admin_user_username'] ) ? trim( sanitize_text_field( $_POST['itsec_admin_user_username'] ) ) : null;
			$change_id_1 = ( isset( $_POST['itsec_admin_user_id'] ) && intval( $_POST['itsec_admin_user_id'] == 1 ) ? true : false );

			$admin_success = true;

			if ( strlen( $username ) >= 1 ) {

				$admin_success = $this->change_admin_user( $username, $change_id_1 );

			} elseif ( $change_id_1 === true ) {

				$admin_success = $this->change_admin_user( null, $change_id_1 );

			}

			if ( $admin_success === false ) {

				$type    = 'error';
				$message = __( 'The new admin username you entered is invalid or WordPress could not change the user id or username. Please check the name and try again.', 'it-l10n-better-wp-security' );

				add_settings_error( 'itsec', esc_attr( 'settings_updated' ), $message, $type );

			}

			if ( is_multisite() ) {

				if ( isset( $type ) ) {

					$error_handler = new WP_Error();

					$error_handler->add( $type, $message );

					$this->core->show_network_admin_notice( $error_handler );

				} else {

					$this->core->show_network_admin_notice( false );

				}

				$this->settings = true;

			}

			if ( $admin_success === true ) {

				$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : ITSEC_Lib::get_home_root() . 'wp-login.php?loggedout=true';
				wp_safe_redirect( $redirect_to );

			}

		}

	}

	/**
	 * Render the settings metabox
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_admin_user_settings() {

		if ( $this->settings === true ) {

			echo '<p>' . __( 'It looks like you have already removed the admin user. No further action is necessary.', 'it-l10n-better-wp-security' ) . '</p>';

		} else {

			echo '<p>' . __( 'This feature will improve the security of your WordPress installation by removing common user attributes that can be used to target your site.', 'it-l10n-better-wp-security' ) . '</p>';
			echo sprintf( '<div class="itsec-warning-message"><span>%s: </span><a href="?page=toplevel_page_itsec-backup">%s</a> %s</div>', __( 'WARNING', 'it-l10n-better-wp-security' ), __( 'Backup your database', 'it-l10n-better-wp-security' ), __( 'before changing the admin user.', 'it-l10n-better-wp-security' ) );
			echo sprintf( '<div class="itsec-notice-message"><span>%s: </span> %s </div>', __( 'Notice', 'it-l10n-better-wp-security' ), __( 'Changing the admin username or id of user 1 will log you out of your site requiring you to log back in again.', 'it-l10n-better-wp-security' ) );

			?>

			<form method="post" action="?page=toplevel_page_itsec_advanced&settings-updated=true"
			      class="itsec-form">

				<?php wp_nonce_field( 'ITSEC_admin_save', 'wp_nonce' ); ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row" class="settinglabel">
							<label
								for="itsec_enable_admin_user"><?php _e( 'Enable Change Admin User', 'it-l10n-better-wp-security' ); ?></label>
						</th>
						<td class="settingfield">
							<?php //username field ?>
							<input type="checkbox" id="itsec_enable_admin_user" name="itsec_enable_admin_user"
							       value="true"/>

							<p class="description"><?php _e( 'Check this box to enable admin user renaming.', 'it-l10n-better-wp-security' ); ?></p>
						</td>
					</tr>

					<?php if ( username_exists( 'admin' ) ) { ?>
						<tr valign="top" id="admin_user_username_field">
							<th scope="row" class="settinglabel">
								<label
									for="itsec_admin_user_username"><?php _e( 'New Admin Username', 'it-l10n-better-wp-security' ); ?></label>
							</th>
							<td class="settingfield">
								<?php //username field ?>
								<input name="itsec_admin_user_username" id="itsec_admin_user_username" value=""
								       type="text"><br/>

								<p class="description"><?php _e( 'Enter a new username to replace "admin." Please note that if you are logged in as admin you will have to log in again.', 'it-l10n-better-wp-security' ); ?></p>
							</td>
						</tr>
					<?php } ?>
					<?php if ( ITSEC_Lib::user_id_exists( 1 ) ) { ?>
						<tr valign="top" id="admin_user_id_field">
							<th scope="row" class="settinglabel">
								<label
									for="itsec_admin_user_id"><?php _e( 'Change User ID 1', 'it-l10n-better-wp-security' ); ?></label>
							</th>
							<td class="settingfield">
								<?php //username field ?>
								<input type="checkbox" id="itsec_admin_user_id" name="itsec_admin_user_id" value="1"/>

								<p class="description"><?php _e( 'Change the ID of the user with ID 1.', 'it-l10n-better-wp-security' ); ?></p>
							</td>
						</tr>
					<?php } ?>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary"
					       value="<?php _e( 'Save Admin User', 'it-l10n-better-wp-security' ); ?>"/>
				</p>
			</form>

		<?php

		}

	}

}