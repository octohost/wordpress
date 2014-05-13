// js to active the link of option pannel
 jQuery(document).ready(function() {
	jQuery('ul li.active ul').slideDown();
	// menu click	
	jQuery('#nav > li > a').click(function(){		
		if (jQuery(this).attr('class') != 'active')
		{ 		
			jQuery('#nav li ul').slideUp(350);
			jQuery(this).next().slideToggle(350);
			jQuery('#nav li a').removeClass('active');
			jQuery(this).addClass('active');
		  
			jQuery('ul.options_tabs li').removeClass('active');
			jQuery(this).parent().addClass('active');		
			var divid =  jQuery(this).attr("id");	
			var add="div#option-"+divid;
			var strlenght = add.length;
			
			if(strlenght<17)
			{	
				var add="div#option-ui-id-"+divid;
				var ulid ="#ui-id-"+divid;
				jQuery('ul.options_tabs li li ').removeClass('currunt');
				jQuery(ulid).parent().addClass('currunt');	
			}			
			jQuery('div.ui-tabs-panel').addClass('deactive');
			jQuery('div.ui-tabs-panel').removeClass('active');
			jQuery(add).removeClass('deactive');		
			jQuery(add).addClass('active');		
		}
	});
	
	// child submenu click
	jQuery('ul.options_tabs li li ').click(function() {			
		jQuery('ul.options_tabs li li ').removeClass('currunt');
		jQuery(this).addClass('currunt');
		var sub_a_id =  jQuery(this).children("a").attr("id");
		var option_add="div#option-"+sub_a_id;
		jQuery('div.ui-tabs-panel').addClass('deactive');
		jQuery('div.ui-tabs-panel').removeClass('active');
		jQuery(option_add).removeClass('deactive');		
		jQuery(option_add).addClass('active');		
	});	
	
});