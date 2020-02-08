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

namespace WP_PluginFramework\Views;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlComponents\Html_Base_Component;
use WP_PluginFramework\HtmlElements\Html_Base_Element;
use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
abstract class View extends Html_Base_Element {

	protected $id             = null;
	protected $controller     = null;
	protected $all_components = array();
	protected $call_backs     = array();
	protected $div_wrapper    = null;

	/**
	 * Construction.
	 *
	 * @param $controller
	 * @param null       $content
	 * @param null       $attributes
	 */
	public function __construct( $controller, $content = null, $attributes = null ) {
		parent::__construct( null, null, $content, $attributes );
		$this->controller = $controller;
	}

	public function add_div_wrapper( $attributes ) {
		$this->div_wrapper = $attributes;
	}

	public function remove_div_wrapper() {
		$this->div_wrapper = null;
	}

	/**
	 * @param $id
	 * @param $component Html_Base_Component
	 */
	protected function add_component( $id, $component ) {
		if ( isset( $id ) && is_object( $component ) ) {
			$component->set_id( $id );
			$component->set_controllerid( $this->id );
			$component->set_parent_view( $this );

			if ( ! isset( $this->$id ) ) {
				$this->$id                   = $component;
				$this->all_components[ $id ] = $component;
			} else {
				Debug_Logger::write_debug_error( 'Duplicate component Id ' . $id );
			}
		}
	}

	public function remove() {
		$selector = '#' . $this->id;
		$method   = 'remove';
		$this->update_client_dom( $selector, $method );
	}

	public function draw_view( $parameters = null ) {
		$this->create_content( $parameters );
		return $this->draw_html();
	}

	public function create_content( $parameters = null, $wrapper = null ) {
		if ( ! isset( $wrapper ) ) {
			$wrapper = $this;
		}

		if ( isset( $this->div_wrapper ) ) {
			$attributes = $this->div_wrapper;

			if ( isset( $this->id ) ) {
				$attributes['id'] = $this->id;
			}

			$div_wrapper = new Div( null, $attributes );
			$wrapper->add_content( $div_wrapper );
			$wrapper = $div_wrapper;
		}

		return $wrapper;
	}

	public function register_callback( $event ) {
		$this->call_backs[] = $event;
	}

	public function update_client_add_callback( $event, $arguments, $controller, $view, $wp_nonce ) {
		if ( isset( $arguments ) ) {
			if ( ! is_array( $arguments ) ) {
				$arguments = array( $arguments );
			}
		}

		$work_item               = array();
		$work_item['type']       = 'wp_framework_ajax_callback';
		$work_item['action']     = Plugin_Container::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER;
		$work_item['controller'] = $controller;
		$work_item['view']       = $view;
		$work_item['wpnonce']    = $wp_nonce;
		$work_item['event_type'] = 'callback';
		$work_item['event']      = $event;
		$work_item['arguments']  = $arguments;
		$work_item['selector']   = '#' . $this->id;
		$work_items[]            = $work_item;
		$this->add_ajax_response( $work_items );
	}

	public function update_client_call_function( $js_function, $arguments ) {
		if ( isset( $arguments ) ) {
			if ( ! is_array( $arguments ) ) {
				$arguments = array( $arguments );
			}
		}

		$work_item              = array();
		$work_item['type']      = 'js_call_function';
		$work_item['function']  = $js_function;
		$work_item['arguments'] = $arguments;
		$work_items[]           = $work_item;
		$this->add_ajax_response( $work_items );
	}

	public function check_event_exist( $event, $event_type, $event_source ) {
		foreach ( $this->all_components as $component ) {
			if ( $component->check_event_exist( $event, $event_type, $event_source ) ) {
				return true;
			}
		}

		if ( 'callback' === $event_type ) {
			foreach ( $this->call_backs as $call_back ) {
				if ( $event === $call_back ) {
					return true;
				}
			}
		}

		return false;
	}

	public function get_ajax_response() {
		foreach ( $this->all_components as $component ) {
			if ( isset( $component ) ) {
				$work_items = $component->get_ajax_response();
				if ( ! empty( $work_items ) ) {
					$this->add_ajax_response( $work_items );
				}
			}
		}

		return $this->ajax_response;
	}

}
