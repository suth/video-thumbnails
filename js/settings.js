// Custom field detection
jQuery(function ($) {

	$('#vt_detect_custom_field').on('click',function(e) {
		e.preventDefault();
		var data = {
			action: 'video_thumbnail_custom_field_detection'
		};
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				if (response) {
					$('#custom_field').val(response);
				} else {
					alert(video_thumbnails_settings_language.detection_failed);
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert(video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);
			}
		});
	});

	$('#test-video-thumbnail-saving-media').on('click',function(e) {
		$('#media-test-result').html( '<p>' + video_thumbnails_settings_language.working + '</p>' );
		e.preventDefault();
		var data = {
			action: 'video_thumbnail_image_download_test'
		};
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				$('#media-test-result').html(response);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('#media-test-result').html('<p>' + video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText + '</p>');
			}
		});
	});

	$('#delete-video-thumbnail-test-images').on('click',function(e) {
		$('#media-test-result').html( '<p>' + video_thumbnails_settings_language.working + '</p>' );
		e.preventDefault();
		var data = {
			action: 'video_thumbnail_delete_test_images'
		};
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				$('#media-test-result').html(response);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('#media-test-result').html('<p>' + video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText + '</p>');
			}
		});
	});

});

// Test actions
function test_video_thumbnail( test_type ) {
	var data = {
		action: 'video_thumbnail_' + test_type + '_test'
	};
	document.getElementById( test_type + '-test' ).innerHTML = video_thumbnails_settings_language.working;
	jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		data: data,
		success: function(response){
			document.getElementById( test_type + '-test' ).innerHTML = response;
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			document.getElementById( test_type + '-test' ).innerHTML = '<p>' + video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText + '</p>';
		}
	});
};

function test_video_thumbnail_markup_detection() {
	var data = {
		action: 'video_thumbnail_markup_detection_test',
		markup: jQuery('#markup-input').val()
	};
	document.getElementById( 'markup-test-result' ).innerHTML = '<p>' + video_thumbnails_settings_language.working + '</p>';
	jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		data: data,
		success: function(response){
			document.getElementById( 'markup-test-result' ).innerHTML = response;
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			document.getElementById( 'markup-test-result' ).innerHTML = '<p>' + video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText + '</p>';
		}
	});
}

// Clear all video thumbnails
function clear_all_video_thumbnails( nonce ) {
	var confimation_result = confirm(video_thumbnails_settings_language.clear_all_confirmation);
	if (confimation_result){
		var data = {
			action: 'clear_all_video_thumbnails',
			nonce: nonce
		};
		document.getElementById( 'clear-all-video-thumbnails-result' ).innerHTML = '<p>' + video_thumbnails_settings_language.working + '</p>';
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				document.getElementById( 'clear-all-video-thumbnails-result' ).innerHTML = response;
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				document.getElementById( 'clear-all-video-thumbnails-result' ).innerHTML = '<p>' + video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText + '</p>';
			}
		});
	}
	else{
		//
	}
};