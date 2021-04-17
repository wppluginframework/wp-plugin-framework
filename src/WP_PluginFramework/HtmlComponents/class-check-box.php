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

use WP_PluginFramework\HtmlElements\Fieldset;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\Input_Checkbox;
use WP_PluginFramework\HtmlElements\Legend;
use WP_PluginFramework\HtmlElements\Span;

/**
 * Summary.
 *
 * Description.
 */
class Check_Box extends Input_Component {

	const DEFAULT_VALUE_TYPE = 'integer';

	protected $items = array();

	/** @var string Label for checkbox, showed behind input. */
	protected $label;

	protected $legend;

	/**
	 * Construction.
	 *
	 * @param null $legend
	 * @param int   $checked
	 * @param null  $name
	 * @param array $attributes
	 */
	public function __construct( $legend = null, $checked = 0, $name = null, $attributes = array() ) {
		/* TODO Must change value and name to hold aan array of items.*/
		$properties['legend'] = $legend;
		$properties['value'] = $checked;
		$properties['name']  = $name;

		$properties['input_attributes'] = array(
			'class' => array( 'wpf-checkbox-input' ),
		);

		parent::__construct( $attributes, $properties );
	}

	/**
	 * Summary.
	 *
	 * @param $value
	 */
	public function set_value( $value ) {
		if ( gettype( $value ) === 'string' ) {
			$value = intval( $value );
		}

		parent::set_value( $value );
	}

	/**
	 * Summary.
	 *
	 * @param $checked
	 */
	public function set_checked( $checked ) {
		$this->set_value( $checked );
	}

	/**
	 * Summary.
	 *
	 * @return string
	 */
	public function get_checked() {
		return $this->get_value();
	}

	/**
	 * Summary.
	 */
	public function pick_client_side_value($client_side_values) {
		$value = parent::pick_client_side_value($client_side_values);
		if( !isset( $value ) ) {
			/* Checkboxes may not send anything when not checked. */
			$value = 0;
		}
		return $value;
	}

	/**
	 * Summary.
	 *
	 * @param null $config
	 */
	public function create_content( $config = null ) {
		$input_attr = $this->input_attributes;
		$input_attr['name'] = $this->id;
		$input_attr['value'] = '1';
		if ( 1 === $this->value ) {
			$input_attr['checked'] = 'checked';
		}

		$input = new Input_Checkbox( $input_attr );

		$span = new Span( $this->label );
		$legend = new Legend( $span, array( 'class' => 'screen-reader-text' ) );

		$fieldset = new Fieldset( $legend );

		$label = new Label( $input, array( 'for' => $this->value ) );
		if ( $this->legend ) {
			$label->add_content( $this->legend );
		}

		$fieldset->add_content( $label );

		$this->add_content( $fieldset );
	}
}

