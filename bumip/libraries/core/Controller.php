<?php
namespace Bumip\Core;

class Controller
{
    protected $config;
    public $request;
    public $urlOffset = 1;
    private $protectedMethods = ['load', 'callmethodbyurl'];
    public $db = false;
    protected $connection;
    public $dbautoconnect = true;
    public $useimgcache = true;
    public $model;
    public $lang;
    public $controller;
    public $lib;
    public $methodCalledByUrl = false;
    

    public function __construct($config = null, $urlOffset = null)
    {
        $this->config = $config;
        if ($config->has("urlObject")) {
            $config->get("urlObject");
        } else {
            $this->request = new Request($config);
        }
        
        $module = $this->request->index[1] ?? "main";
        if (OMIT_MAIN) {
            $module = "main";
        }
        /**
         * The action is the method called using the URL.
         * In a Symfony or CodeIgniter Style controller /hello needs a controller class called Hello.
         * If OMIT_MAIN == True /hello will just try to call the method hello(). If you need a specific controller for Hello,
         * you can Load a new Controller Class inside the hello method
         */
        if (isset($this->request->index[2]) && $this->request->index[2] != "" && !OMIT_MAIN) {
            $this->action = $this->request->index[2];
        } elseif (OMIT_MAIN) {
            $this->action = $this->request->index[1] ?? DEFAULT_ACTION;
        } else {
            $this->action = DEFAULT_ACTION;
        }
        if ($urlOffset) {
            if (!empty($this->request->index[$urlOffset])) {
                $this->action = $this->request->index[$urlOffset];
            } else {
                $this->action = DEFAULT_ACTION;
            }
            $this->requestOffset = $urlOffset;
        }
        /**
         * This part will be removed once we fix autoloading.
         */
        $this->model = new FileLoader(MVCDIR . 'model/');
        $this->controller = new FileLoader(MVCDIR . 'controller/');
        $this->lib = new FileLoader(LIBDIR . 'core/');
        $this->fw = new FileLoader(LIBDIR);
        $this->plugin = new FileLoader(LIBDIR . 'plugins/');
        /**
         * @todo fix this
         * This will be fixed when I copy the query builder over.
         */
        $connection = \Bumip\Core\Database\Connection::getConnection(DATABASE_DRIVER);
        $this->db =  \Bumip\Core\Database\Connection::getDatabase(DATABASE_DRIVER);
        $this->config->data("db", $this->db);
        // if (USEMYSQL and $this->dbautoconnect) {
        //     $this->mysqlconnect();
        // }
        // if (defined('DB_CANNOT_CONNECT') and method_exists($this, 'db_error')) {
        //     $this->db_error();
        //     exit();
        // }
    }
    public function protectMethod($metodName)
    {
        if (!in_array($metodName, $this->protectedMethods)) {
            $this->protectedMethods[] = $metodName;
        }
    }
    public function callMethodByUrl()
    {
        $action = $this->action;
        if (in_array(strtolower($action), $this->protectedMethods)) {
            echo "To enable the controller method '$action' you need to exclude it from the protectedMethods list";
            return false;
        }
        $apps = $this->config->data("apps");
        if (!empty($apps["controllerMap"][$action])) {
            $class = $apps["controllerMap"][$action];
            $this->config->data("parentMethod", $action);
            $app = new $class($this->config, $this, $this->options ?? []);
            $app->callMethodByUrl();
            return true;
        }
        if (method_exists($this, $action)) {
            $r = new \ReflectionMethod(get_class($this), $action);
            $params = $r->getParameters();
            if (count($params)) {
                $args = [];
                foreach ($params as $param) {
                    if ($param->getName() == "args") {
                        $args = $this->request->toArgs($param->getDefaultValue());
                    }
                }
                if (count($args)) {
                    $this->$action($args);
                } else {
                    $this->$action();
                }
            } else {
                $this->$action();
            }
        } else {
            $this->get_404();
        }
        $this->methodCalledByUrl = true;
    }
    /**
     * You should extend this in your controller to display your page.
     *
     * @return void
     */
    public function get_404()
    {
        header("HTTP/1.0 404 Not Found");
    }
    public function index()
    {
        echo "please override this method";
    }
    /**
     * @todo replace with Query builder. Move it to the MainController.
     *
     * @return void
     */
    public function mysqlconnect()
    {
        if (!$this->db) {
            require_once "db.php";
            $this->db = new db();
        }
        $this->db->connect(DBHOST, DBUSER, DBPASS);
        $this->db->selectdb(DBNAME);
    }
    /**
     * @method load()
     *
     * Used to load views or other mvc components while flattening the $data array.
     *
     * @param [type] $file
     * @param boolean $data
     * @return void
     */
    public function load($file, array $data = null, $dir = "view")
    {
        if (isset($GLOBALS['passed'])) {
            $passed = $GLOBALS['passed'];
        }
        if (!empty($passed)) {
            foreach ($passed as $k => $v) {
                $$k = $v;
            }
        }
        if ($data) {
            foreach ($data as $k => $v) {
                $$k = $v;
            }
        }
        if (get_class($this) == "Controller") {
            require MVCDIR . $dir . '/' . $file;
        } else {
            require EXTEND_DIR . 'mvc/' . $dir . '/' . $file;
        }
    }
    public function __call($name, $arguments)
    {
        echo "To enable the controller method '$name' you need to change its visibility to public or protected";
    }
    public function resized()
    {
        $width = false;
        $height = false;
        $cache = false;
        if ($this->useimgcache) {
            $cache = $this->request->index(4);
        }
        $cachetime = 3600;
        $src = UPLOADFOLDER . $this->request->index(4);
        if (!empty($_GET['path'])) {
            $src = UPLOADFOLDER . $_GET['path'];
            if ($this->useimgcache) {
                $cache = UPLOADFOLDER . $_GET['path'];
            }
        }
        if ($this->request->index(2) != "x") {
            $width = $this->request->index(2);
        }
        if ($this->request->index(3) != "x") {
            $height = $this->request->index(3);
        }
        $op = new Resizer($src, $width, $height, $cache, $cachetime);
    }
}
