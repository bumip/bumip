<?php
namespace Bumip\MVC\Controller;

class MainController extends \Bumip\Core\Controller
{
    public $user;
    public function __construct($config = null)
    {
        if ($globalApps = $config->get("globalApps")) {
            if (isset($globalApps['user'])) {
                $this->user = new $this->config->globalApps['user']['entity']();
            }
        }
        parent::__construct($config);
    }
}
