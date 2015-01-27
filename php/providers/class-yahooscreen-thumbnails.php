<?php

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

// Require thumbnail provider class
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-video-thumbnails-provider.php' );

class Yahooscreen_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Yahoo Screen';
	const service_name = 'Yahoo Screen';
	// Slug for the video provider
	public $service_slug = 'yahooscreen';
	const service_slug = 'yahooscreen';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#(\/\/screen\.yahoo\.com\/[^.]+\.html)#' // iFrame SRC
	);

	// Thumbnail URL
	public function get_thumbnail_url( $url ) {
		$request = "http://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20html%20WHERE%20url%3D%22" . urlencode( 'http:' . $url ) . "%22%20AND%20xpath%3D%22%2F%2Fmeta%5B%40property%3D'og%3Aimage'%5D%22%20and%20compat%3D%22html5%22&format=json&callback=";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$json = json_decode( $response['body'] );
			if ( empty( $json->query->results ) ) {
				$result = new WP_Error( 'yahooscreen_invalid_url', sprintf( __( 'Error retrieving video information for <a href="http:%1$s">http:%1$s</a>. Check to be sure this is a valid Yahoo Screen URL.', 'video-thumbnails' ), $url ) );
			} else {
				$result = $json->query->results->meta->content;
				$result_array = explode( 'http://', $result );
				if ( count( $result_array ) > 1 ) {
					$result = 'http://' . $result_array[count( $result_array )-1];
				}
			}
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<iframe width="640" height="360" scrolling="no" frameborder="0" src="https://screen.yahoo.com/first-u-bitcoin-exchange-opens-140857495.html?format=embed" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true" allowtransparency="true"></iframe>',
				'expected'      => 'http://media.zenfs.com/en-US/video/video.abcnewsplus.com/7c70071008e3711818517f19b6ad9629',
				'expected_hash' => '22c2b172b297cf09511d832ddab7b9f5',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}

}

?>