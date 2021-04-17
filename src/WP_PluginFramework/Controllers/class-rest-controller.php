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

use BCQ_BitcoinBank\Accounting;
use BCQ_BitcoinBank\Transactions_Db_Table;
use WP_PluginFramework\Utils\Debug_Logger;
use WP_PluginFramework\Utils\Query_Parameters;

defined( 'ABSPATH' ) || exit;


class Rest_Controller
{

    CONST ENDPOINT_ID_KEY = 'id';

    CONST METHOD_GET_ONE = 'METHOD_GET_ONE';
    CONST METHOD_GET_MANY = 'METHOD_GET_MANY';
    CONST METHOD_GET_ID = 'METHOD_GET_ID';
    CONST METHOD_POST = 'METHOD_POST';

    CONST PARAMETER_KEY_OFFSET = 'offset';
    CONST PARAMETER_KEY_LIMIT = 'limit';
    CONST PARAMETER_KEY_FIELD = 'field';
    CONST PARAMETER_KEY_FILTER = 'filter';
    CONST PARAMETER_KEY_SEARCH = 'search';
    CONST PARAMETER_KEY_ORDER = 'order';

    protected $name_space = null;

    protected $model_class_name = null;
    private $model = null;

    protected $last_error_code = null;
    protected $last_error_message = null;
    protected $invalid_request_parameters = array();
    protected $invalid_db_parameters = array();
    protected $last_http_status = null;

    private $authenticated_wp_user_id = false;

    public function __construct( $name_space, $model_class_name=null ) {
        $this->name_space = $name_space;
        $this->model_class_name = $model_class_name;
    }

    public function register_routes($route = null, $methods=array())
    {
        foreach($methods as $method => $args) {
            switch ($method) {
                case self::METHOD_GET_ONE:
                    register_rest_route( $this->name_space, $route, array(
                        array(
                            'methods'  => \WP_REST_Server::READABLE,
                            'callback' => array($this, 'endpoint_get_one'),
                            'permission_callback' => array($this, 'endpoint_get_has_permission')
                        ),
                        'schema' => array($this, 'get_schema')
                    ));
                    break;

                case self::METHOD_GET_MANY:
                    register_rest_route( $this->name_space, $route, array(
                        array(
                            'methods'  => \WP_REST_Server::READABLE,
                            'callback' => array($this, 'endpoint_get_many'),
                            'permission_callback' => array($this, 'endpoint_get_has_permission')
                        ),
                        'schema' => array($this, 'get_schema')
                    ));
                    break;

                case self::METHOD_GET_ID:
                    register_rest_route( $this->name_space, $route . '/(?P<' . self::ENDPOINT_ID_KEY. '>\d+)', array(
                        array(
                            'methods'  => \WP_REST_Server::READABLE,
                            'callback' => array($this, 'endpoint_get_single'),
                            'args' => array(
                                'id' => array(
                                    'validate_callback' => array($this, 'validate_id_arguments'),
                                    'sanitize_callback' => array($this, 'sanitize_id_arguments')
                                ),
                            ),
                            'permission_callback' => array($this, 'endpoint_get_single_has_permission')
                        ),
                        'schema' => array($this, 'endpoint_get_single_schema'),
                    ));
                    break;

                case self::METHOD_POST:
                    register_rest_route( $this->name_space, $route, array(
                        'methods'  => \WP_REST_Server::CREATABLE,
                        'callback' => array($this, 'endpoint_post'),
                        'permission_callback' => array($this, 'endpoint_post_has_permission'),
                        'schema' => array($this, 'get_schema'),
                    ));
                    break;

                default:
                    Debug_Logger::write_debug_error('Unsupported REST method', $method);
                    break;
            }
        }
    }

    public function update_id_parameter($request, $query_parameters) {
        return self::ENDPOINT_ID_KEY;
    }

    public function validate_id_arguments($param, $request, $key) {
        if (is_numeric( $param )) {
            $id = intval($param);
            if($id >= 0) {
                return true;
            }
        };
        return false;
    }

    public function sanitize_id_arguments($param, $request, $key) {
        return intval($param);
    }

    public function endpoint_get_has_permission($request) {
        return $this->check_basic_authentication_wp_user($request);
    }

    public function endpoint_get_single_has_permission($request) {
        return $this->check_basic_authentication_wp_user($request);
    }

    public function endpoint_post_has_permission($request) {
        return $this->check_basic_authentication_wp_user($request);
    }

    protected function check_basic_authentication_wp_user($request) {
        $authorised = false;

        $user_pass = $this->read_basic_authenticate_header($request);
        if( $user_pass !== false ) {
            $wp_user = get_user_by('login', $user_pass['username']);
            if($wp_user !== false)
            {
                $wp_user_id = $wp_user->ID;
                if( wp_check_password( $user_pass['password'], $wp_user->data->user_pass, $wp_user_id )) {
                    $this->authenticated_wp_user_id = $wp_user_id;
                    $authorised = true;
                } else {
                    $this->last_error_message = 'Wrong password';
                }
            } else {
                $this->last_error_message = 'Username does not exist.';
            }

            if($authorised === false) {
                $this->last_error_code = 'authentication_error';
                $this->last_http_status = 401;
            }
        }

        return $authorised;

    }

    protected function get_authenticated_wp_user_id() {
        return $this->authenticated_wp_user_id;
    }

    protected function read_basic_authenticate_header($request) {
        $ok = true;

        $authorization = $request->get_header('Authorization');
        if (!$authorization) {
            $this->last_error_message = 'Authorization header missing.';
            $ok = false;
        }

        if ($ok) {
            $authorization = explode(' ', $authorization);
            if (!$authorization !== false) {
                $this->last_error_message = 'Error in Authorization header data.';
                $ok = false;
            }
        }

        if ($ok) {
            if (!is_array($authorization)) {
                $this->last_error_message = 'Error in Authorization header data.';
                $ok = false;
            }
        }

        if ($ok) {
            if (!count($authorization) == 2) {
                $this->last_error_message = 'Error in Authorization header data format.';
                $ok = false;
            }
        }

        if ($ok) {
            if ($authorization[0] !== 'Basic') {
                $this->last_error_message = 'Authorization type not supported.';
                $ok = false;
            }
        }

        if ($ok) {
            $user_pass = base64_decode($authorization[1], true);
            if ($user_pass === false) {
                $this->last_error_message = 'Authorization base64 error.';
                $ok = false;
            }
        }

        if ($ok) {
            $user_pass = explode(':', $user_pass);
            if ($user_pass === false) {
                $this->last_error_message = 'Authorization username:password format error.';
                $ok = false;
            }
        }


        if ($ok) {
            if (!is_array($user_pass)) {
                $this->last_error_message = 'Authorization username:password format error.';
                $ok = false;
            }
        }


        if ($ok) {
            if (count($user_pass) !== 2) {
                $this->last_error_message = 'Authorization username:password count error.';
                $ok = false;
            }
        }

        if ($ok) {
            $username_password = array(
                'username' => $user_pass[0],
                'password' => $user_pass[1]
            );
        } else {
            $username_password = false;
            $this->last_error_code = 'authentication_error';
            $this->last_http_status = 401;
        }

        return $username_password;
    }

    public function get_valid_parameter_key_list(){
        return array(
            self::PARAMETER_KEY_OFFSET,
            self::PARAMETER_KEY_LIMIT,
            self::PARAMETER_KEY_FIELD,
            self::PARAMETER_KEY_FILTER,
            self::PARAMETER_KEY_SEARCH,
            self::PARAMETER_KEY_ORDER
        );
    }

    public function get_request_parameters($request) {
        $parameters = new Query_Parameters();

        $offset = $request->get_param(self::PARAMETER_KEY_OFFSET);
        if($offset) {
            $parameters->set_offset($offset);
        }

        $limit = $request->get_param(self::PARAMETER_KEY_LIMIT);
        if($limit) {
            $parameters->set_limit(intval($limit));
        }

        $filters = $request->get_param(self::PARAMETER_KEY_FILTER);
        if($filters) {
            $filters = json_decode($filters, true);
            $parameters->set_filters($filters);
        }

        $fields = $request->get_param(self::PARAMETER_KEY_FIELD);
        if($fields) {
            $fields = explode(',', $fields);
            $parameters->add_fields($fields);
        }

        $order = $request->get_param(self::PARAMETER_KEY_ORDER);
        if($order) {
            $order = json_decode($order, true);
            $parameters->set_orders($order);
        }

        $search = $request->get_param(self::PARAMETER_KEY_SEARCH);
        if($search) {
            $search_json = json_decode($search, true);
            if(is_array($search_json)) {
                $parameters->set_search($search_json);
            } else {
                $search_json_exploded = explode(':', $search);
                if(is_array($search_json_exploded)) {
                    if(count($search_json_exploded) === 2) {
                        $key = trim($search_json_exploded[0]);
                        $value = trim($search_json_exploded[1]);
                        if($key and $value) {
                            $search = array ($key => $value);
                            $parameters->set_search($search);
                        }
                    }
                }
            }
        }

        /* TODO check for invalid parameters */
        //$keys = $this->get_valid_parameter_key_list();
        //$keys = array_flip($keys);
        //$this->invalid_request_parameters = $parameters->filter_data($keys);
        return $parameters;
    }

    public function convert_rest_to_db_request_parameters($query_parameters) {
        $query_db_parameters = clone $query_parameters;
        $mapping_table = $this->get_rest_database_mapping();
        $query_db_parameters->remap_keys($mapping_table);

        /* Wash out non-existing database fields */
        $model = $this->get_model();
        $meta_data_list = $model->get_meta_data_list();
        $this->invalid_db_parameters = $query_db_parameters->filter_data($meta_data_list);
        return $query_db_parameters;
    }

    public function endpoint_get_one_read_data ($request, $query_parameters) {
        $model = $this->get_model();
        $model->load_data_query_parameters($query_parameters);
        $fields = $query_parameters->get_fields();
        $record = $model->get_record($fields);
        return $record;
    }

    public function endpoint_get_one ($request) {
        $query_parameters = $this->get_request_parameters($request);
        $model = $this->get_model();
        if($model) {
            $query_db_parameters = $this->convert_rest_to_db_request_parameters($query_parameters);
        } else {
            $query_db_parameters = $query_parameters;
        }

        $data_record = $this->endpoint_get_one_read_data ($request, $query_db_parameters);

        if ( empty( $data_record ) ) {
            return $this->send_success_response( null, $query_db_parameters  );
        }

        if($model) {
            $data_record = $this->convert_db_to_rest_record($data_record);
        }
        $schema = $this->get_schema();
        $schema = $this->get_schema_filter_fields($schema, $query_parameters->get_fields());
        $response = $this->filter_schema_data($data_record, $schema);
        $data = $this->prepare_for_collection( $response );

        return $this->send_success_response($data, $query_db_parameters);
    }

    public function endpoint_get_many_read_data ($request, $query_parameters) {
        $model = $this->get_model();
        $model->load_data_query_parameters($query_parameters);
        $fields = $query_parameters->get_fields();
        $records = $model->get_copy_all_data($fields);
        return $records;
    }

    public function endpoint_get_many ($request) {
        $query_parameters = $this->get_request_parameters($request);
        $model = $this->get_model();
        if($model) {
            $query_db_parameters = $this->convert_rest_to_db_request_parameters($query_parameters);
        } else {
            $query_db_parameters = $query_parameters;
        }

        $data_records = $this->endpoint_get_many_read_data ($request, $query_db_parameters);

        $data = array();

        if ( empty( $data_records ) ) {
            return $this->send_success_response( $data, $query_db_parameters  );
        }

        foreach ( $data_records as $data_record ) {
            if($model) {
                $data_record = $this->convert_db_to_rest_record($data_record);
            }
            $schema = $this->get_schema();
            $schema = $this->get_schema_filter_fields($schema, $query_parameters->get_fields());
            $response = $this->filter_schema_data($data_record, $schema);
            $data[] = $this->prepare_for_collection( $response );
        }

        return $this->send_success_response($data, $query_db_parameters);
    }

    public function endpoint_get_single_read_database($request, $query_parameters) {
        $model = $this->get_model();
        $model->load_data_query_parameters($query_parameters);
        $fields = $query_parameters->get_fields();
        $record = $model->get_data_record($fields);
        return $record;
    }

    public function endpoint_get_single ($request) {
        $query_parameters = $this->get_request_parameters($request);

        if (isset($request['id'])) {
            $id = $request['id'];
            $rest_id_key = $this->update_id_parameter($request, $query_parameters);
            $db_key = self::convert_rest_to_db_key($rest_id_key);
            $query_parameters->add_filter($db_key, $id);
        }

        $query_db_parameters = $this->convert_rest_to_db_request_parameters($query_parameters);

        $db_record = $this->endpoint_get_single_read_database ($request, $query_db_parameters);

        $data = array();

        if ( empty( $db_record ) ) {
            return $this->send_success_response( $data, $query_parameters );
        }

        $rest_record = $this->convert_db_to_rest_record($db_record);
        $schema = $this->get_schema_for_id_endpoint();
        $schema = $this->get_schema_filter_fields($schema, $query_parameters->get_fields());
        $response = $this->filter_schema_data($rest_record, $schema);

        $data = $this->prepare_for_collection( $response );

        return $this->send_success_response( $data, $query_parameters );
    }

    public function endpoint_post_write_database ($record, $request, $query_parameters) {
        $model = $this->get_model();
        $model->clear_all_data();
        $model->set_data_record($record);
        $model->save_data();
        $read_back_record = $model->get_data_record();
        return $read_back_record;
    }

    public function endpoint_post ($request) {
        $error = false;

        $query_parameters = $this->get_request_parameters($request);

        $rest_json_record = $request->get_json_params();
        if (!is_array($rest_json_record)) {
            $error = new \WP_Error('client_error', 'Client error. Error in json data sent in request.', array('status' => 400));
        }

        if( (!$error) and (empty($rest_json_record))) {
            $error = new \WP_Error('client_error', 'Client error. Error in json data sent in request.', array('status' => 400));
        }

        if( !$error) {
            $write_db_record = $this->convert_db_to_rest_record($rest_json_record);
            if(empty ($write_db_record)) {
                $error = new \WP_Error('client_error', 'Client error. Request has no data fields matching the schema.', array('status' => 400));
            }
        }

        if( !$error) {
            $query_db_parameters = $this->convert_rest_to_db_request_parameters($query_parameters);
            $db_record = $this->endpoint_post_write_database($write_db_record, $request, $query_db_parameters);
            if (is_array($db_record)) {
                $rest_record = $this->convert_db_to_rest_record($db_record);
                $schema = $this->get_schema_xxx();
                $schema = $this->get_schema_filter_fields($schema, $query_parameters->get_fields());
                $response = $this->filter_schema_data($rest_record, $schema);
                $data[] = $this->prepare_for_collection($response);
                return send_success_response($data, $query_parameters);
            } else {
                if ($db_record instanceof \WP_Error) {
                    $error = $db_record;
                } else {
                    $error = new \WP_Error('server_error', 'Server error. Can not create database entry.', array('status' => 500));
                }
            }
        }

        return $this->send_error_response($error);
    }

    function send_success_response($response, $query_parameters) {
        $super_response = array(
            'data' => $response,
            'info' => array(
                'status' => 'success',
                'total_items' => 0,
                'accepted_args' => $this->get_parameter_array($query_parameters),
                'invalid_args' => $this->invalid_db_parameters
            )
        );
        return rest_ensure_response($super_response);
    }

    function send_error_response($error=null) {
        if ($error instanceof \WP_Error) {
            $wp_error = $error;
        }
        else {
            $error_code = $this->last_error_code;
            if (!$error_code) {
                $error_code = 'undefined_error';
            }

            if (is_string($error)) {
                $last_error_message = $error;
            }
            else {
                $last_error_message = $this->last_error_message;
                if (!$last_error_message) {
                    $last_error_message = 'Undefined server error.';
                }
            }

            $http_status = $this->last_http_status;
            if (!$http_status) {
                $error_code = 500;
            }

            $wp_error = new \WP_Error($error_code, $last_error_message, array('status' => $error_code));
        }

        return $wp_error;
    }

    public function get_parameter_array($query_parameters) {
        $parameter_array = array();

        $offset = $query_parameters->get_offset();
        if (isset($offset)) {
            $parameter_array[self::PARAMETER_KEY_OFFSET] = $offset;
        }

        $limit = $query_parameters->get_limit();
        if (isset($limit)) {
            $parameter_array[self::PARAMETER_KEY_LIMIT] = $limit;
        }

        $searches = $query_parameters->get_search();
        if (!empty($searches)) {
            $parameter_array[self::PARAMETER_KEY_SEARCH] = $searches;
        }
        return $parameter_array;
    }

    protected function get_model() {
        if(!$this->model) {
            if($this->model_class_name) {
                $this->model = new $this->model_class_name;
            }
        }
        return $this->model;
    }

    public function filter_schema_data($unfiltered_record, $schema) {
        $filtered_data = array();
        foreach ( $schema['properties'] as $rest_key => $properties ) {
            if(array_key_exists($rest_key, $unfiltered_record)) {
                $filtered_data[$rest_key] = (string)$unfiltered_record[$rest_key];
            }
        }
        return $filtered_data;
    }

    public function prepare_for_collection( $response ) {
        if ( ! ( $response instanceof \WP_REST_Response ) ) {
            return $response;
        }

        $data  = (array) $response->get_data();
        $links = rest_get_server()::get_compact_response_links( $response );

        if ( ! empty( $links ) ) {
            $data['_links'] = $links;
        }

        return $data;
    }



    public function get_schema_filter_fields($schema, $fields) {
        if($fields) {
            foreach ($schema['properties'] as $key => $property) {
                if (! in_array($key, $fields, true)){
                    unset ( $schema['properties'][$key]);
                }
            }
        }
        return $schema;
    }

    public function get_schema() {
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'cheque',
            'description' => 'This schema holds the standardized cheque file.',
            'type' => 'object',
            'properties' => array(
                /* TODO: Pick of from database model */
            )
        );
        return $schema;
    }

    public function get_schema_for_id_endpoint() {
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'cheque',
            'description' => 'This schema holds the standardized cheque file.',
            'type' => 'object',
            'properties' => array(
                /* TODO: Pick of from database model */
            )
        );
        return $schema;
    }

    public function get_rest_database_mapping() {
        return null;
    }

    public function convert_rest_to_db_key ($key) {
        $mappings_table = $this->get_rest_database_mapping();
        if(array_key_exists($key, $mappings_table)) {
            return $mappings_table[$key];
        } else {
            return false;
        }
    }

    public function convert_rest_to_db_keys ($rest_keys) {
        $db_keys = array();
        $mappings_table = $this->get_rest_database_mapping();
        foreach($rest_keys as $rest_key) {
            if (array_key_exists($rest_keys, $mappings_table)) {
                $db_keys[]  = $mappings_table[$rest_key];
            }
            else {
                return false;
            }
        }
        return $db_keys;
    }

    public function convert_db_to_rest_key ($key) {
        $mappings_table = $this->get_rest_database_mapping();
        if(in_array ($key, $mappings_table)) {
            return array_search ($key, $mappings_table, true);
        } else {
            return false;
        }
    }

    public function convert_db_to_rest_keys ($keys) {
        $mappings_table = $this->get_rest_database_mapping();
        if(in_array ($key, $mappings_table)) {
            return array_search ($key, $mappings_table, true);
        } else {
            return false;
        }
    }

    public function convert_rest_to_db_record($rest_record) {
        $db_record = array();
        $mapping_table = $this->get_rest_database_mapping();
        foreach($mapping_table as $rest_key => $db_key) {
            if (isset($records[$db_key])) {
                $db_record[$db_key] = $rest_record[$rest_key];
            }
        }
        return $db_record;
    }


    public function convert_db_to_rest_record($db_record) {
        $rest_record = array();
        $mapping_table = $this->get_rest_database_mapping();
        foreach($mapping_table as $rest_key => $db_key) {
            if (array_key_exists($db_key, $db_record)) {
                $rest_record[$rest_key] = $db_record[$db_key];
            }
        }
        return $rest_record;
    }

}
