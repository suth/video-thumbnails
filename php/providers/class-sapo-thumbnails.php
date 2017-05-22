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

class Sapo_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'SAPO';
	const service_name = 'SAPO';
	// Slug for the video provider
	public $service_slug = 'sapo';
	const service_slug = 'sapo';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#videos\.sapo\.pt/([A-Za-z0-9]+)/mov#', // iFrame SRC
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = "http://videos.sapo.pt/$id";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$doc = new DOMDocument();
			@$doc->loadHTML( $response['body'] );
			$metas = $doc->getElementsByTagName( 'meta' );
			for ( $i = 0; $i < $metas->length; $i++ ) {
				$meta = $metas->item( $i );
				if ( $meta->getAttribute( 'property' ) == 'og:image' ) {
					$og_image = $meta->getAttribute( 'content' );
					parse_str( parse_url( $og_image, PHP_URL_QUERY ), $image_array );
					$result = $image_array['pic'];
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
				'markup'        => '<iframe src="http://rd3.videos.sapo.pt/playhtml?file=http://rd3.videos.sapo.pt/ddACsFSuDEZZRWfNHTTy/mov/1" frameborder="0" scrolling="no" width="640" height="360" webkitallowfullscreen mozallowfullscreen allowfullscreen ></iframe>',
				'expected'      => 'http://cache02.stormap.sapo.pt/vidstore14/thumbnais/e9/08/37/7038489_l5VMt.jpg',
				'expected_hash' => 'd8a74c3d4e054263a37abe9ceed782fd',
				'name'          => esc_html__( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}

}
