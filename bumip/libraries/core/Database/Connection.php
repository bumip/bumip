<?php
namespace Bumip\Core\Database;

class Connection
{
    public static $connection;
    public static $db;
    /**
     * Return the database connection/driver
     *
     * @param string $type [PDO, MongoDb]
     * @param integer $group
     * @return void
     */
    public static function getConnection($type = "PDO", $group = 1)
    {
        if (!self::$connection) { // If no instance then make one
            if ($type == "MongoDb") {
                self::$connection = new MongoDb(mdbtstr);
            } elseif ($type == "PDO") {
                try {
                    self::$connection = new \PDO(PDOSTR, DBUSER, DBPASS);
                } catch (\Throwable $th) {
                }
            } else {
                echo "'$type' is unsupported";
            }
        }
        return self::$connection;
    }
    public static function getDatabase($type = "PDO")
    {
        if (!self::$db) {
            self::getConnection($type);
            if ($type == "MongoDb") {
                self::$db = self::$connection->selectDatabase(DBNAME);
            } elseif ($type == "PDO") {
                $connection = self::$connection;
                //$dbtype = \explode(":", PDOSTR)[0];
                self::$db = new SqlDb(self::$connection);
            //self::$db = new SqlDb(self::$connection);
            } else {
                echo "'$type' is unsupported";
            }
        }
        return self::$db;
    }
    public static function setConnection(& $conn)
    {
        if (!self::$connection) { // If no instance then make one
            self::$connection = & $conn;
        }
        return self::$connection;
    }
}
