
=== iThemes Security (formerly Better WP Security) ===
Contributors: ithemes, ChrisWiegman, mattdanner, chrisjean
Donate link: http://ithemes.com
Tags: security, secure, multi-site, network, mu, login, lockdown, htaccess, hack, header, cleanup, ban, restrict, access, protect, protection, disable, images, image, hotlink, admin, username, database, prefix, wp-content, rename, directory, directories, secure, SSL, iThemes, BackupBuddy, Exchange, iThemes Exchange
Requires at least: 3.8
Tested up to: 3.9.1
Stable tag: 4.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The easiest, most effective way to secure WordPress in seconds.

== License ==
Released under the terms of the GNU General Public License.

== Description ==

= iThemes Security (formerly Better WP Security), #1 WordPress Security Plugin =

iThemes Security (formerly Better WP Security) gives you over 30+ ways to secure and protect your WordPress site. On average, 30,000 new websites are hacked each day. WordPress sites can be an easy target for attacks because of plugin vulnerabilities, weak passwords and obsolete software.

Most WordPress admins don't even know they’re vulnerable, but iThemes Security works to fix common holes, stop automated attacks and strengthen user credentials. With one-click activation for most features, as well as advanced features for experienced users, iThemes Security can help protect any WordPress site.

= Maintained and Supported by iThemes =

iThemes has been building and supporting WordPress tools since 2008. With our full range of WordPress <a href="http://ithemes.com/find/plugins/">plugins</a>, <a href="http://ithemes.com/find/themes/">themes</a> and <a href="http://webdesign.com">training</a>, WordPress security is the next step in providing you with everything you need to build the WordPress web.

= Get Support and Pro Features =

Get added peace of mind with professional support from our expert team and pro features to take your site's security to the next level with <a href="http://ithemes.com/security">iThemes Security Pro</a>.

= Obscure =

iThemes Security hides common WordPress security vulnerabilities, preventing attackers from learning too much about your site and away from sensitive areas like your site's login, admin, etc.

* Changes the URLs for WordPress dashboard areas including login, admin and more
* Completely turns off the ability to login for a given time period (away mode)
* Removes the meta "Generator" tag
* Removes theme, plugin, and core update notifications from users who do not have permission to update them
* Removes Windows Live Write header information
* Removes RSD header information
* Renames "admin" account
* Changes the ID on the user with ID 1
* Changes the Wordpress database table prefix
* Changes wp-content path
* Removes login error messages
* Displays a random version number to non administrative users

= Protect =

Hiding parts of your site is helpful, but won't prevent all attacks. In addition to obscuring sensitive areas of your WordPress site, iThemes Security works to protect it by blocking bad users and increasing the security of passwords and other vital information.

* Scans your site to instantly report where vulnerabilities exist and fixes them in seconds
* Bans troublesome user agents, bots and other hosts
* Prevents brute force attacks by banning hosts and users with too many invalid login attempts
* Strengthens server security
* Enforces strong passwords for all accounts of a configurable minimum role
* Forces SSL for admin pages (on supporting servers)
* Forces SSL for any page or post (on supporting servers)
* Turns off file editing from within Wordpress admin area
* Detects and blocks numerous attacks to your filesystem and database

= Detect =

iThemes Security monitors your site and reports changes to the filesystem and database that might indicate a compromise. iThemes Security also works to detect bots and other attempts to search vulnerabilities.

* Detects bots and other attempts to search for vulnerabilities
* Monitors filesystem for unauthorized changes
* Receive email notifications when someone gets locked out after too many failed login attempts or when a file on your site has been changed.

= Recover =

iThemes Security makes regular backups of your WordPress database, allowing you to get back online quickly in the event of an attack. Use iThemes Security to create and email database backups on a customizable schedule.

For complete site backups and the ability to restore or move WordPress easily, check out <a href="http://ithemes.com/purchase/backupbuddy">BackupBuddy</a> by iThemes.

= Other Benefits =

* Makes it easier for users not accustomed to WordPress to remember login and admin URLs by customizing default admin URLs
* Detects hidden 404 errors on your site that can affect your SEO such as bad links and missing images
* Removes the existing jQuery version used and replaces it with a safe version (the version that comes default with WordPress).

= Compatibility =

* Works on multi-site (network) and single site installations
* Works with Apache, LiteSpeed or NGINX (Note: NGINX will require you to manually edit your virtual host configuration)
* Features like database backups and file checks can be problematic on servers without a minimum of 64MB of RAM. All testing servers allocate 128MB to WordPress and usually don't have any other plugins installed.

= Translations =

* Spanish by <a href="http://www.webhostinghub.com/">Andrew Kurtis</a>

Please <a href="http://ithemes.com/contact" target="_blank">let us know</a> if you would like to contribute a translation.

= Warning =

Please read the installation instructions and FAQ before installing this plugin. iThemes Security makes significant changes to your database and other site files which can be problematic, so a backup is strongly recommended before making any changes to your site with this plugin. While problems are rare, most support requests involve the failure to make a proper backup before installation.

== Installation ==

NOTE: iThemes Security makes significant changers to your database and other site files which can be problematic, so a backup is strongly recommended before making any changes to your site with this plugin. While problems are rare, most support requests involve the failure to make a proper backup before installation.

1. BEFORE YOU BEGIN: Back up your Wordpress database, config file, and .htaccess file. We recommend using <a href="http://ithemes.com/purchase/backupbuddy">BackupBuddy</a>, our WordPress backup plugin for a complete site backup.
2. Upload the zip file to the `/wp-content/plugins/` directory
3. Unzip
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Visit the Security menu for checklist and options

DISCLAIMER: Under no circumstances do we release this plugin with any warranty, implied or otherwise. We cannot be held responsible for any damage that might arise from the use of this plugin.


== Frequently Asked Questions ==

= Why does iThemes Security require the latest WordPress version? Can't I use a slightly older version? =
* One of the best security practices for a WordPress site owner is keeping software up to date. Because of this, we only test this plugin on the latest stable version of WordPress and will only guarantee it works in the latest version.

= Will this plugin completely stop all attacks on my site? =
* No. iThemes Security is designed to help improve the security of your WordPress installation from many common attack methods, but it cannot prevent every possible attack. Nothing replaces diligence and good practice. This plugin makes it a little easier for you to apply both.

= Is "one-click" protection good enough? =
* One-click protection will help reduce the risk of attack on your site, but we recommend fixing as many high, medium and low priority items in the Security Status section as possible. If you have a plugin or theme that conflicts with an iThemes Security feature, we recommend deactivating the offending feature.

= Is this plugin only for new WordPress installs or can I use it on existing sites, too? =
* Many of the changes made by this plugin are complex and can break existing sites. While iThemes Security can be installed on either a new or existing site, we strongly recommend making a <a href="http://ithemes.com/purchase/backupbuddy" target="_blank">complete backup</a> of your existing site before applying any features included in this plugin.

= Will this plugin work on all servers and hosts? =
* iThemes Security requires Apache or LiteSpeed and mod_rewrite or NGINX to work.
* While this plugin should work on all hosts with Apache or LiteSpeed and mod_rewrite or NGINX, it has been known to experience problems in shared hosting environments where it runs out of resources such as available CPU or RAM. For this reason, it is extremely important that you make a backup of your site before installing on any existing site. If you run out of resources during an operation such as renaming your database table, you may need your backup to be able to restore access to your site.
* Finally, please make sure you have adequate RAM if you plan to use the file change detector or make large backups.

= Does this work with network or multisite installations? =
* Yes. We're in the process of developing more documentation, so we'll update this as soon as it's ready.

= Can I help? =
* Of course! We are in constant need of testers. In addition, we can always use help with translations for internationalization. <a href="http://ithemes.com/contributing-to-ithemes-security/">For more information on contributing to iThemes Security, visit this page</a>.

= What changes does this plugin make that can break my site? =
* iThemes Security makes significant changes to your database and other site files which can be problematic for existing WordPress sites. Again, we strongly recommended making a complete backup of your site before using this plugin. While problems are rare, most support requests involve the failure to make a proper backup before installation. DISCLAIMER: Under no circumstances do we release this plugin with any warranty, implied or otherwise. We cannot be held responsible for any damage that might arise from the use of this plugin.
* Note that renaming the wp-content directory will not update the path in existing content. Use this feature only on new sites or in a situation where you can easily update all existing links.
* <a href="http://ithemes.com/fixing-ithemes-security-lockouts/">Fixing iThemes Security Lockouts</a>
* <a href="http://ithemes.com/what-is-changed-by-ithemes-security/">What is Changed By iThemes Security</a>

= I've enabled the Enforce SSL option and it broke my site. How do I get back in? =
* Open your wp-config.php file in a text editor and remove the following 2 lines:
* define('FORCE_SSL_LOGIN', true);
* define('FORCE_SSL_ADMIN', true);

= Where can I get help if something goes wrong? =
* Official support for this plugin is available for <a href="http://ithemes.com/security/" target="_blank">iThemes Security Pro</a> customers. Our team of experts is ready to help.

Free support may be available with the help of the community in the <a href="http://wordpress.org/support/plugin/ithemes-security" target="_blank">WordPress.org support forums</a> (Note: this is community-provided support. iThemes does not monitor the WordPress.org support forums).

== Screenshots ==

1. After activation, iThemes Security guides you through important first steps
2. One-click secure button enables most security features
3. Instantly scan your site and see where you can improve your security with high, medium and low priority items
4. Simple, informative settings options show you what you need to know about each setting
5. Easy-to-navigate Security dashboard

== Changelog ==

= 4.2.2 =
* Don't allow empty file types in file change exclusions
* Add Sync integration for Away Mode
* Minor typo and other fixes
* Better cache clearing and formatting updates
* Make sure rewrite rules are updated on this update
* Remove extra (settings) items from admin bar menu (leave logs and important information)
* Add WP_CONTENT_DIR to system information on dashboard
* Move support nag to free version only and make sure it properly redirects
* Fix check for presence of BackupBuddy to work with BackupBuddy >=4.2.16.0
* Clean up details views on log pages
* Add username column to temp and lockouts tables
* Lockout usernames whether they exist or not
* Don't duplicate lockouts
* Fixed malformed lockout error on lockout message
* Don't display a host lockout when none exists
* Add Sync integration to release lockouts
* Improved reliability of brute force user lockouts

= 4.1.5 =
* Miscelaneous typos and other fixes
* Remove extra file lock on saving .htaccess, nginx.conf and wp-config.php. Only flock will be used in these operations
* Fixed a function not found error in the brute force module
* Improved content filtering in SSL so that more images and other content will link with appropriate protocol.
* Fixed hide backend in cases where a lockout has expired
* Miscelaneous typos and other fixes.

= 4.1.3 =
* Make sure "remove write permissions" works
* Better descriptions on white list
* Add pro table of contents if needed
* Make sure security admin bar item works
* Make sure lockout message only happens when needed
* Suppress errors on readlink calls
* Make sure class is present for permanent ban
* Make sure white list is an array
* Fix white listed IPs not working
* Log when Away-mode is triggered
* Make sure away mode file isn't accidently deleted
* Make sure away mode doesn't even allow access to the login form (as it didn't in 3.x)
* Enhance warnings on "Change content directory" settings
* Better descriptions on white lists
* Fixed XMLRPC label
* Better XMLRPC Dashboard status
* Don't allow logout action on wp-login.php with hide backend
* Better check for variable in SSL admin

= 4.0.27 =
* XMLRPC soft block should now work with WordPress mobile app
* Make sure uploads directory is only working in blog 1 in multisite
* Better checks for run method in module loader

= 4.0.25 =
* Make sure backup directory is present before trying to use it
* Make sure backup file method is respected on all backup operations
* Added ability to limit number of backups saved to disk
* Minor typo and other fixes
* Only load front-end classes as needed
* Add link to free support at .org forums
* Remove select(?ed) from suspicious query strings for 3.9 compatibility
* Fixed domain mapping issue (requires http://wordpress.org/plugins/wordpress-mu-domain-mapping/ domain mapping plugin)
* Remove array type errors on 404 pages
* Remove remaining create function calls
* Make sure logs directory is present before trying to use it
* Log a message when witelisted host triggers a lockout
* Don't create log files if they're not going to be used
* Add pro tab if pro modules need it
* Upgrade module loader to only load what is needed

= 4.0.23 =
* Fix sorting by count in 404 Logs
* Minor code cleanup
* Make sure all wp_enqueue_script dependencies are in proper format
* Reduce priority of hide backend init for better compatibility with other plugins
* SSL now logs users out when activating to prevent cookie conflicts
* When activating SSL Log out the user to prevent cookie conflicts
* Use LOCK_EX as a second file locking method on wp-config.php and .htaccess
* Minor code cleanup
* Make sure all wp_enqueue_script dependencies are in proper format

= 4.0.21 =
* Added ability to "soft" block XMLRPC to prevent pingback vulnerability while still allowing other access
* Updated "Suspicious queary strings" to not block plugin updates
* Update NGINX comment spam rewrite rules to better work with multi-site domain mapping
* Move 404 hook in hide backend from wp to wp_loaded
* Make sure super-admin role is maintained on multi-site when changing user id 1 and admin username at the same time
* Make sure all redirects for hide backend and ssl are 302, not 301
* Better resetting of SSL and disallow file editor on deactivation to account for more states
* Make sure hide backend works with registration
* Minor copy and other fixes
* Update nginx rewrite rule on comment spam when domain mapping is active
* Added the ability to disable file locking (old behavior)
* Better file lock release (try more than 1 method) before failing
* Don't automatically show file lock error on first attempt
* Added Spanish translation by <a href="http://www.webhostinghub.com/">Andrew Kurtis</a>

= 4.0.19 =
* Clean up away mode to prevent lockouts on update or other points

= 4.0.18 =
* Make sure unset admin user field remains if the other setting has been fixed
* Removed admin user from settings table of contents
* Make sure array input is trimmed in file change module
* Correct input type on file change settings sanitization
* Use full URL on file change warning redirect to prevent invalid target
* Reduce erroneous hide backend change warnings
* When accessing htaccess or wpconfig make sure opening settings changes are 664 instead of 644 to reduce issues
* Update hackrepair.com's Agents blacklist
* Make sure global settings save button matches others
* Fixed link in locout email
* Email address settings retain end of line
* Sanitize email addresses on save and not just use
* Make sure whitelist is actually an array before trying to process
* Make sure rewrite rules show on dashboard when file writing isnt allowed
* Added extra information to dashboard server information to help troubleshooting

= 4.0.16 =
* Fixed bug preventing file change scanning from advancing when chunked
* Don't autoload file list on non-multisite installations
* Make sure away mode settings transfer from 3.x or disable away mode
* Better descriptions on save buttons
* Admin use "Fix it" Correctly goes to advanced page

= 4.0.14 =
* Execute permanent ban on the correct lockout count, not the next one
* Updated quick ban rules to match standard ban rules (will work with proxy)
* Fixed an NGINX rule that didn't actually block XMLRPC.php
* Updated rule order on ban users
* Fixed a bug that could prevent away from from turning off in certain time configurations (this resulted in the return to homepage on login)
* Updated some function doc

= 4.0.12 =
* Added "Show intro" button next to screen options to bring the intro modal back
* Added ability to use HTML in error messages
* Minor copy and other tweaks
* Private posts will now work with hide backend
* Added an option for custom login action that can bypass hide login
* Allow admin-ajax.php to bypass hide backend
* Added filters for external backup plugins to register with the dashboard
* Enable theme compatibility mode by default
* Miscellaneous copy and function doc fixes

= 4.0.10 =
* only save post meta for ssl when the value is true
* fixed missing admin user settings if only one part had been changed
* SSL Redirection working properly on front end. No more redirect errors
* hide backend will warn of the new url when saving
* hide backend will now email the notification email(s) when the login area has been moved
* Added BackupBuddy coupon
* Added ability to manually purge log table

= 4.0.8 =
* Removed error message that could happen on user creation with strong passwords enabled
* Moved strong password js later in execution cycle to prevent errors
* More hide backend tweaks to cover remaining white screen issues
* Removed option to enqueue a new version of jQuery unless it is needed

= 4.0.7 =
* Removed extra quotes that could appear in user agents
* Removed error message on login page when jQuery replace in use
* Don't use WordPress rewrites for hide backend, we now create our own rewrite rule
* All modules now use newer upgrade method
* Fix modal dismiss button on settings page
* Ban users rules now should work with proxies
* Saving settings will always generate and write rewrite rules if file writing is allowed
* Hide backend now works with multisite and subdirectory installs
* Make sure tables exist if manually updating from 3.x
* Move admin user settings to advanced page
* Make sure logout happens after processing admin user changes
* All modules now rewritten to call rules on build
* Rename backup and logs folders when wp-content is renamed
* Delay file scan by at least 2 minutes when saving settings
* Added "theme compatibility" mode to remove errors in hide backend caused by themes conflicting with the feature.
* Fixed history.txt (for iThemes customers)
* Moved upgrade to separate function for more seamless update
* Upgrade system rewritten for better functionality
* Make sure 404 doesn't fail if there is not a 404.php in the theme
* Make sure WordPress root URLs render correctly
* Filewrite now only builds rules on demand.
* Fixed dismiss button on intro modal for small screens
* General cleanup and typo fixing
* New .pot file with updated iThemes .pot file generator

= 4.0.5 =
* Fixed away mode not allowing PM times.
* Fixed general copy typos.
* Non super admins will no longer see the "Security" menu item in the admin bar on multisite.
* Update to iThemes' icon-fonts library to account for ABSPATH set to '' or '/'.
* Fixed relative paths on Windows servers.
* Removed the pingback URL from the header if XML-RPC disabled.
* Added file locking to admin user operations to [hopefully] avoid duplicated users.
* 404 white list should transfer to global white list
* White list implementation working across all lockouts
* Add extra dismiss box to close welcome modal (fix for smaller screens)

= 4.0.2 =
* Fixed bug in conversion of wildcard ip (ie 131.2.1.*) to proper netmask. Should prevent 500 errors on sites.

= 4.0.1 =

* Fix for issue whereas a blank deny ip line could be entered into wp-config.php during update if banned users was used.

= 4.0.0 =

Better WP Security is now iThemes Security.

This release is a complete rewrite from the ground up. Special thanks to Cory Miller of iThemes.com and Chris Wiegman for realizing the vision for this plugin and how far we can go with it together.

* New Security Features
	* jQuery Scanner looks for vulnerable versions of jQuery in your theme and gives you the option to replace it with the current version of jQuery from WordPress core.
	* Remove author archives for users without any posts. This helps prevent bots from finding users on your site.
	* Force a unique nicename. This forces the user to choose a Nickname that is different from the login name which will be used for the author slug and other appropriate areas.
	* Disable PHP execution in uploads.

* Improvements
	* New UI with streamlined options and other settings
	* Hide features not in use
	* Smart feature selection for easier use
	* Central logs location
	* Ability to better customize notification and backup emails by sending to one or more addresses
	* Ability to save files anywhere on the host
	* Uses file-system locking for all critical operations
	* Global settings require setting options only once
	* Full BackupBuddy integration
	* Voluntary tracking of when options are turned on or off via Google Analytics
	* Hide backend no longer uses keys
	* Whitelist IPs for all lockouts
	* File change detection can run in batches for better resource usage
	* Backups can ignore unneeded table data such as logs
	* File change detection can ignore specified file types completely
	* All saved files now go to uploads
	* Ban users now has its own whitelist
	* Away mode and nearly all other features tweaked for speed and reliability
	* Module feature includes to accommodate future features as well as possibility of 3rd party features
	* No more insufficient permissions errors on settings tabs

= 3.6.6 =
* Added notice about upgrade

= 3.6.5 =
* Reintroduced InfiniteWP compatibility

= 3.6.4 =
* Updated readme
* Removed FooPlugins support box as iThemes begins integration of all support
* Removed InfiniteWP Compatibility

= 3.6.3 =
* Turned off iThemes Survey
* Updated iThemes email subscription box

= 3.6.2 =
* Fixed error message in above support widget when WordPress debug active.
* Fixed error when creating user in iThemes Exchange

= 3.6.1 =
* Fixed iThemes image path for case-sensitive
* Add iThemes ITSEC survey to help plan further updates
* Added Customizable email to support form

= 3.6 =
* Added WP Security Lock as a partner for sites that have already been compromised.
* Changed social information to iThemes
* Better domain support (Mark Boudreau)
* Add username to notification email (Andreas Geibert)
* Changed author to iThemes
* Added links to backup buddy and iThemes subscription
* Fixed inconsistent count in logs
* updated German translation by <a href="http://fluchtsportler.de" title="kniebremser">Rene Wolf</a>

= 3.5.6 =
* Updated Bulgarian translation by <a href="http://wordpress.org/support/profile/mhalachev">Martin Halachev</a>
* Removed all instances of the deprecated $wpdb->escape
* Fixed possible XSS issue (Github Issue #64 with patch from i0wn)
* Wrapped all wp_mail calls in function_exists checks as it no longer seems to be reliably available after plugins_loaded in WordPress 3.6
* Minor refactoring
* Added (.*) to Zues in hackrepair.com list to mitigate possible issues
* Typo correction on SSL options courtesy of <a href="http://karthost.com">Roy Randolph</a>.
* Changed minimum version to 3.6

= 3.5.5 =
* Fixed error that prevented manual backups from executing
* Updated Turkish translation by <a href="http://hakanertr.wordpress.com">Hakan Er</a>
* Updated shield logo by Martin Halachev
* Minor fixes for strict warnings occuring when on PHP 5.4
* Fix for lstat error for files in the ithemes-security/backups/ directory
* Fixed an error that prevented manual filecheck

= 3.5.4 =
* Bulgarian translation by <a href="http://arthlete.com/">Nikolay Kolev of Gymnastics and Bodyweight Tutorials</a>
* Chinese (Traditional) translation by Toine Cheung
* Fixed an XSS vulnerability in the logevent function. Fix by <a href="http://www.nccgroup.com/en/blog/?author=Richard%20Warren">Richard Warren</a>
* Updated Turkish by <a href="http://hakanertr.wordpress.com">Hakan Er</a>
* 404 Logs now only accessible via the link on the logs page (thank you Marc-Alexandre Montpas)
* Added .htaccess to protect saved backups (thank you Marc-Alexandre Montpas)
* Added extra sanitization when downloading host info from database (was sanitized on upload) (thank you Marc-Alexandre Montpas)
* Brazilian Portuguese translation by <a href="http://profiles.wordpress.org/rafaelfunchal">Rafael Funchal</a>
* German translation by <a href="http://fluchtsportler.de" title="kniebremser">Rene Wolf</a>
* Removed timezone from email lockout notifications (GitHub Issue #35)
* Better variable checking to prevent error messages
* Force user 0 when logging filechecking (GitHub Issue #7)
* CSS update for MP6 from shivapoudel
* Small tweak to prevent email notifications being sent when they shouldn't
* Cleaned up variable checking throughout to eliminate activation errors if php errors or WP_DEBUG is turned on
* Added further checks to reduce errors if file change log is invalid
* Memory should now display correctly in file change email
* Use maybe_unserialize instead of unserialize
* Added option to filter foreign charcters as part of filter suspicious query string
* Updated .pot file

= 3.5.3 =
* Simplified Chinese by <a href="http://haib.in">æµ·æ»¨</a>
* Persian by <a href="http://forum.wp-parsi.com/user/1469-ibrahim/">Ibrahim Jafari</a>
* Typo correction by ihuston
* Fixed Bit51 Google+ Link
* Better proxy support for ban users by kalvindukes
* Updated Spanish translation by <a href="http://pabloromero.org">Pablo Romero</a>
* Updated Readme
* Updated .pot file

= 3.5.2 =
* Fixed error message that could appear when creating backups
* Correct Changelog not displaying correctly on WordPress.org after version 3.5.1

= 3.5.1 =
* Replaced Turkish language version lost in when tagging 3.5 in the WordPress.org repository
* Solved a conflict with other Bit51 plugins that use the common Bit51 class

= 3.5 =
* Integrate with Foo Plugins support system
* Fixed role translation call for Strong password enforcement
* Turkish by <a href="http://hakanertr.wordpress.com">Hakan Er</a>
* Random version number no longer strips unrelated GET variables for better compatibility
* Upgrading no longer automatically rewrites .htaccess and wp-config resulting in much improved reliability
* Fixed possible error when login fails and PHP is set to report errors to screen

= 3.4.10 =
* Replaced feed with standard WordPress feed
* Added better error checking for feed should Feedburner (or any other provider) kill it again

= 3.4.9 =
* More secure user query thanks to John Cave
* Greatly improved intl date handling by <a href="http://www.sceric.net/">SCUDELLER Eric</a>
* Added: French translation by <a href="http://www.sceric.net/">SCUDELLER Eric</a>
* Fixed: bug preventing Jetpack's Infinite Scroll from working with long URL protection

= 3.4.8 =
* Fixed error message that may occur if InfiniteWP is not installed.

= 3.4.7 =
* Added compatibility with InfiniteWP (http://infinitewp.com/)
* Updated default ban list as it was a little too restrictive for my taste
* Added export of 404 logs in .csv format (experimental)
* Add X-Forwarded-For ability to IP logging
* Minor bug and typo fixes

= 3.4.6 =
* Updated usability on ban lists
* Ban list threshold now triggered on hit and not 1 after

= 3.4.5 =
* Replace database override of awaymode with wp-config constant
* Filecheck override is now done through wp-config constant
* Added option to generate new secret key in hide backend
* Added Slovak translation by Erich SzabÃ³
* Possible Google Maps fix for Apache
* Improved time handling for away mode, lockouts, logs, and more
* Added Tagalog translation by Hanne of â€‹<a href="http://pointen.dk/">http://pointen.dk/</a>
* Various table updates from Michael Conover (<a href="twitter.com/sidtheduck">@sidtheduck</a>) at <a href="http://sidtheduck.com">sidtheduck.com</a>.
* Load plugin as global to reduce multiple executions
* Fixed rewrite rules for banned hosts
* Updated .pot file
* Other minor bugfixes and refactoring

= 3.4.4 =
* fixed input vulnerability found at http://packetstormsecurity.org/files/116317/ithemes-security-3.4.3-Cross-Site-Scripting.html
* fixed email address in footer information on backup screen
* file check exclusions should now work properly for individual files.
* One-click protection is now part of the install script.
* Won't log or even check 404 if feature is off.
* Don't clear cache during away check. Let's see instead if the transients â€¦
* Don't clear Supercache page cache on clear logs

= 3.4.3 =
* Only clear WP Supercachce when full page cache clearing is required

= 3.4.2 =
* Gravatars will no longer dissappear after changing user 1 id
* Better cache clearing when changing options
* Reworked away mode for better cache handling
* Subdirectory redirects should now work
* Fixed error message on logout
* Fixed password reset email link
* Will no longer duplicate IPs in ban list when entered via auto-ban
* Minor style updates
* Better namespacing in content.php
* Removed 38.0.0.0/8 from hackrepair.com blacklist
* remove yandex from hackrepair.com blacklist

= 3.4.1 =
* Clean all logs when checked
* Better logic for SSL checking
* Removed echo statement in Filecheck
* Highly compressed NGINX rules
* Added to "Filter Suspicious Query String" Logic

= 3.4 =
* Added Russian Translation
* Updated Hindi contributor to <a href="http://outshinesolutions.com/">Outshine Solutions</a>
* Prevented file change warning from displaying to non-admins
* Fixed error causing multiple backup emails
* Added ability to change ID of user with ID 1
* Fixed bug in plugin base url
* Added extra warnings and "escape route" for away mode
* Fixed hide backend issues since WordPress 3.4
* Lookup IP addresses directly from logs
* Fixed dbdelta errors on upgrade
* Updated form styles and appearance
* Added tabs to settings pages for increased usability
* Duplicate IP addresses won't be saved to banned list
* Wildcards now correctly save to banned list
* Suppress errors on filecheck arrays
* Fixed link to permalink settings in hide backend
* Added extra save buttons to system tweaks
* Added logging memory usage to filecheck
* Updated readme.txt
* Updated .pot

= 3.3 =
* More checks to ensure blank "Deny from" lines don't appear in .htaccess
* Added host and user agent blacklist by <a href="http://hackrepair.com">HackRepair.com</a>
* Changed "Options All -Indexes" to "Options -Indexes" in .htaccess rules
* Added log view for all bad login attempts to view logs
* Always show .htaccess and wp-config.php changes in Dashboard
* Database backups no longer turn on automatically with one-click secure.
* Replaced unique key in database tables with primary key (tested in 3.4)

= 3.2.7 =
* Hindi translation by Outshine Solutions
* Spanish translation by Pablo Romero

= 3.2.6 =

* Lithuanian translation by Vincent G
* Fixed bug that could allow blank hosts in .htaccess for ban users
* Removed obsolete translations from before version 3.0
* Fixed various typos
* Numerous minor bug fixes
* Support moved back to WordPress.org forums

= 3.2.5 =

* Users can now specify email address for database backups
* Fixed bug throwing error when saving changes to existing users
* Corrected typo in intl hook
* List banned IPs on separate lines for readability
* Replaced all instances of Wordpress with WordPress
* Logs no longer show errors when records are cleared while viewing file change details
* File check will no longer automatically enable on servers with low RAM
* An extra database key has been introduced to easily disable file checking if it causes memory errors
* updated .pot
* Sanitize ALL server variables to prevent XSS vulnerability

= 3.2.4 =

* Added configurable email address for all email notifications
* Added ability to turn off dashboard warning for file check
* Password reset form will now require strong passwords if configured
* Ability to automatically blacklist an IP address after a specified number of lockouts
* Various minor bugfixes
* Turning off front-end ssl will stop ssl redirect loops in sites with an existing ssl implementation
* Updated language and explanations for various features
* Updated .pot

= 3.2.3 =

* Fixed date offset in log views
* Fixed site admin renaming for multisite users
* Fixed typos throughout
* Block concat MySQL command
* Deny access to readme.txt in protect files
* Fixed 404 table description
* Added domain name to email notifications
* Improved folder check login
* Suppress error messages for file-check operations

= 3.2.2 =

* Fixed 500 error when ban-users in enabled and IP or agents list are empty
* Fixed error that logged bad logins and 404s even when features were turned off

= 3.2.1 =

* Added choice to completely disable front-end SSL, enable per page, or enable site-wide
* Fixed login URL on new user email when new user is created by an existing user and hide backend is enabled
* Default all SSL to off for new installations
* Fixed strong password roles to work correctly
* A little 418 humor
* Updated .pot file
* Updated readme.txt

= 3.2 =

* File checker checks for changed files
* SSL for individual pages and posts
* One-click protection removes all .htaccess and wp-config.php options
* Option to not allow the plugin to write to .htaccess and wp-config.php
* Tweaked NGINX rewrite rules
* Moved SSL options to separate page for better usability
* Tables now display in native WordPress format
* Updated language throughout
* Tweaked Apache rewrite rules
* Various minor bug-fixes
* New installation video (see plugin homepage)
* Updated .pot file

= 3.1 =

* Significantly less resource usage
* Fixed white screen errors on load
* Fixed backup scheduling errors
* updated .pot file
* numerous minor bugfixes

= 3.0.12 =

* Displays log messages for all lockouts
* Scheduled backup times can be much more easily customized
* Setting wp-config.php and .htaccess to 0444 is now optional
* Updated .pot file
* Fixes to Apache/LiteSpeed rules and NGINX rules
* Numerous minor bugfixes

= 3.0.11 =

* Fixed bug with redundant backup caller in admin script

= 3.0.10 =

* Better LiteSpeed support
* Better database backup scheduling
* Better line spacing in .htaccess and wp-config.php
* WordPress 3.3.1 now required
* Status area now links to proper options and not top of tweaks page
* NGINX rule fixes
* admin-ajax.php now works even with hide backend
* error surpression on file operations
* update .pot
* Many language updates
* Better update script for multisite installs

= 3.0.9 =

* Fixed multi-site issue due to 3.0.8 update support fix
* Fixed awaymode settings issues

= 3.0.8 =

* Fixed improper php open tags
* Fixed erroneous PHP_EOL in nginx rules
* LiteSpeed support
* Better update support (not relying on activation hook anymore)
* Added abstract keyword to bit51.php
* Removed itsec references in bit51.php
* updated .pot file

= 3.0.7 =

* Changed method of end of line character technique for better cross-platform server compatibility
* Fixed 2 lines of <? in content.php replacing them with <?php

= 3.0.6 =

* Another fix to the "line 2072" error. This would be a lot easier with a Windows host as those are the only folks that seem to have the issue

= 3.0.5 =

* Changes to language on ban users page
* fixed "line 2072" error

= 3.0.4 =

* Changed IP banning to only accept * wildcards for ranges
* All host banning is not done via server configuration rather than php
* Numerous minor bugfixes

= 3.0.3 =

* Fixed bug in backup file path

= 3.0.2 =

* Fixed default options on saving
* Fixed setup options
* Other minor bugfixes

= 3.0.1 =

* Turned off flag that caused plugin settings to reset on update.

= 3.0 =

* Complete rewrite from the ground up
* Menu changes
* UI completely rewritten
* Now supports NGINX
* Scheduled database backups
* Added ability to block user agents in addition to hosts
* Numerous bugfixes

= 2.18 =

* Another attempt to fix the login error that started with 2.16. Changed logic for determining hide backend feature.

= 2.17 =

* Fixed an error that started with version 2.16 and prevented user from being able to login to the WordPress Dashboard.

= 2.16 =

* Fixed login link in new user email after breaking it in version 2.15

= 2.15 =

* Now loads all features at init to [hopefully] eliminate function not found errors

= 2.14 =

* Bugfixes from 2.13
* Removed randomized version for all logged-in users due to conflicts with admin-bar

= 2.13 =

* Bugfixes from 2.12

= 2.12 =

* Bugfixes from 2.11

= 2.11 =

* Fixed login-slug in new user email
* Fixed login slugs throughout site
* Remove reset-password options
* Improved rewrite rules (I would credit the author but I'm afraid in a bone headed move I never wrote down the author with the notes)
* No longer loads pluggable

= 2.10 =

* Added Romanian translation by Luke Tyler

= 2.9 =

* readme.txt typo correction
* Added ability to whitelist hosts and ip addresses for intrusion detection
* intrusion detection now lists 404 errors found to help ease troubleshooting
* intrusion detection now records referrer to make tracking 404 errors easier
* corrected error when attempting to list multiple hosts when banning users

= 2.8 =

* German Translation by Stefan Meier

= 2.7.1 =

* Fixed a logic bug caused by changes in 2.7

= 2.7 =

* Fixed a bug preventing login lockouts from releasing.

= 2.6 =

* Added link to author of Italian Translation
* Fixed bug preventing the "Ban Users" function from working.

= 2.5 =

* Italian translation by Paolo Stivanin
* Support information moved to separate page for easy access
* Minor bug fixes

= 2.4 =

* Fixed a bug that generated a 404 error when clicking the reset password link that is emailed to users
* Added the option to customize the error message displayed for the login lockdown and intrusion detection lockouts

= 2.3 =

* Fixed various typos
* meta.php require_once now works correctly
* fixed bug in which .htaccess and wp-config.php were not reporting correct permissions
* Version is now hidden on admin pages except for multi-site

= 2.2 =

* Emergency fix restoring version number display for backend as previous fix made multi-site installations unusable

= 2.1 =

* Added options to customize intrusion detection to allow custom lockout duration and error threshold
* Time now correctly displays for intrusion detection lockouts and lockouts are released at the correct time
* Version number now hidden for all users without administrator role on backend
* Saved hide backend key to database to allow for easier use in other plugins that link directly to wp-login.php (still has to be manually entered in each affected plugin)
* Will now use the correct wp-config.php file if it is located outside of the directory used for the wordpress installation
* Empties APC cache (when installed) after changing wp-content directory preventing the necessity to restart Apache
* Fixed display bugs for login and intrusion lockout lists.

= 2.0 =

* Now supported by Bit51.com
* Removed blocking of http HEAD requests to improve integration with social networking APIs such as Twitter
* French translation by Claude ALTAYRAC

= 1.9 =

* Error message on lockouts more ambiguous
* Added email notification for intrusion detection lockouts
* Added Bahasa Indonesia (Indonesian) translation by Belajar SEO, Jasa SEO Indonesia

= 1.8.1 =

* Minor bug fixes

= 1.8 =

* Changed plugin description
* Improved translation support
* Added Turn off file editor in WordPress backend
* Improved accuracy of version checking when upgrading
* Ban Users now allows for more than just IP address, it has been renamed accordingly

= 1.7 =

* Renamed detect 404s section to intrusion detection to include upcoming features
* general spelling and grammer corrections
* Moved configuration to network dashboard for multisite installations
* Improved multisite support
* Warns if .htaccess or wp-config.php files aren't writable where needed
* Added icon to menu for easier identification
* Cleaned up and refined status information

= 1.6 =

* Fixed WLManifest link removal from header
* Added nofollow to all meta links
* "Away Mode" page now displays current time even when feature has not been enabled
* Status page now shows system information
* htaccess contents moved to status page
* fixed fatal activation error affecting php 5.2 users

= 1.5 =

* Meta links update correctly when changing backend links

= 1.4 =

* Fixed another issue that prevented the "htaccess" options page from displaying on some hosts

= 1.3 =

* Fixed an issue that prevented the "htaccess" options page from displaying on some hosts

= 1.2 =

* Finished support for localization

= 1.1 =

* Fixed bug that prevented cleaning old lockouts from database

= 1.0 =

* More code documentation
* Added warnings to changing content directory (until I can find a good way to update all existing content)
* Added options to clean old entries out of the database
* Fixed minor typos throughout

= 0.16.BETA =

* Updated Homepage

= 0.15.BETA =

* Fixed error for potential conflicts with old htaccess rules

= 0.14.BETA =

* Removed hotlinking protection as it has been deemed to be outside the scope of this project
* Removed protocol from hide backend htaccess rules for consistency between http and https
* Combined all httaccess rules into single iThemes Security Block
* 404 check now ignores all logged in users

= 0.13.BETA =

* Fixed a bug that could erase part of the wp-config file= 0.12.BETA =

* Changing content directories should no longer break sites that were upgraded from versions prior to 3.0

= 0.11.BETA =

* Update to project homepage and other minor changes

= 0.10.BETA =

* Removed WP version check from status page as it was redundant
* On uninstall wp-content location will be returned to default
* Fixed setup error
* Error checking now correctly identifies database table prefix
* Rendom version # generator now removes version number from scripts and css where it can (thanks to Dave for this)

= 0.9.BETA =

* Bug fixes
* Internationalization improvements

= 0.8.BETA =

* Fixed more critical bugs

= 0.7.BETA =

* Fixed more critical bugs

= 0.6.BETA =

* Fixed 2 critical bugs

= 0.5.BETA =

* Major refactoring
* Streamline database tables
* Numerous bugfixes
* Code documentation and continued internationalization prep

= 0.4.BETA =

* Changed the main menu name to "Security"
* Minimum requirement raised to 3.0.2
* Begun code documentation and intl prep

= 0.3.BETA =

* Numerous bugfixes
* 404 check will NOT ban logged in users
* Lockdown rules no longer apply to logged in users

= 0.2.BETA =

* Updated hidebe to handle standard logout links
* Numerous other bugfixes

= 0.1.BETA =

* Finished status reporting
* Force SSL for admin pages (on supporting servers)
* Change wp-content path

= ALPHA 11 =

* Added security checklist
* Added option to rename existing admin account
* Added option to change DB table prefix
* Various bugfixes

= ALPHA 10 =

* Added more htaccess security options
* All htaccess options have been moved to their own page
* Added simple intrusion detection based on 404s
* Bugfixes and code optimization

= ALPHA 9 =

* Deactivating now removes all htaccess areas and turns off associated options
* Enforce strong passwords for all users of a given minimum role
* Minor bug fixes

= ALPHA 8 =

* Added various .htaccess options to strengthen file security
* Modified "hide backend" rewrite rules to work with multi-site
* Removed non-security hide-backend options
* Various bug fixes
* Renamed "General" options page to "System Tweaks" to avoid confusion
* Added more options to clean up WordPress headers
* Added options to remove plugin notifications from non-super admin users

= ALPHA 7 =

* Continued code refactoring and bug-fixes
* Improved error handling and upgrade support
* Combined status and support options pages

= ALPHA 6 =

* Added sanitization and validation to user input
* Added "away mode" to limit backend access by time
* Script no longer dies when logged out and instead returns to homepage.

= ALPHA 5 =

* Complete refactor of the existing code
* Divided settings sections for better UX
* Added htaccess checks
* Redesigned options system for less database calls
* Reduced table usage from 4 to 2
* Added email notifications for login limiter
* Added complete access blocker for login limiter

= ALPHA 4 =

* Added login limiter to limit invalid attempts
* various Bug fixes

= ALPHA 3 =

* Corrected error display
* Added registration rules regardless of whether registrations are on or off.
* Added "Display random version to non-admins"
* Fixed rewrite rules on hide admin urls so going to the admin slug will work whether the user is logged in or not
* Added crude upgrade warning to warn of old (not so great) rewrite rules

= ALPHA 2 =

* Optimized and commented code
* Added uninstall function
* Numerous fixes to bugs and logic

= 0.1 ALPHA =

* First alpha release including simple featureset.

== Upgrade Notice ==

= 4.2.2 =
Better WP Security is now iThemes Security with new features and a greatly improved code base. We recommend disabling Better WP Security before upgrading to 4.2.0 if you are not already on 4.0 or greater.
