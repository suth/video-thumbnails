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
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-video-thumbnails-provider.php' );

class Blip_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Blip';
	const service_name = 'Blip';
	// Slug for the video provider
	public $service_slug = 'blip';
	const service_slug = 'blip';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#(https?\:\/\/blip\.tv\/[^\r\n\'\"]+)#' // Blip URL
	);

	// Thumbnail URL
	public function get_thumbnail_url( $url ) {
		$request = "http://blip.tv/oembed?url=$url";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$json = json_decode( $response['body'] );
			if ( isset( $json->error ) ) {
				$result = new WP_Error( 'blip_invalid_url', sprintf( __( 'Error retrieving video information for <a href="%1$s">%1$s</a>. Check to be sure this is a valid Blip video URL.', 'video-thumbnails' ), $url ) );
			} else {
				$result = $json->thumbnail_url;
			}
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => 'http://blip.tv/cranetv/illustrator-katie-scott-6617917',
				'expected'      => 'http://a.images.blip.tv/CraneTV-IllustratorKatieScott610.jpg',
				'expected_hash' => '26a622f72bd4bdb3f8189f85598dd95d',
				'name'          => __( 'Video URL', 'video-thumbnails' )
			),
			array(
				'markup'        => '<iframe src="http://blip.tv/play/AYLz%2BEsC.html?p=1" width="780" height="438" frameborder="0" allowfullscreen></iframe><embed type="application/x-shockwave-flash" src="http://a.blip.tv/api.swf#AYLz+EsC" style="display:none"></embed>',
				'expected'      => 'http://a.images.blip.tv/GeekCrashCourse-TheAvengersMarvelMovieCatchUpGeekCrashCourse331.png',
				'expected_hash' => '87efa9f6b0d9111b0826ae4fbdddec1b',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}

}

?>