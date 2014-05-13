<?php

/**

 * adamos  Theme Customizer

 *

 * @package adamos

 * @link http://ottopress.com/tag/customizer/

 */


/**

 * Add postMessage support for site title and description for the Theme Customizer.

 *

 * @param WP_Customize_Manager $wp_customize Theme Customizer object.

 *

 */

function adamos_customize_register( $wp_customize ) {

	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';

	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

}

add_action( 'customize_register', 'adamos_customize_register' );



/**

 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.

 *

 * @since adamos 1.0

 */

function adamos_customize_preview_js() {

	wp_enqueue_script( 'adamos_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20120827', true );

}

add_action( 'customize_preview_init', 'adamos_customize_preview_js' );



add_action ('admin_menu', 'adamos_admin');

function adamos_admin() {

}