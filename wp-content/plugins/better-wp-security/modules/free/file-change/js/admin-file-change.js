jQuery( document ).ready( function () {

	jQuery( "#itsec_file_change_enabled" ).change(function () {

		if ( jQuery( "#itsec_file_change_enabled" ).is( ':checked' ) ) {

			jQuery( "#file_change-settings" ).show();

		} else {

			jQuery( "#file_change-settings" ).hide();

		}

	} ).change();

	if ( itsec_file_change.mem_limit <= 128 ) {

		jQuery( "#itsec_file_change_enabled" ).change( function () {

			if ( this.checked ) {
				alert( itsec_file_change.text );

			}

		} );

	}

	jQuery( '.jquery_file_tree' ).fileTree(
		{
			root: itsec_file_change.ABSPATH,
			script: ajaxurl,
			expandSpeed: - 1,
			collapseSpeed: - 1,
			multiFolder: false

		}, function ( file ) {

			jQuery( '#itsec_file_change_file_list' ).val( file.substring( itsec_file_change.ABSPATH.length ) + "\n" + jQuery( '#itsec_file_change_file_list' ).val() );

		}, function ( directory ) {

			jQuery( '#itsec_file_change_file_list' ).val( directory.substring( itsec_file_change.ABSPATH.length ) + "\n" + jQuery( '#itsec_file_change_file_list' ).val() );

		}
	);

	//process tooltip actions
	jQuery( '#itsec_one_time_file_check' ).submit( function ( event ) {

		event.preventDefault();

		var data = {
			action: 'itsec_file_change_ajax',
			nonce: itsec_file_change.nonce
		};

		//let user know we're working
		jQuery( "#itsec_one_time_file_check_submit" ).removeClass( 'button-primary' ).addClass( 'button-secondary' ).attr( 'value', itsec_file_change.scanning_button_text );

		//call the ajax
		jQuery.ajax(
			{
				url: ajaxurl,
				type: 'POST',
				data: data,
				complete: function ( response ) {

					if ( response.responseText == 1 || response.responseText == - 1 ) {
						window.location.replace( '?page=toplevel_page_itsec_logs' )
					}

					jQuery( "#itsec_one_time_file_check_submit" ).removeClass( 'button-secondary' ).addClass( 'button-primary' ).attr( 'value', itsec_file_change.button_text );

					if ( response.responseText == 0 ) {
						jQuery( "#itsec_file_change_status" ).text( itsec_file_change.no_changes );
					}

				}
			}
		);

	} );

} );

jQuery( window ).load( function () {

	jQuery( document ).on( 'mouseover mouseout', '.jqueryFileTree > li a', function ( event ) {
		if ( event.type == 'mouseover' ) {
			jQuery( this ).children( '.itsec_treeselect_control' ).css( 'visibility', 'visible' );
		} else {
			jQuery( this ).children( '.itsec_treeselect_control' ).css( 'visibility', 'hidden' );
		}
	} );

} );


