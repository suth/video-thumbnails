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

if ( !class_exists( 'Refactored_Settings_Section_0_5_0' ) ) :

class Refactored_Settings_Section_0_5_0 {

	protected $page;
	protected $key;
	protected $name;
	protected $description;
	protected $callback;
	protected $fields;

	function __construct() {
        $this->fields = array();
	}

    public static function withKey($key)
    {
        $obj = new self;
        $obj = $obj->key($key);

        return $obj;
    }

    public function key($key)
    {
        $this->key = $key;

        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function page($page)
    {
        $this->page = $page;

        foreach ($this->getFields() as $field) {
            $field->page($this->getPage());
        }

        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function addFields($fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    public function addField($field)
    {
        $field->page($this->getPage());
        $field->section($this->getKey());

        $this->fields[] = $field;

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function __get($name)
    {
        foreach ($this->getFields() as $field) {
            if ($name == $field->getKey()) return $field;
        }
    }

    /**
     * Sanitizes input data
     *
     * @param array $input
     * @return array
     */
    public function sanitize($input)
    {
        $output = array();

		foreach ($this->getFields() as $field) {
            $output[$field->getKey()] = $field->sanitize(
                $input[$field->getKey()]
            );
		}

		return $output;
    }

    public function callback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    public function getCallback()
    {
        if ($this->callback) {
            return $this->callback;
        }

        return array(&$this, 'render');
    }

    public function render()
    {
        if ($this->getDescription()) {
            echo '<p>' . $this->getDescription() . '</p>';
        }
    }

    /**
     * Calls the WP do_action function
     * Uses action name with the format "rfs/$tag:page_key.section_key"
     *
     * @param string $tag
     */
    private function doAction($tag)
    {
        do_action('rfs/' . $tag . ':' . $this->getPage() . '.' . $this->getKey(), $this);
    }

    public function init()
    {
        $this->doAction('pre_init');

        add_settings_section(
            $this->getKey(),
            $this->getName(),
            $this->getCallback(),
            $this->getPage()
        );

        foreach ($this->getFields() as $field) {
            $field->init();
        }

        $this->doAction('post_init');
    }
}

endif;
