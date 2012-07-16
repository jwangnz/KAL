<?php

class KAL_Handle implements KAL_HandleInterface {
    private $kind;
    private $hintID;
    private $cacheTime;
    private $forceMaster;

    private $lastIDGen;

    public function __construct(KAL_Kind $kind, $hint_id = null, $cache_time = null) {
        $this->kind = $kind;
        $this->hintID = (int) $hint_id;
        $this->cacheTime = (int) $cache_time;
        $this->forceMaster = false;
        $this->lastIDGen = array();
    }

    public function getHintID() {
        return $this->hintID;
    }

    public function getCacheTime() {
        return $this->cacheTime;
    }

    public function setHintID($hint_id) {
        $this->hintID = $hint_id;
        return $this;
    }

    public function setCacheTime($cache_time) {
        $this->cacheTime = (int) $cache_time;
        return $this;
    }

    public function setForceMaster($force_master) {
        $this->forceMaster = (bool) $force_master;
        return $this;
    }

    public function getForceMaster() {
        return $this->forceMaster;
    }

    public function findOne($pairs) {
        if (is_scalar($pairs)) {
            $pairs = array($this->kind->getSplitField() => $pairs);
        }

        foreach ($this->getFilters() as $filter) {
            $result = $filter->beforeFindOne($pairs);
            if ($result || $result === false) {
                return $result;
            }
        }

        $query = KAL_QueryBuilder::buildSelect($this->kind->getName(), array(),
            KAL_QueryBuilder::buildCondition($pairs), array());

        $hint_id = $this->determineHintID($pairs);
        $conn = $this->kind->getConn();

        if (! $this->getForceMaster()) {
            $result = $conn->queryOne($hint_id, $query, $this->getCacheTime());
        } else {
            $results = $conn->queryMaster($hint_id, $query, $this->getCacheTime());
            $result = (count($results) > 0) ? $results[0] : false;
        }
        if ($result != false) {
            $result = $this->convertRow($result);
        }

        foreach ($this->getFilters() as $filter) {
            $filter->afterFindOne($pairs, $result);
        }

        return $result;
    }

    public function updateOne($pairs, array $update, array $change) {
        if (is_scalar($pairs)) {
            $pairs = array($this->kind->getSplitField() => $pairs);
        }

        foreach ($this->getFilters() as $filter) {
            if ($filter->beforeUpdateOne($pairs, $update, $change) === false) {
                return false;
            }
        }

        $condition = KAL_QueryBuilder::buildCondition($pairs);
        $query = KAL_QueryBuilder::buildUpdate($this->kind->getName(), $update,
            $change, $condition, array());

        $hint_id = $this->determineHintID($pairs);
        $conn = $this->kind->getConn();

        $result = $conn->queryMaster($hint_id, $query);

        foreach ($this->getFilters() as $filter) {
            $filter->afterUpdateOne($pairs, $update, $change);
        }

        return $result;
    }

    public function deleteOne($pairs) {
        if (is_scalar($pairs)) {
            $pairs = array($this->kind->getSplitField() => $pairs);
        }

        foreach ($this->getFilters() as $filter) {
            if ($filter->beforeDeleteOne($pairs) === false) {
                return false;
            }
        }

        $condition = KAL_QueryBuilder::buildCondition($pairs);
        $query = KAL_QueryBuilder::buildDelete($this->kind->getName(), $condition, array());

        $hint_id = $this->determineHintID($pairs);
        $conn = $this->kind->getConn();

        $result = $conn->queryMaster($hint_id, $query);

        foreach ($this->getFilters() as $filter) {
            $filter->afterDeleteOne($pairs, $result);
        }

        return $result;
    }

    public function findMulti(array $pairs_list) {
        foreach ($this->getFilters() as $filter) {
            $result = $filter->findMulti($pairs_list);
            if ($result) {
                return $result;
            }
        }

        throw new LogicException('"findMulti" does not handled by any filters');
    }

    public function insertOne(array $pairs) {
        $special_fields = $this->kind->getSpecialFields();
        $idgen = array();
        foreach ($special_fields as $field_name => $handle) {
            if (!isset($pairs[$field_name])) {
                if ($handle instanceof KAL_ColumnGeneratorInterface) {
                    $idgen[$field_name] = $pairs[$field_name] = $handle->generate();
                }
            } else if ($handle instanceof KAL_ColumnConverterInterface) {
                $pairs[$field_name] = $handle->encode($pairs[$field_name]);
            }
        }

        // 过滤
        foreach ($this->getFilters() as $filter) {
            if ($filter->beforeInsertOne($pairs) === false) {
                return false;
            }
        }

        $query = KAL_QueryBuilder::buildInsert($this->kind->getName(), $pairs);

        $hint_id = $this->determineHintID($pairs);
        $conn = $this->kind->getConn();

        $result = $conn->queryMaster($hint_id, $query);
        if ($result->affectedRowNum()) {
            $this->lastIDGen = $idgen;
        } else {
            $this->lastIDGen = array();
        }

        // 过滤
        foreach ($this->getFilters() as $filter) {
            $filter->afterInsertOne($pairs, $result);
        }

        return $result;
    }

    public function find($pattern/*, $args ... */) {
        $args = func_get_args();
        array_unshift($args, array());
        return call_user_func_array(array($this, "findColumns"), $args);
    }

    public function findColumns(array $fields, $pattern/*, $args ... */) {
        $args = func_get_args();
        $values = array_slice($args, 2);

        $query = KAL_QueryBuilder::buildSelect($this->kind->getName(), $fields,
            $pattern, $values);

        $conn = $this->kind->getConn();
        if (! $this->getForceMaster()) {
            $result = $conn->query($this->getHintID(), $query, $this->getCacheTime());
        } else {
            $result = $conn->queryMaster($this->getHintID(), $query,
                $this->getCacheTime());
        }
        if ($result != false) {
            $result = $this->convertResult($result);
            if (empty($fields)) {
                foreach ($result as $row) {
                    foreach ($this->getFilters() as $filter) {
                        $filter->afterFindOne($row->toArray(), $row);
                    }
                }
            }
        }

        return $result;
    }

    public function update(array $update, array $change, $pattern/*, $args ... */) {
        $args = func_get_args();
        $values = array_slice($args, 3);

        $query = KAL_QueryBuilder::buildUpdate($this->kind->getName(), $update,
            $change, $pattern, $values);

        $conn = $this->kind->getConn();
        $result = $conn->queryMaster($this->getHintID(), $query);
        return $result;
    }

    public function delete($pattern/*, $args ... */) {
        $args = func_get_args();
        $args = array_slice($args, 1);

        if (trim($pattern) == "") {
            throw new Exception("empty pattern is forbidden");
        }

        $query = KAL_QueryBuilder::buildDelete($this->kind->getName(), $pattern, $args);

        $conn = $this->kind->getConn();
        $result = $conn->queryMaster($this->getHintID(), $query);
        return $result;
    }

    public function getLastIDGen($field_name) {
        if (isset($this->lastIDGen[$field_name])) {
            return $this->lastIDGen[$field_name];
        }
        return null;
    }

    private function determineHintID($pairs) {
        if (! $this->kind->isSingleTable()) {
            $split_field = $this->kind->getSplitField();
            if (isset($pairs[$split_field])) {
                return $pairs[$split_field];
            } else {
                throw new Exception('value of split field: "'.$split_field.
                    '" not provided');
            }
        } else {
            return 1;
        }
    }

    private function convertResult(ArrayAccess $result) {
        foreach ($result as $ii => $row) {
            $result[$ii] = $this->convertRow($row);
        }
        return $result;
    }

    private function convertRow(array $row) {
        $special_fields = $this->kind->getSpecialFields();
        foreach ($special_fields as $field_name => $handle) {
            if ($handle instanceof KAL_ColumnConverterInterface) {
                $row[$field_name] = $handle->decode($row[$field_name]);
            }
        }
        return $row;
    }

    private function getFilters() {
        $filters = $this->kind->getFilters();
        foreach ($filters as $filter) {
            $filter->setHandle($this);
        }
        return $filters;
    }
}
