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

namespace WP_PluginFramework\DataTypes;

use WP_PluginFramework\HtmlElements\A;

defined( 'ABSPATH' ) || exit;

class Money_Address_Type extends String_Type {

	public function validate( $value ) {
	    /* Money Address has ostly same format, '@' is replaced with '*' */
	    $test = str_replace('@', '*', $value);
		if ( is_email( $test ) ) {
			return true;
		} else {
			$this->add_validate_errors('Error. Invalid money address format.' );
			return false;
		}
	}

    public function create_content() {
        $text = $this->get_formatted_text();
        $href = 'payment:address="' . $text . '"';
        $a = new A($text, $href);
        $this->set_content($a);
    }

}
