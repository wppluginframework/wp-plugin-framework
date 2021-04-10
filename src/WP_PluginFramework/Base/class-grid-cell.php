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

namespace WP_PluginFramework\Base;

use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\Th;
use WP_PluginFramework\Utils\DebugLogger;

defined( 'ABSPATH' ) || exit;

/**
 * Summary.
 *
 * Description.
 */
class Grid_Cell extends Content_Object{

    const CELL_ELEMENT = 'cell_element';

    protected $cell_element = 'td';

    protected $attributes = array();

    public function __construct($content=null, $attributes=null, $properties = null)
    {
        parent::__construct( $content, $properties );
        $this->set_attributes($attributes);
    }

    public function set_attributes( $attributes ) {
        return $this->set_property('attributes', $attributes);
    }

    public function set_attribute( $key, $value ) {
        return $this->set_property_key_value('attributes', $key, $value);
    }

    public function add_attribute_value( $key, $value ) {
        return $this->add_property_key_value('attributes', $key, $value);
    }

    public function remove_attribute( $key ) {
        return $this->remove_property_key_value('attributes', $key);
    }

    public function remove_attribute_value( $key, $value ) {
        return $this->remove_property_key_value('attributes', $key, $value);
    }

    public function set_id( $id ) {
        return $this->set_attribute('id', $id);
    }

    public function remove_id( $id ) {
        return $this->set_attribute('class', $id );
    }

    public function set_class( $class ) {
        return $this->set_attribute('class', $class);
    }

    public function add_class( $class ) {
        return $this->add_attribute_value('class', $class);
    }

    public function remove_class( $class=null ) {
        return $this->remove_attribute_value( 'calss', $class );
    }

    public function set_style( $class ) {
        return $this->set_attribute('style', $class);
    }

    public function add_style( $class ) {
        return $this->add_attribute_value('style', $class);
    }

    public function remove_style( $class=null ) {
        return $this->remove_attribute_value( 'style', $class );
    }

    public function create_content() {
        $content = null;
        switch($this->cell_element) {
            case 'th':
                $content = new Th($this->contents, $this->attributes);
                break;
            case 'td':
                $content = new Td($this->contents, $this->attributes);
                break;
            default:
                DebugLogger::WriteDebugError('Unsupported Cell_Grid element', $this->cell_element);
                break;
        }
        return $content;
    }
}
