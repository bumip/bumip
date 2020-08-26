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
                self::$connection = new \PDO(PDOSTR, DBUSER, DBPASS);
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
                self::$db = new SqlDb('mysql', function ($query, $queryString, $queryParameters) use ($connection) {
                    $statement = $connection->prepare($queryString);
                    $statement->execute($queryParameters);

                    // when the query is fetchable return all results and let hydrahon do the rest
                    // (there's no results to be fetched for an update-query for example)
                    if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
                        return $statement->fetchAll(\PDO::FETCH_ASSOC);
                    }
                });
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
