<?php

class KAL_ColumnIDGen implements KAL_ColumnGeneratorInterface {
    private $kind;
    private $isTimeBased;
    private $idman;

    public function __construct($kind, $is_time_based = 0, $idman = "") {
        $this->kind = $kind;
        $this->isTimeBased = (bool) $is_time_based;
        $this->idman = $idman;
    }

    public function generate() {
        $value = false;
        $dkxi_id = DKXI_ID::factory($this->idman);
        if ($this->isTimeBased) {
            $value = $dkxi_id->newTimeId($this->kind);
        } else {
            $value = $dkxi_id->newId($this->kind);
        }
        return $value;
    }
}
