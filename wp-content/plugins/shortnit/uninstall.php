<?php

//	Delete all Shortn.It options from DB
delete_option( 'shortn_it_version' );
delete_option( 'shortn_it_use_mobile_style' );
delete_option( 'shortn_it_link_text' );
delete_option( 'shortn_it_permalink_prefix' );
delete_option( 'shortn_it_permalink_custom' );
delete_option( 'shortn_it_use_lowercase' );
delete_option( 'shortn_it_use_uppercase' );
delete_option( 'shortn_it_use_numbers' );
delete_option( 'shortn_it_length' );
delete_option( 'shortn_use_short_url' );
delete_option( 'shortn_use_shortlink' );
delete_option( 'shortn_it_registered' );
delete_option( 'shortn_it_registered_on' );
delete_option( 'shortn_it_permalink_domain' );
delete_option( 'shortn_it_domain_custom' );
delete_option( 'shortn_it_hide_godaddy' );
delete_option( 'shortn_it_use_url_as_link_text' );
delete_option( 'shortn_it_add_to_rss' );
delete_option( 'shortn_it_add_to_rss_text' );
delete_option( 'shortn_it_hide_nag' );

//	Delete all Shortn.It URL metadata for any post type
foreach ( get_post_types() as $post_type )
	delete_metadata( $post_type, null, '_shortn_it_url', '', true );