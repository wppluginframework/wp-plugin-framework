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

namespace WP_PluginFramework\HtmlElements;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Table extends Html_Base_Element {

	/**
	 * Construction.
	 *
	 * @param null $content
	 * @param null $attributes
	 */
	public function __construct( $content = null, $attributes = null ) {
		parent::__construct( 'table', true, $content, $attributes );
	}

	/**
	 * Summary.
	 *
	 * @param $content
	 * @param $tr_attributes
	 * @param $td_attributes
	 */
	public function add_row( $content, $tr_attributes, $td_attributes ) {
		$tr = null;

		$object_type = gettype( $content );
		switch ( $object_type ) {
			case 'object':
				$class_type = get_class( $content );
				switch ( $class_type ) {
					case 'Tr':
						break;

					case 'Th':
					case 'Td':
						$tr = new Tr( $content, $tr_attributes );
						break;

					default:
						$tr = new Tr( $content, $tr_attributes );
						break;
				}
				break;

			case 'array':
				/* TODO more code needed here to check other input scenarios */
				$tr = new Tr( $content, $tr_attributes );
				break;

			case 'string':
				$td = new Td( $content, $td_attributes );
				$tr = new Tr( $td, $tr_attributes );
				break;

			default:
				Debug_Logger::write_debug_error( 'Unhandled data type ' . gettype( $object_type ) );
				break;
		}

		if ( $tr ) {
			$this->add_content( $tr );
		}
	}
}
