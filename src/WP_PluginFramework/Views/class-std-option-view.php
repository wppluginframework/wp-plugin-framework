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

use WP_PluginFramework\DataTypes\Data_Type;
use WP_PluginFramework\HtmlComponents\Push_Button;

class Std_Option_View extends Std_View {

    /** @var Push_Button */
    public $std_submit;

    public function __construct( $id, $controller, $model, $properties = array() )
    {
        foreach($model::$meta_data as $name => $meta) {
            $data_object = Data_Type::create_data_object($meta, $name);
            $this->$name = $data_object->get_html_component();
        }

        $this->std_submit = new Push_Button('Save');

        parent::__construct( $id, $controller, $model, $properties );

        $this->std_status_bar->set_id('admin_status_bar');
    }

    public function create_content( $parameters = null ) {
        foreach ($this->components as $name => $component) {
            if(($name != 'std_submit') and ($name != 'std_status_bar'))
            {
                $this->add_form_input($name, $component);
            }
        }

        $this->add_button( 'std_submit', $this->std_submit );
        $this->std_submit->set_primary( true );

        parent::create_content( $parameters );
    }
}
