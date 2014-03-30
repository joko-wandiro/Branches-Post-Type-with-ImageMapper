( function($){
$(document).ready( function(){
    Feedback= {
		selector: {},
        'default': function(data){
			console.log(data);
        },
        'alert': function(data){
			alert(data);
        },
        'get_province': function(data){
			$(this.selector).html(data.html);
        }		
    }
	
    Ajax= {		
		'form': '',
        'type': "default",
		'extra': "default",
		container: '',
        send: function(url, data){
            AjaxObj= this;
            $.ajax({
				async: false,
                dataType: 'json',
                type: 'POST',
                url: url,
                data: data,
                beforeSend: function(){
					AjaxObj.blockUI();
                },
                complete: function(){
					AjaxObj.unBlock();
                },
                success: function(data){
                    type= AjaxObj.type;
                    Feedback[type](data);
                }
            });
        },
		loading: function(url, data){
			container= this.container;
			$(container).load(url + ' ' + container, data);
		},
		blockUI: function(){
			$.blockUI({ 
//				message: '<h1>Loading...</h1>'
				message: phc_branches_post_type_params.loading_text,
			});
		},
		unBlock: function(){
			$.unblockUI(); 
		},
		get_data_based_province: function(key){
			data= {
			action: 'phc_branches_post_type_ajax',
			province: key
			}
			// Feedback.selector= $('#feedback_selector');
			Feedback.selector= phc_branches_post_type_params.feedback_selector;		
			Ajax.type= "get_province";
			Ajax.send(phc_branches_post_type_params.ajaxurl, data);
		}
	}
			
	function get_data_based_province($key){
		data= {
		action: 'phc_branches_post_type_ajax',
		province: key
		}
		Feedback.selector= phc_branches_post_type_params.feedback_selector;
		Ajax.type= "get_province";
		Ajax.send(phc_branches_post_type_params.ajaxurl, data);
	}
})
})(jQuery);