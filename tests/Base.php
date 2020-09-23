<?php
use PHPUnit\Framework\TestCase;

final class JsonSchemaSqlTest extends TestCase
{
    public $c;
    public $className = "Bumip\JsonSchema\JsonSchemaSql";
    public function setUp():void
    {
        $pdo = new PDO("sqlite:tests/database/dbtest.db");
        $this->c = new $this->className($pdo);
    }
    /** @test */
    public function testClassisCorrect()
    {
        $this->assertEquals(get_class($this->c), $this->className);
    }
}
