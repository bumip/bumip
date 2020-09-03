<?php
namespace Bumip\Core;

class DataHolder implements \IteratorAggregate
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
            $delimiter = strpos($key, "/") !== false ? "/" : false;
            if (!$delimiter) {
                $delimiter = strpos($key, ".") !== false ? "." : false;
            }
            if ($delimiter) {
                $d = $this->data;
                foreach (explode($delimiter, $key) as $k) {
                    if (is_object($d) && get_class($d) == "Bumip\Core\DataHolder") {
                        $d = $d->data;
                    }
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
    public function has($key)
    {
        return isset($this->data[$key]);
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
    /**
     * Returns json_encoded string
     *
     * @param enum $options example JSON_PRETTY_PRINT
     * @return string
     */
    public function toJson(enum $options = null):string
    {
        return json_encode($this->data, $options);
    }
    /**
     * getIterator
     *
     * @return ArrayIterator
     */
    public function getIterator():\ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }
}
