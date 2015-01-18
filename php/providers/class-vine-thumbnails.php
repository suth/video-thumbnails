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

class Vine_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Vine';
	const service_name = 'Vine';
	// Slug for the video provider
	public $service_slug = 'vine';
	const service_slug = 'vine';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#(?:www\.)?vine\.co/v/([A-Za-z0-9_]+)#', // URL
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = "https://vine.co/v/$id";
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
					$result = $meta->getAttribute( 'content' );
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
				'markup'        => '<iframe class="vine-embed" src="https://vine.co/v/bpj7Km0T3d5/embed/simple" width="600" height="600" frameborder="0"></iframe><script async src="//platform.vine.co/static/scripts/embed.js" charset="utf-8"></script>',
				'expected'      => 'https://v.cdn.vine.co/v/thumbs/D6DDE013-F8DA-4929-9BED-49568F424343-184-00000008A20C1AEC_1.0.6.mp4.jpg',
				'expected_hash' => '7cca5921108abe15b8c1c1f884a5b3ac',
				'name'          => __( 'iFrame Embed/Video URL', 'video-thumbnails' )
			),
		);
	}

}

?>