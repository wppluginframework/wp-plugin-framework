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

use WP_PluginFramework\HtmlComponents\Status_Bar;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Controller extends Std_Controller {

	/**
	 * Construction.
	 *
	 * @param null $model_class
	 * @param null $view_class
	 * @param null $id
	 */
	public function __construct( $model_class = null, $view_class = null, $id = null ) {
		parent::__construct( $model_class, $view_class, $id );
		$this->set_permission( true, 'manage_options' );
	}

    /**
     * @param $input_name
     */
    protected function response_set_input_error( $input_name ) {
        $error_input                = $this->get_server_context_data( 'error_input', array() );
        $error_input[ $input_name ] = true;
        $this->set_server_context_data( 'error_input', $error_input );
    }

    /**
     *
     */
    protected function hide_input_error_indications() {
        $this->view->hide_input_error_indications();
    }

    /**
     *
     */
    protected function show_onput_error_indications() {
        $error_inputs = $this->get_server_context_data( 'error_input' );
        if ( $error_inputs ) {
            $this->view->show_input_error_indications( $error_inputs );
        }
    }
}
