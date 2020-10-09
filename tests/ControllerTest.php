<?php

use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    public function setUp():void
    {
        // define("APP_NAME", "YOURAPP");
        // $_SERVER['REQUEST_URI'] = "hello";
        // $_SERVER['HTTP_HOST'] = "hello";
        // $project_dir = explode('/', dirname($_SERVER['SCRIPT_FILENAME']));
        // $project_dir = array_pop($project_dir);
        // require_once "bumip/libraries/core/bootstrap_functions.php";
        // /** User configuration */
        // require_once "configuration/constants.php";
        // require_once "configuration/database.php";
        // /**
        //  * From here you should edit only if you have problem or
        //  * you need a custom configuration and you should add the following file to your .gitignore
        //  */
        // require_once "bumip/configuration/constants.php";
        // require_once 'vendor/autoload.php';
    }
    /** @test */
    public function controller_class_is_correct()
    {
        $config = new \Bumip\Core\DataHolder;
        $config->data("requestObject", new \Bumip\Core\Request($config));
        $app = new \Bumip\Core\Controller($config);
        $this->assertEquals(get_class($app), "Bumip\Core\Controller");
    }
}
