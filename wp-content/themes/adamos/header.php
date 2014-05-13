<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package adamos
 * @since adamos 2.0
 */
?><!DOCTYPE html>
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />

<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="wrap">
<div id="page" class="hfeed site">
	<?php do_action( 'before' ); ?>
    <div id="masthead-wrap">
<header id="masthead" class="site-header header_container" role="banner">
    <?php if ( get_theme_mod( 'adamos_logo' ) ) : ?>
    <div class="site-logo">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo get_theme_mod( 'adamos_logo' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"></a>
    </div>
<?php else : ?>

		<div class="site-introduction">
			<h1 class="site-title"><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<p class="site-description"><?php bloginfo( 'description' ); ?></p> 
		</div>
<?php endif; ?>

<nav role="navigation" class="site-navigation main-navigation">
			<h1 class="assistive-text"><?php _e( 'Menu', 'adamos' ); ?></h1>
			<div class="assistive-text skip-link"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'adamos' ); ?>"><?php _e( 'Skip to content', 'adamos' ); ?></a></div>

			<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
		</nav><!-- .site-navigation .main-navigation -->

	</header><!-- #masthead .site-header -->
	</div><!-- #masthead-wrap -->
    <div class="header-image">
	<?php $header_image = get_header_image();
		if ( ! empty( $header_image ) ) { ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
				<img src="<?php header_image(); ?>" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" />
			</a>
		<?php } // if ( ! empty( $header_image ) ) ?>
	</div>
    <?php if(is_front_page()):?>
    <div class="flex-container">
              <div class="flexslider">
                <ul class="slides">
                <?php
                query_posts(array('category_name' => 'featured', 'posts_per_page' => 3));
                if(have_posts()) :
                    while(have_posts()) : the_post();
                ?>
                  <li>
                    <?php the_post_thumbnail(); ?>
                    <div class="caption_wrap">
                    <div class="flex-caption">
                    <div class="flex-caption-title">
                    <a href="<?php the_permalink() ?>"><?php the_title(); ?></a>
                    </div>
                    <p><?php echo get_slider_excerpt(); ?>
                    <a href="<?php the_permalink() ?>">...</a></p>
                    </div>
                    </div>
                  </li>
                <?php
                    endwhile;
                endif;
                wp_reset_query();
                ?>
                </ul>
              </div>
	</div>
     <div class="featuretext_top">
			 <h3><?php echo get_theme_mod( 'featured_textbox' ); ?></h3>
             <p><a href="<?php echo esc_url( get_theme_mod( 'header_one_url' ) ); ?>"><?php echo esc_html(get_theme_mod( 'featured_textbox_text' ) ); ?></a></p>
	</div>
    <?php endif; ?>
	<div id="main" class="site-main">