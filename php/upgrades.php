<?php

/*  Copyright 2013 Sutherland Boswell  (email : sutherland.boswell@gmail.com)

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

function upgrade_video_thumbnails_options( $options ) {

	// If consolidated options don't exist, assume version 1.8.2
	if ( ! $options ) $options['version'] = '1.8.2';

	// Boolean for if options need updating
	$options_need_updating = false;

	// Upgrade our options if we need to
	if ( version_compare( $options['version'], VIDEO_THUMBNAILS_VERSION, '<' ) ) {

		// Upgrade to 2.0
		if ( version_compare( $options['version'], '2.0', '<' ) ) {

			$options['save_media'] = get_option( 'video_thumbnails_save_media' );
			delete_option( 'video_thumbnails_save_media' );

			$options['set_featured'] = get_option( 'video_thumbnails_set_featured' );
			delete_option( 'video_thumbnails_set_featured' );

			$options['custom_field'] = get_option( 'video_thumbnails_custom_field' );
			delete_option( 'video_thumbnails_custom_field' );

			$options['post_types'] = get_option( 'video_thumbnails_post_types' );
			delete_option( 'video_thumbnails_post_types' );

			$options_need_updating = true;
			$options['version'] = '2.0';

		}

	}

	// Save options to database if they've been updated
	if ( $options_need_updating ) {
		update_option( 'video_thumbnails', $options );
	}

	return $options;

}

?>