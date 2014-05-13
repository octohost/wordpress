<?php
/*
Plugin Name: Easy SMTP Mail
Version: 1.1
Plugin URI: http://webriti.com/
Description: the wp_mail() function to use SMTP and set your SMTP settings or your wp_mail() function no need any configuration.
Author: harimaliya,priyanshu.mittal
Author URI: http://webriti.com/
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html

/*** The instantiated version of this plugin's class ***/
	
if (!function_exists('WebritiSmtpMail')) 
{	class WebritiSmtpMail
	{		
		/*** This plugin's identifier ***/	 
		const ID = 'webriti-smtp-mail';
		
		/*** This plugin's name ***/
		const NAME = 'Webriti SMTP Mail';
		
		/*** This plugin's version ***/
		const VERSION = '1.1';
		// Array of options and their default values
		
		/*** Has the internationalization text domain been loaded?  @var bool ***/
		public $loaded_textdomain = false;

		
		/*** Declares the WordPress action and filter callbacks ***/
		public function __construct() 
		{						
				/** Define Directory Location Constants */				
				define('WEBRITI_PLUGIN_DIR_PATH_INC', plugin_dir_path(__FILE__).'inc');
				
				/** Define https file Location Constants */
				define('WEBRITI_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ));				
				
				// Webriti smtp plugin hook file
				$this->load_plugin_hooks_file();
				
				// Webriti smtp form
				$this->webriti_smtp_form();
				
				// Webriti smtp textdomain
				$this->load_plugin_textdomain();
				
		}		
		/*** plugin text domain
		 */
		public function load_plugin_textdomain() {
			if (!$this->loaded_textdomain) {
				load_plugin_textdomain('webritismtp', false, self::ID . '/lang');
			}
		}	

		public function load_plugin_hooks_file()
		{
			// Webriti plugin option css and js
			function load_webriti_smtpmail_css_js()
			{	
				wp_enqueue_script('tab', WEBRITI_PLUGIN_DIR_URL .'js/option-panel-js.js');		
				wp_enqueue_style('option', WEBRITI_PLUGIN_DIR_URL .'css/style-option.css');
				wp_enqueue_style('bootstrap', WEBRITI_PLUGIN_DIR_URL .'css/bootstrap.css');
			}			
			
			// default data set plug-in activation 
			function webriti_smtp_activate()
			{	$wpms_options = array (
				'mail_from' => '', 
				'mail_from_name' => '',
				'mailer' => 'smtp',
				'mail_set_return_path' => 'false',
				'smtp_host' => 'localhost',
				'smtp_port' => 'port',
				'smtp_ssl' => 'ssl',
				'smtp_auth' => 'true',
				'smtp_user' => '',
				'smtp_pass' => ''
				);				
				foreach ($wpms_options as $name => $val)
				{	
					add_option($name, $val);
				}				
			}
			
			function webriti_smtp_mail_from ($orig) 
			{	
				if(is_email(get_option('mail_from')))
				{	return get_option('mail_from'); } 
				else 
				{
					// Get the site domain and get rid of www.
					$sitename = strtolower( $_SERVER['SERVER_NAME'] );
					if ( substr( $sitename, 0, 4 ) == 'www.' )
					{	$sitename = substr( $sitename, 4 );	}
					return $default_from = 'wordpress@' . $sitename;
					die;
				}				
				
			} // End of webriti_wp_mail_smtp_mail_from() function definition
			
			function webriti_smtp_mail_from_name($orig)
			{
				if(get_option('mail_from_name'))
				{ return get_option('mail_from_name'); }
				else
				{ return "Wordpress"; }
			}			
			
			register_activation_hook(__FILE__, 'webriti_smtp_activate');				
			
			// Add filters to replace the mail from name and emaila ddress
			add_filter('wp_mail_from','webriti_smtp_mail_from');
			
			add_filter('wp_mail_from_name','webriti_smtp_mail_from_name');
			// Add an action on phpmailer_init
			add_action('phpmailer_init','webriti_phpmailer_init_smtp');	
			
			// This code is copied, from wp-includes/pluggable.php as at version 2.2.2
			function webriti_phpmailer_init_smtp($phpmailer) 
			{	
				// Set the mailer type as per config above, this overrides the already called isMail method
				// Set Mailer value
				$phpmailer->Mailer = get_option('mailer');				
				// Set From value
				$phpmailer->From = get_option('mail_from');				
				// Set FromName value
				$phpmailer->FromName = get_option('mail_from_name');				
				// Set SMTPSecure value
				$phpmailer->SMTPSecure = get_option('smtp_ssl');					
				// Set Host value
				$phpmailer->Host = get_option('smtp_host');				
				// Set Port value
				$phpmailer->Port = get_option('smtp_port');   
				// If usrname option is not blank we have to use authentication
				if (get_option('smtp_user') != '') {
					$phpmailer->SMTPAuth = get_option('smtp_auth');
					$phpmailer->Username = get_option('smtp_user');
					$phpmailer->Password = get_option('smtp_pass');
				}					
				$phpmailer = apply_filters('wp_mail_smtp_custom_options', $phpmailer);
			} // End of phpmailer_init_smtp() function definition
			
		}
		
		function webriti_smtp_form()
		{
			function webriti_smtp_mail_admin_menu()
			{	
				$menu = add_options_page( __('Webriti SMTP Mail', 'webritismtp'), __('Webriti SMTP Mail', 'webritismtp'), 'manage_options', 'webriti_smtpmail_panels', 'webriti_smtpmail_options_panels_page');				
				add_action( 'admin_print_styles-' . $menu, 'load_webriti_smtpmail_css_js' );
			}

			// Wwbriti option page
			function webriti_smtpmail_options_panels_page()
			{	
				require_once(WEBRITI_PLUGIN_DIR_PATH_INC .'/webriti_smtpmail_options.php');
			}
			
			// admin menu hook and function
			add_action( 'admin_menu', 'webriti_smtp_mail_admin_menu');
		}
	} 
}/// class end 	
$webritiSmtpMail = new WebritiSmtpMail; 
?>