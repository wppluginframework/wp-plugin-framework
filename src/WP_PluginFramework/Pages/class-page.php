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

namespace WP_PluginFramework\Pages;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Html_Base_Element;

/**
 * Summary.
 *
 * Description.
 */
class Page extends Html_Base_Element {

    public function __construct( $content = null, $properties = null ) {
        $attributes = array('class' => 'wrap');
        parent::__construct( 'div', true, $content, $attributes, $properties );
    }

    public function create_content( $config = null ) {}

    public function draw() {
        $this->create_content();
        return $this->draw_html();
    }

}
