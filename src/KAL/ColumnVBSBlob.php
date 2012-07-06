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
        $new_value = array();
        foreach ($value as $key => $val) {
            if (isset($this->map[$key])) {
                $new_value[$this->map[$key]] = $val;
            }
        }
        return vbs_encode($new_value);
    }

    public function decode($value) {
        $new_value = array();

        $used = 0;
        $value = vbs_decode($value, $used);
        if ($used < strlen($value) || !is_array($value)) {
            return array();
        }
        foreach ($value as $key => $value) {
            if (isset($this->reverseMap[$key])) {
                $new_value[$this->reverseMap[$key]] = $value;
            }
        }
        return $new_value;
    }
}
