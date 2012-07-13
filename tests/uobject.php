<?php

/**
 * 使用UObject示例
 *
 * 表结构
CREATE TABLE motv_user_info_0 (
  user_id bigint(20) NOT NULL DEFAULT 0 COMMENT 'SPLIT KEY', -- 用户ID
  site_id int NOT NULL DEFAULT 0,                       -- 用来登陆的站点
  PRIMARY KEY (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */

require_once "/kpool/kxi/binding/kxi/boot.php";
require_once dirname(__FILE__)."/../src/autoload.php";

$loader = new KAL_ConfigLoader();
// 分表字段
$loader->setSplitMap(array(
    "user" => "user_id",
));
$loader->setDBMan("frodo");
KAL_Factory::init($loader);

$kind = KAL_Factory::getKind("motv_user_info");
$filter = array("KAL_UObjectFilter", array(array("user_id", "site_id"), "", "frodo"));
$kind->getConfig()->set("filters", array($filter));
$handle = $kind->getHandle();

$user_id = 1610612776;
$row = $handle->findOne($user_id); // 等同于: $handle->findOne(array("user_id" => $user_id));
var_dump($row);

$user_ids = array(1610612776, 1);
$rows = $handle->findMulti($user_ids);
var_dump($rows);
