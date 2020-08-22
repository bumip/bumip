<?php
/**
 * @var string $project_dir is the name of the project used most for working locally
 * @final int USEMYSQL is used to automatic connect to a mysql database
 * @final int MULTILANG enables multilanguage support
 * @final string DEFAULT_LANGUAGE contains the locale string for the default language
 * @final string live_as_remote is just a hack to force is_localrun to return false
 */
define('PROJECT_NAME', $project_dir);
define('USEDB', true);
define('MULTILANG', true);
define("DEFAULT_LANGUAGE", "en_US");
/**
 * Error Reporting Handling
 */
if (is_localrun()) {
    ini_set('display_errors', 1);
    $default_project_error_level =  E_ALL;
} else {
    ini_set('display_errors', 0);
    $default_project_error_level =  E_ERROR;
}
define('DEFAULT_PROJECT_ERROR_LEVEL', $default_project_error_level);
error_reporting(DEFAULT_PROJECT_ERROR_LEVEL);
