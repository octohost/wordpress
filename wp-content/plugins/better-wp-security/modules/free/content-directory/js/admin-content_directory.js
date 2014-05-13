jQuery( document ).ready( function () {

	jQuery( "#itsec_enable_content_dir" ).change(function () {

		if ( jQuery( "#itsec_enable_content_dir" ).is( ':checked' ) ) {
			jQuery( "#content_directory_name_field" ).show();

		} else {
			jQuery( "#content_directory_name_field" ).hide();

		}

	} ).change();

} );