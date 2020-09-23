<?php

use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    public function setUp():void
    {
        if (!defined("APP_NAME")) {
            define("APP_NAME", "YOURAPP");
        }
        
        $_SERVER['REQUEST_URI'] = "hello";
        $_SERVER['HTTP_HOST'] = "hello";
        $project_dir = explode('/', dirname($_SERVER['SCRIPT_FILENAME']));
        $project_dir = array_pop($project_dir);
        require_once "bumip/libraries/core/bootstrap_functions.php";
        /** User configuration */
        require_once "configuration/constants.php";
        require_once "configuration/database.php";
        /**
         * From here you should edit only if you have problem or
         * you need a custom configuration and you should add the following file to your .gitignore
         */
        require_once "bumip/configuration/constants.php";
        require_once 'vendor/autoload.php';
        $this->connection = \Bumip\Core\Database\Connection::getConnection(DATABASE_DRIVER);
        $this->db =  \Bumip\Core\Database\Connection::getDatabase(DATABASE_DRIVER);
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);//Error Handling
    }
    /** @test */
    public function isDbWorking()
    {
        $this->assertTrue(isset($this->db));
    }
    /** @test */
    public function isTableCreated()
    {
        $success = true;
        $sql = "CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name varchar(100) NOT NULL,
            brand varchar(100) NOT NULL,
            price decimal(10, 2) NOT NULL,
            user_id INTEGER UNSIGNED NOT NULL
          )";
        try {
            $this->connection->exec($sql);
        } catch (PDOException $e) {
            echo $e->getMessage();
            $success = false;
        }
        $this->assertTrue($success);
    }
    /** @test */
    public function insertSomething()
    {
        try {
            $this->db->insertInto('items', ["name" => "Backpack", "brand" => "Nintendo", "price" => 8.5, "user_id" => 2])->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
            $this->assertTrue(false);
            return false;
        }
        $this->assertTrue(true);
        return true;
    }
    /** @test */
    public function insertSomethingUsingMagic()
    {
        try {
            $this->db->items->insertOne(["name" => "Phone Cover", "brand" => "D-Brand", "price" => 8.5, "user_id" => 2])->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
            $this->assertTrue(false);
            return false;
        }
        $this->assertTrue(true);
        return true;
    }
    /** @test */
    public function insertSomethingUsingMagicWithLessFields()
    {
        /** This test should fail */
        try {
            $this->db->items->insertOne(["name" => "Phone Cover", "price" => 8.5, "user_id" => 2])->execute();
        } catch (PDOException $e) {
            //echo $e->getMessage();
            $this->assertTrue(true);
            return false;
        }
        $this->assertTrue(false);
        return true;
    }
    /** @test */
    public function getAll()
    {
        try {
            $items = $this->db->from('items');
            foreach ($items as $i) {
                $i["name"];
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            $this->assertTrue(false);
            return false;
        }
        $this->assertTrue(true);
        return true;
    }
    /** @test */
    public function getAllUsingMagic()
    {
        try {
            $items = $this->db->items->find();
            foreach ($items as $i) {
                $i["name"];
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            $this->assertTrue(false);
            return false;
        }
        $this->assertTrue(true);
        return true;
    }
    /** @test */
    public function getSome()
    {
        try {
            $items = $this->db->from('items')->where("brand = 'Nintendo'");
            foreach ($items as $i) {
                $i["name"];
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            $this->assertTrue(false);
            return false;
        }
        $this->assertTrue(true);
        return true;
    }
    /** @test */
    public function getSomewithMagic()
    {
        try {
            $items = $this->db->items->find(["brand" => 'D-Brand', "user_id" => 2]);
            foreach ($items as $i) {
                $i["name"];
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            $this->assertTrue(false);
            return false;
        }
        $this->assertTrue(true);
        return true;
    }
}
