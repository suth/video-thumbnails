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

class Googledrive_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Google Drive';
	const service_name = 'Google Drive';
	// Slug for the video provider
	public $service_slug = 'googledrive';
	const service_slug = 'googledrive';

	public $options_section = array(
		'description' => '<p><strong>Optional</strong>: Only required if using videos from Google Drive.</p><p><strong>Directions</strong>: Go to the <a href="https://cloud.google.com/console/project">Google Developers Console</a>, create a project, then enable the Drive API. Next go to the credentials section and create a new public API access key. Choose server key, then leave allowed IPs blank and click create. Copy and paste your new API key below.</p>',
		'fields' => array(
			'api_key' => array(
				'name' => 'API Key',
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
		'#(?:https?:)?//docs\.google\.com/(?:a/[^/]+/)?file/d/([A-Za-z0-9\-_]+)/preview#', // iFrame URL
		'#(?:https?:)?//video\.google\.com/get_player\?docid=([A-Za-z0-9\-_]+)#', // Flash URL
		'#<param value="(?:[^"]+)?docid=([A-Za-z0-9\-_]+)(?:[^"]+)?" name="flashvars">#', // Flash (YouTube player)
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		// Get our API key
		$api_key = ( isset( $this->options['api_key'] ) && $this->options['api_key'] != '' ? $this->options['api_key'] : false );

		if ( $api_key ) {
			$request = "https://www.googleapis.com/drive/v2/files/$id?fields=thumbnailLink&key=$api_key";
			$response = wp_remote_get( $request );
			if( is_wp_error( $response ) ) {
				$result = $this->construct_info_retrieval_error( $request, $response );
			} else {
				$json = json_decode( $response['body'] );
				if ( isset( $json->error ) ) {
					$result = new WP_Error( 'googledrive_info_retrieval', $json->error->message );
				} else {
					$result = $json->thumbnailLink;
					$result = str_replace( '=s220', '=s480', $result );
				}
			}
		} else {
			$result = new WP_Error( 'googledrive_api_key', __( 'You must enter an API key in the <a href="' . admin_url( 'options-general.php?page=video_thumbnails&tab=provider_settings' ) . '">provider settings</a> to retrieve thumbnails from Google Drive.', 'video-thumbnails' ) );
		}
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<iframe src="https://docs.google.com/file/d/0B2tG5YeQL99ZUHNja3l6am9jSGM/preview?pli=1" width="640" height="385"></iframe>',
				'expected'      => 'https://lh3.googleusercontent.com/QL3d7Wh7V_qcXnMpXT6bio77RS0veyCZZ0zQbMX6gd-qH7aeIXBkXlcSJVDEyftiiA=s480',
				'expected_hash' => '3bc674d8d77b342e633ab9e93e345462',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
			array(
				'markup'        => '<iframe height="385" src="https://docs.google.com/a/svpanthers.org/file/d/0BxQsabDaO6USYUgxSUJ3T0ZBa3M/preview" width="100%"></iframe>',
				'expected'      => 'https://lh6.googleusercontent.com/WeOdCsaplJ3am25To1uLZiVYkyrilAQ5rxzhjnyyFc5GAF4QeCF1eq3EMpbP7O5dFg=s480',
				'expected_hash' => 'f120755bbd1d35e381cb84a829ac0dfa',
				'name'          => __( 'iFrame Embed (Apps account)', 'video-thumbnails' )
			),
			array(
				'markup'        => '<object width="500" height="385" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="src" value="https://video.google.com/get_player?docid=0B92WKFCDHyg9NTRqYTFjVkZmNlk&amp;ps=docs&amp;partnerid=30&amp;cc_load_policy=1" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><embed width="500" height="385" type="application/x-shockwave-flash" src="https://video.google.com/get_player?docid=0B92WKFCDHyg9NTRqYTFjVkZmNlk&amp;ps=docs&amp;partnerid=30&amp;cc_load_policy=1" allowFullScreen="true" allowScriptAccess="always" allowfullscreen="true" allowscriptaccess="always" /></object>',
				'expected'      => 'https://lh3.googleusercontent.com/U_lqaX1o7E9iU75XwCrHZ4pdSi-Vch2F_GK5Ib7WAxgwKTvTl0kMHXm2GxKo1Pcp3Q=s480',
				'expected_hash' => '31cf8e05f981c1beb6e04823ad54d267',
				'name'          => __( 'Flash Embed', 'video-thumbnails' )
			),
			array(
				'markup'        => '<object style="" id="" data="https://youtube.com/get_player?el=leaf" wmode="opaque" allowfullscreen="true" allowscriptaccess="always" type="application/x-shockwave-flash" height="720px" width="1280px"><param value="true" name="allowFullScreen"><param value="always" name="allowscriptaccess"><param value="opaque" name="wmode"><param value="allow_embed=0&partnerid=30&autoplay=1&showinfo=0&docid=0B9VJd4kStxIVellHZEdXdmdSamM&el=leaf" name="flashvars"></object>',
				'expected'      => 'https://lh5.googleusercontent.com/mHn5gESachhZHi-kbPCRbR6RVXZm3bR7oNNXL97LyYjpzV3Eqty71J2Waw0DPnXKKw=s480',
				'expected_hash' => '2d0ad4881e4b38de0510a103d2f40dd1',
				'name'          => __( 'Flash Embed (YouTube player)', 'video-thumbnails' )
			),
		);
	}

}

?>