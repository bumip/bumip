<?php
/**
 * Here are included the required files fo the framework
 */

require_once CONFDIR."locale.php";
// require_once CONFDIR."costants.php";
// require_once MVCDIR.'loader.php';
// require_once LIBDIR."core/controller.php";
$configuration = new Bumip\Core\DataHolder();
$app = new Bumip\MVC\Controller\MainController($configuration);
