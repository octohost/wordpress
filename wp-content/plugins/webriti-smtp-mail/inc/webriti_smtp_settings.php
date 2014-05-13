<div class="block ui-tabs-panel" id="option-ui-id-1" >
	<h2><?php _e('Webriti SMTP Mail','webritismtp');?></h2><hr>

	<?php if(isset($_POST['option_submit']))
		{   
			// Sender name
			$mail_from_name = $_POST['mail_from_name'];
			update_option('mail_from_name',$mail_from_name);
			// Sender email address
			$mail_from = $_POST['mail_from'];
			update_option('mail_from',$mail_from);				
				
			if($_POST['mailer']) {
				$mailer = $_POST['mailer'];
				update_option('mailer',$mailer);
			}
			
			if($_POST['smtp_host']) {
				$smtp_host = $_POST['smtp_host'];
				update_option('smtp_host',$smtp_host);
			}
			
			if($_POST['smtp_port']) {
				$smtp_port = $_POST['smtp_port'];
				update_option('smtp_port',$smtp_port);
			}
			
			if($_POST['smtp_ssl']) {
				$smtp_ssl = $_POST['smtp_ssl'];
				update_option('smtp_ssl',$smtp_ssl);
			}
			
			if($_POST['smtp_auth']) {
				$smtp_auth = $_POST['smtp_auth'];
				update_option('smtp_auth',$smtp_auth);
			}
			
			if($_POST['smtp_user']) {
				$smtp_user = $_POST['smtp_user'];
				update_option('smtp_user',$smtp_user);
			}
			
			if($_POST['smtp_pass']) {
				$smtp_pass = $_POST['smtp_pass'];
				update_option('smtp_pass',$smtp_pass);
			}			
		}
	?>	
	<h3><?php _e('Advanced Email Options', 'webritismtp'); ?></h3>	
	<form method="post" action="#">		
		<div class="section">
			<h3><?php _e('From Email', 'webritismtp'); ?></h3>
			<input name="mail_from" type="text" id="mail_from" value="<?php print(get_option('mail_from')); ?>" size="40" />			
			<span class="description"><?php _e('Enter your email address that emails should be sent from.', 'webritismtp'); ?></span>
		</div>
		
		<div class="section">
			<h3><?php _e('From Name', 'webritismtp'); ?></h3>
			<input name="mail_from_name" type="text" id="mail_from_name" value="<?php print(get_option('mail_from_name')); ?>" size="40"  /><span class="description"><?php _e('Enter your name that emails should be sent from.', 'webritismtp'); ?></span>
		</div>		
		<div class="section">
			<h3><?php _e('Mailer', 'webritismtp'); ?></h3>
			<p><input id="mailer_smtp" type="radio" name="mailer" value="smtp" <?php checked('smtp', get_option('mailer')); ?> />
			<?php _e('Send all WordPress emails via SMTP.', 'webritismtp'); ?></p>
		</div>		
		<h3><?php _e('SMTP Options', 'webritismtp'); ?></h3>
		<div style="  border-color: #C8C8C8;  border-style: solid; margin-top: 10px;    min-height: 40px;    padding-bottom: 0;    padding-left: 20px;   padding-right: 0;">
		<p><?php _e('Fill up your SMTP Details in below section.', 'webritismtp'); ?></p>
		<div class="section">
			<h3><?php _e('SMTP Host', 'webritismtp'); ?></h3>
			<input name="smtp_host" type="text" id="smtp_host" value="<?php print(get_option('smtp_host')); ?>" size="40" />
				<span class="description"><?php _e('Enter your Smtp Host Name, like for example google smtp host is smtp.gmail.com', 'webritismtp'); ?></span>
		</div>
		<div class="section">
			<h3><?php _e('SMTP Port', 'webritismtp'); ?></h3>
			<input name="smtp_port" type="text" id="smtp_port" value="<?php print(get_option('smtp_port')); ?>" size="40" />
			<span class="description"><?php _e('Enter Smtp Servers port, like for example google smtp port is <465>.', 'webritismtp'); ?></span>		
		</div>
		<div class="section">
			<h3><?php _e('Encryption', 'webritismtp'); ?></h3>			
			<input id="smtp_ssl_ssl" type="radio" name="smtp_ssl" value="ssl" <?php checked('ssl', get_option('smtp_ssl')); ?> />
			<label for="smtp_ssl_ssl"><span><?php _e('Use SSL encryption.', 'webritismtp'); ?></span></label><br />			
		</div>
		<div class="section">
		<h3><?php _e('Authentication', 'webritismtp'); ?></h3>	
		<input id="smtp_auth_true" type="radio" name="smtp_auth" value="true" <?php checked('true', get_option('smtp_auth')); ?> />
		<label for="smtp_auth_true"><span><?php _e('Yes: Use SMTP authentication.', 'webritismtp'); ?></span></label><br />
		<!--<span class="description"><?php //_e('If this is set to no, the values below are ignored.', 'webritismtp'); ?></span>-->
		</div>
		<div class="section">
			<h3><?php _e('Username ( Email Address )', 'webritismtp'); ?></h3>
		<input name="smtp_user" type="text" id="smtp_user" value="<?php print(get_option('smtp_user')); ?>" size="40" class="code" />
		</div>
		<div class="section">
		<h3><?php _e('Password', 'webritismtp'); ?></h3>
		<input name="smtp_pass" type="password" id="smtp_pass" value="<?php print(get_option('smtp_pass')); ?>" size="40" class="code" />
		</div><br>
		</div>
		<br>
		
		<div class="section">
			<input type="submit" name="option_submit" id="option_submit" class="btn btn-primary" value="<?php _e('Save Changes'); ?>" />
		</div>
		
	</form>	<br>
	<div class="section">	
		<h2><?php _e('Send a Test Email','webritismtp');?></h2><hr>	
		<form method="POST" action="#">
			<h3><?php _e('To:', 'webritismtp'); ?></h3>		
			<p><input name="webrit_to" type="text" id="webrit_to" value="" size="40" class="code" />
			<span class="description"><?php _e('Type an email address here and then click Send Test to generate a test email.', 'webritismtp'); ?></span></p>		
			<p class="submit"><input type="submit" name="webrit_test_action" id="webrit_test_action" class="btn btn-primary" value="<?php _e('Send Test', 'webritismtp'); ?>" /></p>
		</form>
		<?php // Send a test mail if necessary
			if (isset($_POST['webrit_test_action']) && isset($_POST['webrit_to'])) 
			{				
				global $phpmailer;				
				 if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) 
				{	require_once ABSPATH . WPINC . '/class-phpmailer.php';
					require_once ABSPATH . WPINC . '/class-smtp.php';
					$phpmailer = new PHPMailer( true );
				}
				
				// Set up the mail variables
				$webrit_to = $_POST['webrit_to'];
				$subject = 'Webriti WP Mail SMTP: ' . __('Test mail to ', 'webritismtp') . $webrit_to;
				$message = __('This is dummy massage generated by  Webriti SMTP Mail WordPress plugin.', 'webritismtp');
				
				// Send the test mail
				$result = wp_mail($webrit_to, $subject, $message);
				if($result) {
				?>				
				<div id="message" class="updated fade"><p><strong><?php _e('Test Message Sent', 'webritismtp'); ?></strong></p>
				<p><?php _e('The result is:', 'webritismtp'); ?></p>
				<pre><?php var_dump($result); ?></pre>
				<p><?php _e('The full debugging output is shown below:', 'webritismtp'); ?></p>
				<pre><?php var_dump($phpmailer); ?></pre>
				<p><?php _e('The SMTP debugging output is shown below:', 'webritismtp'); ?></p>
				<pre><?php echo $smtp_debug; ?></pre>
				</div>
				<?php  	unset($phpmailer);	} else { echo " Test email process is failed."; }
			} ?>
	</div>	
</div>