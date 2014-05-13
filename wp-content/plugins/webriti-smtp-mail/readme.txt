=== Easy SMTP Mail ===
Contributors:harimaliya,priyanshu.mittal
Tags:email, gmail, mail, mail smtp, outgoing mail, phpmailer, privacy, security, sendmail, smtp, ssl, tls, wordpress smtp, wp smtp, wp-phpmailer, wp_mail
Requires at least: 3.0
Tested up to: 3.8.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easy SMTP Plugin help you to send Emails from your WordPress Blog using your preferred SMTP Server. 

== Description ==

Webriti SMTP Mail can help us to send emails via SMTP instead of the PHP mail() function. It adds a settings page to "Dashboard"->"Settings"->"Webriti SMTP Email" where you can configure the email settings.

This plugin reconfigure's the wp_mail() function to send SMTP Mails. But you have to make sure that leaving Smtp Host name blank will not reconfigure the WordPress wp_mail() function.

The Practical use of this plugins comes when theme have a contact page, which on submit, shows you the successful message, but, still admin did not receive any email, here comes this smtp plugin which will makes your contact form workable. 

**Webriti Smtp Mail Plugin has following settings.**

###General Settings

The general settings section consists of the following options

1. From Email Address: The email address that was sending emails to users.
2. From Name: Specify your name which you want to show in the Email Headers.
3. SMTP Host: Your outgoing mail server (example: smtp.gmail.com)
4. SMTP Port: The port that will be used to relay outbound mail to your mail server (example: 465)
5. SMTP Authentication: Yes
6. Username: Enter your mail server username.
7. Password: Enter password you use to login to the mail server.

###Test Mail Utility
With this utility you can always check weither you have configured your SMTP Mail Server details correctly or not. 



== Installation ==
1. Download
2. Upload to your `/wp-contents/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress and Enjoy.

== Screenshots ==

1. Configure SMTP Settings.
2. Help and support.


== Frequently Asked Questions ==

= How to use gmail as smtp server? = 

For  using Gmail Smtp Server with this plugin then set the following details

* Smtp Host mail.gmail.com
* Smtp Port usually 465,
* Username: Your gmail username,
* Password: Your gmail password,
* SSL :On

== Changelog ==
= 1.1 =
* Small bug fixed.

= 1.0.4 =
* New Tab adding for Free wordPress Themes.

= 1.0.3 =
* Formatting not fixed in 1.0.2 is now set.

= 1.0.2 =
* Proper formatting of readme.txt file is done.

= 1.0.1 =
* Added Desctipin Text.

= 1.0 =
* Easy Smtp Mail Plugin Released

== Upgrade Notice ==
Always try to use latest version on repository.