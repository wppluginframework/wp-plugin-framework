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

use WP_PluginFramework\Base\Content_Object;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Data_Type extends Content_Object {

	protected static $default_data_type         = null;
	protected static $default_string_validation = null;

	protected $data_size         = null;
	protected $default_value     = null;
	protected $db_field_type     = null;
	protected $db_collation      = null;
	protected $value             = null;
	protected $validate_errors   = array();
	protected $html_widget_class = null;
    protected $label             = null;

	const DATA_SIZE         = 'data_size';
	const DEFAULT_VALUE     = 'default_value';
	const DB_FIELD_TYPE     = 'db_field_type';
	const HTML_WIDGET_CLASS = 'html_widget_class';

	public function __construct( $properties, $key, $value = null ) {
		if ( isset( $properties ) ) {
			if ( gettype( $properties ) !== 'array' ) {
				Debug_Logger::write_debug_error( 'Metadata wrong type ' . gettype( $properties ) );
			}
		}

		if ( ! isset( $properties['data_type'] ) ) {
			$properties['data_type'] = static::$default_data_type;
		}

		if ( ! isset( $properties['db_field_type'] ) ) {
			switch ( static::$default_data_type ) {
				case 'integer':
					if ( ! isset( $properties['data_size'] ) ) {
						$properties['data_size'] = 8;
					}
					$properties['db_field_type'] = 'INT(' . strval( $properties['data_size'] ) . ')';
					break;

				case 'string':
					if ( ! isset( $properties['data_size'] ) ) {
						$properties['data_size'] = 16;
					}
					$properties['db_field_type'] = 'VARCHAR(' . strval( $properties['data_size'] ) . ')';
					break;

				case 'double':
					$properties['db_field_type'] = 'DOUBLE';
					break;

				default:
					Debug_Logger::write_debug_error( 'Missing property \'db_field_type\' data type"' );
					break;
			}
		}

		if ( ! isset( $properties['string_validation'] ) ) {
			if ( isset( static::$default_string_validation ) ) {
				$properties['string_validation'] = static::$default_string_validation;
			}
		}

		$properties['name'] = $key;

		if ( isset( $value ) ) {
			$this->value = $value;
		} else {
			$this->value = $this->default_value;
		}

		parent::__construct( null, $properties );
	}

	public static function get_type() {
		return static::$default_data_type;
	}

	/**
	 * Summary.
	 *
	 * @return null
	 */
	public function get_default_value() {
		return $this->default_value;
	}

	/**
	 * Summary.
	 *
	 * @return null
	 */
	public function get_database_type() {
		return $this->db_field_type;
	}

	/**
	 * Summary.
	 *
	 * @return null
	 */
	public function get_database_collation() {
		return $this->db_collation;
	}

	/**
	 * Summary.
	 *
	 * @param $value
	 *
	 * @return bool|false|int
	 */
	public function validate( $value ) {
		if ( isset( $this->string_validation ) ) {
			$value_str = strval( $value );
			return preg_match( $this->string_validation, $value_str );
		} else {
			return true;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $errors
	 *
	 * @return bool|null
	 */
	public function add_validate_errors( $errors ) {
		return $this->add_array_property_item( 'ValidateErrors', $errors );
	}

	/**
	 * Summary.
	 *
	 * @return |null |null
	 */
	public function get_validate_errors() {
		return $this->get_property( 'ValidateErrors' );
	}

	/**
	 * Summary.
	 *
	 * @return null
	 */
	public function get_value() {
		return $this->value;
	}

    public function get_value_type($type) {
        $converted_value = null;
        switch(gettype($this->value)) {
            case 'integer':
                switch($type) {
                    case 'integer':
                        $converted_value = $this->value;
                        break;
                    case 'string':
                        $converted_value = $this->get_string();
                        break;

                    default:
                        Debug_Logger::write_debug_error( 'Missing data type conversion.', gettype($this->value), $type );
                        break;
                }
                break;

            case 'string':
                switch($type) {
                    case 'integer':
                        $converted_value = intval($this->value);
                        break;
                    case 'string':
                        $converted_value = $this->value;
                        break;

                    default:
                        Debug_Logger::write_debug_error( 'Missing data type conversion.', gettype($this->value), $type );
                        break;
                }
                break;

            default:
                Debug_Logger::write_debug_error( 'Missing data type conversion.', gettype($this->value), $type );
                break;
        }
        return $converted_value;
    }

    public function get_label() {
        return $this->label;
    }

	/**
	 * Summary.
	 *
	 * @return string
	 */
	public function get_string() {
		return strval( $this->value );
	}

	public function get_formatted_text() {
		return $this->get_string();
	}

	public function get_html_component_class_name() {
        $html_component_class_name = $this->html_widget_class;
        if(!isset($html_component_class_name))
        {
            switch ( static::$default_data_type ) {
                case 'integer':
                case 'string':
                case 'double':
                    $html_component_class_name = 'Text_Line';
                    break;

                default:
                    Debug_Logger::write_debug_error( 'Missing property \'db_field_type\' data type"' );
                    break;
            }
        }

        if ( ! strpos( $html_component_class_name, '\\' ) ) {
            /* If only class name set, use vendor's default HtmlComponents */
            $namespace            = explode( '\\', __NAMESPACE__ );
            $html_component_class_name = $namespace[0] . '\\HtmlComponents\\' . $html_component_class_name;
        }

	    return $html_component_class_name;
    }

	public function get_html_component( $attributes = null ) {
        $html_component       = null;

		$html_component_class = $this->get_html_component_class_name();

		if ( isset( $html_component_class ) ) {
			$html_component = new $html_component_class();
            $html_component->set_attributes($attributes);
            $html_component->set_properties($this->get_properties());
		}
		return $html_component;
	}

    public function create_content() {
	    $content = $this->get_formatted_text();
        $this->set_content( $content );
    }

    static public function create_data_object( $metadata, $key, $value = null ) {
        $data_type   = self::format_data_type_name( $metadata['data_type'] );
        $data_object = new $data_type( $metadata, $key, $value );
        return $data_object;
    }

    static public function format_data_type_name( $name ) {
        if ( strstr ( $name, '\\' )) {
            $data_type = $name;
        } else {
            $data_type = __NAMESPACE__ . '\\' . $name;
        }
        return $data_type;
    }
}
