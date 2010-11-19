=== Video Thumbnails ===
Contributors: sutherlandboswell
Donate link: http://example.com/
Tags: Video, YouTube, Vimeo, Thumbnails
Requires at least: 3.0
Tested up to: 3.0.1
Stable tag: 0.1.2

Video Thumbnails is a simple plugin that makes it easier to display video thumbnails in your template.

== Description ==

Video Thumbnails makes it simple to display video thumbnails in your templates. Simply use `<?php video_thumbnail(); ?>` in a loop to grab the URL of a thumbnail for a video embedded in your post.

Video Thumbnails currently supports:

*   YouTube
*   Vimeo
*   JR Embed (uses `[youtube id=VIDEO_ID]` to embed videos, I've modified mine to also allow `[vimeo id=VIDEO_ID]`)

If no thumbnail is found, a default thumbnail is returned, which can be changed by replacing the `default.jpg` file found in your `/plugins/video-thumbnails/` directory.

This is just a start, so don't hesitate to share suggestions and let me know if you find any problems.

== Installation ==

1. Upload the `/video-thumbnails/` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php video_thumbnail(); ?>` in a loop inside your template. Because this is only a URL, you should set it as an image tag's source. Ex: `<img src="<?php video_thumbnail(); ?>" />`

== Frequently Asked Questions ==

= My video service isn't included, can you add it? =

If the service allows a way to retrieve thumbnails, I'll do my best to add it.

= I don't want a thumbnail for posts without a video, what should I do? =

I have several additions in mind, and better handling of this type of situation is one of them.

== Screenshots ==

Coming Soon

== Changelog ==

= 0.1.2 =
Fixed a possible issue with how the default image URL is created

= 0.1.1 =
Fixed an issue with the plugin directory's name that caused the default URL to be broken
Added support for YouTube URLs

= 0.1 =
Initial release