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

namespace WP_PluginFramework\Controllers;

defined( 'ABSPATH' ) || exit;

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
abstract class Controller {

	const EVENT_TYPE_NONE     = 0;
	const EVENT_TYPE_POST     = 'post';
	const EVENT_TYPE_GET      = 'get';
	const EVENT_TYPE_CLICK    = 'click';
	const EVENT_TYPE_CALLBACK = 'callback';
	const EVENT_TYPE_INIT     = 'init';

	const EVENT_METHOD_GET  = 'get';
	const EVENT_METHOD_POST = 'post';
	const EVENT_METHOD_AJAX = 'ajax';
	const EVENT_METHOD_INIT = 'init';

	const PROTECTED_DATA_WP_NONCE         = '_wpnonce';
	const PROTECTED_DATA_CONTROLLER       = '_controller';
	const PROTECTED_DATA_PROXY_CONTROLLER = '_proxy_controller';
	const PROTECTED_DATA_VIEW             = '_view';

	/* Received data from client side, nonce protected. */
	private $nonce_protected_data = array(
		self::PROTECTED_DATA_WP_NONCE         => null,
		self::PROTECTED_DATA_CONTROLLER       => null,
		self::PROTECTED_DATA_PROXY_CONTROLLER => null,
		self::PROTECTED_DATA_VIEW             => null,
	);

	private $client_context_data         = array();
	private $client_context_data_touched = false;
	private $server_context_data         = array();
	private $server_context_data_touched = false;
	private $event                       = null;
	private $event_type                  = self::EVENT_TYPE_NONE;
	private $event_source                = null;
	private $init_function               = null;
	protected $registered_events         = array();

	private $required_capabilities = null;
	private $required_login        = null;

	protected $id = null;

	/** @var Controller Current active controller as changed by ReloadController. */
	private $_active_controller = null;

	/** @var Form_View */
	protected $view = null;
	/** @var Model */
	protected $model         = null;
	protected $ajax_response = array();

	/**
	 * Construction.
	 *
	 * @param null $model_class
	 * @param null $view_class
	 * @param null $id
	 */
	public function __construct( $model_class = null, $view_class = null, $id = null ) {
		$this->_active_controller = $this;

		$my_controller_name = get_called_class();
		$this->nonce_protected_data[ self::PROTECTED_DATA_CONTROLLER ]       = $my_controller_name;
		$this->nonce_protected_data[ self::PROTECTED_DATA_PROXY_CONTROLLER ] = $my_controller_name;

		if ( ! isset( $id ) ) {
			/* Replace namespace indicator '\' with '_' to get better names. Will be used in jquery selectors. */
			$id_name  = str_replace( '\\', '_', $my_controller_name );
			$this->id = $id_name;
		} else {
			$this->id = $id;
		}

		if ( $model_class ) {
			$this->model = new $model_class();
		}

		if ( $view_class ) {
			$this->nonce_protected_data[ self::PROTECTED_DATA_VIEW ] = $view_class;
		}

		$this->read_event_request_data();
	}

	/**
	 * Summary.
	 *
	 * @param $event
	 */
	public function set_event( $event ) {
		$this->event = $event;
	}

	/**
	 * Summary.
	 *
	 * @return null
	 */
	public function get_event() {
		return $this->event;
	}

	/**
	 * Summary.
	 *
	 * @return int
	 */
	public function get_event_type() {
		return $this->event_type;
	}

	/**
	 * Summary.
	 *
	 * @return string|null
	 */
	public function get_event_function() {
		if ( isset( $this->event ) ) {
			if ( isset( $this->event_type ) ) {
				if ( self::EVENT_TYPE_NONE !== $this->event_type ) {
					return $this->event . '_' . $this->event_type;
				}
			}
		}

		return null;
	}

	/**
	 * Summary.
	 *
	 * @param $init_function
	 */
	public function set_init_callback( $init_function ) {
		$this->init_function = $init_function;
	}

	/**
	 * Summary.
	 *
	 * @param null $view_class
	 * @param bool $clear_client_context_data
	 */
	public function reload_view( $view_class = null, $clear_client_context_data = false ) {
		if ( ! isset( $view_class ) ) {
			$view_class = $this->nonce_protected_data[ self::PROTECTED_DATA_VIEW ];
		}

		if ( $view_class ) {
			$this->ajax_response = array();
			if ( $clear_client_context_data ) {
				$this->client_context_data         = array();
				$this->client_context_data_touched = false;
			}

			switch ( $this->event_source ) {
				case self::EVENT_METHOD_AJAX:
					$this->view = $this->instantiate_view( $view_class );
					$values     = array();
					$values     = $this->load_model_values( $values );
					$this->init_view( $values );
					$this->view->remove_div_wrapper();
					$html = $this->draw_view();

					if ( ! isset( $html ) ) {
						Debug_Logger::write_debug_error( 'DrawView returned nothing.' );
					}

					$selector = 'div#' . $this->id;
					$this->view->update_client_dom( $selector, 'html', $html );
					break;

				case self::EVENT_METHOD_GET:
				case self::EVENT_METHOD_POST:
					$this->view = $this->instantiate_view( $view_class );
					$values     = array();
					$values     = $this->load_model_values( $values );
					$this->init_view( $values );
					break;

				default:
					Debug_Logger::write_debug_error( 'Unhandled event source ' . $this->event_source );
					break;
			}
		} else {
			Debug_Logger::write_debug_error( 'No view has been set.' );
		}
	}

	/**
	 * @param $controller Controller
	 * @return null
	 */
	public function reload_controller( $controller ) {
		$this->ajax_response               = array();
		$this->client_context_data         = array();
		$this->client_context_data_touched = false;

		if ( isset( $controller ) ) {
			if ( is_string( $controller ) ) {
				$controller = new $controller();
			}
		}

		$this->_active_controller = $controller;
		$controller->nonce_protected_data[ self::PROTECTED_DATA_PROXY_CONTROLLER ] = $this->nonce_protected_data[ self::PROTECTED_DATA_PROXY_CONTROLLER ];

		switch ( $this->event_source ) {
			case self::EVENT_METHOD_AJAX:
				$controller->load_context_data();
				$this->view = $controller->instantiate_view();
				$values     = array();
				$values     = $controller->load_model_values( $values );
				$controller->init_view( $values );
				$html = $controller->draw_view();

				if ( ! isset( $html ) ) {
					Debug_Logger::write_debug_error( 'DrawView returned nothing.' );
				}

				$selector = 'div#' . $this->id;
				$this->view->update_client_dom( $selector, 'replaceWith', $html );
				break;

			case self::EVENT_METHOD_GET:
			case self::EVENT_METHOD_POST:
				$controller->load_context_data();
				$this->view = $controller->instantiate_view();
				$values     = array();
				$values     = $controller->load_model_values( $values );
				$controller->init_view( $values );
				break;

			default:
				Debug_Logger::write_debug_error( 'Unhandled event source ' . $this->event_source );
				$controller = null;
				break;
		}

		return $controller;
	}

    public function create_content() {}

    public function draw_html() {
	    return $this->draw();
    }

	/**
	 * Summary.
	 *
	 * @return string
	 */
	public function draw() {
		if ( $this->check_permissions() ) {
			$this->load_context_data();

			$controller = $this;

			$nonce_protected_data = $this->read_nonce_protected_data( $this->event_source );
			if ( $nonce_protected_data ) {
				if ( $this->check_wp_nonce( $nonce_protected_data ) ) {
					/* Don't flood the debug log due to hacking attempt. Nonce now confirmed and we can write to log. */
					Debug_Logger::continue_wp_debug_logging();

					$my_controller_class = get_called_class();

					/* Check if nonce protected data is sent to this controller */
					if ( $my_controller_class === $nonce_protected_data[ self::PROTECTED_DATA_PROXY_CONTROLLER ] ) {
						/* Yes, it is our data and valid. Handle the events. */

						if ( $my_controller_class !== $nonce_protected_data[ self::PROTECTED_DATA_CONTROLLER ] ) {
							/* The view on client side has been reloaded with another controller. */
							$controller = new $nonce_protected_data[ self::PROTECTED_DATA_CONTROLLER ]();

							$proxy_ctrl = $this->nonce_protected_data[ self::PROTECTED_DATA_PROXY_CONTROLLER ];
							$controller->nonce_protected_data[ static::PROTECTED_DATA_PROXY_CONTROLLER ] = $proxy_ctrl;
						}

						/* The nonce protected data is valid, save it to the controller's instance. */
						$controller->nonce_protected_data = $nonce_protected_data;

						$controller->instantiate_view();
						/* Need to have view instantiated to check if it have events registered */

						$values = $controller->load_values();
						$controller->init_view( $values );

						if ( $controller->check_event_exist( $controller->event, $controller->event_type, $controller->event_source ) ) {
							$controller = $controller->event_handler();
						} else {
							Debug_Logger::write_debug_error( 'Invalid ajax event: Event=' . $this->event . ' EventType=' . $this->event_type . ' EventSource=' . $this->event_source );
						}
					} else {
						/* Nonce protected data not for us, only load view and do not handle events. */
						$controller->instantiate_view();
						/* Can not load values from client, it belongs to some other controllers. Load model value only. */
						$values = $controller->load_model_values( null );
						$controller->init_view( $values );
					}
				} else {
					Debug_Logger::write_debug_note( 'Invalid wpnonce. (Or nonce expired because user logged out and in again.)' );

					$controller->instantiate_view();
					/* Can not load values from client, it belongs to some other controllers. Load model value only. */
					$values = $controller->load_model_values( null );
					$controller->init_view( $values );
				}
			} else {
				$controller->load_context_data();
				$controller->instantiate_view();
				/* Can not load values from client, it belongs to some other controllers. Load model value only. */
				$values = $controller->load_model_values( null );
				$controller->init_view( $values );
			}

			$response = $controller->draw_view();

			if ( ! isset( $response ) ) {
				Debug_Logger::write_debug_error( 'DrawView returned nothing.' );
			}
		} else {
			$response = esc_html__( 'Error: Invalid permission.', 'read-more-login' );
		}

		return $response;
	}

	/**
	 * Summary.
	 *
	 * @return false|mixed|string|void
	 */
	public function ajax_handler() {
		$response = array();

		$this->event_source = self::EVENT_METHOD_AJAX;

		if ( $this->check_permissions() ) {
			$this->load_context_data();

			$nonce_protected_data = $this->read_nonce_protected_data( $this->event_source );
			if ( $nonce_protected_data ) {
				if ( $this->check_wp_nonce( $nonce_protected_data ) ) {
					/* Don't flood the debug log due to hacking attempt. Nonce now confirmed and we can write to log. */
					Debug_Logger::continue_wp_debug_logging();

					$this->nonce_protected_data = $nonce_protected_data;

					$this->instantiate_view();
					/* Need to have view instantiated to check if it have events registered */

					if ( $this->check_event_exist( $this->event, $this->event_type, $this->event_source ) ) {
						$values = $this->load_values();
						$this->init_view( $values );

						$this->event_handler();

						$response['result'] = 'ok';

						$work_items = $this->get_view_response();
						if ( isset( $work_items ) ) {
							if ( ! empty( $work_items ) ) {
								$this->ajax_response = array_merge( $this->ajax_response, $work_items );
							}
						}

						if ( ! is_array( $this->client_context_data ) ) {
							/* Must only send array, or client javascript will not understand. */
							$this->client_context_data = array();
						}

						$response['context_data'] = $this->client_context_data;
						$response['work']         = $this->ajax_response;
					} else {
						$response['result']  = 'error';
						$response['message'] = esc_html__( 'Error: Invalid server request.', 'read-more-login' );
						Debug_Logger::write_debug_error( 'Invalid ajax event: Event=' . $this->event . ' EventType=' . $this->event_type . ' EventSource=' . $this->event_source );
					}
				} else {
					$response['result']  = 'error';
					$response['message'] = esc_html__( 'Error: Session has expired. Please reload page.', 'read-more-login' );
					Debug_Logger::write_debug_note( 'Invalid wpnonce. (Or nonce expired because user logged out and in again.)' );
				}
			} else {
				$response['result']  = 'error';
				$response['message'] = esc_html__( 'Error: Invalid server request.', 'read-more-login' );
				Debug_Logger::write_debug_error( 'Missing nonce.' );
			}
		} else {
			$response['result']  = 'error';
			$response['message'] = esc_html__( 'Error: Invalid permission.', 'read-more-login' ) . $this->invalid_permissions_message();
		}

		$response_json = wp_json_encode( $response );

		return $response_json;
	}

	/**
	 * Summary.
	 *
	 * @param $init_event
	 */
	public function init_handler( $init_event ) {
		if ( $this->check_permissions() ) {
			$this->load_context_data();

			$nonce_protected_data = $this->read_nonce_protected_data( $this->event_source );
			if ( $nonce_protected_data ) {
				if ( $this->check_wp_nonce( $nonce_protected_data ) ) {
					/* Don't flood the debug log due to hacking attempt. Nonce now confirmed and we can write to log. */
					Debug_Logger::continue_wp_debug_logging();

					$this->nonce_protected_data = $nonce_protected_data;

					$this->instantiate_view();

					if ( $this->check_event_exist( $init_event, 'init' ) ) {
						if ( $this->check_event_exist( $this->event, $this->event_type ) ) {
							$values = $this->load_values();
							$this->init_view( $values );

							$init_function  = $init_event . '_init';
							$event_function = $this->event . '_' . $this->event_type;

							$no_response = $this->$init_function( $event_function );

							if ( isset( $no_response ) ) {
								Debug_Logger::write_debug_error( 'Non-expected return from ' . $init_function );
							}
						}
					}

					$this->save_context_data();
				}
			}
		}
	}

	/**
	 * Summary.
	 *
	 * @param $required_login
	 * @param null           $capabilities
	 */
	protected function set_permission( $required_login, $capabilities = null ) {
		$this->required_login        = $required_login;
		$this->required_capabilities = $capabilities;
	}

	/**
	 * Summary.
	 *
	 * @return bool
	 */
	protected function check_permissions() {
		$permission_ok = true;

		if ( isset( $this->required_login ) ) {
			if ( true === $this->required_login ) {
				if ( ! is_user_logged_in() ) {
					Debug_Logger::write_debug_note( 'Invalid permission. Not logged in. (Or could be indication of hacking.)' );
					$permission_ok = false;
				}
			}

			if ( isset( $this->required_capabilities ) ) {
				if ( ! current_user_can( $this->required_capabilities ) ) {
					Debug_Logger::write_debug_note( 'Invalid permission. No capabilities. (Or could be indication of hacking.)' );
					$permission_ok = false;
				}
			}
		} else {
			Debug_Logger::write_debug_error( 'Permission not defined.' );
			$permission_ok = false;
		}

		return $permission_ok;
	}

	/**
	 * Summary.
	 *
	 * @return string
	 */
	protected function invalid_permissions_message() {
		if ( ! isset( $this->required_login ) ) {
			return esc_html__( 'Error. Invalid permission.', 'read-more-login' );
		}

		if ( true === $this->required_login ) {
			return esc_html__( 'Error. You are not logged in.', 'read-more-login' );
		}

		if ( isset( $this->required_capabilities ) ) {
			if ( ! current_user_can( $this->required_capabilities ) ) {
				return esc_html__( 'Error. Access denied.', 'read-more-login' );
			}
		}

		return esc_html__( 'Undefined error.', 'read-more-login' );
	}

	/**
	 * Summary.
	 *
	 * @param array $values
	 *
	 * @return array|mixed
	 */
	protected function load_values( $values = array() ) {
		if ( isset( $_POST['action'] ) && ( Plugin_Container::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER === $_POST['action'] ) ) {
			$values = $this->load_form_values( $values );
		} else {
			$values = $this->load_model_values( $values );
		}

		return $values;
	}


	/**
	 * Summary.
	 *
	 * @param array $values
	 *
	 * @return array|mixed
	 */
	protected function load_model_values( $values = array() ) {
		if ( isset( $this->model ) ) {
			if ( $this->model->load_data() === 1 ) {
				$values = $this->model->get_data_record();
			}
		}

		return $values;
	}

	/**
	 * Summary.
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	protected function load_form_values( $values = array() ) {
		if ( isset( $this->view ) ) {
			$values = $this->view->read_client_side_values( $values );
		}
		return $values;
	}

	/**
	 * Summary.
	 *
	 * @param null $view_class
	 *
	 * @return Form_View|null
	 */
	protected function instantiate_view( $view_class = null ) {
		$view = null;

		if ( $view_class ) {
			$this->nonce_protected_data[ self::PROTECTED_DATA_VIEW ] = $view_class;
		}

		if ( $this->nonce_protected_data[ self::PROTECTED_DATA_VIEW ] ) {
			$my_controller_name = get_called_class();

			$this->view = new $this->nonce_protected_data[ self::PROTECTED_DATA_VIEW ]($this->id, $my_controller_name);

			$view = $this->view;
		}

		return $view;
	}

	/**
	 * Summary.
	 *
	 * @param $values
	 */
	protected function init_view( $values ) {
		if ( isset( $this->init_function ) ) {
			$this->view->add_hidden_fields( '_init_callback', $this->init_function );
		}
	}

	/**
	 * Summary.
	 *
	 * @return mixed
	 */
	protected function create_wp_nonce() {
		$this->nonce_protected_data = $this->calculate_wp_nonce( $this->nonce_protected_data );
		return $this->nonce_protected_data[ self::PROTECTED_DATA_WP_NONCE ];
	}

	/**
	 * Summary.
	 *
	 * @param array $nonce_protected_data
	 *
	 * @return array|null
	 */
	protected function calculate_wp_nonce( $nonce_protected_data = array() ) {
		$nonce_string = $this->format_wp_nonce_string( $nonce_protected_data );

		if ( $nonce_string ) {
			$wp_nonce = wp_create_nonce( $nonce_string );
			$nonce_protected_data[ self::PROTECTED_DATA_WP_NONCE ] = $wp_nonce;
			return $nonce_protected_data;
		} else {
			Debug_Logger::write_debug_error( 'Failed to create nonce string.' );
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $nonce_protected_data
	 *
	 * @return bool
	 */
	protected function check_wp_nonce( $nonce_protected_data ) {
		$nonce_string = $this->format_wp_nonce_string( $nonce_protected_data );

		if ( $nonce_string ) {
			$calculated_wpnonce = wp_create_nonce( $nonce_string );

			if ( $calculated_wpnonce === $nonce_protected_data[ self::PROTECTED_DATA_WP_NONCE ] ) {
				return true;
			}
		} else {
			Debug_Logger::write_debug_error( 'Failed to create nonce string.' );
		}

		return false;
	}

	/**
	 * Summary.
	 *
	 * @param $nonce_protected_data
	 *
	 * @return string
	 */
	protected function format_wp_nonce_string( $nonce_protected_data ) {
		$nonce_string = '';
		foreach ( $nonce_protected_data as $key => $value ) {
			if ( self::PROTECTED_DATA_WP_NONCE !== $key ) {
				$nonce_input_type = gettype( $value );

				if ( $nonce_string ) {
					$nonce_string .= '_';
				}

				switch ( $nonce_input_type ) {
					case 'string':
						$nonce_string .= $value;
						break;

					case 'integer':
						$nonce_string .= strval( $value );
						break;

					default:
						Debug_Logger::write_debug_error( 'Undefined type ' . $nonce_input_type . ' for nonce protected data ' . $key );
						break;
				}
			}
		}

		return $nonce_string;
	}

	/**
	 * Summary.
	 *
	 * @param $event_source
	 *
	 * @return array|null
	 */
	protected function read_nonce_protected_data( $event_source ) {
		$nonce_protected_data = array();

		switch ( $event_source ) {
			case 'ajax':
			case 'post':
				$nonce_protected_data[ self::PROTECTED_DATA_WP_NONCE ] = Security_Filter::safe_read_post_request( self::PROTECTED_DATA_WP_NONCE, Security_Filter::ALPHA_NUM );

				if ( isset( $nonce_protected_data[ self::PROTECTED_DATA_WP_NONCE ] ) ) {
					$controller_class = Security_Filter::safe_read_post_request( self::PROTECTED_DATA_CONTROLLER, Security_Filter::CLASS_NAME );
					/* jQuery ajax post adds double slashes */
					$controller_class                                        = stripslashes( $controller_class );
					$nonce_protected_data[ self::PROTECTED_DATA_CONTROLLER ] = $controller_class;

					$controller_class = Security_Filter::safe_read_post_request( self::PROTECTED_DATA_PROXY_CONTROLLER, Security_Filter::CLASS_NAME );
					/* jQuery ajax post adds double slashes */
					$controller_class = stripslashes( $controller_class );
					$nonce_protected_data[ self::PROTECTED_DATA_PROXY_CONTROLLER ] = $controller_class;

					$view_class = Security_Filter::safe_read_post_request( self::PROTECTED_DATA_VIEW, Security_Filter::CLASS_NAME );
					/* jQuery ajax post adds double slashes */
					$view_class                                        = stripslashes( $view_class );
					$nonce_protected_data[ self::PROTECTED_DATA_VIEW ] = $view_class;
				}
				break;

			case 'get':
				$nonce_protected_data[ self::PROTECTED_DATA_WP_NONCE ] = Security_Filter::safe_read_get_request( self::PROTECTED_DATA_WP_NONCE, Security_Filter::ALPHA_NUM );

				if ( isset( $nonce_protected_data[ self::PROTECTED_DATA_WP_NONCE ] ) ) {
					$controller_class = Security_Filter::safe_read_get_request( self::PROTECTED_DATA_CONTROLLER, Security_Filter::CLASS_NAME );
					/* jQuery ajax post adds double slashes */
					$controller_class                                        = stripslashes( $controller_class );
					$nonce_protected_data[ self::PROTECTED_DATA_CONTROLLER ] = $controller_class;

					$controller_class = Security_Filter::safe_read_get_request( self::PROTECTED_DATA_PROXY_CONTROLLER, Security_Filter::CLASS_NAME );
					/* jQuery ajax post adds double slashes */
					$controller_class = stripslashes( $controller_class );
					$nonce_protected_data[ self::PROTECTED_DATA_PROXY_CONTROLLER ] = $controller_class;

					$view_class = Security_Filter::safe_read_get_request( self::PROTECTED_DATA_VIEW, Security_Filter::CLASS_NAME );
					/* jQuery ajax post adds double slashes */
					$view_class                                        = stripslashes( $view_class );
					$nonce_protected_data[ self::PROTECTED_DATA_VIEW ] = $view_class;
				}
				break;

			default:
				break;
		}

		if ( isset( $nonce_protected_data[ self::PROTECTED_DATA_WP_NONCE ] ) ) {
			return $nonce_protected_data;
		} else {
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @return array
	 */
	protected function get_nonce_protected_data() {
		return $this->nonce_protected_data;
	}

	/**
	 * Summary.
	 *
	 * @return mixed
	 */
	protected function get_view_class() {
		return $this->nonce_protected_data[ self::PROTECTED_DATA_VIEW ];
	}

	/**
	 * Summary.
	 *
	 * @param $event
	 * @param $event_type
	 * @param $event_source
	 */
	protected function register_event( $event, $event_type, $event_source ) {
		$event_data                        = array();
		$event_data['Type']                = $event_type;
		$event_data['Source']              = $event_source;
		$this->registered_events[ $event ] = $event_data;
	}

	/**
	 * Summary.
	 *
	 * @param $event
	 * @param $event_type
	 * @param null       $event_source
	 *
	 * @return bool
	 */
	protected function check_event_exist( $event, $event_type, $event_source = null ) {
		$event_exist = false;

		if ( isset( $event ) && isset( $event_type ) ) {
			$correct_event_types = array( 'click', 'init', 'post', 'get', 'callback' );
			if ( in_array( $event_type, $correct_event_types ) ) {
				foreach ( $this->registered_events as $key => $event_data ) {
					if ( $event === $key ) {
						if ( ( '*' === $event_type ) || ( $event_data['Type'] === $event_type ) ) {
							if ( ( ! isset( $event_source ) ) || ( '*' === $event_source ) || ( $event_data['Source'] === $event_source ) ) {
								$event_exist = true;
								break;
							}
						}
					}
				}

				if ( ! $event_exist ) {
					/* If controller has no such event, check views */
					if ( isset( $this->view ) ) {
						$event_exist = $this->view->check_event_exist( $event, $event_type, $event_source );
					}
				}
			} else {
				Debug_Logger::write_debug_error( 'Undefined event type ' . $event_type . ' for event=' . $event );
			}

			if ( $event_exist ) {
				$event_function = $event . '_' . $event_type;

				if ( ! method_exists( $this, $event_function ) ) {
					$event_exist = false;
					Debug_Logger::write_debug_error( 'Missing event function ' . $event_function );
				}
			} else {
				Debug_Logger::write_debug_error( 'Undefined event event=' . $event . ' type=' . $event_type );
			}
		}

		return $event_exist;
	}

	/**
	 * Summary.
	 */
	protected function enqueue_script() {
		$unique_prefix = Plugin_Container::get_prefixed_plugin_slug();

		$script_handler = $unique_prefix . '_script_handler';
		$src            = plugin_dir_url( __FILE__ ) . '../../../js/view-controller.js';
		wp_enqueue_script( $script_handler, $src, array( 'jquery' ), Plugin_Container::get_plugin_version(), true );

		$ajax_handler   = '/wp-admin/admin-ajax.php';
		$url_to_my_site = site_url() . $ajax_handler;

		$data_array = array(
			'url_to_my_site'      => $url_to_my_site,
			'form_input_selector' => 'input, textarea, select',
			'wp_ajax_function'    => Plugin_Container::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER,
			'context_data'        => $this->client_context_data,
		);
		wp_localize_script( $script_handler, 'wp_plugin_framework_script_vars', $data_array );

		$style_handler1 = $unique_prefix . '_style_handler';
		$style_url      = plugin_dir_url( __FILE__ ) . '../../../css/style.css';
		$style_version  = Plugin_Container::get_plugin_version();
		wp_enqueue_style( $style_handler1, $style_url, array(), $style_version );

		if ( is_rtl() ) {
			$style_rtl_handler = $unique_prefix . '_style_rtl_handler';
			$style_rtl_url     = plugin_dir_url( __FILE__ ) . '../../../css/style-rtl.css';
			$style_version     = Plugin_Container::WP_PLUGIN_MVC_FRAMEWORK_VERSION;
			wp_enqueue_style( $style_rtl_handler, $style_rtl_url, array(), $style_version );
		} else {
			$style_ltr_handler = $unique_prefix . '_style_ltr_handler';
			$style_ltr_url     = plugin_dir_url( __FILE__ ) . '../../../css/style-ltr.css';
			$style_version     = Plugin_Container::WP_PLUGIN_MVC_FRAMEWORK_VERSION;
			wp_enqueue_style( $style_ltr_handler, $style_ltr_url, array(), $style_version );
		}
	}

	/**
	 * Summary.
	 *
	 * @param null $parameters
	 *
	 * @return string
	 */
	protected function draw_view( $parameters = null ) {
		$this->enqueue_script();

		$this->create_wp_nonce();
		foreach ( $this->nonce_protected_data as $name => $protected_data ) {
			$this->view->add_hidden_fields( $name, $protected_data );
		}

		return $this->view->draw_view( $parameters );
	}

	/**
	 * Summary.
	 *
	 * @return array
	 */
	protected function get_view_response() {
		return $this->view->get_ajax_response();
	}

	/**
	 * Summary.
	 *
	 * ClientContextData is a buffer keeping data between user interactions.
	 * Note! ClientContextData will be sent to visitor via AJAX calls.
	 * Do not store secret data like password in this buffer!
	 *
	 * @param $key
	 * @param $value
	 */
	protected function set_client_context_data( $key, $value ) {
		$this->client_context_data[ $key ] = $value;
		$this->client_context_data_touched = true;
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	protected function get_client_context_data( $key ) {
		if ( isset( $this->client_context_data[ $key ] ) ) {
			return $this->client_context_data[ $key ];
		} else {
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 * @param $value
	 */
	protected function set_server_context_data( $key, $value ) {
		$this->server_context_data[ $key ] = $value;
		$this->server_context_data_touched = true;
	}

	/**
	 * Summary.
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	protected function get_server_context_data( $key ) {
		if ( isset( $this->server_context_data[ $key ] ) ) {
			return $this->server_context_data[ $key ];
		} else {
			return null;
		}
	}

	/**
	 * Summary.
	 */
	protected function save_context_data() {
		if ( $this->client_context_data_touched ) {
			/* If we have touched the context data, must save otherwise it will be forgotten. */
			$key             = Plugin_Container::get_prefixed_plugin_slug() . '_client_context_data';
			$GLOBALS[ $key ] = $this->client_context_data;
		}

		if ( $this->server_context_data_touched ) {
			/* If we have touched the context data, must save otherwise it will be forgotten. */
			$key             = Plugin_Container::get_prefixed_plugin_slug() . '_server_context_data';
			$GLOBALS[ $key ] = $this->server_context_data;
		}
	}

	/**
	 * Summary.
	 */
	protected function load_context_data() {
		$key = Plugin_Container::get_prefixed_plugin_slug() . '_server_context_data';
		if ( isset( $GLOBALS[ $key ] ) ) {
			$this->server_context_data = $GLOBALS[ $key ];
		}

		$key = Plugin_Container::get_prefixed_plugin_slug() . '_client_context_data';
		if ( isset( $GLOBALS[ $key ] ) ) {
			$this->client_context_data = $GLOBALS[ $key ];
		} else {
			if ( isset( $_POST['_context_data'] ) ) {
				if ( is_array( $_POST['_context_data'] ) ) {
					/* Only array is acceptable from client */
					$this->client_context_data = $_POST['_context_data'];
				}
			}
		}
	}

	/**
	 * Summary.
	 *
	 * @return Controller
	 */
	protected function event_handler() {
		if ( ( isset( $this->event ) ) && ( isset( $this->event_type ) ) ) {
			$event_function = $this->get_event_function();

			if ( method_exists( $this, $event_function ) ) {
				switch ( $this->event_type ) {
					case self::EVENT_TYPE_CLICK:
						$no_response = $this->$event_function();
						break;

					case self::EVENT_TYPE_CALLBACK:
						$arguments = null;
						if ( isset( $_POST['_arguments'] ) ) {
							$arguments = $_POST['_arguments'];
							if ( ! is_array( $arguments ) ) {
								/* No arguments is converted to empty string. Convert back to empty array. */
								$arguments = array();
							}
						}

						$no_response = call_user_func_array( array( $this, $event_function ), $arguments );
						break;

					case self::EVENT_TYPE_POST:
						$no_response = $this->$event_function( $_POST );
						break;

					case self::EVENT_TYPE_GET:
						$no_response = $this->$event_function( $_GET );
						break;

					default:
						Debug_Logger::write_debug_error( 'Undefined event type ' . $this->event_type . ' for event=' . $event_function );
						break;
				}
			} else {
				Debug_Logger::write_debug_error( 'Event function ' . $event_function . ' missing in ' . get_called_class() . '.' );
			}

			if ( isset( $no_response ) ) {
				Debug_Logger::write_debug_error( 'Event ' . $event_function . ' function return non-expected data.' );
			}
		}

		return $this->_active_controller;
	}

	/**
	 * Summary.
	 */
	protected function read_event_request_data() {
		/* By default assume method is get as normal http request. */
		$this->event_type = self::EVENT_TYPE_GET;

		$this->event = Security_Filter::safe_read_post_request( '_event', Security_Filter::STRING_KEY_NAME );
		if ( isset( $this->event ) ) {
			/* Event found in post method. */
			$this->event_type = Security_Filter::safe_read_post_request( '_event_type', Security_Filter::STRING_KEY_NAME );
			if ( ! isset( $this->event_type ) ) {
				/* If no event type found, then is must be a post event type. */
				$this->event_type = self::EVENT_TYPE_POST;
			}

			$this->event_source = self::EVENT_METHOD_POST;
		} else {
			/* Or it is a get event. */
			$this->event = Security_Filter::safe_read_get_request( '_event', Security_Filter::STRING_KEY_NAME );
			if ( isset( $this->event ) ) {
				$this->event_type = Security_Filter::safe_read_get_request( '_event_type', Security_Filter::STRING_KEY_NAME );
				if ( ! isset( $this->event_type ) ) {
					/* If no event type found, then is must be a get event type. */
					$this->event_type = self::EVENT_TYPE_GET;
				}
			}

			$this->event_source = self::EVENT_TYPE_GET;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $event
	 * @param null  $arguments
	 */
	public function register_callback( $event, $arguments = null ) {
		$controller_class = get_called_class();
		$view_class       = $this->nonce_protected_data[ self::PROTECTED_DATA_VIEW ];
		$wp_nonce         = $this->create_wp_nonce();

		$this->view->update_client_add_callback( $event, $arguments, $controller_class, $view_class, $wp_nonce );
	}
}
