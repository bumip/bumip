<?php
/**
 * Edit here to set  available languages for the project.
 */
$avail_lang["it"] = "it_IT";
$avail_lang["en"] = "en_US";
/**
 * Stop Editing
 */
$language = new Bumip\Core\DataHolder();

$language->data("availableLanguages", $avail_lang);
$locale = "en_US";
if (!defined("DEFAULT_LANGUAGE")) {
    define("DEFAULT_LANGUAGE", $locale);
}
$language->data("locale", $locale);
$current_lang = explode('_', $locale)[0];
$language->data("language", $current_lang);
return $language;
