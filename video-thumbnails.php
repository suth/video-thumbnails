<?php

/**
 * Video Thumbnails bootstrap file
 *
 * This file includes the dependencies used by the plugin,
 * registers activation and deactivation functions, and
 * then defines a function that starts up the plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Video Thumbnails
 * Plugin URI:        http://wpvideothumbnails.com
 * Description:       Automatically create thumbnails for posts using embedded videos.
 * Version:           3.0
 * Author:            Sutherland Boswell
 * Author URI:        http://sutherlandboswell.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       video-thumbnails
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-video-thumbnails.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_video_thumbnails() {

	$video_thumbnails = new Video_Thumbnails();
	$video_thumbnails->run();

}
run_video_thumbnails();
