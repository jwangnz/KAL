<?php

class KAL_ConfigLoader implements KAL_ConfigLoaderInterface {
    private $dbman;
    private $splitMap;

    public function __construct(array $split_map = array(), $dbman = "") {
        $this->splitMap = $split_map;
        $this->dbman = $dbman;
    }

    public function setSplitMap(array $map) {
        $this->splitMap = $map;
        return $this;
    }

    public function setDBMan($dbman) {
        $this->dbman = $dbman;
        return $this;
    }

    public function loadKindConfig($kind_name) {
        $config = array(
            "dbman"          => $this->dbman,
            "split_field"    => $this->getSplitField($kind_name),
            "filters"        => array(),
            "is_single"      => false,
            "special_fields" => array(),
        );
        return new KAL_Config($config);
    }

    private function getSplitField($kind_name) {
        $parts = explode("_", $kind_name, 3);
        return $this->splitMap[$parts[1]];
    }
}
