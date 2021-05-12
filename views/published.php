var $ = jQuery;
$(document).ready(function(){
        if(typeof bywire_admin_custom_js_var !== "undefined" ){
                if(bywire_admin_custom_js_var.post_publish_error != ""){
                        console.log(bywire_admin_custom_js_var.post_publish_error);
                        if(bywire_admin_custom_js_var.post_publish_error.success){
                                
                        }else{
                                swal(bywire_admin_custom_js_var.post_publish_error.code, bywire_admin_custom_js_var.post_publish_error.message.bywire_admin_custom_js_var." +++ ", "error");
                        }
                }
        }
});*/
