<?php
namespace Bumip\Apps\Admin;

/**
 * Most of the stuff on the constructor will  be moved in the Subcontroller Constructor.
 */
class AdminController extends \Bumip\Core\SubController
{
    public function __construct(\Bumip\Core\DataHolder $config = null, $parent = null, array $options = [])
    {
        parent::__construct($config, $parent, $options);
        $this->protectMethod("rebuildEnabledApps");
    }
    public function dbtest()
    {
        if (DATABASE_DRIVER == "PDO") {
            try {
                $db = $this->connection;
                $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);//Error Handling
                $sql ="CREATE TABLE IF NOT EXISTS `people` (
                    PRIMARY KEY `id` AUTO_INCREMENT unsigned int(11) NOT NULL ,
                    `name` varchar(255) DEFAULT '',
                    `age` int(11) DEFAULT NULL,
                  ) ;
                  " ;
                $db->exec($sql);
            } catch (\PDOException $e) {
                //echo $e->getMessage();//Remove or change message in production code
            }
            print("Inserting Values.\n");
            $people = $this->db->insertInto("people")->values(['id' => null, 'name' => 'Ray', 'age' => 25])->execute();
            
            // $people = $this->db->insertInto("people")->values(['name' => 'John',  'age' => 30])->execute();
            
            // $people = $this->db->insertInto("people")->values(['name' => 'Ali', 'age' => 22])->execute();
        }
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
    /**
     * get package json as array
     *
     * @param string $package (package name)
     * @return array
     */
    private function getPackage(string $package):array
    {
        $path = "app/apps/" . $package . "/app/";
        if (is_file($path . "package.json")) {
            $package = json_decode(file_get_contents($path . "package.json"), true);
            $package["fullpath"] = $path;
            $package["path"] = $package["name"] . "/app/";
            return $package;
        }
        return false;
    }
    public function rebuildEnabledApps()
    {
        $delimiter = ['//@begin apps.json', '//@end apps.json'];
        $apps = file_get_contents('app/apps.json');
        $enabledApps = file_get_contents('app/enabledApps_default.php');
        $apps = json_decode($apps, true);
        $newStr = "\n". '$confApps = ' . var_export($apps, true) . ';' . "\n";
        $enabledApps = \Bumip\Helpers\StringHelper::replaceDelimited($enabledApps, $newStr, $delimiter);
        \file_put_contents('app/enabledApps.php', $enabledApps);
        return true;
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
            $ui[$k]["file"] =  $package["path"] . "UI/" .$uilib . "/" . $ui[$k]["file"];
            $ui[$k]["downloadUrl"] = ROOT_EXT . $this->config->get("parentMethod") . "/getfile?file=" . $ui[$k]["file"] . "&package=" . $package["name"];
        }
        echo json_encode($ui, JSON_PRETTY_PRINT);
    }
    
    public function getfile()
    {
        $file = $_REQUEST["file"] ?? false;
        $package = $_REQUEST["package"] ?? false;
        if (!$file || !$package) {
            return false;
        } else {
            $package = $this->getPackage($package);
            $file = "app/apps/" . $file;
            $tp = file_get_contents($file);
            $data = $package["customData"] ?? [];
            $defaultData = [
                "apiRoot" => ROOT_EXT,
                "apiEntryPoint" => ROOT_EXT . $this->config->get("parentMethod"),
                "appName" => APP_NAME
            ];
            $data = array_merge($defaultData, $data);
            echo \Bumip\Helpers\StringHelper::processTemplate($tp, $data);
        }
    }
    public function install($args = "1:package")
    {
        $package =  $args["package"];
        $package = $this->getPackage($package);
        if (!$package) {
            echo("Package {$args["package"]} does not exists.");
            return false;
        }
        echo "node app-maestro.js " . ROOT_EXT . $this->config->get("parentMethod") . "/update_ui/" . $package["name"] . "/vuetify";
    }
}
