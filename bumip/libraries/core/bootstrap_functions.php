<?php
/**
 * this function returns, using strpos and the host address, if the project is
 * running locally or remotely. useful for database or path configuration
 * @return bool
 */
function is_localrun($additionalLocalHosts = [])
{
    // used for debugging purposes
    if (defined('force_remoterun')) {
        return false;
    }
    $h = $_SERVER["HTTP_HOST"];
    $commonLocalHosts = ["localhost", "127.0.0.1", "192.168"];
    foreach (array_merge($commonLocalHosts, $additionalLocalHosts) as $lh) {
        if (strpos($h, $lh) === 0) {
            return true;
        }
    }
    //some machine can set their local name using name.local
    if (strpos($h, ".local") !== false) {
        return true;
    }
    return false;
}

function set_locale($locale = false)
{
    if (defined("CURRENT_LANGUAGE")) {
        $locale = CURRENT_LANGUAGE;
    } else {
        $locale = DEFAULT_LANGUAGE;
    }
    putenv("LC_ALL=$locale");
    setlocale(LC_ALL, $locale);
    setlocale(LC_TIME, $locale);
    bindtextdomain("messages", MVCDIR."locale");
    bind_textdomain_codeset("messages", "utf-8");
    textdomain("messages");
    define("DEFINED_LOCALE", true);
}
