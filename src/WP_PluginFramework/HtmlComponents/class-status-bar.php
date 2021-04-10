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

use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\HtmlElements\Span;
use WP_PluginFramework\HtmlElements\Button;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Status_Bar extends Html_Base_Component {

	const STATUS_SUCCESS = 'success';
	const STATUS_INFO    = 'info';
	const STATUS_WARNING = 'warning';
	const STATUS_ERROR   = 'error';

	const TYPE_REMOVABLE_BLOCK = 'block';
	const TYPE_INLINE_TEXT     = 'text';

	/** @var string Type const code. */
	protected $type;
	/** @var string Status const code. */
	protected $status;
	/** @var string Text to be displayed on StatusBar. */
	protected $text;


	/**
	 * Construction.
	 *
	 * @param null $type
	 * @param null $text
	 * @param null $status
	 */
	public function __construct( $type = null, $text = null, $status = null ) {
		$attributes = array();

		$properties['type']   = $type;
		$properties['text']   = $text;
		$properties['status'] = $status;

		parent::__construct( $attributes, $properties, null, 'div', true );
	}

    public function set_id( $id ) {
        $this->set_attribute('id', $id);
        parent::set_id( $id);
    }

	/**
	 * Summary.
	 *
	 * @param $text
	 * @param $status
	 */
	public function set_status_html( $text, $status ) {
		if ( is_string( $text ) ) {
			if ( strpos( strtolower( $text ), '<strong>' ) === false ) {
				$text = new Html_Text( $text );
				$text = new Strong( $text );
			} else {
				$text = new Html_Text( $text );
			}
		}
		$this->set_status_text( $text, $status );
	}

	/**
	 * Summary.
	 *
	 * @param $text
	 * @param $status
	 */
	public function set_status_text( $text, $status ) {
		$this->text   = $text;
		$this->status = $status;

		if (isset($this->id)) {
			$selector = 'div#' . $this->id;
			$bar      = $this->create_html_bar( $text, $status );
			$html     = $bar->draw_html();

			$this->update_client_dom( $selector, 'html', array( $html ) );
		} else {
			Debug_Logger::write_debug_error( 'Component not registered.');
		}
	}

	/**
	 * Summary.
	 *
	 * @param $text
	 * @param $status
	 *
	 * @return Div
	 */
	public function create_html_bar( $text, $status ) {
		if ( self::TYPE_REMOVABLE_BLOCK === $this->type ) {
			$strong_text         = new Strong( $text );
			$p                   = new P( $strong_text );
			$attributes['class'] = 'wppmvcf-status-bar updated settings-error notice is-dismissible';
			$div                 = new Div( $p, $attributes );

			$span   = new Span( 'Dismiss this notice.', array( 'class' => 'screen-reader-text' ) );
			$button = new Button( $span, array( 'class' => 'notice-dismiss' ) );
			$div->add_content( $button );
		} else {
			$text = $this->text;
			if ( is_string( $text ) ) {
				if ( strpos( strtolower( $text ), '<strong>' ) === false ) {
					$text = new Strong( $text );
				}
			}
			$p = new P( $text );

			$attributes['class'] = 'wppmvcf-status-bar-' . $status;
			$div                 = new Div( $p, $attributes );
		}

		return $div;
	}

	/**
	 * Summary.
	 *
	 * @param null $config
	 */
	public function create_content( $config = null ) {
		if ( $this->text ) {
			$bar = $this->create_html_bar( $this->text, $this->status );
			$this->add_content( $bar );
		}
	}
}
