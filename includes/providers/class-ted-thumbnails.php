<?php

class Ted_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'ted';
    public $name = 'TED';

    public $regexes = array(
		'#//embed(?:\-ssl)?\.ted\.com/talks/(?:lang/[A-Za-z_-]+/)?([A-Za-z0-9_-]+)\.html#', // iFrame SRC
    );

	public function get_thumbnail_url( $id ) {
        $request = "http://www.ted.com/talks/oembed.json?url=http%3A%2F%2Fwww.ted.com%2Ftalks%2F$id";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$result = json_decode( $response['body'] );
			$result = str_replace( '240x180.jpg', '480x360.jpg', $result->thumbnail_url );
		}
		return $result;        
	}

    public static function get_tests() {
		return array(
            array(
				'markup'     => '<iframe src="http://embed.ted.com/talks/kitra_cahana_stories_of_the_homeless_and_hidden.html" width="640" height="360" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
                'expected'   => 'kitra_cahana_stories_of_the_homeless_and_hidden',
				'image_url'  => 'http://images.ted.com/images/ted/341053090f8bac8c324c75be3114b673b4355e8a_480x360.jpg?lang=en',
				'image_hash' => 'f2a5f6af49e841b4f9c7b95d6ca0372a',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
			array(
				'markup'     => '<iframe src="https://embed-ssl.ted.com/talks/lang/fr-ca/shimpei_takahashi_play_this_game_to_come_up_with_original_ideas.html" width="640" height="360" frameborder="0" scrolling="no" allowfullscreen="allowfullscreen"></iframe>',
                'expected'   => 'shimpei_takahashi_play_this_game_to_come_up_with_original_ideas',
				'image_url'  => 'http://images.ted.com/images/ted/b1f1183311cda4df9e1b65f2b363e0b806bff914_480x360.jpg?lang=en',
				'image_hash' => 'ff47c99c9eb95e3d6c4b986b18991f22',
				'name'       => __( 'Custom Language', 'video-thumbnails' )
			),
		);
	}
}
