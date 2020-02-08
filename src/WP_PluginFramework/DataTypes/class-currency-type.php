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

/**
 * Summary.
 *
 * Description.
 */
class Currency_Type extends Data_Type {

	protected static $default_data_type = 'double';

	public function __construct( $metadata, $key, $value = null ) {
		if ( ! isset( $metadata['data_size'] ) ) {
			$metadata['data_size'] = 12;
		}
		if ( ! isset( $metadata['db_field_type'] ) ) {
			$metadata['db_field_type'] = 'DECIMAL(10,2)';
		}

		parent::__construct( $metadata, $key, $value );
	}

	public function get_formatted_text() {
		$s = number_format( $this->value, 2, '.', ',' );
		return $s;
	}
}
