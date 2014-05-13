
// masonry code 
$(document).ready(function() {
  $('#post-area').masonry({
    // options
    itemSelector : '.post',
    // options...
  isAnimated: true,
  animationOptions: {
    duration: 400,
    easing: 'linear',
    queue: false
  }
	
  });
});


// hover code for index  templates
$(document).ready(function() {
	
		$('#post-area .image').hover(
			function() {
				$(this).stop().fadeTo(300, 0.8);
			},
			function() {
				$(this).fadeTo(300, 1.0);
			}
		);	
		
	
});


// comment form values
$(document).ready(function(){
	$("#comment-form input").focus(function () {
		var origval = $(this).val();	
		$(this).val("");	
		//console.log(origval);
		$("#comment-form input").blur(function () {
			if($(this).val().length === 0 ) {
				$(this).val(origval);	
				origval = null;
			}else{
				origval = null;
			};	
		});
	});
});


// clear text area
$('textarea.comment-input').focus(function() {
   $(this).val('');
});