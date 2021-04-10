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

namespace WP_PluginFramework\Views;

use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\Plugin_Container;

/**
 * Summary.
 *
 * Description.
 */
class Admin_Std_View extends Std_View {

	/**
	 * Construction.
	 *
	 * @param $id
	 * @param $controller
	 */
	public function __construct( $id, $controller ) {
		parent::__construct( $id, $controller );

        $this->admin_status_bar = new Status_Bar( Status_Bar::TYPE_REMOVABLE_BLOCK );
        $this->register_component( 'admin_status_bar', $this->admin_status_bar );

        $this->content_config['form_input_layout']           = 'double_column_table';
        $this->content_config['form_placeholder_table_attr'] = array( 'class' => 'form-table' );
        $this->content_config['form_placeholder_tr_attr']    = null;
        $this->content_config['form_placeholder_th_attr']    = array( 'class' => 'row' );
        $this->content_config['form_placeholder_td_attr']    = null;
        $this->content_config['form_input_encapsulation'] = null;
        $this->content_config['form_input_width'] = '100%';

        /* Make admin panel identical to common WordPress admin panels to get the ordinary admin panel style. */
        $this->remove_div_wrapper();
	}

	/**
	 * Summary.
	 *
	 * @param $id
	 * @param $component
	 */
	protected function register_component( $id, $component ) {
		if ( is_object( $component ) ) {
			$class            = get_class( $component );
			$plugin_container = Plugin_Container::instance();
			$my_space         = $plugin_container->get_wp_framework_namespace();
			switch ( $class ) {
				case $my_space . '\HtmlComponents\PushButton':
					$component->add_class( 'button' );
					break;
			}
		}

		parent::register_component( $id, $component );
	}

    public function create_content( $parameters = null ) {
        $this->add_content( $this->admin_status_bar );

        parent::create_content( $parameters );
    }
}
