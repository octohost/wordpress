WordPress in Docker
================

The way this is installed:

1. You have a source controlled copy of all Wordpress core, plugins, themes and HTML/CSS/JS.
2. All configuration is done via environment variables.
3. All uploads are sent to S3.
4. All emails are sent via SMTP.
5. Installs are painless \(via git push\) and all data is stored outside of the container.
6. When WordPress needs to be updated, it will be much easier with the items in source code control.
7. After a WordPress hack - you can just restart the container.

All required environment variables are detailed below:

```
# MySQL connection.
octo config:set wordpress-container-name/MYSQL_DATABASE 'test_wordpress'
octo config:set wordpress-container-name/MYSQL_USERNAME 'test_wordpress'
octo config:set wordpress-container-name/MYSQL_PASSWORD 'password-goes-here'
octo config:set wordpress-container-name/MYSQL_SERVER 'ip.add.ress.here'

# Access to the /phpwpinfo.php file - shows information about the installation.
octo config:set wordpress-container-name/PHPINFO_LOGIN 'username'
octo config:set wordpress-container-name/PHPINFO_PASSWORD 'password'

# Salts from https://api.wordpress.org/secret-key/1.1/salt/
# NOTE: YOU CANNOT HAVE SPACES IN THE SALTS - ALL SPACES MUST BE REPLACED.
octo config:set wordpress-container-name/LOGGED_IN_KEY 'hO@Ozt1N3:dkAt)9_d:I?+N[+&03>3DKnh=7jbTwi|g?W6Jc8bLT/LUY!,J_xLMi'
octo config:set wordpress-container-name/SECURE_AUTH_KEY 'Of2xM_H#K}8A-s|^/!y=19WtSsi:EzjCF_oo~dUl_`8<qtf=m^[GoZx?mdf~DEi4'
octo config:set wordpress-container-name/NONCE_KEY 'uiN6Y}eA]lk6`|8Ld%}MG>P9F>k.+D0gds+8.}*/J[J9Zg[_+C9*3&V^&.@;C9)!'
octo config:set wordpress-container-name/AUTH_KEY 'k#~[]/@W+x}YRmt+Ss#(vlK}u[75&7`*d:8;`/|0qbq.>)6hwy`T6pW0i0AAcl@5'
octo config:set wordpress-container-name/LOGGED_IN_SALT 'fIx-T+|!aW-Do_;@gf|fyi{s(-G8lxN;Se5`|Vk_0&ehL_6Vd>TNz#NbmR{k~4L|'
octo config:set wordpress-container-name/NONCE_SALT 'm)X1$u5K7aKg9m*afe,3uijkSq~sJB=e0R*@k$bO?Wlkm9L9p9q+){Z6i-+|}Q(#'
octo config:set wordpress-container-name/AUTH_SALT 'DeEE>N.g^g^*D+Hzx<A{uJJ8|mYB,vk6>38(%P#XWs?Z_y?}Ze6q2).w$7ZepEk<'
octo config:set wordpress-container-name/SECURE_AUTH_SALT 'VxB#{s^RAau>-7W<k1sfBAnwnH*^f[3~YR1G-)0KN]SWAAY!llo!f43lt;{N3+J4'

# For the S3 Uploads
octo config:set wordpress-container-name/AWS_ACCESS_KEY_ID 'ASDFGQWERTASDF'
octo config:set wordpress-container-name/AWS_SECRET_ACCESS_KEY 'much-longer-string-that-you-need-to-enter'
```

To install.

1. Setup a MySQL database.
2. In S3, grant your user permission to write to a bucket for your uploads.
3. On the octohost - set up all of the config variables.
4. Clone and push.
5. Activate the plugins and configure items in the admin interface.

```
git clone https://github.com/octohost/wordpress.git
cd wordpress
git remote add octo git@ip.address.here:wordpress.git
git push octo master
```

To work on the site locally:

1. Install [direnv](http://direnv.net/).
2. cp envrc .envrc # Change the exported values to be accurate.
3. `brew install wp-cli`
4. Install `wp server` - simple instructions are [here](https://github.com/wp-cli/wp-cli/wiki/Community-Packages).
5. Startup the server inside the wordpress directory, `wp server` - you should see some output that looks like this:

```
[] darron@~: cd Dropbox/src/wordpress/
direnv: loading .envrc
direnv export: +AUTH_KEY +AUTH_SALT +AWS_ACCESS_KEY_ID +AWS_SECRET_ACCESS_KEY +LOGGED_IN_KEY +LOGGED_IN_SALT +MYSQL_DATABASE +MYSQL_SERVER +MYSQL_USERNAME +NONCE_KEY +NONCE_SALT +SECURE_AUTH_KEY +SECURE_AUTH_SALT
[master] darron@~/Dropbox/src/wordpress: wp server
PHP 5.4.25 Development Server started at Tue May 13 23:34:24 2014
Listening on http://localhost:8080
Document root is /Users/darron/Dropbox/src/wordpress
Press Ctrl-C to quit.
[Tue May 13 23:34:34 2014] ::1:61179 [302]: /
[Tue May 13 23:34:34 2014] ::1:61186 [200]: /wp-includes/css/buttons.min.css?ver=3.9.1
[Tue May 13 23:34:34 2014] ::1:61187 [200]: /wp-admin/css/install.min.css?ver=3.9.1
[Tue May 13 23:34:34 2014] ::1:61188 [200]: /wp-includes/js/jquery/jquery.js?ver=1.11.0
```

Should be a relatively hassle and hack free container.
