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
class Time_Stamp_Type extends Data_Type {

	protected static $default_data_type = 'string';

	public function __construct( $metadata, $key, $value = null ) {
		if ( ! isset( $metadata['db_field_type'] ) ) {
			$metadata['db_field_type'] = 'DATETIME';
		}

		if ( isset( $metadata['default_value'] ) ) {
			switch ( $metadata['default_value'] ) {
				case 'integer':
					if ( $this->default_value >= 0 ) {
						$metadata['default_value'] = date( 'Y-m-d H:i:s', $this->default_value );
					} else {
						Debug_Logger::write_debug_error( 'Invalid default value ' . $value );
					}
					break;

				default:
					Debug_Logger::write_debug_error( 'Unsupported data type for default value ' . gettype( $value ) );
					break;
			}
		}

		parent::__construct( $metadata, $key, $value );
	}

	public function set_value( $value ) {
		switch ( gettype( $value ) ) {
			case 'integer':
				$this->value = date( 'Y-m-d H:i:s', $value );
				break;

			default:
				Debug_Logger::write_debug_error( 'Unsupported data type ' . gettype( $value ) );
				break;
		}
	}

	public function get_value_seconds() {
		return strtotime( $this->value );
	}
}
