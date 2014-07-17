jQuery(function ($) {

	/**
	 * Custom field detection
	 */
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

	/**
	 * Debugging tests
	 */
	$('a.toggle-video-thumbnails-test-content').on('click',function(e) {
		e.preventDefault();
		$(this).closest('.video-thumbnails-test').toggleClass('closed');
	});

	function enable_video_thumbnails_tests() {
		$('.video-thumbnails-test-button').attr('disabled',false);
	}

	function disable_video_thumbnails_tests() {
		$('.video-thumbnails-test-button').attr('disabled',true);
	}

	/* Provider testing */

	function test_single_provider(provider_slug) {
		disable_video_thumbnails_tests();
		$('#'+provider_slug+'-provider-test').addClass('test-working');
		$('#'+provider_slug+'-provider-test .retest-video-provider').val(video_thumbnails_settings_language.working);
		var data = {
			action: 'video_thumbnail_provider_test',
			provider_slug: provider_slug
		};
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				$('#'+provider_slug+'-provider-test .test-results').html(response);
				$('#'+provider_slug+'-provider-test .retest-video-provider').val(video_thumbnails_settings_language.retest);
				$('#'+provider_slug+'-provider-test').removeClass('test-working');
				done_testing_single_provider();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('#'+provider_slug+'-provider-test .test-results').html('<p>' + video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText + '</p>');
				$('#'+provider_slug+'-provider-test .retest-video-provider').val(video_thumbnails_settings_language.retest);
				$('#'+provider_slug+'-provider-test').removeClass('test-working');
				done_testing_single_provider();
			}
		});
	}

	var provider_index = 0;
	var testing_all_providers = false;

	function done_testing_single_provider() {
		// If we aren't testing providers, don't do anything
		if (testing_all_providers==false) {
			enable_video_thumbnails_tests();
		}
		provider_index = provider_index + 1;
		if (provider_index>=video_thumbnails_provider_slugs.provider_slugs.length) {
			testing_all_providers = false;
			enable_video_thumbnails_tests();
			return;
		} else {
			test_single_provider(video_thumbnails_provider_slugs.provider_slugs[provider_index]);
		}
	}

	$('#test-all-video-thumbnail-providers').on('click',function(e) {
		e.preventDefault();
		$('#provider-test-results').removeClass('hidden');
		$(this).parent().remove();
		testing_all_providers = true;
		test_single_provider(video_thumbnails_provider_slugs.provider_slugs[provider_index]);
	});

	$('.retest-video-provider').on('click',function(e) {
		e.preventDefault();
		test_single_provider( $(this).data('provider-slug') );
	});

	/* Markup detection testing */
	$('#test-markup-detection').on('click',function(e) {
		disable_video_thumbnails_tests();
		e.preventDefault();
		var data = {
			action: 'video_thumbnail_markup_detection_test',
			markup: $('#markup-input').val()
		};
		document.getElementById( 'markup-test-result' ).innerHTML = '<p>' + video_thumbnails_settings_language.working + '</p>';
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				document.getElementById( 'markup-test-result' ).innerHTML = response;
				enable_video_thumbnails_tests();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				document.getElementById( 'markup-test-result' ).innerHTML = '<p>' + video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText + '</p>';
				enable_video_thumbnails_tests();
			}
		});
	});

	/* Media download testing */
	$('#test-video-thumbnail-saving-media').on('click',function(e) {
		disable_video_thumbnails_tests();
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
				enable_video_thumbnails_tests();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('#media-test-result').html('<p>' + video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText + '</p>');
				enable_video_thumbnails_tests();
			}
		});
	});

	$('#delete-video-thumbnail-test-images').on('click',function(e) {
		disable_video_thumbnails_tests();
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
				enable_video_thumbnails_tests();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('#media-test-result').html('<p>' + video_thumbnails_settings_language.ajax_error + ' ' + XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText + '</p>');
				enable_video_thumbnails_tests();
			}
		});
	});

});

/**
 * Clear all video thumbnails
 */
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