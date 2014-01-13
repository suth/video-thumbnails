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
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-video-thumbnails-providers.php' );

class GoogleDrive_Thumbnails extends Video_Thumbnails_Providers {

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
		'#(?:https?:)?//docs\.google\.com/(?:a/[^/]+/)?file/d/([A-Za-z0-9]+)/preview#', // iFrame URL
		'#(?:https?:)?//video\.google\.com/get_player\?docid=([A-Za-z0-9]+)#', // Flash URL
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		// Get our API key
		$api_key = ( isset( $this->options['api_key'] ) && $this->options['api_key'] != '' ? $this->options['api_key'] : false );

		if ( $api_key ) {
			$request = "https://www.googleapis.com/drive/v2/files/$id?fields=thumbnailLink&key=$api_key";
			$response = wp_remote_get( $request, array( 'sslverify' => false ) );
			if( is_wp_error( $response ) ) {
				$result = new WP_Error( 'googledrive_info_retrieval', __( 'Error retrieving video information from the URL <a href="' . $request . '">' . $request . '</a> using <code>wp_remote_get()</code><br />If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.<br />Details: ' . $response->get_error_message() ) );
			} else {
				$json = json_decode( $response['body'] );
				$result = $json->thumbnailLink;
				$result = str_replace( '=s220', '=s480', $result );
			}
		} else {
			$result = new WP_Error( 'googledrive_api_key', __( 'You must enter an API key to retrieve thumbnails from Google Drive' ) );
		}
		return $result;
	}

	// Test cases
	public $test_cases = array(
		array(
			'markup'        => '<iframe src="https://docs.google.com/file/d/0B2tG5YeQL99ZUHNja3l6am9jSGM/preview?pli=1" width="640" height="385"></iframe>',
			'expected'      => 'https://lh3.googleusercontent.com/QL3d7Wh7V_qcXnMpXT6bio77RS0veyCZZ0zQbMX6gd-qH7aeIXBkXlcSJVDEyftiiA=s480',
			'expected_hash' => '3bc674d8d77b342e633ab9e93e345462',
			'name'          => 'iFrame embed'
		),
		array(
			'markup'        => '<iframe height="385" src="https://docs.google.com/a/svpanthers.org/file/d/0BxQsabDaO6USYUgxSUJ3T0ZBa3M/preview" width="100%"></iframe>',
			'expected'      => 'https://lh6.googleusercontent.com/WeOdCsaplJ3am25To1uLZiVYkyrilAQ5rxzhjnyyFc5GAF4QeCF1eq3EMpbP7O5dFg=s480',
			'expected_hash' => 'f120755bbd1d35e381cb84a829ac0dfa',
			'name'          => 'iFrame embed (Apps account)'
		),
		array(
			'markup'        => '<object width="500" height="385" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="src" value="https://video.google.com/get_player?docid=0B92WKFCDHyg9NTRqYTFjVkZmNlk&amp;ps=docs&amp;partnerid=30&amp;cc_load_policy=1" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><embed width="500" height="385" type="application/x-shockwave-flash" src="https://video.google.com/get_player?docid=0B92WKFCDHyg9NTRqYTFjVkZmNlk&amp;ps=docs&amp;partnerid=30&amp;cc_load_policy=1" allowFullScreen="true" allowScriptAccess="always" allowfullscreen="true" allowscriptaccess="always" /></object>',
			'expected'      => 'https://lh3.googleusercontent.com/U_lqaX1o7E9iU75XwCrHZ4pdSi-Vch2F_GK5Ib7WAxgwKTvTl0kMHXm2GxKo1Pcp3Q=s480',
			'expected_hash' => '31cf8e05f981c1beb6e04823ad54d267',
			'name'          => 'Flash embed'
		),
	);

}

// Add to provider array
add_filter( 'video_thumbnail_providers', array( 'GoogleDrive_Thumbnails', 'register_provider' ) );

?>