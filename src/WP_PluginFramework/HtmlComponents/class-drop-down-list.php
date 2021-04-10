<?php
/**
 * WordPress Plugin MVC Framework Library
 *
 * Copyright (C) 2018 Arild Hegvik.
 *
 * GNU LESSER GENERAL PUBLIC LICENSE (GNU LGPLv3)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package WP Plugin Framework
 */

namespace WP_PluginFramework\HtmlComponents;

use WP_PluginFramework\HtmlElements\Select;
use WP_PluginFramework\HtmlElements\Option;
use WP_PluginFramework\Utils\Debug_Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Summary.
 *
 * Description.
 */
class Drop_Down_List extends Input_Component {

	const DEFAULT_VALUE_TYPE = 'string';

	protected $items = array();
	protected $name  = null;

	/**
	 * Construction.
	 *
	 * @param null $items
	 * @param null $selected
	 * @param null $name
	 * @param null $attributes
	 */
	public function __construct( $items = null, $selected = null, $name = null, $attributes = null ) {
		$this->items = $items;
		$this->value = $selected;
		$this->name  = $name;

		$attributes['name'] = $name;

		parent::__construct( $attributes, null );
	}

	public function get_selected() {
	    return $this->value;
    }

    public function set_items( $items = array(), $selected=null ) {
        if (isset($this->id)) {
            $form_selector = $this->get_form_selector();
            $name          = $this->name;
            $selector      = $form_selector . ' select[name=' . $name . ']';
            $options = $this->create_options($items, $selected);
            $html     = $this->draw_options($options);
            $this->update_client_dom( $selector, 'html', array( $html ) );
        } else {
            Debug_Logger::write_debug_error( 'Component not registered.');
        }
    }

    protected function draw_options( $options )
    {
        $html = '';
        foreach ($options as $option) {
            $html .= $option->draw_html();
        }
        return $html;
    }

    protected function create_options( $items, $selected=null ) {
        $options = array();
        if(isset($items))
        {
            foreach ($items as $value => $item)
            {
                $attributes = array();

                $attributes['value'] = $value;

                if ($value === $this->value)
                {
                    $attributes['selected'] = 'selected';
                }

                $option = new Option($item, $attributes);

                array_push($options, $option);
            }
        }

	    return $options;
    }

	public function create_content( $config = null ) {
		$input_attr['name'] = $this->name;
		$select             = new Select( null, $input_attr );

		$options = $this->create_options( $this->items, $this->value );
        $select->set_content( $options );

		$this->add_content( $select );
	}
}
