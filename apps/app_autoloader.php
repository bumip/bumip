<?php
function getEnabledApps()
{
    return include 'enabledApps.php';
}
spl_autoload_register(function ($class) {
    // foreach (explode("\", $class) as $key => $value) {
    //     # code...
    // }
    $conf = getEnabledApps();
    if (empty($conf["classMap"][$class])) {
        return;
    }
    $package = $conf["classMap"][$class];
    $package = $conf["enabledApps"][$package];
    $path =  "apps/" . $package["directory"]  . '/app/'.  $package["classPaths"][$class];
    include $path;
});
