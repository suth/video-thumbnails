<?php

class Metacafe_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'metacafe';
    public $name = 'Metacafe';

    public $regexes = array(
		'#http://www\.metacafe\.com/fplayer/([A-Za-z0-9\-_]+)/#' // Metacafe embed
    );

	public function get_thumbnail_url( $id ) {
		$request = "http://www.metacafe.com/api/item/$id/";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$xml = new SimpleXMLElement( $response['body'] );
			$result = $xml->xpath( "/rss/channel/item/media:thumbnail/@url" );
			$result = (string) $result[0]['url'];
			$result = $this->drop_url_parameters( $result );
		}
		return $result;
	}

    public static function get_tests() {
		return array(
            array(
				'markup'     => '<embed flashVars="playerVars=autoPlay=no" src="http://www.metacafe.com/fplayer/8456223/men_in_black_3_trailer_2.swf" width="440" height="248" wmode="transparent" allowFullScreen="true" allowScriptAccess="always" name="Metacafe_8456223" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>',
                'expected'   => '8456223',
				'image_url'  => 'http://s4.mcstatic.com/thumb/8456223/22479418/4/catalog_item5/0/1/men_in_black_3_trailer_2.jpg',
				'image_hash' => '977187bfb00df55b39724d7de284f617',
				'name'       => __( 'Flash Embed', 'video-thumbnails' )
			),
		);
	}
}
