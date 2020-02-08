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

namespace WP_PluginFramework\Database;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Wp_Db_Interface {

	const WHERE_EQUAL         = '=';
	const WHERE_NOT_EQUAL     = '!=';
	const WHERE_GREATER       = '>';
	const WHERE_GREATER_EQUAL = '>=';
	const WHERE_LESS          = '<';
	const WHERE_LESS_EQUAL    = '<=';

	private $prepare_error     = false;
	private $last_query_result = null;

	/**
	 * Construction.
	 */
	public function __construct() {     }

	/**
	 * Summary.
	 *
	 * @param $table_name
	 * @param $select_columns
	 * @param null           $where
	 *
	 * @return null
	 */
	public function read( $table_name, $select_columns, $where = null ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );
		$safe_select_columns      = $this->prepare_sql_select( $select_columns );

		$sql = 'SELECT ' . $safe_select_columns . ' FROM `' . $safe_prefixed_table_name . '`';

		if ( isset( $where ) ) {
			$safe_where = $this->prepare_sql_where( $where );
			$sql       .= ' WHERE ' . $safe_where;
		}

		if ( $this->query_get_result( $sql ) ) {
			return $this->last_query_result;
		} else {
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 * @param $data_array
	 *
	 * @return null
	 */
	public function write( $table_name, $data_array ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );

		/* QueryUpdate will do the sql escape */
		if ( $this->query_insert( $safe_prefixed_table_name, $data_array ) ) {
			return $this->last_query_result;
		} else {
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 * @param $where
	 * @param $data
	 *
	 * @return bool
	 */
	public function update( $table_name, $where, $data ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );

		/* QueryUpdate will do the sql escape */
		return $this->query_update( $safe_prefixed_table_name, $data, $where );
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 * @param $condition
	 *
	 * @return bool
	 */
	public function delete_table_record( $table_name, $condition ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );

		/* QueryUpdate will do the sql escape */
		return $this->query_delete( $safe_prefixed_table_name, $condition );
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 * @param $meta_data_list
	 * @param $primary_key
	 *
	 * @return bool
	 */
	public function create_table( $table_name, $meta_data_list, $primary_key ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );
		$safe_meta_data_list      = $this->prepare_data_value( $meta_data_list, false, 2 );
		$safe_primary_key         = $this->prepare_data_value( $primary_key, true );

		$safe_fields = '';
		foreach ( $safe_meta_data_list as $safe_field_name => $safe_meta ) {
			$safe_db_type = $safe_meta['db_type'];

			if ( '' !== $safe_fields ) {
				$safe_fields .= ',';
			}

			$safe_fields .= '`' . $safe_field_name . '` ' . $safe_db_type;

			if ( $safe_field_name === $safe_primary_key ) {
				$safe_fields .= ' AUTO_INCREMENT';
			}

			if ( isset( $safe_meta['db_collation'] ) ) {
				$safe_fields .= ' COLLATE `' . $safe_meta['db_collation'] . '`';
			}
		}

		if ( $safe_primary_key ) {
			$safe_fields .= ',PRIMARY KEY (`' . $safe_primary_key . '`)';
		}

		$sql = 'CREATE TABLE `' . $safe_prefixed_table_name . '` (' . $safe_fields . ')';

		return $this->query_get_result( $sql );
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function remove_table( $table_name ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );

		$sql = 'DROP TABLE `' . $safe_prefixed_table_name . '`';

		return $this->query_get_result( $sql );
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 * @param $column_list
	 * @param $column_type_list
	 * @param null             $column_default_value
	 * @param null             $column_location
	 *
	 * @return bool
	 */
	public function create_table_field( $table_name, $column_list, $column_type_list, $column_default_value = null, $column_location = null ) {
		$safe_prefixed_table_name  = $this->prepare_table_name( $table_name );
		$safe_column_list          = $this->prepare_key_value( $column_list, true, 1 );
		$safe_colum_type_list      = $this->prepare_data_value( $column_type_list, true, 1 );
		$safe_column_default_value = $this->prepare_data_value( $column_default_value, false, 1 );
		$safe_column_location      = $this->prepare_key_value( $column_location, false );

		$sql = 'ALTER TABLE `' . $safe_prefixed_table_name . '` ADD COLUMN `' . $safe_column_list . '` ' . $safe_colum_type_list;

		if ( $safe_column_default_value ) {
			$sql .= ' DEFAULT `' . $safe_column_default_value . '`';
		}

		if ( $safe_column_location ) {
			$sql .= ' AFTER `' . $safe_column_location . '`';
		}

		return $this->query_get_result( $sql );
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 * @param $column
	 * @param $new_column
	 * @param $colum_data_type
	 *
	 * @return bool
	 */
	public function change_table_rename_field( $table_name, $column, $new_column, $colum_data_type ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );
		$safe_field_name          = $this->prepare_key_value( $column );
		$safe_new_field_name      = $this->prepare_key_value( $new_column );
		$safe_db_field_type       = $this->prepare_data_value( $colum_data_type );

		$sql = 'ALTER TABLE `' . $safe_prefixed_table_name . '` CHANGE COLUMN `' . $safe_field_name . '` `' . $safe_new_field_name . '` ' . $safe_db_field_type;

		return $this->query_get_result( $sql );
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 * @param $column
	 *
	 * @return bool
	 */
	public function delete_table_column( $table_name, $column ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );
		$safe_column              = $this->prepare_key_value( $column );

		$sql = 'ALTER TABLE `' . $safe_prefixed_table_name . '` DROP COLUMN `' . $safe_column . '`';

		return $this->query_get_result( $sql );
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function table_exist( $table_name ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );

		if ( $this->query_get_result( 'SHOW TABLES' ) ) {
			foreach ( $this->last_query_result as $table_entry ) {
				foreach ( $table_entry as $key => $table_name ) {
					if ( $table_name === $safe_prefixed_table_name ) {
						return true;
					}
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $table_name
	 *
	 * @return null
	 */
	public function get_table_description( $table_name ) {
		$safe_prefixed_table_name = $this->prepare_table_name( $table_name );

		$sql = 'DESCRIBE `' . $safe_prefixed_table_name . '`';

		if ( $this->query_get_result( $sql ) ) {
			return $this->last_query_result;
		} else {
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $safe_prefixed_table_name
	 * @param $safe_data_array
	 *
	 * @return bool
	 */
	private function query_insert( $safe_prefixed_table_name, $safe_data_array ) {
		$this->last_query_result = null;

		if ( ! $this->prepare_error ) {
			global $wpdb;

			$this->last_query_result = $wpdb->insert( $safe_prefixed_table_name, $safe_data_array );

			if ( $wpdb->last_error ) {
				$this->last_query_result = null;
				Debug_Logger::write_debug_error( 'Database insert error:' . $wpdb->last_error );
			} else {
				$this->last_query_result = $wpdb->insert_id;
				return true;
			}
		} else {
			Debug_Logger::write_debug_error( 'Database could not insert table ' . $safe_prefixed_table_name );
		}

		return false;
	}

	/**
	 * Summary.
	 *
	 * @param $safe_prefixed_table_name
	 * @param $safe_data
	 * @param $safe_condition
	 *
	 * @return bool
	 */
	private function query_update( $safe_prefixed_table_name, $safe_data, $safe_condition ) {
		$this->last_query_result = null;

		if ( ! $this->prepare_error ) {
			global $wpdb;

			$this->last_query_result = $wpdb->update( $safe_prefixed_table_name, $safe_data, $safe_condition );

			if ( $wpdb->last_error ) {
				Debug_Logger::write_debug_error( 'Database delete error:' . $wpdb->last_error );
			} else {
				return true;
			}
		} else {
			Debug_Logger::write_debug_error( 'Database could not update table ' . $safe_prefixed_table_name );
		}

		return false;
	}

	/**
	 * Summary.
	 *
	 * @param $safe_prefixed_table_name
	 * @param $safe_condition
	 *
	 * @return bool
	 */
	private function query_delete( $safe_prefixed_table_name, $safe_condition ) {
		$this->last_query_result = null;

		if ( ! $this->prepare_error ) {
			global $wpdb;

			$this->last_query_result = $wpdb->delete( $safe_prefixed_table_name, $safe_condition );

			if ( false === $this->last_query_result ) {
				Debug_Logger::write_debug_error( 'Database delete failed.' );
			}

			if ( 0 === $this->last_query_result ) {
				Debug_Logger::write_debug_error( 'Database delete no data.' );
			}

			if ( $wpdb->last_error ) {
				Debug_Logger::write_debug_error( 'Database delete error:' . $wpdb->last_error );
			} else {
				return true;
			}
		} else {
			Debug_Logger::write_debug_error( 'Database could not delete column ' . $safe_condition . ' in table ' . $safe_prefixed_table_name );
		}

		return false;
	}

	/**
	 * Summary.
	 *
	 * @param $sql
	 *
	 * @return bool
	 */
	private function query_get_result( $sql ) {
		$this->last_query_result = null;

		if ( ! $this->prepare_error ) {
			global $wpdb;

			$this->last_query_result = $wpdb->get_results( $sql, ARRAY_A );

			if ( $wpdb->last_error ) {
				Debug_Logger::write_debug_error( 'Database error:' . $wpdb->last_error . 'in query "' . $sql . '"' );
			} else {
				return true;
			}
		} else {
			Debug_Logger::write_debug_error( 'Database could not query ' . $sql );
		}

		return false;
	}


	/**
	 * Summary.
	 *
	 * @param $table_name
	 *
	 * @return array|string|null
	 */
	private function prepare_table_name( $table_name ) {
		if ( isset( $table_name ) && is_string( $table_name ) ) {
			global $wpdb;

			$prefixed_table_name      = $wpdb->prefix . $table_name;
			$safe_prefixed_table_name = $this->prepare_key_value( $prefixed_table_name );
			return $safe_prefixed_table_name;
		} else {
			Debug_Logger::write_debug_error( 'Table name missing.' );
			$this->prepare_error = true;
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $select_columns
	 *
	 * @return string
	 */
	private function prepare_sql_select( $select_columns ) {
		if ( is_string( $select_columns ) && ( '*' === $select_columns ) ) {
			$safe_select_column_str = '*';
		} else {
			$safe_select_columns = $this->prepare_key_value( $select_columns, true, 1 );

			$safe_select_column_str = '';
			if ( is_array( $safe_select_columns ) ) {
				foreach ( $safe_select_columns as $safe_select_column ) {
					if ( $safe_select_column_str ) {
						$safe_select_column_str .= ',';
					}

					$safe_select_column_str .= '`' . $safe_select_column . '`';
				}
			} elseif ( is_string( $safe_select_columns ) ) {
				if ( '' === $safe_select_columns ) {
					$safe_select_column_str = '*';
				} else {
					$safe_select_column_str .= '`' . $safe_select_columns . '`';
				}
			}
		}
		return $safe_select_column_str;
	}

	/**
	 * Summary.
	 *
	 * @param $where
	 * @param bool  $to_string
	 *
	 * @return array|string|null
	 */
	private function prepare_sql_where( $where, $to_string = true ) {
		$where_str = '';

		$safe_where = $this->prepare_key_value( $where, true, 2 );

		if ( $to_string ) {
			if ( $safe_where ) {
				$more_than_one_where = false;
				$defined_comparators = array(
					self::WHERE_EQUAL,
					self::WHERE_NOT_EQUAL,
					self::WHERE_GREATER,
					self::WHERE_GREATER_EQUAL,
					self::WHERE_LESS,
					self::WHERE_LESS_EQUAL,
				);

				foreach ( $safe_where as $safe_key => $safe_where_item ) {
					if ( is_array( $safe_where_item ) ) {
						$where_field = $safe_where_item['field'];
						$where_value = $safe_where_item['value'];
					} else {
						$where_field = $safe_key;
						$where_value = $safe_where_item;
					}

					if ( isset( $safe_where_item['comparator'] ) ) {
						if ( in_array( $safe_where_item['comparator'], $defined_comparators, true ) ) {
							$comparator = $safe_where_item['comparator'];
						} else {
							Debug_Logger::write_debug_error( 'Invalid comparator "' . $safe_where_item['comparator'] . '"' );
							$this->prepare_error = true;
							return null;
						}
					} else {
						$comparator = '=';
					}

					if ( $more_than_one_where ) {
						$where_str .= ' AND ';
					}

					$where_str .= '`' . $where_field . '`' . $comparator . "'" . $where_value . "'";

					$more_than_one_where = true;
				}
			}
			return $where_str;
		} else {
			return $safe_where;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $value
	 * @param bool  $mandatory_value
	 * @param int   $max_array_level
	 * @param bool  $mandatory_array_value
	 *
	 * @return array|string|null
	 */
	private function prepare_key_value( $value, $mandatory_value = true, $max_array_level = 0, $mandatory_array_value = false ) {
		return $this->prepare_value( $value, true, $mandatory_value, $max_array_level, $mandatory_array_value = false );
	}

	/**
	 * Summary.
	 *
	 * @param $value
	 * @param bool  $mandatory_value
	 * @param int   $max_array_level
	 * @param bool  $mandatory_array_value
	 *
	 * @return array|string|null
	 */
	private function prepare_data_value( $value, $mandatory_value = true, $max_array_level = 0, $mandatory_array_value = false ) {
		return $this->prepare_value( $value, false, $mandatory_value, $max_array_level, $mandatory_array_value = false );
	}

	/**
	 * Summary.
	 *
	 * @param $value
	 * @param $is_key_value
	 * @param $mandatory_value
	 * @param $max_array_level
	 * @param $mandatory_array_value
	 *
	 * @return array|string|null
	 */
	private function prepare_value( $value, $is_key_value, $mandatory_value, $max_array_level, $mandatory_array_value ) {
		if ( isset( $value ) ) {
			$value_type = gettype( $value );
			switch ( $value_type ) {
				case 'array':
					if ( $max_array_level > 0 ) {
						$max_array_level--;

						$safe_array = array();
						foreach ( $value as $array_key => $array_value ) {
							if ( ! isset( $array_value ) ) {
								if ( $mandatory_array_value ) {
									Debug_Logger::write_debug_error( 'Prepare missing array values.' );
									$this->prepare_error = true;
								}
							}
							$safe_array_value = $this->prepare_value( $array_value, $is_key_value, $mandatory_value, $max_array_level, $mandatory_array_value );

							if ( is_integer( $array_key ) ) {
								$safe_array[ $array_key ] = $safe_array_value;
							} elseif ( is_string( $array_key ) ) {
								$safe_array_key = $this->prepare_value( $array_key, $mandatory_value, $is_key_value, $max_array_level, $mandatory_array_value );
								if ( $safe_array_key ) {
									$safe_array[ $safe_array_key ] = $safe_array_value;
								} else {
									Debug_Logger::write_debug_error( 'Database prepare error. key error: ' . $safe_array_key );
									$this->prepare_error = true;
									return null;
								}
							} else {
								Debug_Logger::write_debug_error( 'Database prepare error. invalid object: ' . gettype( $array_key ) );
								$this->prepare_error = true;
								return null;
							}
						}
						return $safe_array;
					} else {
						Debug_Logger::write_debug_error( 'Database prepare error. Invalid array level.' );
						$this->prepare_error = true;
						return null;
					}
					break;

				case 'string':
					$safe_value = esc_sql( $value );

					if ( $is_key_value ) {
						$value_string = strval( $value );
						if ( $safe_value === $value_string ) {
							if ( ! preg_match( '/^[A-Za-z0-9_\-]+$/', $safe_value ) ) {
								Debug_Logger::write_debug_error( 'Database prepare invalid characters: ' . $safe_value );
								$this->prepare_error = true;
							}
						} else {
							Debug_Logger::write_debug_error( 'Database prepare error: ' . $safe_value );
							$this->prepare_error = true;
						}
					}
					return $safe_value;
					break;

				case 'integer':
					return $value;
					break;

				default:
					Debug_Logger::write_debug_error( 'Unsupported type: ' . $value_type );
					$this->prepare_error = true;
					break;
			}
		} else {
			if ( true === $mandatory_value ) {
				Debug_Logger::write_debug_error( 'Prepare missing value' );
				$this->prepare_error = true;
			}
		}

		return null;
	}
}
