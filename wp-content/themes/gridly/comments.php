<?php

	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if ( post_password_required() ) { ?>
		This post is password protected. Enter the password to view comments.
	<?php
		return;
	}
?>

<?php if ( have_comments() ) : ?>
	
  <div class="comments-area"> 
	<h2><?php comments_number('No Comments', 'One Comment', '% Comments' );?></h2>

	<div class="navigation">
		<div class="next-posts"><?php previous_comments_link() ?></div>
		<div class="prev-posts"><?php next_comments_link() ?></div>
	</div>
   
	<ol class="commentlist">
		<?php wp_list_comments(); ?>
	</ol>

	<div class="navigation">
		<div class="next-posts"><?php previous_comments_link() ?></div>
		<div class="prev-posts"><?php next_comments_link() ?></div>
	</div>

</div>
	
 <?php else : // this is displayed if there are no comments so far ?>

	<?php if ( comments_open() ) : ?>
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>
	<?php endif; ?>
	
<?php endif; ?>


<?php if ( comments_open() ) : ?>



<div id="comment-form">

	<h2><?php comment_form_title( 'Leave a Comment', 'Leave a Comment to %s' ); ?></h2>

	<div class="cancel-comment-reply">
		<?php cancel_comment_reply_link(); ?>
	</div>

	<?php if ( get_option('comment_registration') && !is_user_logged_in() ) : ?>
		<p>You must be <a href="<?php echo wp_login_url( get_permalink() ); ?>">logged in</a> to post a comment.</p>
	<?php else : ?>

	<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

		<?php if ( is_user_logged_in() ) : ?>

			<p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a></p>

		<?php else : ?>

				<input type="text" name="author" id="author" value="Name *" size="22" class="text-input" tabindex="1" <?php if ($req) echo "aria-required='true'"; ?> />

				<input type="text" name="email" id="email" value="Email *" size="22" class="text-input" tabindex="2" <?php if ($req) echo "aria-required='true'"; ?> />

			<input type="text" name="url" id="url" value="Website *" size="22" class="text-input" tabindex="3" />
		
  
		<?php endif; ?>

	
			<textarea name="comment" id="comment" cols="58" rows="10" tabindex="4" class="comment-input">Comment</textarea>
	
	   <div class="clear"></div>	
			<input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" class="comment-submit" />
			<?php comment_id_fields(); ?>
	
		
		<?php do_action('comment_form', $post->ID); ?>

	</form>
 </div>

	<?php endif; // If registration required and not logged in ?>
	

<?php endif; ?>
