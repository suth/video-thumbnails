<?php

class Vimeo_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'vimeo';
    public $name = 'Vimeo';

    public $regexes = array(
        '#<object[^>]+>.+?http://vimeo\.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+)&.+?</object>#s', // Standard Vimeo embed code
		'#(?:https?:)?//player\.vimeo\.com/video/([0-9]+)#', // Vimeo iframe player
		'#\[vimeo id=([A-Za-z0-9\-_]+)]#', // JR_embed shortcode
		'#\[vimeo clip_id="([A-Za-z0-9\-_]+)"[^>]*]#', // Another shortcode
		'#\[vimeo video_id="([A-Za-z0-9\-_]+)"[^>]*]#', // Yet another shortcode
		'#(?:https?://)?(?:www\.)?vimeo\.com/([0-9]+)#', // Vimeo URL
		'#(?:https?://)?(?:www\.)?vimeo\.com/channels/(?:[A-Za-z0-9]+)/([0-9]+)#' // Channel URL
    );

    private function get_thumbnail_using_credentials( $id )
    {
        $vimeo = new phpVimeo( $this->options['client_id'], $this->options['client_secret'] );
        $vimeo->setToken( $this->options['access_token'], $this->options['access_token_secret'] );
        $response = $vimeo->call('vimeo.videos.getThumbnailUrls', array('video_id'=>$id));
        return $response->thumbnails->thumbnail[count($response->thumbnails->thumbnail)-1]->_content;
    }

    private function get_thumbnail_using_oembed( $id )
    {
        $request = "http://vimeo.com/api/oembed.json?url=http%3A//vimeo.com/$id";
        $response = wp_remote_get( $request );
        if( is_wp_error( $response ) ) {
            $result = $this->construct_info_retrieval_error( $request, $response );
        } elseif ( $response['response']['code'] == 404 ) {
            $result = new WP_Error( 'vimeo_info_retrieval', __( 'The Vimeo endpoint located at <a href="' . $request . '">' . $request . '</a> returned a 404 error.<br />Details: ' . $response['response']['message'], 'video-thumbnails' ) );
        } elseif ( $response['response']['code'] == 403 ) {
            $result = new WP_Error( 'vimeo_info_retrieval', __( 'The Vimeo endpoint located at <a href="' . $request . '">' . $request . '</a> returned a 403 error.<br />This can occur when a video has embedding disabled or restricted to certain domains. Try entering API credentials in the provider settings.', 'video-thumbnails' ) );
        } else {
            $result = json_decode( $response['body'] );
            $result = $result->thumbnail_url;
        }
        return $result;
    }

	public function get_thumbnail_url( $id ) {
		if ( $this->has_api_credentials() ) {
			$result = $this->get_thumbnail_using_credentials( $id );
		} else {
            $result = $this->get_thumbnail_using_oembed( $id );
		}
		return $result;
	}

    private function has_api_credentials()
    {
        return $this->setting( 'client_id' ) &&
            $this->setting( 'client_secret' ) &&
            $this->setting( 'access_token' ) &&
            $this->setting( 'access_token_secret' );
    }

    /**
     * Define the settings for this provider
     *
     * @param Refactored_Settings_Section $settings
     * @return Refactored_Settings_Section
     */
    public function settings( $settings )
    {
        return $settings->description('Only required for accessing private videos.')
            ->addFields(array(
                Refactored_Settings_Field_0_5_0::withKey('client_id')
                    ->name('Client ID')
                    ->type('text'),
                Refactored_Settings_Field_0_5_0::withKey('client_secret')
                    ->name('Client Secret')
                    ->type('text'),
                Refactored_Settings_Field_0_5_0::withKey('access_token')
                    ->name('Access Token')
                    ->type('text'),
                Refactored_Settings_Field_0_5_0::withKey('access_token_secret')
                    ->name('Access Token Secret')
                    ->type('text')
            ));
    }

    public static function get_tests() {
		return array(
            array(
				'markup'     => '<iframe src="http://player.vimeo.com/video/41504360" width="500" height="281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
                'expected'   => '41504360',
				'image_url'  => 'http://i.vimeocdn.com/video/287850781_1280.jpg',
				'image_hash' => '5388e0d772b827b0837444b636c9676c',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
			array(
				'markup'     => '<object width="500" height="281"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=41504360&amp;force_embed=1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=1&amp;color=00adef&amp;fullscreen=1&amp;autoplay=0&amp;loop=0" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=41504360&amp;force_embed=1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=1&amp;color=00adef&amp;fullscreen=1&amp;autoplay=0&amp;loop=0" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="500" height="281"></embed></object>',
                'expected'   => '41504360',
				'image_url'  => 'http://i.vimeocdn.com/video/287850781_1280.jpg',
				'image_hash' => '5388e0d772b827b0837444b636c9676c',
				'name'       => __( 'Flash Embed', 'video-thumbnails' )
			),
			array(
				'markup'     => 'https://vimeo.com/channels/soundworkscollection/44520894',
                'expected'   => '44520894',
				'image_url'  => 'http://i.vimeocdn.com/video/502998892_1280.jpg',
				'image_hash' => 'fde254d7ef7b6463cbd2451a99f2ddb1',
				'name'       => __( 'Channel URL', 'video-thumbnails' )
			),
		);
	}
}
