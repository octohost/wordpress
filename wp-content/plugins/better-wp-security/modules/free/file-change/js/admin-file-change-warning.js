jQuery( document ).ready( function () {

	//process tooltip actions
	jQuery( '#itsec_go_to_logs, #itsec_dismiss_file_change_warning' ).click( function ( event ) {

		event.preventDefault();

		var button = this.value;

		var data = {
			action: 'itsec_file_change_warning_ajax',
			nonce: itsec_file_change_warning.nonce
		};

		//call the ajax
		jQuery.post( ajaxurl, data, function () {

			jQuery( '#itsec_file_change_warning_dialog' ).remove();

			if ( button == 'View Logs' ) {
				window.location.replace( itsec_file_change_warning.url )
			}

		} );

	} );

} );



