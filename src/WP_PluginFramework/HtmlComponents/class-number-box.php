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

use WP_PluginFramework\HtmlElements\Input_Text;
use WP_PluginFramework\HtmlElements\P;

/**
 * Summary.
 *
 * Description.
 */
class Number_Box extends Input_Component {

	const DEFAULT_VALUE_TYPE = 'integer';

	/**
	 * Construction.
	 *
	 * @param null  $label
	 * @param null  $text
	 * @param null  $name
	 * @param array $properties
	 */
	public function __construct( $label = null, $text = null, $name = null, $properties = array() ) {
		if ( ! isset( $text ) ) {
			$text = '';
		}

		$attributes = null;

		$properties['label'] = $label;
		$properties['value'] = $text;
		$properties['name']  = $name;

		$properties['input_attributes'] = array(
			'type' => 'number',
			'step' => 1,
		);

		parent::__construct( $attributes, $properties );
	}

	public function set_value( $value ) {
		parent::set_property( 'value', $value );
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
		$input_attr          = $this->input_attributes;
		$input_attr['name']  = $this->name;
		$input_attr['value'] = $this->value;
		if ( isset( $this->type ) ) {
			$input_attr['type'] = $this->type;
		}

		$input = new Input_Text( $input_attr );

		$this->add_content( $input );
	}
}
