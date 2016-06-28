<?php

class Sapo_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'sapo';
    public $name = 'SAPO';

    public $regexes = array(
		'#videos\.sapo\.pt/([A-Za-z0-9]+)/mov#', // iFrame SRC
    );

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

    public static function get_tests() {
		return array(
            array(
				'markup'     => '<iframe src="http://rd3.videos.sapo.pt/playhtml?file=http://rd3.videos.sapo.pt/ddACsFSuDEZZRWfNHTTy/mov/1" frameborder="0" scrolling="no" width="640" height="360" webkitallowfullscreen mozallowfullscreen allowfullscreen ></iframe>',
                'expected'   => 'ddACsFSuDEZZRWfNHTTy',
				'image_url'  => 'http://cache02.stormap.sapo.pt/vidstore14/thumbnais/e9/08/37/7038489_l5VMt.jpg',
				'image_hash' => 'd8a74c3d4e054263a37abe9ceed782fd',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}
}
