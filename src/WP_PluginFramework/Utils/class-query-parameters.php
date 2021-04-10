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

use WP_PluginFramework\Base\Base_Object;
use WP_PluginFramework\Database\Wp_Db_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Summary.
 *
 * Description.
 */
class Query_Parameters extends Base_Object
{
    protected $offset = null;
    protected $limit = null;
    protected $fields = array();
    protected $filters = array();
    protected $searches = array();
    protected $orders = array();

    public function set_offset($offset) {
        $this->offset = $offset;
    }

    public function get_offset() {
        return $this->offset;
    }

    public function set_limit($limit) {
        $this->limit = $limit;
    }

    public function get_limit() {
        return $this->limit;
    }

    public function select_all_fields() {
        /* No fields will give as all field by default. */
        $this->fields = null;
    }

    public function add_field($field) {
        $field = trim($field);
        array_push($this->fields, $field);
    }

    public function add_fields($fields) {
        foreach ($fields as $field) {
            $field = trim($field);
            $this->add_field($field);
        }
    }

    public function set_fields($fields) {
        $fields = array_map('trim', $fields);
        $this->fields = array();
        foreach ($fields as $field) {
            $field = trim($field);
            $this->add_field($field);
        }
    }

    public function filter_fields($fields) {
        return array();

        $fields = array_map('trim', $fields);
        $exclusinge_fields = array_intersect($this->fields, $fields);
        $this->fields = $exclusinge_fields;
    }

    public function get_fields() {
        return $this->fields;
    }

    public function set_filters($filters) {
        $this->filters = array();
        foreach ($filters as $key => $value) {
            $this->add_filter($key, $value);
        }
    }

    public function add_filter($key, $value = null) {
        $key = trim($key);
        $filter = array(
            'field' => $key,
            'value' => $value,
            'comparator' => Wp_Db_Interface::WHERE_EQUAL
        );
        array_push($this->filters, $filter);
    }

    public function add_filters($filters) {
        array_push($this->filters, $filters);
    }

    public function get_filters() {
        return $this->filters;
    }

    public function set_search($search) {
        $this->searches = $search;
    }

    public function filter_searches($keys) {
        $filtered_searches = array();
        $invalid_keys = array();
        foreach ($this->searches as $key => $value) {
            if (array_key_exists($key, $keys)) {
                $filtered_searches[$key] = $value;
            }
            else {
                $invalid_keys[$key] = $value;
            }
        }
        $this->searches = $filtered_searches;
        return $invalid_keys;
    }

    public function get_search() {
        return $this->searches;
    }

    public function set_orders($order) {
        $this->orders = $order;
    }

    public function get_orders() {
        return $this->orders;
    }

    public function remap_keys($mapping_table, $keep_non_existing = false) {
        if ($keep_non_existing) {
            foreach ($this->fields as $i => $field) {
                if (array_key_exists($field, $mapping_table)) {
                    $this->fields[$i] = $mapping_table[$field];
                }
            }
        }
        else {
            $new_fields = array();
            foreach ($this->fields as $i => $field) {
                if (array_key_exists($field, $mapping_table)) {
                    $new_fields[] = $mapping_table[$field];
                }
            }
            $this->fields = $new_fields;
        }
    }

    public function filter_data($fields) {
        $invalid_keys = array();
        $invalid_keys = array_merge($this->filter_fields($fields), $invalid_keys);
        $invalid_keys = array_merge($this->filter_searches($fields), $invalid_keys);
        return $invalid_keys;
    }
}
