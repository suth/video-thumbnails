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

class Kaltura_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Kaltura';
	const service_name = 'Kaltura';
	// Slug for the video provider
	public $service_slug = 'kaltura';
	const service_slug = 'kaltura';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
	    '#http://cdnapi\.kaltura\.com/p/[0-9]+/sp/[0-9]+/embedIframeJs/uiconf_id/[0-9]+/partner_id/[0-9]+\?entry_id=([a-z0-9_]+)#' // Hosted
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = "http://www.kaltura.com/api_v3/?service=thumbAsset&action=getbyentryid&entryId=$id";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$xml = new SimpleXMLElement( $response['body'] );
			$result = (string) $xml->result->item->id;
			$request = "http://www.kaltura.com/api_v3/?service=thumbAsset&action=geturl&id=$result";
			$response = wp_remote_get( $request );
			if( is_wp_error( $response ) ) {
				$result = $this->construct_info_retrieval_error( $request, $response );
			} else {
				$xml = new SimpleXMLElement( $response['body'] );
				$result = (string) $xml->result;
			}
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'   => '<script type="text/javascript" src="http://cdnapi.kaltura.com/p/1374841/sp/137484100/embedIframeJs/uiconf_id/12680902/partner_id/1374841?entry_id=1_y7xzqsxw&playerId=kaltura_player_1363589321&cache_st=1363589321&autoembed=true&width=400&height=333&"></script>',
				'expected' => 'http://example.com/thumbnail.jpg',
				'name'     => __( 'Auto Embed', 'video-thumbnails' )
			),
		);
	}

}

?>