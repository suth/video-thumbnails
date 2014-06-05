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

class Justintv_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Justin.tv';
	const service_name = 'Justin.tv';
	// Slug for the video provider
	public $service_slug = 'justintv';
	const service_slug = 'justintv';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
	    '#//(?:www\.)?justin\.tv/swflibs/JustinPlayer\.swf\?channel=([a-zA-Z0-9_]+)#' // Channel player
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = "http://api.justin.tv/api/channel/show/$id.json";
		$response = wp_remote_get( $request, array( 'sslverify' => false ) );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$result = json_decode( $response['body'] );
			$result = $result->screen_cap_url_huge;
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<object type="application/x-shockwave-flash" data="http://www.justin.tv/swflibs/JustinPlayer.swf?channel=twit" id="live_embed_player_flash" height="300" width="400" bgcolor="#000000"><param name="allowFullScreen" value="true"/><param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" /><param name="movie" value="http://www.justin.tv/swflibs/JustinPlayer.swf" /><param name="flashvars" value="hostname=www.justin.tv&channel=twit&auto_play=false&start_volume=25" /></object>',
				'expected'      => 'http://static-cdn.jtvnw.net/previews/live_user_twit-630x473.jpg',
				'expected_hash' => 'b8c0dd6565f34e6bfbbddbb07ff0df74',
				'name'          => __( 'Flash Embed', 'video-thumbnails' )
			),
		);
	}

}

?>