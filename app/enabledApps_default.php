<?php
require_once "app_autoloader.php";
$conf = $conf ?? [];
//@begin apps.json
$confApps["enabledApps"]["package_0001"] = [
    "directory" => "org.bumip.admin",
    "classPaths" => [
        "Bumip\Apps\Admin\AdminController" => "Admin.php"
    ]
];
$confApps["enabledApps"]["package_0002"] = [
    "directory" => "com.tomokit.reservation-manager",
    "classPaths" => [
        "Tomokit\ReservationManagerController" => "ReservationManager.php"
    ]
];
$confApps["enabledApps"]["package_0003"] = [
    "directory" => "org.bumip.user",
    "classPaths" => [
        "Bumip\Apps\User\UserController" => "User.php"
    ]
];
$confApps["classMap"] = [
    "Bumip\Apps\Admin\AdminController" => "package_0001",
    "Tomokit\ReservationManagerController" => "package_0002",
    "Bumip\Apps\User\UserController" => "package_0003"
];
$confApps["controllerMap"] = [
    "admin" => "Bumip\Apps\Admin\AdminController",
    "user" => "Bumip\Apps\User\UserController",
    "hosting3" => "Tomokit\ReservationManagerController"
];
$confApps["enabledEntities"] = [
    "global" => [
        "user" => [
            "class" => "Bumip\Entities\User",
            "path" => "app/entities/User.php",
            "controllerProperty" => "user"
        ]
    ]
];
//@end apps.json
return array_merge($conf, $confApps);
