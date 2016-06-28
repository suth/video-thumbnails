<?php

class Vk_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'vk';
    public $name = 'VK';

    public $regexes = array(
		'#(//(?:www\.)?vk\.com/video_ext\.php\?oid=\-?[0-9]+(?:&|&\#038;|&amp;)id=\-?[0-9]+(?:&|&\#038;|&amp;)hash=[0-9a-zA-Z]+)#', // URL
    );

	public function get_thumbnail_url( $id ) {
        $request = "http:$id";
		$request = html_entity_decode( $request );
		$response = wp_remote_get( $request );
		$result = false;
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
            preg_match( '#"thumb":"([^"]+)"#', $response['body'], $matches );
            $result = stripslashes( $matches[1] );
		}
		return $result;
	}

    public static function get_tests() {
		return array(
            array(
				'markup'     => '<iframe src="http://vk.com/video_ext.php?oid=220943440&id=168591360&hash=75a37bd3930f4fab&hd=1" width="607" height="360" frameborder="0"></iframe>',
                'expected'   => '//vk.com/video_ext.php?oid=220943440&id=168591360&hash=75a37bd3930f4fab',
				'image_url'  => 'http://cs540302.vk.me/u220943440/video/l_afc9770f.jpg',
				'image_hash' => 'fd8c2af4ad5cd4e55afe129d80b42d8b',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}
}
