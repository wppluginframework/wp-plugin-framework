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

use WP_PluginFramework\DataTypes\Data_Type;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Std_View extends Form_View {

	protected $headers = null;
	protected $footers = array();

	/**
	 * Construction.
	 *
	 * @param $id
	 * @param $controller
	 * @param array      $attributes
	 */
	public function __construct( $id, $controller, $attributes = array() ) {
		parent::__construct( $controller, $attributes );

		$this->id      = $id;
		$this->form_id = $id;

		$this->content_config['form_input_encapsulation'] = 'table';
		$this->div_wrapper                                = array( 'class' => 'wpf-controller' );
		$this->content_config['form_input_width']         = '400px';
	}

	public function add_header( $id, $component ) {
		$this->add_component( $id, $component );
		$this->headers[ $id ] = $component;
	}

	public function add_footer( $id, $component ) {
		$this->add_component( $id, $component );
		$this->footers[ $id ] = $component;
	}

	/**
	 * @param $data_object Data_Type
	 */
	public function add_data_object_input( $data_object ) {
		$id = $data_object->name;

		$html_component_class = $data_object->get_property( Data_Type::HTML_WIDGET_CLASS );

		if ( isset( $html_component_class ) ) {
			if ( ! strpos( $html_component_class, '\\' ) ) {
				/* If only class name set, use vendor's default HtmlComponents */
				$namespace            = explode( '\\', __NAMESPACE__ );
				$html_component_class = $namespace[0] . '\\HtmlComponents\\' . $html_component_class;
			}

			if ( isset( $html_component_class ) ) {
				$component = new $html_component_class();
				$component->set_properties( $data_object->get_properties() );
			}
		}

		$component->set_name( $id );
		$this->add_form_input( $id, $component );
	}

	public function add_model_record_inputs( $model, $submit_button = null ) {
		/*
		 * If model is empty, we need to initialize some data first.
		 * In this case data will have the default values.
		 */
		$model->init_data_if_empty();

		$data_objects = $model->get_data_object_record();

		foreach ( $data_objects as $data_object ) {
			$this->add_data_object_input( $data_object );
		}

		if ( isset( $submit_button ) ) {
			if ( is_string( $submit_button ) ) {
				$component = new Push_Button( $submit_button );
			} elseif ( is_object( $submit_button ) ) {
				$component = $submit_button;
			}

			$this->add_button( 'std_submit', $component );
		}

		$this->add_footer( 'status_bar_footer', new Status_Bar() );
	}

	public function create_content( $parameters = null, $wrapper = null ) {
		if ( ! isset( $wrapper ) ) {
			$wrapper = $this;
		}

		$wrapper = parent::create_content( $parameters, $wrapper );

		if ( isset( $this->headers ) ) {
			foreach ( array_reverse( $this->headers ) as $header ) {
				if ( is_string( $header ) ) {
					$header = new H( 1, $header );
					$wrapper->prepend_content( $header );
				} elseif ( is_object( $header ) ) {
					$wrapper->prepend_content( $header, $this->content_config );
				} else {
					Debug_Logger::write_debug_error( 'Unhandled component type ' . gettype( $header ) );
				}
			}
		}

		if ( isset( $this->footers ) ) {

			foreach ( $this->footers as $footer ) {
				if ( is_string( $footer ) ) {
					$footer = new P( $footer );
					$wrapper->add_content( $footer );
				} elseif ( is_object( $footer ) ) {
					$footer->create_content( $this->content_config );
					$wrapper->add_content( $footer );
				} else {
					Debug_Logger::write_debug_error( 'Unhandled component type ' . gettype( $footer ) );
				}
			}
		}

		return $wrapper;
	}
}
