<?php
/**
 * WordPress Plugin MVC Framework Library
 *
 * Copyright (C) 2020 Arild Hegvik.
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

use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\Tr;

defined( 'ABSPATH' ) || exit;

class Grid_Layout extends Html_Base_Component {

	protected $grid = array();

	/**
	 * Construction.
	 *
	 * @param $direction
	 * @param null      $size
	 * @param null      $properties
	 * @param null      $attributes
	 */
	public function __construct( $rows, $columns, $properties = null, $attributes = null ) {

		if (( $rows > 0 ) and ( $columns > 0)) {
			$row = array_fill(0, $columns, null);
			$this->grid = array_fill(0, $rows, $row);
		}

		parent::__construct( $attributes, $properties, null, 'table', true );
	}

	public function set_cell_content( $row, $column, $content, $config = null ) {
		$this->grid[$row][$column] = $content;
	}

	public function create_content( $config = null ){
		foreach ( $this->grid as $row ){
			$tr = new Tr( null );
			foreach ($row as $column ) {
				$td = new Td( $column);
				$tr->add_content($td);
			}
			$this->add_content($tr);
		}
	}

}