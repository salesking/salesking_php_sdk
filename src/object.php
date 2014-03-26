<?php
/**
 * This file brings in the Salesking Object class
 * @version     1.0.0
 * @package     SalesKing PHP SDK
 * @license     MIT License; see LICENSE
 * @copyright   Copyright (C) 2012 David Jardin
 * @link        http://www.salesking.eu
 */

if(!class_exists("SaleskingException")) {
    throw new Exception("missing salesking.php library file");
}

/**
 * Salesking SDK file for read/write access to objects
 * @since 1.0.0
 * @package SalesKing PHP SDK
*/
class SaleskingObject {

    /**
     * object data
     * @var array data
     * @since 1.0.0
     */
    protected $data = array ();

    /**
     * object type
     * @var string obj_type
     * @since 1.0.0
     */
    protected $obj_type = null;

    /**
     * object schema
     * @var mixed schema definition
     * @since 1.0.0
     */
    protected $schema = null;


    /**
     * parent API object
     * @var Salesking parent object
     * @since 1.0.0
     */
    public $api = null;

    /**
     * Constructor method which is used to set some config stuff and load the schema file
     * @since 1.0.0
     * @param Salesking $api API object
     * @param array $config configuration array
     */
    public function __construct(Salesking $api, array $config)
    {
        // set static properties
        $this->obj_type = $config['obj_type'];
        $this->api = $api;

        // load schema from json file
        $this->schema = SaleskingHelper::loadSchema($this->obj_type);
    }

    /**
     * magic setter function to set object data
     * @param string $property
     * @param mixed $value
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function __set($property, $value)
    {
        if(property_exists($this->schema->properties,$property))
        {
            if($this->validate($property, $value)) {
                $this->data[$property] = $value;
            }
            else
            {
                throw new SaleskingException(
                    "SET_PROPERTYVALIDATION",
                    "invalid property value. Property: ".$property." - Value: ".$value,
                    array("property" => $property, "value" => $value)
                );
            }
        }
        else
        {
            throw new SaleskingException("SET_INVALIDPROPERTY","invalid property for this object type",$property);
        }
    }

    /**
     * Validate input values for the specified object property
     * @param string $property
     * @param mixed $value
     * @return bool
     * @since 1.0.0
     */
    public function validate($property, $value)
    {
        if(property_exists($this->schema->properties,$property))
        {
            //validate property type
            switch ($this->schema->properties->$property->type) {
                case "string":
                    break;
                case "integer":
                    if(is_object($value) or is_array($value))
                    {
                        return false;
                    }

                    if(!ctype_digit((string)$value) AND $value != "")
                    {
                        return false;
                    }
                    break;
                case "number":
                    if(is_object($value) or is_array($value))
                    {
                        return false;
                    }

                    if(!is_numeric($value) AND $value != "")
                    {
                        return false;
                    }
                    break;
                case "array":
                    if(!is_array($value) AND $value != "")
                    {
                        return false;
                    }
                    break;
            }

            //validate maximum property length
            if(property_exists($this->schema->properties->$property,"maxLength"))
            {
                if(strlen($value) > $this->schema->properties->$property->maxLength)
                {
                    return false;
                }
            }

            //validate minimum property length
            if(property_exists($this->schema->properties->$property,"minLength"))
            {
                if(strlen($value) < $this->schema->properties->$property->minLength AND $value != '')
                {
                    return false;
                }
            }

            //validate predefined input values
            if(property_exists($this->schema->properties->$property,"enum"))
            {
                if(!in_array($value,$this->schema->properties->$property->enum) AND $value != "")
                {
                    return false;
                }
            }

            //validate input format
            if(property_exists($this->schema->properties->$property,"format"))
            {
                switch ($this->schema->properties->$property->format) {
                    case "date":
                        if(!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/',$value) AND $value != "")
                        {
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
     * @since 1.0.0
     */
    public function __get($property)
    {
        if(array_key_exists($property,$this->data))
        {
            return $this->data[$property];
        }

        return null;
    }

    /**
     * converts object data to an JSON encoded string
     * @return string JSON-encoded object data
     * @since 1.0.0
     */
    public function __toString()
    {
        return json_encode($this->getData());
    }

    /**
     * helper function to bind data to object
     * @param array|object $data
     * @param array $map array("company" => "organisation") maps value of company to organisation
     * @return SaleskingObject
     * @throws SaleskingException
     * @since 1.0.0
    */
    public function bind($data, $map = array())
    {
        // validate data type
        if(is_object($data) OR is_array($data))
        {
            // walk through all of our objects properties
            foreach(get_object_vars($this->schema->properties) as $property => $value)
            {
                if(is_array($data))
                {
                    if(array_key_exists($property, $data))
                    {
                        $this->$property = $data[$property];
                    }
                }
                else
                {
                    if(property_exists($data,$property))
                    {
                        $this->$property = $data->$property;
                    }
                }
            }

            // manually assign stuff from one object to another by using a mapping array
            if(count($map))
            {
                foreach($map as $source => $target)
                {
                    if(is_array($data))
                    {
                        if(array_key_exists($source, $data) AND property_exists($this->schema->properties, $target))
                        {
                            $this->$target = $data[$source];
                        }
                    }
                    else
                    {
                        if(property_exists($data, $source) AND property_exists($this->schema->properties, $target))
                        {
                            $this->$target = $data->$source;
                        }
                    }
                }
            }
        }
        else
        {
            throw new SaleskingException("BIND_INVALIDTYPE","invalid data type - please provide an array or object");
        }

        return $this;
    }

    /**
     * Fetch object type
     * @return string object type
     * @since 1.0.0
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
        $this->schema = SaleskingHelper::loadSchema($this->obj_type);
    }

    /**
     * Fetch object data
     * @param string $format data format
     * @internal param string $type define function return type (array, object)
     * @since 1.0.0
     * @return array|object object data
     * @throws SaleskingException
     */
    public function getData($format = "array")
    {
        //return object data depending on selected type
        switch($format)
        {
            case "array":
                return $this->data;
            break;

            case "object":
                $object = new stdClass();

                foreach($this->data as $key => $value)
                { // TODO autodetect if value is array and recurse for each
                    $object->$key = $value;
                }

                return $object;
            break;

            default:
                throw new SaleskingException("GETDATA_FORMAT","Invalid format");
            break;

        }
    }

    /**
     * Saves the current object to Salesking
     * @return mixed response
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function save()
    {
        $obj_type = $this->getObjType();

        //we have wrap our information this way because the api needs it this way
        $object = new StdClass;
        $object->$obj_type = $this->getData();

        // when there's already an ID, we're just updating, otherwise we're creating a new row
        if($this->id)
        {
            //get endpoint
            $endpoint = $this->getEndpoint("update");

            // make request
            $response = $this->api->request("/api/".str_ireplace('{id}',$this->id,$endpoint->href),$endpoint->method,json_encode($object));

            // let's decide what to do next
            switch($response['code']) {
                case "200":
                    $this->bind($response['body']->$obj_type);
                    return $response;
                    break;

                default:
                    throw new SaleskingException("UPDATE_ERROR","Update failed, an error occured",$response);
                    break;
            }

        }
        else
        {
            $endpoint = $this->getEndpoint("create");

            $response = $this->api->request("/api/".$endpoint->href,$endpoint->method,json_encode($object));

            switch($response['code']) {
                case "201":
                    $this->bind($response['body']->$obj_type);
                    return $response;
                    break;

                default:
                    throw new SaleskingException("CREATE_ERROR","Create failed, an error occured.",$response);
                    break;
            }
        }

    }

    /**
     * load object data from Salesking
     * @return SaleskingObject
     * @throws SaleskingException
     * @param string $id object id
     * @since 1.0.0
     */
    public function load($id = null)
    {
        $obj_type = $this->getObjType();
        $endpoint = $this->getEndpoint("self");

        if($id != null)
        {
            $this->id = $id;
        }

        if($this->id)
        {
            $response = $this->api->request("/api/".str_ireplace('{id}',$this->id,$endpoint->href));

            switch($response['code']) {
                case "200":
                    return $this->bind($response['body']->$obj_type);
                    break;

                default:
                    throw new SaleskingException("LOAD_ERROR","Fetching failed, an error happend",$response);
                    break;
            }
        }
        else
        {
            throw new SaleskingException("LOAD_IDNOTSET","could not load object");
        }
    }

    /**
     * Deletes the current object
     * @return mixed response
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function delete()
    {
        //fetch endpoint information from schema file
        $endpoint = $this->getEndpoint("destroy");

        if($this->id)
        {
            $response = $this->api->request("/api/".str_ireplace('{id}',$this->id,$endpoint->href),$endpoint->method);

            switch($response['code']) {
                case "200":
                    return $response;
                    break;

                default:
                    throw new SaleskingException("DELETE_ERROR","Deleting failed, an error happend",$response);
                    break;
            }
        }
        else
        {
            throw new SaleskingException("DELETE_IDNOTSET","could not delete object");
        }
    }

    /**
     * Get Salesking API endpoint for a specific task
     * @param string $rel
     * @return object endpoint information
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function getEndpoint($rel = "self")
    {
        //valid endpoint?
        if(array_key_exists($rel,$this->schema->links))
        {
            return $this->schema->links[$rel];
        }
        else
        {
            throw new SaleskingException("ENDPOINT_NOTFOUND","invalid endpoint");
        }
    }

}