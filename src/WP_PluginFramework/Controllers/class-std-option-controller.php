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

use WP_PluginFramework\HtmlComponents\Status_Bar;
use WP_PluginFramework\Models\Model;

/**
 * Summary.
 *
 * Description.
 */
class Std_Option_Controller extends Std_Controller
{

    public function __construct($model_class = null, $view_class = null, $id = null)
    {
        if(!isset($view_class)) {
            $view_class = 'WP_PluginFramework\Views\Std_Option_View';
        }
        parent::__construct($model_class, $view_class, $id);
    }

}
