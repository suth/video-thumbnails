<?php
/*
Plugin Name: Video Thumbnails
Plugin URI: http://sutherlandboswell.com/2010/11/wordpress-video-thumbnails/
Description: A plugin designed to fetch video thumbnails. Use <code>&lt;?php video_thumbnail(); ?&gt;</code> in a loop to return a URL for the thumbnail of the first video in a post. Currently works with YouTube and Vimeo, and with the JR_embed plugin.
Author: Sutherland Boswell
Author URI: http://sutherlandboswell.com
Version: 0.1.2
License: GPL2
*/
/*  Copyright 2010 Sutherland Boswell  (email : sutherland.boswell@gmail.com)

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

// Get Vimeo Thumbnail
function getVimeoInfo($id, $info = 'thumbnail_medium') {
    if (!function_exists('curl_init')) die('CURL is not installed!');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://vimeo.com/api/v2/video/$id.php");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = unserialize(curl_exec($ch));
    $output = $output[0][$info];
    curl_close($ch);
    return $output;
};

// The Main Event
function video_thumbnail() {

	// Gets the post's content
	$markup = get_the_content();
	
	// Checks for a standard YouTube embed
	preg_match('#<object[^>]+>.+?http://www.youtube.com/v/([A-Za-z0-9\-_]+).+?</object>#s', $markup, $matches);

	// Checks for any YouTube URL
	if(!isset($matches[1])) {
		preg_match('#http://www.youtube.com/watch\?v=([A-Za-z0-9\-_]+)#s', $markup, $matches);
	}
	
	// If no standard YouTube embed is found, checks for one embedded with JR_embed
	if(!isset($matches[1])) {
		preg_match('#\[youtube id=([A-Za-z0-9\-_]+)]#s', $markup, $matches);
	};
	
	// If we've found a YouTube video ID, output the thumbnail URL
	if(isset($matches[1])) {
		echo 'http://img.youtube.com/vi/' . $matches[1] . '/0.jpg';
	}
	
	// If we didn't find anything, check for a standard Vimeo embed
	else {
		preg_match('#<object[^>]+>.+?http://vimeo.com/moogaloop.swf\?clip_id=([A-Za-z0-9\-_]+)&.+?</object>#s', $markup, $matches);
		
		// If we still haven't found anything, check for Vimeo embedded with JR_embed
		if(!isset($matches[1])) {
	    	preg_match('#\[vimeo id=([A-Za-z0-9\-_]+)]#s', $markup, $matches);
	    };
	};
	
	// Now if we've found a Vimeo ID, let's echo the thumbnail
	if(isset($matches[1])) {
		echo getVimeoInfo($matches[1], $info = 'thumbnail_medium');
	}
	
	// If we still don't find anything, display a default thumbnail
	else {
		echo plugins_url() . "/video-thumbnails/default.jpg";
	}
};

?>