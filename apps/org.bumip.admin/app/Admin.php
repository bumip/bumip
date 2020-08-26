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
    }
    public function hello()
    {
        echo "helo";
    }
    public function example($args = '2:id/3:table_id')
    {
        list($id, $table_id) = array_values($args);
        print $id;
    }
    private function job()
    {
        echo $this->url->pairGetValue("job");
    }
}
