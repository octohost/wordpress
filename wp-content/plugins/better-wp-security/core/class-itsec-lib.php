<?php

/**
 * Miscelaneous plugin-wide functions
 *
 * @package iThemes-Security
 * @since   4.0
 */
final class ITSEC_Lib {

	/**
	 * Loads core functionality across both admin and frontend.
	 */
	function __construct() {

		return;

	}

	/**
	 * Converts CIDR to ip range.
	 *
	 * Modified from function at http://stackoverflow.com/questions/4931721/getting-list-ips-from-cidr-notation-in-php
	 * as it was far more elegant than my own solution
	 *
	 * @param string $cidr cidr notation to convert
	 *
	 * @return array        range of ips returned
	 */
	public static function cidr_to_range( $cidr ) {

		$range = array();

		if ( strpos( $cidr, '/' ) ) {

			$cidr = explode( '/', $cidr );

			$range[] = long2ip( ( ip2long( $cidr[0] ) ) & ( ( - 1 << ( 32 - (int) $cidr[1] ) ) ) );
			$range[] = long2ip( ( ip2long( $cidr[0] ) ) + pow( 2, ( 32 - (int) $cidr[1] ) ) - 1 );

		} else { //if not a range just return the original ip

			$range[] = $cidr;

		}

		return $range;

	}

	/**
	 * Clear caches.
	 *
	 * Clears popular WordPress caching mechanisms.
	 *
	 * @since 4.0
	 *
	 * @param bool $page [optional] true to clear page cache
	 *
	 * @return void
	 */
	public static function clear_caches( $page = false ) {

		//clear APC Cache
		if ( function_exists( 'apc_store' ) ) {
			apc_clear_cache(); //Let's clear APC (if it exists) when big stuff is saved.
		}

		//clear w3 total cache or wp super cache
		if ( function_exists( 'w3tc_pgcache_flush' ) ) {

			if ( $page == true ) {
				w3tc_pgcache_flush();
				w3tc_minify_flush();
			}

			w3tc_dbcache_flush();
			w3tc_objectcache_flush();

		} else if ( function_exists( 'wp_cache_clear_cache' ) && $page == true ) {

			wp_cache_clear_cache();

		}

	}

	/**
	 * Creates appropriate database tables.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public static function create_database_tables() {

		global $wpdb;

		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		//Set up log table
		$tables = "CREATE TABLE " . $wpdb->prefix . "itsec_log (
				log_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				log_type varchar(20) NOT NULL DEFAULT '',
				log_function varchar(255) NOT NULL DEFAULT '',
				log_priority int(2) NOT NULL DEFAULT 1,
				log_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				log_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				log_host varchar(20),
				log_username varchar(20),
				log_user bigint(20) UNSIGNED,
				log_url varchar(255),
				log_referrer varchar(255),
				log_data longtext NOT NULL DEFAULT '',
				PRIMARY KEY  (log_id),
				KEY log_type (log_type),
				KEY log_date_gmt (log_date_gmt)
				) " . $charset_collate . ";";

		//set up lockout table
		$tables .= "CREATE TABLE " . $wpdb->prefix . "itsec_lockouts (
				lockout_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				lockout_type varchar(20) NOT NULL,
				lockout_start datetime NOT NULL,
				lockout_start_gmt datetime NOT NULL,
				lockout_expire datetime NOT NULL,
				lockout_expire_gmt datetime NOT NULL,
				lockout_host varchar(20),
				lockout_user bigint(20) UNSIGNED,
				lockout_username varchar(20),
				lockout_active int(1) NOT NULL DEFAULT 1,
				PRIMARY KEY  (lockout_id),
				KEY lockout_expire_gmt (lockout_expire_gmt),
				KEY lockout_host (lockout_host),
				KEY lockout_user (lockout_user),
				KEY lockout_username (lockout_username),
				KEY lockout_active (lockout_active)
				) " . $charset_collate . ";";

		//set up temp table
		$tables .= "CREATE TABLE " . $wpdb->prefix . "itsec_temp (
				temp_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				temp_type varchar(20) NOT NULL,
				temp_date datetime NOT NULL,
				temp_date_gmt datetime NOT NULL,
				temp_host varchar(20),
				temp_user bigint(20) UNSIGNED,
				temp_username varchar(20),
				PRIMARY KEY  (temp_id),
				KEY temp_date_gmt (temp_date_gmt),
				KEY temp_host (temp_host),
				KEY temp_user (temp_user),
				KEY temp_username (temp_username)
				) " . $charset_collate . ";";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		@dbDelta( $tables );

	}

	/**
	 * Gets location of wp-config.php
	 *
	 * Finds and returns path to wp-config.php
	 *
	 * @return string path to wp-config.php
	 *
	 * */
	public static function get_config() {

		if ( file_exists( trailingslashit( ABSPATH ) . 'wp-config.php' ) ) {

			return trailingslashit( ABSPATH ) . 'wp-config.php';

		} else {

			return trailingslashit( dirname( ABSPATH ) ) . 'wp-config.php';

		}

	}

	/**
	 * Return primary domain from given url
	 *
	 * Returns primary domain name (without subdomains) of given URL
	 *
	 * @param string  $address address to filter
	 * @param boolean $apache  [true] does this require an apache style wildcard
	 *
	 * @return string domain name
	 *
	 * */
	public static function get_domain( $address, $apache = true ) {

		preg_match( "/^(http:\/\/)?([^\/]+)/i", $address, $matches );

		$host = $matches[2];

		preg_match( "/[^\.\/]+\.[^\.\/]+$/", $host, $matches );

		if ( $apache == true ) {
			$wc = '(.*)';
		} else {
			$wc = '*.';
		}

		if ( ! is_array( $matches ) ) {
			return false;
		}

		// multisite domain mapping compatibility. when hide login is enabled,
		// rewrite rules redirect valid POST requests from MAPPED_DOMAIN/wp-login.php?SECRET_KEY
		// because they aren't coming from the "top-level" domain. blog_id 1, the parent site,
		// is a completely different, unrelated domain in this configuration.
		if ( is_multisite() && function_exists( 'domain_mapping_warning' ) ) {

			if ( $apache == true ) {
				return $wc;
			} else {
				return '*';
			}

		} elseif ( isset( $matches[0] ) ) {

			return $wc . $matches[0];

		} else {

			return false;

		}

	}

	/**
	 * Returns the root of the WordPress install
	 *
	 * @since 4.0.6
	 *
	 * @return string the root folder
	 */
	public static function get_home_root() {

		//homeroot from wp_rewrite
		$home_root = parse_url( home_url() );

		if ( isset( $home_root['path'] ) ) {

			$home_root = trailingslashit( $home_root['path'] );

		} else {

			$home_root = '/';

		}

		return $home_root;

	}

	/**
	 * Gets location of .htaccess
	 *
	 * Finds and returns path to .htaccess or nginx.conf if appropriate
	 *
	 * @return string path to .htaccess
	 *
	 * */
	public static function get_htaccess() {

		global $itsec_globals;

		if ( ITSEC_Lib::get_server() === 'nginx' ) {

			return $itsec_globals['settings']['nginx_file'];

		} else {

			return ABSPATH . '.htaccess';

		}

	}

	/**
	 * Returns the actual IP address of the user.
	 *
	 * Determines the user's IP address by returning the fowarded IP address if present or
	 * the direct IP address if not.
	 *
	 * @since 4.0
	 *
	 * @return  String The IP address of the user
	 *
	 */
	public static function get_ip() {

		//Just get the headers if we can or else use the SERVER global
		if ( function_exists( 'apache_request_headers' ) ) {

			$headers = apache_request_headers();

		} else {

			$headers = $_SERVER;

		}

		//Get the forwarded IP if it exists
		if ( array_key_exists( 'X-Forwarded-For', $headers ) && ( filter_var( $headers['X-Forwarded-For'],
		                                                                      FILTER_VALIDATE_IP,
		                                                                      FILTER_FLAG_IPV4 ) || filter_var( $headers['X-Forwarded-For'],
		                                                                                                        FILTER_VALIDATE_IP,
		                                                                                                        FILTER_FLAG_IPV6 ) )
		) {

			$the_ip = $headers['X-Forwarded-For'];

		} elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR',
		                             $headers ) && ( filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP,
		                                                         FILTER_FLAG_IPV4 ) || filter_var( $headers['HTTP_X_FORWARDED_FOR'],
		                                                                                           FILTER_VALIDATE_IP,
		                                                                                           FILTER_FLAG_IPV6 ) )
		) {

			$the_ip = $headers['HTTP_X_FORWARDED_FOR'];

		} else {

			$the_ip = $_SERVER['REMOTE_ADDR'];

		}

		return esc_sql( $the_ip );

	}

	/**
	 * Gets PHP Memory Limit.
	 *
	 * @since 4.0
	 *
	 * @return int php memory limit in megabytes
	 */
	public static function get_memory_limit() {

		return (int) ini_get( 'memory_limit' );

	}

	/**
	 * Returns the URI path of the current module
	 *
	 * @since 4.0
	 *
	 * @param string $file     the module file from which to derive the path
	 * @param bool   $with_sub include the subdirectory if needed
	 *
	 * @return string the path of the current module
	 */
	public static function get_module_path( $file, $with_sub = false ) {

		$directory = dirname( $file );

		$path_info = parse_url( get_bloginfo( 'url' ) );

		$path = trailingslashit( '/' . ltrim( str_replace( '\\', '/',
		                                                   str_replace( rtrim( ABSPATH, '\\\/' ), '', $directory ) ),
		                                      '\\\/' ) );

		if ( $with_sub === true && isset( $path_info['path'] ) ) {

			$path = $path_info['path'] . $path;

		}

		return $path;

	}

	/**
	 * Returns a psuedo-random string of requested length.
	 *
	 * @param int $length how long the string should be (max 62)
	 *
	 * @return string
	 */
	public static function get_random( $length ) {

		$string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		return substr( str_shuffle( $string ), mt_rand( 0, strlen( $string ) - $length ), $length );

	}

	/**
	 * Returns the server type of the plugin user.
	 *
	 * @return string|bool server type the user is using of false if undetectable.
	 */
	public static function get_server() {

		$server_raw = strtolower( filter_var( $_SERVER['SERVER_SOFTWARE'], FILTER_SANITIZE_STRING ) );

		//figure out what server they're using
		if ( strpos( $server_raw, 'apache' ) !== false ) {

			return 'apache';

		} elseif ( strpos( $server_raw, 'nginx' ) !== false ) {

			return 'nginx';

		} elseif ( strpos( $server_raw, 'litespeed' ) !== false ) {

			return 'litespeed';

		} else { //unsupported server

			return false;

		}

	}

	/**
	 * Determine whether the server supports SSL (shared cert not supported
	 *
	 * @return bool true if ssl is supported or false
	 */
	public static function get_ssl() {

		$url = str_replace( 'http://', 'https://', get_bloginfo( 'url' ) );

		if ( function_exists( 'wp_http_supports' ) && wp_http_supports( array( 'ssl' ), $url ) ) {
			return true;
		} elseif ( function_exists( 'curl_init' ) ) {

			$timeout    = 5; //timeout for the request
			$site_title = trim( get_bloginfo() );

			$request = curl_init();

			curl_setopt( $request, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $request, CURLOPT_VERBOSE, false );
			curl_setopt( $request, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $request, CURLOPT_HEADER, true );
			curl_setopt( $request, CURLOPT_URL, $url );
			curl_setopt( $request, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $request, CURLOPT_CONNECTTIMEOUT, $timeout );

			$data = curl_exec( $request );

			$header_size = curl_getinfo( $request, CURLINFO_HEADER_SIZE );
			$http_code   = intval( curl_getinfo( $request, CURLINFO_HTTP_CODE ) );
			$body        = substr( $data, $header_size );

			preg_match( '/<title>(.+)<\/title>/', $body, $matches );

			if ( $http_code == 200 && isset( $matches[1] ) && strpos( $matches[1], $site_title ) !== false ) {
				return true;
			} else {
				return false;
			}

		}

		return false;

	}

	/**
	 * Converts IP with a netmask wildcards to one with * instead
	 *
	 * @param string $ip ip to convert
	 *
	 * @return string     the converted ip
	 */
	public static function ip_mask_to_range( $ip ) {

		if ( strpos( $ip, '/' ) ) {

			$parts  = explode( '/', trim( $ip ) );
			$octets = array_reverse( explode( '.', trim( $parts[0] ) ) );

			if ( isset( $parts[1] ) && intval( $parts[1] ) > 0 ) {

				$wildcards = $parts[1] / 8;

				for ( $count = 0; $count < $wildcards; $count ++ ) {

					$octets[$count] = '[0-9]+';

				}

				return implode( '.', array_reverse( $octets ) );

			} else {

				return $ip;

			}

		}

		return $ip;

	}

	/**
	 * Converts IP with * wildcards to one with a netmask instead
	 *
	 * @param string $ip ip to convert
	 *
	 * @return string     the converted ip
	 */
	public static function ip_wild_to_mask( $ip ) {

		$host_parts = array_reverse( explode( '.', trim( $ip ) ) );

		if ( strpos( $ip, '*' ) ) {

			$mask           = 0; //used to calculate netmask with wildcards
			$converted_host = str_replace( '*', '0', $ip );

			//convert hosts with wildcards to host with netmask and create rule lines
			foreach ( $host_parts as $part ) {

				if ( $part === '*' ) {
					$mask = $mask + 8;
				}

			}

			$converted_host = trim( $converted_host );

			//Apply a mask if we had to convert
			if ( $mask > 0 ) {
				$converted_host .= '/' . $mask;
			}

			return $converted_host;

		}

		return $ip;

	}

	/**
	 * Determine whether we're on the login page or not
	 *
	 * @return bool true if is login page else false
	 */
	public static function is_login_page() {

		return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );

	}

	/**
	 * Checks if the jquery version saved is vulnerable to http://bugs.jquery.com/ticket/9521
	 *
	 * @return mixed|bool true if known safe false if unsafe or null if untested
	 */
	public static function safe_jquery_version() {

		$jquery_version = get_site_option( 'itsec_jquery_version' );

		if ( $jquery_version !== false and version_compare( $jquery_version, '1.6.3', '>=' ) ) {
			return true;
		} elseif ( $jquery_version === false ) {
			return NULL;
		}

		return false;

	}

	/**
	 * Forces the given page to a WordPress 404 error
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public static function set_404() {

		global $wp_query;

		status_header( 404 );

		if ( function_exists( 'nocache_headers' ) ) {
			nocache_headers();
		}

		$wp_query->set_404();
		$page_404 = get_404_template();

		if ( strlen( $page_404 ) > 1 ) {
			include( $page_404 );
		} else {
			include( get_query_template( 'index' ) );
		}

		die();

	}

	/**
	 * Increases minimum memory limit.
	 *
	 * This function, adopted from builder, attempts to increase the minimum
	 * memory limit before heavy functions.
	 *
	 * @since 4.0
	 *
	 * @param int $new_memory_limit what the new memory limit should be
	 *
	 * @return void
	 */
	public static function set_minimum_memory_limit( $new_memory_limit ) {

		$memory_limit = @ini_get( 'memory_limit' );

		if ( $memory_limit > - 1 ) {

			$unit = strtolower( substr( $memory_limit, - 1 ) );

			$new_unit = strtolower( substr( $new_memory_limit, - 1 ) );

			if ( 'm' == $unit ) {
				$memory_limit *= 1048576;
			} else if ( 'g' == $unit ) {
				$memory_limit *= 1073741824;
			} else if ( 'k' == $unit ) {
				$memory_limit *= 1024;
			}

			if ( 'm' == $new_unit ) {
				$new_memory_limit *= 1048576;
			} else if ( 'g' == $new_unit ) {
				$new_memory_limit *= 1073741824;
			} else if ( 'k' == $new_unit ) {
				$new_memory_limit *= 1024;
			}

			if ( (int) $memory_limit < (int) $new_memory_limit ) {
				@ini_set( 'memory_limit', $new_memory_limit );
			}

		}

	}

	/**
	 * Checks if user exists
	 *
	 * Checks to see if WordPress user with given id exists
	 *
	 * @param int $id user id of user to check
	 *
	 * @return bool true if user exists otherwise false
	 *
	 * */
	public static function user_id_exists( $user_id ) {

		global $wpdb;

		//return false if username is null
		if ( $user_id == '' ) {
			return false;
		}

		//queary the user table to see if the user is there
		$userid = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM `" . $wpdb->users . "` WHERE ID='%s';",
		                                          sanitize_text_field( $user_id ) ) );

		if ( $userid == $user_id ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Validates a list of ip addresses
	 *
	 * @param string $ip string of hosts to check
	 *
	 * @return array array of good hosts or false
	 */
	public static function validates_ip_address( $ip ) {

		//validate list
		$ip             = trim( filter_var( $ip, FILTER_SANITIZE_STRING ) );
		$ip_parts       = explode( '.', $ip );
		$error_handler  = NULL;
		$is_ip          = 0;
		$part_count     = 1;
		$good_ip        = true;
		$found_wildcard = false;

		foreach ( $ip_parts as $part ) {

			if ( $good_ip == true ) {

				if ( ( is_numeric( $part ) && $part <= 255 && $part >= 0 ) || $part === '*' || ( $part_count === 3 && strpos( $part,
				                                                                                                              '/' ) !== false )
				) {
					$is_ip ++;
				}

				switch ( $part_count ) {

					case 1: //1st octet

						if ( $part === '*' || strpos( $part, '/' ) !== false ) {

							return false;

						}

						break;

					case 2: //2nd octet

						if ( $part === '*' ) {

							$found_wildcard = true;

						} elseif ( strpos( $part, '/' ) !== false ) {

							return false;

						}

						break;

					case 3: //3rd octet

						if ( $part !== '*' ) {

							if ( $found_wildcard === true ) {

								return false;

							}

						} elseif ( strpos( $part, '/' ) !== false ) {

							return false;

						} else {

							$found_wildcard = true;

						}

						break;

					default: //4th octet and netmask

						if ( $part !== '*' ) {

							if ( $found_wildcard == true ) {

								return false;

							} elseif ( strpos( $part, '/' ) !== false ) {

								$netmask = intval( substr( $part, ( strpos( $part, '/' ) + 1 ) ) );

								if ( ! is_numeric( $netmask ) && 1 > $netmask && 31 < $netmask ) {

									return false;

								}

							}

						}

						break;

				}

				$part_count ++;

			}

		}

		if ( ( strpos( $ip, '/' ) !== false && ip2long( trim( substr( $ip, 0, strpos( $ip,
		                                                                              '/' ) ) ) ) === false ) || ( strpos( $ip,
		                                                                                                                   '/' ) === false && ip2long( trim( str_replace( '*',
		                                                                                                                                                                  '0',
		                                                                                                                                                                  $ip ) ) ) === false )
		) { //invalid ip

			return false;

		}

		return true; //ip is valid

	}

	/**
	 * Validates a file path
	 *
	 * Adapted from http://stackoverflow.com/questions/4049856/replace-phps-realpath/4050444#4050444 as a replacement for PHP's realpath
	 *
	 * @param string $path The original path, can be relative etc.
	 *
	 * @return bool true if the path is valid and writeable else false
	 */
	public static function validate_path( $path ) {

		// whether $path is unix or not
		$unipath = strlen( $path ) == 0 || $path{0} != '/';

		// attempts to detect if path is relative in which case, add cwd
		if ( strpos( $path, ':' ) === false && $unipath ) {
			$path = getcwd() . DIRECTORY_SEPARATOR . $path;
		}

		// resolve path parts (single dot, double dot and double delimiters)
		$path      = str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $path );
		$parts     = array_filter( explode( DIRECTORY_SEPARATOR, $path ), 'strlen' );
		$absolutes = array();

		foreach ( $parts as $part ) {

			if ( '.' == $part ) {
				continue;
			}

			if ( '..' == $part ) {

				array_pop( $absolutes );

			} else {

				$absolutes[] = $part;

			}

		}

		$path = implode( DIRECTORY_SEPARATOR, $absolutes );

		// resolve any symlinks
		if ( file_exists( $path ) && linkinfo( $path ) > 0 ) {
			$path = @readlink( $path );
		}

		// put initial separator that could have been lost
		$path = ! $unipath ? '/' . $path : $path;

		$test = @touch( $path . '/test.txt' );
		@unlink( $path . '/test.txt' );

		return $test;

	}

}
