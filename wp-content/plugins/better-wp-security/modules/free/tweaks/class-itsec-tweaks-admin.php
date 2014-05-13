<?php

class ITSEC_Tweaks_Admin {

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
	 * s@since 4.0
	 *
	 * @return void
	 */
	public function add_admin_meta_boxes() {

		$id    = 'tweaks_system';
		$title = __( 'System Tweaks', 'it-l10n-better-wp-security' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_tweaks_system' ),
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

		$id    = 'tweaks_wordpress';
		$title = __( 'WordPress Tweaks', 'it-l10n-better-wp-security' );

		add_meta_box(
			$id,
			$title,
			array( $this, 'metabox_tweaks_wordpress' ),
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

		if ( is_multisite() ) {

			$id    = 'tweaks_multisite';
			$title = __( 'Multi-site Tweaks', 'it-l10n-better-wp-security' );

			add_meta_box(
				$id,
				$title,
				array( $this, 'metabox_tweaks_multisite' ),
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

	}

	/**
	 * echos Disable Directory Browsing Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_server_directory_browsing() {

		if ( isset( $this->settings['directory_browsing'] ) && $this->settings['directory_browsing'] === true ) {
			$directory_browsing = 1;
		} else {
			$directory_browsing = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_directory_browsing" name="itsec_tweaks[directory_browsing]" value="1" ' . checked( 1, $directory_browsing, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_directory_browsing">' . __( 'Disable Directory Browsing', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Prevents users from seeing a list of files in a directory when no index file is present.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Long URL Strings Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_server_long_url_strings() {

		if ( isset( $this->settings['long_url_strings'] ) && $this->settings['long_url_strings'] === true ) {
			$long_url_strings = 1;
		} else {
			$long_url_strings = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_long_url_strings" name="itsec_tweaks[long_url_strings]" value="1" ' . checked( 1, $long_url_strings, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_long_url_strings">' . __( 'Filter Long URL Strings', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Limits the number of characters that can be sent in the URL. Hackers often take advantage of long URLs to try to inject information into your database.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Filter Non-English Characters Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_server_non_english_characters() {

		if ( isset( $this->settings['non_english_characters'] ) && $this->settings['non_english_characters'] === true ) {
			$non_english_characters = 1;
		} else {
			$non_english_characters = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_non_english_characters" name="itsec_tweaks[non_english_characters]" value="1" ' . checked( 1, $non_english_characters, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_non_english_characters">' . __( 'Filter Non-English Characters', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Filter out non-english characters from the query string. This should not be used on non-english sites and only works when "Filter Suspicious Query String" has been selected.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Protect Files Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_server_protect_files() {

		if ( isset( $this->settings['protect_files'] ) && $this->settings['protect_files'] === true ) {
			$protect_files = 1;
		} else {
			$protect_files = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_protect_files" name="itsec_tweaks[protect_files]" value="1" ' . checked( 1, $protect_files, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_protect_files">' . __( 'Protect System Files', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description"> ' . __( 'Prevent public access to readme.html, readme.txt, wp-config.php, install.php, wp-includes, and .htaccess. These files can give away important information on your site and serve no purpose to the public once WordPress has been successfully installed.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Filter Request MethodsField
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_server_request_methods() {

		if ( isset( $this->settings['request_methods'] ) && $this->settings['request_methods'] === true ) {
			$request_methods = 1;
		} else {
			$request_methods = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_request_methods" name="itsec_tweaks[request_methods]" value="1" ' . checked( 1, $request_methods, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_request_methods">' . __( 'Filter Request Methods', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Filter out hits with the trace, delete, or track request methods.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Filter Suspicious Query Strings Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_server_suspicious_query_strings() {

		if ( isset( $this->settings['suspicious_query_strings'] ) && $this->settings['suspicious_query_strings'] === true ) {
			$suspicious_query_strings = 1;
		} else {
			$suspicious_query_strings = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_suspicious_query_strings" name="itsec_tweaks[suspicious_query_strings]" value="1" ' . checked( 1, $suspicious_query_strings, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_suspicious_query_strings">' . __( 'Filter Suspicious Query Strings in the URL', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'These are very often signs of someone trying to gain access to your site but some plugins and themes can also be blocked.', 'it-l10n-better-wp-security' ) . '</label>';

		echo $content;

	}

	/**
	 * echos Remove write permissions Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_server_write_permissions() {

		if ( isset( $this->settings['write_permissions'] ) && $this->settings['write_permissions'] === true ) {
			$write_permissions = 1;
		} else {
			$write_permissions = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_write_permissions" name="itsec_tweaks[write_permissions]" value="1" ' . checked( 1, $write_permissions, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_write_permissions">' . __( 'Remove File Writing Permissions', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Prevents scripts and users from being able to write to the wp-config.php file and .htaccess file. Note that in the case of this and many plugins this can be overcome however it still does make the files more secure. Turning this on will set the UNIX file permissions to 0444 on these files and turning it off will set the permissions to 0664.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Force Unique Nicename Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_disable_unused_author_pages() {

		if ( isset( $this->settings['disable_unused_author_pages'] ) && $this->settings['disable_unused_author_pages'] === true ) {
			$disable_unused_author_pages = 1;
		} else {
			$disable_unused_author_pages = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_disable_unused_author_pages" name="itsec_tweaks[disable_unused_author_pages]" value="1" ' . checked( 1, $disable_unused_author_pages, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_disable_unused_author_pages"> ' . __( "Disables a user's author page if their post count is 0.", 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description"> ' . __( "This makes it harder for bots to determine usernames by disabling post archives for users that don't post to your site.", 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Force Unique Nicename Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_force_unique_nicename() {

		if ( isset( $this->settings['force_unique_nicename'] ) && $this->settings['force_unique_nicename'] === true ) {
			$force_unique_nicename = 1;
		} else {
			$force_unique_nicename = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_force_unique_nicename" name="itsec_tweaks[force_unique_nicename]" value="1" ' . checked( 1, $force_unique_nicename, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_force_unique_nicename"> ' . __( 'Force users to choose a unique nickname', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description"> ' . __( "This forces users to choose a unique nickname when updating their profile or creating a new account which prevents bots and attackers from easily harvesting user's login usernames from the code on author pages. Note this does not automatically update existing users as it will affect author feed urls if used.", 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Disable Login Errors Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_login_errors() {

		if ( isset( $this->settings['login_errors'] ) && $this->settings['login_errors'] === true ) {
			$enabled = 1;
		} else {
			$enabled = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_login_errors" name="itsec_tweaks[login_errors]" value="1" ' . checked( 1, $enabled, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_login_errors"> ' . __( 'Disable login error messages', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description"> ' . __( 'Prevents error messages from being displayed to a user upon a failed login attempt.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Reduce Comment Spam Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_comment_spam() {

		if ( isset( $this->settings['comment_spam'] ) && $this->settings['comment_spam'] === true ) {
			$comment_spam = 1;
		} else {
			$comment_spam = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_comment_spam" name="itsec_tweaks[comment_spam]" value="1" ' . checked( 1, $comment_spam, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_comment_spam">' . __( 'Reduce Comment Spam', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'This option will cut down on comment spam by denying comments from bots with no referrer or without a user-agent identified.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Hide Core Update Notifications Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_core_updates() {

		if ( isset( $this->settings['core_updates'] ) && $this->settings['core_updates'] === true ) {
			$core_updates = 1;
		} else {
			$core_updates = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_core_updates" name="itsec_tweaks[core_updates]" value="1" ' . checked( 1, $core_updates, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_core_updates">' . __( 'Hide Core Update Notifications', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Hides core update notifications from users who cannot update core. Please note that this only makes a difference in multi-site installations.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Disable XML-RPC Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_disable_xmlrpc() {

		if ( isset( $this->settings['disable_xmlrpc'] ) && $this->settings['disable_xmlrpc'] === true ) {

			$log_type = 2;

		} elseif ( ! isset( $this->settings['disable_xmlrpc'] ) || ( isset( $this->settings['disable_xmlrpc'] ) && $this->settings['disable_xmlrpc'] === false ) ) {

			$log_type = 0;

		} elseif ( isset( $this->settings['disable_xmlrpc'] ) ) {

			$log_type = $this->settings['disable_xmlrpc'];

		}

		echo '<select id="itsec_tweaks_server_disable_xmlrpc" name="itsec_tweaks[disable_xmlrpc]">';

		echo '<option value="0" ' . selected( $log_type, '0' ) . '>' . __( 'Off', 'it-l10n-better-wp-security' ) . '</option>';
		echo '<option value="1" ' . selected( $log_type, '1' ) . '>' . __( 'Only Disable Trackbacks/Pingbacks', 'it-l10n-better-wp-security' ) . '</option>';
		echo '<option value="2" ' . selected( $log_type, '2' ) . '>' . __( 'Completely Disable XMLRPC', 'it-l10n-better-wp-security' ) . '</option>';
		echo '</select>';
		echo '<label for="itsec_tweaks_server_disable_xmlrpc"> ' . __( 'Disable XMLRPC', 'it-l10n-better-wp-security' ) . '</label>';
		printf(
			'<p class="description"><ul><li>%s</li><li>%s</li><li>%s</li></ul></p>',
			__( 'Off = XMLRPC is fully enabled and will function as normal.', 'it-l10n-better-wp-security' ),
			__( 'Only Diable Trackbacks/Pingbacks = Your site will not be susceptible to denial of service attacks via the trackback/pingback feature. Other XMLRPC features will work as normal. You need this if you require features such as Jetpack or the WordPress Mobile app.', 'it-l10n-better-wp-security' ),
			__( 'Completely Disable XMLRPC is the safest, XMLRPC will be completely disabled by your webserver. This will prevent features such as Jetpack that require XMLRPC from working.', 'it-l10n-better-wp-security' )
		);

	}

	/**
	 * echos Remove EditURI Header Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_edituri_header() {

		if ( isset( $this->settings['edituri_header'] ) && $this->settings['edituri_header'] === true ) {
			$edituri_header = 1;
		} else {
			$edituri_header = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_edituri_header" name="itsec_tweaks[edituri_header]" value="1" ' . checked( 1, $edituri_header, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_edituri_header">' . __( 'Remove the RSD (Really Simple Discovery) header. ', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Removes the RSD (Really Simple Discovery) header. If you don\'t integrate your blog with external XML-RPC services such as Flickr then the "RSD" function is pretty much useless to you.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Disable File Editor Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_file_editor() {

		if ( isset( $this->settings['file_editor'] ) && $this->settings['file_editor'] === true ) {
			$file_editor = 1;
		} else {
			$file_editor = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_file_editor" name="itsec_tweaks[file_editor]" value="1" ' . checked( 1, $file_editor, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_file_editor">' . __( 'Disable File Editor', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Disables the file editor for plugins and themes requiring users to have access to the file system to modify files. Once activated you will need to manually edit theme and other files using a tool other than WordPress.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Remove WordPress Generator Meta Tag Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_generator_tag() {

		if ( isset( $this->settings['generator_tag'] ) && $this->settings['generator_tag'] === true ) {
			$generator_tag = 1;
		} else {
			$generator_tag = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_generator_tag" name="itsec_tweaks[generator_tag]" value="1" ' . checked( 1, $generator_tag, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_generator_tag">' . __( 'Remove WordPress Generator Meta Tag', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Removes the <code>&lt;meta name="generator" content="WordPress [version]" /&gt;</code></pre> meta tag from your sites header. This process hides version information from a potential attacker making it more difficult to determine vulnerabilities.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Hide Plugin Update Notifications Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_plugin_updates() {

		if ( isset( $this->settings['plugin_updates'] ) && $this->settings['plugin_updates'] === true ) {
			$plugin_updates = 1;
		} else {
			$plugin_updates = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_plugin_updates" name="itsec_tweaks[plugin_updates]" value="1" ' . checked( 1, $plugin_updates, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_plugin_updates">' . __( 'Hide Plugin Update Notifications', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Hides plugin update notifications from users who cannot update plugins. Please note that this only makes a difference in multi-site installations.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Display Random Version Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_random_version() {

		if ( isset( $this->settings['random_version'] ) && $this->settings['random_version'] === true ) {
			$random_version = 1;
		} else {
			$random_version = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_random_version" name="itsec_tweaks[random_version]" value="1" ' . checked( 1, $random_version, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_random_version">' . __( 'Display Random Version', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Where a WordPress version must be displayed, it will display a random WordPress version and will remove the WordPress version completely where possible.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Replace jQuery Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_safe_jquery() {

		if ( isset( $this->settings['safe_jquery'] ) && $this->settings['safe_jquery'] === true ) {
			$safe_jquery = 1;
		} else {
			$safe_jquery = 0;
		}

		$raw_version = get_site_option( 'itsec_jquery_version' );
		$is_safe     = ITSEC_Lib::safe_jquery_version() === true;

		if ( $raw_version !== false ) {
			$version = sanitize_text_field( $raw_version );
		} else {
			$version = sprintf(
				'%s <a href="%s" target="_blank">%s</a> %s',
				__( 'undetermined. Please', 'it-l10n-better-wp-security' ),
				site_url(),
				__( 'check your homepage', 'it-l10n-better-wp-security' ),
				__( 'to see if you even need this feature', 'it-l10n-better-wp-security' )
			);
		}

		if ( $is_safe === true ) {
			$color = 'green';
		} else {
			$color = 'red';
		}

		if ( $is_safe !== true && $raw_version !== false ) {
			echo '<input type="checkbox" id="itsec_tweaks_wordpress_safe_jquery" name="itsec_tweaks[safe_jquery]" value="1" ' . checked( 1, $safe_jquery, false ) . '/>';
		}

		echo '<label for="itsec_tweaks_wordpress_safe_jquery">' . __( 'Enqueue a safe version of jQuery', 'it-l10n-better-wp-security' ) . '</label>';
		echo '<p class="description">' . __( 'Remove the existing jQuery version used and replace it with a safe version (the version that comes default with WordPress).', 'it-l10n-better-wp-security' ) . '</p>';

		echo '<p class="description" style="color: ' . $color . '">' . __( 'Your current jQuery version is ', 'it-l10n-better-wp-security' ) . $version . '.</p>';
		printf(
			'<p class="description">%s <a href="%s" target="_blank">%s</a> %s</p>',
			__( 'Note that this only checks the homepage of your site and only for users who are logged in. This is done intentionally to save resources. If you think this is in error ', 'it-l10n-better-wp-security' ),
			site_url(),
			__( 'click here to check again.', 'it-l10n-better-wp-security' ),
			__( 'This will open your homepage in a new window allowing the plugin to determine the version of jQuery actually being used. You can then come back here and reload this page to see your version.', 'it-l10n-better-wp-security' )
		);

	}

	/**
	 * echos Hide Theme Update Notifications Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_theme_updates() {

		if ( isset( $this->settings['theme_updates'] ) && $this->settings['theme_updates'] === true ) {
			$theme_updates = 1;
		} else {
			$theme_updates = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_theme_updates" name="itsec_tweaks[theme_updates]" value="1" ' . checked( 1, $theme_updates, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_theme_updates">' . __( 'Hide Theme Update Notifications', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Hides theme update notifications from users who cannot update themes. Please note that this only makes a difference in multi-site installations.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Disable PHP In Uploads Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_uploads_php() {

		if ( isset( $this->settings['uploads_php'] ) && $this->settings['uploads_php'] === true ) {
			$uploads_php = 1;
		} else {
			$uploads_php = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_uploads_php" name="itsec_tweaks[uploads_php]" value="1" ' . checked( 1, $uploads_php, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_uploads_php">' . __( 'Disable PHP in Uploads', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'Disable PHP execution in the uploads directory. This will prevent uploading of malicious scripts to uploads.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * echos Remove Windows Live Writer Header Field
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function tweaks_wordpress_wlwmanifest_header() {

		if ( isset( $this->settings['wlwmanifest_header'] ) && $this->settings['wlwmanifest_header'] === true ) {
			$wlwmanifest_header = 1;
		} else {
			$wlwmanifest_header = 0;
		}

		$content = '<input type="checkbox" id="itsec_tweaks_server_wlwmanifest_header" name="itsec_tweaks[wlwmanifest_header]" value="1" ' . checked( 1, $wlwmanifest_header, false ) . '/>';
		$content .= '<label for="itsec_tweaks_server_wlwmanifest_header">' . __( 'Remove the Windows Live Writer header. ', 'it-l10n-better-wp-security' ) . '</label>';
		$content .= '<p class="description">' . __( 'This is not needed if you do not use Windows Live Writer or other blogging clients that rely on this file.', 'it-l10n-better-wp-security' ) . '</p>';

		echo $content;

	}

	/**
	 * Build rewrite rules.
	 *
	 * @since 4.0
	 *
	 * @param mixed $input options to build rules from
	 *
	 * @return array         rules to write
	 */
	public static function build_rewrite_rules( $input = null ) {

		$server_type = ITSEC_Lib::get_server(); //Get the server type to build the right rules

		//Get the rules from the database if input wasn't sent
		if ( $input === null ) {
			$input = get_site_option( 'itsec_tweaks' );
		}

		$rules = ''; //initialize all rules to blank string

		//Process Protect Files Rules
		if ( $input['protect_files'] === true ) {

			if ( $server_type === 'nginx' ) { //NGINX rules

				$rules .= "\t# " . __( 'Rules to block access to WordPress specific files and wp-includes', 'it-l10n-better-wp-security' ) . PHP_EOL .
				          "\tlocation ~ /\.ht { deny all; }" . PHP_EOL .
				          "\tlocation ~ wp-config.php { deny all; }" . PHP_EOL .
				          "\tlocation ~ readme.html { deny all; }" . PHP_EOL .
				          "\tlocation ~ readme.txt { deny all; }" . PHP_EOL .
				          "\tlocation ~ /install.php { deny all; }" . PHP_EOL .
				          "\tlocation ^wp-includes/(.*).php { deny all; }" . PHP_EOL .
				          "\tlocation ^/wp-admin/includes(.*)$ { deny all; }" . PHP_EOL;

			} else { //rules for all other servers

				$rules .= "# " . __( 'Rules to block access to WordPress specific files', 'it-l10n-better-wp-security' ) . PHP_EOL .
				          "<files .htaccess>" . PHP_EOL .
				          "\tOrder allow,deny" . PHP_EOL .
				          "\tDeny from all" . PHP_EOL .
				          "</files>" . PHP_EOL .
				          "<files readme.html>" . PHP_EOL .
				          "\tOrder allow,deny" . PHP_EOL .
				          "\tDeny from all" . PHP_EOL .
				          "</files>" . PHP_EOL .
				          "<files readme.txt>" . PHP_EOL .
				          "\tOrder allow,deny" . PHP_EOL .
				          "\tDeny from all" . PHP_EOL .
				          "</files>" . PHP_EOL .
				          "<files install.php>" . PHP_EOL .
				          "\tOrder allow,deny" . PHP_EOL .
				          "\tDeny from all" . PHP_EOL .
				          "</files>" . PHP_EOL .
				          "<files wp-config.php>" . PHP_EOL .
				          "\tOrder allow,deny" . PHP_EOL .
				          "\tDeny from all" . PHP_EOL .
				          "</files>" . PHP_EOL;

			}

		}

		//Rules to disanle XMLRPC
		if ( $input['disable_xmlrpc'] == 2 ) {

			if ( strlen( $rules ) > 1 ) {
				$rules .= PHP_EOL;
			}

			$rules .= "# " . __( 'Rules to disable XML-RPC', 'it-l10n-better-wp-security' ) . PHP_EOL;

			if ( $server_type === 'nginx' ) { //NGINX rules

				$rules .= "\tlocation ~ xmlrpc.php { deny all; }" . PHP_EOL;

			} else { //rules for all other servers

				$rules .= "<files xmlrpc.php>" . PHP_EOL .
				          "\tOrder allow,deny" . PHP_EOL .
				          "\tDeny from all" . PHP_EOL .
				          "</files>" . PHP_EOL;

			}

		}

		//Primary Rules for Directory Browsing
		if ( $input['directory_browsing'] == true ) {

			if ( strlen( $rules ) > 1 ) {
				$rules .= PHP_EOL;
			}

			$rules .= "# " . __( 'Rules to disable directory browsing', 'it-l10n-better-wp-security' ) . PHP_EOL;

			if ( $server_type !== 'nginx' ) { //Don't use this on NGINX

				$rules .= "Options -Indexes" . PHP_EOL;

			}

		}

		//Apache rewrite rules (and related NGINX rules)
		if ( $input['protect_files'] == true || $input['uploads_php'] == true || $input['request_methods'] == true || $input['suspicious_query_strings'] == true || $input['non_english_characters'] == true || $input['comment_spam'] == true ) {

			if ( strlen( $rules ) > 1 ) {
				$rules .= PHP_EOL;
			}

			//Open Apache rewrite rules
			if ( $server_type !== 'nginx' ) {

				$rules .= "<IfModule mod_rewrite.c>" . PHP_EOL .
				          "\tRewriteEngine On" . PHP_EOL;

			}

			//Rewrite Rules for Protect Files
			if ( $input['protect_files'] == true && $server_type !== 'nginx' ) {

				$rules .= PHP_EOL . "\t# " . __( 'Rules to protect wp-includes', 'it-l10n-better-wp-security' ) . PHP_EOL;

				$rules .= "\tRewriteRule ^wp-admin/includes/ - [F]" . PHP_EOL .
				          "\tRewriteRule !^wp-includes/ - [S=3]" . PHP_EOL .
				          "\tRewriteCond %{SCRIPT_FILENAME} !^(.*)wp-includes/ms-files.php" . PHP_EOL .
				          "\tRewriteRule ^wp-includes/[^/]+\.php$ - [F]" . PHP_EOL .
				          "\tRewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F]" . PHP_EOL .
				          "\tRewriteRule ^wp-includes/theme-compat/ - [F]" . PHP_EOL;

			}

			//Rewrite Rules for Disable PHP in Uploads
			if ( $input['uploads_php'] === true ) {

				$rules .= PHP_EOL . "\t# " . __( 'Rules to prevent php execution in uploads', 'it-l10n-better-wp-security' ) . PHP_EOL;

				if ( $server_type !== 'nginx' ) {

					$rules .= "\tRewriteRule ^(.*)/uploads/(.*).php(.?) - [F]" . PHP_EOL;

				} else { //rules for all other servers

					$rules .= "\tlocation ^(.*)/uploads/(.*).php(.?){ deny all; }" . PHP_EOL;

				}

			}

			//Apache rewrite rules for disable http methods
			if ( $input['request_methods'] == true ) {

				$rules .= PHP_EOL . "\t# " . __( 'Rules to block unneeded HTTP methods', 'it-l10n-better-wp-security' ) . PHP_EOL;

				if ( $server_type === 'nginx' ) { //NGINX rules

					$rules .= "\tif (\$request_method ~* \"^(TRACE|DELETE|TRACK)\"){ return 403; }" . PHP_EOL;

				} else { //rules for all other servers

					$rules .= "\tRewriteCond %{REQUEST_METHOD} ^(TRACE|DELETE|TRACK) [NC]" . PHP_EOL . "\tRewriteRule ^(.*)$ - [F]" . PHP_EOL;

				}

			}

			//Process suspicious query rules
			if ( $input['suspicious_query_strings'] == true ) {

				$rules .= PHP_EOL . "\t# " . __( 'Rules to block suspicious URIs', 'it-l10n-better-wp-security' ) . PHP_EOL;

				if ( $server_type === 'nginx' ) { //NGINX rules

					$rules .= "\tset \$susquery 0;" . PHP_EOL .
					          "\tif (\$args ~* \"\\.\\./\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"\.(bash|git|hg|log|svn|swp|cvs)\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"etc/passwd\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"boot.ini\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"ftp:\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"http:\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"https:\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"(<|%3C).*script.*(>|%3E)\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"mosConfig_[a-zA-Z_]{1,21}(=|%3D)\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"base64_encode\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"(%24&x)\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"(&#x22;|&#x27;|&#x3C;|&#x3E;|&#x5C;|&#x7B;|&#x7C;|%24&x)\"){ set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"(127.0)\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"(globals|encode|localhost|loopback)\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$args ~* \"(request|insert|concat|union|declare)\") { set \$susquery 1; }" . PHP_EOL .
					          "\tif (\$susquery = 1) { return 403; }" . PHP_EOL;

				} else { //rules for all other servers

					$rules .= "\tRewriteCond %{QUERY_STRING} \.\.\/ [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} ^.*\.(bash|git|hg|log|svn|swp|cvs) [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} etc/passwd [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} boot\.ini [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} ftp\:  [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} http\:  [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} https\:  [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} mosConfig_[a-zA-Z_]{1,21}(=|%3D) [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} base64_encode.*\(.*\) [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} ^.*(\[|\]|\(|\)|<|>|ê|\"|;|\?|\*|=$).* [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} ^.*(&#x22;|&#x27;|&#x3C;|&#x3E;|&#x5C;|&#x7B;|&#x7C;).* [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} ^.*(%24&x).* [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} ^.*(127\.0).* [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} ^.*(globals|encode|localhost|loopback).* [NC,OR]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} ^.*(request|concat|insert|union|declare).* [NC]" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} !^loggedout=true" . PHP_EOL .
					          "\tRewriteCond %{QUERY_STRING} !^action=rp" . PHP_EOL .
					          "\tRewriteCond %{HTTP_COOKIE} !^.*wordpress_logged_in_.*$" . PHP_EOL .
					          "\tRewriteCond %{HTTP_REFERER} !^http://maps\.googleapis\.com(.*)$" . PHP_EOL .
					          "\tRewriteRule ^(.*)$ - [F]" . PHP_EOL;

				}

			}

			//Process filtering of foreign characters
			if ( $input['non_english_characters'] == true ) {

				$rules .= PHP_EOL . "\t# " . __( 'Rules to block foreign characters in URLs', 'it-l10n-better-wp-security' ) . PHP_EOL;

				if ( $server_type === 'nginx' ) { //NGINX rules

					$rules .= "\tif (\$args ~* \"(%0|%A|%B|%C|%D|%E|%F)\") { return 403; }" . PHP_EOL;

				} else { //rules for all other servers

					$rules .= "\tRewriteCond %{QUERY_STRING} ^.*(%0|%A|%B|%C|%D|%E|%F).* [NC]" . PHP_EOL . "\tRewriteRule ^(.*)$ - [F]" . PHP_EOL;

				}

			}

			//Process Comment spam rules
			if ( $input['comment_spam'] == true ) {

				$rules .= PHP_EOL . "\t# " . __( 'Rules to help reduce spam', 'it-l10n-better-wp-security' ) . PHP_EOL;

				if ( $server_type === 'nginx' ) { //NGINX rules

					$rules .= "\tlocation /wp-comments-post.php {" . PHP_EOL .
					          "\t\tvalid_referers jetpack.wordpress.com/jetpack-comment/ " . ITSEC_Lib::get_domain( get_site_url(), false ) . ";" . PHP_EOL .
					          "\t\tset \$rule_0 0;" . PHP_EOL .
					          "\t\tif (\$request_method ~ \"POST\"){ set \$rule_0 1\$rule_0; }" . PHP_EOL .
					          "\t\tif (\$invalid_referer) { set \$rule_0 2\$rule_0; }" . PHP_EOL .
					          "\t\tif (\$http_user_agent ~ \"^$\"){ set \$rule_0 3\$rule_0; }" . PHP_EOL .
					          "\t\tif (\$rule_0 = \"3210\") { return 403; }" . PHP_EOL .
					          "\t}";

				} else { //rules for all other servers

					$rules .= "\tRewriteCond %{REQUEST_METHOD} POST" . PHP_EOL . "\tRewriteCond %{REQUEST_URI} ^(.*)wp-comments-post\.php*" . PHP_EOL . "\tRewriteCond %{HTTP_REFERER} !^" . ITSEC_Lib::get_domain( get_site_url() ) . ".* " . PHP_EOL . "\tRewriteCond %{HTTP_REFERER} !^http://jetpack\.wordpress\.com/jetpack-comment/ [OR]" . PHP_EOL . "\tRewriteCond %{HTTP_USER_AGENT} ^$" . PHP_EOL . "\tRewriteRule ^(.*)$ - [F]" . PHP_EOL;

				}

			}

			//Close Apache Rewrite rules
			if ( $server_type !== 'nginx' ) { //non NGINX rules

				$rules .= "</IfModule>";

			}

		}

		if ( strlen( $rules ) > 0 ) {
			$rules = explode( PHP_EOL, $rules );
		} else {
			$rules = false;
		}

		//create a proper array for writing
		return array( 'type' => 'htaccess', 'priority' => 10, 'name' => 'Tweaks', 'rules' => $rules, );

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

			if ( isset( $initials['file_editor'] ) && $initials['file_editor'] === false && defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT === true ) { //initially off, now on

				$input['file_editor'] = false;

			} elseif ( isset( $initials['file_editor'] ) && $initials['file_editor'] === true && ( ! defined( 'DISALLOW_FILE_EDIT' ) || DISALLOW_FILE_EDIT === false ) ) { //initially on, now off

				$input['file_editor'] = true;

			} elseif ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT === true ) { //no initial state, now on

				$input['file_editor'] = true;

			} else { //no initial state or other info. Set off

				$input['file_editor'] = false;

			}

		} else {

			$deactivating = false;

			//Get the rules from the database if input wasn't sent
			if ( $input === null ) {
				$input = get_site_option( 'itsec_tweaks' );
			}

		}

		$comment_add = array( 'type' => 'add', 'search_text' => '//The entry below were created by iThemes Security to disable the file editor', 'rule' => '//The entry below were created by iThemes Security to disable the file editor', );

		$comment_remove = array( 'type' => 'delete', 'search_text' => '//The entry below were created by iThemes Security to disable the file editor', 'rule' => false, );

		$rule_add = array( 'type' => 'add', 'search_text' => 'DISALLOW_FILE_EDIT', 'rule' => "define( 'DISALLOW_FILE_EDIT', true );", );

		$rule_remove = array( 'type' => 'delete', 'search_text' => 'DISALLOW_FILE_EDIT', 'rule' => false, );

		if ( $input['file_editor'] == true ) {

			if ( $deactivating === true ) {
				$rule[] = $comment_remove;
			} else {
				$rule[] = $comment_add;
			}

			$rule[] = $rule_add;

		} else {

			$rule[] = $comment_remove;

			$rule[] = $rule_remove;

		}

		return array( 'type' => 'wpconfig', 'name' => 'Tweaks', 'rules' => $rule, );

	}

	/**
	 * Sets the status in the plugin dashboard
	 *
	 * @since 4.0
	 *
	 * @return array array of statuses
	 */
	public function dashboard_status( $statuses ) {

		if ( isset( $this->settings['protect_files'] ) && $this->settings['protect_files'] === true ) {

			$status_array = 'safe-medium';
			$status       = array( 'text' => __( 'You are protecting common WordPress files from access.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_protect_files', );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'You are not protecting common WordPress files from access. Click here to protect WordPress files.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_protect_files', );

		}

		array_push( $statuses[$status_array], $status );

		if ( ITSEC_Lib::get_server() != 'nginx' ) {

			if ( isset( $this->settings['directory_browsing'] ) && $this->settings['directory_browsing'] === true && ITSEC_Lib::get_server() != 'nginx' ) {

				$status_array = 'safe-low';
				$status       = array( 'text' => __( 'You have successfully disabled directory browsing on your site.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_directory_browsing', );

			} else {

				$status_array = 'low';
				$status       = array( 'text' => __( 'You have not disabled directory browsing on your site. Click here to prevent a user from seeing every file present in your WordPress site.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_directory_browsing', );

			}

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['request_methods'] ) && $this->settings['request_methods'] === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'You are blocking HTTP request methods you do not need.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_request_methods', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'You are not blocking HTTP request methods you do not need. Click here to block extra HTTP request methods that WordPress should not normally need.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_request_methods', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['suspicious_query_strings'] ) && $this->settings['suspicious_query_strings'] === true ) {

			$status_array = 'safe-medium';
			$status       = array( 'text' => __( 'Your WordPress site is blocking suspicious looking information in the URL.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_suspicious_query_strings', );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'Your WordPress site is not blocking suspicious looking information in the URL. Click here to block users from trying to execute code that they should not be able to execute.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_suspicious_query_strings', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['non_english_characters'] ) && $this->settings['non_english_characters'] === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'Your WordPress site is blocking non-english characters in the URL.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_non_english_characters', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'Your WordPress site is not blocking non-english characters in the URL. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_non_english_characters', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['long_url_strings'] ) && $this->settings['long_url_strings'] === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'Your installation does not accept long URLs.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_long_url_strings', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'Your installation accepts long (over 255 character) URLS. This can lead to vulnerabilities. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_long_url_strings', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['write_permissions'] ) && $this->settings['write_permissions'] === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'Your wp-config.php and .htaccess files are not writeable.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_write_permissions', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'Your wp-config.php and .htaccess files are writeable. This can lead to vulnerabilities. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_write_permissions', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['generator_tag'] ) && $this->settings['generator_tag'] === true ) {

			$status_array = 'safe-medium';
			$status       = array( 'text' => __( 'Your WordPress installation is not telling every bot that you use WordPress.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_generator_tag', );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'Your WordPress installation is telling every bot that you use WordPress with a special "generator" tag. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_generator_tag', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['wlwmanifest_header'] ) && $this->settings['wlwmanifest_header'] === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'Your WordPress installation is not publishing the Windows Live Writer header.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_wlwmanifest_header', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'Your WordPress installation is publishing the Windows Live Writer header. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_wlwmanifest_header', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['edituri_header'] ) && $this->settings['edituri_header'] === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'Your WordPress installation is not publishing the Really Simple Discovery (RSD) header.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_edituri_header', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'Your WordPress installation is publishing the Really Simple Discovery (RSD) header. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_edituri_header', );

		}

		array_push( $statuses[$status_array], $status );

		if ( is_multisite() ) {

			if ( isset( $this->settings['theme_updates'] ) && $this->settings['theme_updates'] === true ) {

				$status_array = 'safe-medium';
				$status       = array( 'text' => __( 'Your WordPress installation is not telling users who cannot update themes about theme updates.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_theme_updates', );

			} else {

				$status_array = 'medium';
				$status       = array( 'text' => __( 'Your WordPress installation is telling users who cannot update themes about theme updates. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_theme_updates', );

			}

			array_push( $statuses[$status_array], $status );

			if ( isset( $this->settings['plugin_updates'] ) && $this->settings['plugin_updates'] === true ) {

				$status_array = 'safe-medium';
				$status       = array( 'text' => __( 'Your WordPress installation is not telling users who cannot update plugins about plugin updates.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_plugin_updates', );

			} else {

				$status_array = 'medium';
				$status       = array( 'text' => __( 'Your WordPress installation is telling users who cannot update plugins about plugin updates. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_plugin_updates', );

			}

			array_push( $statuses[$status_array], $status );

			if ( isset( $this->settings['core_updates'] ) && $this->settings['core_updates'] === true ) {

				$status_array = 'safe-medium';
				$status       = array( 'text' => __( 'Your WordPress installation is not telling users who cannot update WordPress core about WordPress core updates.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_core_updates', );

			} else {

				$status_array = 'medium';
				$status       = array( 'text' => __( 'Your WordPress installation is telling users who cannot update WordPress core about WordPress core updates. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_core_updates', );

			}

			array_push( $statuses[$status_array], $status );

		}

		if ( isset( $this->settings['comment_spam'] ) && $this->settings['comment_spam'] === true ) {

			$status_array = 'safe-medium';
			$status       = array( 'text' => __( 'Your WordPress installation is not allowing users without a user agent to post comments.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_comment_spam', );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'Your WordPress installation is allowing users without a user agent to post comments. Fix this to reduce comment spam.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_comment_spam', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['random_version'] ) && $this->settings['random_version'] === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'Version information is obscured to all non admin users.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_random_version', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'Users may still be able to get version information from various plugins and themes. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_random_version', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['file_editor'] ) && $this->settings['file_editor'] === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'Users cannot edit plugin and themes files directly from within the WordPress Dashboard.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_file_editor', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'Users can edit plugin and themes files directly from within the WordPress Dashboard. Click here to fix this.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_file_editor', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['disable_xmlrpc'] ) && $this->settings['disable_xmlrpc'] === 2 ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'XML-RPC is not available on your WordPress installation.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_disable_xmlrpc', );

		} elseif ( isset( $this->settings['disable_xmlrpc'] ) && $this->settings['disable_xmlrpc'] === 1 ) {

			$status_array = 'low';
			$status       = array( 'text' => __( 'XML-RPC is protecting you from the trackback and pingback attack but is still available on your site.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_disable_xmlrpc', );

		}else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'XML-RPC is available on your WordPress installation. Attackers can use this feature to attack your site. Click here to disable access to XML-RPC.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_disable_xmlrpc', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['uploads_php'] ) && $this->settings['uploads_php'] === true ) {

			$status_array = 'safe-medium';
			$status       = array( 'text' => __( 'Users cannot execute PHP from the uploads folder.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_uploads_php', );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'Users can execute PHP from the uploads folder.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_uploads_php', );

		}

		array_push( $statuses[$status_array], $status );

		$safe_jquery = ITSEC_Lib::safe_jquery_version();

		if ( $safe_jquery === true ) {

			$status_array = 'safe-high';
			$status       = array( 'text' => __( 'The front page of your site is using a safe version of jQuery.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_wordpress_safe_jquery', );

		} elseif ( $safe_jquery === false ) {

			$status_array = 'high';
			$status       = array( 'text' => __( 'The front page of your site is not using a safe version of jQuery or the version of jQuery cannot be determined.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_wordpress_safe_jquery', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'Your version of jQuery has not been determined. Load your homepage while logged in to determine the version of jQuery you are using', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_wordpress_safe_jquery', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['force_unique_nicename'] ) && $this->settings['force_unique_nicename'] === true ) {

			$status_array = 'safe-high';
			$status       = array( 'text' => __( "User's nicknames are different from their display name.", 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_force_unique_nicename', );

		} else {

			$status_array = 'high';
			$status       = array( 'text' => __( "User nicknames may be the same as their login name. This means their login user name may be publicly available throughout the site.", 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_force_unique_nicename', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['login_errors'] ) && $this->settings['login_errors'] === true ) {

			$status_array = 'safe-low';
			$status       = array( 'text' => __( 'Your login page is not giving out unnecessary information upon failed login.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_login_errors', );

		} else {

			$status_array = 'low';
			$status       = array( 'text' => __( 'Your login page is giving out unnecessary information upon failed login.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_login_errors', );

		}

		array_push( $statuses[$status_array], $status );

		if ( isset( $this->settings['disable_unused_author_pages'] ) && $this->settings['disable_unused_author_pages'] === true ) {

			$status_array = 'safe-medium';
			$status       = array( 'text' => __( 'User profiles for users without content are not publicly available.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_disable_unused_author_pages', );

		} else {

			$status_array = 'medium';
			$status       = array( 'text' => __( 'User profiles for users without content are publicly available. This can make it relatively easy to gain the username of important users.', 'it-l10n-better-wp-security' ), 'link' => '#itsec_tweaks_server_disable_unused_author_pages', );

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
		$this->settings    = get_site_option( 'itsec_tweaks' );
		$this->module_path = ITSEC_Lib::get_module_path( __FILE__ );

		add_filter( 'itsec_file_modules', array( $this, 'register_file' ) ); //register tooltip action
		add_filter( 'itsec_tracking_vars', array( $this, 'tracking_vars' ) );
		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) ); //add meta boxes to admin page
		add_action( 'itsec_admin_init', array( $this, 'initialize_admin' ) ); //initialize admin area
		add_filter( 'itsec_add_dashboard_status', array( $this, 'dashboard_status' ) ); //add information for plugin status
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

		add_settings_section(
			'tweaks_server',
			__( 'Configure Server Tweaks', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		add_settings_section(
			'tweaks_wordpress',
			__( 'Configure WordPress Tweaks', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		add_settings_section(
			'tweaks_multisite',
			__( 'Configure Multisite Tweaks', 'it-l10n-better-wp-security' ),
			array( $this, 'empty_callback_function' ),
			'security_page_toplevel_page_itsec_settings'
		);

		//Add settings fields
		add_settings_field(
			'itsec_tweaks[protect_files]',
			__( 'System Files', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_server_protect_files' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_server'
		);

		if ( ITSEC_Lib::get_server() != 'nginx' ) {

			add_settings_field(
				'itsec_tweaks[directory_browsing]',
				__( 'Directory Browsing', 'it-l10n-better-wp-security' ),
				array( $this, 'tweaks_server_directory_browsing' ),
				'security_page_toplevel_page_itsec_settings',
				'tweaks_server'
			);

		}

		add_settings_field(
			'itsec_tweaks[request_methods]',
			__( 'Request Methods', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_server_request_methods' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_server'
		);

		add_settings_field(
			'itsec_tweaks[suspicious_query_strings]',
			__( 'Suspicious Query Strings', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_server_suspicious_query_strings' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_server'
		);

		add_settings_field(
			'itsec_tweaks[non_english_characters]',
			__( 'Non-English Characters', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_server_non_english_characters' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_server'
		);

		add_settings_field(
			'itsec_tweaks[long_url_strings]',
			__( 'Long URL Strings', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_server_long_url_strings' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_server'
		);

		add_settings_field(
			'itsec_tweaks[write_permissions]',
			__( 'File Writing Permissions', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_server_write_permissions' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_server'
		);

		add_settings_field(
			'itsec_tweaks[uploads_php]',
			__( 'Uploads', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_uploads_php' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_server'
		);

		add_settings_field(
			'itsec_tweaks[generator_tag]',
			__( 'Generator Meta Tag', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_generator_tag' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[wlwmanifest_header]',
			__( 'Windows Live Writer Header', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_wlwmanifest_header' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[edituri_header]',
			__( 'EditURI Header', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_edituri_header' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[comment_spam]',
			__( 'Comment Spam', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_comment_spam' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[random_version]',
			__( 'Display Random Version', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_random_version' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[file_editor]',
			__( 'File Editor', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_file_editor' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[disable_xmlrpc]',
			__( 'XML-RPC', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_disable_xmlrpc' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[safe_jquery]',
			__( 'Replace jQuery With a Safe Version', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_safe_jquery' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[login_errors]',
			__( 'Login Error Messages', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_login_errors' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[force_unique_nicename]',
			__( 'Force Unique Nickname', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_force_unique_nicename' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		add_settings_field(
			'itsec_tweaks[disable_unused_author_pages]',
			__( 'Disable Extra User Archives', 'it-l10n-better-wp-security' ),
			array( $this, 'tweaks_wordpress_disable_unused_author_pages' ),
			'security_page_toplevel_page_itsec_settings',
			'tweaks_wordpress'
		);

		if ( is_multisite() ) {

			add_settings_field(
				'itsec_tweaks[theme_updates]',
				__( 'Theme Update Notifications', 'it-l10n-better-wp-security' ),
				array( $this, 'tweaks_wordpress_theme_updates' ),
				'security_page_toplevel_page_itsec_settings',
				'tweaks_multisite'
			);

			add_settings_field(
				'itsec_tweaks[plugin_updates]',
				__( 'Plugin Update Notifications', 'it-l10n-better-wp-security' ),
				array( $this, 'tweaks_wordpress_plugin_updates' ),
				'security_page_toplevel_page_itsec_settings',
				'tweaks_multisite'
			);

			add_settings_field(
				'itsec_tweaks[core_updates]',
				__( 'Core Update Notifications', 'it-l10n-better-wp-security' ),
				array( $this, 'tweaks_wordpress_core_updates' ),
				'security_page_toplevel_page_itsec_settings',
				'tweaks_multisite'
			);

		}

		//Register the settings field for the entire module
		register_setting(
			'security_page_toplevel_page_itsec_settings',
			'itsec_tweaks',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Render the settings metabox
	 *
	 * @return void
	 */
	public function metabox_tweaks_system() {

		echo '<p>' . __( 'These are advanced settings that may be utilized to further strengthen the security of your WordPress site.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p>' . __( 'Note: These settings are listed as advanced because they block common forms of attacks but they can also block legitimate plugins and themes that rely on the same techniques. When activating the settings below, we recommend enabling them one by one to test that everything on your site is still working as expected.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p>' . __( 'Remember, some of these settings might conflict with other plugins or themes, so test your site after enabling each setting.', 'it-l10n-better-wp-security' ) . '</p>';

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'tweaks_server', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_settings' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-better-wp-security' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Render the settings metabox
	 *
	 * @return void
	 */
	public function metabox_tweaks_wordpress() {

		echo '<p>' . __( 'These are advanced settings that may be utilized to further strengthen the security of your WordPress site.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p>' . __( 'Note: These settings are listed as advanced because they block common forms of attacks but they can also block legitimate plugins and themes that rely on the same techniques. When activating the settings below, we recommend enabling them one by one to test that everything on your site is still working as expected.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p>' . __( 'Remember, some of these settings might conflict with other plugins or themes, so test your site after enabling each setting.', 'it-l10n-better-wp-security' ) . '</p>';

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'tweaks_wordpress', false );

		echo '<p>' . PHP_EOL;

		settings_fields( 'security_page_toplevel_page_itsec_settings' );

		echo '<input class="button-primary" name="submit" type="submit" value="' . __( 'Save All Changes', 'it-l10n-better-wp-security' ) . '" />' . PHP_EOL;

		echo '</p>' . PHP_EOL;

	}

	/**
	 * Render the settings metabox
	 *
	 * @return void
	 */
	public function metabox_tweaks_multisite() {

		echo '<p>' . __( 'These are advanced settings that may be utilized to further strengthen the security of your WordPress site.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p>' . __( 'Note: These settings are listed as advanced because they block common forms of attacks but they can also block legitimate plugins and themes that rely on the same techniques. When activating the settings below, we recommend enabling them one by one to test that everything on your site is still working as expected.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p>' . __( 'Remember, some of these settings might conflict with other plugins or themes, so test your site after enabling each setting.', 'it-l10n-better-wp-security' ) . '</p>';

		$this->core->do_settings_section( 'security_page_toplevel_page_itsec_settings', 'tweaks_multisite', false );

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

		$one_click_settings['itsec_tweaks'][] = array(
			'option' => 'generator_tag',
			'value'  => 1,
		);

		$one_click_settings['itsec_tweaks'][] = array(
			'option' => 'wlwmanifest_header',
			'value'  => 1,
		);

		$one_click_settings['itsec_tweaks'][] = array(
			'option' => 'theme_updates',
			'value'  => 1,
		);

		$one_click_settings['itsec_tweaks'][] = array(
			'option' => 'plugin_updates',
			'value'  => 1,
		);

		$one_click_settings['itsec_tweaks'][] = array(
			'option' => 'core_updates',
			'value'  => 1,
		);

		$one_click_settings['itsec_tweaks'][] = array(
			'option' => 'login_errors',
			'value'  => 1,
		);

		$one_click_settings['itsec_tweaks'][] = array(
			'option' => 'force_unique_nicename',
			'value'  => 1,
		);

		$one_click_settings['itsec_tweaks'][] = array(
			'option' => 'disable_unused_author_pages',
			'value'  => 1,
		);

		return $one_click_settings;

	}

	/**
	 * Register ban users for file writer
	 *
	 * @param  array $file_modules array of file writer modules
	 *
	 * @return array                   array of file writer modules
	 */
	public function register_file( $file_modules ) {

		$file_modules['tweaks'] = array(
			'rewrite' => array( $this, 'save_rewrite_rules' ),
			'config'  => array( $this, 'save_config_rules' ),
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

		global $itsec_globals;

		$input['protect_files']               = ( isset( $input['protect_files'] ) && intval( $input['protect_files'] == 1 ) ? true : false );
		$input['directory_browsing']          = ( isset( $input['directory_browsing'] ) && intval( $input['directory_browsing'] == 1 ) ? true : false );
		$input['request_methods']             = ( isset( $input['request_methods'] ) && intval( $input['request_methods'] == 1 ) ? true : false );
		$input['suspicious_query_strings']    = ( isset( $input['suspicious_query_strings'] ) && intval( $input['suspicious_query_strings'] == 1 ) ? true : false );
		$input['non_english_characters']      = ( isset( $input['non_english_characters'] ) && intval( $input['non_english_characters'] == 1 ) ? true : false );
		$input['long_url_strings']            = ( isset( $input['long_url_strings'] ) && intval( $input['long_url_strings'] == 1 ) ? true : false );
		$input['write_permissions']           = ( isset( $input['write_permissions'] ) && intval( $input['write_permissions'] == 1 ) ? true : false );
		$input['generator_tag']               = ( isset( $input['generator_tag'] ) && intval( $input['generator_tag'] == 1 ) ? true : false );
		$input['wlwmanifest_header']          = ( isset( $input['wlwmanifest_header'] ) && intval( $input['wlwmanifest_header'] == 1 ) ? true : false );
		$input['edituri_header']              = ( isset( $input['edituri_header'] ) && intval( $input['edituri_header'] == 1 ) ? true : false );
		$input['theme_updates']               = ( isset( $input['theme_updates'] ) && intval( $input['theme_updates'] == 1 ) ? true : false );
		$input['plugin_updates']              = ( isset( $input['plugin_updates'] ) && intval( $input['plugin_updates'] == 1 ) ? true : false );
		$input['core_updates']                = ( isset( $input['core_updates'] ) && intval( $input['core_updates'] == 1 ) ? true : false );
		$input['comment_spam']                = ( isset( $input['comment_spam'] ) && intval( $input['comment_spam'] == 1 ) ? true : false );
		$input['random_version']              = ( isset( $input['random_version'] ) && intval( $input['random_version'] == 1 ) ? true : false );
		$input['file_editor']                 = ( isset( $input['file_editor'] ) && intval( $input['file_editor'] == 1 ) ? true : false );
		$input['disable_xmlrpc']              = isset( $input['disable_xmlrpc'] ) ? intval( $input['disable_xmlrpc'] ) : 0;
		$input['uploads_php']                 = ( isset( $input['uploads_php'] ) && intval( $input['uploads_php'] == 1 ) ? true : false );
		$input['safe_jquery']                 = ( isset( $input['safe_jquery'] ) && intval( $input['safe_jquery'] == 1 ) ? true : false );
		$input['login_errors']                = ( isset( $input['login_errors'] ) && intval( $input['login_errors'] == 1 ) ? true : false );
		$input['force_unique_nicename']       = ( isset( $input['force_unique_nicename'] ) && intval( $input['force_unique_nicename'] == 1 ) ? true : false );
		$input['disable_unused_author_pages'] = ( isset( $input['disable_unused_author_pages'] ) && intval( $input['disable_unused_author_pages'] == 1 ) ? true : false );

		if (
			( $input['protect_files'] !== $this->settings['protect_files'] ||
			  $input['directory_browsing'] !== $this->settings['directory_browsing'] ||
			  $input['request_methods'] !== $this->settings['request_methods'] ||
			  $input['suspicious_query_strings'] !== $this->settings['suspicious_query_strings'] ||
			  $input['non_english_characters'] !== $this->settings['non_english_characters'] ||
			  $input['comment_spam'] !== $this->settings['comment_spam'] ||
			  $input['disable_xmlrpc'] !== $this->settings['disable_xmlrpc'] ||
			  $input['uploads_php'] !== $this->settings['uploads_php']
			) ||
			isset( $itsec_globals['settings']['write_files'] ) && $itsec_globals['settings']['write_files'] === true
		) {

			add_site_option( 'itsec_rewrites_changed', true );

		}

		if ( $input['file_editor'] !== $this->settings['file_editor'] ) {

			add_site_option( 'itsec_config_changed', true );

		}

		if ( is_multisite() ) {

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

		if ( isset( $_POST['itsec_tweaks'] ) ) {

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'security_page_toplevel_page_itsec_settings-options' ) ) {
				die( __( 'Security error!', 'it-l10n-better-wp-security' ) );
			}

			update_site_option( 'itsec_tweaks', $_POST['itsec_tweaks'] ); //we must manually save network options

		}

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

			if ( isset( $rule['name'] ) && $rule['name'] == 'Tweaks' ) {
				unset ( $config_rules[$key] );
			}

		}

		$config_rules[] = $this->build_wpconfig_rules();

		$itsec_files->set_config_rules( $config_rules );

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

			if ( isset( $rule['name'] ) && $rule['name'] == 'Tweaks' ) {
				unset ( $rewrite_rules[$key] );
			}

		}

		$rewrite_rules[] = $this->build_rewrite_rules();

		$itsec_files->set_rewrite_rules( $rewrite_rules );

	}

	/**
	 * Add header for server tweaks
	 *
	 * @return void
	 */
	public function server_tweaks_intro() {

		echo '<h2 class="settings-section-header">' . __( 'Server Tweaks', 'it-l10n-better-wp-security' ) . '</h2>';
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

		$vars['itsec_tweaks'] = array(
			'protect_files'               => '0:b',
			'directory_browsing'          => '0:b',
			'request_methods'             => '0:b',
			'suspicious_query_strings'    => '0:b',
			'non_english_characters'      => '0:b',
			'long_url_strings'            => '0:b',
			'write_permissions'           => '0:b',
			'uploads_php'                 => '0:b',
			'generator_tag'               => '0:b',
			'wlwmanifest_header'          => '0:b',
			'edituri_header'              => '0:b',
			'comment_spam'                => '0:b',
			'random_version'              => '0:b',
			'file_editor'                 => '0:b',
			'disable_xmlrpc'              => '0:b',
			'core_updates'                => '0:b',
			'plugin_updates'              => '0:b',
			'theme_updates'               => '0:b',
			'safe_jquery'                 => '0:b',
			'login_errors'                => '0:b',
			'force_unique_nicename'       => '0:b',
			'disable_unused_author_pages' => '0:b',
		);

		return $vars;

	}

	/**
	 * Add header for WordPress Multisite tweaks
	 *
	 * @return void
	 */
	public function wordpress_multisite_tweaks_intro() {

		echo '<h2 class="settings-section-header">' . __( 'Multisite Tweaks', 'it-l10n-better-wp-security' ) . '</h2>';
	}

	/**
	 * Add header for WordPress tweaks
	 *
	 * @return void
	 */
	public function wordpress_tweaks_intro() {

		echo '<h2 class="settings-section-header">' . __( 'WordPress Tweaks', 'it-l10n-better-wp-security' ) . '</h2>';
	}

}