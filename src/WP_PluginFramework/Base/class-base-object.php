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

namespace WP_PluginFramework\Base;

use WP_PluginFramework\Utils\Debug_Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Summary.
 *
 * Description.
 */
class Base_Object {
	/**
	 * Construction.
	 *
	 * @param array $properties Properties to be stored in object.
	 */
	public function __construct( $properties = null ) {
		if ( isset( $properties ) ) {
			$this->set_properties( $properties );
		}
	}

	/**
	 * Summary.
	 *
	 * @param array $properties Properties to be stored in object.
	 */
	public function set_properties( $properties ) {
	    if( $properties === null ) {
	        Debug_Logger::write_debug_error('Empty properties.');
        }
		foreach ( $properties as $key => $value ) {
			$this->$key = $value;
		}

		return;
	}

    /**
	 * Summary.
	 *
	 * @return array All properties returned.
	 */
	public function get_properties() {
		$properties = get_object_vars( $this );
		return $properties;
	}

	/**
	 * Summary.
	 *
	 * @param $property
	 * @param $value
	 */
	public function set_property( $property, $value ) {
		if(property_exists($this, $property)) {
			$this->$property = $value;
			return true;
		} else {
			Debug_Logger::write_debug_error('Properties does not exist.', $properry, $value);
			return false;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $property
	 * @param $values
	 *
	 * @return bool|null
	 */
	public function add_property( $property, $values ) {
		if ( isset( $this->$property ) ) {
			if ( is_array( $this->$property ) ) {
				if ( empty( $this->$property ) ) {
					if ( is_array( $values ) ) {
						$this->$property = $values;
					} else {
						array_push( $this->$property, $values );
					}
				} else {
					if ( is_array( $values ) ) {
						$this->$property = array_merge( $this->$property, $values );
					} else {
						array_push( $this->$property, $values );
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $property
	 *
	 * @return |null
	 */
	public function get_property( $property ) {
		if ( isset( $this->$property ) ) {
			return $this->$property;
		} else {
			return null;
		}
	}

	public function set_property_key_value( $property, $key, $value) {
		if ( isset( $this->$property ) ) {
			if ( is_array( $this->$property ) ) {
				if(array_key_exists($key, $this->$property)) {
					$this->$property[ $key ] = $value;
				}else {
					/* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
					$value_arr = array($key => $value);
					$this->$property = array_merge ($this->$property, $value_arr);
				}
			}
		}
	}

	/**
	 * Add values to an property array given with key.
	 *
	 * $this->$property[$key] = {array of values}
	 *
	 * @param $property
	 * @param $key          string
	 * @param $values       array|string
	 *
	 * @return bool|null    Return null if property does not exist. True on success. False on failure.
	 */
	protected function set_property_key_values( $property, $key, $values ) {
		if ( isset( $this->$property ) ) {
			if ( is_array( $this->$property ) ) {
				if ( array_key_exists( $key, $this->$property ) ) {
					if ( is_array( $this->$property ) ) {
						if ( is_array( $values ) ) {
							/* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
							$tmp             = $this->$property;
							$tmp[ $key ]     = array_merge( $this->$property[ $key ], $values );
							$this->$property = $tmp;

						} else {
							/* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
							$tmp             = $this->$property;
							$value_arr       = array( $values );
							$tmp[ $key ]     = array_merge( $tmp[ $key ], $value_arr );
							$this->$property = $tmp;
						}
					} else {
						return false;
					}
				} else {
					if ( is_array( $values ) ) {
						$this->$property[ $key ] = $values;
					} else {
						/* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
						$tmp             = $this->$property;
						$tmp[ $key ]     = array( $values );
						$this->$property = $tmp;
					}
				}
			}
			return true;
		} else {
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $property
	 * @param $key
	 * @param $values
	 *
	 * @return bool|null
	 */
	protected function remove_property_key_values( $property, $key, $values ) {
		if ( isset( $this->$property ) ) {
			if ( is_array( $this->$property ) ) {
				if ( array_key_exists( $key, $this->$property ) ) {
					if ( is_array( $values ) ) {
						/* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
						$tmp             = $this->$property;
						$tmp[ $key ]     = array_diff( $this->$property[ $key ], $values );
						$this->$property = $tmp;
					} else {
						/* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
						$tmp             = $this->$property;
						$value_arr       = array( $values );
						$tmp[ $key ]     = array_diff( $tmp[ $key ], $value_arr );
						$this->$property = $tmp;
					}
					return true;
				} else {
					return false;
				}
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	/**
	 * Summary.
	 *
	 * @param $property
	 * @param $key
	 *
	 * @return |null
	 */
	protected function get_property_key_values( $property, $key ) {
		if ( isset( $this->$property ) ) {
			if ( isset( $this->$property[ $key ] ) ) {
				return $this->$property[ $key ];
			}
		}
		return null;
	}
}
