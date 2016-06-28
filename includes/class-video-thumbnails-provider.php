<?php

class Video_Thumbnails_Provider
{
    protected $plugin_settings;

    public function __construct( $plugin_settings )
    {
        $this->plugin_settings = $plugin_settings;
    }

    public function init()
    {
        if ( method_exists( $this, 'settings' ) ) {
            $this->plugin_settings->addSection(
                call_user_func(
                    array( &$this, 'settings' ),
                    Refactored_Settings_Section_0_5_0::withKey( $this->key )
                        ->name( $this->name )
                )
            );
        }
    }

    public function parse( $markup ) {
		$videos = array();
		foreach ( $this->regexes as $regex ) {
			if ( preg_match_all( $regex, $markup, $matches, PREG_OFFSET_CAPTURE ) ) {
                $videos[] = $this->make_match( $matches[1][0] );
			}
		}
		return $videos;
	}

    /**
     * Create a match object
     *
     * @param array $match
     * @return Video_Thumbnails_Match
     */
    protected function make_match( $match )
    {
        return new Video_Thumbnails_Match( $match[0], array( $this, 'get_thumbnail_url' ), $match[1] );
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
		$code = $this->key . '_info_retrieval';
		$message = sprintf( __( 'Error retrieving video information from the URL <a href="%1$s">%1$s</a> using <code>wp_remote_get()</code><br />If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.', 'video-thumbnails' ), $request ) . '<br />' . __( 'Error Details:', 'video-thumbnails' ) . ' ' . $response->get_error_message();
		return new WP_Error( $code, $message );
	}
}
