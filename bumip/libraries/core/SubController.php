<?php
namespace Bumip\Core;

class SubController extends \Bumip\Core\Controller
{
    public function __construct($config = null)
    {
        parent::__construct($config, 2);
    }
    // public function callMethodByUrl()
    // {
    //     $action = $this->url->index(2);
    //     if (!$action) {
    //         $action = 'index';
    //     }
    //     if (!empty($this->restricted_actions) and isset($this->restricted_actions[$action])) {
    //         if (!$this->{$this->restricted_actions[$action]['bounce_test']}($action)) {
    //             $this->orginal_action = $action;
    //             $action = $this->restricted_actions[$action]['bounce_to'];
    //         }
    //     }
    //     if (method_exists($this, $action)) {
    //         $this->$action();
    //     } else {
    //         if (method_exists($this, 'index')) {
    //             $this->index();
    //         } else {
    //             $this->get_404();
    //         }
    //     }
    //     $this->methodCalledByUrl = true;
    // }
}
