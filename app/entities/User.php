<?php
namespace Bumip\Entities;

class User extends \Bumip\Core\DataHolder
{
    private $usernameField = "email";
    private $table = "users";
    private $sessionLength = (86400 * 7);
    private $isLogged = false;
    private $logged = false;
    private $db;
    
    public function __construct($overrides = [], $config = null, $data = [])
    {
        if (!empty($overrides)) {
            foreach ($overrides as $k => $v) {
                $this->$k = $v;
            }
        }
        parent::__construct($data);
    }
    public function restoreSession()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $s = false;
        if (isset($_SESSION['username'])) {
            $s = $_SESSION;
        } elseif (isset($_COOKIE['username'])) {
            $s = $_COOKIE;
        }
        // GO ON...
        if ($s) {
            $this->data("username", $s['username']);
            $this->data("password", $s['password']);
        }
    }
    public function setupSession()
    {
        setcookie("username", $this->data("username"), time() + $this->sessionLength);
        setcookie("password", $this->data("password"), time() + $this->sessionLength);
        $_SESSION['username'] = $this->data("username");
        $_SESSION['password'] = $this->data("password");
    }
    public function login()
    {
        $q = array($this->username_field => $this->data("username"), 'password' => $this->data("password"));
        $res = $this->db->{$this->tab}->findOne($q);
        if ($res) {
            $this->setup_session();
            $this->setup($res);
            return $this->isLogged = true;
        } else {
            return $this->isLogged = false;
        }
    }
    public function isLogged()
    {
        return $this->isLogged;
    }
    public function logout()
    {
        $this->removeData();
        unset($_SESSION['username'], $_SESSION['password']);
        setcookie("username", $this->username, time() - (86400 * 7));
        setcookie("password", $this->password, time() - (86400 * 7));
    }
}
