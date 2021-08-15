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

namespace WP_PluginFramework\Views;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\DataTypes\Data_Type;
use WP_PluginFramework\HtmlComponents\Html_Base_Component;
use WP_PluginFramework\HtmlComponents\Input_Component;
use WP_PluginFramework\HtmlComponents\Push_Button;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\Hr;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Tbody;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\Th;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Std_View extends Form_View {

	protected $headers              = null;
	protected $form_inputs          = array();
	protected $buttons              = array();
	protected $footers              = array();
	protected $input_form_categories= array();
	protected $content_config        = array();
	protected $form_table_tr_wrapper = null;

    /** @var Status_Bar */
    public $std_status_bar;

	/**
	 * Construction.
	 *
	 * @param $id
	 * @param $controller
	 * @param array      $attributes
	 */
	public function __construct( $id, $controller, $model = null, $properties = array() ) {

		$this->form_id = $id;

		if(array_key_exists('admin_view', $properties)) {
			$this->admin_view = $properties['admin_view'];
		} else {
			$this->admin_view = is_admin();
		}

        if($this->admin_view) {
            $this->content_config['form_input_layout']           = 'double_column_table';
            $this->content_config['form_placeholder_table_attr'] = array( 'class' => 'form-table' );
            $this->content_config['form_placeholder_tbody_attr'] = array( 'class' => 'wpf-table-placeholder' );
            $this->content_config['form_placeholder_tr_attr']    = null;
            $this->content_config['form_placeholder_th_attr']    = array( 'class' => 'row' );
            $this->content_config['form_placeholder_td_attr']    = null;
            $this->content_config['form_input_encapsulation'] = null;
            $this->content_config['form_input_width'] = '100%';
        }	else {
            $this->div_wrapper                                  = array( 'class' => 'wpf-controller' );
            $this->content_config['form_input_encapsulation']   = 'table';
            $this->content_config['form_input_width']           = '400px';
            $this->content_config['form_input_layout']          = 'single_column_table';
            $this->content_config['form_placeholder_table_attr']= array( 'class' => 'wpf-table-placeholder' );
            $this->content_config['form_placeholder_tbody_attr']= array( 'class' => 'wpf-table-placeholder' );
            $this->content_config['form_placeholder_tr_attr']   = array( 'class' => 'wpf-table-placeholder' );
            $this->content_config['form_placeholder_th_attr']   = array( 'class' => 'wpf-table-placeholder-input' );
            $this->content_config['form_placeholder_td_attr']   = array( 'class' => 'wpf-table-placeholder-input' );
        }

        parent::__construct( $id, $controller, null, $properties );
	}

	public function add_header( $id, $component ) {
		$this->headers[ $id ] = $component;
	}

	/**
	 * @param $id
	 * @param $component Html_Base_Component
	 */
	public function add_form_input( $name, $component, $label=null, $description=null, $category=null ) {
	    if(!isset($label)){
	        $label = $component->get_property('label');
        }
        if(!isset($description)){
            $description = $component->get_property('description');
        }
        if(!isset($category)){
            $category = $component->get_property('category');
        }
	    $form_input  =array(
	        'component' => $component,
            'label' => $label,
            'description' => $description,
            'category' => $category
        );
		array_push($this->form_inputs, $form_input);
	}

	public function get_form_input_components() {
		$form_input_components = array();
		$components = $this->get_html_components();
		foreach ($components as $id => $component)  {
			if (is_object($component)) {
				if ($component instanceof Input_Component) {
					$form_input_components[$id] = $component;
				}
			}
		}
		return $form_input_components;
	}

	public function get_form_input_component( $id = null ) {
		$form_input_component = null;
		$form_input_components = $this->get_form_input_components();
		if(isset($form_input_components[$id])) {
			$form_input_component = $form_input_components[$id];
		}
		return $form_input_component;
	}

	public function add_input_form_category( $category ) {
		$this->input_form_categories[] = $category;
	}

	public function add_button( $id, $component ) {
		$this->buttons[ $id ] = $component;
	}

	public function add_footer( $id, $component ) {
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

	/**
	 * @param array $inputs string List of component names
	 */
	public function show_input_error_indications( $inputs = null ) {
		if ( isset( $inputs ) ) {
			foreach ( $inputs as $component_name => $value ) {
				if ( isset( $this->components[ $component_name ] ) ) {
					$this->components[ $component_name ]->show_input_error_indication();
				} else {
					Debug_Logger::write_debug_error( 'Non-existing Html Component ' . $component_name );
				}
			}
		} else {
			foreach ( $this->components as $component ) {
				if($component instanceof Input_Component) {
					$component->show_input_error_indication();
				}
			}
		}
	}

	public function hide_input_error_indications( $inputs = null ) {
		if ( isset( $inputs ) ) {
			foreach ( $inputs as $component_name => $value ) {
				if ( isset( $this->components[ $component_name ] ) ) {
					$this->components[ $component_name ]->hide_input_error_indication();
				} else {
					Debug_Logger::write_debug_error( 'Non-existing Html Component ' . $component_name );
				}
			}
		} else {
			foreach ( $this->components as $component ) {
				if($component instanceof Input_Component) {
					$component->hide_input_error_indication();
				}
			}
		}
	}

	public function create_input_label( $form_input, $content_config ){
		$name = $form_input['component']->get_id();
		$td_label = new Th(null, array(
			'class' => 'wpf-table-placeholder-input row',
		));
		if ( isset( $form_input['label'])) {
			$label = new Label($form_input['label'], array('for' => $name, 'class' => 'wpf-label'));
			$td_label->add_content( $label );
		}
		return $td_label;
	}

	public function create_input_form_content( $form_input, $content_config ){
        $td_input = new Td(null, array('class' => 'wpf-table-placeholder-input'));
        if ( isset( $form_input['component'] ) ) {
            $td_input->add_content( $form_input['component'], $content_config );
        }
        if ( isset( $form_input['description'] )) {
            $p = new P($form_input['description'], array('class' => 'wpf-table-input-description'));
            $td_input->add_content( $p );
        }
        return $td_input;
    }

	public function create_content( $parameters = null ) {
		if ( isset( $this->headers ) ) {
			foreach ( $this->headers as $header ) {
				if ( is_string( $header ) ) {
					$header = new H( 1, $header );
					$this->add_pre_form_content( $header );
				} elseif ( is_object( $header ) ) {
					$this->add_pre_form_content( $header, $this->content_config );
				} else {
					Debug_Logger::write_debug_error( 'Unhandled component type ' . gettype( $header ) );
				}
			}
		}

        if( ($this->form_inputs) or ($this->buttons ))
        {
            if (isset($this->content_config['form_input_encapsulation']) && ($this->content_config['form_input_encapsulation']) === 'table')
            {
                $td_form_attr = $this->content_config['form_placeholder_td_attr'];
                $td_form_attr['width'] = $this->content_config['form_input_width'];

                $td_form = new Td(null, $td_form_attr);
                $td_spacing = new Td(null, $this->content_config['form_placeholder_td_attr']);
                $this->form_table_tr_wrapper = new Tr($td_form, $this->content_config['form_placeholder_tr_attr']);
                $this->form_table_tr_wrapper->add_content($td_spacing);
                $tbody_wrapper = new Tbody($this->form_table_tr_wrapper, $this->content_config['form_placeholder_tbody_attr']);
                $table_wrapper = new Table($tbody_wrapper, $this->content_config['form_placeholder_table_attr']);
                $this->add_content($table_wrapper);
            }
            else
            {
                $td_form = $this;
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
                $tbody = new TBody( null, $this->content_config['form_placeholder_tbody_attr'] );
                foreach ( $this->form_inputs as $form_input ) {
					$td_label = $this->create_input_label( $form_input, $this->content_config );
					$td_input = $this->create_input_form_content( $form_input, $this->content_config );
					if($this->content_config['form_input_layout'] == 'double_column_table') {
						$tr = new Tr($td_label);
						$tr->add_content($td_input);
						$tbody->add_content( $tr );
					} else {
						$tr = new Tr($td_label);
						$tbody->add_content( $tr );
						$tr = new Tr($td_input);
						$tbody->add_content( $tr );
					}
                }
                $table = new Table($tbody, $this->content_config['form_placeholder_table_attr']);
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

                    $tbody = new Tbody( null, $this->content_config['form_placeholder_tbody_attr'] );
                    foreach ( $this->form_inputs as $form_input ) {
                        if ( isset( $form_input['category'] ) && ( $form_input['category'] === $category['name'] ) ) {
							$td_label = $this->create_input_label( $form_input, $this->content_config );
							$td_input = $this->create_input_form_content( $form_input, $this->content_config );
							if($this->content_config['form_input_layout'] == 'double_column_table') {
								$tr = new Tr($td_label);
								$tr->add_content($td_input);
								$tbody->add_content( $tr );
							} else {
								$tr = new Tr($td_label);
								$tbody->add_content( $tr );
								$tr = new Tr($td_input);
								$tbody->add_content( $tr );
							}
                        }
                    }
                    $table = new Table($tbody, $this->content_config['form_placeholder_table_attr']);
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


		if ( isset( $this->footers ) ) {

			foreach ( $this->footers as $footer ) {
				if ( is_string( $footer ) ) {
					$footer = new P( $footer );
					$this->add_post_form_content( $footer );
				} elseif ( is_object( $footer ) ) {
					$footer->create_content( $this->content_config );
					$this->add_post_form_content( $footer );
				} else {
					Debug_Logger::write_debug_error( 'Unhandled component type ' . gettype( $footer ) );
				}
			}
		}

		parent::create_content( $parameters );
	}
}
