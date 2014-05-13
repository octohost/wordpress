jQuery(document).ready( function($) {
	$( '<span id="edit_shortn_it_url_buttons">\
		<a class="edit button button-small hide-if-no-js" href="#shortn_it">Edit</a>\
		<a class="save button button-small hide" href="#">OK</a>\
		<a class="cancel hide" href="#">Cancel</a></span>' )
		.insertAfter( '#shortn_it_url' );
	
	$( '#edit_shortn_it_url_buttons .edit' ).click( function( e ) {
		e.preventDefault();
		$( '.shortn_it_url_wrap, #edit_shortn_it_url_buttons .edit' ).addClass( 'hide' );
		$( '#shortn_it_url, #edit_shortn_it_url_buttons .save, #edit_shortn_it_url_buttons .cancel' ).removeClass( 'hide' );
	});
	
	$( '#edit_shortn_it_url_buttons .save' ).click( function( e ) {
		e.preventDefault();
		$( '#shortn_it_url, #edit_shortn_it_url_buttons .save, #edit_shortn_it_url_buttons .cancel' ).addClass( 'hide' );
		$( '.shortn_it_url_wrap, #edit_shortn_it_url_buttons .edit' ).removeClass( 'hide' );
		if( $( '#shortn_it_url' ).val() != '' )
			$( '.shortn_it_url' ).text( $( '#shortn_it_url' ).val());
		else
			$( '#shortn_it_url' ).val( $( '.shortn_it_url' ).text());
		$( '#shortn_it_url_match' ).remove();
	});
	
	$( '#edit_shortn_it_url_buttons .cancel' ).click( function( e ) {
		e.preventDefault();
		$( '#shortn_it_url, #edit_shortn_it_url_buttons .save, #edit_shortn_it_url_buttons .cancel' ).addClass( 'hide' );
		$( '.shortn_it_url_wrap, #edit_shortn_it_url_buttons .edit' ).removeClass( 'hide' );
		$( '#shortn_it_url' ).val( $( '.shortn_it_url' ).text());
		$( '#shortn_it_url_match' ).remove();
	});
	
	$( '#shortn_it_url' ).keyup(function( e ) {
		$( this ).val( $( this ).val().replace(/[^0-9a-zA-Z\-\.\_]/, '' ));
		if( $( this ).val() != '' ) {
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: vars.ajax,
				data: { action: 'shortn_it_json_check_url', id: $( '#post_ID' ).val(), nonce: $( '#shortn_it_nonce' ).val(), string: $( this ).val() },
				success: function( response ) {
					if( $( '#shortn_it_url_match' ).length == 0)
						$( '<p id="shortn_it_url_match"></p>' ).insertAfter( '#edit_shortn_it_url_buttons' );
					$( '#shortn_it_url_match' ).css( { color: (( response.exists ) ? '#B00' : '#0B0' ) } )
						.html( ( ( response.exists ) ? 'This short URL is already in use by:<br><a href="' + response.edit_url + '">' + response.match_title + '</a>' : 'This short URL is available!' ) );
				}
			});
		}
	});
});