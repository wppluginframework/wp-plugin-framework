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

namespace WP_PluginFramework\HtmlComponents;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Html_Base_Element;

/**
 * Summary.
 *
 * Description.
 */
abstract class Html_Base_Component extends Html_Base_Element {

	protected $id;

	/**
	 * Construction.
	 *
	 * @param null $attributes
	 * @param null $properties
	 * @param null $content
	 * @param null $tag
	 * @param null $end_tag
	 */
	public function __construct( $attributes = null, $properties = null, $content = null, $tag = null, $end_tag = null ) {
		parent::__construct( $tag, $end_tag, $content, $attributes, $properties );
	}

    /**
     * Summary.
     *
     * @param $name
     */
    public function set_id( $id ) {
        $this->id  = $id;
    }

    /**
     * Summary.
     *
     * @return |null
     */
    public function get_id() {
        return  $this->id;
    }

	public function pick_client_side_value( $client_side_values ) {
		return null;
	}

	/**
	 * Summary.
	 *
	 * @param $value
	 */
	public function set_value( $value ) {    }

	/**
	 * Summary.
	 */
	public function get_value( ) {
		return null;
	}
}
