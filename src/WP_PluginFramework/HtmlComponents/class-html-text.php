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

/**
 * Summary.
 *
 * Description.
 */
class Html_Text extends Html_Base_Component {

	private $html = '';

	/**
	 * Construction.
	 *
	 * @param $html
	 */
	public function __construct( $html ) {
		$this->html = $html;

		parent::__construct();
	}

	/**
	 * Summary.
	 *
	 * @param $html
	 */
	public function set_html( $html ) {
		$this->html = $html;
	}

	/**
	 * Summary.
	 *
	 * @return string
	 */
	public function get_html() {
		return $this->html;
	}

	/**
	 * Summary.
	 *
	 * @return string
	 */
	public function draw_html() {
		return $this->html;
	}
}
