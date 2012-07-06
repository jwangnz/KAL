<?php

class KAL_ColumnVBSBlob implements KAL_ColumnConverterInterface {
    private $map;
    private $reverseMap;

    public function __construct($fields_map = array()) {
        if (!is_array($fields_map)) {
            throw new InvalidArgumentException('argument 1 should be array');
        }
        $this->map = $fields_map;
        $this->reverseMap = array_flip($this->map);
    }

    public function encode($value) {
        $newValue = array();
        foreach ($values as $key => $value) {
            if (isset($this->map[$key])) {
                $newValue[$this->map[$key]] = $value;
            }
        }
        return vbs_encode($newValue);
    }

    public function decode($value) {
        $newValue = array();

        $used = 0;
        $value = vbs_decode($value, $used);
        if ($used < strlen($value) || !is_array($value)) {
            return array();
        }
        foreach ($value as $key => $value) {
            if (isset($this->reverseMap[$key])) {
                $newValue[$this->reverseMap[$key]] = $value;
            }
        }
        return $newValue;
    }
}
