<?php

interface KAL_ConfigInterface {
    public function get($id);

    public function set($id, $value);
}
