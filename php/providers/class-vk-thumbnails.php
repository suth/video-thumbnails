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

class VK_Thumbnails extends Video_Thumbnails_Providers {

	// Human-readable name of the video provider
	public $service_name = 'VK';
	const service_name = 'VK';
	// Slug for the video provider
	public $service_slug = 'vk';
	const service_slug = 'vk';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#(//(?:www\.)?vk\.com/video_ext\.php\?oid=\-?[0-9]+&id=\-?[0-9]+&hash=[0-9a-zA-Z]+)#', // URL
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = "http:$id";
		$response = wp_remote_get( $request, array( 'sslverify' => false ) );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$doc = new DOMDocument();
			@$doc->loadHTML( $response['body'] );
			$metas = $doc->getElementsByTagName( 'img' );
			for ( $i = 0; $i < $metas->length; $i++ ) {
				$meta = $metas->item( $i );
				if ( $meta->getAttribute( 'id' ) == 'player_thumb' ) {
					$result = $meta->getAttribute( 'src' );
					break;
				}
			}
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<iframe src="http://vk.com/video_ext.php?oid=157000410&id=164106383&hash=0fdb5f49218be7c2&hd=1" width="607" height="360" frameborder="0"></iframe>',
				'expected'      => 'http://cs513416.vk.me/u157000410/video/l_73b292cc.jpg',
				'expected_hash' => '6d4b086ff1a55c9b48f56bc7848e6c84',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}

}

// Add to provider array
add_filter( 'video_thumbnail_providers', array( 'VK_Thumbnails', 'register_provider' ) );

?>