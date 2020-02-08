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

namespace WP_PluginFramework\Controllers;

defined( 'ABSPATH' ) || exit;

/**
 * Summary.
 *
 * Description.
 */
class Form_Controller extends Controller {

	/**
	 * Summary.
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	protected function load_form_values( $values = array() ) {
		$values = parent::load_form_values( $values );

		foreach ( $_POST as $key => $value ) {
			if ( ( is_string( $key ) ) && ( is_string( $value ) ) ) {
				if ( ( '_' !== $key[0] ) && ( 'action' !== $key ) ) {
					/* jQuery ajax post adds double slashes */
					$values[ $key ] = stripslashes( $value );
				}
			}
		}

		if ( isset( $this->view ) ) {
			$this->view->clear_checkbox_before_form_post();
		}

		return $values;
	}

	/**
	 * Summary.
	 *
	 * @param array $values
	 */
	protected function init_view( $values ) {
		parent::init_view( $values );

		$this->view->set_values( $values );
	}
}
