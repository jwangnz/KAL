<?php

/**
 * 单表及自动字段示例
 *
 * 表结构
CREATE TABLE motv_site_user (
  site_id int NOT NULL DEFAULT 0,
  site_user_id varchar(120) NOT NULL DEFAULT 0,
  account blob NOT NULL,                                         -- 帐号信息
  UNIQUE (site_id, site_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 *
 *
 */

require_once "/kpool/kxi/binding/kxi/boot.php";
require_once dirname(__FILE__)."/../src/autoload.php";

$loader = new KAL_ConfigLoader();
$loader->setDBMan("kxm");
KAL_Factory::init($loader);

$kind = KAL_Factory::getKind("motv_site_user");
// 设置成单表
$kind->getConfig()->set("is_single", true);

// 设置特殊字段
$special_fields = array(
    "account" => array("KAL_ColumnVBSBlob", array(array("name" => "name", "logo" => "logo"))),
);
$kind->getConfig()->set("special_fields", $special_fields);

$handle = $kind->getHandle();
$result = $handle->findOne(array("site_id" => 2, "site_user_id" => 1639037717));
var_dump($result);


