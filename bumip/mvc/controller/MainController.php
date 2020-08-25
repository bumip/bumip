<?php
namespace Bumip\MVC\Controller;

class MainController extends \Bumip\Core\Controller
{
    public $user;
    public $config;
    public function __construct($config)
    {
        $this->config = $config;
        if ($globalApps = $this->config->get("globalApps")) {
            if (isset($globalApps['user'])) {
                $this->user = new $this->config->globalApps['user']['entity']();
            }
        }
        parent::__construct();
    }
}
