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

use WP_PluginFramework\HtmlElements\A;

/**
 * Summary.
 *
 * Description.
 */
class Link extends Html_Base_Component {

	/** @var string URL link. */
	protected $href;
	/** @var string Text to be displayed for link. */
	protected $text;

	/**
	 * Construction.
	 *
	 * @param null $href
	 * @param null $text
	 */
	public function __construct( $href = null, $text = null ) {
		$attributes = null;

		$properties['href'] = $href;
		$properties['text'] = $text;

		parent::__construct( $attributes, $properties, null, 'div', true );
	}

	/**
	 * Summary.
	 *
	 * @param null $config
	 */
	public function create_content( $config = null ) {
		$a                  = new A( $this->text, $this->href );
		$this->add_content( $a );
	}
}
