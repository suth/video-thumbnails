<?php

class Youku_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'youku';
    public $name = 'Youku';

    public $regexes = array(
        '#http://player\.youku\.com/embed/([A-Za-z0-9=]+)#', // iFrame
		'#http://player\.youku\.com/player\.php/sid/([A-Za-z0-9=]+)/v\.swf#', // Flash
		'#http://v\.youku\.com/v_show/id_([A-Za-z0-9=]+)\.html#' // Link
    );

	public function get_thumbnail_url( $id ) {
        $request = "http://v.youku.com/player/getPlayList/VideoIDS/$id/";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$result = json_decode( $response['body'] );
			$result = $result->data[0]->logo;
		}
		return $result;        
	}

    public static function get_tests() {
		return array(
            array(
				'markup'     => '<iframe height=498 width=510 src="http://player.youku.com/embed/XMzQyMzk5MzQ4" frameborder=0 allowfullscreen></iframe>',
                'expected'   => 'XMzQyMzk5MzQ4',
				'image_url'  => 'http://g1.ykimg.com/1100641F464F0FB57407E2053DFCBC802FBBC4-E4C5-7A58-0394-26C366F10493',
				'image_hash' => 'deac7bb89058a8c46ae2350da9d33ba8',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
			array(
				'markup'     => '<embed src="http://player.youku.com/player.php/sid/XMzQyMzk5MzQ4/v.swf" quality="high" width="480" height="400" align="middle" allowScriptAccess="sameDomain" allowFullscreen="true" type="application/x-shockwave-flash"></embed>',
                'expected'   => 'XMzQyMzk5MzQ4',
				'image_url'  => 'http://g1.ykimg.com/1100641F464F0FB57407E2053DFCBC802FBBC4-E4C5-7A58-0394-26C366F10493',
				'image_hash' => 'deac7bb89058a8c46ae2350da9d33ba8',
				'name'       => __( 'Flash Embed', 'video-thumbnails' )
			),
			array(
				'markup'     => 'http://v.youku.com/v_show/id_XMzQyMzk5MzQ4.html',
                'expected'   => 'XMzQyMzk5MzQ4',
				'image_url'  => 'http://g1.ykimg.com/1100641F464F0FB57407E2053DFCBC802FBBC4-E4C5-7A58-0394-26C366F10493',
				'image_hash' => 'deac7bb89058a8c46ae2350da9d33ba8',
				'name'       => __( 'Video URL', 'video-thumbnails' )
			),
		);
	}
}
