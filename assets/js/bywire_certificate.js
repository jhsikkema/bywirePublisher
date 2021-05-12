var $ = jQuery;
$(document).ready(function(){
	if(typeof bywire_admin_custom_js_var !== "undefined" ){
		if(bywire_admin_custom_js_var.post_publish_error != ""){
			console.log(bywire_admin_custom_js_var.post_publish_error);
			if(bywire_admin_custom_js_var.post_publish_error.success){
				swal("Post Published", "Thank you for submitting post to Bywire."+"aaaaa", "success");
			}else{
				swal(bywire_admin_custom_js_var.post_publish_error.code, bywire_admin_custom_js_var.post_publish_error.message+"aaaaa", "error");
			}
		}
	}
});
