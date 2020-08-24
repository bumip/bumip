<?php
namespace Bumip\Core;

class Controller
{
    public $url;
    public $db = false;
    public $dbautoconnect = true;
    public $useimgcache = true;
    public $model;
    public $lang;
    public $controller;
    public $lib;
    public $methodCalledByUrl = false;
    

    public function __construct()
    {
        $this->url = new Url();
        $module = $this->url->index[1];
        if (OMIT_MAIN) {
            $module = "main";
        }
        /**
         * The action is the method called using the URL.
         * In a Symfony or CodeIgniter Style controller /hello needs a controller class called Hello.
         * If OMIT_MAIN == True /hello will just try to call the method hello(). If you need a specific controller for Hello,
         * you can Load a new Controller Class inside the hello method
         */
        if (isset($this->url->index[2]) && $this->url->index[2] != "" && !OMIT_MAIN) {
            $this->action = $this->url->index[2];
        } elseif (OMIT_MAIN) {
            $this->action = $this->url->index[1];
        } else {
            $this->action = DEFAULT_ACTION;
        }
        /**
         * This part will be removed once we fix autoloader.
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
        if (USEMYSQL and $this->dbautoconnect) {
            $this->mysqlconnect();
        }
        if (defined('DB_CANNOT_CONNECT') and method_exists($this, 'db_error')) {
            $this->db_error();
            exit();
        }
    }
        
    public function callMethodByUrl()
    {
        $action = $this->action;
        if (method_exists($this, $action)) {
            $this->$action();
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
     * Used to load views.
     *
     * @param [type] $file
     * @param boolean $data
     * @return void
     */
    public function load($file, $data = false)
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
        require MVCDIR . 'view/' . $file;
    }

    public function resized()
    {
        $width = false;
        $height = false;
        $cache = false;
        if ($this->useimgcache) {
            $cache = $this->url->index(4);
        }
        $cachetime = 3600;
        $src = UPLOADFOLDER . $this->url->index(4);
        if (!empty($_GET['path'])) {
            $src = UPLOADFOLDER . $_GET['path'];
            if ($this->useimgcache) {
                $cache = UPLOADFOLDER . $_GET['path'];
            }
        }
        if ($this->url->index(2) != "x") {
            $width = $this->url->index(2);
        }
        if ($this->url->index(3) != "x") {
            $height = $this->url->index(3);
        }
        $op = new Resizer($src, $width, $height, $cache, $cachetime);
    }
}
