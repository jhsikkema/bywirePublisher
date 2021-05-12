jQuery.noConflict();
jQuery(document).ready(function(){
	jQuery('.accordion-list > .accordion-item > .accordion-card-body').hide();
	jQuery('.accordion-list > .accordion-item.active > .accordion-card-body').show();
    jQuery('.accordion-list > .accordion-item > .accordion-card-header').click(function() {
	  if (jQuery(parent).hasClass("active")) {
	      jQuery(this).parent().removeClass("active").find(".accordion-card-body").slideUp();
	  } else {
	      jQuery(".accordion-list > .accordion-item.active > .accordion-card-body").slideUp();
	      jQuery(".accordion-list > .accoridon-item.active").removeClass("active"); 
	      jQuery(this).parent().addClass("active").find(".accordion-card-body").slideDown();
	  }
	  return false;
	});

	jQuery('ul[role="tabs"] li a').click(function(event){
		event.preventDefault();

		jQuery('div[role="tabs"]').removeClass('show');
		jQuery('div[role="tabs"]' + jQuery(this).attr('href')).addClass('show');
	});

	jQuery('[role="toggle-password-visibility"]').click(function(event){
		event.preventDefault();

		let siblingInput = jQuery(this).parent().siblings('input');
		jQuery(siblingInput).attr(
			'type',
			jQuery(siblingInput).attr('type') == 'password' ? 'text' : 'password'
		);

		jQuery(this).text(
			jQuery(siblingInput).attr('type') == 'password' ? 'Show password' : 'Hide Password'
		);
	});


});
