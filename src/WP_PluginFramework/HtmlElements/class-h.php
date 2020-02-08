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
class H extends Html_Base_Element {

	/**
	 * Construction.
	 *
	 * @param $level
	 * @param null  $content
	 * @param null  $attributes
	 */
	public function __construct( $level, $content = null, $attributes = null ) {
		$tag = 'h' . strval( $level );
		parent::__construct( $tag, true, $content, $attributes );
	}
}
