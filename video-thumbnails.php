<?php
/*
Plugin Name: Video Thumbnails
Plugin URI: https://refactored.co/plugins/video-thumbnails
Description: Automatically retrieve video thumbnails for your posts and display them in your theme. Supports YouTube, Vimeo, Facebook, Vine, Justin.tv, Twitch, Dailymotion, Metacafe, VK, Blip, Google Drive, Funny or Die, CollegeHumor, MPORA, Wistia, Youku, and Rutube.
Author: Sutherland Boswell
Author URI: http://sutherlandboswell.com
Version: 2.12.3
License: GPL2
Text Domain: video-thumbnails
Domain Path: /languages/
*/
/*  Copyright 2015 Sutherland Boswell  (email : sutherland.boswell@gmail.com)

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

// Define

define( 'VIDEO_THUMBNAILS_PATH', dirname(__FILE__) );
define( 'VIDEO_THUMBNAILS_FIELD', '_video_thumbnail' );
define( 'VIDEO_THUMBNAILS_VERSION', '2.12.3' );

// Providers
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/providers.php' );

// Extensions
require_once( VIDEO_THUMBNAILS_PATH . '/php/extensions/extensions.php' );

// Settings
require_once( VIDEO_THUMBNAILS_PATH . '/php/class-video-thumbnails-settings.php' );

// Class

class Video_Thumbnails {

	var $providers = array();
	var $settings;

	function __construct() {

		// Load translations
		add_action( 'plugins_loaded', array( &$this, 'plugin_textdomain' ));

		// Create provider array
		$this->providers = apply_filters( 'video_thumbnail_providers', $this->providers );

		// Settings
		$this->settings = new Video_Thumbnails_Settings();

		// Initialize meta box
		add_action( 'admin_init', array( &$this, 'meta_box_init' ) );

		// Add actions to save video thumbnails when saving
		add_action( 'save_post', array( &$this, 'save_video_thumbnail' ), 100, 1 );

		// Add actions to save video thumbnails when posting from XML-RPC (this action passes the post ID as an argument so 'get_video_thumbnail' is used instead)
		add_action( 'xmlrpc_publish_post', 'get_video_thumbnail', 10, 1 );

		// Add action for Ajax reset script on edit pages
		if ( in_array( basename( $_SERVER['PHP_SELF'] ), apply_filters( 'video_thumbnails_editor_pages', array( 'post-new.php', 'page-new.php', 'post.php', 'page.php' ) ) ) ) {
			add_action( 'admin_head', array( &$this, 'ajax_reset_script' ) );
		}

		// Add action for Ajax reset callback
		add_action( 'wp_ajax_reset_video_thumbnail', array( &$this, 'ajax_reset_callback' ) );

		// Add admin menus
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

		// Add JavaScript and CSS to admin pages
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ), 20 );

		// Get the posts to be scanned in bulk
		add_action('wp_ajax_video_thumbnails_bulk_posts_query', array( &$this, 'bulk_posts_query_callback' ) );
		// Get the thumbnail for an individual post
		add_action('wp_ajax_video_thumbnails_get_thumbnail_for_post', array( &$this, 'get_thumbnail_for_post_callback' ) );

	}

	/**
	 * Load language files
	 */
	function plugin_textdomain() {
		load_plugin_textdomain( 'video-thumbnails', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Adds the admin menu items
	 */
	function admin_menu() {
		add_management_page( __( 'Bulk Video Thumbnails', 'video-thumbnails' ), __( 'Bulk Video Thumbs', 'video-thumbnails' ), 'manage_options', 'video-thumbnails-bulk', array( &$this, 'bulk_scanning_page' ) );
	}

	/**
	 * Enqueues necessary admin scripts
	 * @param  string $hook A hook for the current admin page
	 */
	function admin_scripts( $hook ) {
		// Bulk tool page
		if ( 'tools_page_video-thumbnails-bulk' == $hook ) {
			wp_enqueue_script( 'video-thumbnails-bulk-js', plugins_url( '/js/bulk.js' , __FILE__ ), array( 'jquery' ), VIDEO_THUMBNAILS_VERSION );
			wp_localize_script( 'video-thumbnails-bulk-js', 'video_thumbnails_bulk_language', array(
				'working'              => __( 'Working...', 'video-thumbnails' ),
				'started'              => __( 'Started Scanning', 'video-thumbnails' ),
				'resumed'              => __( 'Resumed Scanning', 'video-thumbnails' ),
				'paused'               => __( 'Paused Scanning', 'video-thumbnails' ),
				'done'                 => __( 'Done!', 'video-thumbnails' ),
				'final_count_singular' => __( 'Scanned 1 post', 'video-thumbnails' ),
				'final_count_plural'   => __( 'Scanned %d posts', 'video-thumbnails' ),
				'queue_singular'       => __( '1 post in queue', 'video-thumbnails' ),
				'queue_plural'         => __( '%d posts in queue', 'video-thumbnails' ),
				'scanning_of'          => __( 'Scanning %1$s of %2$s', 'video-thumbnails' ),
				'no_thumbnail'         => __( 'No thumbnail', 'video-thumbnails' ),
				'new_thumbnail'        => __( 'New thumbnail:', 'video-thumbnails' ),
				'existing_thumbnail'   => __( 'Existing thumbnail:', 'video-thumbnails' ),
				'error'                => __( 'Error:', 'video-thumbnails' ),
			) );
			wp_enqueue_style( 'video-thumbnails-bulk-css', plugins_url('/css/bulk.css', __FILE__), false, VIDEO_THUMBNAILS_VERSION );
		}
	}

	/**
	 * Initialize meta box on edit page
	 */
	function meta_box_init() {
		if ( is_array( $this->settings->options['post_types'] ) ) {
			foreach ( $this->settings->options['post_types'] as $type ) {
				add_meta_box( 'video_thumbnail', 'Video Thumbnail', array( &$this, 'meta_box' ), $type, 'side', 'low' );
			}
		}
	}

	/**
	 * Renders the video thumbnail meta box
	 */
	function meta_box() {
		global $post;
		// Add hidden troubleshooting info
		add_thickbox();
		?>
		<div id="video-thumbnail-not-found-troubleshooting" style="display:none;">
			<h2><?php _e( 'Troubleshooting Video Thumbnails', 'video-thumbnails' ); ?></h2>
			<?php $this->no_video_thumbnail_troubleshooting_instructions(); ?>
		</div>
		<?php
		$custom = get_post_custom( $post->ID );
		if ( isset( $custom[VIDEO_THUMBNAILS_FIELD][0] ) ) $video_thumbnail = $custom[VIDEO_THUMBNAILS_FIELD][0];

		if ( isset( $video_thumbnail ) && $video_thumbnail != '' ) {
			echo '<p id="video-thumbnails-preview"><img src="' . $video_thumbnail . '" style="max-width:100%;" /></p>';	}

		if ( get_post_status() == 'publish' || get_post_status() == 'private' ) {
			if ( isset( $video_thumbnail ) && $video_thumbnail != '' ) {
				echo '<p><a href="#" id="video-thumbnails-reset" onclick="video_thumbnails_reset(\'' . $post->ID . '\' );return false;">' . __( 'Reset Video Thumbnail', 'video-thumbnails' ) . '</a></p>';
			} else {
				echo '<p id="video-thumbnails-preview">' . __( 'No video thumbnail for this post.', 'video-thumbnails' ) . '</p>';
				echo '<p><a href="#" id="video-thumbnails-reset" onclick="video_thumbnails_reset(\'' . $post->ID . '\' );return false;">' . __( 'Search Again', 'video-thumbnails' ) . '</a> <a href="#TB_inline?width=400&height=600&inlineId=video-thumbnail-not-found-troubleshooting" class="thickbox" style="float:right;">' . __( 'Troubleshoot', 'video-thumbnails' ) . '</a></p>';
			}
		} else {
			if ( isset( $video_thumbnail ) && $video_thumbnail != '' ) {
				echo '<p><a href="#" id="video-thumbnails-reset" onclick="video_thumbnails_reset(\'' . $post->ID . '\' );return false;">' . __( 'Reset Video Thumbnail', 'video-thumbnails' ) . '</a></p>';
			} else {
				echo '<p>' . __( 'A video thumbnail will be found for this post when it is published.', 'video-thumbnails' ) . '</p>';
			}
		}
	}

	/**
	 * Prints a guide for troubleshooting no video thumbnails
	 */
	public static function no_video_thumbnail_troubleshooting_instructions() {
		?>
		<h3><?php _e( 'Fixing "No video thumbnail for this post"', 'video-thumbnails' ); ?></h3>
		<ol>
			<li><?php _e( 'Ensure you have saved any changes to your post.', 'video-thumbnails' ); ?></li>
			<li><?php echo sprintf( __( 'If you are using a a plugin or theme that stores videos in a special location other than the main post content area, be sure you\'ve entered the correct custom field on the <a href="%s">settings page</a>. If you don\'t know the name of the field your video is being saved in, please contact the developer of that theme or plugin.', 'video-thumbnails' ), admin_url( 'options-general.php?page=video_thumbnails' ) ); ?></li>
			<li><?php echo sprintf( __( 'Copy and paste your embed code into the "Test Markup for Video" section of the <a href="%1$s">Debugging page</a>. If this doesn\'t find the thumbnail, you\'ll want to be sure to include the embed code you scanned when you request support. If it does find a thumbnail, please double check that you have the Custom Field set correctly in the <a href="%2$s">settings page</a> if you are using a a plugin or theme that stores videos in a special location.', 'video-thumbnails' ), admin_url( 'options-general.php?page=video_thumbnails&tab=debugging' ), admin_url( 'options-general.php?page=video_thumbnails' ) ); ?></li>
			<li><?php echo sprintf( __( 'Go to the <a href="%s">Debugging page</a> and click "Test Image Downloading" to test your server\'s ability to save an image from a video source.', 'video-thumbnails' ), admin_url( 'options-general.php?page=video_thumbnails&tab=debugging' ) ); ?></li>
			<li><?php _e( 'Try posting a video from other sources to help narrow down the problem.', 'video-thumbnails' ); ?></li>
			<li><?php _e( 'Search the <a href="http://wordpress.org/support/plugin/video-thumbnails">support threads</a> to see if anyone has had the same issue.', 'video-thumbnails' ); ?></li>
			<li><?php _e( 'If you are still unable to resolve the problem, <a href="http://wordpress.org/support/plugin/video-thumbnails">start a thread</a> with a <strong>good descriptive</strong> title ("Error" or "No thumbnails" is a <strong>bad</strong> title) and be sure to include the results of your testing as well. Also be sure to include the <strong>name of your theme</strong>, any <strong>video plugins</strong> you\'re using, and any other details you can think of.', 'video-thumbnails' ); ?></li>
		</ol>
		<?php
	}

	/**
	 * A usort() callback that sorts videos by offset
	 */
	function compare_by_offset( $a, $b ) {
		return $a['offset'] - $b['offset'];
	}

	/**
	 * Find all the videos in a post
	 * @param  string $markup Markup to scan for videos
	 * @return array          An array of video information
	 */
	function find_videos( $markup ) {

		$videos = array();

		// Filter to modify providers immediately before scanning
		$providers = apply_filters( 'video_thumbnail_providers_pre_scan', $this->providers );

		foreach ( $providers as $key => $provider ) {

			$provider_videos = $provider->scan_for_videos( $markup );

			if ( empty( $provider_videos ) ) continue;

			foreach ( $provider_videos as $video ) {
				$videos[] = array(
					'id'       => $video[0],
					'provider' => $key,
					'offset'   => $video[1]
				);
			}

		}

		usort( $videos, array( &$this, 'compare_by_offset' ) );

		return $videos;

	}

	/**
	 * Finds the first video in markup and retrieves a thumbnail
	 * @param  string $markup Post markup to scan
	 * @return mixed          Null if no thumbnail or a string with a remote URL
	 */
	function get_first_thumbnail_url( $markup ) {
		$thumbnail = null;
		$videos = $this->find_videos( $markup );
		foreach ( $videos as $video ) {
			$thumbnail = $this->providers[$video['provider']]->get_thumbnail_url( $video['id'] );
			if ( $thumbnail != null ) break;
		}
		return $thumbnail;
	}

	/**
	 * Finds the video thumbnail for a post, saves/sets as featured image if enabled, saves image URL to custom field and then returns the URL
	 * @param  int   $post_id An optional post ID (can be left blank in a loop)
	 * @return mixed          A string with an image URL if successful or null if there is no video thumbnail
	 */
	function get_video_thumbnail( $post_id = null ) {

		// Get the post ID if none is provided
		if ( $post_id == null OR $post_id == '' ) $post_id = get_the_ID();

		// Check to see if thumbnail has already been found
		if( ( $thumbnail_meta = get_post_meta( $post_id, VIDEO_THUMBNAILS_FIELD, true ) ) != '' ) {
			return $thumbnail_meta;
		}
		// If the thumbnail isn't stored in custom meta, fetch a thumbnail
		else {

			$new_thumbnail = null;
			// Filter for extensions to set thumbnail
			$new_thumbnail = apply_filters( 'new_video_thumbnail_url', $new_thumbnail, $post_id );

			if ( $new_thumbnail == null ) {
				// Get the post or custom field to search
				if ( $this->settings->options['custom_field'] ) {
					$markup = get_post_meta( $post_id, $this->settings->options['custom_field'], true );
				} else {
					$post_array = get_post( $post_id );
					$markup = $post_array->post_content;
					$markup = apply_filters( 'the_content', $markup );
				}

				// Filter for extensions to modify what markup is scanned
				$markup = apply_filters( 'video_thumbnail_markup', $markup, $post_id );

				$new_thumbnail = $this->get_first_thumbnail_url( $markup );
			}

			// Return the new thumbnail variable and update meta if one is found
			if ( $new_thumbnail != null && !is_wp_error( $new_thumbnail ) ) {

				// Save as Attachment if enabled
				if ( $this->settings->options['save_media'] == 1 ) {
					$attachment_id = $this->save_to_media_library( $new_thumbnail, $post_id );
					if ( is_wp_error( $attachment_id ) ) {
						return $attachment_id;
					}
					$new_thumbnail = wp_get_attachment_image_src( $attachment_id, 'full' );
					$new_thumbnail = $new_thumbnail[0];
				}

				// Add hidden custom field with thumbnail URL
				if ( !update_post_meta( $post_id, VIDEO_THUMBNAILS_FIELD, $new_thumbnail ) ) add_post_meta( $post_id, VIDEO_THUMBNAILS_FIELD, $new_thumbnail, true );

				// Set attachment as featured image if enabled
				if ( $this->settings->options['set_featured'] == 1 && $this->settings->options['save_media'] == 1 ) {
					// Make sure there isn't already a post thumbnail
					if ( !ctype_digit( get_post_thumbnail_id( $post_id ) ) ) {
						set_post_thumbnail( $post_id, $attachment_id );
					}
				}
			}

			return $new_thumbnail;

		}
	}

	/**
	 * Gets a video thumbnail when a published post is saved
	 * @param  int $post_id The post ID
	 */
	function save_video_thumbnail( $post_id ) {
		// Don't save video thumbnails during autosave or for unpublished posts
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return null;
		if ( get_post_status( $post_id ) != 'publish' ) return null;
		// Check that Video Thumbnails are enabled for current post type
		$post_type = get_post_type( $post_id );
		if ( in_array( $post_type, (array) $this->settings->options['post_types'] ) || $post_type == $this->settings->options['post_types'] ) {
			$this->get_video_thumbnail( $post_id );
		} else {
			return null;
		}
	}

	/**
	 * Creates a file name for use when saving an image to the media library.
	 * It will either use a sanitized version of the title or the post ID.
	 * @param  int    $post_id The ID of the post to create the filename for
	 * @return string          A filename (without the extension)
	 */
	static function construct_filename( $post_id ) {
		$filename = get_the_title( $post_id );
		$filename = sanitize_title( $filename, $post_id );
		$filename = urldecode( $filename );
		$filename = preg_replace( '/[^a-zA-Z0-9\-]/', '', $filename );
		$filename = substr( $filename, 0, 32 );
		$filename = trim( $filename, '-' );
		if ( $filename == '' ) $filename = (string) $post_id;
		return $filename;
	}

	/**
	 * Saves a remote image to the media library
	 * @param  string $image_url URL of the image to save
	 * @param  int    $post_id   ID of the post to attach image to
	 * @return int               ID of the attachment
	 */
	public static function save_to_media_library( $image_url, $post_id ) {

		$error = '';
		$response = wp_remote_get( $image_url );
		if( is_wp_error( $response ) ) {
			$error = new WP_Error( 'thumbnail_retrieval', sprintf( __( 'Error retrieving a thumbnail from the URL <a href="%1$s">%1$s</a> using <code>wp_remote_get()</code><br />If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.', 'video-thumbnails' ), $image_url ) . '<br>' . __( 'Error Details:', 'video-thumbnails' ) . ' ' . $response->get_error_message() );
		} else {
			$image_contents = $response['body'];
			$image_type = wp_remote_retrieve_header( $response, 'content-type' );
		}

		if ( $error != '' ) {
			return $error;
		} else {

			// Translate MIME type into an extension
			if ( $image_type == 'image/jpeg' ) {
				$image_extension = '.jpg';
			} elseif ( $image_type == 'image/png' ) {
				$image_extension = '.png';
			} elseif ( $image_type == 'image/gif' ) {
				$image_extension = '.gif';
			} else {
				return new WP_Error( 'thumbnail_upload', __( 'Unsupported MIME type:', 'video-thumbnails' ) . ' ' . $image_type );
			}

			// Construct a file name with extension
			$new_filename = self::construct_filename( $post_id ) . $image_extension;

			// Save the image bits using the new filename
			do_action( 'video_thumbnails/pre_upload_bits', $image_contents );
			$upload = wp_upload_bits( $new_filename, null, $image_contents );
			do_action( 'video_thumbnails/after_upload_bits', $upload );

			// Stop for any errors while saving the data or else continue adding the image to the media library
			if ( $upload['error'] ) {
				$error = new WP_Error( 'thumbnail_upload', __( 'Error uploading image data:', 'video-thumbnails' ) . ' ' . $upload['error'] );
				return $error;
			} else {

				do_action( 'video_thumbnails/image_downloaded', $upload['file'] );

				$wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );

				$upload = apply_filters( 'wp_handle_upload', array(
					'file' => $upload['file'],
					'url'  => $upload['url'],
					'type' => $wp_filetype['type']
				), 'sideload' );

				// Contstruct the attachment array
				$attachment = array(
					'post_mime_type'	=> $upload['type'],
					'post_title'		=> get_the_title( $post_id ),
					'post_content'		=> '',
					'post_status'		=> 'inherit'
				);
				// Insert the attachment
				$attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );

				// you must first include the image.php file
				// for the function wp_generate_attachment_metadata() to work
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				do_action( 'video_thumbnails/pre_generate_attachment_metadata', $attach_id, $upload['file'] );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
				do_action( 'video_thumbnails/after_generate_attachment_metadata', $attach_id, $upload['file'] );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				// Add field to mark image as a video thumbnail
				update_post_meta( $attach_id, 'video_thumbnail', '1' );

			}

		}

		return $attach_id;

	}

	/**
	 * Ajax reset script for post editor
	 */
	function ajax_reset_script() {
		echo '<!-- Video Thumbnails Ajax Search -->' . PHP_EOL;
		echo '<script type="text/javascript">' . PHP_EOL;
		echo 'function video_thumbnails_reset(id) {' . PHP_EOL;
		echo '  var data = {' . PHP_EOL;
		echo '    action: "reset_video_thumbnail",' . PHP_EOL;
		echo '    post_id: id' . PHP_EOL;
		echo '  };' . PHP_EOL;
		echo '  document.getElementById(\'video-thumbnails-preview\').innerHTML=\'' . __( 'Working...', 'video-thumbnails' ) . ' <img src="' . home_url( 'wp-admin/images/loading.gif' ) . '"/>\';' . PHP_EOL;
		echo '  jQuery.post(ajaxurl, data, function(response){' . PHP_EOL;
		echo '    document.getElementById(\'video-thumbnails-preview\').innerHTML=response;' . PHP_EOL;
		echo '  });' . PHP_EOL;
		echo '};' . PHP_EOL;
		echo '</script>' . PHP_EOL;
	}

	/**
	 * Ajax callback for resetting a video thumbnail in the post editor
	 */
	function ajax_reset_callback() {
		global $wpdb; // this is how you get access to the database

		$post_id = $_POST['post_id'];

		delete_post_meta( $post_id, VIDEO_THUMBNAILS_FIELD );

		$video_thumbnail = get_video_thumbnail( $post_id );

		if ( is_wp_error( $video_thumbnail ) ) {
			echo $video_thumbnail->get_error_message();
		} else if ( $video_thumbnail != null ) {
			echo '<img src="' . $video_thumbnail . '" style="max-width:100%;" />';
		} else {
			echo __( 'No video thumbnail for this post.', 'video-thumbnails' );
		}

		die();
	}

	/**
	 * Ajax callback used to get all the post IDs to be scanned in bulk
	 */
	function bulk_posts_query_callback() {
		// Some default args
		$args = array(
			'posts_per_page' => -1,
			'post_type' => $this->settings->options['post_types'],
			'fields'    => 'ids'
		);
		// Setup an array for any form data and parse the jQuery serialized data
		$form_data = array();
		parse_str( $_POST['params'], $form_data );

		$args = apply_filters( 'video_thumbnails/bulk_posts_query', $args, $form_data );

		$query = new WP_Query( $args );
		echo json_encode( $query->posts );
		die();
	}

	/**
	 * Ajax callback used to get the video thumbnail for an individual post in the process of running the bulk tool
	 */
	function get_thumbnail_for_post_callback() {

		$post_id = $_POST['post_id'];
		$thumb = get_post_meta( $post_id, VIDEO_THUMBNAILS_FIELD, true );

		if ( $thumb == '' ) {
			global $video_thumbnails;
			$thumb = $video_thumbnails->get_video_thumbnail( $post_id );
			if ( $thumb ) {
				$type = 'new';
			}
		} else {
			$type = 'existing';
		}

		if ( $thumb != '' ) {
			$result = array(
				'type' => $type,
				'url' => $thumb
			);
		} else {
			$result = array();
		}

		echo json_encode( $result );
		die();
	}

	/**
	 * A function that renders the bulk scanning page
	 */
	function bulk_scanning_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'video-thumbnails' ) );
		}

		?>
		<div class="wrap">

			<div id="icon-tools" class="icon32"></div><h2><?php _e( 'Bulk Video Thumbnail Generator', 'video-thumbnails' ); ?></h2>

			<p><?php _e( 'Use this tool to scan all of your posts for Video Thumbnails.', 'video-thumbnails' ); ?></p>

			<form id="video-thumbnails-bulk-scan-options">
				<table class="form-table">
					<tbody>
						<?php do_action( 'video_thumbnails/bulk_options_form'); ?>
						<tr valign="top">
							<th scope="row"><span id="queue-count">...</span></th>
							<td>
								<input type="submit" value="<?php esc_attr_e( 'Scan Now', 'video-thumbnails' ); ?>" class="button button-primary">
							</td>
						</tr>
					</tbody>
				</table>
			</form>

			<div id="vt-bulk-scan-results">
				<div class="progress-bar-container">
					<span class="percentage">0%</span>
					<div class="progress-bar">&nbsp;</div>
				</div>
				<table class="stats">
					<thead>
						<tr>
							<th><?php _e( 'Scanned', 'video-thumbnails' ); ?></th>
							<th><?php _e( 'New Thumbnails', 'video-thumbnails' ); ?></th>
							<th><?php _e( 'Existing', 'video-thumbnails' ); ?></th>
						</tr>
					</thead>
					<tr>
						<td class="scanned">0</td>
						<td class="found-new">0</td>
						<td class="found-existing">0</td>
					</tr>
				</table>
				<ul class="log"></ul>
			</div>

		</div>
		<?php

	}

}

$video_thumbnails = new Video_Thumbnails();

do_action( 'video_thumbnails_plugin_loaded' );

// End class

// Get video thumbnail function
function get_video_thumbnail( $post_id = null ) {
	global $video_thumbnails;
	return $video_thumbnails->get_video_thumbnail( $post_id );
}

// Echo thumbnail
function video_thumbnail( $post_id = null ) {
	if ( ( $video_thumbnail = get_video_thumbnail( $post_id ) ) == null ) { echo plugins_url() . '/video-thumbnails/default.jpg'; }
	else { echo $video_thumbnail; }
}

?>