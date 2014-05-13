jQuery(document).ready( function($) {
	
	$('#shortn_it_domain_custom').keyup(function(e) {
		$('.shortn-look-like .domain').text($(this).val());
	});
	
	$('#shortn_it_permalink_custom').keyup(function(e) {
		$('.shortn-look-like .prefix').text($(this).val());
	});
	
	$('input[name=shortn_it_permalink_domain]').change(function(e) {
		if($('input[name=shortn_it_permalink_domain]:checked').val() == 'custom') {
			$('#custom-domain-input').slideDown();
			$('.shortn-look-like .domain').text($('#shortn_it_domain_custom').val());
		}
		
		if($('input[name=shortn_it_permalink_domain]:checked').val() == 'default') {
			$('#custom-domain-input').slideUp();
			$('.shortn-look-like .domain').text(document.domain);
		}
		
		$(this).next('button').html('hello');
		
	});
	
	$('input[name=shortn_it_permalink_prefix]').change(function(e) {
		if($('input[name=shortn_it_permalink_prefix]:checked').val() == 'custom') {
			$('#custom-prefix-input').slideDown();
			$('.shortn-look-like .prefix').text($('#shortn_it_permalink_custom').val());
		}
		
		if($('input[name=shortn_it_permalink_prefix]:checked').val() == 'default') {
			$('#custom-prefix-input').slideUp();
			$('.shortn-look-like .prefix').text('/');
		}
	});
	
	$('#shortn_it_length').bind('keyup change', change_url_ops);
	$('#shortn_it_use_lowercase, #shortn_it_use_uppercase, #shortn_it_use_numbers').change(change_url_ops);
	
	function change_url_ops(e) {
		if(e.type == 'change' || (e.which >= 48 && e.which >= 57) || e.which == 8 || e.which == 46) {
			$('.shortn-look-like .url').text(generate_rand_string());
		
			if(parseInt($(this).val()) <= 8)
				$('#warning-msg').remove();
			else if($('#warning-msg').length == 0)
				$('<span id="warning-msg"></span>').insertAfter(this);

			if(parseInt($(this).val()) > 50) $('#warning-msg').text('Why bother?');
			else if(parseInt($(this).val()) > 20) $('#warning-msg').text('This is just getting ridiculous.');
			else if(parseInt($(this).val()) > 16) $('#warning-msg').text('Oh boy, you might as well call it a long URL.');
			else if(parseInt($(this).val()) > 12) $('#warning-msg').text('Wow, you\'re really pushing the definition of "short".');
			else if(parseInt($(this).val()) > 8) $('#warning-msg').text('Easy there tiger, these URLs are supposed to be short.');
		}
	}
	
	function generate_rand_string() {
		valid_chars = '';
		if($('#shortn_it_use_lowercase').is(':checked'))
			valid_chars += 'abcdefghijklmnopqrstuvwxyz';
		if($('#shortn_it_use_uppercase').is(':checked'))
			valid_chars += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if($('#shortn_it_use_numbers').is(':checked'))
			valid_chars += '0123456789';
		
		// start with an empty random string
		random_string = '';
	
		// repeat the steps until we've created a string of the right length
		for (i = 0; i < $('#shortn_it_length').val(); i++) {
			// pick a random number from 1 up to the number of valid chars
			random_pick = Math.floor((Math.random()*valid_chars.length)+1);
	
			// take the random character out of the string of valid chars
			// subtract 1 from random_pick because strings are indexed starting at 0, and we started picking at 1
			random_char = valid_chars.charAt(random_pick-1);
	
			// add the randomly-chosen char onto the end of our string so far
			random_string += random_char;
		}
		
		return random_string;
	}
});