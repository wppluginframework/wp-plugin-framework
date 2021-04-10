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

namespace WP_PluginFramework\DataTypes;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Integer_Type extends Data_Type {

	protected static $default_data_type         = 'integer';
	protected static $default_string_validation = '/^[0-9]+/';

	public function __construct( $metadata, $key, $value = null ) {
		parent::__construct( $metadata, $key, $value );
	}

	public function get_formatted_text() {
		$s = number_format( $this->value, 0, '', ',' );
		return $s;
	}

	public function set_value( $value ) {
		switch ( gettype( $value ) ) {
			case 'string':
				$this->value = intval( $value );
				break;

			case 'integer':
				$this->value = $value;
				break;

			default:
				Debug_Logger::write_debug_error( 'Unsupported data type ' . gettype( $value ) );
				break;
		}
	}
}
