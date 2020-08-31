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
            if (strpos($key, "/") !== false) {
                $d = $this->data;
                foreach (explode("/", $key) as $k) {
                    if (!isset($d[$k])) {
                        return $returnOnNoValue;
                    }
                    $d = $d[$k];
                }
                return $d;
            }
            return $this->data[$key] ?? $returnOnNoValue;
        }
    }
    public function dataAsDataHolder($key)
    {
        $d = $this->data($key);
        return $d ? new DataHolder($d) : false;
    }
    public function get($key)
    {
        return $this->data($key);
    }
    public function set($key, $value)
    {
        $this->data($key, $value);
    }
    public function removeData($key = null)
    {
        if ($key) {
            unset($this->data[$key]);
        } else {
            $this->data = [];
        }
    }
}
