<?php

/*  Copyright 2014 Sutherland Boswell  (email : sutherland.boswell@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Require thumbnail provider class
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-video-thumbnails-provider.php' );

class Tudou_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Tudou';
	const service_name = 'Tudou';
	// Slug for the video provider
	public $service_slug = 'tudou';
	const service_slug = 'tudou';

	public $options_section = array(
		'description' => '<p><strong>Optional</strong>: Only required if using videos from Tudou.</p><p><strong>Directions</strong>: Go to <a href="http://open.tudou.com/">open.tudou.com</a> and create an application, then copy and paste your app key below.</p>',
		'fields' => array(
			'app_key' => array(
				'name' => 'App Key',
				'type' => 'text',
				'description' => ''
			)
		)
	);

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#//(?:www\.)?tudou\.com/programs/view/html5embed\.action\?type=(?:[0-9]+)(?:&|&\#038;|&amp;)code=([A-Za-z0-9\-_]+)#', // iFrame SRC
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		// Get our API key
		$app_key = ( isset( $this->options['app_key'] ) && $this->options['app_key'] != '' ? $this->options['app_key'] : false );

		if ( $app_key ) {
			$request = "http://api.tudou.com/v6/video/info?app_key=$app_key&format=json&itemCodes=$id";
			$response = wp_remote_get( $request, array( 'sslverify' => false ) );
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

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<iframe src="http://www.tudou.com/programs/view/html5embed.action?type=1&code=V-TeNdhKVCA&lcode=eNoG-G9OkrQ&resourceId=0_06_05_99" allowtransparency="true" scrolling="no" border="0" frameborder="0" style="width:480px;height:400px;"></iframe>',
				'expected'      => 'http://g3.tdimg.com/83fedbc41cf9055dce9182a0c07da601/w_2.jpg',
				'expected_hash' => '3a5e656f8c302ae5b23665f22d296ae1',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}

}

?>