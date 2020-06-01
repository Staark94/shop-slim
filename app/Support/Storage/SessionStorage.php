<?php

namespace Cart\Support\Storage;

use Countable;
use Cart\Support\Storage\Contracts\StorageInterface;

class SessionStorage implements StorageInterface, Countable
{
    protected $bucket;

    public function counts($r = 0)
    {
        return  count($r);
    }

    public function __construct($bucket = 'default')
    {
        $this->bucket = $bucket;
    }

    public function set($index, $val)
    {
        $_SESSION[$this->bucket][$index] = $val;
    }

    public function setArray($index, $value = array())
    {
        if(is_array($value))
        {
            foreach($value as $item => $val)
            {
                $_SESSION[$this->bucket][$index][$item] = $val;
            }
        }
    }

    public function get($index)
    {
        if (!$this->exists($index))
        {
            return;
        }

        return $_SESSION[$this->bucket][$index];
    }

    public function exists($index)
    {
        return isset($_SESSION[$this->bucket][$index]);
    }

    public function all()
    {
        if(empty($_SESSION)) {
            return;
        }

        return $_SESSION[$this->bucket];
    }

    public function unset($index)
    {
        if ($this->exists($index))
        {
            unset($_SESSION[$this->bucket][$index]);
        }
    }

    public function unsetItem($index, $item)
    {
        if ($this->exists($index))
        {
            unset($_SESSION[$this->bucket][$index][$item]);
        }
    }

    public function clear()
    {
        unset($_SESSION[$this->bucket]);
    }

    public function count()
    {
        if (!$this->all()) {
            return;
        }

        return count($this->all());
    }
}