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

namespace WP_PluginFramework\DataTypes;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Boolean_Type extends Data_Type {

	protected static $default_data_type = 'integer';

	public function __construct( $metadata, $key, $value = null ) {
		if ( ! isset( $metadata['db_field_type'] ) ) {
			$metadata['db_field_type'] = 'BOOLEAN';
		}

		if ( ! isset( $metadata[ self::HTML_WIDGET_CLASS ] ) ) {
			$metadata[ self::HTML_WIDGET_CLASS ] = 'CheckBox';
		}

		parent::__construct( $metadata, $key, $value );
	}

	public function get_formatted_text() {
		if ( isset( $this->value ) ) {
			if ( $this->value ) {
				$s = 'true';
			} else {
				$s = 'false';
			}
		} else {
			$s = 'null';
		}

		return $s;
	}

	public function set_value( $value ) {
		switch ( gettype( $value ) ) {
			case 'string':
				if ( '1' === $value ) {
					$this->value = 1;
				} elseif ( '0' === $value ) {
					$this->value = 0;
				} else {
					Debug_Logger::write_debug_error( 'Invalid string value ' . gettype( $value ) );
				}
				break;

			case 'boolean':
				if ( true === $value ) {
					$this->value = 1;
				} elseif ( false === $value ) {
					$this->value = 0;
				} else {
					Debug_Logger::write_debug_error( 'Invalid boolean value ' . gettype( $value ) );
				}
				break;

			default:
				Debug_Logger::write_debug_error( 'Unsupported data type ' . gettype( $value ) );
				break;
		}
	}
}
