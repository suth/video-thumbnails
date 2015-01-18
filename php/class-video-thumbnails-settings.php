<?php

/*  Copyright 2013 Sutherland Boswell  (email : sutherland.boswell@gmail.com)

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

class Video_Thumbnails_Settings {

	public $options;

	var $default_options = array(
		'save_media'   => 1,
		'set_featured' => 1,
		'post_types'   => array( 'post' ),
		'custom_field' => ''
	);

	function __construct() {
		// Activation and deactivation hooks
		register_activation_hook( VIDEO_THUMBNAILS_PATH . '/video-thumbnails.php', array( &$this, 'plugin_activation' ) );
		register_deactivation_hook( VIDEO_THUMBNAILS_PATH . '/video-thumbnails.php', array( &$this, 'plugin_deactivation' ) );
		// Set current options
		add_action( 'plugins_loaded', array( &$this, 'set_options' ) );
		// Add options page to menu
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		// Initialize options
		add_action( 'admin_init', array( &$this, 'initialize_options' ) );
		// Custom field detection callback
		add_action( 'wp_ajax_video_thumbnail_custom_field_detection', array( &$this, 'custom_field_detection_callback' ) );
		// Ajax clear all callback
		add_action( 'wp_ajax_clear_all_video_thumbnails', array( &$this, 'ajax_clear_all_callback' ) );
		// Ajax test callbacks
		add_action( 'wp_ajax_video_thumbnail_provider_test', array( &$this, 'provider_test_callback' ) ); // Provider test
		add_action( 'wp_ajax_video_thumbnail_image_download_test', array( &$this, 'image_download_test_callback' ) ); // Saving media test
		add_action( 'wp_ajax_video_thumbnail_delete_test_images', array( &$this, 'delete_test_images_callback' ) ); // Delete test images
		add_action( 'wp_ajax_video_thumbnail_markup_detection_test', array( &$this, 'markup_detection_test_callback' ) ); // Markup input test
		// Admin scripts
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ) );
		// Add "Go Pro" call to action to settings footer
		add_action( 'video_thumbnails/settings_footer', array( 'Video_Thumbnails_Settings', 'settings_footer' ) );
	}

	// Activation hook
	function plugin_activation() {
		add_option( 'video_thumbnails', $this->default_options );
	}

	// Deactivation hook
	function plugin_deactivation() {
		delete_option( 'video_thumbnails' );
	}

	// Set options & possibly upgrade
	function set_options() {
		// Get the current options from the database
		$options = get_option( 'video_thumbnails' );
		// If there aren't any options, load the defaults
		if ( ! $options ) $options = $this->default_options;
		// Check if our options need upgrading
		$options = $this->upgrade_options( $options );
		// Set the options class variable
		$this->options = $options;
	}

	function upgrade_options( $options ) {

		// Boolean for if options need updating
		$options_need_updating = false;

		// If there isn't a settings version we need to check for pre 2.0 settings
		if ( ! isset( $options['version'] ) ) {

			// Check for post type setting
			$post_types = get_option( 'video_thumbnails_post_types' );

			// If there is a a post type option we know there should be others
			if ( $post_types !== false ) {

				$options['post_types'] = $post_types;
				delete_option( 'video_thumbnails_post_types' );

				$options['save_media'] = get_option( 'video_thumbnails_save_media' );
				delete_option( 'video_thumbnails_save_media' );

				$options['set_featured'] = get_option( 'video_thumbnails_set_featured' );
				delete_option( 'video_thumbnails_set_featured' );

				$options['custom_field'] = get_option( 'video_thumbnails_custom_field' );
				delete_option( 'video_thumbnails_custom_field' );

			}

			// Updates the options version to 2.0
			$options['version'] = '2.0';
			$options_need_updating = true;

		}

		if ( version_compare( $options['version'], VIDEO_THUMBNAILS_VERSION, '<' ) ) {
			$options['version'] = VIDEO_THUMBNAILS_VERSION;
			$options_need_updating = true;
		}

		// Save options to database if they've been updated
		if ( $options_need_updating ) {
			update_option( 'video_thumbnails', $options );
		}

		return $options;

	}

	function admin_menu() {
		add_options_page(
			__( 'Video Thumbnails Options', 'video-thumbnails' ),
			__( 'Video Thumbnails', 'video-thumbnails' ),
			'manage_options',
			'video_thumbnails',
			array( &$this, 'options_page' )
		);
	}

	function admin_scripts( $hook ) {
		if ( 'settings_page_video_thumbnails' == $hook ) {
			wp_enqueue_style( 'video-thumbnails-settings-css', plugins_url( '/css/settings.css', VIDEO_THUMBNAILS_PATH . '/video-thumbnails.php' ), false, VIDEO_THUMBNAILS_VERSION );
			wp_enqueue_script( 'video_thumbnails_settings', plugins_url( 'js/settings.js' , VIDEO_THUMBNAILS_PATH . '/video-thumbnails.php' ), array( 'jquery' ), VIDEO_THUMBNAILS_VERSION );
			wp_localize_script( 'video_thumbnails_settings', 'video_thumbnails_settings_language', array(
				'detection_failed'       => __( 'We were unable to find a video in the custom fields of your most recently updated post.', 'video-thumbnails' ),
				'working'                => __( 'Working...', 'video-thumbnails' ),
				'retest'                 => __( 'Retest', 'video-thumbnails' ),
				'ajax_error'             => __( 'AJAX Error:', 'video-thumbnails' ),
				'clear_all_confirmation' => __( 'Are you sure you want to clear all video thumbnails? This cannot be undone.', 'video-thumbnails' ),
			) );
			global $video_thumbnails;
			$provider_slugs = array();
			foreach ( $video_thumbnails->providers as $provider ) {
				$provider_slugs[] = $provider->service_slug;
			}
			wp_localize_script( 'video_thumbnails_settings', 'video_thumbnails_provider_slugs', array(
				'provider_slugs' => $provider_slugs
			) );
		}
	}

	function custom_field_detection_callback() {
		if ( current_user_can( 'manage_options' ) ) {
			echo $this->detect_custom_field();
		}
		die();
	}

	function detect_custom_field() {
		global $video_thumbnails;
		$latest_post = get_posts( array(
			'posts_per_page'  => 1,
			'post_type'  => $this->options['post_types'],
			'orderby' => 'modified',
		) );
		$latest_post = $latest_post[0];
		$custom = get_post_meta( $latest_post->ID );
		foreach ( $custom as $name => $values ) {
			foreach ($values as $value) {
				if ( $video_thumbnails->get_first_thumbnail_url( $value ) ) {
					return $name;
				}
			}
		}
	}

	function ajax_clear_all_callback() {
		if ( !current_user_can( 'manage_options' ) ) die();
		if ( wp_verify_nonce( $_POST['nonce'], 'clear_all_video_thumbnails' ) ) {
			global $wpdb;
			// Clear images from media library
			$media_library_items = get_posts( array(
				'showposts'  => -1,
				'post_type'  => 'attachment',
				'meta_key'   => 'video_thumbnail',
				'meta_value' => '1',
				'fields'     => 'ids'
			) );
			foreach ( $media_library_items as $item ) {
				wp_delete_attachment( $item, true );
			}
			echo '<p><span style="color:green">&#10004;</span> ' . sprintf( _n( '1 attachment deleted', '%s attachments deleted', count( $media_library_items ), 'video-thumbnails' ), count( $media_library_items ) ) . '</p>';
			// Clear custom fields
			$custom_fields_cleared = $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key='_video_thumbnail'" );
			echo '<p><span style="color:green">&#10004;</span> ' . sprintf( _n( '1 custom field cleared', '%s custom fields cleared', $custom_fields_cleared, 'video-thumbnails' ), $custom_fields_cleared ) . '</p>';
		} else {
			echo '<p><span style="color:red">&#10006;</span> ' . __( '<strong>Error</strong>: Could not verify nonce.', 'video-thumbnails' ) . '</p>';
		}

		die();
	}

	function get_file_hash( $url ) {
		$response = wp_remote_get( $url );
		if( is_wp_error( $response ) ) {
			$result = false;
		} else {
			$result = md5( $response['body'] );
		}
		return $result;
	}

	function provider_test_callback() {

		if ( !current_user_can( 'manage_options' ) ) die();

		global $video_thumbnails;

		?>
			<table class="widefat">
				<thead>
					<tr>
						<th><?php _e( 'Name', 'video-thumbnails' ); ?></th>
						<th><?php _e( 'Pass/Fail', 'video-thumbnails' ); ?></th>
						<th><?php _e( 'Result', 'video-thumbnails' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$provider = $video_thumbnails->providers[$_POST['provider_slug']];
				foreach ( $provider->get_test_cases() as $test_case ) {
					echo '<tr>';
					echo '<td><strong>' . $test_case['name'] . '</strong></td>';
					$markup = apply_filters( 'the_content', $test_case['markup'] );
					$result = $video_thumbnails->get_first_thumbnail_url( $markup );
					if ( is_wp_error( $result ) ) {
						$error_string = $result->get_error_message();
						echo '<td style="color:red;">&#10007; ' . __( 'Failed', 'video-thumbnails' ) . '</td>';
						echo '<td>';
						echo '<div class="error"><p>' . $error_string . '</p></div>';
						echo '</td>';
						$failed++;
					} else {
						$result_hash = false;
						if ( $result == $test_case['expected'] ) {
							$matched = true;
						} else {
							$result_hash = $this->get_file_hash( $result );
							$matched = ( $result_hash == $test_case['expected_hash'] ? true : false );
						}
						
						if ( $matched ) {
							echo '<td style="color:green;">&#10004; ' . __( 'Passed', 'video-thumbnails' ) . '</td>';
						} else {
							echo '<td style="color:red;">&#10007; ' . __( 'Failed', 'video-thumbnails' ) . '</td>';
						}
						echo '<td>';
						if ( $result ) {
							echo '<a href="' . $result . '">' . __( 'View Image', 'video-thumbnails' ) . '</a>';
						}
						if ( $result_hash ) {
							echo ' <code>' . $result_hash . '</code>';
						}
						echo '</td>';
					}
					echo '</tr>';
				} ?>
				<tbody>
			</table>
		<?php die();
	} // End provider test callback

	function image_download_test_callback() {

		if ( !current_user_can( 'manage_options' ) ) die();

		// Try saving 'http://img.youtube.com/vi/aKAGU2jkaNg/maxresdefault.jpg' to media library
		$attachment_id = Video_Thumbnails::save_to_media_library( 'http://img.youtube.com/vi/aKAGU2jkaNg/maxresdefault.jpg', 1 );
		if ( is_wp_error( $attachment_id ) ) {
			echo '<p><span style="color:red;">&#10006;</span> ' . $attachment_id->get_error_message() . '</p>';
		} else {
			update_post_meta( $attachment_id, 'video_thumbnail_test_image', '1' );
			$image = wp_get_attachment_image_src( $attachment_id, 'full' );
			echo '<img src="' . $image[0] . '" style="float:left; max-width: 250px; margin-right: 10px;">';
			echo '<p><span style="color:green;">&#10004;</span> ' . __( 'Attachment created', 'video-thumbnails' ) . '</p>';
			echo '<p><a href="' . get_edit_post_link( $attachment_id ) . '">' . __( 'View in Media Library', 'video-thumbnails' ) . '</a></p>';
			echo '<a href="' . $image[0] . '" target="_blank">' . __( 'View full size', 'video-thumbnails' ) . '</a>';
			echo '<span style="display:block;clear:both;"></span>';
		}

		die();
	} // End saving media test callback

	function delete_test_images_callback() {

		if ( !current_user_can( 'manage_options' ) ) die();

		global $wpdb;
		// Clear images from media library
		$media_library_items = get_posts( array(
			'showposts'  => -1,
			'post_type'  => 'attachment',
			'meta_key'   => 'video_thumbnail_test_image',
			'meta_value' => '1',
			'fields'     => 'ids'
		) );
		foreach ( $media_library_items as $item ) {
			wp_delete_attachment( $item, true );
		}
		echo '<p><span style="color:green">&#10004;</span> ' . sprintf( _n( '1 attachment deleted', '%s attachments deleted', count( $media_library_items ), 'video-thumbnails' ), count( $media_library_items ) ) . '</p>';

		die();
	} // End delete test images callback

	function markup_detection_test_callback() {

		if ( !current_user_can( 'manage_options' ) ) die();

		$new_thumbnail = null;

		global $video_thumbnails;

		$markup = apply_filters( 'the_content', stripslashes( $_POST['markup'] ) );

		$new_thumbnail = $video_thumbnails->get_first_thumbnail_url( $markup );

		if ( $new_thumbnail == null ) {
			// No thumbnail
			echo '<p><span style="color:red;">&#10006;</span> ' . __( 'No thumbnail found', 'video-thumbnails' ) . '</p>';
		} elseif ( is_wp_error( $new_thumbnail ) ) {
			// Error finding thumbnail
			echo '<p><span style="color:red;">&#10006;</span> ' . __( 'Error Details:', 'video-thumbnails' ) . ' ' . $new_thumbnail->get_error_message() . '</p>';
		} else {
			// Found a thumbnail
			$remote_response = wp_remote_head( $new_thumbnail );
			if ( is_wp_error( $remote_response ) ) {
				// WP Error trying to read image from remote server
				echo '<p><span style="color:red;">&#10006;</span> ' . __( 'Thumbnail found, but there was an error retrieving the URL.', 'video-thumbnails' ) . '</p>';
				echo '<p>' . __( 'Error Details:', 'video-thumbnails' ) . ' ' . $remote_response->get_error_message() . '</p>';
			} elseif ( $remote_response['response']['code'] != '200' ) {
				// Response code isn't okay
				echo '<p><span style="color:red;">&#10006;</span> ' . __( 'Thumbnail found, but it may not exist on the source server. If opening the URL below in your web browser returns an error, the source is providing an invalid URL.', 'video-thumbnails' ) . '</p>';
				echo '<p>' . __( 'Thumbnail URL:', 'video-thumbnails' ) . ' <a href="' . $new_thumbnail . '" target="_blank">' . $new_thumbnail . '</a>';
			} else {
				// Everything is okay!
				echo '<p><span style="color:green;">&#10004;</span> ' . __( 'Thumbnail found! Image should appear below.', 'video-thumbnails' ) . ' <a href="' . $new_thumbnail . '" target="_blank">' . __( 'View full size', 'video-thumbnails' ) . '</a></p>';
				echo '<p><img src="' . $new_thumbnail . '" style="max-width: 500px;"></p>';
			}
		}

		die();
	} // End markup detection test callback

	function initialize_options() {
		add_settings_section(  
			'general_settings_section',
			__( 'General Settings', 'video-thumbnails' ),
			array( &$this, 'general_settings_callback' ),
			'video_thumbnails'
		);
		$this->add_checkbox_setting(
			'save_media',
			__( 'Save Thumbnails to Media Library', 'video-thumbnails' ),
			__( 'Checking this option will download video thumbnails to your server', 'video-thumbnails' )
		);
		$this->add_checkbox_setting(
			'set_featured',
			__( 'Automatically Set Featured Image', 'video-thumbnails' ),
			__( 'Check this option to automatically set video thumbnails as the featured image (requires saving to media library)', 'video-thumbnails' )
		);
		// Get post types
		$post_types = get_post_types( null, 'names' );
		// Remove certain post types from array
		$post_types = array_diff( $post_types, array( 'attachment', 'revision', 'nav_menu_item' ) );
		$this->add_multicheckbox_setting(
			'post_types',
			__( 'Post Types', 'video-thumbnails' ),
			$post_types
		);
		$this->add_text_setting(
			'custom_field',
			__( 'Custom Field (optional)', 'video-thumbnails' ),
			'<a href="#" class="button" id="vt_detect_custom_field">' . __( 'Automatically Detect', 'video-thumbnails' ) . '</a> ' . __( 'Enter the name of the custom field where your embed code or video URL is stored.', 'video-thumbnails' )
		);
		register_setting( 'video_thumbnails', 'video_thumbnails', array( &$this, 'sanitize_callback' ) );
	}

	function sanitize_callback( $input ) {
		$current_settings = get_option( 'video_thumbnails' );
		$output = array();
		// General settings
		if ( !isset( $input['provider_options'] ) ) {
			foreach( $current_settings as $key => $value ) {
				if ( $key == 'version' OR $key == 'providers' ) {
					$output[$key] = $current_settings[$key];
				} elseif ( isset( $input[$key] ) ) {
					$output[$key] = $input[$key];
				} else {
					$output[$key] = '';
				}
			}
		}
		// Provider settings
		else {
			$output = $current_settings;
			unset( $output['providers'] );
			$output['providers'] = $input['providers'];
		}
		return $output;
	}  

	function general_settings_callback() {  
		echo '<p>' . __( 'These options configure where the plugin will search for videos and what to do with thumbnails once found.', 'video-thumbnails' ) . '</p>';  
	}

	function add_checkbox_setting( $slug, $name, $description ) {
		add_settings_field(
			$slug,
			$name,
			array( &$this, 'checkbox_callback' ),
			'video_thumbnails',
			'general_settings_section',
			array(
				'slug'        => $slug,
				'description' => $description
			)
		);
	}

	function checkbox_callback( $args ) {
		$html = '<label for="' . $args['slug'] . '"><input type="checkbox" id="' . $args['slug'] . '" name="video_thumbnails[' . $args['slug'] . ']" value="1" ' . checked( 1, $this->options[$args['slug']], false ) . '/> ' . $args['description'] . '</label>';
		echo $html;
	}

	function add_multicheckbox_setting( $slug, $name, $options ) {
		add_settings_field(
			$slug,
			$name,
			array( &$this, 'multicheckbox_callback' ),
			'video_thumbnails',
			'general_settings_section',
			array(
				'slug'    => $slug,
				'options' => $options
			)
		);
	}

	function multicheckbox_callback( $args ) {
		if ( is_array( $this->options[$args['slug']] ) ) {
			$selected_types = $this->options[$args['slug']];
		} else {
			$selected_types = array();
		}
		$html = '';
		foreach ( $args['options'] as $option ) {
			$checked = ( in_array( $option, $selected_types ) ? 'checked="checked"' : '' );
			$html .= '<label for="' . $args['slug'] . '_' . $option . '"><input type="checkbox" id="' . $args['slug'] . '_' . $option . '" name="video_thumbnails[' . $args['slug'] . '][]" value="' . $option . '" ' . $checked . '/> ' . $option . '</label><br>';			
		}
		echo $html;
	}

	function add_text_setting( $slug, $name, $description ) {
		add_settings_field(
			$slug,
			$name,
			array( &$this, 'text_field_callback' ),
			'video_thumbnails',
			'general_settings_section',
			array(
				'slug'          => $slug,
				'description' => $description
			)
		);
	}

	function text_field_callback( $args ) {
		$html = '<input type="text" id="' . $args['slug'] . '" name="video_thumbnails[' . $args['slug'] . ']" value="' . $this->options[$args['slug']] . '"/>';
		$html .= '<label for="' . $args['slug'] . '"> ' . $args['description'] . '</label>';
		echo $html;
	}

	function options_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'video-thumbnails' ) );
		}

		global $video_thumbnails;

		?><div class="wrap">

			<div id="icon-options-general" class="icon32"></div><h2><?php _e( 'Video Thumbnails Options', 'video-thumbnails' ); ?></h2>

			<?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general_settings'; ?> 
			<h2 class="nav-tab-wrapper">
				<a href="?page=video_thumbnails&tab=general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'General', 'video-thumbnails' ); ?></a>
				<a href="?page=video_thumbnails&tab=provider_settings" class="nav-tab <?php echo $active_tab == 'provider_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Providers', 'video-thumbnails' ); ?></a>
				<a href="?page=video_thumbnails&tab=mass_actions" class="nav-tab <?php echo $active_tab == 'mass_actions' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Mass Actions', 'video-thumbnails' ); ?></a>
				<a href="?page=video_thumbnails&tab=debugging" class="nav-tab <?php echo $active_tab == 'debugging' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Debugging', 'video-thumbnails' ); ?></a>
				<a href="?page=video_thumbnails&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Support', 'video-thumbnails' ); ?></a>
			</h2>

			<?php
			// Main settings
			if ( $active_tab == 'general_settings' ) {
			?>
			<h3><?php _e( 'Getting started', 'video-thumbnails' ); ?></h3>

			<p><?php _e( 'If your theme supports post thumbnails, just leave "Save Thumbnails to Media Library" and "Automatically Set Featured Image" enabled, then select what post types you\'d like scanned for videos.', 'video-thumbnails' ); ?></p>

			<p><?php _e( 'For more detailed instructions, check out the page for <a href="http://wordpress.org/extend/plugins/video-thumbnails/">Video Thumbnails on the official plugin directory</a>.', 'video-thumbnails' ); ?></p>

			<form method="post" action="options.php">  
				<?php settings_fields( 'video_thumbnails' ); ?>  
				<?php do_settings_sections( 'video_thumbnails' ); ?>            
				<?php submit_button(); ?>  
			</form>

			<?php
			// End main settings
			}
			// Provider Settings
			if ( $active_tab == 'provider_settings' ) {
			?>

			<form method="post" action="options.php">
				<input type="hidden" name="video_thumbnails[provider_options]" value="1" />
				<?php settings_fields( 'video_thumbnails' ); ?>  
				<?php do_settings_sections( 'video_thumbnails_providers' ); ?>            
				<?php submit_button(); ?>  
			</form>

			<?php
			// End provider settings
			}
			// Scan all posts
			if ( $active_tab == 'mass_actions' ) {
			?>
			<h3><?php _e( 'Scan All Posts', 'video-thumbnails' ); ?></h3>

			<p><?php _e( 'Scan all of your past posts for video thumbnails. Be sure to save any settings before running the scan.', 'video-thumbnails' ); ?></p>

			<p><a class="button-primary" href="<?php echo admin_url( 'tools.php?page=video-thumbnails-bulk' ); ?>"><?php _e( 'Scan Past Posts', 'video-thumbnails' ); ?></a></p>

			<h3><?php _e( 'Clear all Video Thumbnails', 'video-thumbnails' ); ?></h3>

			<p><?php _e( 'This will clear the video thumbnail field for all posts and delete any video thumbnail attachments. Note: This only works for attachments added using version 2.0 or later.', 'video-thumbnails' ); ?></p>

			<p><input type="submit" class="button-primary" onclick="clear_all_video_thumbnails('<?php echo wp_create_nonce( 'clear_all_video_thumbnails' ); ?>');" value="<?php esc_attr_e( 'Clear Video Thumbnails', 'video-thumbnails' ); ?>" /></p>

			<div id="clear-all-video-thumbnails-result"></div>

			<?php
			// End scan all posts
			}
			// Debugging
			if ( $active_tab == 'debugging' ) {
			?>

			<p><?php _e( 'Use these tests to help diagnose any problems. Please include results when requesting support.', 'video-thumbnails' ); ?></p>

			<div class="video-thumbnails-test closed">

				<a href="#" class="toggle-video-thumbnails-test-content show-hide-test-link"><span class="show"><?php _e( 'Show', 'video-thumbnails' ); ?></span> <span class="hide"><?php _e( 'Hide', 'video-thumbnails' ); ?></span></a>

				<a href="#" class="toggle-video-thumbnails-test-content title-test-link"><h3 class="test-title"><?php _e( 'Test Video Providers', 'video-thumbnails' ); ?></h3></a>

				<div class="video-thumbnails-test-content">

					<p><?php _e( 'This test automatically searches a sample for every type of video supported and compares it to the expected value. Sometimes tests may fail due to API rate limits.', 'video-thumbnails' ); ?></p>

					<p><input type="submit" class="button-primary video-thumbnails-test-button" id="test-all-video-thumbnail-providers" value="<?php esc_attr_e( 'Test Video Providers', 'video-thumbnails' ); ?>" /></p>

					<div id="provider-test-results" class="hidden">
						<?php
						foreach ( $video_thumbnails->providers as $provider ) {
							echo '<div id="' . $provider->service_slug . '-provider-test" class="single-provider-test-results">';
								echo '<h3>' . $provider->service_name . ' <input type="submit" data-provider-slug="' . $provider->service_slug . '" class="button-primary retest-video-provider video-thumbnails-test-button" value="' . esc_attr__( 'Waiting...', 'video-thumbnails' ) . '" /></h3>';
								echo '<div class="test-results"></div>';
							echo '</div>';
						}
						?>
					</div>

				</div><!-- /.video-thumbnails-test-content -->

			</div>
			<div class="video-thumbnails-test closed">

				<a href="#" class="toggle-video-thumbnails-test-content show-hide-test-link"><span class="show"><?php _e( 'Show', 'video-thumbnails' ); ?></span> <span class="hide"><?php _e( 'Hide', 'video-thumbnails' ); ?></span></a>

				<a href="#" class="toggle-video-thumbnails-test-content title-test-link"><h3 class="test-title"><?php _e( 'Test Markup for Video', 'video-thumbnails' ); ?></h3></a>

				<div class="video-thumbnails-test-content">

					<p><?php _e( 'Copy and paste an embed code below to see if a video is detected.', 'video-thumbnails' ); ?></p>

					<textarea id="markup-input" cols="50" rows="5"></textarea>

					<p><input type="submit" id="test-markup-detection" class="button-primary video-thumbnails-test-button" value="<?php esc_attr_e( 'Scan For Thumbnail', 'video-thumbnails' ); ?>" /></p>

					<div id="markup-test-result"></div>

				</div><!-- /.video-thumbnails-test-content -->

			</div>
			<div class="video-thumbnails-test closed">

				<a href="#" class="toggle-video-thumbnails-test-content show-hide-test-link"><span class="show"><?php _e( 'Show', 'video-thumbnails' ); ?></span> <span class="hide"><?php _e( 'Hide', 'video-thumbnails' ); ?></span></a>

				<a href="#" class="toggle-video-thumbnails-test-content title-test-link"><h3 class="test-title"><?php _e( 'Test Saving to Media Library', 'video-thumbnails' ); ?></h3></a>

				<div class="video-thumbnails-test-content">

					<p><?php _e( 'This test checks for issues with the process of saving a remote thumbnail to your local media library.', 'video-thumbnails' ); ?></p>

					<p><?php _e( 'Also be sure to test that you can manually upload an image to your site. If you\'re unable to upload images, you may need to <a href="http://codex.wordpress.org/Changing_File_Permissions">change file permissions</a>.', 'video-thumbnails' ); ?></p>

					<p>
						<input type="submit" id="test-video-thumbnail-saving-media" class="button-primary video-thumbnails-test-button" value="<?php esc_attr_e( 'Download Test Image', 'video-thumbnails' ); ?>" />
						<input type="submit" id="delete-video-thumbnail-test-images" class="button video-thumbnails-test-button" value="<?php esc_attr_e( 'Delete Test Images', 'video-thumbnails' ); ?>" />
					</p>

					<div id="media-test-result"></div>

				</div><!-- /.video-thumbnails-test-content -->

			</div>
			<div class="video-thumbnails-test closed">

				<a href="#" class="toggle-video-thumbnails-test-content show-hide-test-link"><span class="show"><?php _e( 'Show', 'video-thumbnails' ); ?></span> <span class="hide"><?php _e( 'Hide', 'video-thumbnails' ); ?></span></a>

				<a href="#" class="toggle-video-thumbnails-test-content title-test-link"><h3 class="test-title"><?php _e( 'Installation Information', 'video-thumbnails' ); ?></h3></a>

				<div class="video-thumbnails-test-content">

					<table class="widefat">
						<thead>
							<tr>
								<th></th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><strong><?php _e( 'WordPress Version', 'video-thumbnails' ); ?></strong></td>
								<td><?php echo get_bloginfo( 'version' ); ?></td>
								<td></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Video Thumbnails Version', 'video-thumbnails' ); ?></strong></td>
								<td><?php echo VIDEO_THUMBNAILS_VERSION; ?></td>
								<td></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Video Thumbnails Settings Version', 'video-thumbnails' ); ?></strong></td>
								<td><?php echo $this->options['version']; ?></td>
								<td></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'PHP Version', 'video-thumbnails' ); ?></strong></td>
								<td><?php echo PHP_VERSION; ?></td>
								<td></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Post Thumbnails', 'video-thumbnails' ); ?></strong></td>
								<td><?php if ( current_theme_supports( 'post-thumbnails' ) ) : ?><span style="color:green">&#10004;</span> <?php _e( 'Your theme supports post thumbnails.', 'video-thumbnails' ); ?><?php else: ?><span style="color:red">&#10006;</span> <?php _e( 'Your theme does not support post thumbnails, you\'ll need to make modifications or switch to a different theme. <a href="http://codex.wordpress.org/Post_Thumbnails">More info</a>', 'video-thumbnails' ); ?><?php endif; ?></td>
								<td></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Video Providers', 'video-thumbnails' ); ?></strong></td>
								<td>
									<?php global $video_thumbnails; ?>
										<?php $provider_names = array(); foreach ( $video_thumbnails->providers as $provider ) { $provider_names[] = $provider->service_name; }; ?>
									<strong><?php echo count( $video_thumbnails->providers ); ?></strong>: <?php echo implode( ', ', $provider_names ); ?>
								</td>
								<td></td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<th></th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
					</table>

				</div><!-- /.video-thumbnails-test-content -->

			</div>

			<?php
			// End debugging
			}
			// Support
			if ( $active_tab == 'support' ) {

				Video_Thumbnails::no_video_thumbnail_troubleshooting_instructions();

			// End support
			}
			?>

			<?php do_action( 'video_thumbnails/settings_footer' ); ?>

		</div><?php
	}

	public static function settings_footer() {
		?>
		<div style="width: 250px; margin: 20px 0; padding: 0 20px; background: #fff; border: 1px solid #dfdfdf; text-align: center;">
			<div>
				<p><?php _e( 'Support video thumbnails and unlock additional features', 'video-thumbnails' ); ?></p>
				<p><a href="https://refactored.co/plugins/video-thumbnails" class="button button-primary button-large"><?php _e( 'Go Pro', 'video-thumbnails' ); ?></a></p>
			</div>
		</div>
		<?php
	}

}

?>