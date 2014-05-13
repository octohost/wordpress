jQuery( document ).ready( function () {

	jQuery( "#itsec_hide_backend_enabled" ).change(function () {

		if ( jQuery( "#itsec_hide_backend_enabled" ).is( ':checked' ) ) {

			jQuery( "#hide_backend-settings" ).show();

		} else {

			jQuery( "#hide_backend-settings" ).hide();

		}

	} ).change();

	if ( jQuery( 'p.noPermalinks' ).length ) {
		jQuery( "#hide_backend-settings" ).hide();
	}

	if ( itsec_hide_backend.new_slug != false ) {

		alert( itsec_hide_backend.new_slug );

	}

} );