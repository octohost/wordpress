<?php

class ITSEC_Core_Admin {

	function run() {

		if ( is_admin() ) {

			$this->initialize();

		}

	}

	/**
	 * Add meta boxes to primary options pages.
	 *
	 * @since 4.0
	 *
	 * @param array $available_pages array of available page_hooks
	 *
	 * @return void
	 */
	public function add_admin_meta_boxes( $available_pages ) {

		foreach ( $available_pages as $page ) {

			add_meta_box(
				'itsec_security_updates',
				__( 'Download Our WordPress Security Pocket Guide', 'it-l10n-better-wp-security' ),
				array( $this, 'metabox_security_updates' ),
				$page,
				'priority_side',
				'core'
			);

			add_meta_box(
				'itsec_need_help',
				__( 'Need Help Securing Your Site?', 'it-l10n-better-wp-security' ),
				array( $this, 'metabox_need_help' ),
				$page,
				'side',
				'core'
			);

			if ( ! class_exists( 'backupbuddy_api' ) ) {
				add_meta_box(
					'itsec_get_backup',
					__( 'Complete Your Security Strategy With BackupBuddy', 'it-l10n-better-wp-security' ),
					array( $this, 'metabox_get_backupbuddy' ),
					$page,
					'side',
					'core'
				);
			}

		}

		add_meta_box(
			'itsec_get_started',
			__( 'Getting Started', 'it-l10n-better-wp-security' ),
			array( $this, 'metabox_get_started' ),
			'toplevel_page_itsec',
			'normal',
			'core'
		);

	}

	/**
	 * Adds links to the plugin row meta
	 *
	 * @since 4.0
	 *
	 * @param array $meta Existing meta
	 *
	 * @return array
	 */
	public function add_plugin_meta_links( $meta ) {

		$meta[] = '<a href="http://ithemes.com/security/ithemes-security-professional-setup" target="_blank">' . __( 'Get Pro Setup', 'it-l10n-better-wp-security' ) . '</a>';
		$meta[] = '<a href="http://ithemes.com/security" target="_blank">' . __( 'Get Support', 'it-l10n-better-wp-security' ) . '</a>';

		return $meta;
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
	private function initialize() {

		add_action( 'itsec_add_admin_meta_boxes', array(
			$this, 'add_admin_meta_boxes'
		) ); //add meta boxes to admin page
		add_filter( 'itsec_meta_links', array( $this, 'add_plugin_meta_links' ) );

		//Process support plugin nag
		add_action( 'itsec_admin_init', array( $this, 'setup_nag' ) );

		//Process support plugin nag
		add_action( 'itsec_admin_init', array( $this, 'support_nag' ) );

	}

	/**
	 * Display the Get BackupBuddy metabox
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_get_backupbuddy() {

		echo '<p style="text-align: center;"><img src="' . plugins_url( 'img/backupbuddy-logo.png', __FILE__ ) . '" alt="BackupBuddy"></p>';
		echo '<p>' . __( 'BackupBuddy is the complete backup, restore and migration solution for your WordPress site. Schedule automated backups, store your backups safely off-site and restore your site quickly & easily.', 'it-l10n-better-wp-security' ) . '</p>';
		echo sprintf( '<p style="font-weight: bold; font-size: 1em;">%s<span style="display: block; text-align: center; font-size: 1.2em; background: #ebebeb; padding: .5em;">%s</span></p>', __( '25% off BackupBuddy with coupon code', 'it-l10n-better-wp-security' ), __( 'BACKUPPROTECT', 'it-l10n-better-wp-security' ) );
		echo '<a href="http://ithemes.com/better-backups" class="button-secondary" target="_blank">' . __( 'Get BackupBuddy', 'it-l10n-better-wp-security' ) . '</a>';

	}

	/**
	 * Display the metabox for getting started
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_get_started() {

		echo '<div class="itsec_getting_started">';
		echo '<div class="column">';
		echo '<h2>' . __( 'Watch the Walk-Through Video', 'it-l10n-better-wp-security' ) . '</h2>';
		echo '<a class="itsec-video-link" href="#" data-video-id="itsec_video"><img src="' . plugins_url( 'img/video.png', __FILE__ ) . '" /></a>';
		echo sprintf( '<p class="itsec-video-description">%s <a href="http://ithem.es/6y" target="_blank">%s</a> %s </p>', __( 'In this short video, we walk through', 'it-l10n-better-wp-security' ), __( 'how to get started securing your site', 'it-l10n-better-wp-security' ), __( 'with iThemes Security.', 'it-l10n-better-wp-security' ) );
		echo '<p class="itsec_video"><iframe src="//player.vimeo.com/video/89142424?title=0&amp;byline=0&amp;portrait=0" width="853" height="480" frameborder="0" ></iframe></p>';

		echo '</div>';

		echo '<div class="column two">';
		echo '<h2>' . __( 'Website Security is a complicated subject, but we have experts that can help.', 'it-l10n-better-wp-security' ) . '</h2>';
		echo '<p>' . __( 'Get added peace of mind with professional support from our expert team and pro features to take your site security to the next level with iThemes Security Pro.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p><a class="button-primary" href="http://www.ithemes.com/security" target="_blank">' . __( 'Get Support and Pro Features', 'it-l10n-better-wp-security' ) . '</a></p>';
		echo '</div>';
		echo '</div>';

	}

	/**
	 * Display the Need Help metabox
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_need_help() {

		echo '<p>' . __( 'Since you are using the free version of iThemes Security from WordPress.org, you can get free support from the WordPress community.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p><a class="button-secondary" href="http://wordpress.org/support/plugin/better-wp-security" target="_blank">' . __( 'Get Free Support', 'it-l10n-better-wp-security' ) . '</a></p>';
		echo '<p>' . __( 'Be sure your site has been properly secured by having one of our security experts tailor iThemes Security settings to the specific needs of this site.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p><a class="button-secondary" href="http://ithemes.com/security/ithemes-security-professional-setup" target="_blank">' . __( 'Have an expert secure my site', 'it-l10n-better-wp-security' ) . '</a></p>';
		echo '<p>' . __( 'Get added peace of mind with professional support from our expert team and pro features with iThemes Security Pro.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p><a class="button-secondary" href="http://www.ithemes.com/security" target="_blank">' . __( 'Get iThemes Security Pro', 'it-l10n-better-wp-security' ) . '</a></p>';

	}

	/**
	 * Display the Security Updates signup metabox.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function metabox_security_updates() {

		ob_start();
		?>

		<div id="mc_embed_signup">
			<form
				action="http://ithemes.us2.list-manage.com/subscribe/post?u=7acf83c7a47b32c740ad94a4e&amp;id=5176bfed9e"
				method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
				target="_blank" novalidate>
				<div style="text-align: center;">
					<img src="<?php echo plugins_url( 'img/security-ebook.png', __FILE__ ) ?>" width="145"
					     height="187" alt="WordPress Security - A Pocket Guide">
				</div>
				<p><?php _e( 'Get tips for securing your site + the latest WordPress security updates, news and releases from iThemes.', 'better-wp-security' ); ?></p>

				<div id="mce-responses" class="clear">
					<div class="response" id="mce-error-response" style="display:none"></div>
					<div class="response" id="mce-success-response" style="display:none"></div>
				</div>
				<label for="mce-EMAIL"
				       style="display: block;margin-bottom: 3px;"><?php _e( 'Email Address', 'better-wp-security' ); ?></label>
				<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL"
				       placeholder="email@domain.com">
				<br/><br/>
				<input type="submit" value="<?php _e( 'Subscribe', 'better-wp-security' ); ?>" name="subscribe"
				       id="mc-embedded-subscribe" class="button button-secondary">
			</form>
		</div>

		<?php
		ob_end_flush();

	}

	/**
	 * Display (and hide) setup nag.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function setup_nag() {

		global $blog_id, $itsec_globals;

		if ( is_multisite() && ( $blog_id != 1 || ! current_user_can( 'manage_network_options' ) ) ) { //only display to network admin if in multisite
			return;
		}

		$options = $itsec_globals['data'];

		//display the notifcation if they haven't turned it off
		if ( ( ! isset( $options['setup_completed'] ) || $options['setup_completed'] === false ) ) {

			if ( ! function_exists( 'ithemes_plugin_setup_notice' ) ) {

				function ithemes_plugin_setup_notice() {

					global $itsec_globals;

					echo '<div class="updated" id="itsec_setup_notice"><span class="it-icon-itsec"></span>'
					     . $itsec_globals['plugin_name'] . ' ' . __( 'is almost ready.', 'it-l10n-better-wp-security' ) . '<a href="#" class="itsec-notice-button" onclick="document.location.href=\'?itsec_setup=yes&_wpnonce=' . wp_create_nonce( 'itsec-nag' ) . '\';">' . __( 'Secure Your Site Now', 'it-l10n-better-wp-security' ) . '</a><a target="_blank" href="http://ithemes.com/ithemes-security-4-is-here" class="itsec-notice-button">' . __( "See what's new in 4.0", 'it-l10n-better-wp-security' ) . '</a><a href="#" class="itsec-notice-hide" onclick="document.location.href=\'?itsec_setup=no&_wpnonce=' . wp_create_nonce( 'itsec-nag' ) . '\';">&times;</a>
						</div>';

				}

			}

			if ( is_multisite() ) {
				add_action( 'network_admin_notices', 'ithemes_plugin_setup_notice' ); //register notification
			} else {
				add_action( 'admin_notices', 'ithemes_plugin_setup_notice' ); //register notification
			}

		}

		//if they've clicked a button hide the notice
		if ( isset( $_GET['itsec_setup'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'itsec-nag' ) ) {

			$options = $itsec_globals['data'];

			$options['setup_completed'] = true;

			update_site_option( 'itsec_data', $options );

			if ( is_multisite() ) {
				remove_action( 'network_admin_notices', 'ithemes_plugin_setup_notice' );
			} else {
				remove_action( 'admin_notices', 'ithemes_plugin_setup_notice' );
			}

			if ( sanitize_text_field( $_GET['itsec_setup'] ) == 'no' && isset( $_SERVER['HTTP_REFERER'] ) ) {

				wp_redirect( $_SERVER['HTTP_REFERER'], '302' );

			} else {

				wp_redirect( 'admin.php?page=itsec', '302' );

			}

		}

	}

	/**
	 * Display (and hide) support the plugin reminder.
	 *
	 * This will display a notice to the admin of the site only asking them to support
	 * the plugin after they have used it for 30 days.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function support_nag() {

		global $blog_id, $itsec_globals;

		if ( is_multisite() && ( $blog_id != 1 || ! current_user_can( 'manage_network_options' ) ) ) { //only display to network admin if in multisite
			return;
		}

		$options = $itsec_globals['data'];

		//display the notifcation if they haven't turned it off and they've been using the plugin at least 30 days
		if ( ( ! isset( $options['already_supported'] ) || $options['already_supported'] === false ) && $options['activation_timestamp'] < ( $itsec_globals['current_time_gmt'] - 2592000 ) ) {

			if ( ! function_exists( 'ithemes_plugin_support_notice' ) ) {

				function ithemes_plugin_support_notice() {

					global $itsec_globals;

					echo '<div class="updated" id="itsec_support_notice">
						<span>' . __( 'It looks like you\'ve been enjoying', 'it-l10n-better-wp-security' ) . ' ' . $itsec_globals['plugin_name'] . ' ' . __( 'for at least 30 days. Would you consider a small donation to help support continued development of the plugin?', 'it-l10n-better-wp-security' ) . '</span><input type="button" class="itsec-notice-button" value="' . __( 'Support This Plugin', 'it-l10n-better-wp-security' ) . '" onclick="document.location.href=\'?itsec_donate=yes&_wpnonce=' . wp_create_nonce( 'itsec-nag' ) . '\';">  <input type="button" class="itsec-notice-button" value="' . __( 'Rate it 5★\'s', 'it-l10n-better-wp-security' ) . '" onclick="document.location.href=\'?itsec_rate=yes&_wpnonce=' . wp_create_nonce( 'itsec-nag' ) . '\';">  <input type="button" class="itsec-notice-button" value="' . __( 'Tell Your Followers', 'it-l10n-better-wp-security' ) . '" onclick="document.location.href=\'?itsec_tweet=yes&_wpnonce=' . wp_create_nonce( 'itsec-nag' ) . '\';">  <input type="button" class="itsec-notice-hide" value="&times;" onclick="document.location.href=\'?itsec_no_nag=off&_wpnonce=' . wp_create_nonce( 'itsec-nag' ) . '\';">
						</div>';

				}

			}

			if ( is_multisite() ) {
				add_action( 'network_admin_notices', 'ithemes_plugin_support_notice' ); //register notification
			} else {
				add_action( 'admin_notices', 'ithemes_plugin_support_notice' ); //register notification
			}

		}

		//if they've clicked a button hide the notice
		if ( ( isset( $_GET['itsec_no_nag'] ) || isset( $_GET['itsec_rate'] ) || isset( $_GET['itsec_tweet'] ) || isset( $_GET['itsec_donate'] ) ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'itsec-nag' ) ) {

			$options = $itsec_globals['data'];

			$options['already_supported'] = true;

			update_site_option( 'itsec_data', $options );

			if ( is_multisite() ) {
				remove_action( 'network_admin_notices', 'ithemes_plugin_support_notice' );
			} else {
				remove_action( 'admin_notices', 'ithemes_plugin_support_notice' );
			}

			//take the user to paypal if they've clicked donate
			if ( isset( $_GET['itsec_donate'] ) ) {
				wp_redirect( 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=V647NGJSBC882', '302' );
				exit();
			}

			//Go to the WordPress page to let them rate it.
			if ( isset( $_GET['itsec_rate'] ) ) {
				wp_redirect( 'http://wordpress.org/plugins/better-wp-security/', '302' );
				exit();
			}

			//Compose a Tweet
			if ( isset( $_GET['itsec_tweet'] ) ) {
				wp_redirect( 'http://twitter.com/home?status=' . urlencode( 'I use ' . $itsec_globals['plugin_name'] . ' for WordPress by @iThemes and you should too - http://bit51.com/software/better-wp-security/' ), '302' );
				exit();
			}

			if ( sanitize_text_field( $_GET['itsec_no_nag'] ) == 'off' && isset( $_SERVER['HTTP_REFERER'] ) ) {

				wp_redirect( $_SERVER['HTTP_REFERER'], '302' );

			} else {

				wp_redirect( 'admin.php?page=itsec', '302' );

			}

		}

	}

}