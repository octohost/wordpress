jQuery( document ).ready( function () {

	jQuery( "#itsec_strong_passwords_enabled" ).change(function () {

		if ( jQuery( "#itsec_strong_passwords_enabled" ).is( ':checked' ) ) {

			jQuery( "#strong_passwords-settings" ).show();

		} else {

			jQuery( "#strong_passwords-settings" ).hide();

		}

	} ).change();

} );