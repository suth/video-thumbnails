// Custom field detection
jQuery(function ($) {

	$('#vt_detect_custom_field').on('click',function(e) {
		e.preventDefault();
		var data = {
			action: 'video_thumbnail_custom_field_detection'
		};
		$.post(ajaxurl, data, function(response){
			if (response) {
				$('#custom_field').val(response);
			} else {
				alert(video_thumbnails_settings_language.detection_failed);
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
	jQuery.post(ajaxurl, data, function(response){
		document.getElementById( test_type + '-test' ).innerHTML = response;
	});
};

function test_video_thumbnail_markup_detection() {
	var data = {
		action: 'video_thumbnail_markup_detection_test',
		markup: jQuery('#markup-input').val()
	};
	document.getElementById( 'markup-test-result' ).innerHTML = '<p>' + video_thumbnails_settings_language.working + '</p>';
	jQuery.post(ajaxurl, data, function(response){
		document.getElementById( 'markup-test-result' ).innerHTML = response;
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
		jQuery.post(ajaxurl, data, function(response){
			document.getElementById( 'clear-all-video-thumbnails-result' ).innerHTML = response;
		});
	}
	else{
		//
	}
};