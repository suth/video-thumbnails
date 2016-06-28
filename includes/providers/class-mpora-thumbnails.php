<?php

class Mpora_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'mpora';
    public $name = 'MPORA';

    public $regexes = array(
        '#http://(?:video\.|www\.)?mpora\.com/(?:ep|videos)/([A-Za-z0-9]+)#', // Flash or iFrame src
		'#mporaplayer_([A-Za-z0-9]+)_#' // Object ID
    );

	public function get_thumbnail_url( $id ) {
		return 'http://ugc4.mporatrons.com/thumbs/' . $id . '_640x360_0000.jpg';
	}

    public static function get_tests() {
		return array(
            array(
				'markup'     => '<object width="480" height="270" id="mporaplayer_wEr2CBooV_N" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" type="application/x-shockwave-flash" ><param name="movie" value="http://video.mpora.com/ep/wEr2CBooV/"></param><param name="wmode" value="transparent"></param><param name="allowScriptAccess" value="always"></param><param name="allowFullScreen" value="true"></param><embed src="http://video.mpora.com/ep/wEr2CBooV/" width="480" height="270" wmode="transparent" allowfullscreen="true" allowscriptaccess="always" type="application/x-shockwave-flash"></embed></object>',
                'expected'   => 'wEr2CBooV',
				'image_url'  => 'http://ugc4.mporatrons.com/thumbs/wEr2CBooV_640x360_0000.jpg',
				'image_hash' => '95075bd4941251ebecbab3b436a90c49',
				'name'       => __( 'Flash Embed', 'video-thumbnails' )
			),
			array(
				'markup'     => '<iframe width="640" height="360" src="http://mpora.com/videos/AAdfegovdop0/embed" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
                'expected'   => 'AAdfegovdop0',
				'image_url'  => 'http://ugc4.mporatrons.com/thumbs/AAdfegovdop0_640x360_0000.jpg',
				'image_hash' => '45db22a2ba5ef20163f52ba562b89259',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}
}
