<?php
namespace Bumip\Apps\Admin;

class AdminController extends \Bumip\Core\SubController
{
    private $parent;

    public function __construct($config = null, $parent = null, $options = [])
    {
        if ($parent) {
            $this->parent = $parent;
        }
        // $this->db = &$c->db;
        $connection = \Bumip\Core\Database\Connection::getConnection(DATABASE_DRIVER);
        $this->db =  \Bumip\Core\Database\Connection::getDatabase(DATABASE_DRIVER);
        $this->url = &$c->url;
        $this->user = &$this->parent->user;
        // foreach ($options as $k => $v) {
        //     $this->options[$k] = $v;
        // }
        parent::__construct($config);
        /**
         * 3 becomes 1, helps with params.
         */
        $this->url->setOffset(3);
    }
    public function hello()
    {
    }
    public function example($args = '1:id/2:table_id')
    {
        list($id, $table_id) = array_values($args);
        print $id;
    }
    private function job()
    {
        echo $this->url->pairGetValue("job");
    }
    private function getPackage($package)
    {
        $path = "app/apps/" . $package . "/app/";
        if (is_file($path . "package.json")) {
            $package = json_decode(file_get_contents($path . "package.json"), true);
            $package["path"] = $path;
            return $package;
        }
        return false;
    }
    public function update_ui($args = "1:package/2:uilib")
    {
        $package = $this->getPackage($args["package"]);
        if (!$package) {
            echo("Package {$args["package"]} does not exists.");
            return false;
        }
        $uilib = \ucfirst($args["uilib"]);
        $ui = [];
        foreach ($package["UI"][$uilib] as $k => $v) {
            $ui[$k] = $v;
            $ui[$k]["file"] =  $package["path"] . $ui[$k]["file"];
        }
        echo json_encode($ui, JSON_PRETTY_PRINT);
    }
    public function getfile()
    {
    }
    public function install($args = "1:package")
    {
        $package =  $args["package"];
        $package = $this->getPackage($package);
        if (!$package) {
            echo("Package {$args["package"]} does not exists.");
            return false;
        }
    }
}
