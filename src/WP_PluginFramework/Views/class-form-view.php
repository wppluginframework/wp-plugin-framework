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
	protected $show_form             = true;

	const SEND_METHOD_AJAX = 'ajax';
	const SEND_METHOD_POST = 'post';
	const SEND_METHOD_GET  = 'get';

	private $method = 'ajax';

	protected $hidden_fields         = array();

	/**
	 * Construction.
	 *
	 * @param $controller
	 * @param array      $attributes
	 */
	public function __construct( $controller, $attributes = array() ) {
		parent::__construct( $controller, null, $attributes );

		$my_class = get_called_class();
	}

	public function set_method( $method ) {
		$this->method = $method;
	}

	public function add_hidden_fields( $name, $value ) {
		$attributes            = array(
			'name'  => $name,
			'value' => $value,
		);
		$this->hidden_fields[] = $attributes;
	}

	public function create_content( $parameters = null ) {
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

			$form = new Form( $this->contents, $attributes );

			foreach ( $this->hidden_fields as $hidden_field_attributes ) {
				$hidden_input = new Input_Hidden( $hidden_field_attributes );
				$form->add_content( $hidden_input );
			}

			$this->set_content( $form );
		}

		parent::create_content( $parameters );
	}
}
