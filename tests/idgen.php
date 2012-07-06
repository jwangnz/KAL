<?php

/**
 * 自增ID
 */

require_once "/kpool/kxi/binding/kxi/boot.php";
require_once dirname(__FILE__)."/../src/autoload.php";

$loader = new KAL_ConfigLoader();
$loader->setDBMan("kxm");
$loader->setSplitMap(array(
    "user" => "user_id",
));
KAL_Factory::init($loader);

$kind = KAL_Factory::getKind("motv_user_info");
$field = array("KAL_ColumnIDGen", array("long", array("kind" => "motv_user_id", "idman" => "frodo")));
$kind->getConfig()->set("special_fields", array(
    "user_id" =>$field,
));

$handle = $kind->getHandle();
$result = $handle->insertOne(array(
    "site_id" => 0,
));

var_dump($handle->getLastIDGen("user_id"));
