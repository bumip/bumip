<?php
/**
 * Here are included the required files fo the framework
 */

$language = require_once CONFDIR."locale.php";
// require_once CONFDIR."costants.php";
// require_once MVCDIR.'loader.php';
// require_once LIBDIR."core/controller.php";
$language->data("multilanguage", MULTILANG);
$configuration = new Bumip\Core\DataHolder(["language" => $language]);
if (!isset($_SESSION)) {
    session_start();
}
$extend_main = EXTEND_DIR . 'mvc/controller/Main.php';
if (is_file($extend_main)) {
    require_once $extend_main;
    $app = new \App\MVC\Controller\Main($configuration);
//define('dontautorun', 1);
} else {
    $app = new Bumip\MVC\Controller\MainController($configuration);
}
$app->callMethodByUrl();
