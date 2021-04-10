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

namespace WP_PluginFramework\HtmlComponents;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Div;

/**
 * Summary.
 *
 * Description.
 */
class Grid extends Html_Base_Component {

	const VERTICAL   = 'vertical';
	const HORIZONTAL = 'horizontal';

	const TABLE_LAYOUT = 'table_layout';

	protected $direction = null;

	/**
	 * Construction.
	 *
	 * @param $direction
	 * @param null      $size
	 * @param null      $properties
	 * @param null      $attributes
	 */
	public function __construct( $direction, $size = null, $properties = null, $attributes = null ) {
		$this->direction = $direction;

		if ( $size ) {
			for ( $i = 0; $i < $size; $i++ ) {
				$div              = new Div();
				$this->contents[] = $div;
			}
		}

		parent::__construct( $attributes, $properties, null, 'div', true );
	}

	/**
	 * Summary.
	 *
	 * @param $direction
	 * @param null      $size
	 * @param null      $properties
	 * @param null      $attributes
	 *
	 * @return Grid
	 */
	public function set_grid( $direction, $size = null, $properties = null, $attributes = null ) {
		$grid = new Grid( $direction, $size, $properties, $attributes );
		$this->add_content( $grid );
		return $grid;
	}

	/**
	 * Summary.
	 *
	 * @param $tag
	 */
	public function add_placeholder( $tag ) {
		$div = new Div();
		$div->set_property( 'placeholder', $tag );
		parent::add_content( $div );
	}

	/**
	 * Summary.
	 *
	 * @param $component
	 */
	public function add_component( $component ) {
		$div = new Div();
		$div->set_property( 'component', $component );
		parent::add_content( $div );
	}

	/**
	 * Summary.
	 *
	 * @param $content
	 */
	public function add_content( $content ) {
		if ( ! ( $content instanceof Grid ) ) {
			$div = new Div( $content );
			parent::add_content( $div );
		} else {
			parent::add_content( $content );
		}
	}
}
