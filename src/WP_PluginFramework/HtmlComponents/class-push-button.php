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

use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\Views\Form_View;
use WP_PluginFramework\HtmlElements\Button;
use WP_PluginFramework\HtmlElements\Form;
use WP_PluginFramework\HtmlElements\Input_Hidden;

/**
 * Summary.
 *
 * Description.
 */
class Push_Button extends Html_Base_Component {

	const METHOD_AJAX = 'ajax';
	const METHOD_POST = 'post';
	const METHOD_GET  = 'get';

	protected $method        = self::METHOD_AJAX;
	protected $label         = null;
	protected $primary       = false;
	protected $hidden_fields = array();

	private $add_parents_hidden_field = true;

	/**
	 * Construction.
	 *
	 * @param null  $label
	 * @param null  $method
	 * @param array $attributes
	 */
	public function __construct( $label = null, $method = null, $attributes = array() ) {
		$this->label = $label;

		if ( isset( $method ) ) {
			$this->method = $method;
		}

		switch ( $this->method ) {
			case self::METHOD_AJAX:
				if ( ! isset( $attributes['class'] ) ) {
					$attributes['class'] = array( 'wp_plugin_framework_ajax_button' );
				}
				$attributes['type'] = 'button';
				break;

			case self::METHOD_POST:
				$attributes['type']  = 'submit';
				$attributes['class'] = array( 'wp_plugin_framework_post_button' );
				break;

			case self::METHOD_GET:
				$attributes['type']  = 'get';
				$attributes['class'] = array( 'wp_plugin_framework_get_button' );
				break;
		}

		$properties = null;

		parent::__construct( $attributes, $properties );
	}

	public function set_id( $id ) {
		$this->id = $id;

		$this->attributes['name']  = '_event';
		$this->attributes['value'] = $id;
	}

	public function check_event_exist( $event, $event_type, $event_source ) {
		if ( ( $event === $this->id ) && ( 'click' === $event_type ) ) {
			return true;
		}

		return false;
	}

	public function set_primary( $primary ) {
		$this->primary = $primary;
	}

	public function get_primary() {
		return $this->primary;
	}

	public function add_hidden_fields( $name, $value ) {
		$attributes            = array(
			'name'  => $name,
			'value' => $value,
		);
		$this->hidden_fields[] = $attributes;
	}

	public function set_hidden_field( $name, $value ) {
		$n = count( $this->hidden_fields );
		for ( $i = 0; $i < $n; $i++ ) {
			if ( $this->hidden_fields[ $i ]['name'] === $name ) {
				$this->hidden_fields[ $i ]['value'] = $value;
			}
		}
	}

	public function remove_hidden_fields( $name = null ) {
		if ( ! isset( $name ) ) {
			$this->hidden_fields            = array();
			$this->add_parents_hidden_field = false;
		}
	}

	public function create_content( $config = null ) {
		if ( $this->primary ) {
			$this->add_class( 'button-primary' );
		}

		if ( ! isset( $config ) ) {
			/* If no config, draw a stand-alone button with wrapped in a form */
			if ( $this->add_parents_hidden_field ) {
				$hidden_fields = $this->parent_view->get_property( 'hidden_fields' );
				foreach ( $hidden_fields as $hidden_field_attributes ) {
					$this->add_hidden_fields( $hidden_field_attributes['name'], $hidden_field_attributes['value'] );
				}
			}

			$attributes = array();
			switch ( $this->method ) {
				case Form_View::SEND_METHOD_AJAX:
					break;

				case Form_View::SEND_METHOD_POST:
					$attributes['method'] = 'post';
					$this->add_hidden_fields( 'action', Plugin_Container::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER );
					$this->add_hidden_fields( '_event_type', 'click' );
					break;

				case Form_View::SEND_METHOD_GET:
					$attributes['method'] = 'get';
					$this->add_hidden_fields( 'action', Plugin_Container::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER );
					$this->add_hidden_fields( '_event_type', 'click' );
					break;
			}

			$form = new Form( null, $attributes );

			foreach ( $this->hidden_fields as $hidden_field_attributes ) {
				$hidden_input = new Input_Hidden( $hidden_field_attributes );
				$form->add_content( $hidden_input );
			}

			$button = new Button( $this->label, $this->attributes );
			$form->add_content( $button );

			$this->add_content( $form );
		} else {
			$button = new Button( $this->label, $this->attributes );
			$this->add_content( $button );
		}
	}
}
