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

class Video_Thumbnails_Provider {

	public $options = array();

	function __construct() {
		// If options are defined add the settings section
		if ( isset( $this->options_section ) ) add_action( 'admin_init', array( &$this, 'initialize_options' ) );
		// Get current settings for this provider
		$options = get_option( 'video_thumbnails' );
		if ( isset( $options['providers'][$this->service_slug] ) ) {
			$this->options = $options['providers'][$this->service_slug];
		}
	}

	function initialize_options() {
		add_settings_section(  
			$this->service_slug . '_provider_settings_section',
			$this->service_name . ' Settings',
			array( &$this, 'settings_section_callback' ),
			'video_thumbnails_providers'
		);
		foreach ( $this->options_section['fields'] as $key => $value ) {
			add_settings_field(
				$key,
				$value['name'],
				array( &$this, $value['type'] . '_setting_callback' ),
				'video_thumbnails_providers',
				$this->service_slug . '_provider_settings_section',
				array(
					'slug'        => $key,
					'description' => $value['description']
				)
			);
		}
	}

	function settings_section_callback() {
		echo $this->options_section['description'];
	}

	function text_setting_callback( $args ) {
		$value = ( isset( $this->options[$args['slug']] ) ? $this->options[$args['slug']] : '' );
		$html = '<input type="text" id="' . $args['slug'] . '" name="video_thumbnails[providers][' . $this->service_slug . '][' . $args['slug'] . ']" value="' . $value . '"/>';
		$html .= '<label for="' . $args['slug'] . '"> ' . $args['description'] . '</label>';
		echo $html;
	}

	public function scan_for_thumbnail( $markup ) {
		foreach ( $this->regexes as $regex ) {
			if ( preg_match( $regex, $markup, $matches ) ) {
				return $this->get_thumbnail_url( $matches[1] );
			}
		}
	}

	public function scan_for_videos( $markup ) {
		$videos = array();
		foreach ( $this->regexes as $regex ) {
			if ( preg_match_all( $regex, $markup, $matches, PREG_OFFSET_CAPTURE ) ) {
				$videos = array_merge( $videos, $matches[1] );
			}
		}
		return $videos;
	}

	/**
	 * Drops the parameters from a thumbnail URL
	 * @param  string $url
	 * @return string
	 */
	static function drop_url_parameters( $url ) {
		$url = explode( '?', $url );
		return $url[0];
	}

	/**
	 * Constructs a WP_Error object after failed API retrieval
	 * @param  string   $request  The URL wp_remote_get() failed to retrieve
	 * @param  WP_Error $response A WP_Error object returned by the failed wp_remote_get()
	 * @return WP_Error           An error object with a descriptive message including troubleshooting instructions
	 */
	function construct_info_retrieval_error( $request, $response ) {
		$code = $this->service_slug . '_info_retrieval';
		$message = sprintf( __( 'Error retrieving video information from the URL <a href="%1$s">%1$s</a> using <code>wp_remote_get()</code><br />If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.', 'video-thumbnails' ), $request ) . '<br />' . __( 'Error Details:', 'video-thumbnails' ) . ' ' . $response->get_error_message();
		return new WP_Error( $code, $message );
	}

	// // Requires PHP 5.3.0+	
	// public static function register_provider( $providers ) {
	// 	$providers[] = new static;
	// 	return $providers;
	// }

}

?>