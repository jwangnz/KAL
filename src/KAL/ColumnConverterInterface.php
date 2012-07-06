<?php

interface KAL_ColumnConverterInterface {
    public function encode($value);

    public function decode($value);
}
