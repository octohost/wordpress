<?php

class ITSEC_SSL {

	private $settings;

	function run() {

		$this->settings = get_site_option( 'itsec_ssl' );

		//Don't redirect any SSL if SSL is turned off.
		if ( isset( $this->settings['frontend'] ) && $this->settings['frontend'] >= 1 ) {

			add_action( 'template_redirect', array( $this, 'ssl_redirect' ) );
			add_filter( 'the_content', array( $this, 'replace_content_urls' ) );

		}

	}

	/**
	 * Check if current url is using SSL
	 *
	 * @return bool true if ssl false if not
	 *
	 */
	function is_ssl() {

		//modified logic courtesy of "Good Samaritan"
		if ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Redirects to or from SSL where appropriate
	 *
	 * @return void
	 */
	function ssl_redirect() {

		global $post;

		$hide_options = get_site_option( 'itsec_hide_backend' );

		if ( isset( $hide_options['enabled'] ) && $hide_options['enabled'] === true && $_SERVER['REQUEST_URI'] == ITSEC_Lib::get_home_root() . $hide_options['slug'] ) {

			return;

		}

		if ( is_singular() && $this->settings['frontend'] == 1 ) {

			$require_ssl = get_post_meta( $post->ID, 'itsec_enable_ssl', true );
			$bwps_ssl    = get_post_meta( $post->ID, 'bwps_enable_ssl', true );

			if ( $bwps_ssl == 1 ) {

				$require_ssl = 1;
				delete_post_meta( $post->ID, 'bwps_enable_ssl' );
				update_post_meta( $post->ID, 'itsec_enable_ssl', true );

			} elseif ( $bwps_ssl != 1 ) {

				delete_post_meta( $post->ID, 'bwps_enable_ssl' );

				if ( $require_ssl != 1 ) {
					delete_post_meta( $post->ID, 'itsec_enable_ssl' );
				}

			}

			if ( ( $require_ssl == 1 && $this->is_ssl() === false ) || ( $require_ssl != 1 && $this->is_ssl() === true ) ) {

				$href = ( $_SERVER['SERVER_PORT'] == '443' ? 'http' : 'https' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				wp_redirect( $href, 302 );

			}

		} else {

			if ( ( $this->settings['frontend'] == 2 && ! $this->is_ssl() ) || ( ( $this->settings['frontend'] == 0 || $this->settings['frontend'] == 1 ) && $this->is_ssl() ) ) {

				$href = ( $_SERVER['SERVER_PORT'] == '443' ? 'http' : 'https' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				wp_redirect( $href, 302 );

			}

		}

	}

	/**
	 * Replace urls in content with ssl
	 *
	 * @since 4.1
	 *
	 * @param string $content the content
	 *
	 * @return string the content
	 */
	public function replace_content_urls( $content ) {

		if ( $this->is_ssl() ) {

			$content = str_replace( site_url( '', 'http' ), site_url( '', 'https' ), $content );

		}

		return $content;
	}

}
