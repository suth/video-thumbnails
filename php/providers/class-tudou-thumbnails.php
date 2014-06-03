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

class Tudou_Thumbnails extends Video_Thumbnails_Providers {

	// Human-readable name of the video provider
	public $service_name = 'Tudou';
	const service_name = 'Tudou';
	// Slug for the video provider
	public $service_slug = 'tudou';
	const service_slug = 'tudou';

	public static function register_provider( $providers ) {
		$providers[self::service_slug] = new self;
		return $providers;
	}

	// Regex strings
	public $regexes = array(
		'#//(?:www\.)?tudou\.com/programs/view/html5embed\.action\?type=(?:[0-9]+)&code=([A-Za-z0-9_-]+)#', // iFrame SRC
	);

	// Thumbnail URL
	public function get_thumbnail_url( $id ) {
		return $result;
	}

	// Test cases
	public static function get_test_cases() {
		return array(
			array(
				'markup'        => '<iframe src="http://www.tudou.com/programs/view/html5embed.action?type=1&code=V-TeNdhKVCA&lcode=eNoG-G9OkrQ&resourceId=0_06_05_99" allowtransparency="true" scrolling="no" border="0" frameborder="0" style="width:480px;height:400px;"></iframe>',
				'expected'      => '',
				'expected_hash' => '',
				'name'          => __( 'iFrame Embed', 'video-thumbnails' )
			),
		);
	}

}

// Add to provider array
add_filter( 'video_thumbnail_providers', array( 'Tudou_Thumbnails', 'register_provider' ) );

?>