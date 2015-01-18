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

class Twitch_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Twitch';
	const service_name = 'Twitch';
	// Slug for the video provider
	public $service_slug = 'twitch';
	const service_slug = 'twitch';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#(?:www\.)?twitch\.tv/(?:[A-Za-z0-9_]+)/c/([0-9]+)#', // Video URL
		'#<object[^>]+>.+?http://www\.twitch\.tv/widgets/archive_embed_player\.swf.+?chapter_id=([0-9]+).+?</object>#s', // Flash embed
		'#<object[^>]+>.+?http://www\.twitch\.tv/swflibs/TwitchPlayer\.swf.+?videoId=c([0-9]+).+?</object>#s', // Newer Flash embed
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$request = "https://api.twitch.tv/kraken/videos/c$id";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$result = json_decode( $response['body'] );
			$result = $result->preview;
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => 'http://www.twitch.tv/jodenstone/c/5793313',
				'expected'      => 'http://static-cdn.jtvnw.net/jtv.thumbs/archive-605904705-320x240.jpg',
				'expected_hash' => '1b2c51fc7380c74d1b2d34751d73e4cb',
				'name'          => __( 'Video URL', 'video-thumbnails' )
			),
			array(
				'markup'        => '<object bgcolor="#000000" data="http://www.twitch.tv/swflibs/TwitchPlayer.swf" height="378" id="clip_embed_player_flash" type="application/x-shockwave-flash" width="620"><param name="movie" value="http://www.twitch.tv/swflibs/TwitchPlayer.swf" /><param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" /><param name="allowFullScreen" value="true" /><param name="flashvars" value="channel=jodenstone&auto_play=false&start_volume=25&videoId=c5793313&device_id=bbe9fbac133ab340" /></object>',
				'expected'      => 'http://static-cdn.jtvnw.net/jtv.thumbs/archive-605904705-320x240.jpg',
				'expected_hash' => '1b2c51fc7380c74d1b2d34751d73e4cb',
				'name'          => __( 'Flash Embed', 'video-thumbnails' )
			),
		);
	}

}

?>