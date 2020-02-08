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

namespace WP_PluginFramework\Models;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Plugin_Container;
use WP_PluginFramework\Utils\Debug_Logger;

/**
 * Summary.
 *
 * Description.
 */
class Version_Info extends Option_Model {

	const PLUGIN_SLUG                 = 'plugin_slug';
	const PLUGIN_NAME                 = 'plugin_name';
	const PLUGIN_EDITION              = 'plugin_edition';
	const PLUGIN_VERSION              = 'plugin_version';
	const WP_PLUGIN_FRAMEWORK_NAME    = 'wp_plugin_framework_name';
	const WP_PLUGIN_FRAMEWORK_VERSION = 'wp_plugin_framework_version';
	const INSTALL_DATE_TIME           = 'install_date_time';
	const EDITION_UPGRADE_DATE_TIME   = 'edition_upgrade_date_time';
	const UPGRADE_DATE_TIME           = 'upgrade_date_time';
	const ACTIVATED_DATE_TIME         = 'activated_date_time';
	const DEACTIVATED_DATE_TIME       = 'deactivated_date_time';
	const UNINSTALLED_DATE_TIME       = 'uninstall_date_time';
	const PLUGIN_INSTALL_STATE        = 'plugin_install_state';

	const PLUGIN_INSTALL_STATE_ACTIVATED   = 'activated';
	const PLUGIN_INSTALL_STATE_DEACTIVATED = 'deactivated';
	const PLUGIN_INSTALL_STATE_UNINSTALLED = 'uninstalled';

	/* Metadata describing database fields and data properties: */
	static $meta_data = array(
		self::PLUGIN_SLUG                 => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::PLUGIN_NAME                 => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::PLUGIN_EDITION              => array(
			'data_type'     => 'String_Type',
			'default_value' => 'Standard',
		),
		self::PLUGIN_VERSION              => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
		self::WP_PLUGIN_FRAMEWORK_NAME    => array(
			'data_type'     => 'String_Type',
			'default_value' => 'WP_Plugin_Framework',
		),
		self::WP_PLUGIN_FRAMEWORK_VERSION => array(
			'data_type'     => 'String_Type',
			'default_value' => Plugin_Container::WP_PLUGIN_MVC_FRAMEWORK_VERSION,
		),
		self::INSTALL_DATE_TIME           => array(
			'data_type'     => 'Time_Stamp_Type',
			'default_value' => 0,
		),
		self::EDITION_UPGRADE_DATE_TIME   => array(
			'data_type'     => 'Time_Stamp_Type',
			'default_value' => 0,
		),
		self::UPGRADE_DATE_TIME           => array(
			'data_type'     => 'Time_Stamp_Type',
			'default_value' => 0,
		),
		self::ACTIVATED_DATE_TIME         => array(
			'data_type'     => 'Time_Stamp_Type',
			'default_value' => 0,
		),
		self::DEACTIVATED_DATE_TIME       => array(
			'data_type'     => 'Time_Stamp_Type',
			'default_value' => 0,
		),
		self::UNINSTALLED_DATE_TIME       => array(
			'data_type'     => 'Time_Stamp_Type',
			'default_value' => 0,
		),
		self::PLUGIN_INSTALL_STATE        => array(
			'data_type'     => 'String_Type',
			'default_value' => '',
		),
	);

	public function save_version_info( $plugin_version_data, $framework_version, $plugin_slug, $plugin_name, $install_state = false ) {
		$previous_plugin_version_data = false;

		$result = $this->load_data();
		if ( $result ) {
			$problem                                 = false;
			$previous_plugin_version_data            = array();
			$previous_plugin_version_data['Edition'] = $this->get_data( self::PLUGIN_EDITION );
			$previous_plugin_version_data['Version'] = $this->get_data( self::PLUGIN_VERSION );

			$current_state = $this->get_data( self::PLUGIN_INSTALL_STATE );

			switch ( $current_state ) {
				case self::PLUGIN_INSTALL_STATE_ACTIVATED:
					if ( self::PLUGIN_INSTALL_STATE_ACTIVATED === $install_state ) {
						Debug_Logger::write_debug_warning( 'Plugin was not correctly deactivated last time.' );
						$problem = true;
					}
					if ( self::PLUGIN_INSTALL_STATE_UNINSTALLED === $install_state ) {
						Debug_Logger::write_debug_warning( 'Plugin was not correctly deactivated before uninstall.' );
						$problem = true;
					}
					break;

				case self::PLUGIN_INSTALL_STATE_DEACTIVATED:
					if ( self::PLUGIN_INSTALL_STATE_DEACTIVATED === $install_state ) {
						Debug_Logger::write_debug_warning( 'Plugin was not correctly activated last time.' );
						$problem = true;
					}
					break;

				case self::PLUGIN_INSTALL_STATE_UNINSTALLED:
					if ( self::PLUGIN_INSTALL_STATE_DEACTIVATED === $install_state ) {
						Debug_Logger::write_debug_warning( 'Plugin was not correctly activated last time before deactivation.' );
						$problem = true;
					}
					break;

				default:
					Debug_Logger::write_debug_warning( 'Previous install state missing.' );
					$problem = true;
					break;
			}

			if ( $problem ) {
				$data_record = $this->get_data_record();
				foreach ( $data_record as $key => $value ) {
					Debug_Logger::write_debug_note( 'Previous version info: ' . $key . ': ' . $value );
				}
			}

			if ( $install_state === self::PLUGIN_INSTALL_STATE_ACTIVATED ) {
				if ( $plugin_version_data['Edition'] !== $previous_plugin_version_data['Edition'] ) {
					$this->set_data( self::EDITION_UPGRADE_DATE_TIME, time() );
				}

				if ( $plugin_version_data['Version'] !== $previous_plugin_version_data['Version'] ) {
					$this->set_data( self::UPGRADE_DATE_TIME, time() );
				}
			}
		} else {
			/* First time installed */
			$this->set_data( self::INSTALL_DATE_TIME, time() );
			$previous_plugin_version = false;
		}

		$this->set_data( self::PLUGIN_SLUG, $plugin_slug );
		$this->set_data( self::PLUGIN_NAME, $plugin_name );
		$this->set_data( self::PLUGIN_EDITION, $plugin_version_data['Edition'] );
		$this->set_data( self::PLUGIN_VERSION, $plugin_version_data['Version'] );
		$this->set_data( self::WP_PLUGIN_FRAMEWORK_NAME, 'WP_Plugin_Framework' );
		$this->set_data( self::WP_PLUGIN_FRAMEWORK_VERSION, $framework_version );
		switch ( $install_state ) {
			case self::PLUGIN_INSTALL_STATE_ACTIVATED:
				$this->set_data( self::ACTIVATED_DATE_TIME, time() );
				break;
			case self::PLUGIN_INSTALL_STATE_DEACTIVATED:
				$this->set_data( self::DEACTIVATED_DATE_TIME, time() );
				break;
			case self::PLUGIN_INSTALL_STATE_UNINSTALLED:
				$this->set_data( self::UNINSTALLED_DATE_TIME, time() );
				break;
		}
		if ( $install_state ) {
			$this->set_data( self::PLUGIN_INSTALL_STATE, $install_state );
		}
		$this->save_data();

		return $previous_plugin_version_data;
	}
}
