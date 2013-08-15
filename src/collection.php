<?php
/**
 * This file brings in the Salesking collection class
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
 * Salesking SDK file for read access to collections
 * @since 1.0.0
 * @package SalesKing PHP SDK
 */
class SaleskingCollection {
    /**
     * already fetched items
     * @var array already fetched items
     * @since 1.0.0
     */
    protected $items = array();

    /**
     * object schema
     * @var mixed object schema
     * @since 1.0.0
     */
    protected $schema = null;

    /**
     * collection object type
     * @var string collection type
     * @since 1.0.0
     */
    protected $obj_type = null;

    /**
     * use autoload function
     * @var boolean use autoload function
     * @since 1.0.0
     */
    protected $autoload = false;

    /**
     * parent object
     * @var Salesking parent object
     * @since 1.0.0
     */
    protected $api = null;

    /**
     * collection filters
     * @var array collection filters
     * @since 1.0.0
     */
    protected $filters = array();

    /**
     * current page
     * @var int current page
     * @since 1.0.0
     */
    protected $current_page = null;

    /**
     * total pages
     * @var int total pages
     * @since 1.0.0
     */
    protected $total_pages = null;

    /**
     * total items
     * @var int total items
     * @since 1.0.0
     */
    protected $total_entries = null;

    /**
     * number of items per page
     * @var int number of items per page
     * @since 1.0.0
     */
    protected $per_page = 100;

    /**
     * sorting direction
     * @var string sorting direction
     * @since 1.0.0
     */
    protected $sort = "ASC";

    /**
     * property to use for sorting
     * @var string property to use for sorting
     * @since 1.0.0
     */
    protected $sort_by = null;

    /**
     * Constructor method which is used to set some config stuff and load the schema file
     * @param Salesking $api
     * @param array $config
     * @since 1.0.0
     */
    public function __construct(Salesking $api, array $config) {
        $this->api = $api;
        $this->obj_type = $config['obj_type'];

        $this->schema = SaleskingHelper::loadSchema($this->obj_type);

        //are we using the autoload function?
        if(array_key_exists("autoload",$config))
        {
            $this->autoload = $config['autoload'];
        }
    }

    /**
     * returns A JSON encoded string of the collection items
     * @return string
     * @since 1.0.0
     */
    public function __toString() {
        return json_encode($this->getItems());
    }

    /**
     * Execute a request against the Salesking API to fetch the items
     * @param int $page page number to fetch
     * @return SaleskingCollection
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function load(int $page = null) {
        // initialize empty query
        $query = array();

        // append filters to query string
        $filters = $this->getFilters();
        if(count($filters)){
            foreach($filters as $filter => $value)
            {
                $query[] = "filter[".$filter."]" ."=". urlencode($value);
            }
        }

        // append sorting direction
        if($this->sort != "")
        {
            $query[] = "sort=".$this->sort;
        }

        // append sorting property
        if($this->sort_by != "")
        {
            $query[] = "sort_by=".$this->sort_by;
        }

        // append per-page property
        if($this->per_page != "")
        {
            $query[] = "per_page=".$this->per_page;
        }

        // append requested page
        if($page != "")
        {
            $query[] = "page=".$page;
        }

        // put the query together
        $query = "?".implode("&",$query);

        // execute request
        $response = $this->api->request("/api/".$this->schema->links['instances']->href.$query);

        // decide what to do next depending on responsecode
        switch($response['code']) {
            case "200":
                // get object type and pluralize it because we need it for decoding our response
                $obj_type = $this->getObjType();
                $types = SaleskingHelper::pluralize($obj_type);

                // set number of total entries and pages
                $this->total_entries = $response['body']->collection->total_entries;
                $this->total_pages = $response['body']->collection->total_pages;
                $this->current_page = $response['body']->collection->current_page;

                // bind data from response to objects
                foreach($response['body']->$types as $object)
                {
                    $item = $this->api->getObject($obj_type);
                    $item->bind($object->$obj_type);
                    $this->items[] = $item;
                }

                // autoload is true, so lets fetch all the other pages recursivly
                if($this->autoload === true AND $this->total_pages > 1 AND $page == "")
                {
                    for($i = 2; $i <= $this->total_pages; $i++)
                    {
                        $this->load($i);
                    }
                }

                return $this;
                break;

            default:
                throw new SaleskingException("LOAD_ERROR","Fetching failed, an error happend",$response);
                break;
        }
    }
    /**
     * Return current filters
     * @return array filters
     * @since 1.0.0
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set multiple filters at once
     * @param array $filters
     * @return SaleskingCollection
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function setFilters(array $filters)
    {
        if(!is_array($filters))
        {
            throw new SaleskingException("SETFILTERS_ARRAYNEEDED","Only arrays can be used to define filters");
        }

        $this->filters = null;
        foreach ($filters as $filter => $value)
        {
            $this->addFilter($filter, $value);
        }

        return $this;
    }

    /**
     * Add a new filter which shall be used when doing the request
     * @param string $filter
     * @param mixed $value
     * @return SaleskingCollection
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function addFilter($filter,$value)
    {
        if(property_exists($this->schema->links['instances']->properties,"filter[".$filter."]"))
        {
            if($this->validateFilter($filter,$value))
            {
                $this->filters[$filter] = $value;
            }
            else
            {
                throw new SaleskingException("FILTER_INVALID","Invalid filter value",array("filter" => $filter, "value" => $value));
            }

            return $this;
        }

        throw new SaleskingException("FILTER_NOTEXISTING","Filter does not exist");
    }

    /**
     * Validate collection filter and value
     * @param string $filter name of the filter
     * @param mixed $value filter value
     * @return bool
     * @since 1.0.0
     */
    public function validateFilter($filter, $value)
    {
        // make sure that we have a valid filter
        if(property_exists($this->schema->links['instances']->properties,"filter[".$filter."]"))
        {
            //this is a little bit ugly but we need it to work around a php bug
            $name = "filter[".$filter."]";
            $schema = $this->schema->links['instances']->properties->$name;
        }
        else
        {
            return false;
        }

        //validate property type
        switch ($schema->type) {
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
        if(property_exists($schema,"maxLength"))
        {
            if(strlen($value) > $schema->maxLength)
            {
                return false;
            }
        }

        //validate predefined input values
        if(property_exists($schema,"enum"))
        {
            if(!in_array($value,$schema->enum) AND $value != "")
            {
                return false;
            }
        }

        //validate input format
        if(property_exists($schema,"format"))
        {
            switch ($schema->format) {
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

    /**
     * Returns the fetched items
     * @return array Items
     * @since 1.0.0
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Returns current collection type
     * @return string collection type
     * @since 1.0.0
     */
    public function getObjType() {
        return $this->obj_type;
    }

    /**
     * set object type
     * @param string $obj_type collection type
     * @since 1.0.0
     */
    public function setObjType($obj_type) {
        $this->obj_type = $obj_type;
        $this->schema = SaleskingHelper::loadSchema($this->obj_type);
    }

    /**
     * magic method for mapping all kinds of method calls to addFilter
     * @param string $method method name
     * @param array $args array of arguments
     * @return SaleskingCollection
     * @throws BadMethodCallException
     * @since 1.0.0
     */
    public function __call($method, array $args) {
        try {
            $this->addFilter($method,$args[0]);
            return $this;
        }
        catch (SaleskingException $e)
        {
            if($e->getCode() == "FILTER_NOTEXISTING")
            {
                throw new BadMethodCallException('Call to undefined method :'.$method);
            }

            throw $e;
        }
    }

    /**
     * Set the current sorting direction
     * @param string $direction Sorting direction - either ASC or DESC
     * @return SaleskingCollection
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function sort($direction)
    {
        // make sure that we have a valid direction string
        if($direction == "ASC" OR $direction == "DESC")
        {
            $this->sort = $direction;
            return $this;
        }

        throw new SaleskingException("SORT_INVALIDDIRECTION","Invalid sorting direction - please choose either ASC or DESC");
    }

    /**
     * Get the current sorting direction
     * @return string sorting direction
     * @since 1.0.0
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set the property which is used to sort the entries when doing a request
     * @param string $property
     * @return SaleskingCollection
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function sortBy($property)
    {
        // make sure that the api supports sorting for this kind of object
        if(property_exists($this->schema->links['instances']->properties,"sort_by"))
        {
            // make sure that we have a valid property
            if(in_array($property,$this->schema->links['instances']->properties->sort_by->enum))
            {
                $this->sort_by = $property;
                return $this;
            }
            else
            {
                throw new SaleskingException("SORTBY_INVALIDPROPERTY","Invalid property for sorting");
            }
        }
        else
        {
            throw new SaleskingException("SORTBY_CANNOTSORT","object type doesnt support sorting");
        }
    }

    /**
     * Get the current property which is used to sort the entries when doing the request
     * @return string object property
     * @since 1.0.0
     */
    public function getSortBy()
    {
        return $this->sort_by;
    }

    /**
     * Set the number of entries which are gonna be fetched per request
     * @param int $number Number of entries
     * @return SaleskingCollection
     * @throws SaleskingException
     * @since 1.0.0
     */
    public function perPage($number)
    {
        if(is_numeric($number) AND $number <= 100)
        {
            $this->per_page = $number;
            return $this;
        }
        else
        {
            throw new SaleskingException("PERPAGE_ONLYINT","Please set an integer <100 for the per-page limit");
        }
    }

    /**
     * Return the number of entries which are gonna be fetched per request
     * @return int Number of entries per page to fetch
     * @since 1.0.0
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     * total number of collection items
     * @return int total items
     * @since 1.0.0
     */
    public function getTotal()
    {
        return $this->total_entries;
    }

    /**
     * returns total number of collection pages
     * @return int total pages
     * @since 1.0.0
     */
    public function getTotalPages()
    {
        return $this->total_pages;
    }

    /**
     * returns the current page number
     * @return int current page
     * @since 1.0.0
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }
}