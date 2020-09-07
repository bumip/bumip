<?php

use PHPUnit\Framework\TestCase;

final class AdminTest extends TestCase
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
        require_once "app/apps/org.bumip.admin/app/Admin.php";
    }
    /** @test */
    public function is_there_package_json()
    {
        $this->assertTrue(is_file('app/package.json'));
    }
    /** @test */
    public function can_rebuild_apps_json()
    {
        $config = new \Bumip\Core\DataHolder;
        $config->data("urlObject", new \Bumip\Core\Url($config));
        $app = new \Bumip\Apps\Admin\AdminController($config);
        $this->assertTrue($app->rebuildEnabledApps());
    }
    /** @test */
    public function can_replace_delimited()
    {
        $config = new \Bumip\Core\DataHolder;
        $config->data("urlObject", new \Bumip\Core\Url($config));
        $app = new \Bumip\Apps\Admin\AdminController($config);
        $str = "Ciao //@begin edit //@end miao";
        $newstr = \Bumip\Helpers\StringHelper::replaceDelimited($str, " edited ");
        $this->assertTrue(is_string($newstr));
        $this->assertEquals($newstr, "Ciao //@begin edited //@end miao");
    }
}
