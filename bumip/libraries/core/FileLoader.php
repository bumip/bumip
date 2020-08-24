<?php
namespace Bumip\Core;

/**
 * @class FileLoader Class
 * Having seen how usless is to have $this->model->load() to just load a model file I created this helper.
 * Got to say it's pretty usless now that we can use autoloaders. I will keep this file for retrocompatibilty but I hope it will be deprecated soon.
 */
class FileLoader
{
    public $basepath = false;

    public function __construct($basepath)
    {
        $this->basepath = $basepath;
    }

    public function load($file)
    {
        foreach (func_get_args() as $file) {
            require_once $this->basepath . $file;
        }
    }
}
