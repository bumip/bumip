<?php
require_once "app_autoloader.php";
$conf = $conf ?? [];
//@begin apps.json
$confApps = array (
  'enabledApps' => 
  array (
    'package_0001' => 
    array (
      'directory' => 'org.bumip.admin',
      'classPaths' => 
      array (
        'Bumip\\Apps\\Admin\\AdminController' => 'Admin.php',
      ),
    ),
    'package_0002' => 
    array (
      'directory' => 'com.tomokit.reservation-manager',
      'classPaths' => 
      array (
        'Tomokit\\ReservationManagerController' => 'ReservationManager.php',
      ),
    ),
    'package_0003' => 
    array (
      'directory' => 'org.bumip.user',
      'classPaths' => 
      array (
        'Bumip\\Apps\\User\\UserController' => 'User.php',
      ),
    ),
  ),
  'classMap' => 
  array (
    'Bumip\\Apps\\Admin\\AdminController' => 'package_0001',
    'Tomokit\\ReservationManagerController' => 'package_0002',
    'Bumip\\Apps\\User\\UserController' => 'package_0003',
  ),
  'controllerMap' => 
  array (
    'admin' => 'Bumip\\Apps\\Admin\\AdminController',
    'user' => 'Bumip\\Apps\\User\\UserController',
    'hosting3' => 'Tomokit\\ReservationManagerController',
  ),
  'enabledEntities' => 
  array (
    'global' => 
    array (
      'user' => 
      array (
        'class' => 'Bumip\\Entities\\User',
        'path' => 'app/entities/User.php',
        'controllerProperty' => 'user',
      ),
    ),
  ),
);
//@end apps.json
return array_merge($conf, $confApps);
