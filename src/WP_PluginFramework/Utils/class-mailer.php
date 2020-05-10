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

namespace WP_PluginFramework\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Summary.
 *
 * Description.
 */
class Mailer {

	private $receiver_address_list = array();
	private $from_address          = '';
	private $copy_address_list     = array();
	private $subject               = '';
	private $body                  = '';

	/**
	 * Construction.
	 */
	public function __construct( $receiver_address = '' ) {
		if ( '' !== $receiver_address ) {
			$this->add_receiver_address( $receiver_address );
		}
	}

	/**
	 * Summary.
	 *
	 * @param $email_adr
	 *
	 * @return bool
	 */
	public function set_from_address( $email_adr ) {
		$email_adr = $this->validate_email_address($email_adr);
		if ($email_adr) {
			$this->from_address = $email_adr;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $email_adr
	 *
	 * @return bool
	 */
	public function add_receiver_address( $email_adr ) {
		$email_adr = $this->validate_email_address($email_adr);
		if ($email_adr) {
			$this->receiver_address_list[] = $email_adr;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $email_adr
	 *
	 * @return bool
	 */
	public function add_copy_address( $email_adr ) {
		$email_adr = $this->validate_email_address($email_adr);
		if ($email_adr) {
			$this->copy_address_list[] = $email_adr;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $subject_str
	 */
	public function aet_subject( $subject_str ) {
		$this->subject = $subject_str;
	}

	/**
	 * Summary.
	 *
	 * @param $body_str
	 */
	public function set_body( $body_str ) {
		$this->body = wpautop( $body_str );
	}

	/**
	 * Summary.
	 *
	 * @return bool
	 */
	public function send() {
		$result  = false;
		$headers = array( 'Content-Type: text/html' );

		if ( '' !== $this->from_address ) {
			$headers[] = 'From: ' . $this->from_address;
			$headers[] = 'Reply-To: ' . $this->from_address;
		}

		foreach ( $this->copy_address_list as $cc ) {
			$headers[] = 'Cc: ' . $cc;
		}

		if ( '' !== $this->receiver_address_list[0] ) {
			Debug_Logger::write_debug_note( Debug_Logger::obfuscate( $this->receiver_address_list[0] ), $this->subject );

			if ( wp_mail( $this->receiver_address_list[0], $this->subject, $this->body, $headers ) ) {
				$result = true;
			} else {
				Debug_Logger::write_debug_warning( 'E-mail not sent.', Debug_Logger::obfuscate( $this->receiver_address_list[0] ), $this->subject );
			}
		}

		return $result;
	}


	public function validate_email_address($email)
	{
		$from_name = '';
		$from_email = '';

		$bracket_pos = strpos( $email, '<' );
		if ( false !== $bracket_pos ) {
			// Text before the bracketed email is the "From" name.
			if ( $bracket_pos > 0 ) {
				$from_name = substr( $email, 0, $bracket_pos - 1 );
				$from_name = str_replace( '"', '', $from_name );
				$from_name = trim( $from_name );
			}

			$from_email = substr( $email, $bracket_pos + 1 );
			$from_email = str_replace( '>', '', $from_email );
			$from_email = trim( $from_email );

			// Avoid setting an empty $from_email.
		} elseif ( '' !== trim( $email ) ) {
			$from_email = trim( $email );
		}

		if (filter_var($from_email, FILTER_VALIDATE_EMAIL))
		{
			if($from_name) {
				$email = $from_name . ' <' . $from_email . '>';
			} else {
				$email = $from_email;
			}
		}
		else
		{
			$email = false;
		}

		return $email;
	}
}
