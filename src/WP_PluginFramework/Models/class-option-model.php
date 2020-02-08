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

/**
 * Summary.
 *
 * Description.
 */
class Option_Model extends Model {

	const OPTION_NAME = null;

	/**
	 * Construction.
	 */
	public function __construct( $model_name = null ) {
		if ( ! isset( $model_name ) ) {
			$model_name = static::OPTION_NAME;
		}

		parent::__construct( $model_name );
	}

	public function create() {
		$this->init_default();
		$data_record = $this->get_data_record();
		add_option( $this->model_name, $data_record );
		return $data_record;
	}

	public function remove() {
		delete_option( $this->model_name );
	}

	/**
	 * Summary.
	 *
	 * @param $condition
	 *
	 * @return bool|int
	 */
	protected function load_data_record( $condition ) {
		$data_record = get_option( $this->model_name );

		if ( false !== $data_record ) {
			$data_record_filtered = array();
			foreach ( $this->get_meta_data_list() as $key => $metadata ) {
				if ( isset( $data_record[ $key ] ) ) {
					$data_record_filtered[ $key ] = $data_record[ $key ];
				}
			}

			$this->add_data_record( $data_record_filtered );

			/* Options are always only 1 record. */
			return 1;
		} else {
			/* Option don't exist. */
			return false;
		}
	}

	public function load_column( $field_name_list ) {
	}

	/**
	 * Summary.
	 *
	 * @param $index
	 *
	 * @return bool
	 */
	protected function save_data_index( $index ) {
		$data_record = $this->get_data_record();
		update_option( $this->model_name, $data_record );
		return true;
	}
}
