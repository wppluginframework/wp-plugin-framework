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

namespace WP_PluginFramework\Models;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Database\Wp_Db_Interface;
use WP_PluginFramework\DataTypes\Data_Type;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
abstract class Active_Record extends Model {

	const TABLE_NAME = null;

	protected $database = null;

	/**
	 * Construction.
	 *
	 * @param null $table_name
	 */
	public function __construct( $table_name = null ) {
		$this->database = new Wp_Db_Interface();

		if ( ! $table_name ) {
			$table_name = static::TABLE_NAME;
		}

		parent::__construct( $table_name );
	}

	/**
	 * Summary.
	 *
	 * @param $conditions
	 *
	 * @return bool|int|void
	 */
	protected function load_data_record( $conditions ) {
		$records_loaded = false;

		$db_data_list = $this->database->read( $this->model_name, '*', $conditions );
		if ( $db_data_list ) {
			$records_loaded = count( $db_data_list );
			for ( $i = 0; $i < $records_loaded; $i++ ) {
				$data_list = array();
				foreach ( $db_data_list[ $i ] as $key => $value ) {
					$data_list[ $key ] = $value;

					$normal_type = $this->get_data_type( $key );
					if ( isset( $normal_type ) ) {
						$data_type = gettype( $value );
						if ( $normal_type !== $data_type ) {

							switch ( $data_type ) {
								case 'string':
									switch ( $normal_type ) {
										case 'integer':
											$normal_value      = intval( $value );
											$data_list[ $key ] = $normal_value;
											break;

										case 'double':
											$normal_value      = floatval( $value );
											$data_list[ $key ] = $normal_value;
											break;

										case 'boolean':
											$normal_value      = intval( $value );
											$data_list[ $key ] = $normal_value;
											break;

										default:
											Debug_Logger::write_debug_error( 'Unhandled data type ' . $normal_type );
									}
									break;

								case 'NULL':
									$data_list[ $key ] = null;
									break;

								default:
									Debug_Logger::write_debug_error( 'Unhandled data type ' . $data_type );
							}
						}
					} else {
						Debug_Logger::write_debug_error( 'Unknown column ' . $key . ' in ' . $this->model_name );
					}
				}
				$this->add_data_record( $data_list );
			}
		}

		return $records_loaded;
	}

	public function load_column( $column_list ) {
		$this->clear_all_data();
		$this->data_objects = $this->database->read( $this->model_name, $column_list );

		$n = count( $this->data_objects );
		for ( $index = 0; $index < $n; $index++ ) {
			$this->touched_data[] = false;
		}

		return count( $this->data_objects );
	}

	public function save_data_index( $index ) {
		if ( $this->touched_data[ $index ] ) {
			if ( null === $this->data_objects[ $index ][ static::PRIMARY_KEY ] ) {
				$id = $this->database->write( $this->model_name, $this->data_objects[ $index ] );
				if ( null !== $id ) {
					$this->data_objects[ $index ][ static::PRIMARY_KEY ] = $id;
					return true;
				} else {
					return false;
				}
			} else {
				$condition = array( static::PRIMARY_KEY => strval( $this->data_objects[ $index ][ static::PRIMARY_KEY ] ) );
				return $this->database->update( $this->model_name, $condition, $this->data_objects[ $index ] );
			}
		}
		return false;
	}

	public function delete() {
		if ( count( $this->data_objects ) ) {
			if ( null !== $this->data_objects[0][ static::PRIMARY_KEY ] ) {
				$condition = array( static::PRIMARY_KEY => $this->data_objects[0][ static::PRIMARY_KEY ] );
				$this->database->delete_table_record( $this->model_name, $condition );
				$this->clear_data();
			}
		}
	}

	public function change_field_name_index( $index, $field_name, $new_field_name ) {
		$this->data_objects[ $index ][ $new_field_name ] = $this->data_objects[ $index ][ $field_name ];
		unset( $this->data_objects[ $index ][ $field_name ] );
		$this->touched_data[ $index ] = true;

	}

	public function change_field_name( $field_name, $new_field_name ) {
		$this->change_field_name_index( 0, $field_name, $new_field_name );
	}

	public function change_All_field_name( $field_name, $new_field_name ) {
		$n = count( $this->data_objects );
		for ( $index = 0; $index < $n; $index++ ) {
			$this->change_field_name_index( $index, $field_name, $new_field_name );
		}
	}

	public function create() {
		if ( $this->database->table_exist( $this->model_name ) ) {
			$description_list         = $this->database->get_table_description( $this->model_name );
			$previous_meta_field_name = null;
			$metadata_list            = $this->get_meta_data_list();
			foreach ( $metadata_list as $meta_field_name => $metadata ) {
				$data_object     = $this->create_data_object( $metadata, $meta_field_name );
				$meta_field_type = $data_object->get_database_type();
				$field_found     = false;

				foreach ( $description_list as $description ) {
					$description_field_name = $description['Field'];
					$description_field_type = $description['Type'];

					if ( $meta_field_name === $description_field_name ) {
						$field_found = true;
						if ( strtolower( $meta_field_type ) !== strtolower( $description_field_type ) ) {
							Debug_Logger::write_debug_note( 'Changing table "' . $this->model_name . '" field "' . $meta_field_name . '" type from "' . strtoupper( $description_field_type ) . '" to "' . strtoupper( $meta_field_type ) . '"' );

							$now     = current_time( 'timestamp', true );
							$now_str = date( 'YmdHis', $now );

							$temp_field_name = $meta_field_name . '_' . $now_str;

							Debug_Logger::write_debug_note( 'Create new temporary field "' . $temp_field_name . '"' );
							$this->database->create_table_field( $this->model_name, $temp_field_name, $meta_field_type, $metadata['default_value'], $meta_field_name );

							Debug_Logger::write_debug_note( 'Convert old data from "' . $meta_field_name . '" to "' . $temp_field_name . '"...' );
							$this->load_column( array( static::PRIMARY_KEY, $meta_field_name ) );
							$old_data = $this->get_copy_all_data();
							$this->change_All_field_name( $meta_field_name, $temp_field_name );
							$this->save_all_data();

							Debug_Logger::write_debug_note( 'Compare data...' );
							$this->load_column( array( static::PRIMARY_KEY, $temp_field_name ) );
							$converted_data = $this->get_copy_all_data();
							$convert_error  = false;
							$n              = count( $old_data );
							for ( $i = 0; $i < $n; $i++ ) {
								$old_data_str       = strval( $old_data[ $i ][ $meta_field_name ] );
								$converted_data_str = strval( $converted_data[ $i ][ $temp_field_name ] );
								if ( $old_data_str !== $converted_data_str ) {
								    if( $this->is_numeric_datatype($description_field_type)  or $this->is_numeric_datatype($meta_field_type))
                                    {
                                        $old_data_str       = doubleval( $old_data[ $i ][ $meta_field_name ] );
                                        $converted_data_str = doubleval( $converted_data[ $i ][ $temp_field_name ] );
                                        if ( $old_data_str !== $converted_data_str )
                                        {
                                            Debug_Logger::write_debug_error( 'Error converting data index=' . strval( $i ) . ' Old value "' . $old_data_str . " new value '" . $converted_data_str . "'" );
                                            $convert_error = true;
                                        }
                                    }
								} else {
                                    Debug_Logger::write_debug_error( 'Error converting data index=' . strval( $i ) . ' Old value "' . $old_data_str . " new value '" . $converted_data_str . "'" );
                                    $convert_error = true;
                                }
							}

							if ( $convert_error ) {
								$now                 = current_time( 'timestamp', true );
								$now_str             = date( 'YmdHis', $now );
								$old_data_field_name = $meta_field_name . '_old_' . $now_str;
								$this->database->change_table_rename_field( $this->model_name, $meta_field_name, $old_data_field_name, $description_field_type);
								$this->database->change_table_rename_field( $this->model_name, $temp_field_name, $meta_field_name, $meta_field_type);

								Debug_Logger::write_debug_error( 'Error converting data in table "' . $this->model_name . '"' );
								Debug_Logger::write_debug_error( 'Old data stored in field "' . $old_data_field_name . '"' );
								Debug_Logger::write_debug_error( 'Error converting data. Check table manually or retry.' );
							} else {
								Debug_Logger::write_debug_note( 'Data converted successfully.' );
								Debug_Logger::write_debug_note( 'Delete old field "' . $meta_field_name . '"' );
								$this->database->delete_table_column( $this->model_name, $meta_field_name );
								Debug_Logger::write_debug_note( 'Rename temporary field "' . $temp_field_name . '" to "' . $meta_field_name . '"' );
								$this->database->change_table_rename_field( $this->model_name, $temp_field_name, $meta_field_name, $meta_field_type );
							}
						}
					}
				}

				if ( false === $field_found ) {
					Debug_Logger::write_debug_note( 'Warning: Changing table "' . $this->model_name . '" add field "' . $meta_field_name . '" with type "' . strtoupper( $meta_field_type ) . '"' );
					$default_value = $data_object->get_default_value();
					$result = $this->database->create_table_field( $this->model_name, $meta_field_name, $meta_field_type, $default_value, $previous_meta_field_name );
				}

				$previous_meta_field_name = $meta_field_name;

			}
		} else {
			$data_type = Data_Type::format_data_type_name( 'Id_Type' );

			$data_object = new $data_type();

			$db_metadata = array(
				static::PRIMARY_KEY => array(
					'db_type'       => $data_object->get_database_type(),
					'default_value' => null,
				),
			);

			$metadata_list = $this->get_meta_data_list();
			foreach ( $metadata_list as $field_name => $metadata ) {
				$data_object = $this->create_data_object( $metadata, $field_name );

				$db_metadata[ $field_name ] = array(
					'db_type'       => $data_object->get_database_type(),
					'default_value' => $data_object->get_default_value(),
					'db_collation'  => $data_object->get_database_collation(),
				);
			}

			$this->database->create_table( $this->model_name, $db_metadata, self::PRIMARY_KEY );
		}
	}

    public function is_numeric_datatype($db_type) {
	    $numeric_list = array('integer', 'int', 'smallint', 'tinyint', 'mediumint', 'bitint', 'unsigned', 'float', 'double', 'decimal', 'numeric');
	    foreach($numeric_list as $numeric_type) {
            if ( strpos ( strtolower ( $db_type ), $numeric_type) !== false ) {
                return true;
            }
        }
        return  false;
    }

	public function remove() {
		$this->database->remove_table( $this->model_name );
	}
}
