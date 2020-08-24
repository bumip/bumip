<?php
$avail_lang["it"] = "it_IT";
$avail_lang["en"] = "en_US";
global $avail_lang;
$locale = "en_US";
if (!defined("DEFAULT_LANGUAGE")) {
    define("DEFAULT_LANGUAGE", $locale);
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
    bindtextdomain("messages", SPECDIR."locale");
    bind_textdomain_codeset("messages", "utf-8");
    textdomain("messages");
    define("DEFINED_LOCALE", true);
}
global $locale;
$language = explode('_', $locale)[0];
global $language;
