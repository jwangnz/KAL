<?php

class KAL_Config implements KAL_ConfigInterface, ArrayAccess {
    private $values;

    public function __construct(array $values = array()) {
        $this->values = $values;
    }

    public function offsetExists($id) {
        return array_key_exists($id, $this->values);
    }

    public function offsetGet($id) {
        if (! array_key_exists($id, $this->values)) {
            throw new InvalidArgumentException('Identifier "'.$id.'" is not defined');
        }
        return $this->values[$id];
    }

    public function offsetSet($id, $value) {
        $this->values[$id] = $value;
    }

    public function offsetUnset($id) {
        unset($this->values[$id]);
    }

    public function set($id, $value) {
        $this->values[$id] = $value;
        return $this;
    }

    public function get($id) {
        return $this->offsetGet($id);
    }
}
