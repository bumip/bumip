<?php
/**
 * Hi and welcome to Bumip. To start using it
 * We ask you to read, understand and change the following definitions
 * based on your needs.
 * the @bumipteam
 *
 * You can edit the common constants file if you have any problem
 * @final string APP_NAME contains the name of your application
 * @var string $project_dir is the name of the project used most for working locally
 */
define("APP_NAME", "YOURAPP");
$project_dir = explode('/', dirname($_SERVER['SCRIPT_FILENAME']));
$project_dir = array_pop($project_dir);
require_once "bumip/libraries/core/bootstrap_functions.php";
/** User configuration */
require_once "configuration/constants.php";
/**
 * From here you should edit only if you have problem or
 * you need a custom configuration and you should add the following file to your .gitignore
 */
require_once "bumip/configuration/constants.php";
require_once 'vendor/autoload.php';
require_once "bumip/bumip.php";
