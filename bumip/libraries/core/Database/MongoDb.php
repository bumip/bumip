<?php
namespace \Bumip\Core\Database;
class MongoDb extends \MongoDB\Client
{
    public function __get($databaseName)
    {
        return $this->selectDatabase($databaseName);
    }
}
