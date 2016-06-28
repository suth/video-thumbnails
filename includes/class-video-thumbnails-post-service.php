<?php

/**
 * The post service.
 */
class Video_Thumbnails_Post_Service {

	/**
	 * The ID of this plugin.
     *
	 * @var string $plugin_name
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string $version
	 */
	private $version;

	/**
	 * The settings class.
	 *
	 * @var Refactored_Settings $settings
	 */
	private $settings;

	/**
	 * Video providers.
	 *
	 * @var Video_Thumbnails_Providers $providers
	 */
	private $providers;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 * @param      Refactored_Settings    $settings    The settings of this plugin.
	 * @param      Video_Thumbnails_Providers    $providers    Video providers.
	 */
	public function __construct( $plugin_name, $version, $settings, $providers ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->settings = $settings;
        $this->providers = $providers;

	}

    protected function post_types()
    {
        return $this->settings->basic->post_types->getValue();
    }

    protected function custom_field()
    {
        return $this->settings->basic->custom_field->getValue();
    }

    protected function is_autosaving()
    {
        return defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
    }

    protected function is_not_a_selected_post_type( $post_id )
    {
        return ! in_array( get_post_type( $post_id ), $this->post_types() );
    }

    protected function should_skip_post( $post_id )
    {
        if ( $this->is_autosaving() ||
             $this->is_not_a_selected_post_type( $post_id ) ||
             Video_Thumbnails_Meta::exists( $post_id )
        ) {
            return true;
        }

        return false;
    }

    protected function get_markup( $post_id )
    {
        if ( $this->custom_field() ) {
            $markup = get_post_meta( $post_id, $this->custom_field(), true );
        } else {
            $markup = get_post( $post_id )->post_content;
            $markup = apply_filters( 'the_content', $markup );
        }

        return apply_filters( 'video_thumbnails/markup', $markup, $post_id );
    }

    protected function parse_for_thumbnail_url( $content )
    {
        $new_thumbnail = false;

        $videos = $this->providers->parse( $content );

        if ( count( $videos ) > 0 ) {
            $new_thumbnail = $videos[0]->get_image_url();
        }

        return $new_thumbnail;
    }

    protected function get_new_thumbnail_url( $post_id )
    {
        $new_thumbnail = apply_filters( 'video_thumbnails/new_image_url', false, $post_id );
        
        if ( ! $new_thumbnail ) {
            $new_thumbnail = $this->parse_for_thumbnail_url( $this->get_markup( $post_id ) );
        }

        return $new_thumbnail;
    }

    public function save_post( $post_id )
    {
        if ( $this->should_skip_post( $post_id ) ) return null;

        $url = $this->get_new_thumbnail_url( $post_id );

        $attachment_id = Video_Thumbnails_Media_Library::save( $url, $post_id );

        if ( is_wp_error( $attachment_id ) ) {
            return $attachment_id;
        }

        Video_Thumbnails_Meta::set( $post_id, $attachment_id );
    }
}
