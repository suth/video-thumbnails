<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
class Video_Thumbnails {

	/**
	 * The loader responsible for maintaining and registering all hooks that power the plugin.
	 *
	 * @var Video_Thumbnails_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
     * @var string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
     * @var string $version
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 */
	public function __construct() {

		$this->plugin_name = 'video-thumbnails';
		$this->version = '3.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Video_Thumbnails_Loader. Orchestrates the hooks of the plugin.
	 * - Video_Thumbnails_i18n. Defines internationalization functionality.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/suth/refactored-settings/class-refactored-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/suth/refactored-settings/class-refactored-settings-section.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/suth/refactored-settings/class-refactored-settings-field.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-meta.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-media-library.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-meta-box.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-match.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-provider.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-providers.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-video-thumbnails-post-service.php';

		$this->loader = new Video_Thumbnails_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Video_Thumbnails_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 */
	private function set_locale() {

		$plugin_i18n = new Video_Thumbnails_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks
	 */
	private function define_hooks() {

        /** Settings **/
        $settings = Video_Thumbnails_Settings::get()->version( $this->get_version() );

        $this->loader->add_action( 'admin_init', $settings, 'init' );

        /** Meta Box **/
        $meta_box = new Video_Thumbnails_Meta_Box( $this->get_plugin_name(), $this->get_version(), $settings );

        $this->loader->add_action( 'admin_init', $meta_box, 'register' );

        /** Providers **/
        $providers = new Video_Thumbnails_Providers(
            $this->get_plugin_name(),
            $this->get_version(),
            $settings
        );

        $this->loader->add_action( 'init', $providers, 'init' );

        /** Post Service **/
        $post_service = new Video_Thumbnails_Post_Service(
            $this->get_plugin_name(),
            $this->get_version(),
            $settings,
            $providers
        );

        $this->loader->add_action( 'save_post', $post_service, 'save_post' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Video_Thumbnails_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
