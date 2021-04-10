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

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Textarea;

/**
 * Summary.
 *
 * Description.
 */
class Text_Box extends Input_Component {

	const DEFAULT_VALUE_TYPE = 'string';

	/**
	 * Summary.
	 *
	 * @param null $label
	 * @param null $text
	 * @param null $name
	 */
	public function __construct( $label = null, $text = null, $name = null ) {
		$attributes = array();

		$properties['value'] = $text;
		$properties['label'] = $label;
		$properties['name']  = $name;

		parent::__construct( $attributes, $properties );
	}

	public function set_value( $value ) {
		parent::set_property( 'value', $value );
		$this->touched['value'] = true;
	}

	public function get_value() {
		return parent::get_property( 'value' );
	}

	public function set_text( $text ) {
		$this->set_value( $text );
	}

	public function get_text() {
		return $this->get_value();
	}

	public function create_content( $config = null ) {
		$attributes['name']  = $this->name;
		$attributes['class'] = 'large-text';
		$attributes['type']  = 'text';
		$attributes['rows']  = '8';

		$textarea = new Textarea( $this->value, $attributes );

		$this->add_content( $textarea );
	}
}
