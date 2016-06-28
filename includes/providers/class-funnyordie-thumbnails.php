<?php

class FunnyOrDie_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'funnyordie';
    public $name = 'Funny or Die';

    public $regexes = array(
		'#http://www\.funnyordie\.com/embed/([A-Za-z0-9]+)#', // Iframe src
		'#id="ordie_player_([A-Za-z0-9]+)"#' // Flash object
    );

	public function get_thumbnail_url( $id ) {
		$request = "http://www.funnyordie.com/oembed.json?url=http%3A%2F%2Fwww.funnyordie.com%2Fvideos%2F$id";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$result = json_decode( $response['body'] );
			$result = $result->thumbnail_url;
		}
		return $result;
	}

    public static function get_tests() {
		return array(
			array(
				'markup'     => '<iframe src="http://www.funnyordie.com/embed/5325b03b52" width="640" height="400" frameborder="0"></iframe>',
                'expected'   => '5325b03b52',
				'image_url'  => 'http://t.fod4.com/t/5325b03b52/c480x270_17.jpg',
				'image_hash' => '5aafa4a5f27bd4aead574db38a9e8b2b',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
			array(
				'markup'     => '<object width="640" height="400" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" id="ordie_player_5325b03b52"><param name="movie" value="http://player.ordienetworks.com/flash/fodplayer.swf" /><param name="flashvars" value="key=5325b03b52" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always"><embed width="640" height="400" flashvars="key=5325b03b52" allowfullscreen="true" allowscriptaccess="always" quality="high" src="http://player.ordienetworks.com/flash/fodplayer.swf" name="ordie_player_5325b03b52" type="application/x-shockwave-flash"></embed></object>',
                'expected'   => '5325b03b52',
				'image_url'  => 'http://t.fod4.com/t/5325b03b52/c480x270_17.jpg',
				'image_hash' => '5aafa4a5f27bd4aead574db38a9e8b2b',
				'name'       => __( 'Flash Embed', 'video-thumbnails' )
			),
		);
	}
}
