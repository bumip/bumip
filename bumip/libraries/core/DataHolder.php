<?php
namespace Bumip\Core;

class DataHolder
{
    private $data;
    private $protectedKeys = [];
    public function __construct($data = [])
    {
        $this->data = $data;
    }
    public function data($key, $value = null, $returnOnNoValue = false)
    {
        if ($value !== null) {
            if (in_array($key, $this->protectedKeys)) {
                trigger_error("The key '" . $key . "' is protected.");
                return false;
            }
            $this->data[$key] = $value;
        } else {
            return $this->data[$key] ?? $returnOnNoValue;
        }
    }
    public function get($key)
    {
        return $this->data($key);
    }
    public function set($key, $value)
    {
        $this->data($key, $value);
    }
}
