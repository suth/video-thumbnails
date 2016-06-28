<?php

class Vine_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'vine';
    public $name = 'Vine';

    public $regexes = array(
        '#(?:www\.)?vine\.co/v/([A-Za-z0-9_]+)#', // URL
    );

	public function get_thumbnail_url( $id ) {
        $request = "https://vine.co/v/$id";
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
					$result = $meta->getAttribute( 'content' );
					break;
				}
			}
		}
		return $result;
	}

    public static function get_tests() {
		return array(
            array(
				'markup'     => '<iframe class="vine-embed" src="https://vine.co/v/bpj7Km0T3d5/embed/simple" width="600" height="600" frameborder="0"></iframe><script async src="//platform.vine.co/static/scripts/embed.js" charset="utf-8"></script>',
                'expected'   => 'bpj7Km0T3d5',
				'image_url'  => 'https://v.cdn.vine.co/v/thumbs/D6DDE013-F8DA-4929-9BED-49568F424343-184-00000008A20C1AEC_1.0.6.mp4.jpg',
				'image_hash' => '7cca5921108abe15b8c1c1f884a5b3ac',
				'name'       => __( 'iFrame Embed/Video URL', 'video-thumbnails' )
			),
		);
	}
}
