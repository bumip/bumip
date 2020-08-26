<?php
/**
 * Here are included the required files fo the framework
 */

$language = require_once CONFDIR."locale.php";
// require_once CONFDIR."costants.php";
// require_once MVCDIR.'loader.php';
// require_once LIBDIR."core/controller.php";
$configuration = new Bumip\Core\DataHolder(["language" => $language]);

$extend_main = EXTEND_DIR . 'mvc/controller/main.php';
if (is_file($extend_main)) {
    require_once $extend_main;
    $app = new \App\MVC\Controller\Main($configuration);
//define('dontautorun', 1);
} else {
    $app = new Bumip\MVC\Controller\MainController($configuration);
}
