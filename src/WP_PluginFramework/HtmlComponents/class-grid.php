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

namespace WP_PluginFramework\HtmlComponents;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Base\Grid_Cell;
use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Tr;

/**
 * Summary.
 *
 * Description.
 */
class Grid extends Table {

	protected $colum_count = 0;
    protected $row_count = 0;
    protected $header_data = array();
    protected $body_data = array();
    protected $footer_data = array();

	public function __construct( $data = array(), $attributes = null, $properties = null ) {
	    if(isset($data))
        {
            $this->body_data = $data;
        }
        $content = null;
		parent::__construct( $content, $attributes );
	}

	public function resize( $rows, $columns, $shrink=false ) {
	    if( $this->row_count > 0 ) {
	        /* Resize existing rows */
	        if( $columns > $this->colum_count ) {
	            for( $y=0; $y<$this->row_count; $y++ ){
                    for( $i = $this->colum_count; $i<$columns; $i++){
                        $cell = new Grid_Cell();
                        array_push($this->body_data[$y], $cell);
                    }
                }
            }
        }

        /* Add new rows */
        for( $y=$this->row_count; $y<$rows; $y++ ){
            $new_row = array();
            for( $i = 0; $i<$columns; $i++){
                $cell = new Grid_Cell();
                array_push($new_row, $cell);
            }
            array_push($this->body_data, $new_row );
        }

        $this->row_count = $rows;
        $this->colum_count = $columns;
    }

    public function add_row( $row=array(), $meta_data=null ) {
        $new_row = array();
        foreach( $row as $column ) {
            $cell = new Grid_Cell( $column );
            array_push($new_row, $cell);
        }
        array_push($this->body_data, $new_row );
    }

    public function add_row_header ( $row=array()){
        $new_row = array();
        $properties = array(Grid_Cell::CELL_ELEMENT => 'th');
        foreach( $row as $column ) {
            $cell = new Grid_Cell( $column, null, $properties );
            array_push($new_row, $cell);
        }
        array_push($this->header_data, $new_row );
    }

    public function add_row_footer ( $row=array() ){
        $new_row = array();
        foreach( $row as $column ) {
            $cell = new Grid_Cell( $column );
            array_push($new_row, $cell);
        }
        array_push($this->footer_data, $new_row );
    }

    public function add_data_objects_row( $data_object ) {
	    if($data_object) {
            $label = $data_object->get_label();
            $row = array($label, $data_object);
            $this->add_row($row);
        }
    }

    public function add_data_objects_rows( $data_objects=array() ) {
        foreach ($data_objects as $data_object)
        {
            $this->add_data_objects_row($data_object);
        }
    }

    public function get_row( $row = -1 ) {
        $row_data = $this->body_data[$row];
        return $row_data;
    }

    public function add_rows( $rows=array(), $meta_data=array(), $count=null ) {
        foreach( $rows as $row ) {
            $this->add_row( $row );
        }
    }

    public function set_row_attribute( $y, $key, $value ) {

    }

    public function add_cell( $content=null, $attributes=array(), $properties=array(), $numbers=1 ) {
        $last = array_key_last ( $this->body_data );
	    for($i=0; $i<$numbers; $i++) {
	        $cell = new Grid_Cell($content, $attributes, $properties);
            array_push($this->body_data[$last], $cell);
        }
    }

    public function add_cell_header( $content=null, $attributes=array(), $properties=array(), $numbers=1 ) {
        $properties[Grid_Cell::CELL_ELEMENT] = 'th';
        $last = array_key_last ( $this->body_data );
        for($i=0; $i<$numbers; $i++) {
            $cell = new Grid_Cell($content, $attributes, $properties);
            array_push($this->body_data[$last], $cell);
        }
    }


    public function add_columns( $content=null, $meta_data=null ) {

    }

    public function set_cell_content( $y, $x, $content ) {

    }

	public function set_cell_data( $cell_data ) {
	    $this->cell_data = $cell_data;
    }


	public function create_content($config = null)
    {
        foreach ($this->header_data as $row) {
            $tr = new Tr();
            foreach($row as $grid_cell) {
                $content = $grid_cell->create_content();
                $tr->add_content($content);
            }
            $this->add_content($tr);
        }

        foreach ($this->body_data as $row) {
            $tr = new Tr();
            foreach($row as $grid_cell) {
                $content = $grid_cell->create_content();
                $tr->add_content($content);
            }
            $this->add_content($tr);
        }

        foreach ($this->footer_data as $row) {
            $tr = new Tr();
            foreach($row as $grid_cell) {
                $content = $grid_cell->create_content();
                $tr->add_content($content);
            }
            $this->add_content($tr);
        }

        parent::create_content($config);
    }
}
