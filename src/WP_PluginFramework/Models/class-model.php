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

namespace WP_PluginFramework\Models;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\Base\Base_Object;
use WP_PluginFramework\DataTypes\Data_Type;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
abstract class Model extends Base_Object {

	const PRIMARY_KEY                     = 'id';
	const VALIDATION_ERROR_REQUIRED_FIELD = 0;
	const VALIDATION_ERROR_INVALID        = 1;

	static $meta_data;

	protected $data_objects = array();
	/** @var array boolean Each array item indicates a DataObject has been touched. */
	protected $touched_data      = array();
	protected $validation_errors = array();
	/** @var string Name used for storage in database, files etc. */
	protected $model_name;

	/**
	 * Construction.
	 *
	 * @param null $model_name
	 */
	public function __construct( $model_name = null ) {
		parent::__construct( null );

		if ( ! $model_name ) {
			$class_name = get_called_class();
			$class_name = substr( $class_name, strrpos( $class_name, '\\' ) + 1 );

			$plugin_container = Plugin_Container::instance();
			$model_name       = $plugin_container->get_prefixed_plugin_slug() . '_' . $class_name;
		}

		$this->model_name = $model_name;

		$this->clear_all_data();
	}

	/*
	 * @return false    Model data/table/option don't exist or has never been created.
	 * @return integer  Number of data records loaded.
	 */
	abstract protected function load_data_record( $condition);

	abstract public function load_column( $field_name_list);
	abstract protected function save_data_index( $index);

	/**
	 * Summary.
	 */
	public function create() {  }

	/**
	 * Summary.
	 */
	public function remove() {  }

	/**
	 * Summary.
	 *
	 * @param $key
	 *
	 * @return |null
	 */
	protected function get_meta_data( $key ) {
		if ( array_key_exists( $key, static::$meta_data ) ) {
			return static::$meta_data[ $key ];
		} else {
			Debug_Logger::write_debug_error( 'Unknown model key ' . $key );
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @return
	 */
	public function get_meta_data_list() {
		return static::$meta_data;
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function field_name_exist( $key ) {
		if ( static::PRIMARY_KEY === $key ) {
			return true;
		}
		$metadata_list = $this->get_meta_data_list();
		return array_key_exists( $key, $metadata_list );
	}

	/**
	 * @param  $name string
	 *
	 * @return string
	 */
	protected function format_data_type_name( $name ) {
		$namespace_root = str_replace( '\\Models', '', __NAMESPACE__ );
		$data_type      = $namespace_root . '\\DataTypes\\' . $name;
		return $data_type;
	}

	/**
	 * @param $metadata array
	 * @param $key string
	 * @param $value
	 *
	 * @return Data_Type
	 */
	protected function create_data_object( $metadata, $key, $value = null ) {
		$data_type   = $this->format_data_type_name( $metadata['data_type'] );
		$data_object = new $data_type( $metadata, $key, $value );
		return $data_object;
	}

	/**
	 * Summary.
	 *
	 * @param $record
	 * @param null   $metadata_list
	 *
	 * @return
	 */
	protected function init_record( $record, $metadata_list = null ) {
		if ( null === $metadata_list ) {
			$metadata_list               = $this->get_meta_data_list();
			$record[ self::PRIMARY_KEY ] = null;
		}

		foreach ( $metadata_list as $meta_name => $metadata ) {
			if ( static::PRIMARY_KEY !== $meta_name ) {
				if ( isset( $metadata['default_value'] ) ) {
					$default_value = $metadata['default_value'];
				} else {
					$default_value = null;
				}
				$record[ $meta_name ] = $default_value;
			} else {
				Debug_Logger::write_debug_note( 'Field name ' . self::PRIMARY_KEY . ' is reserved.' );
			}
		}
		return $record;
	}

	/**
	 * Summary.
	 */
	public function init_data_if_empty() {
		if ( count( $this->data_objects ) === 0 ) {
			$this->add_data_record();
		}
	}

	/**
	 * Summary.
	 */
	public function init_default() {
		$this->clear_all_data();
		$this->add_data_record();
	}

	/**
	 * Summary.
	 */
	public function clear_data() {
		if ( count( $this->data_objects ) > 0 ) {
			array_shift( $this->data_objects );
			array_shift( $this->touched_data );
		}
	}

	/**
	 * Summary.
	 */
	public function clear_all_data() {
		$this->data_objects = array();
		$this->touched_data = array();
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 *
	 * @return string|null
	 */
	public function get_data_type_class( $key ) {
		$metadata = $this->get_meta_data( $key );
		if ( isset( $metadata ) ) {
			$data_type_class = $this->format_data_type_name( $metadata['data_type'] );
		} else {
			Debug_Logger::write_debug_error( 'No metadata for key ' . $key );
			$data_type_class = null;
		}

		return $data_type_class;
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 *
	 * @return mixed|string|null
	 */
	public function get_data_type( $key ) {
		$data_type = null;

		if ( static::PRIMARY_KEY === $key ) {
			return 'integer';
		} else {
			$data_type_class = $this->get_data_type_class( $key );
			if ( isset( $data_type_class ) ) {
				$data_type = call_user_func( array( $data_type_class, 'get_type' ) );
				if ( null === $data_type ) {
					Debug_Logger::write_debug_error( 'No data type for key ' . $key );
				}
			} else {
				Debug_Logger::write_debug_error( 'No data type class for key ' . $key );
			}
		}

		return $data_type;
	}

	/**
	 * Summary.
	 *
	 * @param $data_record
	 *
	 * @return array
	 */
	public function filter_existing_data( $data_record ) {
		$existing_data_record = array();

		foreach ( $data_record as $key => $value ) {
			if ( $this->field_name_exist( $key ) ) {
				$existing_data_record[ $key ] = $value;
			}
		}

		return $existing_data_record;
	}

	/**
	 * Summary.
	 *
	 * @param $data_record
	 *
	 * @return bool
	 */
	public function validate_data_record( $data_record ) {
		$result = true;
		foreach ( $data_record as $key => $value ) {
			if ( ! $this->validate_data( $key, $value ) ) {
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function validate_data( $key, $value ) {
		if ( ( ! isset( $value ) ) || ( '' === $value ) ) {
			if ( $this->is_required_field( $key ) ) {
				$this->add_validate_error( $key, static::VALIDATION_ERROR_REQUIRED_FIELD );
				return false;
			}
		}

		$metadata    = $this->get_meta_data( $key );
		$data_object = $this->create_data_object( $metadata, $key );
		if ( isset( $data_object ) ) {
			if ( $data_object->validate( $value ) ) {
				return true;
			} else {
				$errors = $data_object->get_validate_errors();
				if ( isset( $errors ) ) {
					$this->add_validate_error( $key, $errors );
				} else {
					$this->add_validate_error( $key, static::VALIDATION_ERROR_INVALID );
				}
			}
		}

		return false;
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 * @param $errors
	 */
	public function add_validate_error( $key, $errors ) {
		$this->set_property_key_values( 'ValidationErrors', $key, $errors );
	}

	/**
	 * Summary.
	 *
	 * @param null $key
	 *
	 * @return |null
	 */
	public function get_validate_errors( $key = null ) {
		if ( isset( $key ) ) {
			return $this->get_property_key_values( 'ValidationErrors', $key );
		} else {
			return $this->get_property( 'ValidationErrors' );
		}
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 *
	 * @return |null
	 */
	public function is_required_field( $key ) {
		$metadata = $this->get_meta_data( $key );
		if ( isset( $metadata ) ) {
			if ( isset( $metadata['required'] ) ) {
				return $metadata['required'];
			}
		}

		return null;
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function check_data_type( $key, $value ) {
		if ( $this->field_name_exist( $key ) ) {
			$value_data_type = gettype( $value );
			if ( 'NULL' === $value_data_type ) {
				// TODO Should implement check if null is allowed data type.
				return true;
			}
			$meta_data_type = $this->get_data_type( $key );
			if ( ( 'double' === $meta_data_type ) && ( 'integer' === $value_data_type ) ) {
				return true;
			}
			if ( $value_data_type === $meta_data_type ) {
				return true;
			} else {
				return false;
			}
		} else {
			Debug_Logger::write_debug_error( 'Invalid key ' . $key );
			return false;
		}
	}

	/**
	 * Summary.
	 */
	public function touch_data() {
		$this->touched_data[0] = true;

	}

	/**
	 * Summary.
	 */
	public function touch_all_data() {
		$n = count( $this->data_objects );
		for ( $index = 0; $index < $n; $index++ ) {
			$this->touched_data[ $index ] = true;
		}
	}

	/**
	 * Summary.
	 *
	 * @param null $data_list
	 *
	 * @return int|void
	 */
	protected function add_data_record( $data_list = null ) {
		$new_data_record = array();
		$new_data_record = $this->init_record( $new_data_record );

		$new_data_record      = array( $new_data_record );
		$this->data_objects   = array_merge( $this->data_objects, $new_data_record );
		$this->touched_data[] = false;

		$index = count( $this->data_objects ) - 1;

		if ( null !== $data_list ) {
			$this->set_data_record_indexed( $index, $data_list );
		}

		return $index;
	}

	/**
	 * Summary.
	 *
	 * @param $data_record
	 *
	 * @return bool
	 */
	public function set_data_record( $data_record ) {
		$this->set_data_record_indexed( 0, $data_record );

		return true;
	}

	/**
	 * Summary.
	 *
	 * @param $index
	 * @param $data_record
	 */
	protected function set_data_record_indexed( $index, $data_record ) {
		foreach ( $data_record as $key => $value ) {
			$this->set_data_index( $index, $key, $value );
		}
	}

	/**
	 * Summary.
	 *
	 * @param $index
	 * @param $key
	 * @param $value
	 */
	public function set_data_index( $index, $key, $value ) {
		$this->init_data_if_empty();
		if ( $this->check_data_type( $key, $value ) ) {
			$this->data_objects[ $index ][ $key ] = $value;
			$this->touched_data[ $index ]         = true;
		} else {
			/* Create a data object and try set value using that object. Maybe it has conversation function */
			$metadata = $this->get_meta_data( $key );
			if ( isset( $metadata ) ) {
				$data_object = $this->create_data_object( $metadata, $key, $value );
				if ( method_exists( $data_object, 'set_value' ) ) {
					$data_object->set_value( $value );
					$normalized_value                     = $data_object->get_value();
					$this->data_objects[ $index ][ $key ] = $normalized_value;
					$this->touched_data[ $index ]         = true;
				} else {
					Debug_Logger::write_debug_error( 'Invalid value type ' . gettype( $value ) . ' for ' . $key );
				}
			}
		}
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 * @param $value
	 */
	public function set_data( $key, $value ) {
		$this->set_data_index( 0, $key, $value );

	}

	/**
	 * Summary.
	 *
	 * @param $key
	 * @param $value
	 */
	public function set_all_data( $key, $value ) {
		$n = count( $this->data_objects );
		for ( $index = 0; $index < $n; $index++ ) {
			$this->set_data_index( 0, $key, $value );
		}
	}

	/**
	 * Summary.
	 *
	 * @return mixed
	 */
	public function get_data_record() {
		return $this->get_data_record_indexed( 0 );
	}

	/**
	 * Summary.
	 *
	 * @param $index
	 *
	 * @return mixed
	 */
	protected function get_data_record_indexed( $index ) {
		return $this->data_objects[ $index ];
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 *
	 * @return |null
	 */
	public function get_data( $key ) {
		return $this->get_data_index( 0, $key );
	}

	/**
	 * Summary.
	 *
	 * @param $index
	 * @param $key
	 *
	 * @return |null
	 */
	public function get_data_index( $index, $key ) {
		if ( $this->field_name_exist( $key ) ) {
			if ( $index < count( $this->data_objects ) ) {
				return $this->data_objects[ $index ][ $key ];
			} else {
				return null;
			}
		} else {
			Debug_Logger::write_debug_error( 'Invalid key ' . $key );
		}

		return null;
	}

	/**
	 * Summary.
	 *
	 * @return array
	 */
	public function get_data_object_record() {
		return $this->get_data_object_record_indexed( 0 );
	}

	public function get_data_object_record_indexed( $index ) {
		$data_obj_record = array();
		$metadata_list   = $this->get_meta_data_list();
		foreach ( $metadata_list as $key => $metadata ) {
			$data_obj_record[ $key ] = $this->get_data_object_index( $index, $key );
		}

		return $data_obj_record;
	}

	public function get_data_object( $key ) {
		return $this->get_data_object_index( 0, $key );
	}

	public function get_data_object_index( $index, $key ) {
		if ( $this->field_name_exist( $key ) ) {
			$value       = $this->data_objects[ $index ][ $key ];
			$metadata    = $this->get_meta_data( $key );
			$data_object = $this->create_data_object( $metadata, $key, $value );
			return $data_object;
		} else {
			Debug_Logger::write_debug_error( 'Invalid key ' . $key );
		}

		return null;
	}

	public function sort_data( $key, $sort_order, $sort_flag ) {
		if ( $this->get_data_count() > 1 ) {
			if ( $this->field_name_exist( $key ) ) {
				foreach ( $this->data_objects as $i => $row ) {
					$orders[ $i ] = $row[ $key ];
				}
			}

			array_multisort( $orders, $sort_order, $sort_flag, $this->data_objects );
		}
	}

	public function get_copy_all_data() {
		// TODO May have to clone this
		return $this->data_objects;
	}

	public function get_data_count() {
		return count( $this->data_objects );
	}

	public function fetch_data() {
		if ( count( $this->data_objects ) ) {
			array_shift( $this->data_objects );
			array_shift( $this->touched_data );
		}
	}

	public function load_data( $conditions = null, $value = null ) {
		if ( gettype( $conditions ) === 'string' ) {
			$field = $conditions;

			$conditions   = array();
			$conditions[] = array(
				'field' => $field,
				'value' => $value,
			);
		}

		$this->clear_all_data();
		return $this->load_more_data( $conditions );
	}

	public function load_all_data() {
		$this->clear_all_data();
		$condition      = null;
		$records_loaded = $this->load_data_record( $condition );

		if ( $records_loaded ) {
			for ( $index = 0; $index < $records_loaded; $index ++ ) {
				$this->touched_data[] = false;
			}
		}

		return $records_loaded;
	}

	public function load_more_data( $conditions ) {
		if ( isset( $conditions ) ) {
			foreach ( $conditions as $condition ) {
				$where_field = $condition['field'];
				$where_value = $condition['value'];
				if ( ! $this->check_data_type( $where_field, $where_value ) ) {
					Debug_Logger::write_debug_error( 'Wrong data type for key=' . $where_field . ' value=' . $where_value );
				}
			}
		}

		return $this->load_data_record( $conditions );
	}

	public function save_data() {
		return $this->save_data_index( 0 );
	}

	public function save_all_data() {
		$err = false;
		$n   = count( $this->data_objects );
		for ( $index = 0; $index < $n; $index++ ) {
			if ( $this->save_data_index( $index ) === false ) {
				$err = true;
			}
		}

		if ( $err ) {
			return false;
		} else {
			return true;
		}
	}

}
