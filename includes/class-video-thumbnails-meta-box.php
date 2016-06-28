<?php

/**
 * The meta box functionality of the plugin.
 */
class Video_Thumbnails_Meta_Box {

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
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 * @param      Refactored_Settings    $settings    The settings of this plugin.
	 */
	public function __construct( $plugin_name, $version, $settings ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->settings = $settings;

	}

    /**
     * Get the posts types the meta box should appear on
     *
     * @return array
     */
    public function get_post_types()
    {
        return $this->settings->basic->post_types->getValue();
    }

    /**
     * Register the meta box on the edit page
     */
    public function register() {
        $post_types = $this->get_post_types();

        foreach ( $post_types as $type ) {
            add_meta_box( $this->plugin_name, __( 'Video Thumbnail', 'video-thumbnails' ), array( &$this, 'render' ), $type, 'side', 'low' );
        }
	}

	/**
	 * Renders the video thumbnail meta box
	 */
	public function render() {
		global $post;

        self::render_for_post_id( $post->ID );
	}

    private static function render_for_post_id( $post_id )
    {
		$video_thumbnail = Video_Thumbnails_Meta::get_as_url( $post_id );

		if ( $video_thumbnail ) {
            self::render_has_thumbnail( $post_id, $video_thumbnail );
        } else {
            self::render_no_thumbnail( $post_id );
        }
    }

    private static function render_has_thumbnail( $post_id, $src )
    {
        if ( $src !== Video_Thumbnails_Media_Library::get_image_url( get_post_thumbnail_id( $post_id ) ) ) {
            echo '<p id="video-thumbnails-preview"><img src="' . $src . '" style="max-width:100%;" /></p>';
        } else {
            echo '<p><span class="dashicons dashicons-yes" style="color: #fff;background: #79ba49;display: block;width: 20px;height: 20px;text-align: center;border-radius: 10px;float: left;margin-right: 6px;line-height: 20px;"></span> ';
            echo __( 'Same as featured image', 'video-thumbnails' );
            echo '</p>';
        }
        echo '<p><a href="#" id="video-thumbnails-reset" onclick="video_thumbnails_reset(\'' . $post_id . '\' );">';
        echo __( 'Reset Video Thumbnail', 'video-thumbnails' );
        echo '</a></p>';
    }

    private static function render_no_thumbnail( $post_id )
    {
        echo '<p id="video-thumbnails-preview">';
        echo __( 'No video thumbnail for this post.', 'video-thumbnails' );
        echo '</p>';
        echo '<p><a href="#" id="video-thumbnails-reset" onclick="video_thumbnails_reset(\'' . $post_id . '\' );">';
        echo __( 'Search', 'video-thumbnails' );
        echo '</a> ';
        // TODO: link to troubleshooting info
        echo '<a href="#" style="float:right;">' . __( 'Troubleshooting', 'video-thumbnails' ) . '<a/></p>';
    }

    private static function render_troubleshooting()
    {
        ?>
        <h2><?php _e( 'Troubleshooting Video Thumbnails', 'video-thumbnails' ); ?></h2>
		<h3><?php _e( 'Fixing "No video thumbnail for this post"', 'video-thumbnails' ); ?></h3>
		<ol>
			<li><?php _e( 'Ensure you have saved any changes to your post.', 'video-thumbnails' ); ?></li>
			<li><?php echo sprintf( __( 'If you are using a a plugin or theme that stores videos in a special location other than the main post content area, be sure you\'ve entered the correct custom field on the <a href="%s">settings page</a>. If you don\'t know the name of the field your video is being saved in, please contact the developer of that theme or plugin.', 'video-thumbnails' ), admin_url( 'options-general.php?page=video_thumbnails' ) ); ?></li>
			<li><?php echo sprintf( __( 'Copy and paste your embed code into the "Test Markup for Video" section of the <a href="%1$s">Debugging page</a>. If this doesn\'t find the thumbnail, you\'ll want to be sure to include the embed code you scanned when you request support. If it does find a thumbnail, please double check that you have the Custom Field set correctly in the <a href="%2$s">settings page</a> if you are using a a plugin or theme that stores videos in a special location.', 'video-thumbnails' ), admin_url( 'options-general.php?page=video_thumbnails&tab=debugging' ), admin_url( 'options-general.php?page=video_thumbnails' ) ); ?></li>
			<li><?php echo sprintf( __( 'Go to the <a href="%s">Debugging page</a> and click "Test Image Downloading" to test your server\'s ability to save an image from a video source.', 'video-thumbnails' ), admin_url( 'options-general.php?page=video_thumbnails&tab=debugging' ) ); ?></li>
			<li><?php _e( 'Try posting a video from other sources to help narrow down the problem.', 'video-thumbnails' ); ?></li>
			<li><?php _e( 'Search the <a href="http://wordpress.org/support/plugin/video-thumbnails">support threads</a> to see if anyone has had the same issue.', 'video-thumbnails' ); ?></li>
			<li><?php _e( 'If you are still unable to resolve the problem, <a href="http://wordpress.org/support/plugin/video-thumbnails">start a thread</a> with a <strong>good descriptive</strong> title ("Error" or "No thumbnails" is a <strong>bad</strong> title) and be sure to include the results of your testing as well. Also be sure to include the <strong>name of your theme</strong>, any <strong>video plugins</strong> you\'re using, and any other details you can think of.', 'video-thumbnails' ); ?></li>
		</ol>
		<?php 
    }

}
