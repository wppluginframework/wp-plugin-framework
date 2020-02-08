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

namespace WP_PluginFramework\Views;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\HtmlComponents\Nav_Tab_Menu;
use WP_PluginFramework\HtmlElements\H;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Panel_Std_View extends Admin_Std_View {

	/** @var Status_Bar */
	public $admin_status_bar;
	/** @var array */
	protected $nav_tabs = null;
	/** @var array */
	protected $my_tab_name = null;
	/** @var Div */
	protected $std_view_section = null;
	/** @var Div */
	protected $top_header_section = null;
	/** @var Div */
	protected $bottom_footer_section = null;
	/** @var H */
	protected $page_header = null;

	/**
	 * Construction.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		parent::__construct( $id, $controller );

		$this->add_component( 'admin_status_bar', new Status_Bar( Status_Bar::TYPE_REMOVABLE_BLOCK ) );

		$this->div_wrapper = array( 'class' => 'wpf-admin-area-cell' );

		$this->content_config['form_input_layout']           = 'double_column_table';
		$this->content_config['form_placeholder_table_attr'] = array( 'class' => 'form-table' );
		$this->content_config['form_placeholder_tr_attr']    = null;
		$this->content_config['form_placeholder_th_attr']    = array( 'class' => 'row' );
		$this->content_config['form_placeholder_td_attr']    = null;

		$this->content_config['form_input_encapsulation'] = null;

		$this->content_config['form_input_width'] = '100%';
	}

	public function set_header( $header ) {
		$this->page_header = new H( 1, $header );
	}

	public function add_nav_tab( $navtab ) {
		$this->nav_tabs[] = $navtab;
	}

	public function set_tab_name( $name ) {
		$this->my_tab_name = $name;
	}

	public function set_footer_section( $footer ) {
		$this->bottom_footer_section = $footer;
	}

	public function add_admin_columns() {
		$std_view_attr          = array( 'class' => 'wpf-admin-area-col2 wpf-admin-area-collapsible' );
		$this->std_view_section = new Div( null, $std_view_attr );
	}

	public function create_content( $parameters = null, $wrapper = null ) {
		if ( ! isset( $wrapper ) ) {
			$wrapper = $this;
		}

		$attributes['class'] = 'wrap';
		$div_wrapper         = new Div( null, $attributes );
		$wrapper->add_content( $div_wrapper );
		$wrapper = $div_wrapper;

		if ( isset( $this->page_header ) ) {
			$wrapper->add_content( $this->page_header );
		}

		if ( isset( $this->top_header_section ) ) {
			$wrapper->add_content( $this->top_header_section );
		}

		if ( isset( $this->nav_tabs ) ) {
			$attributes['class'] = 'nav-tab-wrapper';
			$navtab              = new Nav_Tab_Menu( $this->nav_tabs, $this->my_tab_name, $attributes );
			$wrapper->add_content( $navtab, $this->content_config );
		}

		$wrapper->add_content( $this->admin_status_bar );

		if ( isset( $this->std_view_section ) ) {
			$wrapper->add_content( $this->std_view_section );
			$wrapper = $this->std_view_section;
		}

		parent::create_content( $parameters, $wrapper );

		if ( isset( $this->bottom_footer_section ) ) {
			$wrapper->add_content( $this->bottom_footer_section );
		}
	}
}
