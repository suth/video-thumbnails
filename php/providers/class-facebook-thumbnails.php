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
        '#(?://|\%2F\%2F)(?:www\.)?facebook\.com(?:/|\%2F)(?:[a-zA-Z0-9]+)(?:/|\%2F)videos(?:/|\%2F)([0-9]+)#', // URL Embed
		'#http://www\.facebook\.com/v/([0-9]+)#', // Flash Embed
		'#https?://www\.facebook\.com/video/embed\?video_id=([0-9]+)#', // iFrame Embed
		'#https?://www\.facebook\.com/video\.php\?v=([0-9]+)#'
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = 'https://graph.facebook.com/' . $id . '/picture?redirect=false';
		$response = wp_remote_get( $request );
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
				'expected'      => 'https://fbcdn-vthumb-a.akamaihd.net/hvthumb-ak-xap1/v/t15.0-10/p160x160/50796_2560034672650_2560032632599_65313_313_b.jpg?oh=e8c767b1efafa6d8a4b672bad7be38d6&oe=55364081&__gda__=1428807476_a4d83140019b11ad602f2ef9960a364e',
				'expected_hash' => '6b033d8f16dbf273048c5771d32ede64',
				'name'          => __( 'Flash Embed', 'video-thumbnails' )
			),
			array(
				'markup'        => '<iframe src="https://www.facebook.com/video/embed?video_id=2560032632599" width="960" height="720" frameborder="0"></iframe>',
				'expected'      => 'https://fbcdn-vthumb-a.akamaihd.net/hvthumb-ak-xap1/v/t15.0-10/p160x160/50796_2560034672650_2560032632599_65313_313_b.jpg?oh=e8c767b1efafa6d8a4b672bad7be38d6&oe=55364081&__gda__=1428807476_a4d83140019b11ad602f2ef9960a364e',
				'expected_hash' => '6b033d8f16dbf273048c5771d32ede64',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
			array(
				'markup'        => '<div id="fb-root"></div> <script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/en_US/all.js#xfbml=1"; fjs.parentNode.insertBefore(js, fjs); }(document, \'script\', \'facebook-jssdk\'));</script><div class="fb-post" data-href="https://www.facebook.com/video.php?v=10150326323406807" data-width="466"><div class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/video.php?v=10150326323406807">Post</a> by <a href="https://www.facebook.com/PeterJacksonNZ">Peter Jackson</a>.</div></div>',
				'expected'      => 'https://fbcdn-vthumb-a.akamaihd.net/hvthumb-ak-xfa1/v/t15.0-10/p128x128/244423_10150326375786807_10150326323406807_4366_759_b.jpg?oh=013ce21bb54de51c383071598b269a91&oe=552CD270&__gda__=1428479462_339647870ec32227c391e98000935aec',
				'expected_hash' => '184d20db21ac8edef9c9cee291be5ee6',
				'name'          => __( 'FBML Embed', 'video-thumbnails' )
			),
		);
	}

}

?>