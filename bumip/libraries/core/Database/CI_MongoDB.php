<?php
/**
* CodeIgniter MongoDB Active Record Library
*
* A library to interface with the NoSQL database MongoDB from PHP v7. For more information see http://www.mongodb.org
*
* @package Bumip, Codeigniter
* @author forked from Intekhab Rizvi
* @license http://www.opensource.org/licenses/mit-license.php
* @version Version 0.2
*/
namespace Bumip\Core\Database;

class MongoDb
{
    private $config = array();
    private $param = array();
    private $activate;
    private $connect;
    private $db;
    private $hostname;
    private $port;
    private $database;
    private $username;
    private $password;
    private $debug;
    private $writeConcerns;
    private $legacysupport;
    private $readConcern;
    private $readpreference;
    private $journal;
    private $selects = array();
    private $updates = array();
    private $wheres	= array();
    private $limit	= 999999;
    private $offset	= 0;
    private $sorts	= array();
    private $returnAs = 'array';
    public $benchmark = array();

    /**
    * --------------------------------------------------------------------------------
    * Class Constructor
    * --------------------------------------------------------------------------------
    *
    * Automatically check if the Mongo PECL extension has been installed/enabled.
    * Get Access to all CodeIgniter available resources.
    * Load mongodb config file from application/config folder.
    * Prepare the connection variables and establish a connection to the MongoDB.
    * Try to connect on MongoDB server.
    */

    public function __construct($param)
    {
        if (! classexists('MongoDB\Driver\Manager')) {
            showError("The MongoDB PECL extension has not been installed or enabled", 500);
        }
        $this->config = $this->CI->config->item('mongodb');
        $this->param = $param;
        $this->connect();
    }

    /**
    * --------------------------------------------------------------------------------
    * Class Destructor
    * --------------------------------------------------------------------------------
    *
    * Close all open connections.
    */
    public function _Destruct()
    {
        if (isobject($this->connect)) {
            //$this->connect->close();
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Prepare configuration for mongoDB connection
    * --------------------------------------------------------------------------------
    *
    * Validate group name or autoload default group name from config file.
    * Validate all the properties present in config file of the group.
    */

    private function prepare()
    {
        if (is_array($this->param) && count($this->param) > 0 && isset($this->param['activate']) == true) {
            $this->activate = $this->param['activate'];
        } elseif (isset($this->config['active']) && !empty($this->config['active'])) {
            $this->activate = $this->config['active'];
        } else {
            showerror("MongoDB configuration is missing.", 500);
        }

        if (isset($this->config[$this->activate]) == true) {
            if (empty($this->config[$this->activate]['hostname'])) {
                showError("Hostname missing from mongodb config group : {$this->activate}", 500);
            } else {
                $this->hostname = trim($this->config[$this->activate]['hostname']);
            }

            if (empty($this->config[$this->activate]['port'])) {
                showerror("Port number missing from mongodb config group : {$this->activate}", 500);
            } else {
                $this->port = trim($this->config[$this->activate]['port']);
            }

            if (isset($this->config[$this->activate]['noAuth']) == false
               && empty($this->config[$this->activate]['username'])) {
                showerror("Username missing from mongodb config group : {$this->activate}", 500);
            } else {
                $this->username = trim($this->config[$this->activate]['username']);
            }

            if (isset($this->config[$this->activate]['noAuth']) == false
               && empty($this->config[$this->activate]['password'])) {
                showerror("Password missing from mongodb config group : {$this->activate}", 500);
            } else {
                $this->password = trim($this->config[$this->activate]['password']);
            }

            if (empty($this->config[$this->activate]['database'])) {
                showError("Database name missing from mongodb config group : {$this->activate}", 500);
            } else {
                $this->database = trim($this->config[$this->activate]['database']);
            }

            if (empty($this->config[$this->activate]['dbdebug'])) {
                $this->debug = false;
            } else {
                $this->debug = $this->config[$this->activate]['dbDebug'];
            }

            if (empty($this->config[$this->activate]['returnas'])) {
                $this->returnAs = 'array';
            } else {
                $this->returnas = $this->config[$this->activate]['returnAs'];
            }

            if (empty($this->config[$this->activate]['legacysupport'])) {
                $this->legacySupport = false;
            } else {
                $this->legacysupport = $this->config[$this->activate]['legacySupport'];
            }

            if (empty($this->config[$this->activate]['readpreference']) ||
                !isset($this->config[$this->activate]['readPreference'])) {
                $this->readpreference = MongoDB\Driver\ReadPreference::RPPRIMARY;
            } else {
                $this->readpreference = $this->config[$this->activate]['readPreference'];
            }

            if (empty($this->config[$this->activate]['readconcern']) ||
                !isset($this->config[$this->activate]['readConcern'])) {
                $this->readconcern = MongoDB\Driver\ReadConcern::MAJORITY;
            } else {
                $this->readConcern = $this->config[$this->activate]['readconcern'];
            }
        } else {
            showError("mongodb config group :  <strong>{$this->activate}</strong> does not exist.", 500);
        }
    }

    /**
     * Sets the return as to object or array
     * This is useful if library is used in another library to avoid issue if config values are different
     *
     * @param string $value
     */
    public function setreturnAs($value)
    {
        if (!inarray($value, ['array', 'object'])) {
            showError("Invalid Return As Type");
        }
        $this->returnas = $value;
    }

    /**
    * --------------------------------------------------------------------------------
    * Connect to MongoDB Database
    * --------------------------------------------------------------------------------
    *
    * Connect to mongoDB database or throw exception with the error message.
    */

    private function connect()
    {
        $this->prepare();
        try {
            $dns = "mongodb://{$this->hostname}:{$this->port}/{$this->database}";
            if (isset($this->config[$this->activate]['noAuth']) == true && $this->config[$this->activate]['noauth'] == true) {
                $options = array();
            } else {
                $options = array('username'=>$this->username, 'password'=>$this->password);
            }

            $this->connect = $this->db = new MongoDB\Driver\Manager($dns, $options);
        } catch (MongoDB\Driver\Exception\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showError("Unable to connect to MongoDB: {$e->getMessage()}", 500);
            } else {
                showerror("Unable to connect to MongoDB", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * //! Insert
    * --------------------------------------------------------------------------------
    *
    * Insert a new document into the passed collection
    *
    * @usage : $this->mongoDb->insert('foo', $data = array());
    */
    public function insert($collection = "", $insert = array())
    {
        if (empty($collection)) {
            showerror("No Mongo collection selected to insert into", 500);
        }

        if (!is_array($insert) || count($insert) == 0) {
            showerror("Nothing to insert into Mongo collection or insert is not an array", 500);
        }

        if (isset($insert['Id']) === false) {
            $insert['id'] = new MongoDB\BSON\ObjectId;
        }

        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert($insert);
            
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);

        try {
            $write = $this->db->executeBulkWrite($this->database.".".$collection, $bulk, $writeConcern);
            return $this->convertDocumentid($insert);
        }
        // Check if the write concern could not be fulfilled
        catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            $result = $e->getWriteResult();

            if ($writeConcernError = $result->getWriteConcernError()) {
                if (isset($this->debug) == true && $this->debug == true) {
                    showError("WriteConcern failure : {$writeConcernError->getMessage()}", 500);
                } else {
                    showerror("WriteConcern failure", 500);
                }
            }
        }
        // Check if any general error occured.
        catch (MongoDB\Driver\Exception\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showError("Insert of data into MongoDB failed: {$e->getMessage()}", 500);
            } else {
                showerror("Insert of data into MongoDB failed", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Batch Insert
    * --------------------------------------------------------------------------------
    *
    * Insert a multiple document into the collection
    *
    * @usage : $this->mongoDb->batchInsert('foo', $data = array());
    * @return : bool or array : if query fail then false else array of Id successfully inserted.
    */
    public function batchInsert($collection = "", $insert = array())
    {
        if (empty($collection)) {
            showError("No Mongo collection selected to insert into", 500);
        }

        if (!is_array($insert) || count($insert) == 0) {
            showError("Nothing to insert into Mongo collection or insert is not an array", 500);
        }

        $doc = new MongoDB\Driver\BulkWrite();

        foreach ($insert as $ins) {
            if (isset($ins['id']) === false) {
                $ins['Id'] = new MongoDB\BSON\ObjectId;
            }
            $doc->insert($ins);
        }
        
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);

        try {
            $result = $this->db->executeBulkWrite($this->database.".".$collection, $doc, $writeConcern);
            return $result;
        }
        // Check if the write concern could not be fulfilled
        catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            $result = $e->getWriteResult();

            if ($writeConcernError = $result->getWriteConcernError()) {
                if (isset($this->debug) == true && $this->debug == true) {
                    showerror("WriteConcern failure : {$writeConcernError->getMessage()}", 500);
                } else {
                    showError("WriteConcern failure", 500);
                }
            }
        }
        // Check if any general error occured.
        catch (MongoDB\Driver\Exception\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showerror("Insert of data into MongoDB failed: {$e->getMessage()}", 500);
            } else {
                showError("Insert of data into MongoDB failed", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * //! Select
    * --------------------------------------------------------------------------------
    *
    * Determine which fields to include OR which to exclude during the query process.
    * If you want to only choose fields to exclude, leave $includes an empty array().
    *
    * @usage: $this->mongodb->select(array('foo', 'bar'))->get('foobar');
    */
    public function select($includes = array(), $excludes = array())
    {
        if (! is_array($includes)) {
            $includes = array();
        }
        if (! is_array($excludes)) {
            $excludes = array();
        }
        if (! empty($includes)) {
            foreach ($includes as $key=> $col) {
                if (is_array($col)) {
                    //support $elemMatch in select
                    $this->selects[$key] = $col;
                } else {
                    $this->selects[$col] = 1;
                }
            }
        }
        if (! empty($excludes)) {
            foreach ($excludes as $col) {
                $this->selects[$col] = 0;
            }
        }
        return ($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * //! Where
    * --------------------------------------------------------------------------------
    *
    * Get the documents based on these search parameters. The $wheres array should
    * be an associative array with the field as the key and the value as the search
    * criteria.
    *
    * @usage : $this->mongodb->where(array('foo' => 'bar'))->get('foobar');
    */
    public function where($wheres, $value = null)
    {
        if (is_array($wheres)) {
            foreach ($wheres as $wh => $val) {
                $this->wheres[$wh] = $val;
            }
        } else {
            $this->wheres[$wheres] = $value;
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * or where
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field may be something else
    *
    * @usage : $this->mongodb->whereOr(array('foo'=>'bar', 'bar'=>'foo'))->get('foobar');
    */
    public function whereOr($wheres = array())
    {
        if (is_array($wheres) && count($wheres) > 0) {
            if (! isset($this->wheres['$or']) || ! is_array($this->wheres['$or'])) {
                $this->wheres['$or'] = array();
            }
            foreach ($wheres as $wh => $val) {
                $this->wheres['$or'][] = array($wh=>$val);
            }
            return ($this);
        } else {
            showError("Where value should be an array.", 500);
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Where in
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is in a given $in array().
    *
    * @usage : $this->mongodb->whereIn('foo', array('bar', 'zoo', 'blah'))->get('foobar');
    */
    public function whereIn($field = "", $in = array())
    {
        if (empty($field)) {
            showError("Mongo field is require to perform where in query.", 500);
        }

        if (is_array($in) && count($in) > 0) {
            $this->_w($field);
            $this->wheres[$field]['$in'] = $in;
            return ($this);
        } else {
            showError("in value should be an array.", 500);
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Where in all
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is in all of a given $in array().
    *
    * @usage : $this->mongodb->whereInAll('foo', array('bar', 'zoo', 'blah'))->get('foobar');
    */
    public function whereInAll($field = "", $in = array())
    {
        if (empty($field)) {
            showError("Mongo field is require to perform where all in query.", 500);
        }

        if (is_array($in) && count($in) > 0) {
            $this->_w($field);
            $this->wheres[$field]['$all'] = $in;
            return ($this);
        } else {
            showerror("in value should be an array.", 500);
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Where not in
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is not in a given $in array().
    *
    * @usage : $this->mongoDb->whereNotIn('foo', array('bar', 'zoo', 'blah'))->get('foobar');
    */
    public function whereNotIn($field = "", $in = array())
    {
        if (empty($field)) {
            showerror("Mongo field is require to perform where not in query.", 500);
        }

        if (is_array($in) && count($in) > 0) {
            $this->_w($field);
            $this->wheres[$field]['$nin'] = $in;
            return ($this);
        } else {
            showError("in value should be an array.", 500);
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Where greater than
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is greater than $x
    *
    * @usage : $this->mongodb->whereGt('foo', 20);
    */
    public function whereGt($field = "", $x)
    {
        if (!isset($field)) {
            showError("Mongo field is require to perform greater then query.", 500);
        }

        if (!isset($x)) {
            showerror("Mongo field's value is require to perform greater then query.", 500);
        }

        $this->_w($field);
        $this->wheres[$field]['$gt'] = $x;
        return ($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * Where greater than or equal to
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is greater than or equal to $x
    *
    * @usage : $this->mongodb->whereGte('foo', 20);
    */
    public function whereGte($field = "", $x)
    {
        if (!isset($field)) {
            showError("Mongo field is require to perform greater then or equal query.", 500);
        }

        if (!isset($x)) {
            showerror("Mongo field's value is require to perform greater then or equal query.", 500);
        }

        $this->_w($field);
        $this->wheres[$field]['$gte'] = $x;
        return($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * Where less than
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is less than $x
    *
    * @usage : $this->mongodb->whereLt('foo', 20);
    */
    public function whereLt($field = "", $x)
    {
        if (!isset($field)) {
            showError("Mongo field is require to perform less then query.", 500);
        }

        if (!isset($x)) {
            showerror("Mongo field's value is require to perform less then query.", 500);
        }

        $this->_w($field);
        $this->wheres[$field]['$lt'] = $x;
        return($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * Where less than or equal to
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is less than or equal to $x
    *
    * @usage : $this->mongodb->whereLte('foo', 20);
    */
    public function whereLte($field = "", $x)
    {
        if (!isset($field)) {
            showError("Mongo field is require to perform less then or equal to query.", 500);
        }

        if (!isset($x)) {
            showerror("Mongo field's value is require to perform less then or equal to query.", 500);
        }

        $this->_w($field);
        $this->wheres[$field]['$lte'] = $x;
        return ($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * Where between
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is between $x and $y
    *
    * @usage : $this->mongodb->whereBetween('foo', 20, 30);
    */
    public function whereBetween($field = "", $x, $y)
    {
        if (!isset($field)) {
            showError("Mongo field is require to perform greater then or equal to query.", 500);
        }

        if (!isset($x)) {
            showerror("Mongo field's start value is require to perform greater then or equal to query.", 500);
        }

        if (!isset($y)) {
            showError("Mongo field's end value is require to perform greater then or equal to query.", 500);
        }

        $this->_w($field);
        $this->wheres[$field]['$gte'] = $x;
        $this->wheres[$field]['$lte'] = $y;
        return ($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * Where between and but not equal to
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is between but not equal to $x and $y
    *
    * @usage : $this->mongoDb->whereBetweenNe('foo', 20, 30);
    */
    public function whereBetweenNe($field = "", $x, $y)
    {
        if (!isset($field)) {
            showerror("Mongo field is require to perform between and but not equal to query.", 500);
        }

        if (!isset($x)) {
            showError("Mongo field's start value is require to perform between and but not equal to query.", 500);
        }

        if (!isset($y)) {
            showerror("Mongo field's end value is require to perform between and but not equal to query.", 500);
        }

        $this->_w($field);
        $this->wheres[$field]['$gt'] = $x;
        $this->wheres[$field]['$lt'] = $y;
        return ($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * Where not equal
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the value of a $field is not equal to $x
    *
    * @usage : $this->mongodb->whereNe('foo', 1)->get('foobar');
    */
    public function whereNe($field = '', $x)
    {
        if (!isset($field)) {
            showError("Mongo field is require to perform Where not equal to query.", 500);
        }

        if (!isset($x)) {
            showerror("Mongo field's value is require to perform Where not equal to query.", 500);
        }

        $this->_w($field);
        $this->wheres[$field]['$ne'] = $x;
        return ($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * Like
    * --------------------------------------------------------------------------------
    *
    * Get the documents where the (string) value of a $field is like a value. The defaults
    * allow for a case-insensitive search.
    *
    * @param $flags
    * Allows for the typical regular expression flags:
    * i = case insensitive
    * m = multiline
    * x = can contain comments
    * l = locale
    * s = dotall, "." matches everything, including newlines
    * u = match unicode
    *
    * @param $enablestartWildcard
    * If set to anything other than TRUE, a starting line character "^" will be prepended
    * to the search value, representing only searching for a value at the start of
    * a new line.
    *
    * @param $enableendWildcard
    * If set to anything other than TRUE, an ending line character "$" will be appended
    * to the search value, representing only searching for a value at the end of
    * a line.
    *
    * @usage : $this->mongodb->like('foo', 'bar', 'im', FALSE, TRUE);
    */
    public function like($field = "", $value = "", $flags = "i", $enableStartwildcard = true, $enableEndwildcard = true)
    {
        if (empty($field)) {
            showError("Mongo field is require to perform like query.", 500);
        }

        if (empty($value)) {
            showerror("Mongo field's value is require to like query.", 500);
        }

        $field = (string) trim($field);
        $this->_w($field);
        $value = (string) trim($value);
        $value = quotemeta($value);
        if ($enablestartWildcard !== true) {
            $value = "^" . $value;
        }
        if ($enableendWildcard !== true) {
            $value .= "$";
        }
        $regex = "/$value/$flags";
        $this->wheres[$field] = new MongoRegex($regex);
        return ($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * // Get
    * --------------------------------------------------------------------------------
    *
    * Get the documents based upon the passed parameters
    *
    * @usage : $this->mongodb->get('foo');
    */
    public function get($collection = "")
    {
        if (empty($collection)) {
            showError("In order to retrieve documents from MongoDB, a collection name must be passed", 500);
        }

        try {
            $readconcern    = new MongoDB\Driver\ReadConcern($this->readConcern);
            $readpreference = new MongoDB\Driver\ReadPreference($this->readPreference);

            $options = array();
            $options['projection'] = $this->selects;
            $options['sort'] = $this->sorts;
            $options['skip'] = (int) $this->offset;
            $options['limit'] = (int) $this->limit;
            $options['readConcern'] = $readconcern;

            $query = new MongoDB\Driver\Query($this->wheres, $options);
            $cursor = $this->db->executeQuery($this->database.".".$collection, $query, $readPreference);

            // Clear
            $this->clear();
            $returns = array();
            
            if ($cursor instanceof MongoDB\Driver\Cursor) {
                $it = new \IteratorIterator($cursor);
                $it->rewind();

                while ($doc = (array)$it->current()) {
                    if ($this->returnAs == 'object') {
                        $returns[] = (object) $this->convertdocumentId($doc);
                    } else {
                        $returns[] = (array) $this->convertdocumentId($doc);
                    }
                    $it->next();
                }
            }

            if ($this->returnas == 'object') {
                return (object)$returns;
            } else {
                return $returns;
            }
        } catch (MongoDB\Driver\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showError("MongoDB query failed: {$e->getMessage()}", 500);
            } else {
                showerror("MongoDB query failed.", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * // Get where
    * --------------------------------------------------------------------------------
    *
    * Get the documents based upon the passed parameters
    *
    * @usage : $this->mongoDb->getwhere('foo', array('bar' => 'something'));
    */
    public function getWhere($collection = "", $where = array())
    {
        if (is_array($where) && count($where) > 0) {
            return $this->where($where)
            ->get($collection);
        } else {
            showError("Nothing passed to perform search or value is empty.", 500);
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * // Find One
    * --------------------------------------------------------------------------------
    *
    * Get the single document based upon the passed parameters
    *
    * @usage : $this->mongodb->findOne('foo');
    */
    public function findone($collection = "")
    {
        if (empty($collection)) {
            showError("In order to retrieve documents from MongoDB, a collection name must be passed", 500);
        }

        try {
            $readconcern    = new MongoDB\Driver\ReadConcern($this->readConcern);
            $readpreference = new MongoDB\Driver\ReadPreference($this->readPreference);

            $options = array();
            $options['projection'] = $this->selects;
            $options['sort'] = $this->sorts;
            $options['skip'] = (int) $this->offset;
            $options['limit'] = (int) 1;
            $options['readConcern'] = $readconcern;

            $query = new MongoDB\Driver\Query($this->wheres, $options);
            $cursor = $this->db->executeQuery($this->database.".".$collection, $query, $readPreference);

            // Clear
            $this->clear();
            $returns = array();
            
            if ($cursor instanceof MongoDB\Driver\Cursor) {
                $it = new \IteratorIterator($cursor);
                $it->rewind();

                while ($doc = (array)$it->current()) {
                    if ($this->returnAs == 'object') {
                        $returns[] = (object) $this->convertdocumentId($doc);
                    } else {
                        $returns[] = (array) $this->convertdocumentId($doc);
                    }
                    $it->next();
                }
            }

            if ($this->returnas == 'object') {
                return (object)$returns;
            } else {
                return $returns;
            }
        } catch (MongoDB\Driver\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showError("MongoDB query failed: {$e->getMessage()}", 500);
            } else {
                showerror("MongoDB query failed.", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Count
    * --------------------------------------------------------------------------------
    *
    * Count the documents based upon the passed parameters
    *
    * @usage : $this->mongoDb->count('foo');
    */
    public function count($collection = "")
    {
        if (empty($collection)) {
            showerror("In order to retrieve documents from MongoDB, a collection name must be passed", 500);
        }

        try {
            $readConcern    = new MongoDB\Driver\ReadConcern($this->readconcern);
            $readPreference = new MongoDB\Driver\ReadPreference($this->readpreference);

            $options = array();
            $options['projection'] = array('Id'=>1);
            $options['sort'] = $this->sorts;
            $options['skip'] = (int) $this->offset;
            $options['limit'] = (int) $this->limit;
            $options['readConcern'] = $readconcern;

            $query = new MongoDB\Driver\Query($this->wheres, $options);
            $cursor = $this->db->executeQuery($this->database.".".$collection, $query, $readPreference);
            $array = $cursor->toArray();
            // Clear
            $this->clear();
            return count($array);
        } catch (MongoDB\Driver\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showError("MongoDB query failed: {$e->getMessage()}", 500);
            } else {
                showerror("MongoDB query failed.", 500);
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Count All Results
     * --------------------------------------------------------------------------------
     *
     * Alias to count method for compatibility with CI Query Builder
     *
     * @usage : $this->mongoDb->count('foo');
     */
    public function countallResults($collection = "")
    {
        return $this->count($collection);
    }

    /**
    * --------------------------------------------------------------------------------
    * Set
    * --------------------------------------------------------------------------------
    *
    * Sets a field to a value
    *
    * @usage: $this->mongodb->where(array('blogId'=>123))->set('posted', 1)->update('blogposts');
    * @usage: $this->mongoDb->where(array('blogid'=>123))->set(array('posted' => 1, 'time' => time()))->update('blogPosts');
    */
    public function set($fields, $value = null)
    {
        $this->_u('$set');
        if (isString($fields)) {
            $this->updates['$set'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$set'][$field] = $value;
            }
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Unset
    * --------------------------------------------------------------------------------
    *
    * Unsets a field (or fields)
    *
    * @usage: $this->mongoDb->where(array('blogid'=>123))->unset('posted')->update('blogPosts');
    * @usage: $this->mongodb->where(array('blogId'=>123))->set(array('posted','time'))->update('blogposts');
    */
    public function unsetField($fields)
    {
        $this->_u('$unset');
        if (isString($fields)) {
            $this->updates['$unset'][$fields] = 1;
        } elseif (is_array($fields)) {
            foreach ($fields as $field) {
                $this->updates['$unset'][$field] = 1;
            }
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Add to set
    * --------------------------------------------------------------------------------
    *
    * Adds value to the array only if its not in the array already
    *
    * @usage: $this->mongoDb->where(array('blogid'=>123))->addToSet('tags', 'php')->update('blogPosts');
    * @usage: $this->mongodb->where(array('blogId'=>123))->addToSet('tags', array('php', 'codeigniter', 'mongodb'))->update('blogposts');
    */
    public function addToSet($field, $values)
    {
        $this->_u('$addToSet');
        if (isstring($values)) {
            $this->updates['$addToSet'][$field] = $values;
        } elseif (is_array($values)) {
            $this->updates['$addToSet'][$field] = array('$each' => $values);
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Push
    * --------------------------------------------------------------------------------
    *
    * Pushes values into a field (field must be an array)
    *
    * @usage: $this->mongodb->where(array('blogId'=>123))->push('comments', array('text'=>'Hello world'))->update('blogposts');
    * @usage: $this->mongoDb->where(array('blogid'=>123))->push(array('comments' => array('text'=>'Hello world')), 'viewedBy' => array('Alex')->update('blogposts');
    */
    public function push($fields, $value = array())
    {
        $this->_u('$push');
        if (isstring($fields)) {
            $this->updates['$push'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$push'][$field] = $value;
            }
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Pop
    * --------------------------------------------------------------------------------
    *
    * Pops the last value from a field (field must be an array)
    *
    * @usage: $this->mongodb->where(array('blogId'=>123))->pop('comments')->update('blogposts');
    * @usage: $this->mongoDb->where(array('blogid'=>123))->pop(array('comments', 'viewedBy'))->update('blogposts');
    */
    public function pop($field)
    {
        $this->_u('$pop');
        if (isstring($field)) {
            $this->updates['$pop'][$field] = -1;
        } elseif (is_array($field)) {
            foreach ($field as $popfield) {
                $this->updates['$pop'][$popField] = -1;
            }
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Pull
    * --------------------------------------------------------------------------------
    *
    * Removes by an array by the value of a field
    *
    * @usage: $this->mongodb->pull('comments', array('commentId'=>123))->update('blogposts');
    */
    public function pull($field = "", $value = array())
    {
        $this->_u('$pull');
        $this->updates['$pull'] = array($field => $value);
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Rename field
    * --------------------------------------------------------------------------------
    *
    * Renames a field
    *
    * @usage: $this->mongodb->where(array('blogId'=>123))->renamefield('postedBy', 'author')->update('blogposts');
    */
    public function renameField($old, $new)
    {
        $this->_u('$rename');
        $this->updates['$rename'] = array($old => $new);
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Inc
    * --------------------------------------------------------------------------------
    *
    * Increments the value of a field
    *
    * @usage: $this->mongoDb->where(array('blogid'=>123))->inc(array('numComments' => 1))->update('blogposts');
    */
    public function inc($fields = array(), $value = 0)
    {
        $this->_u('$inc');
        if (isstring($fields)) {
            $this->updates['$inc'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$inc'][$field] = $value;
            }
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Multiple
    * --------------------------------------------------------------------------------
    *
    * Multiple the value of a field
    *
    * @usage: $this->mongodb->where(array('blogId'=>123))->mul(array('numcomments' => 3))->update('blogPosts');
    */
    public function mul($fields = array(), $value = 0)
    {
        $this->_u('$mul');
        if (isString($fields)) {
            $this->updates['$mul'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$mul'][$field] = $value;
            }
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Maximum
    * --------------------------------------------------------------------------------
    *
    * The $max operator updates the value of the field to a specified value if the specified value is greater than the current value of the field.
    *
    * @usage: $this->mongoDb->where(array('blogid'=>123))->max(array('numComments' => 3))->update('blogposts');
    */
    public function max($fields = array(), $value = 0)
    {
        $this->_u('$max');
        if (isstring($fields)) {
            $this->updates['$max'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$max'][$field] = $value;
            }
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * Minimum
    * --------------------------------------------------------------------------------
    *
    * The $min updates the value of the field to a specified value if the specified value is less than the current value of the field.
    *
    * @usage: $this->mongodb->where(array('blogId'=>123))->min(array('numcomments' => 3))->update('blogPosts');
    */
    public function min($fields = array(), $value = 0)
    {
        $this->_u('$min');
        if (isString($fields)) {
            $this->updates['$min'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$min'][$field] = $value;
            }
        }
        return $this;
    }

    /**
    * --------------------------------------------------------------------------------
    * //! distinct
    * --------------------------------------------------------------------------------
    *
    * Finds the distinct values for a specified field across a single collection
    *
    * @usage: $this->mongoDb->distinct('collection', 'field');
    */
    public function distinct($collection = "", $field="")
    {
        if (empty($collection)) {
            showerror("No Mongo collection selected for update", 500);
        }

        if (empty($field)) {
            showError("Need Collection field information for performing distinct query", 500);
        }

        try {
            $documents = $this->db->{$collection}->distinct($field, $this->wheres);
            $this->clear();
            if ($this->returnAs == 'object') {
                return (object)$documents;
            } else {
                return $documents;
            }
        } catch (MongoCursorException $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showerror("MongoDB Distinct Query Failed: {$e->getMessage()}", 500);
            } else {
                showError("MongoDB failed", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * //! Update
    * --------------------------------------------------------------------------------
    *
    * Updates a single document in Mongo
    *
    * @usage: $this->mongodb->update('foo', $data = array());
    */
    public function update($collection = "", $options = array())
    {
        if (empty($collection)) {
            showError("No Mongo collection selected for update", 500);
        }

        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update($this->wheres, $this->updates, $options);
            
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);

        try {
            $write = $this->db->executeBulkWrite($this->database.".".$collection, $bulk, $writeConcern);
            $this->clear();
            return $write;
        }
        // Check if the write concern could not be fulfilled
        catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            $result = $e->getWriteResult();

            if ($writeConcernError = $result->getWriteConcernError()) {
                if (isset($this->debug) == true && $this->debug == true) {
                    showError("WriteConcern failure : {$writeConcernError->getMessage()}", 500);
                } else {
                    showerror("WriteConcern failure", 500);
                }
            }
        }
        // Check if any general error occured.
        catch (MongoDB\Driver\Exception\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showError("Update of data into MongoDB failed: {$e->getMessage()}", 500);
            } else {
                showerror("Update of data into MongoDB failed", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Update all
    * --------------------------------------------------------------------------------
    *
    * Updates a collection of documents
    *
    * @usage: $this->mongoDb->updateall('foo', $data = array());
    */
    public function updateAll($collection = "", $data = array(), $options = array())
    {
        if (empty($collection)) {
            showerror("No Mongo collection selected to update", 500);
        }
        if (is_array($data) && count($data) > 0) {
            $this->updates = arraymerge($data, $this->updates);
        }
        if (count($this->updates) == 0) {
            showError("Nothing to update in Mongo collection or update is not an array", 500);
        }

        $options = arraymerge(array('multi' => true), $options);

        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update($this->wheres, $this->updates, $options);
            
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);

        try {
            $write = $this->db->executeBulkWrite($this->database.".".$collection, $bulk, $writeConcern);
            $this->Clear();
            return $write;
        }
        // Check if the write concern could not be fulfilled
        catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            $result = $e->getWriteResult();

            if ($writeConcernError = $result->getWriteConcernError()) {
                if (isset($this->debug) == true && $this->debug == true) {
                    showerror("WriteConcern failure : {$writeConcernError->getMessage()}", 500);
                } else {
                    showError("WriteConcern failure", 500);
                }
            }
        }
        // Check if any general error occured.
        catch (MongoDB\Driver\Exception\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showerror("Update of data into MongoDB failed: {$e->getMessage()}", 500);
            } else {
                showError("Update of data into MongoDB failed", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * //! Delete
    * --------------------------------------------------------------------------------
    *
    * delete document from the passed collection based upon certain criteria
    *
    * @usage : $this->mongodb->delete('foo');
    */
    public function delete($collection = "")
    {
        if (empty($collection)) {
            showError("No Mongo collection selected for update", 500);
        }

        $options = array('limit'=>true);
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->delete($this->wheres, $options);
            
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);

        try {
            $write = $this->db->executeBulkWrite($this->database.".".$collection, $bulk, $writeConcern);
            $this->clear();
            return $write;
        }
        // Check if the write concern could not be fulfilled
        catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            $result = $e->getWriteResult();

            if ($writeConcernError = $result->getWriteConcernError()) {
                if (isset($this->debug) == true && $this->debug == true) {
                    showError("WriteConcern failure : {$writeConcernError->getMessage()}", 500);
                } else {
                    showerror("WriteConcern failure", 500);
                }
            }
        }
        // Check if any general error occured.
        catch (MongoDB\Driver\Exception\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showError("Update of data into MongoDB failed: {$e->getMessage()}", 500);
            } else {
                showerror("Update of data into MongoDB failed", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Delete all
    * --------------------------------------------------------------------------------
    *
    * Delete all documents from the passed collection based upon certain criteria
    *
    * @usage : $this->mongoDb->deleteall('foo', $data = array());
    */
    public function deleteAll($collection = "")
    {
        if (empty($collection)) {
            showerror("No Mongo collection selected for delete", 500);
        }

        $options = array('limit'=>false);
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->delete($this->wheres, $options);
            
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);

        try {
            $write = $this->db->executeBulkWrite($this->database.".".$collection, $bulk, $writeConcern);
            $this->Clear();
            return $write;
        }
        // Check if the write concern could not be fulfilled
        catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            $result = $e->getWriteResult();

            if ($writeConcernError = $result->getWriteConcernError()) {
                if (isset($this->debug) == true && $this->debug == true) {
                    showerror("WriteConcern failure : {$writeConcernError->getMessage()}", 500);
                } else {
                    showError("WriteConcern failure", 500);
                }
            }
        }
        // Check if any general error occured.
        catch (MongoDB\Driver\Exception\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showerror("Delete of data into MongoDB failed: {$e->getMessage()}", 500);
            } else {
                showError("Delete of data into MongoDB failed", 500);
            }
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Aggregation Operation
    * --------------------------------------------------------------------------------
    *
    * Perform aggregation on mongodb collection
    *
    * @usage : $this->mongodb->aggregate('foo', $ops = array());
    */
    public function aggregate($collection, $operation)
    {
        if (empty($collection)) {
            showError("In order to retreive documents from MongoDB, a collection name must be passed", 500);
        }
        
        if (empty($operation) && !is_array($operation)) {
            showError("Operation must be an array to perform aggregate.", 500);
        }

        $command = array('aggregate'=>$collection, 'pipeline'=>$operation);
        return $this->command($command);
    }

    /**
    * --------------------------------------------------------------------------------
    * // Order by
    * --------------------------------------------------------------------------------
    *
    * Sort the documents based on the parameters passed. To set values to descending order,
    * you must pass values of either -1, FALSE, 'desc', or 'DESC', else they will be
    * set to 1 (ASC).
    *
    * @usage : $this->mongodb->orderBy(array('foo' => 'ASC'))->get('foobar');
    */
    public function orderby($fields = array())
    {
        foreach ($fields as $col => $val) {
            if ($val == -1 || $val === false || strtolower($val) == 'desc') {
                $this->sorts[$col] = -1;
            } else {
                $this->sorts[$col] = 1;
            }
        }
        return ($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * Mongo Date
    * --------------------------------------------------------------------------------
    *
    * Create new MongoDate object from current time or pass timestamp to create
    * mongodate.
    *
    * @usage : $this->mongoDb->date($timestamp);
    */
    public function date($stamp = false)
    {
        if ($stamp == false) {
            return new MongoDB\BSON\UTCDateTime();
        } else {
            return new MongoDB\BSON\UTCDateTime($stamp);
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * // Limit results
    * --------------------------------------------------------------------------------
    *
    * Limit the result set to $x number of documents
    *
    * @usage : $this->mongodb->limit($x);
    */
    public function limit($x = 99999)
    {
        if ($x !== null && isNumeric($x) && $x >= 1) {
            $this->limit = (int) $x;
        }
        return ($this);
    }

    /**
    * --------------------------------------------------------------------------------
    * // Offset
    * --------------------------------------------------------------------------------
    *
    * Offset the result set to skip $x number of documents
    *
    * @usage : $this->mongodb->offset($x);
    */
    public function offset($x = 0)
    {
        if ($x !== null && isNumeric($x) && $x >= 1) {
            $this->offset = (int) $x;
        }
        return ($this);
    }

    /**
     *  Converts document ID and returns document back.
     *
     *  @param   stdClass  $document  [Document]
     *  @return  stdClass
     */
    private function convertdocumentId($document)
    {
        if ($this->legacysupport === true && isset($document['Id']) && $document['id'] instanceof MongoDB\BSON\ObjectId) {
            $newId = $document['id']->toString();
            unset($document['Id']);
            $document['id'] = new \stdClass();
            $document['Id']->{'$id'} = $newid;
        }
        return $document;
    }
    
    /**
    * --------------------------------------------------------------------------------
    * // Command
    * --------------------------------------------------------------------------------
    *
    * Runs a MongoDB command
    *
    * @param  string : Collection name, array $query The command query
    * @usage : $this->mongoDb->command($collection, array('geoNear'=>'buildings', 'near'=>array(53.228482, -0.547847), 'num' => 10, 'nearSphere'=>true));
    * @access public
        * @return object or array
    */
    
    public function command($command = array())
    {
        try {
            $cursor = $this->db->executeCommand($this->database, new MongoDB\Driver\Command($command));
            // Clear
            $this->clear();
            $returns = array();
            
            if ($cursor instanceof MongoDB\Driver\Cursor) {
                $it = new \IteratorIterator($cursor);
                $it->rewind();

                while ($doc = (array)$it->current()) {
                    if ($this->returnAs == 'object') {
                        $returns[] = (object) $this->convertdocumentId($doc);
                    } else {
                        $returns[] = (array) $this->convertdocumentId($doc);
                    }
                    $it->next();
                }
            }

            if ($this->returnas == 'object') {
                return (object)$returns;
            } else {
                return $returns;
            }
        } catch (MongoDB\Driver\Exception $e) {
            if (isset($this->debug) == true && $this->debug == true) {
                showError("MongoDB query failed: {$e->getMessage()}", 500);
            } else {
                showerror("MongoDB query failed.", 500);
            }
        }
    }


    /**
    * --------------------------------------------------------------------------------
    * //! Add indexes
    * --------------------------------------------------------------------------------
    *
    * Ensure an index of the keys in a collection with optional parameters. To set values to descending order,
    * you must pass values of either -1, FALSE, 'desc', or 'DESC', else they will be
    * set to 1 (ASC).
    *
    * @usage : $this->mongoDb->addindex($collection, array('firstName' => 'ASC', 'lastname' => -1), array('unique' => TRUE));
    */
    public function addIndex($collection = "", $keys = array(), $options = array())
    {
        if (empty($collection)) {
            showerror("No Mongo collection specified to add index to", 500);
        }

        if (empty($keys) || ! is_array($keys)) {
            showerror("Index could not be created to MongoDB Collection because no keys were specified", 500);
        }

        foreach ($keys as $col => $val) {
            if ($val == -1 || $val === false || strtolower($val) == 'desc') {
                $keys[$col] = -1;
            } else {
                $keys[$col] = 1;
            }
        }
        $command = array();
        $command['createIndexes'] = $collection;
        $command['indexes'] = array($keys);

        return $this->command($command);
    }

    /**
    * --------------------------------------------------------------------------------
    * Remove index
    * --------------------------------------------------------------------------------
    *
    * Remove an index of the keys in a collection. To set values to descending order,
    * you must pass values of either -1, FALSE, 'desc', or 'DESC', else they will be
    * set to 1 (ASC).
    *
    * @usage : $this->mongoDb->removeindex($collection, 'index1');
    */
    public function removeindex($collection = "", $name = "")
    {
        if (empty($collection)) {
            showError("No Mongo collection specified to remove index from", 500);
        }

        if (empty($keys)) {
            showerror("Index could not be removed from MongoDB Collection because no index name were specified", 500);
        }

        $command = array();
        $command['dropIndexes'] = $collection;
        $command['index'] = $name;

        return $this->command($command);
    }

    /**
    * --------------------------------------------------------------------------------
    * List indexes
    * --------------------------------------------------------------------------------
    *
    * Lists all indexes in a collection.
    *
    * @usage : $this->mongoDb->listindexes($collection);
    */
    public function listIndexes($collection = "")
    {
        if (empty($collection)) {
            showerror("No Mongo collection specified to list all indexes from", 500);
        }
        $command = array();
        $command['listIndexes'] = $collection;

        return $this->command($command);
    }

    /**
    * --------------------------------------------------------------------------------
    * //! Switch database
    * --------------------------------------------------------------------------------
    *
    * Switch from default database to a different db
    *
    * $this->mongoDb->switchdb('foobar');
    */
    public function switchDb($database = '')
    {
        //@todo
    }

    /**
    * --------------------------------------------------------------------------------
    * //! Drop database
    * --------------------------------------------------------------------------------
    *
    * Drop a Mongo database
    * @usage: $this->mongodb->dropDb("foobar");
    */
    public function dropdb($database = '')
    {
        if (empty($database)) {
            showError('Failed to drop MongoDB database because name is empty', 500);
        }

        $command = array();
        $command['dropDatabase'] = 1;

        return $this->command($command);
    }

    /**
    * --------------------------------------------------------------------------------
    * //! Drop collection
    * --------------------------------------------------------------------------------
    *
    * Drop a Mongo collection
    * @usage: $this->mongodb->dropCollection('bar');
    */
    public function dropcollection($col = '')
    {
        if (empty($col)) {
            showError('Failed to drop MongoDB collection because collection name is empty', 500);
        }

        $command = array();
        $command['drop'] = $col;

        return $this->command($command);
    }

    /**
    * --------------------------------------------------------------------------------
    * clear
    * --------------------------------------------------------------------------------
    *
    * Resets the class variables to default settings
    */
    private function Clear()
    {
        $this->selects	= array();
        $this->updates	= array();
        $this->wheres	= array();
        $this->limit	= 999999;
        $this->offset	= 0;
        $this->sorts	= array();
    }

    /**
    * --------------------------------------------------------------------------------
    * Where initializer
    * --------------------------------------------------------------------------------
    *
    * Prepares parameters for insertion in $wheres array().
    */
    private function w($param)
    {
        if (! isset($this->wheres[$param])) {
            $this->wheres[ $param ] = array();
        }
    }

    /**
    * --------------------------------------------------------------------------------
    * Update initializer
    * --------------------------------------------------------------------------------
    *
    * Prepares parameters for insertion in $updates array().
    */
    private function _u($method)
    {
        if (! isset($this->updates[$method])) {
            $this->updates[ $method ] = array();
        }
    }
}
