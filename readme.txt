=== Video Thumbnails ===
Contributors: sutherlandboswell
Donate link: http://wie.ly/u/donate
Tags: Video, Thumbnails, YouTube, Vimeo, Vine, Twitch, Dailymotion, Youku, Rutube, Featured Image
Requires at least: 3.2
Tested up to: 3.9.2
Stable tag: 2.10.2

Video Thumbnails simplifies the process of automatically displaying video thumbnails in your WordPress template.

== Description ==

Video Thumbnails makes it easy to automatically display video thumbnails in your template. When you publish a post, this plugin will find the first video embedded and retrieve a thumbnail for you. Thumbnails can be saved to your media library and set as a featured image automatically. There's even support for custom post types and custom fields!

= Video Thumbnails Pro =

[Video Thumbnails Pro](https://refactored.co/plugins/video-thumbnails) adds new options like maximum image sizes, widescreen cropping, more powerful bulk scanning, a customizable upload directory, and more!

= Supported Sites =

* YouTube
* Vimeo
* Facebook
* Vine
* Twitch
* Dailymotion
* Metacafe
* TED
* VK
* Blip
* Google Drive
* Funny or Die
* CollegeHumor
* MPORA
* Wistia
* Youku
* Tudou
* SAPO
* Rutube

Video Thumbnails even works with most video plugins, including:

* [Refactored Video Importer](https://refactored.co/plugins/video-importer)
* Viper's Video Quicktags

= Help Translate =

[Register on our translation site](http://translate.refactored.co/blog/) to help bring Video Thumbnails to more languages.

= Our Other Plugins =

[Video Thumbnails Pro](https://refactored.co/plugins/video-thumbnails) | [Video Importer](https://refactored.co/plugins/video-importer) | [Retinamatic](http://retinamatic.com/)


== Installation ==

1. Upload the `/video-thumbnails/` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

Some functions are available to advanced users who want to customize their theme:

* `<?php video_thumbnail(); ?>` will echo a thumbnail URL or the default image located at `wp-content/plugins/video-thumbnails/default.jpg` if a thumbnail cannot be found. Here is an example: `<img src="<?php video_thumbnail(); ?>" width="300" />`
* `<?php $video_thumbnail = get_video_thumbnail(); ?>` will return the thumbnail URL or return NULL if none is found. In this example, a thumbnail is only shown if one is found: `<?php if( ( $video_thumbnail = get_video_thumbnail() ) != null ) { echo "<img src='" . $video_thumbnail . "' />"; } ?>`

== Frequently Asked Questions ==

= No video thumbnail for this post =

1. Ensure you have saved any changes to your post.
1. If you are using a a plugin or theme that stores videos in a special location other than the main post content area, be sure you've entered the correct custom field on the settings page. If you don't know the name of the field your video is being saved in, please contact the developer of that theme or plugin.
1. Copy and paste your embed code into the "Test Markup for Video" section of the Debugging page. If this doesn't find the thumbnail, you'll want to be sure to include the embed code you scanned when you request support. If it does find a thumbnail, please double check that you have the Custom Field set correctly in the settings page if you are using a a plugin or theme that stores videos in a special location.
1. Go to the Debugging page and click "Test Image Downloading" to test your server's ability to save an image from a video source.
1. Try posting a video from other sources to help narrow down the problem.
1. Check the support threads to see if anyone has had the same issue.
1. If you are still unable to resolve the problem, start a thread with a good descriptive title ("Error" or "No thumbnails" is a bad title) and be sure to include the results of your testing as well. Also be sure to include the name of your theme, any video plugins you're using, and any other details you can think of.

= Why are there black bars on some YouTube thumbnails? =

Video Thumbnails uses high-resolution widescreen thumbnails whenever they are available. If a video is not in HD, a fullscreen thumbnail is used. This can result in letterboxing when the video is actually widescreen. Users of the [pro version](https://refactored.co/plugins/video-thumbnails) can select an aspect ratio in the settings if this is a constant problem. [More info](https://refactored.co/blog/remove-black-bars-youtube-thumbnails).

= Can I get thumbnails from a specific time? =

No, Video Thumbnails only uses thumbnails provided by the source. If you're posting videos from your own account, many providers allow you to choose the thumbnail.

= Can it get thumbnails for my self-hosted videos? =

No, it will only fetch thumbnails for videos from the list of supported sites. Decoding local video files to create thumbnails would require server resources and packages unavailable to most users, so we only focus on supporting major video sites.

= My theme isn't showing thumbnails, what's wrong? =

The most likely problem is that your theme doesn't support post thumbnails. If thumbnails are supported, you should see a box titled "Featured Image" on the edit post page. If thumbnails aren't supported, your theme will have to be modified to support Featured Images or to support one of our custom functions.

= How can I use Video Thumbnails if I use a custom field to store the video? =

On the Video Thumbnails settings page just enter the name of the custom field and the plugin will scan it.

= Can I use the functions outside of a loop? =

Yes, but be sure to include the post ID as a parameter. For example: `<?php $thumbnail = get_video_thumbnail(25); ?>`

= My video service/embedding plugin isn't included, can you add it? =

If the service allows a way to retrieve thumbnails, I'll do my best to add it.

= How do I use this plugin with custom post types? =

The settings page includes a checklist of all your post types so you can pick and choose.

= I am editing my theme and only want to display a thumbnail if one is found. How do I do this? =

`<?php if( ( $video_thumbnail = get_video_thumbnail() ) != null ) { echo "<img src='" . $video_thumbnail . "' />"; } ?>` will only display a thumbnail when one exists, but I recommend using the Featured Image setting and [the_post_thumbnail](http://codex.wordpress.org/Function_Reference/the_post_thumbnail) template tag.

= I edited my theme and now I'm getting huge thumbnails, how can I resize them? =

The best solution is to use the Featured Image setting and [the_post_thumbnail](http://codex.wordpress.org/Function_Reference/the_post_thumbnail) template tag.

As an alternative you could assign a class to the element and style it with CSS.

= I edited my theme and now I'm seeing the thumbnail and the video, how do I only display the thumbnail? =

Every theme is different, so this can be tricky if you aren't familiar with WordPress theme development. You need to edit your template in the appropriate place, replacing `<?php the_content(); >` with `<?php the_excerpt(); >` so that only an excerpt of the post is shown on the home page or wherever you would like to display the video thumbnail.

= Why did it stop finding thumbnails for Vimeo? =

The Vimeo API has a rate limit, so in rare cases you may exceed this limit. Try again after a few hours.

== Screenshots ==

1. The Video Thumbnail meta box on the Edit Post page
1. Settings page

== Changelog ==

= 2.10.2 =
* Added actions hooks before and after generating attachment metadata

= 2.10.1 =
* Removed Justin.tv due to their shutdown

= 2.10 =
* Improved debugging page

= 2.9.1 =
* Improved provider class extensibility

= 2.9 =
* Added Tudou support
* Added SAPO support
* Updated Justin.tv to support the live player now that archives are being removed

= 2.8 =
* Added support for TED Talks
* Improved error handling for AJAX on settings page

= 2.7.9 =
* Better compatibility with image plugins
* Better Rutube support
* Fixed bug that prevented API retrieval error messages

= 2.7.8 =
* Improved Facebook detection
* Higher resolution Facebook thumbnails (when available)

= 2.7.7 =
* Improved error handling
* Fixed bugs
* Updated German translation

= 2.7.6 =
* Added Dailymotion support for protocol-relative embeds

= 2.7.5 =
* Better support for WP Robot
* Fixed bug related to Automatic YouTube Video Posts
* Reorganized video thumbnail extensions

= 2.7.4 =
* Improved VK detection
* Added German translation (thanks [applewerbung](http://wordpress.org/support/profile/applewerbung))
* Improved provider and markup tests by applying "the_content" filter
* Improved permissions checks for debugging

= 2.7.3 =
* Improved image download test
* Updated Vimeo and VK tests

= 2.7.2 =
* Added support for Youku iFrame embed code
* Fixed Vimeo channel URL test

= 2.7.1 =
* Fixed load_plugin_textdomain() path

= 2.7 =
* Video Thumbnails is now translation ready and looking for volunteers

= 2.6.3 =
* Better Google Drive detection (added support for underscores or dashes in the ID)

= 2.6.2 =
* Added feature to settings page that automatically detects the custom field
* Switched to JSON for Vimeo's oEmbed endpoint
* Added support for the "image/gif" MIME type

= 2.6.1 =
* Fixed VK bug caused by IDs with a negative int

= 2.6 =
* Added support for VK iFrame embeds
* Minor bugfixes and improvements

= 2.5.3 =
* Fixed bug with Automatic YouTube Video Posts when automatic publishing is disabled or when rescanning existing posts
* Fixed bug with bulk scanner when 0 posts are queued
* Improved markup test by checking if server can reach the resulting thumbnail URL

= 2.5.2 =
* Better support for Automatic YouTube Video Posts
* Added support tab to settings page

= 2.5.1 =
* Fixed improper error handling

= 2.5 =
* Added support for [Video Thumbnails Pro](https://refactored.co/plugins/video-thumbnails), our new solution for power users and professional bloggers
* Various bugfixes and improvements

= 2.4.2 =
* Fixed bug with image download test

= 2.4.1 =
* Added support for YouTube videos using LazyYT.js
* Better Google Drive support
* Filenames now have non-latin characters removed and use the post ID as a fallback
* Fixed bug caused by internal server error during bulk scans
* Improved performance of provider tests

= 2.4 =
* Added support for Vine
* Added support for Google Drive videos
* Rewritten bulk video thumbnail scanner
* Added troubleshooting link in meta box when no video thumbnail is found
* Changed provider test to compare file hashes in case thumbnail URLs change

= 2.3 =
* Added support for archived Twitch videos
* Fixed issue with Funny or Die thumbnails

= 2.2 =
* Added support for Rutube

= 2.1 =
* Changes under the hood to ensure the first video is found
* Improved YouTube regex

= 2.0.10 =
* Reduced overhead on settings pages

= 2.0.9 =
* Fixed Vimeo protocol-relative embeds
* Fixed bug with PNG image types

= 2.0.8 =
* Better fix for AYVP featured image bug

= 2.0.7 =
* Fix for bug that prevented featured images from being set for posts imported by AYVP

= 2.0.6 =
* Fix for "Fatal error: Cannot use object of type WP_Error as array" bug

= 2.0.5 =
* Added support for Blip URLs

= 2.0.4 =
* Fixed bug with file names when the post title uses non-latin characters

= 2.0.3 =
* Fixed problem caused by YouTube removing http: or https: from their default embed code

= 2.0.2 =
* Added descriptive messages for errors during provider tests
* Fix possible fatal error on activation when VimeoAPIException is already declared
* Fix possible undefined index warnings for provider settings

= 2.0.1 =
* Added support for Facebook's iFrame player

= 2.0 =
* Completely rewritten for better performance, more organized code, and easier maintenance
* Added support for Funny or Die
* Added support for MPORA
* Added support for Wistia
* Added support for Facebook videos
* Added support for CollegeHumor
* Added support for Youku
* Added support for HD YouTube thumbnails
* Added support for higher resolution Vimeo thumbnails
* Added support for private Vimeo videos (API credentials required)
* Added support for Vimeo channel URLs
* Added support for [Automatic Youtube Video Posts Plugin](http://wordpress.org/extend/plugins/automatic-youtube-video-posts/)
* Added filters to make plugin extensible
* Removed cURL requirement (still requires ability to make external requests)
* Better checks for blank thumbnails before added to media library
* Adds 'video_thumbnail' field to video thumbnails saved in the media library
* Option to clear all video thumbnails (clears custom field from posts and deletes video thumbnails added after 2.0 from library)
* Better file names
* Added provider tests on debugging page to help troubleshoot
* Added a markup detection on debugging page
* Added "Installation Information" section to debugging page with helpful troubleshooting info
* Settings improvements
* Bug fixes

= 1.8.2 =
* Fixes issue where some servers were unable to download thumbnails from YouTube
* Fixes possible issue setting new thumbnail as featured image

= 1.8.1 =
* Plugin now scans posts added using XML-RPC which makes posting videos from iOS or other apps work smoothly

= 1.8 =
* Added support for custom fields via a new setting
* Added support for YouTube's privacy-enhanced domain (youtube-nocookie.com)
* Fixed image duplication bug
* Now more consistent with [WordPress Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards) (thanks [Daedalon](http://wordpress.org/support/profile/daedalon))

= 1.7.7 =
* Better cURL error handling
* Better regex matching
* Bug fixes
* Thanks to [Daedalon](http://wordpress.org/support/profile/daedalon) for many of these changes

= 1.7.6 =
* Fixed plugin link
* Added donate button

= 1.7.5 =
* Bugfix for array error on line 408

= 1.7.4 =
* Fixed Dailymotion bug (thanks [Gee](http://wordpress.org/support/profile/geekxx))
* Added detection for Dailymotion URLs (thanks [Gee](http://wordpress.org/support/profile/geekxx))
* Added support for [WP YouTube Lyte](http://wordpress.org/extend/plugins/wp-youtube-lyte/)

= 1.7.3 =
* More comprehensive search for embedded YouTube videos

= 1.7.2 =
* Added support for Dailymotion and Metacafe

= 1.7 =
* Added new option to scan past posts for video thumbnails

= 1.6 =
* Added support for custom post types

= 1.5 =
* Video thumbnails are now only saved when a post's status changes to published.
* Removed URL field from the Video Thumbnail meta box on the Edit Post Page
* Added a "Reset Video Thumbnail" button to the meta box
* Accidental duplicate images should no longer be problem

= 1.1.1 =
* Fixed a bug related to scheduled posts sometimes not saving thumbnail URL to the meta field

= 1.1 =
* Fixed bug created by a change in YouTube's embed codes

= 1.0.9 =
* More work on fixing the duplicate image bug

= 1.0.8 =
* (Attempted to) fix another bug that could create duplicate images when updating a post

= 1.0.7 =
* Fixed a bug that could create duplicate images on auto-save

= 1.0.6 =
* Improved Blip.tv support

= 1.0.5 =
* Now using cURL to help save thumbnails locally instead of file_get_contents()

= 1.0.4 =
* Added compatibility with YouTube's new iframe embedding
* Now supports most embedding plugins

= 1.0.3 =
* Fixed an issue where existing thumbnails (such as ones manually set by the user) would be replaced by Video Thumbnails
* Added a checks to see if cURL is running

= 1.0.1 =
* Removed "Scan for Video Thumbnails" button from settings page until improvements can be made

= 1.0 =
* Video Thumbnails can now be stored in the local WordPress media library
* Video Thumbnails stored locally can automatically be set as the featured image, making it support many themes automatically
* Added an options page to enable/disable local storage and enable/disable automatically setting that thumbnail as the featured image
* Settings page also includes a button to scan all posts for video thumbnails

= 0.6 =
* Added support for Justin.tv
* Fixed bug that could cause a conflict with other plugins

= 0.5.5 =
* Video thumbnails are now found at the time the post is saved

= 0.5.4 =
* Video thumbnails can be retrieved for a specific post ID by passing a parameter to the `video_thumbnail()` or `get_video_thumbnail()` function. For example: `<?php $id = 25; $thumbnail = get_video_thumbnail($id); if($thumbnail!=null) echo $thumbnail; ?>`

= 0.5.3 =
* Better support for Vimeo Shortcode (thanks to Darren for the tip)

= 0.5.2 =
* Added support for [Simple Video Embedder](http://wordpress.org/extend/plugins/simple-video-embedder/)

= 0.5.1 =
* Added a test to make sure the YouTube thumbnail actually exists. This prevents you from getting that ugly default thumbnail from YouTube.

= 0.5 =
* Thumbnail URLs are now stored in a custom field with each post, meaning the plugin only has to interact with outside APIs once per post.
* Added a "Video Thumbnail" meta box to the edit screen for each post, which can be manually set or will be set automatically once `video_thumbnail()` or `get_video_thumbnail()` is called in a loop for that post.

= 0.3 =
* Added basic support for Blip.tv auto embedded using URLs in this format: http://blip.tv/file/12345

= 0.2.3 =
* Added support for any Vimeo URL

= 0.2.2 =
* Added support for [Vimeo Shortcode](http://blog.esimplestudios.com/2010/08/embedding-vimeo-videos-in-wordpress/)

= 0.2.1 =
* Added support for Vimeo players embedded using an iframe

= 0.2 =
* Added `get_video_thumbnail()` to return the URL without echoing or return null if no thumbnail is found, making it possible to only display a thumbnail if one is found.

= 0.1.3 =
* Fixed an issue where no URL was returned when Vimeo's rate limit had been exceeded. The default image URL is now returned, but a future version of the plugin will store thumbnails locally for a better fix.

= 0.1.2 =
* Fixed a possible issue with how the default image URL is created

= 0.1.1 =
* Fixed an issue with the plugin directory's name that caused the default URL to be broken
* Added support for YouTube URLs

= 0.1 =
* Initial release

== Upgrade Notice ==

= 2.1 =
Changes to the scanning process may affect any custom code you've developed for Video Thumbnails

= 2.0 =
Despite being a major upgrade, your settings should remain intact. Please report any problems so they can be fixed quickly!

= 1.0 =
This is the single biggest update to Video Thumbnails so far, so be sure to check out the new settings page and documentation.

= 0.5 =
This version adds the thumbnail URL to the post's meta data, meaning any outside APIs only have to be queried once per post. Vimeo's rate limit was easily exceeded, so this should fix that problem.

== Known Issues ==

* In some cases you may have to manually search for thumbnails on the post editor

== Roadmap ==

With the release of 2.0, focus will be put on ensuring more widespread support and providing tools for other developers.