<?php

class ITSEC_Ban_Users {

	function run() {
		return null;
	}

	/**
	 * Inserts an IP address into the htaccess ban list.
	 *
	 * @since 4.0
	 *
	 * @param      $ip
	 * @param null $ban_list
	 * @param null $white_list
	 *
	 * @return void
	 */
	public static function insert_ip( $ip, $ban_list = null, $white_list = null ) {

		$settings = get_site_option( 'itsec_ban_users' );

		$host = sanitize_text_field( $ip );

		if ( $ban_list === null ) {

			$ban_list = $settings['host_list'];

		}

		if ( $white_list === null ) {

			$white_list = $settings['white_list'];

		}

		if ( ! in_array( $host, $ban_list ) && ! ITSEC_Ban_Users::is_ip_whitelisted( $host, $white_list ) ) {

			$ban_list[]            = $host;
			$settings['host_list'] = $ban_list;
			update_site_option( 'itsec_ban_users', $settings );
			add_site_option( 'itsec_rewrites_changed', true );
			ITSEC_Files::quick_ban( $host );

		}

	}

	/**
	 * Determines whether a given IP address is whitelisted
	 *
	 * @param  string  $ip_to_check ip to check
	 * @param  array   $white_ips   ip list to compare to if not yet saved to options
	 * @param  boolean $current     whether to whitelist the current ip or not (due to saving, etc)
	 *
	 * @return boolean               true if whitelisted or false
	 */
	public static function is_ip_whitelisted( $ip_to_check, $white_ips = null, $current = false ) {

		if ( $white_ips === null ) {

			$settings = get_site_option( 'itsec_ban_users' );

			$white_ips = $settings['white_list'];

		}

		if ( $current === true ) {
			$white_ips[] = ITSEC_Lib::get_ip(); //add current user ip to whitelist to check automatically
		}

		foreach ( $white_ips as $white_ip ) {

			$converted_white_ip = ITSEC_Lib::ip_wild_to_mask( $white_ip );

			$check_range = ITSEC_Lib::cidr_to_range( $converted_white_ip );
			$ip_range    = ITSEC_Lib::cidr_to_range( $ip_to_check );

			if ( sizeof( $check_range ) === 2 ) { //range to check

				$check_min = ip2long( $check_range[0] );
				$check_max = ip2long( $check_range[1] );

				if ( sizeof( $ip_range ) === 2 ) {

					$ip_min = ip2long( $ip_range[0] );
					$ip_max = ip2long( $ip_range[1] );

					if ( ( $check_min < $ip_min && $ip_min < $check_max ) || ( $check_min < $ip_max && $ip_max < $check_max ) ) {
						return true;
					}

				} else {

					$ip = ip2long( $ip_range[0] );

					if ( $check_min < $ip && $ip < $check_max ) {
						return true;
					}

				}

			} else { //single ip to check

				$check = ip2long( $check_range[0] );

				if ( sizeof( $ip_range ) === 2 ) {

					$ip_min = ip2long( $ip_range[0] );
					$ip_max = ip2long( $ip_range[1] );

					if ( $ip_min < $check && $check < $ip_max ) {
						return true;
					}

				} else {

					$ip = ip2long( $ip_range[0] );

					if ( $check == $ip ) {
						return true;
					}

				}

			}

		}

		return false;

	}

}