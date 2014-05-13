<?php
add_action('admin_menu', 'create_theme_options_page');
add_action('admin_init', 'register_and_build_fields');

function create_theme_options_page() {
	add_theme_page('Gridly Options', 'Gridly Options', 'administrator', 'gridly_admin', 'options_page_fn');
}

function register_and_build_fields() {
	register_setting('plugin_options', 'plugin_options', 'validate_setting');
	add_settings_section('main_section', 'Main Settings', 'section_cb', __FILE__);
	
	add_settings_field('gridly_logo', 'Logo:', 'logo_setting', __FILE__, 'main_section'); // LOGO
	add_settings_field('gridly_color_scheme', 'Color Scheme:', 'color_scheme_setting', __FILE__, 'main_section');
	add_settings_field('gridly_responsive', 'Responsive Layout:', 'responsive_setting', __FILE__, 'main_section');
}

function options_page_fn() {
	if($_REQUEST['settings-updated']){
		echo "<div class='updated'><p>Updated</p></div>";
	}
?>
   <div id="theme-options-wrap" class="widefat">
      <div class="icon32" id="icon-tools"></div>
      <h2>Gridly Options</h2>
      <p>Take control of your theme, by overriding the default settings with your own specific preferences.</p>

      <form method="post" action="options.php" enctype="multipart/form-data">
         <?php settings_fields('plugin_options'); ?>
         <?php do_settings_sections(__FILE__); ?>
         <p class="submit">
            <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
         </p>
   </form>
</div>

<?php
}


// Color Scheme
function color_scheme_setting() {
	$options = get_option('plugin_options');
	$items = array("light", "dark", "custom");

	echo "<select name='plugin_options[gridly_color_scheme]'>";
	foreach ($items as $item) {
		$selected = ( $options['gridly_color_scheme'] === $item ) ? 'selected = "selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

// Responsive  Setting
function responsive_setting() {
	$options = get_option('plugin_options');
	$items = array("yes", "no");

	echo "<select name='plugin_options[gridly_responsive]'>";
	foreach ($items as $item) {
		$selected = ( $options['gridly_responsive'] === $item ) ? 'selected = "selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

// Logo
function logo_setting() {
	echo '<input type="file" name="gridly_logo" />';
	$options = get_option('plugin_options');
	if($options['gridly_logo'] != ''){
		echo "<br/><img src='{$options['gridly_logo']}' />";
	}
}

function validate_setting($plugin_options) {
	$keys = array_keys($_FILES);
	$i = 0;

	foreach ($_FILES as $image) {
	// if a files was upload
		if ($image['size']) {
			// if it is an image
			if (preg_match('/(jpg|jpeg|png|gif)$/', $image['type'])) {
				$override = array('test_form' => false);
				$file = wp_handle_upload($image, $override);

				$plugin_options[$keys[$i]] = $file['url'];
			} else {
				$options = get_option('plugin_options');
				$plugin_options[$keys[$i]] = $options[$logo];
				wp_die('No image was uploaded.');
			}
		} else {
			// else, retain the image that's already on file.
			$options = get_option('plugin_options');
			$plugin_options[$keys[$i]] = $options[$keys[$i]];
		}
		$i++;
	}

	return $plugin_options;
}

function section_cb() {}

// Add stylesheet
add_action('admin_head', 'admin_register_head');

function admin_register_head() {    
	$url = get_template_directory_uri() . '/options/options_page.css';
	echo "<link rel='stylesheet' href='$url' />\n";
}