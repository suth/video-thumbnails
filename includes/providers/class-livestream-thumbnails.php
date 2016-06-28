<?php

class Livestream_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'livestream';
    public $name = 'Livestream';

    public $regexes = array(
		'#\/\/cdn\.livestream\.com\/embed\/([A-Za-z0-9_]+)#', // Embed SRC
    );

	public function get_thumbnail_url( $id ) {
		$result = 'http://thumbnail.api.livestream.com/thumbnail?name=' . $id;
		return $result;
	}

    public static function get_tests() {
		return array(
			array(
				'markup'     => '<iframe width="560" height="340" src="http://cdn.livestream.com/embed/WFMZ_Traffic?layout=4&amp;height=340&amp;width=560&amp;autoplay=false" style="border:0;outline:0" frameborder="0" scrolling="no"></iframe>',
                'expected'   => 'WFMZ_Traffic',
				'image_url'  => 'http://thumbnail.api.livestream.com/thumbnail?name=WFMZ_Traffic',
				'image_hash' => '1be02799b2fab7a4749b2187f7687412',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}
}
