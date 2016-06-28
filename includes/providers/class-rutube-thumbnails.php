<?php

class Rutube_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'rutube';
    public $name = 'Rutube';

    public $regexes = array(
		'#(?:https?://)?(?:www\.)?rutube\.ru/video/(?:embed/)?([A-Za-z0-9]+)#', // Video link/Embed src
    );

	public function get_thumbnail_url( $id ) {
        if ( strlen( $id ) < 32 ) {
			$request = "http://rutube.ru/api/oembed/?url=http%3A//rutube.ru/tracks/$id.html&format=json";
		} else {
			$request = "http://rutube.ru/api/video/$id/?format=json";
		}
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
				'markup'     => 'http://rutube.ru/video/ca8607cd4f7ef28516e043dde0068564/',
                'expected'   => 'ca8607cd4f7ef28516e043dde0068564',
				'image_url'  => 'http://pic.rutube.ru/video/3a/c8/3ac8c1ded16501002d20fa3ba3ed3d61.jpg',
				'image_hash' => '85ad79c118ee82c7c2a756ba29a96354',
				'name'       => __( 'Video URL', 'video-thumbnails' )
			),
			array(
				'markup'     => '<iframe width="720" height="405" src="//rutube.ru/video/embed/6608735" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>',
                'expected'   => '6608735',
				'image_url'  => 'http://pic.rutube.ru/video/3a/c8/3ac8c1ded16501002d20fa3ba3ed3d61.jpg',
				'image_hash' => '85ad79c118ee82c7c2a756ba29a96354',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}
}
