<?php
namespace Salesking\PHPSDK;

/**
 * This file brings in the Salesking Object class
 * @version     2.0.0
 * @package     SalesKing PHP SDK
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */

/**
 * Salesking SDK file for read/write access to objects
 * @since 2.0.0
 * @package SalesKing PHP SDK
*/
class Entity
{

    /**
     * object data
     * @var array data
     * @since 2.0.0
     */
    protected $data = array ();

    /**
     * object type
     * @var string obj_type
     * @since 2.0.0
     */
    protected $obj_type = null;

    /**
     * object schema
     * @var mixed schema definition
     * @since 2.0.0
     */
    protected $schema = null;


    /**
     * parent API object
     * @var Salesking parent object
     * @since 2.0.0
     */
    public $api = null;

    /**
     * Constructor method which is used to set some config stuff and load the schema file
     * @since 2.0.0
     * @param API $api API object
     * @param array $config configuration array
     */
    public function __construct(API $api, array $config)
    {
        // set static properties
        $this->obj_type = $config['obj_type'];
        $this->api = $api;

        // load schema from json file
        $this->schema = Helper::loadSchema($this->obj_type);
    }

    /**
     * magic setter function to set object data
     * @param string $property
     * @param mixed $value
     * @throws Exception
     * @since 2.0.0
     */
    public function __set($property, $value)
    {
        if (property_exists($this->schema->properties, $property)) {
            if ($this->validate($property, $value)) {
                $this->data[$property] = $value;
            } else {
                throw new Exception(
                    "SET_PROPERTYVALIDATION",
                    "invalid property value. Property: ".$property." - Value: ".$value,
                    array("property" => $property, "value" => $value)
                );
            }
        } else {
            throw new Exception("SET_INVALIDPROPERTY", "invalid property for this object type", $property);
        }
    }

    /**
     * Validate input values for the specified object property
     * @param string $property
     * @param mixed $value
     * @return bool
     * @since 2.0.0
     */
    public function validate($property, $value)
    {
        if (property_exists($this->schema->properties, $property)) {
            //validate property type
            switch ($this->schema->properties->$property->type) {
                case "string":
                    break;
                case "integer":
                    if (is_object($value) || is_array($value)) {
                        return false;
                    }

                    if (!ctype_digit((string)$value) && $value != "") {
                        return false;
                    }
                    break;
                case "number":
                    if (is_object($value) || is_array($value)) {
                        return false;
                    }

                    if (!is_numeric($value) && $value != "") {
                        return false;
                    }
                    break;
                case "array":
                    if (!is_array($value) && $value != "") {
                        return false;
                    }
                    break;
            }

            //validate maximum property length
            if (property_exists($this->schema->properties->$property, "maxLength")) {
                if (strlen($value) > $this->schema->properties->$property->maxLength) {
                    return false;
                }
            }

            //validate minimum property length
            if (property_exists($this->schema->properties->$property, "minLength")) {
                if (strlen($value) < $this->schema->properties->$property->minLength && $value != '') {
                    return false;
                }
            }

            //validate predefined input values
            if (property_exists($this->schema->properties->$property, "enum")) {
                if (!in_array($value, $this->schema->properties->$property->enum) && $value != "") {
                    return false;
                }
            }

            //validate input format
            if (property_exists($this->schema->properties->$property, "format")) {
                switch ($this->schema->properties->$property->format) {
                    case "date":
                        if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $value)
                            && $value != ""
                        ) {
                            return false;
                        }
                        break;
                    case "date-time":
                        //@todo which date-tme format is accepted??
                        break;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * magic getter function to get object properties
     * @param string $property
     * @return mixed
     * @since 2.0.0
     */
    public function __get($property)
    {
        if (array_key_exists($property, $this->data)) {
            return $this->data[$property];
        }

        return null;
    }

    /**
     * converts object data to an JSON encoded string
     * @return string JSON-encoded object data
     * @since 2.0.0
     */
    public function __toString()
    {
        return json_encode($this->getData());
    }

    /**
     * helper function to bind data to object
     *
     * @param array|Entity $data
     * @param array        $map array("company" => "organisation") maps value of company to organisation
     *
     * @return Entity
     * @throws Exception
     * @since 2.0.0
    */
    public function bind($data, $map = array())
    {
        // validate data type
        if (is_object($data) || is_array($data)) {
            // walk through all of our objects properties
            foreach (get_object_vars($this->schema->properties) as $property => $value) {
                if (is_array($data)) {
                    if (array_key_exists($property, $data)) {
                        $this->$property = $data[$property];
                    }
                } else {
                    if (property_exists($data, $property)) {
                        $this->$property = $data->$property;
                    }
                }
            }

            // manually assign stuff from one object to another by using a mapping array
            if (count($map)) {
                foreach ($map as $source => $target) {
                    if (is_array($data)) {
                        if (array_key_exists($source, $data) && property_exists($this->schema->properties, $target)) {
                            $this->$target = $data[$source];
                        }
                    } else {
                        if (property_exists($data, $source) && property_exists($this->schema->properties, $target)) {
                            $this->$target = $data->$source;
                        }
                    }
                }
            }
        } else {
            throw new Exception("BIND_INVALIDTYPE", "invalid data type - please provide an array or object");
        }

        return $this;
    }

    /**
     * Fetch object type
     * @return string object type
     * @since 2.0.0
     */
    public function getObjType()
    {
        return $this->obj_type;
    }

    /**
     * set object type
     * @param string $type object type
     */
    public function setObjType($obj_type)
    {
        $this->obj_type = $obj_type;
        $this->schema = Helper::loadSchema($this->obj_type);
    }

    /**
     * Fetch object data
     *
     * @param string $format data format
     *
     * @return array|Entity object data
     * @throws Exception
     *@internal param string $type define function return type (array, object)
     * @since 2.0.0
     */
    public function getData($format = "array")
    {
        //return object data depending on selected type
        switch ($format) {
            case "array":
                return $this->data;
            break;

            case "object":
                $object = new \stdClass();

                foreach ($this->data as $key => $value) {
                    // @TODO autodetect if value is array and recurse for each
                    $object->$key = $value;
                }

                return $object;
            break;

            default:
                throw new Exception("GETDATA_FORMAT", "Invalid format");
            break;
        }
    }

    /**
     * Saves the current object to Salesking
     * @return mixed response
     * @throws Exception
     * @since 2.0.0
     */
    public function save()
    {
        $obj_type = $this->getObjType();

        //we have wrap our information this way because the api needs it this way
        $object = new \stdClass();
        $object->$obj_type = $this->getData();

        // when there's already an ID, we're just updating, otherwise we're creating a new row
        if ($this->id) {
            //get endpoint
            $endpoint = $this->getEndpoint("update");

            // make request
            $response = $this->api->request(
                "/api/" . str_ireplace('{id}', $this->id, $endpoint->href),
                $endpoint->method,
                json_encode($object)
            );

            // let's decide what to do next
            switch ($response['code']) {
                case 200:
                    $this->bind($response['body']->$obj_type);
                    return $response;
                    break;

                default:
                    throw new Exception("UPDATE_ERROR", "Update failed, an error occured", $response);
                    break;
            }
        } else {
            $endpoint = $this->getEndpoint("create");

            $response = $this->api->request("/api/" . $endpoint->href, $endpoint->method, json_encode($object));

            switch ($response['code']) {
                case 201:
                    $this->bind($response['body']->$obj_type);
                    return $response;
                    break;

                default:
                    throw new Exception("CREATE_ERROR", "Create failed, an error occured.", $response);
                    break;
            }
        }
    }

    /**
     * load object data from Salesking
     *
     * @param string $id object id
     *
     * @return Entity
     * @throws Exception
     * @since 2.0.0
     */
    public function load($id = null)
    {
        $obj_type = $this->getObjType();
        $endpoint = $this->getEndpoint("self");

        if ($id != null) {
            $this->id = $id;
        }

        if ($this->id) {
            $response = $this->api->request("/api/" . str_ireplace('{id}', $this->id, $endpoint->href));

            switch ($response['code']) {
                case 200:
                    return $this->bind($response['body']->$obj_type);
                    break;

                default:
                    throw new Exception("LOAD_ERROR", "Fetching failed, an error happend", $response);
                    break;
            }
        } else {
            throw new Exception("LOAD_IDNOTSET", "could not load object");
        }
    }

    /**
     * Deletes the current object
     * @return mixed response
     * @throws Exception
     * @since 2.0.0
     */
    public function delete()
    {
        //fetch endpoint information from schema file
        $endpoint = $this->getEndpoint("destroy");

        if ($this->id) {
            $response = $this->api->request(
                "/api/" . str_ireplace('{id}', $this->id, $endpoint->href),
                $endpoint->method
            );

            switch ($response['code']) {
                case 200:
                    return $response;
                    break;

                default:
                    throw new Exception("DELETE_ERROR", "Deleting failed, an error happend", $response);
                    break;
            }
        } else {
            throw new Exception("DELETE_IDNOTSET", "could not delete object");
        }
    }

    /**
     * Get Salesking API endpoint for a specific task
     *
     * @param string $rel
     *
     * @return Entity endpoint information
     * @throws Exception
     * @since 2.0.0
     */
    public function getEndpoint($rel = "self")
    {
        //valid endpoint?
        if (array_key_exists($rel, $this->schema->links)) {
            return $this->schema->links[$rel];
        } else {
            throw new Exception("ENDPOINT_NOTFOUND", "invalid endpoint");
        }
    }
}
