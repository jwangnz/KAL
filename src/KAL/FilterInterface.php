<?php

interface KAL_FilterInterface {
    public function setKind(KAL_KindInterface $kind);

    public function setHandle(KAL_HandleInterface $handle);

    public function beforeInsertOne(array $pairs);

    public function afterInsertOne(array $pairs, $result);

    public function beforeUpdateOne(array $pairs, array $pairs_update, array $pairs_change);

    public function afterUpdateOne(array $pairs, array $pairs_update, array $paris_change, $result);

    public function beforeFindOne(array $pairs);

    public function afterFindOne(array $pairs, $result);

    public function beforeDeleteOne(array $pairs);

    public function afterDeleteOne(array $pairs, $result);

    public function findMulti(array $pairs_list);
}
