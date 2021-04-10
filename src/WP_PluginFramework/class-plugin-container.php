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

namespace WP_PluginFramework;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Controllers\Controller;
use WP_PluginFramework\Utils\Security_Filter;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Models\Version_Info;

/**
 * Summary.
 *
 * Description.
 */
class Plugin_Container {

	const WP_PLUGIN_MVC_FRAMEWORK_VERSION  = '0.0.3.1';
	const WP_PLUGIN_FRAMEWORK_AJAX_HANDLER = 'wp_plugin_framework_ajax_handler';
	const WP_PLUGIN_FRAMEWORK_INIT_KEY     = '_init_callback';

	protected static $_instance = null;
	private static $plugin_data = null;

	protected static $plugin_base_file_path             = null;
	protected static $plugin_namespace                  = null;
	private static $wp_framework_namespace              = null;
	protected static $auto_loader_includes              = array();
	protected static $auto_loader_wp_framework_includes = array( 'Base', 'Controllers', 'Database', 'DataTypes', 'HtmlComponents', 'HtmlElements', 'Models', 'Pages', 'Utils', 'Views' );

	public static $permitted_html_elements = array( 'b', 'i', 'u', 'strong', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'br' );

	public static $debug_enable = null;
	public static $debug_level  = null;

	/**
	 * Construction.
	 */
	public function __construct() {
		static::get_plugin_data();

		if ( ! isset( self::$plugin_base_file_path ) ) {
			self::$plugin_base_file_path = self::$plugin_data['PluginFile'];
		}

		if ( ! isset( self::$plugin_namespace ) ) {
			self::$plugin_namespace = __NAMESPACE__;
		}

		self::$wp_framework_namespace = __NAMESPACE__;

		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Summary.
	 */
	protected function define_constants() {  }

	/**
	 * Summary.
	 */
	protected function includes() {  }

	/**
	 * Summary.
	 *
	 * @throws \Exception
	 */
	protected function init_hooks() {
		spl_autoload_register( array( $this, 'auto_loader' ) );

		add_action( 'plugins_loaded', array( $this, 'plugin_loaded' ) );
		add_action( 'init', array( $this, 'Init' ) );
		add_action( 'wp_ajax_' . self::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER, array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_nopriv_' . self::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER, array( $this, 'ajax_handler' ) );

		register_activation_hook( self::$plugin_base_file_path, array( get_called_class(), 'activate_plugin' ) );
		register_deactivation_hook( self::$plugin_base_file_path, array( get_called_class(), 'deactivate_plugin' ) );
		register_uninstall_hook( self::$plugin_base_file_path, array( get_called_class(), 'uninstall_plugin' ) );
	}

	public static function auto_loader( $class_name ) {
		if ( strncmp( $class_name, self::$plugin_namespace . '\\', ( strlen( self::$plugin_namespace ) + 1 ) ) === 0 ) {
			$class_name2 = substr( $class_name, strlen( self::$plugin_namespace ) + 1 );
			$class_name2 = str_replace( '_', '-', $class_name2 );
			$class_name2 = strtolower( $class_name2 );

			foreach ( self::$auto_loader_includes as $include ) {
				$filename = plugin_dir_path( self::$plugin_base_file_path ) . 'src/' . $include . '/class-' . str_replace( '\\', '/', $class_name2 ) . '.php';
				if ( file_exists( $filename ) ) {
					include $filename;
					if ( class_exists( $class_name ) ) {
						return true;
					}
				}
			}
		} elseif ( strncmp( $class_name, self::$wp_framework_namespace . '\\', ( strlen( self::$wp_framework_namespace ) + 1 ) ) === 0 ) {
			$class_names = explode( '\\', $class_name );
			$namespace   = $class_names[1];
			$class_name2 = $class_names[2];
			$class_name2 = str_replace( '_', '-', $class_name2 );
			$class_name3 = strtolower( $class_name2 );
			$filename    = plugin_dir_path( self::$plugin_base_file_path ) . 'vendor/wppluginframework/wp-plugin-framework/src/WP_PluginFramework/' . $namespace . '/class-' . str_replace( '\\', '/', $class_name3 ) . '.php';
			if ( file_exists( $filename ) ) {
				include $filename;
				if ( class_exists( $class_name ) ) {
					return true;
				}
			}
		}
		return false;
	}

	public function plugin_loaded() {
		$language_path = $this->get_plugin_slug() . '/asset/languages/';
		load_plugin_textdomain( self::$plugin_data['TextDomain'], false, $language_path );

		$plugin_version_data_installed = $this->get_existing_db_version_data();
		if ( false === $plugin_version_data_installed ) {
			$plugin_version_data = static::get_plugin_version_data();
			static::upgrade_version_info( $plugin_version_data, Version_Info::PLUGIN_INSTALL_STATE_ACTIVATED );
		}
		$this->check_upgrade( $plugin_version_data_installed );
	}

	/**
	 * Summary.
	 *
	 * @return bool
	 */
	protected function safe_create_controller() {
		$controller_name = Security_Filter::safe_read_post_request( Controller::PROTECTED_DATA_CONTROLLER, Security_Filter::CLASS_NAME );

		if ( $controller_name )
        {
            /* jQuery ajax post adds double slashes */
            $controller_class = stripslashes($controller_name);

            $controller_class_data = explode('\\', $controller_class);
            if (count($controller_class_data) === 2)
            {
                if ($controller_class_data[0] === self::$plugin_namespace)
                {
                    if (substr($controller_class_data[1], -10) === 'Controller')
                    {
                        $safe_controller_class = $controller_class_data[0] . '\\' . $controller_class_data[1];
                        if (class_exists($safe_controller_class))
                        {
                            $controller = new $safe_controller_class();
                            return $controller;
                        }
                    }
                }
            }
            elseif (count($controller_class_data) === 3)
            {
                if ($controller_class_data[0] === self::$wp_framework_namespace)
                {
                    if ($controller_class_data[1] === 'Controllers')
                    {
                        if (substr($controller_class_data[2], -10) === 'Controller')
                        {
                            $safe_controller_class = $controller_class_data[0] . '\\' . $controller_class_data[1] . '\\' . $controller_class_data[2];
                            if (class_exists($safe_controller_class))
                            {
                                $controller = new $safe_controller_class();
                                return $controller;
                            }
                        }
                    }
                }
            }
        }

		/* Don't flood the debug log due to hacking attempt. Write to buffer until nonce confirmed. */
		Debug_Logger::pause_wp_debug_logging();

		Debug_Logger::write_debug_error( 'Invalid controller name ' . $controller_name );

		return false;
	}

	public function init() {
		$init_event = Security_Filter::safe_read_post_request( self::WP_PLUGIN_FRAMEWORK_INIT_KEY, Security_Filter::CLASS_NAME );

		if ( isset( $init_event ) ) {
			$controller = self::safe_create_controller();
			if ( false !== $controller ) {
				$controller->init_handler( $init_event );
			}
		}
	}

	public function ajax_handler() {
		$controller = self::safe_create_controller();

		if ( false !== $controller ) {
			$response = $controller->ajax_handler();
			echo $response;
		}

		die();
	}

	public static function get_plugin_data( $plugin_data = array() ) {
		if ( ! isset( self::$plugin_data ) ) {
			/* Extract plugin base file from current file path. */
			$filename_length = strlen( __FILE__ );
			$basename        = plugin_basename( __FILE__ );
			$basename_length = strlen( $basename );
			$diff            = $filename_length - $basename_length;
			$path            = substr( __FILE__, 0, $diff );
			$plugin_slug     = substr( $basename, 0, strpos( $basename, '/' ) );
			$plugin_file     = $path . $plugin_slug . '/' . $plugin_slug . '.php';

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			self::$plugin_data               = get_plugin_data( $plugin_file, false, false );
			self::$plugin_data['PluginFile'] = $plugin_file;
			self::$plugin_data['PluginSlug'] = $plugin_slug;
			self::$plugin_data['Version'] = strtolower(trim(self::$plugin_data['Version']));
			$split = explode(' ', self::$plugin_data['Version']);
			if(count ($split) > 1) {
				self::$plugin_data['Edition'] = $split[0];
				self::$plugin_data['Version'] = $split[1];
			} else {
				self::$plugin_data['Edition'] = 'standard';
			}
			self::$plugin_data['EditionCode'] = '';
			self::$plugin_data['EditionRev']  = '';

			/* Create a plugin prefix from author name, using 3 first letter converted to lower case. */
			self::$plugin_data['PluginPrefix'] = substr( strtolower( preg_replace( '/[^a-zA-Z]+/', '', self::$plugin_data['Author'] ) ), 0, 3 );

			foreach ( $plugin_data as $key => $value ) {
				self::$plugin_data[ $key ] = $value;
			}
		}
	}

	public static function get_wp_framework_namespace() {
		return self::$wp_framework_namespace;
	}

	public static function get_plugin_edition() {
		return self::$plugin_data['Edition'];
	}

	public static function get_plugin_version() {
		return self::$plugin_data['Version'] . self::$plugin_data['EditionCode'] . self::$plugin_data['EditionRev'];
	}

	public static function get_plugin_version_data() {
		$version_data            = array();
		$version_data['Edition'] = self::$plugin_data['Edition'];
		$version_data['Version'] = self::$plugin_data['Version'];
		return $version_data;
	}

	public static function get_plugin_version_name( $strip = false, $plugin_version_data = false ) {
		if ( false === $plugin_version_data ) {
			$edition = self::$plugin_data['Edition'];
			$version = self::$plugin_data['Version'];
		} else {
			$edition = $plugin_version_data['Edition'];
			$version = $plugin_version_data['Version'];
		}

		if ( $strip ) {
			$stripped = strtolower( preg_replace( '/[^a-zA-Z0-9-_\.]/', '', $edition ) );
			return $version . '_' . $stripped;
		} else {
			return $version . ' ' . $edition;
		}
	}

	public static function get_plugin_slug() {
		return self::$plugin_data['PluginSlug'];
	}

	public static function get_prefixed_plugin_slug() {
		return self::$plugin_data['PluginPrefix'] . '_' . self::$plugin_data['PluginSlug'];
	}

	public static function get_plugin_name() {
		return self::$plugin_data['Name'];
	}

	public function get_debug_enable() {
		if ( ! isset( self::$debug_enable ) ) {
			self::$debug_enable = 1;
		}
		return self::$debug_enable;
	}

	public function get_debug_level() {
		if ( ! isset( self::$debug_level ) ) {
			self::$debug_level = Debug_Logger::WARNING;
		}
		return self::$debug_level;
	}

	public static function activate_plugin() {
		$plugin_version_name = static::get_plugin_version_name();
		$plugin_version_data = static::get_plugin_version_data();
		$plugin_slug         = static::get_plugin_slug();

		Debug_Logger::write_debug_note( 'Activating ' . $plugin_slug . ' version ' . $plugin_version_name . '.' );

		$language_path = static::get_plugin_slug() . '/asset/languages/';
		load_plugin_textdomain( self::$plugin_data['TextDomain'], false, $language_path );

		$previous_plugin = static::upgrade_version_info( $plugin_version_data, Version_Info::PLUGIN_INSTALL_STATE_ACTIVATED );

		static::check_upgrade( $previous_plugin, true );
	}

	public static function deactivate_plugin() {
		$plugin_version_name = static::get_plugin_version_name();
		$plugin_version_data = static::get_plugin_version_data();
		$plugin_slug         = static::get_plugin_slug();

		Debug_Logger::write_debug_note( 'Deactivating ' . $plugin_slug . ' version ' . $plugin_version_name . '.' );

		static::upgrade_version_info( $plugin_version_data, Version_Info::PLUGIN_INSTALL_STATE_DEACTIVATED );
	}

	public static function uninstall_plugin() {
		$path = plugin_dir_path( self::$plugin_base_file_path ) . 'models';

		if ( is_dir( $path ) ) {
			$filenames = scandir( $path );

			foreach ( $filenames as $filename ) {
				if ( '.' !== $filename[0] ) {
					$class_name = substr( $filename, 6 );
					$class_name = substr( $class_name, 0, strpos( $class_name, '.' ) );
					$class_name = str_replace( '-', '_', $class_name );
					$class_name = ucwords( $class_name, '_' );
					$class_name = self::$plugin_namespace . '\\' . $class_name;
					if ( class_exists( $class_name, true ) ) {
						$model = new $class_name();
						$model->remove();
					}
				}
			}
		}

		$option_name  = self::get_prefixed_plugin_slug() . '_version';
		$version_data = new Version_Info( $option_name );
		$version_data->remove();
	}

	public static function install_plugin() {
		$plugin_version_name = static::get_plugin_version_name();
		$plugin_slug         = self::get_plugin_slug();

		Debug_Logger::write_debug_note( 'Install ' . $plugin_slug . ' version ' . $plugin_version_name . '.' );

		$path = plugin_dir_path( self::$plugin_base_file_path ) . 'models';

		if ( is_dir( $path ) ) {
			$filenames = scandir( $path );

			foreach ( $filenames as $filename ) {
				if ( '.' !== $filename[0] ) {
					if ( 0 === strncmp( $filename, 'class-', 6 ) ) {
						$class_name = substr( $filename, 6 );
						$class_name = substr( $class_name, 0, strpos( $class_name, '.' ) );
						$class_name = str_replace( '-', '_', $class_name );
						$class_name = ucwords( $class_name, '_' );
						$class_name = self::$plugin_namespace . '\\' . $class_name;
						if ( class_exists( $class_name, true ) ) {
							$model = new $class_name();
							$model->create();
						}
						else
						{
                            Debug_Logger::write_debug_error( 'No class ' . $class_name . ' found in ' . $filename . '.' );
                        }
					}
				}
			}
		}
	}

	public static function check_upgrade( $previous_plugin_version_data, $check_reinstalled = false ) {
		if ( ( true === $check_reinstalled ) || ( false === $previous_plugin_version_data ) ) {
			static::install_plugin();
		} else {
			$my_plugin_version = static::get_plugin_version_data();
			if ( $my_plugin_version !== $previous_plugin_version_data ) {
				static::upgrade( $previous_plugin_version_data );
			}
		}
	}

	public static function upgrade( $previous_plugin_version_data ) {
		$plugin_version_name          = static::get_plugin_version_name();
		$previous_plugin_version_name = static::get_plugin_version_name( false, $previous_plugin_version_data );
		$plugin_slug                  = self::get_plugin_slug();
		Debug_Logger::write_debug_note( 'Upgrade ' . $plugin_slug . ' from version ' . $previous_plugin_version_name . ' to ' . $plugin_version_name . '.' );
		$install_state = false;
		self::upgrade_version_info( self::get_plugin_version_data(), $install_state );
	}

	public static function upgrade_version_info( $plugin_version_data, $install_state ) {
		$plugin_slug  = static::get_plugin_slug();
		$plugin_name  = static::get_plugin_name();
		$option_name  = static::get_prefixed_plugin_slug() . '_version';
		$version_data = new Version_Info( $option_name );

		return $version_data->save_version_info( $plugin_version_data, self::WP_PLUGIN_MVC_FRAMEWORK_VERSION, $plugin_slug, $plugin_name, $install_state );
	}

	public static function get_existing_db_edition() {
		$option_name = static::get_prefixed_plugin_slug() . '_version';
		$option      = get_option( $option_name );
		if ( false !== $option ) {
			if ( isset( $option[ Version_Info::PLUGIN_EDITION ] ) ) {
				return $option[ Version_Info::PLUGIN_EDITION ];
			}
		}
		return false;
	}

	public static function get_existing_db_version_data() {
		$option_name = static::get_prefixed_plugin_slug() . '_version';
		$option      = get_option( $option_name );
		if ( false !== $option ) {
			$plugin_data            = array();
			$plugin_data['Edition'] = 'Standard';
			if ( isset( $option[ Version_Info::PLUGIN_EDITION ] ) ) {
				$plugin_data['Edition'] = $option[ Version_Info::PLUGIN_EDITION ];
			}

			if ( isset( $option[ Version_Info::PLUGIN_VERSION ] ) ) {
				$plugin_data['Version'] = $option[ Version_Info::PLUGIN_VERSION ];
				return $plugin_data;
			}
		}

		return false;
	}
}
