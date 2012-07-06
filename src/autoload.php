<?php

function _kal_autoload($class_name) {
    if (strpos($class_name, "KAL_") === 0) {
        require dirname(__FILE__)."/".str_replace("_", "/", $class_name).".php";
    }
}

spl_autoload_register("_kal_autoload");
