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

namespace WP_PluginFramework\HtmlElements;

defined( 'ABSPATH' ) || exit;

/**
 * Summary.
 *
 * Description.
 */
class Td extends Html_Base_Element {

	/**
	 * Construction.
	 *
	 * @param null $content
	 * @param null $attributes
	 */
	public function __construct( $content = null, $attributes = null ) {
		parent::__construct( 'td', true, $content, $attributes );
	}

    public function create_array_content( $contents ) {
        $contents_created = array();

        foreach ( $contents as $content )
        {
            $content_type = gettype($content);
            switch ($content_type)
            {
                case 'object':
                    $content->create_content();
                    $content_created = $content->get_content();
                    break;

                case 'string':
                    $content_created = esc_html( $content );
                    break;

                case 'integer':
                case 'double':
                    $content_created = strval( $content );
                    break;

                default:
                    $content_created = 'Error: Undefined content type in td array.';
                    break;
            }
            $contents_created = array_merge($contents_created, $content_created );
        }

        return $contents_created;
    }

	public function create_content($config = null)
    {
        $content = null;

        $content_type = gettype( $this->contents );
        switch ( $content_type ) {
            case 'object':
                $content = $this->contents->create_content();
                break;

            case 'string':
                $content = esc_html( strval($this->contents) );
                break;

            case 'integer':
            case 'double':
                $content = strval( $this->contents );
                break;

            case 'array':
                $content = $this->create_array_content( $this->contents );
                break;

            default:
                $content = 'Error: Undefined content type in td.';
                break;
        }

        $this->set_content($content);
    }
}
