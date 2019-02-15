<?php
namespace AppZz\Http\CurlClient;

class ArrayAccess implements \ArrayAccess, \Countable {

    protected $_container = array();

    public function __construct (array $array = [])
    {
        if ( ! empty ($array))
        {
            $this->_container = $array;
        }
    }

    public function offsetExists ($offset)
    {
        return isset($this->_container[$offset]);
    }

    public function offsetGet ($offset)
    {
        return $this->offsetExists($offset) ? $this->_container[$offset] : NULL;
    }

    public function offsetSet ($offset, $value)
    {
        if (empty($offset))
        {
            $this->_container[] = $value;
        }
        else
        {
            $this->_container[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->_container[$offset]);
        }
    }

    public function count()
    {
        return count($this->_container);
    }

    public function asArray ()
    {
        return $this->_container;
    }
}
