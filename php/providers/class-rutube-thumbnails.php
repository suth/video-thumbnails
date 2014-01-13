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

// Require thumbnail provider class
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-video-thumbnails-providers.php' );

class Rutube_Thumbnails extends Video_Thumbnails_Providers {

	// Human-readable name of the video provider
	public $service_name = 'Rutube';
	const service_name = 'Rutube';
	// Slug for the video provider
	public $service_slug = 'rutube';
	const service_slug = 'rutube';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#(?:https?://)?(?:www\.)?rutube\.ru/video/([A-Za-z0-9]+)#', // Video link
		'#(?:https?:)?//rutube\.ru/video/embed/([0-9]+)#', // Embed src
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = "http://rutube.ru/api/video/$id/?format=json";
		$response = wp_remote_get( $request, array( 'sslverify' => false ) );
		if( is_wp_error( $response ) ) {
			$result = new WP_Error( 'rutube_info_retrieval', __( 'Error retrieving video information from the URL <a href="' . $request . '">' . $request . '</a> using <code>wp_remote_get()</code><br />If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.<br />Details: ' . $response->get_error_message() ) );
		} else {
			$result = json_decode( $response['body'] );
			$result = $result->thumbnail_url;
		}
		return $result;
	}

	// Test cases
	public $test_cases = array(
		array(
			'markup'        => 'http://rutube.ru/video/ca8607cd4f7ef28516e043dde0068564/',
			'expected'      => 'http://pic.rutube.ru/video/3a/c8/3ac8c1ded16501002d20fa3ba3ed3d61.jpg',
			'expected_hash' => '85ad79c118ee82c7c2a756ba29a96354',
			'name'          => 'Video link'
		),
		array(
			'markup'        => '<iframe width="720" height="405" src="//rutube.ru/video/embed/6608735" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>',
			'expected'      => 'http://pic.rutube.ru/video/3a/c8/3ac8c1ded16501002d20fa3ba3ed3d61.jpg',
			'expected_hash' => '85ad79c118ee82c7c2a756ba29a96354',
			'name'          => 'iFrame embed'
		),
	);

}

// Add to provider array
add_filter( 'video_thumbnail_providers', array( 'Rutube_Thumbnails', 'register_provider' ) );

?>