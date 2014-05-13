<?php

class ITSEC_Help_Admin {

	function run() {

		if ( is_admin() ) {

			$this->initialize();

		}

	}

	/**
	 * Add meta boxes to primary options pages
	 *
	 */
	public function add_admin_meta_boxes() {

		add_meta_box(
			'itsec_help_info',
			__( 'Help', 'it-l10n-better-wp-security' ),
			array( $this, 'add_help_intro' ),
			'security_page_toplevel_page_itsec_help',
			'normal',
			'core'
		);

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

		add_action( 'itsec_add_admin_meta_boxes', array( $this, 'add_admin_meta_boxes' ) ); //add meta boxes to admin page

	}

	/**
	 * Build and echo the away mode description
	 *
	 * @return void
	 */
	public function add_help_intro() {

		echo '<p>' . __( 'Website security is a complicated subject, but we have experts that can help.', 'it-l10n-better-wp-security' ) . '</p>';

		echo '<p><strong>' . __( 'Community Support from WordPress.org', 'it-l10n-better-wp-security' ) . '</strong><br />';
		echo  __( 'Since you are using the free version of iThemes Security from WordPress.org, you can get free support from the WordPress community.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p><a class="button-secondary" href="http://wordpress.org/support/plugin/better-wp-security" target="_blank">' . __( 'Get Free Support', 'it-l10n-better-wp-security' ) . '</a></p>';
		echo '<hr>';

		echo '<p><strong>' . __( 'Support & Pro Features with iThemes Security Pro', 'it-l10n-better-wp-security' ) . '</strong><br />';
		echo  __( 'Get added peace of mind with professional support from our expert team and pro features to take your site security to the next level with iThemes Security Pro.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p><a class="button-secondary" href="http://www.ithemes.com/security" target="_blank">' . __( 'Get iThemes Security Pro', 'it-l10n-better-wp-security' ) . '</a></p>';
		echo '<hr>';

		echo '<p><strong>' . __( 'Have a Pro Secure Your Site', 'it-l10n-better-wp-security' ) . '</strong><br />';
		echo  __( 'Be sure your site has been properly secured by having one of our security experts tailor your security settings to the specific needs of your site.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p><a class="button-secondary" href="http://ithemes.com/security/ithemes-security-professional-setup" target="_blank">' . __( 'Have an expert secure my site', 'it-l10n-better-wp-security' ) . '</a></p>';
		echo '<hr>';

		echo '<p><strong>' . __( 'Hack Repair', 'it-l10n-better-wp-security' ) . '</strong><br />';
		echo  __( 'Has your site been hacked? Contact one of our recommended hack repair partners to get things back in order.', 'it-l10n-better-wp-security' ) . '</p>';
		echo '<p><a class="button-secondary" href="http://ithemes.com/security/wordpress-hack-repair" target="_blank">' . __( 'Get hack repair', 'it-l10n-better-wp-security' ) . '</a></p>';

	}

}