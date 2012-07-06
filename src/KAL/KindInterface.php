<?php

interface KAL_KindInterface {
    public function __construct($kind_name, KAL_ConfigInterface $config);

    public function getName();

    public function getHandle();

    public function getConfig();

    public function getSplitField();

    public function getFilters();

    public function getConn();

    public function getSpecialFields();
}
