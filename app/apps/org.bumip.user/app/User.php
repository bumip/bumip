<?php
namespace Bumip\Apps\User;

/**
 * @todo Find a way to get controller name in class.
 */
class UserController extends \Bumip\Core\SubController
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

    public function example($args = '1:id/2:table_id')
    {
        list($id, $table_id) = array_values($args);
        print $id;
    }
    public function index()
    {
        if (!$this->user->isLogged()) {
            $url = $this->config->get("parentMethod") . "/signup";
            $this->url->redirect($url);
        } else {
            //Dashboard
        }
    }
    public function signup()
    {
        $d = $_REQUEST;
        $data["error"] = false;
        $data["exists"] = false;
        //list($email, $password) = [$d["email"], $d["password"]];
        if (filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $query = ["email" => strtolower($d['email'])];
            if ($u = $this->db->users->findOne($query)) {
                $data["exists"] = true;
            }
        } else {
            $data["error"] = true;
        }
        if (!$data["error"] && !empty($d['password'])) {
        }
        echo json_encode($data);
    }
}
