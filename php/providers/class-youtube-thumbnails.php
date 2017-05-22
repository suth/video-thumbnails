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

class Youtube_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'YouTube';
	const service_name = 'YouTube';
	// Slug for the video provider
	public $service_slug = 'youtube';
	const service_slug = 'youtube';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#(?:https?:)?//www\.youtube(?:\-nocookie)?\.com/(?:v|e|embed)/([A-Za-z0-9\-_]+)#', // Comprehensive search for both iFrame and old school embeds
		'#(?:https?(?:a|vh?)?://)?(?:www\.)?youtube(?:\-nocookie)?\.com/watch\?.*v=([A-Za-z0-9\-_]+)#', // Any YouTube URL. After http(s) support a or v for Youtube Lyte and v or vh for Smart Youtube plugin
		'#(?:https?(?:a|vh?)?://)?youtu\.be/([A-Za-z0-9\-_]+)#', // Any shortened youtu.be URL. After http(s) a or v for Youtube Lyte and v or vh for Smart Youtube plugin
		'#<div class="lyte" id="([A-Za-z0-9\-_]+)"#', // YouTube Lyte
		'#data-youtube-id="([A-Za-z0-9\-_]+)"#' // LazyYT.js
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$maxres = 'http://img.youtube.com/vi/' . $id . '/maxresdefault.jpg';
		$response = wp_remote_head( $maxres );
		if ( !is_wp_error( $response ) && $response['response']['code'] == '200' ) {
			$result = $maxres;
		} else {
			$result = 'http://img.youtube.com/vi/' . $id . '/0.jpg';
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<iframe width="560" height="315" src="http://www.youtube.com/embed/Fp0U2Vglkjw" frameborder="0" allowfullscreen></iframe>',
				'expected'      => 'http://img.youtube.com/vi/Fp0U2Vglkjw/maxresdefault.jpg',
				'expected_hash' => 'c66256332969c38790c2b9f26f725e7a',
				'name'          => esc_html__( 'iFrame Embed HD', 'video-thumbnails' )
			),
			array(
				'markup'        => '<object width="560" height="315"><param name="movie" value="http://www.youtube.com/v/Fp0U2Vglkjw?version=3&amp;hl=en_US"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/Fp0U2Vglkjw?version=3&amp;hl=en_US" type="application/x-shockwave-flash" width="560" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>',
				'expected'      => 'http://img.youtube.com/vi/Fp0U2Vglkjw/maxresdefault.jpg',
				'expected_hash' => 'c66256332969c38790c2b9f26f725e7a',
				'name'          => esc_html__( 'Flash Embed HD', 'video-thumbnails' )
			),
			array(
				'markup'        => '<iframe width="560" height="315" src="http://www.youtube.com/embed/vv_AitYPjtc" frameborder="0" allowfullscreen></iframe>',
				'expected'      => 'http://img.youtube.com/vi/vv_AitYPjtc/0.jpg',
				'expected_hash' => '6c00b9ab335a6ea00b0fb964c39a6dc9',
				'name'          => esc_html__( 'iFrame Embed SD', 'video-thumbnails' )
			),
			array(
				'markup'        => '<object width="560" height="315"><param name="movie" value="http://www.youtube.com/v/vv_AitYPjtc?version=3&amp;hl=en_US"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/vv_AitYPjtc?version=3&amp;hl=en_US" type="application/x-shockwave-flash" width="560" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>',
				'expected'      => 'http://img.youtube.com/vi/vv_AitYPjtc/0.jpg',
				'expected_hash' => '6c00b9ab335a6ea00b0fb964c39a6dc9',
				'name'          => esc_html__( 'Flash Embed SD', 'video-thumbnails' )
			),
		);
	}

}
