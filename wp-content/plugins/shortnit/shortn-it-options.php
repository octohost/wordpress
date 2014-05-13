<?php

//	Create an instance of the Shortn.It class
$Shortn_It = new Shortn_It();

//	Enable/disable GoDaddy.com referal links
if( isset( $_GET[ 'hide_godaddy' ] ) ) { $Shortn_It->shortn_it_hide_godaddy( $_GET[ 'hide_godaddy' ] ); }
//	Enable/disable donation request links
if( isset( $_GET[ 'hide_nag' ] ) ) { $Shortn_It->shortn_it_hide_nag( $_GET[ 'hide_nag' ] ); }

//	Store Shortn.It options in variables for easy access
$shortn_it_permalink_prefix = get_option( 'shortn_it_permalink_prefix' );
$shortn_it_permalink_custom = get_option( 'shortn_it_permalink_custom' );
$shortn_it_permalink_domain = get_option( 'shortn_it_permalink_domain' );
$shortn_it_domain_custom = get_option( 'shortn_it_domain_custom' );
$shortn_it_hide_godaddy = get_option( 'shortn_it_hide_godaddy' );

//	Generate an example Shortn.It URL
$shortn_it_example_url = $Shortn_It->shortn_it_generate_string();

//	Store Shortn.It domain for later use
$shortn_it_domain = $Shortn_It->get_shortn_it_domain();

?>
<style type="text/css">
@import url( '<?php echo plugins_url( 'css/shortn-it.css', __FILE__ ); ?>' );
</style>
			<script src="<?php echo plugins_url( 'js/shortn-it-options.js', __FILE__ ); ?>" charset="utf-8"></script>
			<div class="wrap">
<form method="post" action="options.php" autocomplete="off">
<?php wp_nonce_field( 'update-options' ); ?>
				<?php if( get_option( 'shortn_it_hide_nag' ) == 'no' ) { ?>
				<div class="shortn-wrapper panel" id="shortnItDonate">
					<div class="shortn-section-header">
						<h3><?php _e( 'Donate to Shortn.It', 'shortn_it_textdomain' ); ?></h3>
					</div>
					<p><strong>Love Shortn.It?</strong> Why not <a href="//docof.me/buy-shortn-it/">buy a license</a>? It's only <strong>$5</strong>. Licensing Shortn. It will endow you with benefits including but not limited to:</p>
					<ul>
						<li>Support calls and emails totally getting answered</li>
						<li>Allowing the developer to buy essential items, like food</li>
						<li>(he will most likely spend your money on Maker's Mark)</li>
						<li>Removal of heavy $5 from bank account</li>
						<li>Huge amounts of good karma, if you're into that</li>
						<li>If not, maybe you'll just feel good about it?</li>
						<li>The confidence to crush your enemies</li>
					</ul>
					<p id="buy-button"><a href="?page=shortn-it%2Fshortn-it-options.php&amp;hide_nag=yes" class="cssbutton gray">Nah</a> <a id="buy-it" class="cssbutton blue" href="//docof.me/buy-shortn-it/">I'd like to buy Shortn.It</a></p>
				</div>
				<?php } ?>

				<div class="shortn-wrapper panel" id="domain">
					<div class="shortn-section-header">
						<h3><?php _e( 'Domain &amp; URL Prefix' ); ?></h3>
						<div class="upgrade-now">
							<p><?php if( get_option( 'shortn_it_hide_nag' ) == 'no' ) { ?><a href="//docof.me/buy-shortn-it/">Support Shortn.It</a> | <?php } ?>
								<a href="//docof.me/shortn-it">Help</a></p>
						</div>
					</div>
					
					<?php if( $shortn_it_hide_godaddy != 'yes' ) { ?>
					<p id="godaddy"><strong>Want a shorter domain? <a href="//domai.nr">Find one using Domai.nr</a> and <a href="//www.godaddy.com">register it with GoDaddy</a></strong>.</p>
					<?php } ?>
					
					<div class="shortn-controls-group">
						<h3>Domain</h3>
						<p class="shortn_it_permalink_domain"><label class="radio"><input name="shortn_it_permalink_domain" type="radio" value="default" <?php if( $shortn_it_permalink_domain != 'custom' ) { echo 'checked="checked"'; } ?> class="tog">Default (Main Domain)</label></p>
						<p class="shortn_it_permalink_domain"><label class="radio"><input name="shortn_it_permalink_domain" type="radio" value="custom" class="tog" <?php if($shortn_it_permalink_domain == 'custom' ) { echo 'checked="checked"'; } ?>/>Different Domain</label></p>
						
						<p id="custom-domain-input" <?php if( $shortn_it_permalink_domain != 'custom' ) { echo 'style="display: none;"'; } ?>><label for="shortn_it_domain_custom">Domain Name: </label><input name="shortn_it_domain_custom" id="shortn_it_domain_custom" style="width: 264px;" type="text" value="<?php if( isset( $shortn_it_domain_custom ) ) { echo $shortn_it_domain_custom; } ?>" <?php if( ! isset( $shortn_it_domain_custom ) ) { echo 'placeholder="example.com"'; } ?> class="regular-text code"></p>
					</div>
					
					<div class="shortn-controls-group">
						<h3>Prefix</h3>
						<p><label class="radio"><input name="shortn_it_permalink_prefix" type="radio" value="default" <?php if( $shortn_it_permalink_prefix == 'default' ) { echo 'checked="checked"'; } ?> class="tog"> Default (No Prefix)</label></p>
						<p><label class="radio"><input name="shortn_it_permalink_prefix" type="radio" value="custom" class="tog" <?php if( $shortn_it_permalink_prefix != 'default' ) { echo 'checked="checked"'; } ?>/>Custom Prefix</label></p>
						
						<p id="custom-prefix-input" <?php if( $shortn_it_permalink_prefix == 'default' ) { echo 'style="display: none;"'; } ?>><?php echo $shortn_it_domain; ?><input name="shortn_it_permalink_custom" style="width: 196px;" id="shortn_it_permalink_custom" type="text" value="<?php echo $shortn_it_permalink_custom; ?>" class="regular-text code"></p>
					</div>
					<div class="shortn-look-like">
						<h3>Your short URLs will look like:</h3>
						<p><span class="domain"><?php echo str_replace( 'http://' , '', $shortn_it_domain ); ?></span><span class="prefix"><?php echo ( $shortn_it_permalink_prefix != 'default' ) ? $shortn_it_permalink_custom : '/'; ?></span><span class="url"><?php echo $shortn_it_example_url; ?></span></p>
					</div>
				</div>

				<div class="shortn-wrapper panel" id="advanced">
					<div class="shortn-section-header">
						<h3>URL Generation</h3>
						<div class="upgrade-now">
							<p><?php if( get_option( 'shortn_it_hide_nag' ) == 'no' ) { ?><a href="//docof.me/buy-shortn-it/">Support Shortn.It</a> | <?php } ?>
								<a href="//docof.me/shortn-it">Help</a></p>
						</div>
					</div>
					
					<div style="float: left;">
						<label for="shortn_it_length">Shortn URL Length: </label>
					
						<input name="shortn_it_length" id="shortn_it_length" type="number" style="width: 50px;" value="<?php echo get_option( 'shortn_it_length' ); ?>" class="regular-text">
					</div>
				
					<label for="shortn_it_use_lowercase" class="checkbox">
					<input name="shortn_it_use_lowercase" type="checkbox" id="shortn_it_use_lowercase" value='yes' <?php if( get_option( 'shortn_it_use_lowercase' ) == 'yes' ) { echo 'checked="checked"'; } ?>>
					Use lowercase letters (<code>a-z</code>)</label>
				
					<label for="shortn_it_use_uppercase" class="checkbox">
					<input name="shortn_it_use_uppercase" type="checkbox" id="shortn_it_use_uppercase" value='yes' <?php if( get_option( 'shortn_it_use_uppercase' ) == 'yes' ) { echo 'checked="checked"'; } ?>>
					Use uppercase letters (<code>A-Z</code>)</label>
				
					<label for="shortn_it_use_numbers" class="checkbox">
					<input name="shortn_it_use_numbers" type="checkbox" id="shortn_it_use_numbers" value='yes' <?php if( get_option( 'shortn_it_use_numbers' ) == 'yes' ) { echo 'checked="checked"'; } ?>>
					Use numbers (<code>0-9</code>)</label>
					
					<div class="shortn-look-like">
						<h3>Your short URLs will look like:</h3>
						<p><span class="domain"><?php echo str_replace( 'http://', '', $shortn_it_domain ); ?></span><span class="prefix"><?php echo ( $shortn_it_permalink_prefix != 'default' ) ? $shortn_it_permalink_custom : '/'; ?></span><span class="url"><?php echo $shortn_it_example_url; ?></span></p>
					</div>
				</div>
					
				<div class="shortn-wrapper panel" id="auto">
					<div class="shortn-section-header">
						<h3>Auto-Detection</h3>
						<div class="upgrade-now">
							<p><?php if( get_option( 'shortn_it_hide_nag' ) == 'no' ) { ?><a href="//docof.me/buy-shortn-it/">Support Shortn.It</a> | <?php } ?>
								<a href="//docof.me/shortn-it">Help</a></p>
						</div>
					</div>

					<label for="shortn_use_short_url" class="checkbox">
					<input name="shortn_use_short_url" type="checkbox" id="shortn_use_short_url" value='yes' <?php if( get_option( 'shortn_use_short_url' ) == 'yes' ) { echo 'checked="checked"'; } ?>>
					
					Use <a href="//sites.google.com/a/snaplog.com/wiki/short_url" title="Learn about short_url: the short_url wiki">short_url</a></label>
					
					<label for="shortn_use_shortlink" class="checkbox">
					<input name="shortn_use_shortlink" type="checkbox" id="shortn_use_shortlink" value='yes' <?php if( get_option( 'shortn_use_shortlink' ) == 'yes' ) { echo 'checked="checked"'; } ?>>
					Use <a href="//microformats.org/wiki/rel-shortlink" title="Learn about rel=shortlink">shortlink</a></label>
			<?php /*		
					<label for="shortn_it_track_hits" class="checkbox">
					<input name="shortn_it_track_hits" type="checkbox" id="shortn_it_track_hits" value='yes' <?php if( get_option( 'shortn_it_track_hits' ) == 'yes' ) { echo 'checked="checked"'; } ?>>
					
					Use <a href="//docof.me">docof.me</a> hit tracking</label>
					<button type="submit" name="submit" class="shortn-save-button cssbutton blue">Save Changes</button>
			*/ ?>
					<div class="clear"></div>	
				</div>
				
				<button type="submit" name="submit" class="shortn-save-button cssbutton blue">Save Changes</button>
	
				<input type="hidden" name="action" value="update">
				<input type="hidden" name="page_options" value="shortn_it_permalink_prefix,shortn_it_permalink_custom,shortn_it_use_lowercase,shortn_it_use_uppercase,shortn_it_use_numbers,shortn_it_length,shortn_it_permalink_domain,shortn_it_domain_custom,shortn_use_short_url,shortn_use_shortlink">
	</form>
</div>