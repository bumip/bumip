<?php
namespace Bumip\MVC\Controller;

class MainController extends \Bumip\Core\Controller
{
    public $user;
    public function __construct($config = null)
    {
        $apps = include "app/enabledApps.php";
        $config->data("apps", $apps);
        parent::__construct($config);
        $config = $this->config;
        if ($globalEntities = $config->get("apps/enabledEntities/global")) {
            $options = ["db" => $config->get("db"), "controller" => &$this];
            foreach ($globalEntities as $entity) {
                include $entity["path"];
                $this->{$entity["controllerProperty"]} = new $entity["class"]($options);
            }
        }
    }
}
