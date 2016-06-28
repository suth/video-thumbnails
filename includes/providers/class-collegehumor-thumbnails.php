<?php

class CollegeHumor_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'collegehumor';
    public $name = 'CollegeHumor';

    public $regexes = array(
        '#https?://(?:www\.)?collegehumor\.com/(?:video|e)/([0-9]+)#' // URL
    );

	public function get_thumbnail_url( $id ) {
		$request = "http://www.collegehumor.com/oembed.json?url=http%3A%2F%2Fwww.collegehumor.com%2Fvideo%2F$id";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$result = json_decode( $response['body'] );
			$result = $result->thumbnail_url;
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
				'markup'        => '<iframe src="http://www.collegehumor.com/e/6830834" width="600" height="338" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe><div style="padding:5px 0; text-align:center; width:600px;"><p><a href="http://www.collegehumor.com/videos/most-viewed/this-year">CollegeHumor\'s Favorite Funny Videos</a></p></div>',
				'expected'      => 'http://2.media.collegehumor.cvcdn.com/62/99/20502ca0d5b2172421002b52f437dcf8-mitt-romney-style-gangnam-style-parody.jpg',
				'expected_hash' => 'ceac16f6ee1fa5d8707e813226060a15',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
			array(
				'markup'        => 'http://www.collegehumor.com/video/6830834/mitt-romney-style-gangnam-style-parody',
				'expected'      => 'http://2.media.collegehumor.cvcdn.com/62/99/20502ca0d5b2172421002b52f437dcf8-mitt-romney-style-gangnam-style-parody.jpg',
				'expected_hash' => 'ceac16f6ee1fa5d8707e813226060a15',
				'name'          => __( 'Video URL', 'video-thumbnails' )
			),
		);
	}
    
}
