var $ = jQuery;

function set_classic_publish(content, action) {
    var url = bywire_admin_custom_js_var.plugin_dir+"class.bywire.php"

    var self = this
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                var response = JSON.parse(this.responseText);
            } catch (err) {
                alert(this.responseText)
                return;
	    }
	}
    }
    var params = "action="+action+"&value="+content;
    xmlhttp.open("POST",  url, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(params);
}

jQuery(function ($) { 
    $(document).ajaxComplete(function (event, xhr, settings)  {
        if (typeof settings.data==='string' && /action=get-post-thumbnail-html/.test(settings.data) && xhr.responseJSON && typeof xhr.responseJSON.data==='string') {
	    const match = settings.data.match(/thumbnail_id=[0-9]*/)
	    const id    = match.replace("thumbnail_id", "")
	    
	    set_classic_publish(id, "set-featured-image")
	    
            window.autosave()
           }
    });
});












    
jQuery(document).ready(function($){
	if(typeof bywire_admin_custom_js_var !== "undefined" ){
		if(bywire_admin_custom_js_var.post_publish_error != ""){
		    console.log(bywire_admin_custom_js_var.post_publish_error);
		    var msg        = bywire_admin_custom_js_var.post_publish_error.message
		    var success    = bywire_admin_custom_js_var.post_publish_error.success
		    var error_code = bywire_admin_custom_js_var.post_publish_error.code
		    if(success) {
			    swal("Post Published", msg, "success");
		    } else {
			swal(error_code, msg, "error");
		    }
		}
	}

	window.bw_charts = {};
	$('.chartjs-chart').each(function(){

		var barChartData = {
			labels: JSON.parse($(this).attr('data-labels')),
			datasets: [{
				backgroundColor: '#FF4B4B',
				borderColor: '#FF4B4B',
				data: JSON.parse($(this).attr('data-values'))
			}]
		};

		var ctx = document.getElementById($(this).attr('id')).getContext('2d');
		ctx.height = 300;
		window.bw_charts[$(this).attr('id')] = new Chart(ctx, {
			type: 'bar',
			data: barChartData,
			options: {
				responsive: true,
				maintainAspectRatio: false,
				legend: {
					display: false,
				},
				title: {
					display: false,
				},
				scales: {
					xAxes: [{
						gridLines: {
							display:false,
							drawBorder: false,
						},
						ticks: {
							fontSize: 15
						}
					}],
					yAxes: [{
						gridLines: {
							display:false,
							drawBorder: false,
						},
						ticks: {
							fontSize: 12
						}
					}]
				}
			}
		});
	});

	$(".bw-news-loadmore").on("click", function(event){
		event.preventDefault();

		var total_news = $("#total_news").val();
		var total_no_of_page = Math.ceil(parseInt(total_news) / 9);
		var paged = $("#paged").val();
		var data = {
			action: "bywire_load_more_news",
			paged: paged,
		};
		$.ajax({
			type: "post",
			url: bywire_admin_custom_js_var.ajax_url,
			data: data,
			success: function(res){
				console.log(res);
				var html = "";
				if(res.success){
					if(typeof res.html !== "undefined"){

						$('.news-items > .row').html(
							$('.news-items > .row').html() + res.html
						);

						if(total_no_of_page == paged){
							$(".news_load_more_btn").hide();
						}
						$("#paged").val((parseInt(paged) + 1));
					}
				}else{
					$(".news_load_more_btn").hide();
				}
			}
		});
	});
	jQuery('.accordion-list > li > .acc-card-body').hide();
	jQuery('.accordion-list > li:first-child > .acc-card-body').show();
	jQuery('.accordion-list > li').click(function() {
	  if (jQuery(this).hasClass("active")) {
		jQuery(this).removeClass("active").find(".acc-card-body").slideUp();
	  } else {
		jQuery(".accordion-list > li.active .acc-card-body").slideUp();
		jQuery(".accordion-list > li.active").removeClass("active"); 
		jQuery(this).addClass("active").find(".acc-card-body").slideDown();
	  }
	  return false;
	});
	
});



