jQuery( document ).ready( function () {

	jQuery( "#itsec_brute_force_enabled" ).change(function () {

		if ( jQuery( "#itsec_brute_force_enabled" ).is( ':checked' ) ) {

			jQuery( "#brute_force-settings" ).show();

		} else {

			jQuery( "#brute_force-settings" ).hide();

		}

	} ).change();

} );