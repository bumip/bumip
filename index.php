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
require_once "configuration/constants.php";
