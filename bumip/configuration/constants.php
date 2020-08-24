<?php
$bumip_dir = "bumip/";
$mvc_dir = $bumip_dir."mvc/";
$configuration_dir = "configuration/";
$extend_dir = 'extend/';
$protocol = !empty($_SERVER['HTTPS'])? 'https' : 'http';
define('BUMIP_DIR', $bumip_dir);
define('FWDIR', $bumip_dir);
define('MVCDIR', $mvc_dir);
define('CONFDIR', $configuration_dir);
define('extend_dir', $extend_dir);
define('LIBDIR', FWDIR . "libraries/");
define('DEFAULT_METHOD', "index");
define('DEFAULT_PATH_INFO', "/main/index/");
define('DEFAULT_ACTION', "index");
define('ROOT_EXT', $protocol."://" . $_SERVER["HTTP_HOST"] . ROOT);
define('CONTROLLER_FILE', 'main.php');
define('CONTROLLER_CLASS', 'BumipController');
