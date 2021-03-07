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

use WP_PluginFramework\Pages\Page;
use WP_PluginFramework\HtmlElements\H;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Page extends Page {

    public function create_content( $config = null ) {
        $headline = $this->get_property('headline');
        if( ! $headline ) {
            $headline = get_admin_page_title();
        }
        $h = new H(1, $headline);
        $this->prepend_content($h);

        parent::create_content();
    }

}
