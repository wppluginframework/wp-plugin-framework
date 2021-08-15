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

use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlComponents\Nav_Tab_Menu;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Tab_Page extends Admin_Page {

    /** @var array */
    protected $nav_tabs = array();
    /** @var array */
    protected $my_tab_name = null;

    public function __construct( $nav_tabs = null, $my_name = null, $content = null, $properties = null ) {
        $this->nav_tabs = $nav_tabs;
        $this->my_tab_name = $my_name;
        parent::__construct( $content, $properties );
    }

    public function add_nav_tab( $navtab ) {
        $this->nav_tabs[] = $navtab;
    }

    public function set_tab_name( $name ) {
        $this->my_tab_name = $name;
    }

    public function create_content( $config = null ) {
        if( isset( $this->nav_tabs[$this->my_tab_name]['headline'] ))
        {
            $headline = new H(1, $this->nav_tabs[$this->my_tab_name]['headline']);
            $this->prepend_content($headline);
        }

        $this->std_status_bar = new Status_Bar( Status_Bar::TYPE_REMOVABLE_BLOCK );
        $this->std_status_bar->set_id('std_status_bar');
        $this->prepend_content($this->std_status_bar);

        if ( isset( $this->nav_tabs ) ) {
            $attributes['class'] = 'nav-tab-wrapper';
            $navtab              = new Nav_Tab_Menu( $this->nav_tabs, $this->my_tab_name, $attributes );
            $this->prepend_content( $navtab );
        }

        parent::create_content( $config );
    }

}
