<?php
namespace Tomokit;

class ReservationManagerController extends \Bumip\MVC\SubController
{
    /**
     * @todo FIX BOOTSTRAPPING SUBCONTROLLER
     *
     * @param [BumipController] $c
     * @param array $options
     */
    public function __construct(&$c, $options = [])
    {
        $this->parent = &$c;
        // $this->db = &$c->db;
        $connection = \Bumip\Core\Database\Connection::getConnection(DATABASE_DRIVER);
        $this->db =  \Bumip\Core\Database\Connection::getDatabase($connection);
        $this->url = &$c->url;
        $this->user = &$this->parent->user;
        foreach ($options as $k => $v) {
            $this->options[$k] = $v;
        }
        parent::__construct();
        $this->url->setOffset(3);
        if (!$this->user->logged) {
            $this->url->redirect("user/login");
        }
    }
    public function reservations()
    {
    }
    public function listings()
    {
        $this->properties();
    }
    public function properties()
    {
    }
    public function checkins()
    {
    }
    public function guides()
    {
    }
}
