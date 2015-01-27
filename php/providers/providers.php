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

// Include provider classes
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-youtube-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-vimeo-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-facebook-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-vine-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-blip-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-dailymotion-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-metacafe-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-vk-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-funnyordie-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-mpora-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-wistia-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-youku-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-tudou-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-collegehumor-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-rutube-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-sapo-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-ted-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-twitch-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-googledrive-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-yahooscreen-thumbnails.php' );
require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-livestream-thumbnails.php' );
// require_once( VIDEO_THUMBNAILS_PATH . '/php/providers/class-kaltura-thumbnails.php' );

// Register providers
add_filter( 'video_thumbnail_providers', array( 'Youtube_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Vimeo_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Facebook_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Vine_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Blip_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Dailymotion_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Metacafe_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Vk_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Funnyordie_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Mpora_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Wistia_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Youku_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Tudou_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Collegehumor_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Rutube_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Sapo_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Ted_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Twitch_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Googledrive_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Yahooscreen_Thumbnails', 'register_provider' ) );
add_filter( 'video_thumbnail_providers', array( 'Livestream_Thumbnails', 'register_provider' ) );
// add_filter( 'video_thumbnail_providers', array( 'Kaltura_Thumbnails', 'register_provider' ) );

?>