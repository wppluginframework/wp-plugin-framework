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

use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlElements\Input_Button;

/**
 * Summary.
 *
 * Description.
 */
class Std_Record_View extends Std_View {

	protected $buttons = array();

	/**
	 * Construction.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		parent::__construct( $id, $controller );

		$button = array(
			'value' => 'Save',
			'name'  => 'Update',
		);
		$this->add_buttons( $button );
	}

	public function add_button( $name, $label ) {
		$button = array(
			'value' => $label,
			'name'  => $name,
		);
		$this->add_buttons( $button );
	}

	public function add_buttons( $button ) {
		$this->buttons[] = $button;
	}

	public function create_content( $parameters = null ) {
		parent::create_content( $parameters );
	}

	public function draw_std_view( $dataobj_records ) {
		$this->add_data_object( $dataobj_records );
		$this->create_content();
		return $this->draw_html();
	}
}
