<?php

class ITSEC_One_Version {

	function __construct() {

		if ( is_admin() && get_site_option( 'itsec_free_just_activated' ) && ( ! isset( $_SERVER['HTTP_REFERER'] ) || ( strpos( sanitize_text_field( $_SERVER['HTTP_REFERER'] ), 'action=upload-plugin' ) === false && strpos( sanitize_text_field( $_SERVER['HTTP_REFERER'] ), 'action=install-plugin' ) === false ) ) ) {

			add_action( 'admin_init', array( $this, 'deactivate_extra' ) );
			delete_site_option( 'itsec_free_just_activated' );

		}

	}

	/**
	 * Deactivates extra plugin.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function deactivate_extra() {

		$plugin = 'ithemes-security-pro/ithemes-security-pro.php';

		if ( is_multisite() ) {

			$active  = is_plugin_active_for_network( $plugin );
			$network = true;

		} else {

			$active  = is_plugin_active( $plugin );
			$network = null;

		}

		if ( $active === true ) {

			deactivate_plugins( $plugin, true, $network );
			add_site_option( 'itsec_had_other_version', true );
			wp_redirect( $_SERVER['HTTP_REFERER'], '302' );

		}

	}

}