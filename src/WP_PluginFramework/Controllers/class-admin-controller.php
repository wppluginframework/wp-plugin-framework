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
	 * Summary.
	 *
	 * @param $data_record
	 */
	public function handle_save_success( $data_record ) {
		$this->view->admin_status_bar->set_status_text( 'Your settings have been saved.', Status_Bar::STATUS_SUCCESS );
	}

	/**
	 * Summary.
	 *
	 * @param $data_record
	 */
	public function handle_save_errors( $data_record ) {
		$this->view->admin_status_bar->set_status_text( 'Error saving data.', Status_Bar::STATUS_ERROR );
	}
}
