<?php
spl_autoload_register(function ($class) {
    $segments = explode('\\', $class);
    if (!empty($segments[1]) && $segments[1] == "Core") {
        $path = "libraries/core/" .
        str_replace("\\", "/", explode('Core\\', $class)[1]) . ".php";
        include $path;
    }
});
