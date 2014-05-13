<div class="wrap" id="framework_wrap">   		
    <div id="content_wrap">
		<div class="webriti-header">
			<h2><a href="http://www.webriti.com/"><img class="logo_webriti" src="<?php echo WEBRITI_PLUGIN_DIR_URL; ?>/images/png.png"></a></h2>
		</div>
		<div class="webriti-submenu">		
			<h2><?php _e('Webriti SMTP Mail','webritismtp'); ?></h2>
			<div class="clear"></div>
        </div>
        <div id="content1">
			<div id="options_tabs" class="ui-tabs ">
				<ul class="options_tabs ui-tabs-nav" role="tablist" id="nav">					
					<li class="active">
						<div class="arrow"><div></div></div><a href="#" id="ui-id-1">
						<span class="icon home-theme"></span><?php _e('SMTP Mail Settings ','webritismtp');?></a>
					</li>				
					<li>
						<div class="arrow"><div></div></div><a href="#" id="ui-id-3">
						<span class="icon subscriber"></span><?php _e('Join Newsletter','webritismtp');?></a>
					</li>
					<li>
						<div class="arrow"><div></div></div><a href="#" id="ui-id-4">
						<span class="icon wordpress-pencil"></span><?php _e('WordPress Themes','webritismtp');?></a>
					</li>
					<div id="nav-shadow"></div>
                </ul>				
				<!--most 1 tabs webriti_option_settings --> 
				<?php require_once('webriti_smtp_settings.php'); ?>					
				<!--most 2 tabs subscriber --> 
				<?php require_once('subscriber.php'); ?>
				<!--most 3 tabs subscriber --> 
				<?php require_once('free_wordpress_themes.php'); ?>
			</div>		
        </div>
		<div class="webriti-submenu" id="webriti_submenu"> </div>
		<div class="clear"></div>
    </div>
</div>