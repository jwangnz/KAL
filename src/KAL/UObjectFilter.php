<?php

class KAL_UObjectFilter implements KAL_FilterInterface {
    private $fields;
    private $uoGroup;
    private $idName;

    private $kind;
    private $handle;

    private $uo;

    public function __construct(array $uo_fields, $id_name = '', $uo_group="") {
        if (empty($uo_fields)) {
            throw InvalidArgumentException('argument 1 should not be empty');
        }

        $this->fields = $uo_fields;
        $this->uoGroup = $uo_group;
        $this->idName = $id_name;
    }

    public function setKind(KAL_KindInterface $kind) {
        $this->kind = $kind;
    }

    public function setHandle(KAL_HandleInterface $handle) {
        $this->handle = $handle;

        $this->uo = new KAL_UObject($this->kind->getName(), $this->fields,
            $this->kind->getSplitField(), $this->idName);
        $this->uo->setUOGroup($this->uoGroup);
    }

    public function beforeInsertOne(array $pairs) {
    }

    public function afterInsertOne(array $pairs, $result) {
        if ($result->affectedRowNum()) {
            $this->invalidate($pairs);
        }
    }

    public function beforeUpdateOne(array $pairs, array $update, array $change) {

    }

    public function afterUpdateOne(array $pairs, array $update, array $change, $result) {
        if ($result->affectedRowNum()) {
            $this->invalidate($pairs);
        }
    }

    public function beforeFindOne(array $pairs) {
        if (! $this->idName) {
            $result = $this->uo->queryOne($pairs[$this->kind->getSplitField()]);
        } else {
            $result = $this->uo->queryOne($pairs[$this->kind->getSplitField()],
                $pairs[$this->idName]);
        }
        return $result;
    }

    public function afterFindOne(array $pairs, $result) {

    }

    public function beforeDeleteOne(array $pairs) {

    }

    public function afterDeleteOne(array $pairs, $result) {
        // if ($result->affectedRowNum()) {
            $this->invalidate($pairs);
        // }
    }

    private function invalidate($pairs) {
        if (! $this->idName) {
            $this->uo->invalidate($pairs[$this->kind->getSplitField()]);
        } else {
            $this->uo->invalidate($pairs[$this->kind->getSplitField()], $pairs[$this->idName]);
        }
    }
}
