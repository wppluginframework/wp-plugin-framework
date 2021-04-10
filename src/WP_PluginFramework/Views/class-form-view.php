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

use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\HtmlElements\Form;
use WP_PluginFramework\HtmlElements\Input_Hidden;


/**
 * Summary.
 *
 * Description.
 */
class Form_View extends View {

	protected $form_id = null;
	protected $show_form = true;

	const SEND_METHOD_AJAX = 'ajax';
	const SEND_METHOD_POST = 'post';
	const SEND_METHOD_GET  = 'get';

	private $method = 'ajax';

	protected $hidden_fields = array();
    protected $pre_form_contents = array();
    protected $post_form_contents = array();

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

    public function add_pre_form_content( $content, $config = null ) {
        $content = $this->prepare_create_content($content, $config);

        switch ( gettype( $content ) ) {
            case 'array':
                $this->pre_form_contents = array_merge( $this->pre_form_contents, $content );
                break;

            default:
                array_push( $this->pre_form_contents, $content );
        }
    }

    public function add_post_form_content( $content, $config = null ) {
        $content = $this->prepare_create_content($content, $config);

        switch ( gettype( $content ) ) {
            case 'array':
                $this->post_form_contents = array_merge( $this->post_form_contents, $content );
                break;

            default:
                array_push( $this->post_form_contents, $content );
        }
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
			} else {
                $attributes['id'] = $this->id;
            }

			$form = new Form( null, $attributes );

			foreach ( $this->hidden_fields as $hidden_field_attributes ) {
				$hidden_input = new Input_Hidden( $hidden_field_attributes );
				$form->add_content( $hidden_input );
			}

            $form->add_content( $this->contents );

			$this->set_content( $form );

			if(!empty($this->pre_form_contents)) {
                $this->prepend_content( $this->pre_form_contents );
            }

            if(!empty($this->post_form_contents)) {
                $this->add_content( $this->post_form_contents );
            }
		}

		parent::create_content( $parameters );
	}
}
