<?php

/**
 * The plugin settings class.
 *
 * This is used to define all the configurable options.
 *
 * Acts as a singleton.
 */
class Video_Thumbnails_Settings {

    protected static $instance;

    protected function __construct() {}

    final private function __clone() {}

    public static function get()
    {
        if (!isset(self::$instance)) {
            self::$instance = self::build_instance();
        }

        return self::$instance;
    }

    protected static function build_instance()
    {
        $settings = Refactored_Settings_0_5_0::withKey('video_thumbnails')
            ->title('Video Thumbnails')
            // TODO
            ->version('3.0');

        $section = Refactored_Settings_Section_0_5_0::withKey('basic')
            ->name(__('Basic Settings', 'video-thumbnails'))
            ->description(__('Things should work out of the box, but some users may need the options below.', 'video-thumbnails'));

        $fields = array(
            Refactored_Settings_Field_0_5_0::withKey('post_types')
                ->type('post_types')
                ->name('Post Types')
                ->description(__('Select the post types which you use for videos.', 'video-thumbnails'))
                ->defaultValue(array('post')),
            Refactored_Settings_Field_0_5_0::withKey('custom_field')
                ->type('text')
                ->name('Custom Field')
                ->description(__('If your videos are saved in a custom field enter the name here.', 'video-thumbnails'))
        );

        return $settings->addSection($section->addFields($fields));
    }
}
