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

namespace WP_PluginFramework\Controllers;

defined( 'ABSPATH' ) || exit;

use BCQ_BitcoinBank\Account_Chart_Db_Table;
use WP_PluginFramework\Models\Model;
use WP_PluginFramework\Views\Form_View;
use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class List_Controller extends Form_Controller
{
    public function __construct( $model_class = null, $view_class = null, $id = null ) {
        if ( ! $view_class ) {
            $view_class = 'WP_PluginFramework\Views\List_View';
        }
        parent::__construct( $model_class, $view_class, $id );
    }

    protected function load_model_values( $values = array() ) {
        if ( isset( $this->model ) ) {
            $this->model->load_data();
            $values['data_objects'] = $this->model->get_all_data_objects();
            $values['meta_data'] = $this->model->get_meta_data_list();
        }
        return $values;
    }
}
