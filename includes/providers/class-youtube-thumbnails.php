<?php

class YouTube_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'youtube';
    public $name = 'YouTube';

	public $regexes = array(
		'#(?:https?:)?//www\.youtube(?:\-nocookie)?\.com/(?:v|e|embed)/([A-Za-z0-9\-_]+)#',
		'#(?:https?(?:a|vh?)?://)?(?:www\.)?youtube(?:\-nocookie)?\.com/watch\?.*v=([A-Za-z0-9\-_]+)#',
		'#(?:https?(?:a|vh?)?://)?youtu\.be/([A-Za-z0-9\-_]+)#',
		'#<div class="lyte" id="([A-Za-z0-9\-_]+)"#', // YouTube Lyte
		'#data-youtube-id="([A-Za-z0-9\-_]+)"#' // LazyYT.js
	);

	public function get_thumbnail_url( $id ) {
		$maxres = 'http://img.youtube.com/vi/' . $id . '/maxresdefault.jpg';
		$response = wp_remote_head( $maxres );
		if ( !is_wp_error( $response ) && $response['response']['code'] == '200' ) {
			$result = $maxres;
		} else {
			$result = 'http://img.youtube.com/vi/' . $id . '/0.jpg';
		}
		return $result;
	}

    /**
     * Define the settings for this provider
     *
     * @param Refactored_Settings_Section $settings
     * @return Refactored_Settings_Section
     */
    public function settings( $settings )
    {
        return $settings->description('This is a new section')
            ->addField(Refactored_Settings_Field_0_5_0::withKey('api')
                ->name('API Key')
                ->type('text')
            );
    }

    public static function get_tests() {
		return array(
			array(
				'markup'        => '<iframe width="560" height="315" src="http://www.youtube.com/embed/Fp0U2Vglkjw" frameborder="0" allowfullscreen></iframe>',
                'expected'   => 'Fp0U2Vglkjw',
				'image_url'      => 'http://img.youtube.com/vi/Fp0U2Vglkjw/maxresdefault.jpg',
				'image_hash' => 'c66256332969c38790c2b9f26f725e7a',
				'name'          => __( 'iFrame Embed HD', 'video-thumbnails' )
			),
			array(
				'markup'        => '<object width="560" height="315"><param name="movie" value="http://www.youtube.com/v/Fp0U2Vglkjw?version=3&amp;hl=en_US"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/Fp0U2Vglkjw?version=3&amp;hl=en_US" type="application/x-shockwave-flash" width="560" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>',
                'expected'   => 'Fp0U2Vglkjw',
				'image_url'      => 'http://img.youtube.com/vi/Fp0U2Vglkjw/maxresdefault.jpg',
				'image_hash' => 'c66256332969c38790c2b9f26f725e7a',
				'name'          => __( 'Flash Embed HD', 'video-thumbnails' )
			),
			array(
				'markup'        => '<iframe width="560" height="315" src="http://www.youtube.com/embed/vv_AitYPjtc" frameborder="0" allowfullscreen></iframe>',
                'expected'   => 'vv_AitYPjtc',
				'image_url'      => 'http://img.youtube.com/vi/vv_AitYPjtc/0.jpg',
				'image_hash' => '6c00b9ab335a6ea00b0fb964c39a6dc9',
				'name'          => __( 'iFrame Embed SD', 'video-thumbnails' )
			),
			array(
				'markup'        => '<object width="560" height="315"><param name="movie" value="http://www.youtube.com/v/vv_AitYPjtc?version=3&amp;hl=en_US"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/vv_AitYPjtc?version=3&amp;hl=en_US" type="application/x-shockwave-flash" width="560" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>',
                'expected'   => 'vv_AitYPjtc',
				'image_url'      => 'http://img.youtube.com/vi/vv_AitYPjtc/0.jpg',
				'image_hash' => '6c00b9ab335a6ea00b0fb964c39a6dc9',
				'name'          => __( 'Flash Embed SD', 'video-thumbnails' )
			),
		);
	}
}
