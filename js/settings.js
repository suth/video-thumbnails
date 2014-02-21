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
				alert('We were unable to find a video in the custom fields of your most recently updated post.');
			}
		});
	});

});

// Test actions
function test_video_thumbnail( test_type ) {
	var data = {
		action: 'video_thumbnail_' + test_type + '_test'
	};
	document.getElementById( test_type + '-test' ).innerHTML = 'Working...';
	jQuery.post(ajaxurl, data, function(response){
		document.getElementById( test_type + '-test' ).innerHTML = response;
	});
};

function test_video_thumbnail_markup_detection() {
	var data = {
		action: 'video_thumbnail_markup_detection_test',
		markup: jQuery('#markup-input').val()
	};
	document.getElementById( 'markup-test-result' ).innerHTML = '<p>Working...</p>';
	jQuery.post(ajaxurl, data, function(response){
		document.getElementById( 'markup-test-result' ).innerHTML = response;
	});
}

// Clear all video thumbnails
function clear_all_video_thumbnails( nonce ) {
	var confimation_result = confirm("Are you sure you want to clear all video thumbnails? This cannot be undone.");
	if (confimation_result){
		var data = {
			action: 'clear_all_video_thumbnails',
			nonce: nonce
		};
		document.getElementById( 'clear-all-video-thumbnails-result' ).innerHTML = '<p>Working...</p>';
		jQuery.post(ajaxurl, data, function(response){
			document.getElementById( 'clear-all-video-thumbnails-result' ).innerHTML = response;
		});
	}
	else{
		//
	}
};