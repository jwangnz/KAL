<?php

class KAL_Kind implements KAL_KindInterface {
    private $name;
    private $config;
    private $filters;
    private $filterNames;
    private $specialFields;

    private $conn;

    public function __construct($kind_name, KAL_ConfigInterface $config) {
        $this->name = $kind_name;
        $this->config = $config;
        $this->filters = array();
        $this->filterNames = array();
    }

    public function getName() {
        return $this->name;
    }

    public function isSingleTable() {
        return (bool) $this->config["is_single"];
    }

    public function getConfig() {
        return $this->config;
    }

    public function getConn() {
        if (!$this->conn) {
            $tag = $this->config["dbman"] ? $this->config["dbman"] : "";
            $this->conn = DKXI_Database::factory($tag);
        }
        return $this->conn;
    }

    public function getSplitField() {
        return $this->config["split_field"];
    }

    public function getSpecialFields() {
        if (! $this->specialFields) {
            $this->specialFields = array();
            foreach ($this->config["special_fields"] as $field_name => $config) {
                list($class_name, $parameters) = $config;
                $refl = new ReflectionClass($class_name);
                $this->specialFields[$field_name] = $refl->newInstanceArgs($parameters);
            }
        }
        return $this->specialFields;
    }

    public function getFilters() {
        if (! $this->filters) {
            $this->filters = array();
            foreach ($this->config["filters"] as $filter_config) {
                list($name, $parameters) = $filter_config;
                if (isset($this->filterNames[$name])) {
                    throw new Exception('filter "'.$name.'" is used');
                }
                $this->filterNames[$name] = 1;
                $refl = new ReflectionClass($name);
                $filter = $refl->newInstanceArgs($parameters);
                $filter->setKind($this);
                $this->filters[] = $filter;
            }
        }
        return $this->filters;
    }

    public function getHandle() {
        return new KAL_Handle($this);
    }
}
