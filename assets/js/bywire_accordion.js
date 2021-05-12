jQuery.noConflict();
jQuery(document).ready(function(){
	jQuery('.accordion-list > .accordion-item > .accordion-card-body').hide();
	jQuery('.accordion-list > .accordion-item.active > .accordion-card-body').show();
	jQuery('.accordion-list > .accordion-item').click(function() {
	  if (jQuery(this).hasClass("active")) {
		jQuery(this).removeClass("active").find(".accordion-card-body").slideUp();
	  } else {
		jQuery(".accordion-list > li.active .accordion-card-body").slideUp();
		jQuery(".accordion-list > li.active").removeClass("active"); 
		jQuery(this).addClass("active").find(".accordion-card-body").slideDown();
	  }
	  return false;
	});
	
});
