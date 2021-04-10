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

use WP_PluginFramework\Utils\Debug_Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Summary.
 *
 * Description.
 */
class Content_Object extends Base_Object {

    /** @var array $contents BaseObject Holds content for this object. */
    protected $contents = array();

    public function __construct($content=null, $properties = null)
    {
        $this->set_content( $content );
        parent::__construct( $properties );
    }

    /**
     * Summary.
     *
     * @param $content
     */
    public function set_content( $content ) {
        $this->contents = array();

        if ( isset( $content ) ) {
            switch ( gettype( $content ) ) {
                case 'array':
                    $this->contents = $content;
                    break;

                default:
                    $this->contents = array( $content );
            }
        }
    }

    /**
     * Summary.
     *
     * @param $content
     */
    public function add_content( $content ) {
        switch ( gettype( $content ) ) {
            case 'array':
                foreach ( $content as $content_item ) {
                    array_push( $this->contents, $content_item );
                }
                break;

            default:
                array_push( $this->contents, $content );
        }
    }

    /**
     * Summary.
     *
     * @param $content
     */
    public function prepend_content( $content ) {
        switch ( gettype( $content ) ) {
            case 'array':
                foreach ( array_reverse($content) as $content_item ) {
                    array_unshift($this->contents, $content_item );
                }
                break;

            default:
                array_unshift( $this->contents, $content );
        }
    }

    /**
     * Summary.
     *
     * @return array
     */
    public function get_content() {
        return $this->contents;
    }

    public function draw() {
        $this->create_content();
        return $this->draw_html();
    }

    /**
     * Summary.
     *
     * @return string
     */
    public function draw_html() {
        $html = '';

        $content_type = gettype( $this->contents );
        switch ( $content_type ) {
            case 'object':
                $html .= $this->contents->draw_html();
                break;

            case 'string':
                $html .= esc_html( $this->contents );
                break;

            case 'integer':
            case 'double':
                $html .= strval( $this->contents );
                break;

            case 'array':
                $html .= $this->draw_array( $this->contents );
                break;

            default:
                $html .= 'Error: Undefined html content type.';
                Debug_Logger::write_debug_error('Undefined html content type.', get_class($this), $content_type);
                break;
        }

        return $html;
    }

    /**
     * Summary.
     *
     * @return string
     */
    public function draw_array( $contents ) {
        $html = '';

        foreach ( $contents as $content ) {
            $content_type = gettype( $content );
            switch ( $content_type ) {
                case 'object':
                    $html .= $content->draw_html();
                    break;

                case 'string':
                    $html .= esc_html( $content );
                    break;

                case 'integer':
                case 'double':
                    $html .= strval( $content );
                    break;

                case 'array':
                    $html .= 'Error: Array in array content.';
                    break;

                default:
                    $html .= 'Error: Undefined html content type in array.';
                    Debug_Logger::write_debug_error('Undefined html content type.', get_class($this), $content_type);
                    break;
            }
        }
        return $html;
    }
}
