# Changes

### Beta 7

* Added ability to clear all video thumbnail attachments and custom fields
* Disables the AYVP filter that overrides the post thumbnail
* Minor code reorganization
* New default thumbnail when using `video_thumbnail()`

### Beta 6

* Added support for private Vimeo videos when API credentials are entered in settings
* Added support for Vimeo channel URLs
* Fixed more PHP 5.2 issues
* Settings backend overhauled
* Consolidated settings into a single database entry
* Basic functionality to handle settings upgrades

### Beta 5

* Added a new test where markup can be scanned for thumbnails
* Added helpful information to the debugging page for troubleshooting issues
* Updated MPORA to support the new embed code and thumbnail URLs
* Improved Wistia support
* Fixed error on servers running PHP 5.2
* Began working on a settings overhaul

### Beta 4

* Added CollegeHumor support
* Added Youku support (flash player and link detection)
* Added built-in support for the Automatic YouTube Video Posts plugin
* Added 'video_thumbnail_providers' filter so developers can add support for more video sites
* Added 'new_video_thumbnail_url' filter so developers can manually specify a thumbnail url before trying to scan markup
* Added 'video_thumbnail_markup' filter so developers can modify which markup is scanned for videos
* Removed cURL requirement
* Fixed bug that disabled saving as a featured image

### Beta 3

* Added Wistia support
* Added provider tests to help automatically find bugs
* Higher resolution Vimeo thumbnails
* Added support for Facebook videos
* Video Thumbnails in the media library now have a custom field named 'video_thumbnail' with the value set to '1'
* Added test to show if theme supports post thumbnails

### Beta 2

* Bugfix for "cannot redeclare class" error

### Beta 1

* Added support for Funny or Die
* Added support for MPORA
* Added support for high resolution widescreen YouTube thumbnails (when available)
* Checks for error when uploading thumbnail so blank entries aren't added to media library
* Better file names

# Roadmap

* Add filters for regex arrays
* Possibly make "_video_thumbnail" custom field user changeable
* Remove "Smart YouTube Plugin" specific code from generic YouTube class