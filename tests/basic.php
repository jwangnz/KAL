<?php

/**
 * 基本例子
 *
 * 表结构
CREATE TABLE motv_user_bind_0 (
  user_id bigint(20) NOT NULL DEFAULT '0' COMMENT 'SPLIT_KEY',  -- 用户站内UID
  site_user_id varchar(128) NOT NULL DEFAULT '',                 -- 外站UID
  site_id int(11) NOT NULL DEFAULT '0',                    -- 外站标识
  ctime timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY (user_id, site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 */

require_once "/kpool/kxi/binding/kxi/boot.php";
require_once dirname(__FILE__)."/../src/autoload.php";

$loader = new KAL_ConfigLoader();
// 设置使用哪个DBMan
$loader->setDBMan("frodo");
// 设置分表字段的映射
$loader->setSplitMap(array(
    "user" => "user_id",
));
KAL_Factory::init($loader);

$kind = KAL_Factory::getKind("motv_user_bind");
$handle = $kind->getHandle();~

$user_id = 123;
$site_id = 456;
$row = $handle->findOne(array(
    "user_id" => $user_id,
    "site_id" => $site_id,
));

$rows = $handle->setHintID($user_id)->find("%C in (%Ld)", "site_id", array(1, 2, 3));

$row = $handle->setHintID($user_id)->findColumns(array("user_id"), "%C in (%Ld)", "site_id", array(1, 2, 3));

$result = $handle->deleteOne(array(
    "user_id" => $user_id,
    "site_id" => $site_id,
));

$update = array(
    "ctime" => date("Y-m-d H:i:s"),
);
$change = array(
);
$result = $handle->updateOne(array(
    "user_id" => $user_id,
    "site_id" => $site_id,
), $update, $change);
