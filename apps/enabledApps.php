<?php
require_once "app_autoloader.php";
$conf["enabledApps"]["package_0001"] = [
    "directory" => "org.bumip.admin",
    "classPaths" => [
        "Bumip\Apps\Admin\AdminController" => "Admin.php"
    ]
];
$conf["enabledApps"]["package_0002"] = [
    "directory" => "com.tomokit.reservation-manager",
    "classPaths" => [
        "Tomokit\ReservationManagerController" => "ReservationManager.php"
    ]
];
$conf["classMap"] = [
    "Bumip\Apps\Admin\AdminController" => "package_0001",
    "Tomokit\ReservationManagerController" => "package_0002"
];
$conf["controllerMap"] = [
    "admin" => "Bumip\Apps\Admin\AdminController",
    "hosting3" => "Tomokit\ReservationManagerController"
];
return $conf;
