<?php
/*
Plugin Name: Shortn.It
Plugin URI: http://docof.me/shortn-it
Help & Support: http://docof.me/shortn-it
Description: Personal, customized URL shortening for WordPress.
Version: 1.2.0
Author: David Cochrum
Author URI: http://www.docofmedia.com/

Copyright 2014  David Cochrum  (email : david@docofmedia.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Enable libcurl functions on unsupported installations

//require_once( 'libcurlemu-1.0.4/libcurlemu.inc.php' );

//	Define constant(s)
define( 'SHORTN_IT_META', '_shortn_it_url' );

//	Define global(s)
global $wpdb;

//	Create Shortn.It class
class Shortn_It {
	
	//	Initialize Short.In options upon activation
	public function __construct() {
		
		//	Add Shortn.It option defaults
		add_option( 'shortn_it_version', '1.0.0' );
		add_option( 'shortn_it_use_mobile_style', 'yes' );
		add_option( 'shortn_it_link_text', 'shortn url' );
		add_option( 'shortn_it_permalink_prefix', 'default' );
		add_option( 'shortn_it_permalink_custom', '/a/' );
		add_option( 'shortn_it_use_lowercase', 'yes' );
		add_option( 'shortn_it_use_uppercase', 'yes' );
		add_option( 'shortn_it_use_numbers', 'yes' );
		add_option( 'shortn_it_length', '5' );
		add_option( 'shortn_use_short_url', 'yes' );
		add_option( 'shortn_use_shortlink', 'yes' );
		add_option( 'shortn_it_registered', 'no' );
		add_option( 'shortn_it_registered_on', '0' );
		add_option( 'shortn_it_permalink_domain', 'default' );
		add_option( 'shortn_it_domain_custom', '' );
		add_option( 'shortn_it_hide_godaddy', 'no' );
		add_option( 'shortn_it_use_url_as_link_text', 'yes' );
		add_option( 'shortn_it_add_to_rss', 'yes' );
		add_option( 'shortn_it_add_to_rss_text', 'If you require a short URL to link to this article, please use %%link%%' );
		add_option( 'shortn_it_hide_nag', 'yes' );
		
		//	Create necessary actions
		add_action( 'init', array( &$this, 'shortn_it_do_redirect' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'shortn_it_enqueue_edit_scripts' ) );
		add_action( 'admin_menu', array( &$this, 'shortn_it_admin_panel' ) );
		add_action( 'admin_menu', array( &$this, 'shortn_it_sidebar' ) );
		add_action( 'plugins_loaded', array( &$this, 'shortn_it_url_widget_init' ) );
		add_action( 'save_post', array( &$this, 'shortn_it_save_url' ) );
		add_action( 'wp_ajax_shortn_it_json_check_url', array( &$this, 'shortn_it_json_check_url' ) );
		add_action( 'wp_head', array( &$this, 'shortn_it_short_url_header' ) );
		
		//	Create necessary filters
		add_filter( 'get_shortlink', array( &$this, 'shortn_it_get_shortlink' ), 10, 3 );
		add_filter( 'tweet_blog_post_url', array( &$this, 'get_shortn_it_url_from_long_url' ) );		// Support for Twitter Tools by Crowd Favorite
		
	}
	
	//	Redirect incoming Shortn.It URL page requests to the appropriate post
	public function shortn_it_do_redirect() {
				
		//	Get the matching post ID from the requested URI
		$post_id = $this->shortn_it_get_matching_post_id( $_SERVER['REQUEST_URI'] );
		
		//	If there's a match, send a new, temporary (in case it changes) redirect header 
		if($post_id != '' ) {
			$permalink = get_permalink( $post_id );
			header( 'Location: ' . $permalink, true, 302 );
			exit;
		}
		
	}
	
	//	Get the matching post ID from the given URL
	public function shortn_it_get_matching_post_id( $url ) {
		global $wpdb;
		
		//	If the URL doesn't begin with the chosen prefix, return nothing
		if( stripos( $url, $this->get_shortn_it_url_prefix() ) != 0 ) return '';
		
		$url_prefix = $this->get_shortn_it_url_prefix();

		//	Get the Shortn.It URL by removing the prefix
		$the_short = substr_replace( $url, '', 0, strlen($url_prefix) );
		
		//	Once the prefix has been removed, if there's nothing left but an empty string, return nothing
		if( $the_short == '')
			return '';
		
		//	Query the DB for any post that the Shortn.It URL matches the Shortn.It stored meta
		return $wpdb->get_var( 'SELECT `post_id` FROM `' . $wpdb->postmeta . '` where `meta_key` = "'. SHORTN_IT_META . '" and `meta_value` = "' . substr_replace( $url, '', 0, strlen( $url_prefix ) ) . '"' );
			
	}
	
	//	Get the Shortn.It URL prefix (if there is one)
	public function get_shortn_it_url_prefix() {
		
		//	If a custom prefix has been chosen, return the prefix, or else return the base "/"
		if( get_option( 'shortn_it_permalink_prefix' ) == 'custom' )
			return get_option( 'shortn_it_permalink_custom' );
		else
			return '/';
			
	}
	
	//	Get the complete Shortn.It URL
	public function get_shortn_it_url_permalink( $post_id ) {
		
		//	Get the Shortn.It URL
		$shortn_url = $this->get_shortn_it( $post_id );
		
		//	If no Shortn.It URL is associated with the post, return nothing, or else return the full URL
		if($shortn_url == '' )
			return '';
		else
			return $this->get_shortn_it_domain() . $this->get_shortn_it_url_prefix() . $shortn_url;
			
	}
	
	//	Get the Shortn.It URL for the post
	public function get_shortn_it( $post_id ) {
		
		//	If no post ID was provided, return nothing
		if( $post_id == '' )
			return '';
			
		//	Get the Shortn.It URL from the matching post meta
		$shortn_url = get_post_meta( $post_id, SHORTN_IT_META, true );
		
		//	If the Shortn.It URL was found, return it
		if( $shortn_url != '' )
			return $shortn_url;
			
		//	Or else make a Shortn.It URL, add it to the post meta, and return it
		else {
			$this->shortn_it_make_url( $post_id );
			$shortn_url = get_post_meta( $post_id, SHORTN_IT_META, true );
			if( $shortn_url != '' )
				return $shortn_url;
		}
		
	}
	
	//	Generate a Shortn.It URL and add it to the post meta
	private function shortn_it_make_url( $post ) {
		
		if($post != '' )
			update_post_meta($post, SHORTN_IT_META, $this->shortn_it_generate_string() );
			
	}
	
	//	Generate a random Shortn.It URL that fits the chosen criteria
	private function shortn_it_generate_string() {
		
		$length = get_option( 'shortn_it_length' );
		$valid_chars = '';
		$random_string = '';
		
		//	Create a string containing all valid characters to be used that fit the chosen criteria
		if( get_option( 'shortn_it_use_lowercase' ) == 'yes' )
			$valid_chars .= 'abcdefghijklmnopqrstuvwxyz';
		if( get_option( 'shortn_it_use_uppercase' ) == 'yes' )
			$valid_chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if( get_option( 'shortn_it_use_numbers' ) == 'yes' )
			$valid_chars .= '0123456789';
		
		//	Keep generating a random string until one is found that is not already in use
		$unqiue_url = false;
		while( ! $unqiue_url ) {
			// start with an empty random string
			$random_string = '';
		
			// count the number of chars in the valid chars string so we know how many choices we have
			$num_valid_chars = strlen( $valid_chars );
		
			// repeat the steps until we've created a string of the right length
			for( $i = 0; $i < $length; $i++ ) {
				// pick a random number from 1 up to the number of valid chars
				$random_pick = mt_rand( 1, $num_valid_chars );
		
				// take the random character out of the string of valid chars
				// subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
				$random_char = $valid_chars[ $random_pick-1 ];
		
				// add the randomly-chosen char onto the end of our string so far
				$random_string .= $random_char;
			}
			
			//	Determine if the random string is already set as a Shortn.It URL in another post
			$unqiue_url = ! $this->shortn_it_check_url( $random_string );
		}
		
		//	Once we have a unique Shortn.It URL, return it
		return $random_string;
		
	}
	
	//	Check if a string matches an existing Shortn.It URL
	private function shortn_it_check_url( $the_short ) {
		
		global $wpdb;
		
		// If the string is empty, return false
		if( $the_short == '' )
			return false;
		
		//	Query for any posts (of any type) that have a Shortn.It URL matching the string
		$post_id = $wpdb->get_var( 'SELECT `post_id` FROM `' . $wpdb->postmeta . '` where `meta_key` = "'. SHORTN_IT_META . '" and `meta_value` = "' . $the_short . '"' );
		
		//	Return true if there is a match, false if not
		return ( ! empty( $post_id ) );
		
	}
	
	//	Get the Shortn.It domain chosen in the options
	public function get_shortn_it_domain() {
		
		$shortn_it_permalink_domain = get_option( 'shortn_it_permalink_domain' );
		$shortn_it_domain_custom = get_option( 'shortn_it_domain_custom' );
		
		//	If the custom domain option was chosen, return the specified custom domain, or else return the site URL
		if( $shortn_it_permalink_domain != 'custom' )
			return get_bloginfo( 'url' );
		else
			return 'http://' . $shortn_it_domain_custom;
			
	}
	
	//	Enqueue scripts and styles to be used on post edit and creation pages
	public function shortn_it_enqueue_edit_scripts( $hook_suffix ) {
		
		//	Enqueue the scripts only on "post.php" and "post-new.php"
		if( 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix ) {
			//	Enqueue Javascript
			wp_enqueue_script( 'shortn_it_edit_scripts', plugins_url( 'js/shortn-it.js', __FILE__ ), array( 'jquery' ) );
			//	Pass "admin-ajax.php" URL for use in Javascript
			wp_localize_script( 'shortn_it_edit_scripts', 'vars', array( 'ajax' => admin_url( 'admin-ajax.php' ) ) );
			//	Enqueue CSS
			wp_enqueue_style( 'shortn_it_edit_scripts', plugins_url( 'css/shortn-it.css', __FILE__ ) );
		}
		
	}
	
	//	Add side meta boxes on post edit and creation pages
	public function shortn_it_sidebar() {
		
		//	For compaitibility with older versions of WP, check if the "add_meta_box" functionality exists, if not then do it the old way
		if( function_exists( 'add_meta_box' )) {
			//	Use "add_meta_box" to create the meta box for public post types
			$post_types = get_post_types( array( 'public' => true ) );
			foreach ( $post_types as $post_type )
				add_meta_box( 'shortn_it_box', __( 'Shortn.It', 'shortn_it_textdomain' ), array( &$this, 'shortn_it_generate_sidebar' ), $post_type, 'side', 'high' );
		} else {
			//	For older versions, add the meta box to post and page edit/create pages
			add_action( 'dbx_post_sidebar', array( &$this, 'shortn_it_generate_sidebar' ) );
			add_action( 'dbx_page_sidebar', array( &$this, 'shortn_it_generate_sidebar' ) );
		}
	}
	
	//	Generate the content within the Shortn.It meta box
	public function shortn_it_generate_sidebar() {
		
		//	Get the id of the currently edited post
		$post_id = esc_sql( $_GET['post'] );
		
		//	Get the Shortn.It URL for this post
		$shortn_url = $this->get_shortn_it( $post_id );
		//	Get the full Shortn.It URL for this post
		$shortn_it_permalink = $this->get_shortn_it_url_permalink( $post_id );
		//	If there isn't already a Shortn.It URL for this post, create one
		if($shortn_url == '' )
			$string = $this->shortn_it_generate_string();
			
		//	Populate the meta box with the Shortn.It URL information
		?>
			<p class="shortn_it_current_url">
				<?php wp_nonce_field( basename( __FILE__ ), 'shortn_it_nonce' ); ?>
				
				<?php _e( 'This post\'s shortned url ' . ( ( $shortn_url != '' ) ? 'is' : 'will be' ), 'shortn_it_textdomain' ); ?>:<br>
				<span class="shortn_it_url_prefix"><?php echo str_replace( 'http://', '', $this->get_shortn_it_domain() ) . $this->get_shortn_it_url_prefix(); ?></span>
				<code class="shortn_it_url_wrap">
					<?php echo ( ( $shortn_url != '' ) ? '<a href="' . $shortn_it_permalink . '">
						<span class="shortn_it_url">' . $shortn_url . '</span></a>' : 
					'<span class="shortn_it_url">' .$string . '</span>' ); ?>
				</code>
				<input type="text" id="shortn_it_url" name="shortn_it_url" class="hide" value="<?php echo ( ( $shortn_url != '' ) ? $shortn_url : $string ); ?>" autocomplete="off"></p>
			<?php if( get_option( 'shortn_it_hide_nag' ) == 'no' ) { ?>
			<a href="//docof.me/buy-shortn-it/"><? _e( 'Donate to keep Shortn.It alive.', 'shortn_it_textdomain' ); ?></a>
			<?php
			}
	}
	
	//	Register the Shortn.It sidebar widget
	public function shortn_it_url_widget_init() {
		
		wp_register_sidebar_widget( 'shortn-it-sidebar-widget', __( 'Shortn.It', 'shortn_it_textdomain' ), 'shortn_it_url_widget' );
		
	}
	
	//	Wrap the content for the Shortn.It sidebar widget appropriately
	private function shortn_it_url_widget( $args ) {
		
		extract( $args );
		echo $before_widget;
		echo $before_title; ?>Shortened Permalink<?php echo $after_title;
		$this->shortn_it_url_widget_content();
		echo $after_widget;
		
	}
	
	//	Populate the content for the Shortn.It sidebar widget
	private function shortn_it_url_widget_content() {
		
		echo '<p>This post\'s short url is <a href="';
		$this->the_full_shortn_url();
		echo '">';
		$this->the_full_shortn_url();
		echo '</a></p>';
		
	}
	
	//	Echo the Shortn.It URL for the current post within "the loop"
	public function the_shortn_url() {
	
		$post_id = get_the_ID();
		
		$shortn_url = $this->get_shortn_it( $post_id );
		if($shortn_url != '' )
			echo $shortn_url;
			
	}
	
	//	Echo an anchor tag of the Shortn.It URL for the current post within "the loop"
	public function the_shortn_url_link() {
		
		$post_id = get_the_ID();
		$shortn_url = $this->get_shortn_it( $post_id );
		
		if($shortn_url != '' ) {
			
			if( get_option( 'shortn_it_use_url_as_link_text' ) == 'yes' )
				$anchor_text = $shortn_it_permalink;
			else
				$anchor_text = get_option( 'shortn_it_link_text' );
			
			echo '<a href="' . $this->get_shortn_it_url_permalink( $post_id ) . '" class="shortn_it" rel="nofollow" title="shortened permalink for this page">' . htmlspecialchars( $anchor_text, ENT_QUOTES, 'UTF-8' ) . '</a>';
		}
		
	}
	
	//	Echo the full Shortn.It URL for the current post within "the loop"
	public function the_full_shortn_url() {
	
		$post_id = get_the_ID();
	
		$shortn_url = $this->get_shortn_it( $post_id );
		if( $shortn_url != '' )
			echo $this->get_shortn_it_url_permalink( $post_id );
			
	}
	
	//	Updat the Shortn.It URL meta once the post has been saved
	public function shortn_it_save_url( $post_id ) {
		
		// verify this came from the our screen and with proper authorization.
		if ( ! isset( $_POST['shortn_it_nonce'] ) || ! wp_verify_nonce( $_POST['shortn_it_nonce'], basename( __FILE__ ) ) )
			return $post_id;
		 
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;
		 
		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
			
		// OK, we're authenticated: we need to find and save the data   
		$post = get_post( $post_id );
		update_post_meta( $post_id, SHORTN_IT_META, esc_attr( $_POST['shortn_it_url'] ) );
		return esc_attr( $_POST['shortn_it_url'] );
		
	}
	
	//	Return a JSON string with matching post information (for use with "admin-ajax.php")
	public function shortn_it_json_check_url() {
		
		//	If the nonce doesn't match up, too bad
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], basename( __FILE__ ) ) )
			die ( 'Invalid Nonce' );
		
		//	Output JSON content type header
		header( 'Content-Type: application/json' );
		//	Get the id of the post that matches the requested string
		$match_id = $this->shortn_it_get_matching_post_id( $this->get_shortn_it_url_prefix() . $_REQUEST['string'] );
		//	If the match's ID is the same as the requested ID, proceed as if there were no match
		if($match_id == $_REQUEST['id'])
			$match_id = '';
		//	Echo a JSON string containing a bool of whether or not there was a match, the ID of the matching post, it's title, and the URL to edit that post
		echo json_encode( array ( 'exists' => ! empty( $match_id ), 'match_id' => $match_id, 'match_title' => get_the_title( $match_id ), 'edit_url' => get_edit_post_link( $match_id ) ) );
		die();
		
	}
	
	//	Add the shorturl and shortlink meta tags to the page header
	public function shortn_it_short_url_header() {
	
		$post_id = get_the_ID();
	
		$shortn_url = $this->get_shortn_it( $post_id );
		// Proceed if there is a Shortn.It URL in existance and at lease one of the shorturl or shortlink options are selected
		if($shortn_url != '' && ( get_option( 'shortn_use_short_url' ) == 'yes' || get_option( 'shortn_use_shortlink' ) == 'yes' )) {
			$shortn_it_permalink = $this->get_shortn_it_url_permalink( $post_id );
			
			//	Echo the shorturl and shortlink meta tags depending on whether or not the option for each was selected
			echo '	<!-- Shortn.It version ' . get_option( 'shortn_it_version' ) . " -->\n".
				( ( get_option( 'shortn_use_short_url' ) == 'yes' ) ? "\t" . '<link rel="shorturl" href="' . $shortn_it_permalink . '">' . "\n" : '' ) .
				( ( get_option( 'shortn_use_shortlink' ) == 'yes' ) ? "\t" . '<link rel="shortlink" href="' . $shortn_it_permalink . '">' . "\n" : '' ) .
				"\t" . '<!-- End Shortn.It -->' . "\n";
		}
		
	}
	
	//	Return the Shortn.It URL instead of WP's built-in shortlinks
	public function shortn_it_get_shortlink( $link, $id, $context ) {
		
		return $this->get_shortn_it_url_permalink( $id );
		
	}
	
	//	Return the Shortn.It URL matching the long post URL
	public function get_shortn_it_url_from_long_url( $long ) {
		
		//	Get the post ID from its long URL
		$post_id = url_to_postid( $long );
		//	Return the full Shortn.It URL
		return $this->get_shortn_it_url_permalink( $post_id );
		
	}
	
	//	Add an options page to the settings in the admin backend
	public function shortn_it_admin_panel() {
		
		//	Register the Shortn.It options page
		add_options_page( 'Shortn.It', 'Shortn.It', 'manage_options', 'shortn-it/shortn-it-options.php', array( &$this, 'shortn_it_settings' ) );
		
		//	If the current user has permission to at least edit posts, add a link to the settings menu
		if( current_user_can( 'edit_posts' ) && function_exists( 'add_submenu_page' ) )
			add_filter( 'plugin_action_links_' . __FILE__, array( &$this, 'shortn_it_plugin_actions' ), 10, 2 );
	}
	
	//	Get the Shortn.It settings page from "shortn-it-options.php"
	public function shortn_it_settings() {
		
		require_once( 'shortn-it-options.php' );
		
	}
	
	//	Build the link to the Shortn.It admin options page
	// Thanks to //wpengineer.com/how-to-improve-wordpress-plugins/ for instructions on adding the Settings link
	public function shortn_it_plugin_actions( $links ) {
		
		$settings_link = '<a href="options-general.php?page=shortn-it/shortn-it-options.php">' . __( 'Settings', 'shortn_it_textdomain' ) . '</a>';
		$links = array_merge( array( $settings_link ), $links ); // before other links
		return $links;
		
	}
	
	//	Store whether the Shortn.It plugin was activated and when
	public function shortn_it_register() {
		
		update_option( 'shortn_it_registered', 'yes' );
		update_option( 'shortn_it_registered_on', time());
		
	}
	
	//	Change the option to show/hide GoDaddy referral links
	public function shortn_it_hide_godaddy( $option ) {
		
		if($option == 'yes' )
			update_option( 'shortn_it_hide_godaddy', 'yes' );
		else
			update_option( 'shortn_it_hide_godaddy', 'no' );
			
	}
	
	//	Change the option to show/hide donation request links
	public function shortn_it_hide_nag( $option ) {
		
		if($option == 'yes' )
			update_option( 'shortn_it_hide_nag', 'yes' );
		else
			update_option( 'shortn_it_hide_nag', 'no' );
			
	}
	
}

$Shortn_It = new Shortn_It();
?>