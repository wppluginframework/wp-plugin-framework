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

namespace WP_PluginFramework\Views;

use WP_PluginFramework\HtmlComponents\Sort_List;
use WP_PluginFramework\HtmlElements\P;

defined( 'ABSPATH' ) || exit;

/**
 * Summary.
 *
 * Description.
 */
class List_View extends Form_View {
    protected $meta_data = array();
    protected $data_objects = array();
    protected $status = null;
    protected $message = null;

    public function set_values( $values ) {
        if ( isset( $values ) ) {
            foreach ( $values as $id => $value ) {
                if($id === 'meta_data') {
                    $this->meta_data = $value;
                }
                if($id === 'data_objects') {
                    $this->data_objects = $value;
                }
                if($id === 'status') {
                    $this->status = $value;
                }
                if($id === 'message') {
                    $this->message = $value;
                }
            }
        }
    }

    public function create_content( $parameters = null ) {
        $attribute = array('class' => 'wpf_list_view');
        $sort_list = new Sort_List(null, $attribute);

        $labels = array();
        //array_push($labels, 'ID');
        foreach ($this->meta_data as $key => $meta) {
            if(array_key_exists('label', $meta)) {
                $label = $meta['label'];
            } else {
                $label = $key;
            }
            array_push($labels, $label);
        }
        $sort_list->add_row_header($labels);

        $sort_list->add_rows($this->data_objects);

        $this->add_content($sort_list);

        if($this->message) {
            $p = new P($this->message);
            $this->add_content($p);
        }

        parent::create_content( $parameters );
    }

}
