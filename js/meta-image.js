/*jQuery(document).ready( function($){
	var formfield = null;
	$('#upload_image_button').click(function() {
		$('html').addClass('Image');
		formfield = $('#boj_mbe_image').attr('name');
		tb_show('', 'media-upload.php?type=image & TB_iframe=true');
		return false;
	});
	
	// user inserts file into post.
	//only run custom if user started process using the above process
	// window.send_to_editor(html) is how wp normally handle the received data
	window.original_send_to_editor = window.send_to_editor;
	console.log(original_send_to_editor);
	console.log(send_to_editor);
	window.send_to_editor = function(html){
		var fileurl;
		if (formfield != null) {
			fileurl = $('img',html).attr('src');
			$('#boj_mbe_image').val(fileurl);
			tb_remove();
			$('html').removeClass('Image');
			formfield = null;
		} else {
			window.original_send_to_editor(html);
		}
	};
});*/

jQuery(document).ready( function($){
	// Uploading files
	var file_frame;
	
	jQuery('.upload_image_button').live('click', function( event ){
		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
			text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			// Do something with attachment.id and/or attachment.url here
			console.log(attachment);
//			$('#boj_mbe_image').val(attachment.url);
			$('input[name="pch_bpt_wim_image_map"]').val(attachment.id);
			$('#pch_bpt_wim_image_map_src').attr({src: attachment.url});
		});

		// Finally, open the modal
		file_frame.open();
	});
})