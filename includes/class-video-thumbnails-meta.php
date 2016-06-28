<?php

/**
 * The meta field functionality of the plugin.
 */
class Video_Thumbnails_Meta {

    /**
     * Gets the key for the meta field
     *
     * @return string
     */
    private static function getKey() {
        return '_video_thumbnail';
    }

    /**
     * Get the raw meta field
     *
     * @param mixed $post_id
     * @return string
     */
    private static function getPostMeta( $post_id ) {
        return get_post_meta( $post_id, self::getKey(), true );
    }

    /**
     * Check if the meta field is set
     *
     * @param int $post_id
     * @return bool
     */
    public static function exists( $post_id ) {
        return !! self::getPostMeta( $post_id );
    }

    /**
     * Gets the video thumbnail meta field
     *
     * @param int $post_id
     * @return mixed Returns the value or false if not set
     */
    public static function get( $post_id ) {
        if ( self::exists( $post_id ) ) {
            return self::getPostMeta( $post_id );
        }

        return false;
    }

    /**
     * Sets the video thumbnail meta field
     *
     * @param int $post_id
     * @param mixed $value
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
     */
    public static function set( $post_id, $value ) {
        return update_post_meta( $post_id, self::getKey(), $value );
    }
}
