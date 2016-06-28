<?php

/**
 * The meta field functionality of the plugin.
 */
class Video_Thumbnails_Media_Library {
    /**
     * Construct a retrieval error
     *
     * @param string $image_url
     * @param string $message
     * @return WP_Error
     */
    private static function make_retrieval_error($image_url, $message)
    {
        return new WP_Error( 'thumbnail_retrieval', sprintf( __( 'Error retrieving a thumbnail from the URL <a href="%1$s">%1$s</a> using <code>wp_remote_get()</code><br />If opening that URL in your web browser returns anything else than an error page, the problem may be related to your web server and might be something your host administrator can solve.', 'video-thumbnails' ), $image_url ) . '<br>' . __( 'Error Details:', 'video-thumbnails' ) . ' ' . $message );
    }

	/**
	 * Creates a file name for use when saving an image to the media library.
	 * It will either use a sanitized version of the title or the post ID.
	 * @param  int    $post_id The ID of the post to create the filename for
     * @param  string $extension A file extension to use
	 * @return string          A filename
	 */
	private static function construct_filename( $post_id, $extension ) {
		$filename = get_the_title( $post_id );
		$filename = sanitize_title( $filename, $post_id );
		$filename = urldecode( $filename );
		$filename = preg_replace( '/[^a-zA-Z0-9\-]/', '', $filename );
		$filename = substr( $filename, 0, 32 );
		$filename = trim( $filename, '-' );
		if ( $filename == '' ) $filename = (string) $post_id;
		return $filename . $extension;
	}

    private static function get_extension_for_content_type( $image_type )
    {
        switch ( $image_type ) {
            case 'image/jpeg':
                $extension = '.jpg';
                break;
            case 'image/png':
                $extension = '.png';
                break;
            case 'image/gif':
                $extension = '.gif';
                break;
            default:
                $extension = new WP_Error( 'thumbnail_upload', __( 'Unsupported MIME type:', 'video-thumbnails' ) . ' ' . $image_type );
        }

        return $extension;
    }

	/**
	 * Saves a remote image to the media library
	 * @param  string $image_url URL of the image to save
	 * @param  int    $post_id   ID of the post to attach image to
	 * @return int               ID of the attachment
	 */
	public static function save( $image_url, $post_id ) {
		$response = wp_remote_get( $image_url );

		if( is_wp_error( $response ) ) {
            return self::make_retrieval_error($image_url, $response->get_error_message());
		}

        $image_contents = $response['body'];
        $image_type = wp_remote_retrieve_header( $response, 'content-type' );

        // Translate MIME type into an extension
        $extension = self::get_extension_for_content_type( $image_type );

		if( is_wp_error( $extension ) ) {
            return $extension;
		}

        $new_filename = self::construct_filename( $post_id, $extension );

        // Save the image bits using the new filename
        do_action( 'video_thumbnails/pre_upload_bits', $image_contents );
        $upload = wp_upload_bits( $new_filename, null, $image_contents );
        do_action( 'video_thumbnails/after_upload_bits', $upload );

        // Stop for any errors while saving the data
        if ( $upload['error'] ) {
            return new WP_Error( 'thumbnail_upload', __( 'Error uploading image data:', 'video-thumbnails' ) . ' ' . $upload['error'] );
        }

        do_action( 'video_thumbnails/image_downloaded', $upload['file'] );

        $wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );

        $upload = apply_filters( 'wp_handle_upload', array(
            'file' => $upload['file'],
            'url'  => $upload['url'],
            'type' => $wp_filetype['type']
        ), 'sideload' );

        // Contstruct the attachment array
        $attachment = array(
            'post_mime_type'	=> $upload['type'],
            'post_title'		=> get_the_title( $post_id ),
            'post_content'		=> '',
            'post_status'		=> 'inherit'
        );

        // Insert the attachment
        $attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );

        // Required for wp_generate_attachment_metadata() to work
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        do_action( 'video_thumbnails/pre_generate_attachment_metadata', $attach_id, $upload['file'] );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
        do_action( 'video_thumbnails/after_generate_attachment_metadata', $attach_id, $upload['file'] );

        wp_update_attachment_metadata( $attach_id, $attach_data );

        // Add field to mark image as a video thumbnail
        update_post_meta( $attach_id, 'video_thumbnail', '1' );

		return $attach_id;
	}
}
