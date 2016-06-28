<?php

class Tudou_Thumbnails extends Video_Thumbnails_Provider
{
    public $key = 'tudou';
    public $name = 'Tudou';

    public $regexes = array(
		'#//(?:www\.)?tudou\.com/programs/view/html5embed\.action\?type=(?:[0-9]+)(?:&|&\#038;|&amp;)code=([A-Za-z0-9\-_]+)#', // iFrame SRC
    );

	public function get_thumbnail_url( $id ) {
		if ( $this->setting( 'app_key' ) ) {
			$request = 'http://api.tudou.com/v6/video/info?app_key=' . $this->setting( 'app_key' ) . '&format=json&itemCodes=' . $id;
			$response = wp_remote_get( $request );
			if( is_wp_error( $response ) ) {
				$result = $this->construct_info_retrieval_error( $request, $response );
			} else {
				$result = json_decode( $response['body'] );
				if ( isset( $result->error_info ) ) {
					$result = new WP_Error( 'tudou_api_error', $result->error_info );
				} elseif ( !isset( $result->results[0]->bigPicUrl ) ) {
					$result = new WP_Error( 'tudou_not_found',  sprintf( __( 'Unable to retrieve thumbnail for Tudou video with an ID of %s', 'video-thumbnails' ), $id ) );
				} else {
					$result = $result->results[0]->bigPicUrl;
				}
			}
		} else {
			$result = new WP_Error( 'tudou_api_key', __( 'You must enter an API key in the <a href="' . admin_url( 'options-general.php?page=video_thumbnails&tab=provider_settings' ) . '">provider settings</a> to retrieve thumbnails from Tudou.', 'video-thumbnails' ) );
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
        return $settings->description('Required for Tudou support.')
            ->addField(Refactored_Settings_Field_0_5_0::withKey('app_key')
                ->name('App Key')
                ->type('text')
            );
    }

    public static function get_tests() {
		return array(
            array(
				'markup'     => '<iframe src="http://www.tudou.com/programs/view/html5embed.action?type=1&code=V-TeNdhKVCA&lcode=eNoG-G9OkrQ&resourceId=0_06_05_99" allowtransparency="true" scrolling="no" border="0" frameborder="0" style="width:480px;height:400px;"></iframe>',
                'expected'   => 'V-TeNdhKVCA',
				'image_url'  => 'http://g3.tdimg.com/83fedbc41cf9055dce9182a0c07da601/w_2.jpg',
				'image_hash' => '3a5e656f8c302ae5b23665f22d296ae1',
				'name'       => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}
}
