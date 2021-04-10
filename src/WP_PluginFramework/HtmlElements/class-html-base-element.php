<?php
/**
 * WordPress Plugin MVC Framework Library
 *
 * Copyright (C) 2021 Arild Hegvik.
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

namespace WP_PluginFramework\HtmlElements;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Base\Base_Object;
use WP_PluginFramework\HtmlComponents\Html_Text;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Utils\Security_Filter;

/**
 * Summary.
 *
 * Description.
 */
abstract class Html_Base_Element extends Base_Object {

	/** @var array HtmlBaseElement */
	protected $contents = array();

	protected $tag           = null;
	protected $end_tag       = false;
	protected $attributes    = array();
	protected $ajax_response = array();
	protected $controller_id = null;
	protected $form_id       = null;
	protected $parent_view   = null;

	/**
	 * Construction.
	 *
	 * @param $tag
	 * @param $end_tag
	 * @param $content
	 * @param $attributes
	 * @param null       $properties
	 */
	public function __construct( $tag, $end_tag, $content, $attributes, $properties = null ) {
		$this->tag     = $tag;
		$this->end_tag = $end_tag;
		$this->set_content( $content );
		$this->attributes = $attributes;

		parent::__construct( $properties );
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 * @param $value
	 */
	public function set_attribute( $key, $value ) {
		$this->set_property_key_values( 'attributes', $key, $value );
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 *
	 * @return |null
	 */
	public function get_attribute( $key ) {
		return $this->get_property_key_values( 'attributes', $key );
	}

	/**
	 * Summary.
	 *
	 * @param $class
	 */
	public function add_class( $class ) {
		$this->set_property_key_values( 'attributes', 'class', $class );
	}

	/**
	 * Summary.
	 *
	 * @return |null
	 */
	public function get_class() {
		return $this->get_property_key_values( 'attributes', 'class' );
	}

	/**
	 * Summary.
	 *
	 * @return string
	 */
	public function get_class_string() {
		$classes = $this->get_property_key_values( 'attributes', 'class' );
		return implode( ' ', $classes );
	}

	/**
	 * Summary.
	 *
	 * @param $class
	 */
	public function remove_class( $class ) {
		$this->remove_property_key_values( 'attributes', 'class', $class );
	}

	/**
	 * Summary.
	 *
	 * @param $visibility
	 */
	public function set_visibility( $visibility ) {
		if ( $visibility ) {
			$this->set_attribute( 'style', 'visibility:visible;' );
		} else {
			$this->set_attribute( 'style', 'visibility:hidden;' );
		}
	}

	/**
	 * Summary.
	 *
	 * @param $display
	 */
	public function set_display( $display ) {
		$this->set_attribute( 'style', 'display:' . $display . ';' );
	}

	/**
	 * Summary.
	 *
	 * @param $id
	 */
	public function set_id( $id ) {
		$this->attributes['id'] = $id;
	}

	/**
	 * Summary.
	 *
	 * @param $controller_id
	 */
	public function set_controllerid( $controller_id ) {
		$this->controller_id = $controller_id;
	}

	/**
	 * Summary.
	 *
	 * @param $parent_view
	 */
	public function set_parent_view( $parent_view ) {
		$this->parent_view = $parent_view;
	}

	/**
	 * Summary.
	 *
	 * @param $form_id
	 */
	public function set_form_id( $form_id ) {
		$this->form_id = $form_id;
	}

	/**
	 * Summary.
	 *
	 * @param $name
	 */
	public function set_name( $name ) {
		$this->set_property( 'name', $name );
	}

	/**
	 * Summary.
	 *
	 * @return |null
	 */
	public function get_name() {
		return $this->get_property( 'name' );
	}

	/**
	 * Summary.
	 *
	 * @param null $config
	 */
	public function create_content( $config = null ) {   }

	/**
	 * Summary.
	 *
	 * @param $content
	 * @param null    $config
	 */
	public function set_content( $content, $config = null ) {
		if ( is_object( $content ) ) {
			if ( empty( $content->contents ) ) {
				$content->create_content( $config );
			}
		}

		parent::set_content( $content );
	}

	/**
	 * Summary.
	 *
	 * @param $content
	 * @param null    $config
	 */
	public function add_content( $content, $config = null ) {
		if ( is_object( $content ) ) {
			if ( empty( $content->contents ) ) {
				$content->create_content( $config );
			}
		}

		parent::add_content( $content );
	}

	/**
	 * Summary.
	 *
	 * @param $content
	 * @param null    $config
	 */
	public function prepend_content( $content, $config = null ) {
		if ( is_object( $content ) ) {
			if ( ! isset( $content->tag ) || empty( $content->contents ) ) {
				$content->create_content( $config );
			}
		}

		parent::prepend_content( $content );
	}

	/**
	 * Summary.
	 *
	 * @param $html
	 */
	public function set_html( $html ) {
		$html_content = new Html_Text( $html );
		$this->set_content( $html_content );
	}

	/**
	 * Add html text to content. This function avoid html elements from being escaped during write.
	 *
	 * @param string $html
	 */
	public function add_html( $html ) {
		$html_content = new Html_Text( $html );
		$this->add_content( $html_content );
	}

	/**
	 * Summary.
	 *
	 * @param $text
	 */
	public function set_text( $text ) {
		$this->set_content( $text );
	}

	/**
	 * Summary.
	 *
	 * @return string
	 */
	public function draw_html() {
		$html = '';

		foreach ( $this->contents as $content ) {
			$content_type = gettype( $content );
			switch ( $content_type ) {
				case 'object':
					$html .= $content->draw_html();
					break;

				case 'string':
					$html .= esc_html( $content );
					break;

				case 'integer':
				case 'double':
					$html .= strval( $content );
					break;

				default:
					$html .= 'Error: Undefined html content type';
					break;
			}
		}

		if ( isset( $this->tag ) ) {
			$start_html = '<' . $this->tag;
			if ( isset( $this->attributes ) ) {
				foreach ( $this->attributes as $attribute => $value ) {
					if ( is_array( $value ) ) {
						switch ( $attribute ) {
							case 'class':
								$value = implode( ' ', $value );
								break;

							case 'style':
								$value = implode( ';', $value );
								break;

							default:
								$value = '';
								Debug_Logger::write_debug_error( 'Missing array handler for ' . $attribute );
								break;
						}
					}

					$start_html .= ' ' . $attribute . '="' . esc_attr( $value ) . '"';
				}
			}

			if ( $this->end_tag ) {
				$html = $start_html . '>' . $html . '</' . $this->tag . '>';
			} else {
				if ( '' !== $html ) {
					/* We can not have inner html when closing tag don't exist. */
					die();
				}

				$html .= $start_html . '/>';
			}
		}

		return $html;
	}

	/**
	 * Summary.
	 *
	 * @param $work_items
	 */
	public function add_ajax_response( $work_items ) {
		if ( ! empty( $work_items ) ) {
			$this->ajax_response = array_merge( $this->ajax_response, $work_items );
		}
	}

	/**
	 * Summary.
	 *
	 * @return array
	 */
	public function get_ajax_response() {
		return $this->ajax_response;
	}

	/**
	 * Summary.
	 *
	 * @param $selector
	 * @param $method
	 * @param null     $arguments
	 */
	public function update_client_dom( $selector, $method, $arguments = null ) {
		if ( isset( $arguments ) ) {
			if ( ! is_array( $arguments ) ) {
				$arguments = array( $arguments );
			}
		}

		$work_item              = array();
		$work_item['type']      = 'html';
		$work_item['selector']  = $selector;
		$work_item['method']    = $method;
		$work_item['arguments'] = $arguments;
		$work_items[]           = $work_item;
		$this->add_ajax_response( $work_items );
	}

	/**
	 * Summary.
	 *
	 * @return string|null
	 */
	protected function get_view_selector() {
		if ( isset( $this->controller_id ) ) {
			return 'div#' . $this->controller_id;
		}
		return null;
	}

	/**
	 * Summary.
	 *
	 * @return string|null
	 */
	protected function get_form_selector() {
		$selector = $this->get_view_selector();
		if ( ! $selector ) {
			$selector = '';
		}

		if ( isset( $this->form_id ) ) {
			if ( $selector ) {
				$selector .= ' ';
			}
			$selector .= 'form#' . $this->form_id;
		}

		return $selector;
	}

	/**
	 * Summary.
	 *
	 * @param $event
	 * @param $event_type
	 * @param $event_source
	 *
	 * @return bool
	 */
	public function check_event_exist( $event, $event_type, $event_source ) {
		return false;
	}
}
