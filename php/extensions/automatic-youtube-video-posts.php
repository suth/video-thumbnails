<?php

/*  Copyright 2014 Sutherland Boswell  (email : sutherland.boswell@gmail.com)

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

// Require YouTube provider class
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-youtube-thumbnails.php' );

/**
 * Checks if AYVP is importing
 * @return boolean True if importing, false if not
 */
function is_ayvp_importing() {
	// Global variables used by AYVP
	global $getWP, $tern_wp_youtube_options, $tern_wp_youtube_o;
	// Check for the class used by AYVP
	if ( class_exists( 'ternWP' ) && isset( $getWP ) ) {
		// Load the AYVP options
		$tern_wp_youtube_o = $getWP->getOption( 'tern_wp_youtube', $tern_wp_youtube_options );
		if ( $tern_wp_youtube_o['is_importing'] && $tern_wp_youtube_o['is_importing'] !== false ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function ayvp_new_video_thumbnail_url_filter( $new_thumbnail, $post_id ) {
	global $video_thumbnails;
	if ( !isset( $video_thumbnails->providers['youtube'] ) ) return false;
	// When publishing a post during import, use the global variable to generate thumbnail
	if ( $new_thumbnail == null && is_ayvp_importing() ) {
		global $tern_wp_youtube_array;
		if ( isset( $tern_wp_youtube_array['_tern_wp_youtube_video'] ) && $tern_wp_youtube_array['_tern_wp_youtube_video'] != '' ) {
			$new_thumbnail = $video_thumbnails->providers['youtube']->get_thumbnail_url( $tern_wp_youtube_array['_tern_wp_youtube_video'] );
		}
	}
	// When automatic publishing is disabled or rescanning an existing post, use custom field data to generate thumbnail
	if ( $new_thumbnail == null ) {
		$youtube_id = get_post_meta( $post_id, '_tern_wp_youtube_video', true );
		if ( $youtube_id != '' ) {
			$new_thumbnail = $video_thumbnails->providers['youtube']->get_thumbnail_url( $youtube_id );
		}
	}
	return $new_thumbnail;
}

// Make sure we can use is_plugin_active()
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// If AYVP is active, add filter
if ( is_plugin_active( 'automatic-youtube-video-posts/tern_wp_youtube.php' ) ) {
	add_filter( 'new_video_thumbnail_url', 'ayvp_new_video_thumbnail_url_filter', 10, 2 );
	remove_filter( 'post_thumbnail_html', 'WP_ayvpp_thumbnail' );
	remove_filter( 'post_thumbnail_size', 'WP_ayvpp_thumbnail_size' );
}

?>