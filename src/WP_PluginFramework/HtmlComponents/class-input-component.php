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

use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\HtmlElements\Th;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Input_Component extends Html_Base_Component {

	const DEFAULT_VALUE_TYPE = null;

	/** @var string Name attribute for input element as sent to sever. */
	protected $name;
	/** @var boolean Indicates component has been touched and must update component on client side. */
	protected $touched;
	/** @var string Value as sent from client side. */
	protected $value;
	/** @var string Header text to be displayed for input. */
	protected $header;
	/** @var boolean Client side can not change input. */
	protected $readonly = false;
	/** @var array string */
	protected $input_attributes = array();
	/** @var boolean True will only draw the input element itself. */
	protected $single;

	/**
	 * Summary.
	 *
	 * @param $id
	 */
	public function set_id( $id ) {
		if( !isset( $this->name ) ) {
			/* Every input must have a name in html attribute. */
			$this->set_name( $id );
		}
		parent::set_id( $id );
	}

	/**
	 * Summary.
	 *
	 * @param $client_side_values
	 *
	 * @return string
	 */
	public function pick_client_side_value($client_side_values) {
		$value = null;
		if( isset( $this->name ) ) {
			if( isset( $client_side_values[$this->name] ) ) {
				$value = $client_side_values[$this->name];
			}
		}
		return $value;
	}

	/**
	 * Summary.
	 *
	 * @param $value
	 */
	public function set_value( $value ) {
		if ( gettype( $value ) === static::DEFAULT_VALUE_TYPE ) {
			$this->value = $value;
		} else {
			Debug_Logger::write_debug_note( 'Wrong data type ' . gettype( $value ) . ' for ' . get_called_class() . ' expected ' . static::DEFAULT_VALUE_TYPE );
		}
	}

	/**
	 * Summary.
	 *
	 * @return string
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Summary.
	 *
	 * @param $values
	 *
	 * @return bool
	 */
	public function add_input_class( $values ) {
		if ( $this->set_property_key_values( 'input_attributes', 'class', $values ) ) {
			$form_selector = $this->get_form_selector();
			$name          = $this->name;
			$selector      = $form_selector . ' input[name=' . $name . ']';
			if ( is_array( $values ) ) {
				$values = implode( ' ', $values );
			}
			$this->update_client_dom( $selector, 'addClass', $values );
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $values
	 *
	 * @return bool
	 */
	public function remove_input_class( $values ) {
		if ( $this->remove_property_key_values( 'input_attributes', 'class', $values ) ) {
			$form_selector = $this->get_form_selector();
			$name          = $this->name;
			$selector      = $form_selector . ' input[name=' . $name . ']';
			if ( is_array( $values ) ) {
				$values = implode( ' ', $values );
			}
			$this->update_client_dom( $selector, 'removeClass', $values );
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Summary.
	 */
	public function show_input_error_indication() {
		$this->add_input_class( 'wpf-input-error' );
	}

	/**
	 * Summary.
	 */
	public function hide_input_error_indication() {
		$this->remove_input_class( 'wpf-input-error' );
	}

	/**
	 * Summary.
	 *
	 * @param null $config
	 *
	 * @return Td
	 */
	public function create_content( $config = null ) {
		if ( isset( $config['form_input_layout'] ) && ( 'double_column_table' === $config['form_input_layout'] ) ) {
			$tr = new Tr( null, $config['form_placeholder_tr_attr'] );

			if ( isset( $this->label ) ) {
				$label_attr = array( 'for' => $this->name );
				$label      = new Label( $this->label, $label_attr );
				$th         = new Th( $label, $config['form_placeholder_th_attr'] );
			} else {
				$th = new Th( null, $config['form_placeholder_th_attr'] );
			}

			$tr->add_content( $th );

			$td_wrapper = new Td( null, $config['form_placeholder_td_attr'] );

			$tr->add_content( $td_wrapper );

			$this->add_content( $tr );
		} else {
			if ( isset( $this->label ) ) {
				$label = new Label( $this->label, array( 'class' => 'wpf-label' ) );

				$th = new Th( $label, $config['form_placeholder_th_attr'] );
				$tr = new Tr( $th, $config['form_placeholder_tr_attr'] );
				$this->add_content( $tr );
			}

			$form_placeholder_td_attr = null;
			if(isset($config['form_placeholder_td_attr']))
			{
				$form_placeholder_td_attr = $config['form_placeholder_td_attr'];
			}
			$td_wrapper = new Td(null, $form_placeholder_td_attr);

			$form_placeholder_tr_attr = null;
			if(isset($config['form_placeholder_tr_attr']))
			{
				$form_placeholder_tr_attr = $config['form_placeholder_tr_attr'];
			}
			$tr = new Tr($td_wrapper, $form_placeholder_tr_attr);
			$this->add_content( $tr );
		}

		return $td_wrapper;
	}
}
