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

function video_thumbnails_wpr_after_post_action( $post_id ) {
	// Don't save video thumbnails during autosave or for unpublished posts
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return null;
	if ( get_post_status( $post_id ) != 'publish' ) return null;
	// Check that Video Thumbnails are enabled for current post type
	$post_type = get_post_type( $post_id );
	global $video_thumbnails;
	if ( in_array( $post_type, (array) $video_thumbnails->settings->options['post_types'] ) || $post_type == $video_thumbnails->settings->options['post_types'] ) {
		$video_thumbnails->get_video_thumbnail( $post_id );
	} else {
		return null;
	}
}

add_action( 'wpr_after_post', 'video_thumbnails_wpr_after_post_action', 10, 1 );

?>