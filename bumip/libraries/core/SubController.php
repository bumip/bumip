<?php
namespace Bumip\Core;

class SubController extends \Bumip\Core\Controller
{
    public $parent;
    public function __construct(\Bumip\Core\DataHolder $config = null, $parent = null, array $options = [])
    {
        if ($parent) {
            $this->parent = $parent;
        } else {
            $this->parent = $this;
        }
        $this->connection = \Bumip\Core\Database\Connection::getConnection(DATABASE_DRIVER);
        $this->db =  \Bumip\Core\Database\Connection::getDatabase(DATABASE_DRIVER);
        if (!isset($this->parent->request)) {
            $this->request = new Request($config);
        } else {
            $this->request = &$this->parent->request;
        }
        $this->user = &$this->parent->user;
        foreach ($options as $k => $v) {
            $this->options[$k] = $v;
        }
        $offset = 2;
        $this->request->setOffset($offset + 1);
        parent::__construct($config, $offset);
    }
}
