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
use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\HtmlElements\Form;
use WP_PluginFramework\HtmlElements\Input_Hidden;
use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\Hr;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Form_View extends View {

	protected $form_id = null;
	/** @var array $form_inputs */
	protected $form_inputs           = array();
	protected $buttons               = array();
	protected $input_form_categories = array();
	protected $show_form             = true;

	const SEND_METHOD_AJAX = 'ajax';
	const SEND_METHOD_POST = 'post';
	const SEND_METHOD_GET  = 'get';

	private $method = 'ajax';

	protected $hidden_fields         = array();
	protected $content_config        = array();
	protected $form_table_tr_wrapper = null;

	/**
	 * Construction.
	 *
	 * @param $controller
	 * @param array      $attributes
	 */
	public function __construct( $controller, $attributes = array() ) {
		parent::__construct( $controller, null, $attributes );

		$this->content_config['form_input_layout']           = 'single_column_table';
		$this->content_config['form_placeholder_table_attr'] = array( 'class' => 'wpf-table-placeholder' );
		$this->content_config['form_placeholder_tr_attr']    = null;
		$this->content_config['form_placeholder_th_attr']    = array( 'class' => 'wpf-table-placeholder-input' );
		$this->content_config['form_placeholder_td_attr']    = array( 'class' => 'wpf-table-placeholder-input' );

		$my_class = get_called_class();
	}

	public function set_method( $method ) {
		$this->method = $method;
	}

	/**
	 * @param $id
	 * @param $component Html_Base_Component
	 */
	public function add_form_input( $id, $component ) {
		$this->add_component( $id, $component );
		$this->form_inputs[ $id ] = $component;

		/* If no name is set for a form input, use the id as name*/
		if ( $this->form_inputs[ $id ]->get_property( 'name' ) === null ) {
			$this->form_inputs[ $id ]->set_property( 'name', $id );
		}

		$component->set_form_id( $this->form_id );
	}

	public function add_input_form_category( $category ) {
		$this->input_form_categories[] = $category;
	}

	public function add_button( $id, $component ) {
		$this->add_component( $id, $component );
		$this->buttons[ $id ] = $component;
	}

	public function add_hidden_fields( $name, $value ) {
		$attributes            = array(
			'name'  => $name,
			'value' => $value,
		);
		$this->hidden_fields[] = $attributes;
	}

	public function get_form_input_component( $name = null ) {
		if ( ! isset( $name ) ) {
			return $this->form_inputs;
		} else {
			foreach ( $this->form_inputs as $form_input ) {
				if ( $name === $form_input->get_property( 'name' ) ) {
					return $form_input;
				}
			}
		}

		return null;
	}

	public function clear_checkbox_before_form_post() {
		foreach ( $this->form_inputs as $component ) {
			$component->clear_checkbox_before_form_post();
		}
	}

	/**
	 * @param array $inputs string List of component names
	 */
	public function show_input_error_indications( $inputs = null ) {
		if ( isset( $inputs ) ) {
			foreach ( $inputs as $component_name => $value ) {
				if ( isset( $this->form_inputs[ $component_name ] ) ) {
					$this->form_inputs[ $component_name ]->show_input_error_indication();
				} else {
					Debug_Logger::write_debug_error( 'Non-existing Html Component ' . $component_name );
				}
			}
		} else {
			foreach ( $this->form_inputs as $component ) {
				$component->show_input_error_indication();
			}
		}
	}

	public function hide_input_error_indications( $inputs = null ) {
		if ( isset( $inputs ) ) {
			foreach ( $inputs as $component_name => $value ) {
				if ( isset( $this->form_inputs[ $component_name ] ) ) {
					$this->form_inputs[ $component_name ]->hide_input_error_indication();
				} else {
					Debug_Logger::write_debug_error( 'Non-existing Html Component ' . $component_name );
				}
			}
		} else {
			foreach ( $this->form_inputs as $component ) {
				$component->hide_input_error_indication();
			}
		}
	}

	public function set_values( $values ) {
		if ( isset( $values ) ) {
			foreach ( $values as $key => $value ) {
				foreach ( $this->form_inputs as $component ) {
					/* TODO optimize this */
					$name = $component->get_name();

					if ( $key === $name ) {
						$component->set_value( $value );
					}
				}
			}
		}
	}

	public function get_values() {
		$values = array();

		foreach ( $this->form_inputs as $component ) {
			$name = $component->get_name();
			if ( isset( $name ) ) {
				$values[ $name ] = $component->get_value();
			}
		}

		return $values;
	}

	public function create_content( $parameters = null, $wrapper = null ) {
		if ( ! isset( $wrapper ) ) {
			$wrapper = $this;
		}

		$wrapper = parent::create_content( $parameters, $wrapper );
		if ( $this->show_form ) {
			$attributes = array();
			switch ( $this->method ) {
				case self::SEND_METHOD_AJAX:
					break;

				case self::SEND_METHOD_POST:
					$attributes['method'] = 'post';
					$this->add_hidden_fields( 'action', Plugin_Container::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER );
					$this->add_hidden_fields( '_event_type', 'click' );
					break;

				case self::SEND_METHOD_GET:
					$attributes['method'] = 'get';
					$this->add_hidden_fields( 'action', Plugin_Container::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER );
					$this->add_hidden_fields( '_event_type', 'click' );
					break;
			}

			if ( isset( $this->form_id ) ) {
				$attributes['id'] = $this->form_id;
			}

			$form = new Form( null, $attributes );
			$wrapper->add_content( $form );

			foreach ( $this->hidden_fields as $hidden_field_attributes ) {
				$hidden_input = new Input_Hidden( $hidden_field_attributes );
				$form->add_content( $hidden_input );
			}

			if ( isset( $this->content_config['form_input_encapsulation'] ) && ( $this->content_config['form_input_encapsulation'] ) === 'table' ) {
				$td_form_attr          = $this->content_config['form_placeholder_td_attr'];
				$td_form_attr['width'] = $this->content_config['form_input_width'];

				$td_form                     = new Td( null, $td_form_attr );
				$td_spacing                  = new Td( null, $this->content_config['form_placeholder_td_attr'] );
				$this->form_table_tr_wrapper = new Tr( $td_form, $this->content_config['form_placeholder_tr_attr'] );
				$this->form_table_tr_wrapper->add_content( $td_spacing );
				$table_wrapper = new Table( $this->form_table_tr_wrapper, $this->content_config['form_placeholder_table_attr'] );
				$form->add_content( $table_wrapper );
			} else {
				$td_form = $form;
			}

			if ( isset( $parameters ) ) {
				foreach ( $this->form_inputs as $component ) {
					if ( isset( $component ) ) {
						if ( isset( $component->name ) ) {
							$name = $component->name;
							if ( isset( $parameters[ $name ] ) ) {
								$component->set_value( $parameters[ $name ] );
							}
						}
					}
				}
			}

			if ( empty( $this->input_form_categories ) ) {
				$table = new Table( null, $this->content_config['form_placeholder_table_attr'] );
				foreach ( $this->form_inputs as $component ) {
					if ( isset( $component ) ) {
						$table->add_content( $component, $this->content_config );
					}
				}
				$td_form->add_content( $table );

				if ( isset( $this->buttons ) ) {
					$first_button     = true;
					$button_paragraph = new P( null, array( 'class' => 'wpf-table-placeholder submit' ) );
					foreach ( $this->buttons as $button ) {
						if ( isset( $button ) ) {
							if ( ! $first_button ) {
								$button_paragraph->add_content( '&nbsp;&nbsp;' );
							}
							$first_button = false;

							$button_paragraph->add_content( $button, $this->content_config );
						}
					}
					$td_form->add_content( $button_paragraph );
				}
			} else {
				$first_category = true;
				foreach ( $this->input_form_categories as $category ) {
					if ( false === $first_category ) {
						$divider = new Hr();
						$td_form->add_content( $divider );
					}
					$first_category = false;

					$header = new H( 2, $category['header'] );
					$td_form->add_content( $header );
					if ( isset( $category['description'] ) ) {
						$description = new P( $category['description'] );
						$td_form->add_content( $description );
					}

					$table = new Table( null, $this->content_config['form_placeholder_table_attr'] );
					foreach ( $this->form_inputs as $component ) {
						if ( isset( $component ) ) {
							if ( isset( $component->category ) && ( $component->category === $category['name'] ) ) {
								$table->add_content( $component, $this->content_config );
							}
						}
					}
					$td_form->add_content( $table );

					if ( isset( $this->buttons ) ) {
						$first_button     = true;
						$button_paragraph = new P( null, array( 'class' => 'submit' ) );
						foreach ( $this->buttons as $button ) {
							if ( isset( $button ) ) {
								if ( ! $first_button ) {
									$button_paragraph->add_content( '&nbsp;&nbsp;' );
								}
								$first_button = false;

								$button_paragraph->add_content( $button, $this->content_config );
							}
						}
						$td_form->add_content( $button_paragraph );
					}
				}
			}
		}

		return $wrapper;
	}
}
