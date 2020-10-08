<?php

use PHPUnit\Framework\TestCase;

final class EntityEditorTest extends TestCase
{
    public $c;
    public $className = "Bumip\Core\EntityManager";
    public function setUp(): void
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
        // $this->connection = \Bumip\Core\Database\Connection::getConnection(DATABASE_DRIVER);
        // $this->db =  \Bumip\Core\Database\Connection::getDatabase(DATABASE_DRIVER);
        // $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);//Error Handling
        //$pdo = new PDO("sqlite:tests/database/dbtest.db");
        $conf = new \Bumip\Core\DataHolder();
        $this->c = new $this->className($conf);
        $this->c->setDirectory("tests/entities/");
        require_once 'app/apps/org.bumip.entity-editor/app/EntityEditor.php';
        $this->e = new \Bumip\Apps\Admin\EntityEditor($this->c, ['get' => 'list']);
    }
    /** @test */
    public function testClassisCorrect()
    {
        $this->assertEquals(get_class($this->c), $this->className);
    }
    public function testListEntities()
    {
        $entities = $this->c->list("tests/entities/");
        $this->assertIsArray($entities);
    }
    public function testListEntitiesByUrl()
    {
        $conf = new \Bumip\Core\DataHolder();
        $request = new \Bumip\Core\Request($conf);
        $request->makeIndexes('');
        $entities = $this->e->index($request);
        $this->assertIsArray($entities);
    }
    public function testListEntitiesOnEmpty()
    {
        $entities = $this->c->list("tests/emptydir");
        $this->assertFalse($entities);
    }
    public function testListEntitiesOnEmptyByUrl()
    {
        $this->c->setDirectory("tests/emptydir");
        $conf = new \Bumip\Core\DataHolder();
        $request = new \Bumip\Core\Request($conf);
        $request->makeIndexes('');
        $entities = $this->e->index($request);
        $this->assertFalse($entities);
    }
    public function testGetEntityNotExists()
    {
        $name = "NotEXISTS";
        $entities = $this->c->loadEntity($name);
        $this->assertFalse($entities);
    }
    public function testGetEntityDir()
    {
        $name = "Order";
        $entities = $this->c->loadEntity($name);
        $this->assertIsObject($entities);
    }
    public function testGetEntityDirByUrl()
    {
        $name = "Order";
        $this->c->setDirectory("tests/entities");
        $conf = new \Bumip\Core\DataHolder();
        $request = new \Bumip\Core\Request($conf);
        $request->makeIndexes('entities/' . $name);
        $entities = $this->e->index($request);
        $this->assertIsObject($entities);
    }
    public function testGetEntityFile()
    {
        $name = "User";
        $entities = $this->c->loadEntity($name);
        $this->assertIsObject($entities);
    }
    public function testSaveEntityNew()
    {
        $name = "tester" . rand(0, 500);
        $entity = ['a' => 'n'];
        $result = $this->c->saveEntity($name, $entity);
        $this->assertIsObject($result);
    }
    public function testSaveEntityExisting()
    {
        $name = "tester";
        $entity = ['a' => 'n'];
        $result = $this->c->saveEntity($name, $entity);
        $this->assertIsObject($result);
    }
    public function testSaveEntityExistingSingleFile()
    {
        $name = "User";
        $entity = ['a' => 'n'];
        $result = $this->c->saveEntity($name, $entity);
        $this->assertIsObject($result);
    }
    public function testRemoveTestDirectories()
    {
        foreach (scandir('tests/entities') as $dir) {
            if (strpos($dir, 'tester') === 0) {
                array_map('unlink', glob('tests/entities/' . $dir ."/*.*"));
                rmdir('tests/entities/' . $dir);
            }
        }
        $this->assertFalse(is_dir('tests/entities/tester'));
    }
}
