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

use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlElements\Input_Text;
use WP_PluginFramework\HtmlElements\P;

/**
 * Summary.
 *
 * Description.
 */
class Color_Picker extends Input_Component {

	const DEFAULT_VALUE_TYPE = 'string';

	protected $input_attributes = array();

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

		$attributes = array();

		$properties['label'] = $label;
		$properties['value'] = $text;
		$properties['name']  = $name;

		parent::__construct( $attributes, $properties );
	}

	/**
	 * Summary.
	 *
	 * @param $value
	 */
	public function set_value( $value ) {
		parent::set_property( 'value', $value );
	}

	public function get_value() {
		return parent::get_property( 'value' );
	}

	/**
	 * Summary.
	 *
	 * @param $text
	 */
	public function set_text( $text ) {
		$this->set_value( $text );
	}

	/**
	 * Summary.
	 *
	 * @return string|null
	 */
	public function get_text() {
		return $this->get_value();
	}

	/**
	 * Summary.
	 *
	 * @param null $config
	 */
	public function create_content( $config = null ) {
		$input_attr          = $this->input_attributes;
		$input_attr['name']  = $this->name;
		$input_attr['value'] = $this->value;

		if ( isset( $this->type ) ) {
			$input_attr['type'] = $this->type;
		}

		$input = new Input_Text( $input_attr );

		$div_color_show_attr          = array();
		$div_color_show_attr['class'] = 'wpf-color-show';
		$div_color_show_attr['style'] = 'background-color:' . $this->value . ';';

		$color_show = new Div( '&nbsp;', $div_color_show_attr );

		$div_color_picker_attr          = array();
		$div_color_picker_attr['class'] = 'wpf-color-picker';

		$color_picker = new Div( $input, $div_color_picker_attr );
		$color_picker->add_content( $color_show );

		$this->add_content( $color_picker );
	}
}
