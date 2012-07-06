# KAL: Kind Access Layer

## 特点
- 接口少
- 自动做SQL转义
- 支持基于行级的数据过滤器机制(以此来实现缓存，UO, 数据校验)
- 支持基于字段的数据转换(以此实现自增ID，自动序列化等)
- 可扩展(里面大部分实现都通有对应的interface定义，基本每一部分都可以替换成你自己的实现)

## 依赖
- DKXI_*

## 示例
```
$loader = new KAL_ConfigLoader();
$loader->setSplitMap(array(
    "user" => "user_id",
));
KAL_Factory::init($loader);
$kind = KAL_Factory::getKind("my_user_kind");
$handle = $kind->getHandle();
$user_id = 123;
$row = $handle->findOne($user_id);
```
## 更多例子可以查看 tests/　下的脚本

## 目前问题
- findMuli尚未实现
- 测试不全
- IDGen生成后，不方便拿到生成的ID

