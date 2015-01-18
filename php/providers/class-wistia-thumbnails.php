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

class Wistia_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Wistia';
	const service_name = 'Wistia';
	// Slug for the video provider
	public $service_slug = 'wistia';
	const service_slug = 'wistia';

	// public $options_section = array(
	// 	'description' => '<p><strong>Optional</strong>: Only required if you have a CNAME record set up to use a custom domain.</p>',
	// 	'fields' => array(
	// 		'domain' => array(
	// 			'name' => 'Custom Wistia Domain',
	// 			'type' => 'text',
	// 			'description' => 'Enter the domain corresponding to your CNAME record for Wistia. Ex: videos.example.com'
	// 		)
	// 	)
	// );

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#Wistia\.embed\("([0-9a-zA-Z]+)"#', // JavaScript API embedding
		'#(https?://(?:.+)?(?:wistia\.com|wistia\.net|wi\.st)/(?:medias|embed)/(?:[\+~%\/\.\w\-]*))#', // Embed URL
		'#(https://wistia\.sslcs\.cdngc\.net/deliveries/[0-9a-zA-Z]+\.jpg)#' // Thumbnail image
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {

		// ID is an image URL, return it
		if ( substr( $id, -4 ) == '.jpg' ) return $id;

		// ID is actually an ID, convert it to a URL
		if ( substr( $id, 0, 4 ) != 'http' ) $id = 'http://fast.wistia.net/embed/iframe/' . $id;

		// ID should now be an embed URL, use oEmbed to find thumbnail URL
		$id = urlencode( $id );
		$request = "http://fast.wistia.com/oembed?url=$id";
		$response = wp_remote_get( $request );
		if( is_wp_error( $response ) ) {
			$result = $this->construct_info_retrieval_error( $request, $response );
		} else {
			$result = json_decode( $response['body'] );
			$result = $this->drop_url_parameters( $result->thumbnail_url );
		}

		return $result;

	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<iframe src="http://fast.wistia.net/embed/iframe/po4utu3zde?controlsVisibleOnLoad=true&version=v1&videoHeight=360&videoWidth=640&volumeControl=true" allowtransparency="true" frameborder="0" scrolling="no" class="wistia_embed" name="wistia_embed" width="640" height="360"></iframe>',
				'expected'      => 'https://embed-ssl.wistia.com/deliveries/6928fcba8355e38de4d95863a659e1de23cb2071.jpg',
				'expected_hash' => 'bc4a2cec9ac97e2ccdae2c7387a01cb4',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
			array(
				'markup'        => '<div class=\'wistia_embed\' data-video-height=\'312\' data-video-width=\'499\' id=\'wistia_j1qd2lvys1\'></div> <script charset=\'ISO-8859-1\' src=\'http://fast.wistia.com/static/concat/E-v1.js\'></script> <script> var platform = ( Modernizr.touch ) ? "html5" : "flash"; wistiaEmbed = Wistia.embed("j1qd2lvys1", { version: "v1", videoWidth: 499, videoHeight: 312, playButton: Modernizr.touch, smallPlayButton: Modernizr.touch, playbar: Modernizr.touch, platformPreference: platform, chromeless: Modernizr.touch ? false : true, fullscreenButton: false, autoPlay: !Modernizr.touch, videoFoam: true }); </script>',
				'expected'      => 'https://embed-ssl.wistia.com/deliveries/a086707fe096e7f3fbefef1d1dcba1488d23a3e9.jpg',
				'expected_hash' => '4c63d131604bfc07b5178413ab245813',
				'name'          => __( 'JavaScript Embed', 'video-thumbnails' )
			),
		);
	}

}

?>