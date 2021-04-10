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

namespace WP_PluginFramework\Utils;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Plugin_Container;

/**
 * Summary.
 *
 * Description.
 */
class Security_Filter {

	const POSITIVE_INTEGER      = 'positive_integer';
	const POSITIVE_INTEGER_ZERO = 'positive_integer_zero';
	const BOOL                  = 'bool';
	const STRING_KEY_NAME       = 'string_key_name';
	const ALPHA_NUM             = 'alpha_num';
	const ALPHA                 = 'alpha';
	const WP_USERNAME           = 'wp_username';
	const WP_PASSWORD           = 'wp_password';
	const EMAIL                 = 'email';
	const CLASS_NAME            = 'class_name';
    const URL                   = 'url';

	public static function sanitize_text( $text ) {
		if ( preg_match( '/^[A-Za-z0-9 .,;:+=?_~\/\-!@#\$%\^&\*\(\)]+$/', $text ) ) {
			return strval( $text );
		}

		return null;
	}

	public static function sanitize_alpha_num_text( $text ) {
		if ( $text === '' ) {
			return '';
		} else {
			if ( preg_match( '/^[A-Za-z0-9]+$/', $text ) ) {
				return strval( $text );
			}
		}

		return null;
	}

	public static function sanitize_alpha_text( $text ) {
		if ( $text === '' ) {
			return '';
		} else {
			if ( preg_match( '/^[A-Za-z]+$/', $text ) ) {
				return strval( $text );
			}
		}

		return null;
	}

	public static function sanitize_key_name_text( $text ) {
		if ( $text === '' ) {
			return '';
		} else {
			if ( preg_match( '/^[A-Za-z0-9_\-]+$/', $text ) ) {
				return strval( $text );
			}
		}

		return null;
	}

	public static function sanitize_integer( $text ) {
		if ( preg_match( '/^[1-9][0-9]{0,15}$/', $text ) ) {
			$value = intval( $text );
			if ( gettype( $value ) === 'integer' ) {
				return $value;
			}
		}

		return null;
	}

	public static function sanitize_positive_integer_or_zero( $text ) {
		if ( preg_match( '/^[1-9][0-9]{0,15}$/', $text ) ) {
			$value = intval( $text );
			if ( gettype( $value ) === 'integer' ) {
				if ( $value >= 0 ) {
					return $value;
				}
			}
		}

		return null;
	}

	public static function sanitize_positive_integer( $text ) {
		if ( preg_match( '/^[1-9][0-9]{0,15}$/', $text ) ) {
			$value = intval( $text );
			if ( gettype( $value ) === 'integer' ) {
				if ( $value > 0 ) {
					return $value;
				}
			}
		}

		return null;
	}

	public static function sanitize_float( $text ) {
		if ( preg_match( '/^[0-9,.\-]{0,15}$/', $text ) ) {
			$value = floatval( $text );
			if ( ( gettype( $value ) === 'float' ) || ( gettype( $value ) === 'double' ) ) {
				return $value;
			}
		}

		return null;
	}

	public static function sanitize_bool( $text ) {
		if ( preg_match( '/^[01]$/', $text ) ) {
			$value = ( '1' === $text );
		} else {
			$value = null;
		}

		return $value;
	}

	public static function sanitize_class_name( $text ) {
		if ( preg_match( '/^[A-Za-z0-9_\\\]+$/', $text ) ) {
			if ( strlen( $text ) < 256 ) {
				return strval( $text );
			}
		}

		return null;
	}

	public static function filter( $unfiltered, $filter_datatype ) {
		switch ( $filter_datatype ) {
			case 'string':
			case self::WP_USERNAME:
			case self::WP_PASSWORD:
			case self::EMAIL:
				return self::sanitize_text( $unfiltered );
				break;

            case self::URL:
                return self::sanitize_text( $unfiltered );
                break;

			case self::STRING_KEY_NAME:
				return self::sanitize_key_name_text( $unfiltered );
				break;

			case self::ALPHA_NUM:
				return self::sanitize_alpha_num_text( $unfiltered );
				break;

			case self::ALPHA:
				return self::sanitize_alpha_text( $unfiltered );
				break;

			case 'integer':
				return self::sanitize_integer( $unfiltered );
				break;

			case self::POSITIVE_INTEGER:
				return self::sanitize_positive_integer( $unfiltered );
				break;

			case self::POSITIVE_INTEGER_ZERO:
				return self::sanitize_positive_integer_or_zero( $unfiltered );
				break;

			case 'double':
				return self::sanitize_float( $unfiltered );
				break;

			case self::BOOL:
				return self::sanitize_bool( $unfiltered );
				break;

			case self::CLASS_NAME:
				return self::sanitize_class_name( $unfiltered );
				break;

			default:
				throw new \Exception( 'Undefined filter' );
		}
	}

	public static function filter_list( $unfiltered_list, $filter ) {
		$filtered_list = array();
		foreach ( $unfiltered_list as $unfiltered ) {
			$filtered = self::filter( $unfiltered, $filter );
			if ( null !== $filtered ) {
				$filtered_list[] = $filtered;
			}
		}
		return $filtered_list;
	}

	public static function safe_read_get_request( $key, $filter_datatype ) {
		if ( isset( $_GET[ $key ] ) ) {
			return self::filter( $_GET[ $key ], $filter_datatype );
		} else {
			return null;
		}
	}

	public static function safe_read_get_key_list() {
		$unfiltered_list = array();
		foreach ( $_GET as $key => $value ) {
			$unfiltered_list[] = $key;
		}
		$filtered_list = self::filter_list( $unfiltered_list, self::STRING_KEY_NAME );
		return $filtered_list;
	}

	public static function safe_read_post_request( $key, $filter ) {
		if ( isset( $_POST[ $key ] ) ) {
			return self::filter( $_POST[ $key ], $filter );
		} else {
			return null;
		}
	}

	public static function safe_read_post_key_list() {
		$unfiltered_list = array();
		foreach ( $_POST as $key => $value ) {
			$unfiltered_list[] = $key;
		}
		$filtered_list = self::filter_list( $unfiltered_list, self::STRING_KEY_NAME );
		return $filtered_list;
	}

	public static function safe_read_cookie_string( $cookie_name, $filter ) {
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return self::filter( $_COOKIE[ $cookie_name ], $filter );
		} else {
			$cookie = null;

		}

		return $cookie;
	}

	public static function safe_read_array_item( $array, $key, $filter ) {
		if ( isset( $array[ $key ] ) ) {
			return self::filter( $array[ $key ], $filter );
		} else {
			return null;
		}
	}
}
