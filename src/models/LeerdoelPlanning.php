<?php

// class LeerdoelPlanning{
//     private $map = [];

// }

class LeerdoelPlanning implements ArrayAccess{
    private $leerdoelPlanning = [];

    //To make class indexable like an array
    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->leerdoelPlanning[] = $value;
        } else {
            $this->leerdoelPlanning[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool {
        return isset($this->leerdoelPlanning[$offset]);
    }

    public function offsetUnset($offset): void {
        unset($this->leerdoelPlanning[$offset]);
    }

    public function offsetGet($offset): mixed {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function getAll(){
        return $this->leerdoelPlanning;
    }
}