<?php
/*
Plugin Name: Video Thumbnails
Plugin URI: http://sutherlandboswell.com/projects/wordpress-video-thumbnails/
Description: Automatically retrieve video thumbnails for your posts and display them in your theme. Currently supports YouTube, Vimeo, Blip.tv, Justin.tv, Dailymotion and Metacafe.
Author: Sutherland Boswell
Author URI: http://sutherlandboswell.com
Version: 1.8.2
License: GPL2
*/
/*  Copyright 2010 Sutherland Boswell  (email : sutherland.boswell@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Get Vimeo Thumbnail
function getVimeoInfo( $id, $info = 'thumbnail_large' ) {
	if ( ! function_exists( 'curl_init' ) ) {
		return null;
	} else {
		$ch = curl_init();
		$videoinfo_url = "http://vimeo.com/api/v2/video/$id.php";
		curl_setopt( $ch, CURLOPT_URL, $videoinfo_url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_FAILONERROR, true ); // Return an error for curl_error() processing if HTTP response code >= 400
		$output = unserialize( curl_exec( $ch ) );
		$output = $output[0][$info];
		if ( curl_error( $ch ) != null ) {
			$output = new WP_Error( 'vimeo_info_retrieval', __( 'Error retrieving video information from the URL <a href="' . $videoinfo_url . '">' . $videoinfo_url . '</a>.<br /><a href="http://curl.haxx.se/libcurl/c/libcurl-errors.html">Libcurl error</a> ' . curl_errno( $ch ) . ': <code>' . curl_error( $ch ) . '</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.' ) );
		}
		curl_close( $ch );
		return $output;
	}
};

// Blip.tv Functions
function getBliptvInfo( $id ) {
	$videoinfo_url = "http://blip.tv/players/episode/$id?skin=rss";
	$xml = simplexml_load_file( $videoinfo_url );
	if ( $xml == false ) {
		return new WP_Error( 'bliptv_info_retrieval', __( 'Error retrieving video information from the URL <a href="' . $videoinfo_url . '">' . $videoinfo_url . '</a>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.' ) );
	} else {
		$result = $xml->xpath( "/rss/channel/item/media:thumbnail/@url" );
		$thumbnail = (string) $result[0]['url'];
		return $thumbnail;
	}
}

// Justin.tv Functions
function getJustintvInfo( $id ) {
	$xml = simplexml_load_file( "http://api.justin.tv/api/clip/show/$id.xml" );
	return (string) $xml->clip->image_url_large;
}

// Get DailyMotion Thumbnail
function getDailyMotionThumbnail( $id ) {
	if ( ! function_exists( 'curl_init' ) ) {
		return null;
	} else {
		$ch = curl_init();
		$videoinfo_url = "https://api.dailymotion.com/video/$id?fields=thumbnail_url";
		curl_setopt( $ch, CURLOPT_URL, $videoinfo_url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_FAILONERROR, true ); // Return an error for curl_error() processing if HTTP response code >= 400
		$output = curl_exec( $ch );
		$output = json_decode( $output );
		$output = $output->thumbnail_url;
		if ( curl_error( $ch ) != null ) {
			$output = new WP_Error( 'dailymotion_info_retrieval', __( 'Error retrieving video information from the URL <a href="' . $videoinfo_url . '">' . $videoinfo_url . '</a>.<br /><a href="http://curl.haxx.se/libcurl/c/libcurl-errors.html">Libcurl error</a> ' . curl_errno( $ch ) . ': <code>' . curl_error( $ch ) . '</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.' ) );
		}
		curl_close( $ch ); // Moved here to allow curl_error() operation above. Was previously below curl_exec() call.
		return $output;
	}
};

// Metacafe
function getMetacafeThumbnail( $id ) {
	$videoinfo_url = "http://www.metacafe.com/api/item/$id/";
	$xml = simplexml_load_file( $videoinfo_url );
	if ( $xml == false ) {
		return new WP_Error( 'metacafe_info_retrieval', __( 'Error retrieving video information from the URL <a href="' . $videoinfo_url . '">' . $videoinfo_url . '</a>.<br /><a href="http://curl.haxx.se/libcurl/c/libcurl-errors.html">Libcurl error</a> ' . curl_errno( $ch ) . ': <code>' . curl_error( $ch ) . '</code>. If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.' ) );
	} else {
		$result = $xml->xpath( "/rss/channel/item/media:thumbnail/@url" );
		$thumbnail = (string) $result[0]['url'];
		return $thumbnail;
	}
};

//
// The Main Event
//
function get_video_thumbnail( $post_id = null ) {

	// Get the post ID if none is provided
	if ( $post_id == null OR $post_id == '' ) $post_id = get_the_ID();

	// Check to see if thumbnail has already been found
	if( ( $thumbnail_meta = get_post_meta( $post_id, '_video_thumbnail', true ) ) != '' ) {
		return $thumbnail_meta;
	}
	// If the thumbnail isn't stored in custom meta, fetch a thumbnail
	else {

		// Get the post or custom field to search
		if ( $video_key = get_option( 'video_thumbnails_custom_field' ) ) {
			$markup = get_post_meta( $post_id, $video_key, true );
		} else {
			$post_array = get_post( $post_id );
			$markup = $post_array->post_content;
			$markup = apply_filters( 'the_content', $markup );
		}
		$new_thumbnail = null;

		// Simple Video Embedder Compatibility
		if ( function_exists( 'p75HasVideo' ) ) {
			if ( p75HasVideo( $post_id ) ) {
				$markup = p75GetVideo( $post_id );
			}
		}

		// Checks for the old standard YouTube embed
		preg_match( '#<object[^>]+>.+?https?://www\.youtube(?:\-nocookie)?\.com/[ve]/([A-Za-z0-9\-_]+).+?</object>#s', $markup, $matches );

		// More comprehensive search for YouTube embed, redundant but necessary until more testing is completed
		if ( !isset( $matches[1] ) ) {
			preg_match( '#https?://www\.youtube(?:\-nocookie)?\.com/[ve]/([A-Za-z0-9\-_]+)#', $markup, $matches );
		}

		// Checks for YouTube iframe, the new standard since at least 2011
		if ( !isset( $matches[1] ) ) {
			preg_match( '#https?://www\.youtube(?:\-nocookie)?\.com/embed/([A-Za-z0-9\-_]+)#', $markup, $matches );
		}

		// Checks for any YouTube URL. After http(s) support a or v for Youtube Lyte and v or vh for Smart Youtube plugin
		if ( !isset( $matches[1] ) ) {
			preg_match( '#(?:https?(?:a|vh?)?://)?(?:www\.)?youtube(?:\-nocookie)?\.com/watch\?.*v=([A-Za-z0-9\-_]+)#', $markup, $matches );
		}

		// Checks for any shortened youtu.be URL. After http(s) a or v for Youtube Lyte and v or vh for Smart Youtube plugin
		if ( !isset( $matches[1] ) ) {
			preg_match( '#(?:https?(?:a|vh?)?://)?youtu\.be/([A-Za-z0-9\-_]+)#', $markup, $matches );
		}

		// Checks for YouTube Lyte
		if ( !isset( $matches[1] ) && function_exists( 'lyte_parse' ) ) {
			preg_match( '#<div class="lyte" id="([A-Za-z0-9\-_]+)"#', $markup, $matches );
		}

		// If we've found a YouTube video ID, create the thumbnail URL
		if ( isset( $matches[1] ) ) {
			$youtube_thumbnail = 'http://img.youtube.com/vi/' . $matches[1] . '/0.jpg';

			// Check to make sure it's an actual thumbnail
			if ( ! function_exists( 'curl_init' ) ) {
				$new_thumbnail = $youtube_thumbnail;
			} else {
				$ch = curl_init( $youtube_thumbnail );
				curl_setopt( $ch, CURLOPT_NOBODY, true );
				curl_exec( $ch );
				$retcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
				// $retcode > 400 -> not found, $retcode = 200, found.
				curl_close( $ch );
				if ( $retcode == 200 ) {
					$new_thumbnail = $youtube_thumbnail;
				}
			}
		}

		// Vimeo
		if ( $new_thumbnail == null ) {

			// Standard embed code
			preg_match( '#<object[^>]+>.+?http://vimeo\.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+)&.+?</object>#s', $markup, $matches );

			// Find Vimeo embedded with iframe code
			if ( !isset( $matches[1] ) ) {
				preg_match( '#http://player\.vimeo\.com/video/([0-9]+)#', $markup, $matches );
			}

			// If we still haven't found anything, check for Vimeo embedded with JR_embed
			if ( !isset( $matches[1] ) ) {
				preg_match( '#\[vimeo id=([A-Za-z0-9\-_]+)]#', $markup, $matches );
			}

			// If we still haven't found anything, check for Vimeo URL
			if ( !isset( $matches[1] ) ) {
				preg_match( '#(?:http://)?(?:www\.)?vimeo\.com/([A-Za-z0-9\-_]+)#', $markup, $matches );
			}

			// If we still haven't found anything, check for Vimeo shortcode
			if ( !isset( $matches[1] ) ) {
				preg_match( '#\[vimeo clip_id="([A-Za-z0-9\-_]+)"[^>]*]#', $markup, $matches );
			}
			if ( !isset( $matches[1] ) ) {
				preg_match( '#\[vimeo video_id="([A-Za-z0-9\-_]+)"[^>]*]#', $markup, $matches );
			}

			// Now if we've found a Vimeo ID, let's set the thumbnail URL
			if ( isset( $matches[1] ) ) {
				$vimeo_thumbnail = getVimeoInfo( $matches[1], $info = 'thumbnail_large' );
				if ( is_wp_error( $vimeo_thumbnail ) ) {
					return $vimeo_thumbnail;
				} else if ( isset( $vimeo_thumbnail ) ) {
					$new_thumbnail = $vimeo_thumbnail;
				}
			}
		}

		// Blip.tv
		if ( $new_thumbnail == null ) {

			// Blip.tv embed URL
			preg_match( '#http://blip\.tv/play/([A-Za-z0-9]+)#', $markup, $matches );

			// Now if we've found a Blip.tv embed URL, let's set the thumbnail URL
			if ( isset( $matches[1] ) ) {
				$blip_thumbnail = getBliptvInfo( $matches[1] );
				if ( is_wp_error( $blip_thumbnail ) ) {
					return $blip_thumbnail;
				} else if ( isset( $blip_thumbnail ) ) {
					$new_thumbnail = $blip_thumbnail;
				}
			}
		}

		// Justin.tv
		if ( $new_thumbnail == null ) {

			// Justin.tv archive ID
			preg_match( '#archive_id=([0-9]+)#', $markup, $matches );

			// Now if we've found a Justin.tv archive ID, let's set the thumbnail URL
			if ( isset( $matches[1] ) ) {
				$justin_thumbnail = getJustintvInfo( $matches[1] );
				$new_thumbnail = $justin_thumbnail;
			}
		}

		// Dailymotion
		if ( $new_thumbnail == null ) {

			// Dailymotion flash
			preg_match( '#<object[^>]+>.+?http://www\.dailymotion\.com/swf/video/([A-Za-z0-9]+).+?</object>#s', $markup, $matches );

			// Dailymotion url
			if ( !isset( $matches[1] ) ) {
				preg_match( '#(?:https?://)?(?:www\.)?dailymotion\.com/video/([A-Za-z0-9]+)#', $markup, $matches );
			}

			// Dailymotion iframe
			if ( !isset( $matches[1] ) ) {
				preg_match( '#https?://www\.dailymotion\.com/embed/video/([A-Za-z0-9]+)#', $markup, $matches );
			}

			// Now if we've found a Dailymotion video ID, let's set the thumbnail URL
			if ( isset( $matches[1] ) ) {
				$dailymotion_thumbnail = getDailyMotionThumbnail( $matches[1] );
			if ( is_wp_error( $dailymotion_thumbnail ) ) {
					return $dailymotion_thumbnail;
				} else if ( isset( $dailymotion_thumbnail ) ) {
					$new_thumbnail = strtok( $dailymotion_thumbnail, '?' );
				}
			}
		}

		// Metacafe
		if ( $new_thumbnail == null ) {

			// Find ID from Metacafe embed url
			preg_match( '#http://www\.metacafe\.com/fplayer/([A-Za-z0-9\-_]+)/#', $markup, $matches );

			// Now if we've found a Metacafe video ID, let's set the thumbnail URL
			if ( isset( $matches[1] ) ) {
				$metacafe_thumbnail = getMetacafeThumbnail( $matches[1] );
				if ( is_wp_error( $metacafe_thumbnail ) ) {
					return $metacafe_thumbnail;
				} else if ( isset( $metacafe_thumbnail ) ) {
					$new_thumbnail = strtok( $metacafe_thumbnail, '?' );
				}
			}
		}

		// Return the new thumbnail variable and update meta if one is found
		if ( $new_thumbnail != null ) {

			// Save as Attachment if enabled
			if ( get_option( 'video_thumbnails_save_media' ) == 1 ) {

				$response = wp_remote_get( $new_thumbnail, array( 'sslverify' => false ) );

				// Check for error
				if( is_wp_error( $response ) ) {
					return new WP_Error( 'thumbnail_retrieval', __( 'Error retrieving a thumbnail from the URL <a href="' . $new_thumbnail . '">' . $new_thumbnail . '</a> If opening that URL in your web browser shows an image, the problem may be related to your web server and might be something your server administrator can solve. Error details: "' . $response->get_error_message() . '"' ) );
				}

				$image_contents = wp_remote_retrieve_body( $response );

				$upload = wp_upload_bits( basename( $new_thumbnail ), null, $image_contents );

				$new_thumbnail = $upload['url'];

				$filename = $upload['file'];

				$wp_filetype = wp_check_filetype( basename( $filename ), null );
				$attachment = array(
					'post_mime_type'	=> $wp_filetype['type'],
					'post_title'		=> get_the_title($post_id),
					'post_content'		=> '',
					'post_status'		=> 'inherit'
				);
				$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
				// you must first include the image.php file
				// for the function wp_generate_attachment_metadata() to work
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );
			
			}

			// Add hidden custom field with thumbnail URL
			if ( !update_post_meta( $post_id, '_video_thumbnail', $new_thumbnail ) ) add_post_meta( $post_id, '_video_thumbnail', $new_thumbnail, true );

			// Set attachment as featured image if enabled
			if ( get_option( 'video_thumbnails_set_featured' ) == 1 && get_option( 'video_thumbnails_save_media' ) == 1 && !has_post_thumbnail( $post_id ) ) {
				set_post_thumbnail( $post_id, $attach_id );
			}
		}
		return $new_thumbnail;

	}
};

// Echo thumbnail
function video_thumbnail( $post_id = null ) {
	if ( ( $video_thumbnail = get_video_thumbnail( $post_id ) ) == null ) { echo plugins_url() . '/video-thumbnails/default.jpg'; }
	else { echo $video_thumbnail; }
};

// Create Meta Fields on Edit Page

add_action( 'admin_init', 'video_thumbnail_admin_init' );

function video_thumbnail_admin_init() {
	$video_thumbnails_post_types = get_option( 'video_thumbnails_post_types' );
	if ( is_array( $video_thumbnails_post_types ) ) {
		foreach ( $video_thumbnails_post_types as $type ) {
			add_meta_box( 'video_thumbnail', 'Video Thumbnail', 'video_thumbnail_admin', $type, 'side', 'low' );
		}
	}
}

function video_thumbnail_admin() {
	global $post;
	$custom = get_post_custom( $post->ID );
	$video_thumbnail = $custom["_video_thumbnail"][0];

	if ( isset( $video_thumbnail ) && $video_thumbnail != '' ) {
		echo '<p id="video-thumbnails-preview"><img src="' . $video_thumbnail . '" style="max-width:100%;" /></p>';	}

	if ( get_post_status() == 'publish' || get_post_status() == 'private' ) {
		if ( isset( $video_thumbnail ) && $video_thumbnail != '' ) {
			echo '<p><a href="#" id="video-thumbnails-reset" onclick="video_thumbnails_reset(\'' . $post->ID . '\' );return false;">Reset Video Thumbnail</a></p>';
		} else {
			echo '<p id="video-thumbnails-preview">We didn\'t find a video thumbnail for this post.</p>';
			echo '<p><a href="#" id="video-thumbnails-reset" onclick="video_thumbnails_reset(\'' . $post->ID . '\' );return false;">Search Again</a></p>';
		}
	} else {
		if ( isset( $video_thumbnail ) && $video_thumbnail != '' ) {
			echo '<p><a href="#" id="video-thumbnails-reset" onclick="video_thumbnails_reset(\'' . $post->ID . '\' );return false;">Reset Video Thumbnail</a></p>';
		} else {
			echo '<p>A video thumbnail will be found for this post when it is published.</p>';
		}
	}
}

// AJAX Searching

if ( in_array( basename( $_SERVER['PHP_SELF'] ), apply_filters( 'video_thumbnails_editor_pages', array( 'post-new.php', 'page-new.php', 'post.php', 'page.php' ) ) ) ) {
	add_action( 'admin_head', 'video_thumbnails_ajax' );
}

function video_thumbnails_ajax() {
?>

<!-- Video Thumbnails Researching Ajax -->
<script type="text/javascript">
function video_thumbnails_reset(id) {

	var data = {
		action: 'video_thumbnails',
		post_id: id
	};

	document.getElementById('video-thumbnails-preview').innerHTML='Working... <img src="<?php echo home_url( 'wp-admin/images/loading.gif' ); ?>"/>';

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function(response){
		document.getElementById('video-thumbnails-preview').innerHTML=response;
	});
};
</script>
<?php
}

add_action( 'wp_ajax_video_thumbnails', 'video_thumbnails_callback' );

function video_thumbnails_callback() {
	global $wpdb; // this is how you get access to the database

	$post_id = $_POST['post_id'];

	delete_post_meta( $post_id, '_video_thumbnail' );

	$video_thumbnail = get_video_thumbnail( $post_id );

	if ( is_wp_error( $video_thumbnail ) ) {
		echo $video_thumbnail->get_error_message();
	} else if ( $video_thumbnail != null ) {
		echo '<img src="' . $video_thumbnail . '" style="max-width:100%;" />';
	} else {
		echo 'We didn\'t find a video thumbnail for this post. (be sure you have saved changes first)';
	}

	die();
}

// Find video thumbnail when saving a post, but not on autosave

add_action( 'new_to_publish', 'save_video_thumbnail', 10, 1 );
add_action( 'draft_to_publish', 'save_video_thumbnail', 10, 1 );
add_action( 'pending_to_publish', 'save_video_thumbnail', 10, 1 );
add_action( 'future_to_publish', 'save_video_thumbnail', 10, 1 );
add_action( 'private_to_publish', 'save_video_thumbnail', 10, 1 );

// Finds thumbnail when posting from XML-RPC
// (this action passes the post ID as an argument so 'get_video_thumbnail' is used instead)

add_action( 'xmlrpc_publish_post', 'get_video_thumbnail', 10, 1 );

function save_video_thumbnail( $post ) {
	$post_type = get_post_type( $post->ID );
	$video_thumbnails_post_types = get_option( 'video_thumbnails_post_types' );
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return null;
	} else {
		// Check that Video Thumbnails are enabled for current post type
		if ( in_array( $post_type, (array) $video_thumbnails_post_types ) || $post_type == $video_thumbnails_post_types ) {
			get_video_thumbnail( $post->ID );
		} else {
			return null;
		}
	}
}

// Set Default Options

register_activation_hook( __FILE__, 'video_thumbnails_activate' );
register_deactivation_hook( __FILE__, 'video_thumbnails_deactivate' );

function video_thumbnails_activate() {
	add_option( 'video_thumbnails_save_media', '1' );
	add_option( 'video_thumbnails_set_featured', '1' );
	add_option( 'video_thumbnails_custom_field', '' );
	add_option( 'video_thumbnails_post_types', array( 'post' ) );
}

function video_thumbnails_deactivate() {
	delete_option( 'video_thumbnails_save_media' );
	delete_option( 'video_thumbnails_set_featured' );
	delete_option( 'video_thumbnails_custom_field' );
	delete_option( 'video_thumbnails_post_types' );
}

// Check for cURL

register_activation_hook( __FILE__, 'video_thumbnails_curl_check' );

function video_thumbnails_curl_check() {
	if ( ! function_exists( 'curl_init' ) ) {
		deactivate_plugins( basename( __FILE__ ) ); // Deactivate ourself
		wp_die( 'Sorry, but this plugin requires <a href="http://curl.haxx.se/libcurl/">libcurl</a> to be activated on your server.' );
	}
}

// AJAX for Past Posts

if ( isset ( $_GET['page'] ) && ( $_GET['page'] == 'video-thumbnail-options' ) ) {
	add_action( 'admin_head', 'video_thumbnails_past_ajax' );
}

function video_thumbnails_past_ajax() {
?>

<!-- Video Thumbnails Past Post Ajax -->
<script type="text/javascript">
function video_thumbnails_past(id) {

	var data = {
		action: 'video_thumbnails_past',
		post_id: id
	};

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function(response){

		document.getElementById(id+'_result').innerHTML = response;

	});

};

<?php
$video_thumbnails_post_types = get_option( 'video_thumbnails_post_types' );
$posts = get_posts( array(
	'showposts' => -1,
	'post_type' => $video_thumbnails_post_types
) );

if ( $posts ) {
	foreach ( $posts as $post ) {
		$post_ids[] = $post->ID;
	}
	$ids = implode( ', ', $post_ids );
}
?>

var scanComplete = false;

function scan_video_thumbnails(){

	if(scanComplete==false){
		scanComplete = true;
		var ids = new Array(<?php echo $ids; ?>);
		for (var i = 0; i < ids.length; i++){
			var container = document.getElementById('video-thumbnails-past');
			var new_element = document.createElement('li');
			new_element.setAttribute('id',ids[i]+'_result');
			new_element.innerHTML = 'Waiting...';
			container.insertBefore(new_element, container.firstChild);
		}
		for (var i = 0; i < ids.length; i++){
			document.getElementById(ids[i]+'_result').innerHTML = '<span style="color:yellow">&#8226;</span> Working...';
			video_thumbnails_past(ids[i]);
		}
	} else {
		alert('Scan has already been run, please reload the page before trying again.')
	}

}
</script>

<?php
}

add_action( 'wp_ajax_video_thumbnails_past', 'video_thumbnails_past_callback' );

function video_thumbnails_past_callback() {
	global $wpdb; // this is how you get access to the database

	$post_id = $_POST['post_id'];

	echo get_the_title( $post_id ) . ' - ';

	$video_thumbnail = get_video_thumbnail( $post_id );

	if ( is_wp_error( $video_thumbnail ) ) {
		echo $video_thumbnail->get_error_message();
	} else if ( $video_thumbnail != null ) {
		echo '<span style="color:green">&#10004;</span> Success!';
	} else {
		echo '<span style="color:red">&#10006;</span> Couldn\'t find a video thumbnail for this post.';
	}

	die();
}

// Administration

add_action( 'admin_menu', 'video_thumbnails_menu' );

function video_thumbnails_menu() {
	add_options_page( 'Video Thumbnail Options', 'Video Thumbnails', 'manage_options', 'video-thumbnail-options', 'video_thumbnail_options' );
}

function video_thumbnails_checkbox_option( $option_name, $option_description ) { ?>
	<fieldset><legend class="screen-reader-text"><span><?php echo $option_description; ?></span></legend>
	<label for="<?php echo $option_name; ?>"><input name="<?php echo $option_name; ?>" type="checkbox" id="<?php echo $option_name; ?>" value="1" <?php if ( get_option( $option_name ) == 1 ) echo 'checked="checked"'; ?>/> <?php echo $option_description; ?></label>
	</fieldset> <?php
}

function video_thumbnail_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

?>

<div class="wrap">

	<div id="icon-options-general" class="icon32"></div><h2>Video Thumbnails Options</h2>

	<p>Say thanks by donating, any amount is appreciated!<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHRwYJKoZIhvcNAQcEoIIHODCCBzQCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYB1rPWk/Rr89ydxDsoXWyYIlAwIORRiWzcLHSBBVBMY69PHCO6WVTK2lXYmjZbDrvrHmN/jrM5r3Q008oX19NujzZ4d1VV+dWZxPU+vROuLToOFkk3ivjcvlT825HfdZRoiY/eTwWfBH93YQ+3kAAdc2s3FRxVyC4cUdrtbkBmYpDELMAkGBSsOAwIaBQAwgcQGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIkO3IVfkE9PGAgaA9fgOdXrQSpdGgo8ZgjiOxDGlEHoRL51gvB6AZdhNCubfLbqolJjYfTPEMg6Z0dfrq3hVSF2+nLV7BRcmXAtxY5NkH7vu1Kv0Bsb5kDOWb8h4AfnwElD1xyaykvYAr7CRNqHcizYRXZHKE7elWY0w6xRV/bfE7w6E4ZjKvFowHFp9E7/3mcZDrqxbZVU5hqs5gsV2YJj8fNBzG1bbdTucXoIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMTExMDA3MDUzMjM1WjAjBgkqhkiG9w0BCQQxFgQUHXhTYmeIfU7OyslesSVlGviqHbIwDQYJKoZIhvcNAQEBBQAEgYDAU3s+ej0si2FdN0uZeXhR+GGCDOMSYbkRswu7K3TRDXoD9D9c67VjQ+GfqP95cA9s40aT73goH+AxPbiQhG64OaHZZGJeSmwiGiCo4rBoVPxNUDONMPWaYfp6vm3Mt41gbxUswUEDNnzps4waBsFRJvuFjbbeQVYg7wbVfQC99Q==-----END PKCS7-----"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></form></p>

	<form method="post" action="options.php">
	<?php wp_nonce_field( 'update-options' ); ?>

	<table class="form-table">

	<tr valign="top">
	<th scope="row">Save Thumbnail to Media</th>
	<td><?php video_thumbnails_checkbox_option( 'video_thumbnails_save_media', 'Save local copies of thumbnails using the media library' ); ?></td>
	</tr>

	<tr valign="top">
	<th scope="row">Set as Featured Image</th>
	<td><?php video_thumbnails_checkbox_option( 'video_thumbnails_set_featured', 'Automatically set thumbnail as featured image ("Save Thumbnail to Media" must be enabled)' ); ?></td>
	</tr>

	<tr valign="top">
	<th scope="row">Post Types</th>
	<td>
	<?php $video_thumbnails_post_types = get_option( 'video_thumbnails_post_types' ); ?>
		<?php foreach ( get_post_types() as $type ) : if ( $type == 'attachment' OR $type == 'revision' OR $type == 'nav_menu_item' ) continue; ?>
		<label for="video_thumbnails_post_types_<?php echo $type; ?>">
			<input id="video_thumbnails_post_types_<?php echo $type; ?>" name="video_thumbnails_post_types[]" type="checkbox" value="<?php echo $type; ?>" <?php if ( is_array( $video_thumbnails_post_types ) ) checked( in_array( $type, $video_thumbnails_post_types ) ); ?> />
			<?php echo $type; ?>
		</label>
		<br />
		<?php endforeach; ?>
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">Custom Field (Optional: If your video is stored in a custom field, enter the name of that field here. Otherwise, leave this field blank.)</th> 
	<td><fieldset><legend class="screen-reader-text"><span>Custom Field (Optional: If your video is stored in a custom field, enter the name of that field here. Otherwise, leave this field blank.)</span></legend> 
	<input name="video_thumbnails_custom_field" type="text" id="video_thumbnails_custom_field" value="<?php echo get_option( 'video_thumbnails_custom_field' ); ?>" />
	</fieldset></td>
	</tr>

	</table>

	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
	</p>

	<h3>How to use</h3>

	<p>For themes that use featured images, simply leave the two settings above enabled.</p>

	<p>For more detailed instructions, check out the page for <a href="http://wordpress.org/extend/plugins/video-thumbnails/">Video Thumbnails on the official plugin directory</a>.</p>

	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="video_thumbnails_save_media,video_thumbnails_set_featured,video_thumbnails_post_types,video_thumbnails_custom_field" />

	</form>

	<h3>Scan All Posts</h3>

	<p>Scan all of your past posts for video thumbnails. Be sure to save any settings before running the scan.</p>

	<p><input type="submit" class="button-primary" onclick="scan_video_thumbnails();" value="Scan Past Posts" /></p>

	<ol id="video-thumbnails-past">
	</ol>

</div>

<?php

}

?>