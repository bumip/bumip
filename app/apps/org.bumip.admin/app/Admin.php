<?php
namespace Bumip\Apps\Admin;

class AdminController extends \Bumip\Core\SubController
{
    private $parent;

    public function __construct(\Bumip\Core\DataHolder $config = null, $parent = null, array $options = [])
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
        echo "hello";
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
    public function processTemplate(string $content, array $data, array $delimiter = ['[{', '}]'])
    {
        foreach ($data as $k => $v) {
            if (!is_array($v) && !is_object($v)) {
                $content = str_replace($delimiter[0] . $k . $delimiter[1], $v, $content);
                $content = str_replace($delimiter[0] . " {$k} " . $delimiter[1], $v, $content);
            } elseif (is_object($v)) {
                if (get_class($v) == 'MongoDB\BSON\ObjectId') {
                    $content = str_replace("[[{$k}]]", (string) $v, $content);
                }
            }
        }
        return $content;
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
            echo $this->processTemplate($tp, $data);
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
        echo "node add-app.js " . ROOT_EXT . $this->config->get("parentMethod") . "/update_ui/" . $package["name"] . "/vuetify";
    }
}
