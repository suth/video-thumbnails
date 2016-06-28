<?php

class Twitch_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'twitch';
    public $name = 'Twitch';

    public $regexes = array(
		'#(?:www\.)?twitch\.tv/(?:[A-Za-z0-9_]+)/c/([0-9]+)#', // Video URL
		'#<object[^>]+>.+?http://www\.twitch\.tv/widgets/archive_embed_player\.swf.+?chapter_id=([0-9]+).+?</object>#s', // Flash embed
		'#<object[^>]+>.+?http://www\.twitch\.tv/swflibs/TwitchPlayer\.swf.+?videoId=c([0-9]+).+?</object>#s', // Newer Flash embed
    );

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

    public static function get_tests() {
		return array(
            array(
				'markup'     => 'http://www.twitch.tv/jodenstone/c/5793313',
                'expected'   => '5793313',
				'image_url'  => 'http://static-cdn.jtvnw.net/jtv.thumbs/archive-605904705-320x240.jpg',
				'image_hash' => '1b2c51fc7380c74d1b2d34751d73e4cb',
				'name'       => __( 'Video URL', 'video-thumbnails' )
			),
			array(
				'markup'     => '<object bgcolor="#000000" data="http://www.twitch.tv/swflibs/TwitchPlayer.swf" height="378" id="clip_embed_player_flash" type="application/x-shockwave-flash" width="620"><param name="movie" value="http://www.twitch.tv/swflibs/TwitchPlayer.swf" /><param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" /><param name="allowFullScreen" value="true" /><param name="flashvars" value="channel=jodenstone&auto_play=false&start_volume=25&videoId=c5793313&device_id=bbe9fbac133ab340" /></object>',
                'expected'   => '5793313',
				'image_url'  => 'http://static-cdn.jtvnw.net/jtv.thumbs/archive-605904705-320x240.jpg',
				'image_hash' => '1b2c51fc7380c74d1b2d34751d73e4cb',
				'name'       => __( 'Flash Embed', 'video-thumbnails' )
			),
		);
	}
}
