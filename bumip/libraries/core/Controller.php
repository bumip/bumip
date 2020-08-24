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
        $this->url = new url();
        if (OMIT_MAIN) {
            $module = "main";
        } else {
            $module = $this->url->index[1];
        }
        //print_r($this->url->index);
        if (isset($this->url->index[2]) && $this->url->index[2] != "" && !OMIT_MAIN) {
            $this->action = $this->url->index[2];
        } elseif (OMIT_MAIN) {
            $this->action = $this->url->index[1];
        } else {
            $this->action = DEFAULT_ACTION;
        }

        $this->model = new F_loader(SPECDIR . 'model/');
        $this->controller = new F_loader(SPECDIR . 'controller/');
        $this->lib = new F_loader(LIBDIR . 'core/');
        $this->fw = new F_loader(LIBDIR);
        $this->plugin = new F_loader(LIBDIR . 'plugins/');
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

    public function get_404()
    {
        header("HTTP/1.0 404 Not Found");
    }

    public function mysqlconnect()
    {
        if (!$this->db) {
            require_once "db.php";
            $this->db = new db();
        }
        $this->db->connect(DBHOST, DBUSER, DBPASS);
        $this->db->selectdb(DBNAME);
    }

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
        require SPECDIR . 'view/' . $file;
    }

    public function resized()
    {
        $this->lib->load("resizer.php");
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
        $op = new resizer($src, $width, $height, $cache, $cachetime);
    }
}
