<?php

interface KAL_HandleInterface {
    public function setHintID($hint_id);

    public function setCacheTime($cache_time);

    public function setForceMaster($force_master);

    public function findOne($pairs);

    public function updateOne($pairs, array $update, array $change);

    public function deleteOne($pairs);

    public function findMulti(array $pairs_list);

    public function insertOne(array $pairs);

    public function find($pattern/*, $args ... */);

    public function findColumns(array $fields, $pattern/*, $args ... */);

    public function update(array $update, array $change, $pattern/*, $args ... */);

    public function delete($pattern/*, $args ... */);
}

