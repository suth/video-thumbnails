<?php
/*  Copyright 2016 Sutherland Boswell  (email : hello@sutherlandboswell.com)

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

if ( !class_exists( 'Refactored_Settings_0_5_0' ) ) :

class Refactored_Settings_0_5_0 {

	protected $plugin_file;
	protected $version;
	protected $title;
	protected $key;
    protected $sections;
	protected $options;

	function __construct() {
        $this->sections = array();

        add_action('admin_menu', array(&$this, 'addOptionsPage'));
	}

    /**
     * Construct a new instance with given key
     *
     * @param string $key
     * @return Refactored_Settings
     */
    public static function withKey($key)
    {
        $obj = new self;

        return $obj->key($key);
    }

    /**
     * Set the key
     *
     * @param string $key
     * @return $this
     */
    public function key($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the key to use for the settings
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the version
     *
     * @param string $version
     * @return $this
     */
    public function version($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the title for the setting
     *
     * @param string $title
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the title of the setting
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the plugin's main file
     *
     * @param string $file
     * @return $this
     */
    public function pluginFile($file)
    {
        $this->plugin_file = $file;
        $this->registerActivationDeactivationHooks();

        return $this;
    }

    /**
     * Get the plugin's main file
     *
     * @return string
     */
    public function getPluginFile()
    {
        return $this->plugin_file;
    }

    /**
     * Registers the options page in the dashboard
     *
     * @return string
     */
    public function addOptionsPage()
    {
        return add_options_page(
			$this->getTitle() . ' Settings',
			$this->getTitle(),
			'manage_options',
			$this->getKey(),
			array( &$this, 'optionsPage' )
        );
    }

    /**
     * Calls the WP do_action function
     * Uses action name with the format "rfs/$tag:page_key"
     *
     * @param string $tag
     */
    private function doAction($tag)
    {
        do_action('rfs/' . $tag . ':' . $this->getKey(), $this);
    }

    public function init()
    {
        $this->doAction('pre_init');

        foreach ($this->getSections() as $section) {
            $section->init();
        }

        register_setting( $this->getKey(), $this->getKey(), array( &$this, 'sanitizeInput' ) );

		// Add options page to menu
		add_action( 'admin_menu', array( &$this, 'adminMenu' ) );

        $this->doAction('post_init');
    }

    /**
     * Displays the options page
     */
    public function optionsPage()
    {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

        ?>
        <div class="wrap">

			<div id="icon-options-general" class="icon32"></div><h2><?php echo $this->getTitle() ?> Settings</h2>

			<form method="post" action="options.php">

                <?php $this->doAction('before'); ?>

				<?php settings_fields( $this->getKey() ); ?> 
				<?php do_settings_sections( $this->getKey() ); ?> 

                <?php $this->doAction('after'); ?>

				<?php submit_button(); ?> 

			</form>

        </div>
        <?php
    }

    /**
     * Registers the activation/deactivation hook link to the plugin file
     */
    protected function registerActivationDeactivationHooks()
    {
		register_activation_hook( $this->getPluginFile(), array( &$this, 'pluginActivation' ) );
		register_deactivation_hook( $this->getPluginFile(), array( &$this, 'pluginDeactivation' ) );
    }

    /**
     * Set up options when plugin is activated
     */
    public function pluginActivation()
    {
        $this->doAction('activation');
	}

    /**
     * Remove options on deactivation
     */
    public function pluginDeactivation()
    {
        $this->doAction('deactivation');
		delete_option($this->getKey());
	}

    /**
     * Adds a section to the settings
     *
     * @param Refactored_Settings_Section $section
     * @return $this
     */
    public function addSection($section)
    {
        $section = $section->page($this->getKey());
        $this->sections[] = $section;

        return $this;
    }

    /**
     * Adds multiple sections to the settings
     *
     * @param array $sections
     * @return $this
     */
    public function addSections($sections)
    {
        foreach ($sections as $section) {
            $this->addSection($section);
        }

        return $this;
    }

    /**
     * Get the sections
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    public function __get($name)
    {
        $output = null;
        foreach ($this->getSections() as $section) {
            if ($name == $section->getKey()) {
                $output = $section;
                break;
            }
        }
        return $output;
    }

    /**
     * Sanitizes input from the form
     *
     * @param array $input
     * @return array
     */
	public function sanitizeInput( $input ) {
        $output = array();

        if ($this->getVersion()) {
            $output['version'] = $this->getVersion();
        }

		foreach ($this->getSections() as $section) {
            $output[$section->getKey()] = $section->sanitize(
                $input[$section->getKey()]
            );
		}

		return $output;
	}
}

endif;
