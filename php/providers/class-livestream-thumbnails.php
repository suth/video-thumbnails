<?php

/*  Copyright 2015 Sutherland Boswell  (email : sutherland.boswell@gmail.com)

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

class Livestream_Thumbnails extends Video_Thumbnails_Provider {

	// Human-readable name of the video provider
	public $service_name = 'Livestream';
	const service_name = 'Livestream';
	// Slug for the video provider
	public $service_slug = 'livestream';
	const service_slug = 'livestream';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#\/\/cdn\.livestream\.com\/embed\/([A-Za-z0-9_]+)#', // Embed SRC
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		$result = 'http://thumbnail.api.livestream.com/thumbnail?name=' . $id;
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<iframe width="560" height="340" src="http://cdn.livestream.com/embed/WFMZ_Traffic?layout=4&amp;height=340&amp;width=560&amp;autoplay=false" style="border:0;outline:0" frameborder="0" scrolling="no"></iframe>',
				'expected'      => 'http://thumbnail.api.livestream.com/thumbnail?name=WFMZ_Traffic',
				'expected_hash' => '1be02799b2fab7a4749b2187f7687412',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}

}

?>