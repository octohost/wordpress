<?php

/*

 * Template Name: Full Width

 * Description: A Page Template with no sidebar.

 *

 * @package adamos

 * @since adamos 1.0

 */



get_header(); ?>



		<div id="primary_home" class="content-area">

			<div id="content" class="fullwidth" role="main">



				<?php while ( have_posts() ) : the_post(); ?>



					<?php get_template_part( 'content', 'page' ); ?>



					<?php comments_template( '', true ); ?>



				<?php endwhile; // end of the loop. ?>



			</div><!-- #content .site-content -->

		</div><!-- #primary .content-area -->



<?php get_footer(); ?>