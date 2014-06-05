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

class Facebook_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Facebook';
	const service_name = 'Facebook';
	// Slug for the video provider
	public $service_slug = 'facebook';
	const service_slug = 'facebook';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#http://www\.facebook\.com/v/([0-9]+)#', // Flash Embed
		'#https?://www\.facebook\.com/video/embed\?video_id=([0-9]+)#' // iFrame Embed
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = 'https://graph.facebook.com/' . $id . '/picture?redirect=false';
		$response = wp_remote_get( $request, array( 'sslverify' => false ) );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$result = json_decode( $response['body'] );
			$result = $result->data->url;
			$high_res = str_replace( '_t.jpg', '_b.jpg', $result);
			if ( $high_res != $result ) {
				$response = wp_remote_head( $high_res );
				if ( !is_wp_error( $response ) && $response['response']['code'] == '200' ) {
					$result = $high_res;
				}
			}
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<object width=420 height=180><param name=allowfullscreen value=true></param><param name=allowscriptaccess value=always></param><param name=movie value="http://www.facebook.com/v/2560032632599"></param><embed src="http://www.facebook.com/v/2560032632599" type="application/x-shockwave-flash" allowscriptaccess=always allowfullscreen=true width=420 height=180></embed></object>',
				'expected'      => 'https://graph.facebook.com/2560032632599/picture',
				'expected_hash' => 'fa4a6b4b7a0f056a7558dc9ccacb34c3',
				'name'          => __( 'Flash Embed', 'video-thumbnails' )
			),
			array(
				'markup'        => '<iframe src="https://www.facebook.com/video/embed?video_id=2560032632599" width="960" height="720" frameborder="0"></iframe>',
				'expected'      => 'https://graph.facebook.com/2560032632599/picture',
				'expected_hash' => 'fa4a6b4b7a0f056a7558dc9ccacb34c3',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}

}

?>