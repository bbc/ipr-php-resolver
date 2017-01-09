<?php

namespace BBC\iPlayerRadio\Resolver\Tests\Mocks;

class SimpleIterator implements \Iterator, \ArrayAccess
{
    protected $items = [];
    protected $current = 0;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function current()
    {
        return $this->items[$this->current];
    }

    public function next()
    {
        $this->current ++;
    }

    public function key()
    {
        return $this->current;
    }

    public function valid()
    {
        return array_key_exists($this->current, $this->items);
    }

    public function rewind()
    {
        $this->current = 0;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        // Blank
    }

    public function offsetUnset($offset)
    {
        // Blank
    }
}
