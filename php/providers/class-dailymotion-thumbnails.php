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

class Dailymotion_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Dailymotion';
	const service_name = 'Dailymotion';
	// Slug for the video provider
	public $service_slug = 'dailymotion';
	const service_slug = 'dailymotion';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#<object[^>]+>.+?http://www\.dailymotion\.com/swf/video/([A-Za-z0-9]+).+?</object>#s', // Dailymotion flash
		'#//www\.dailymotion\.com/embed/video/([A-Za-z0-9]+)#', // Dailymotion iframe
		'#(?:https?://)?(?:www\.)?dailymotion\.com/video/([A-Za-z0-9]+)#' // Dailymotion URL
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = "https://api.dailymotion.com/video/$id?fields=thumbnail_url";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$result = json_decode( $response['body'] );
			$result = $result->thumbnail_url;
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<iframe frameborder="0" width="480" height="270" src="http://www.dailymotion.com/embed/video/xqlhts"></iframe><br /><a href="http://www.dailymotion.com/video/xqlhts_adam-yauch-of-the-beastie-boys-dies-at-47_people" target="_blank">Adam Yauch of the Beastie Boys Dies at 47</a> <i>by <a href="http://www.dailymotion.com/associatedpress" target="_blank">associatedpress</a></i>',
				'expected'      => 'http://s1.dmcdn.net/AMjdy.jpg',
				'expected_hash' => '077888b97839254892a377f51c06e642',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
			array(
				'markup'        => '<object width="480" height="270"><param name="movie" value="http://www.dailymotion.com/swf/video/xqlhts"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><param name="wmode" value="transparent"></param><embed type="application/x-shockwave-flash" src="http://www.dailymotion.com/swf/video/xqlhts" width="480" height="270" wmode="transparent" allowfullscreen="true" allowscriptaccess="always"></embed></object><br /><a href="http://www.dailymotion.com/video/xqlhts_adam-yauch-of-the-beastie-boys-dies-at-47_people" target="_blank">Adam Yauch of the Beastie Boys Dies at 47</a> <i>by <a href="http://www.dailymotion.com/associatedpress" target="_blank">associatedpress</a></i>',
				'expected'      => 'http://s1.dmcdn.net/AMjdy.jpg',
				'expected_hash' => '077888b97839254892a377f51c06e642',
				'name'          => __( 'Flash Embed', 'video-thumbnails' )
			),
		);
	}

}

?>