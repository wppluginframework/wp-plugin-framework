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
class Std_Controller extends Form_Controller {

	/**
	 * Summary.
	 */
	public function std_submit_click() {
		$data_record = $this->view->get_values();

		if ( $this->model->validate_data_record( $data_record ) ) {
			$this->handle_validation_success( $data_record );
		} else {
			$this->handle_validation_errors( $data_record );
		}
	}

	/**
	 * Summary.
	 *
	 * @param $data_record
	 */
	public function handle_validation_success( $data_record ) {
		if ( $this->model->set_data_record( $data_record ) ) {
			$this->view->hide_input_error_indications();

			if ( $this->handle_save( $data_record ) ) {
				$this->handle_save_success( $data_record );
			} else {
				$this->handle_save_errors( $data_record );
			}
		}
	}

	/**
	 * Summary.
	 *
	 * @param $data_record
	 */
	public function handle_validation_errors( $data_record ) {
		$errors = $this->model->get_validate_errors();

		if ( $errors ) {
			$required_errors     = array();
			$invalid_errors      = array();
			$other_error_message = array();

			$ok_components = $this->view->get_form_input_component();

			foreach ( $errors as $key => $error ) {
				$component = $this->view->get_form_input_component( $key );
				if ( $component ) {
					$component->add_input_class( 'wpf-input-error' );

					$i = array_search( $component, $ok_components );
					if ( false !== $i ) {
						unset( $ok_components[ $i ] );
					}

					if ( is_array( $error ) ) {
						/* Ony pop one first error message. Other will not be displayed. */
						$error = array_pop( $error );
					}

					if ( is_string( $error ) ) {
						$other_error_message[] = $error;
					} elseif ( is_integer( $error ) ) {
						$label = $component->get_property( 'label' );
						switch ( $error ) {
							case Model::VALIDATION_ERROR_REQUIRED_FIELD:
								$required_errors[] = $label;
								break;

							case Model::VALIDATION_ERROR_INVALID:
								$invalid_errors[] = $label;
								break;
						}
					}
				}
			}

			$message = '';

			if ( ! empty( $required_errors ) ) {
				$labels = implode( ', ', $required_errors );
				/* translators: %s: Lists missing input fields. */
				$message .= esc_html( sprintf('Error. Required field missing: %s.', $labels ) );
			}

			if ( ! empty( $invalid_errors ) ) {
				if ( $message ) {
					$message .= ' ';
				}
				$labels = implode( ', ', $invalid_errors );
				/* translators: %s: Lists entered input fields having errors. */
				$message = esc_html( sprintf( 'Error. Invalid data: %s.', $labels ) );
			}

			if ( ! empty( $other_error_message ) ) {
				if ( $message ) {
					$message .= ' ';
				}
				$other_error_message = implode( ' ', $other_error_message );
				$message            .= $other_error_message;
			}

			if ( ! $message ) {
				$message = 'Error. Invalid input data.';
			}

			$this->view->std_status_bar->set_status_text( $message, Status_Bar::STATUS_ERROR );

			foreach ( $ok_components as $component ) {
				$component->hide_input_error_indication();
			}
		} else {
			$this->view->std_status_bar->set_status_text( 'Error. Invalid data.', Status_Bar::STATUS_ERROR );
		}
	}

	/**
	 * Summary.
	 *
	 * @param $data_record
	 *
	 * @return
	 */
	public function handle_save( $data_record ) {
		return $this->model->save_data();
	}

	/**
	 * Summary.
	 *
	 * @param $data_record
	 */
	public function handle_save_success( $data_record ) {
		$this->view->std_status_bar->set_status_text( 'Your settings have been saved.', Status_Bar::STATUS_SUCCESS );
	}

	/**
	 * Summary.
	 *
	 * @param $data_record
	 */
	public function handle_save_errors( $data_record ) {
		$this->view->std_status_bar->set_status_text( 'Error saving data.', Status_Bar::STATUS_ERROR );
	}
}
