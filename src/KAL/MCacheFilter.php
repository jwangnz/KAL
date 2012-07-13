<?php

class KAL_MCacheFilter implements KAL_FilterInterface {
    private $mcache;
    private $expires;
    private $cache;
    private $uniqueFields;

    private $kind;
    private $handle;
    private $forceMaster;

    public function __construct($expires, array $unique_fields = array(), $mcache_group = "") {
        $this->expires = (int) $expires;
        if (! $this->expires > 0) {
            throw new InvalidArgumentException('argument 1 shoule be greater than zero');
        }
        $this->mcache = DKXI_MCache::factory($mcache_group);

        // 为了支持DKXI_FRResult的反序列化 需要确保DKXI_FRResult先被加载
        class_exists("DKXI_FRResult");

        $this->cache = array();
        $this->uniqueFields = $unique_fields;
    }

    public function setKind(KAL_KindInterface $kind) {
        $this->kind = $kind;
    }

    public function setHandle(KAL_HandleInterface $handle) {
        $this->handle = $handle;
    }

    public function beforeInsertOne(array $pairs) {
    }

    public function afterInsertOne(array $pairs, $result) {
        $this->invalidate($pairs);
    }

    public function beforeUpdateOne(array $pairs, array $paris_update, array $pairs_change) {
        // pass
    }

    public function afterUpdateOne(array $pairs, array $pairs_update,
        array $pairs_change, $result) {
        if ($result->affectedRowNum()) {
            $this->invalidate($pairs);
        }
    }

    public function beforeFindOne(array $pairs) {
        $proc_key = $this->getProcKey($pairs);
        if (isset($this->cache[$proc_key])) {
            return $this->cache[$proc_key];
        }
        $mcache_key = $this->getMCacheKey($pairs);
        $result = $this->mcache->get($mcache_key);
        if ($result && strlen($result)) {
            $result = unserialize($result);
            if ($result instanceof ArrayAccess) {
                $this->cache[$proc_key] = $result;
                return $result;
            }
        }

        // 从主库读
        $this->forceMaster = $this->handle->getForceMaster();
        $this->handle->setForceMaster(true);
    }

    public function afterFindOne(array $pairs, $result) {
        if ($result) {
            $this->cacheNew($pairs, $result);
        }

        // 置回原来状态
        $this->handle->setForceMaster($this->forceMaster);
    }

    public function beforeDeleteOne(array $pairs) {

    }

    public function afterDeleteOne(array $pairs, $result) {
        if ($result->affectedRowNum()) {
            $this->invalidate($pairs);
        }
    }

    public function findMulti(array $pairs_list) {
        return false;
    }

    private function cacheNew($pairs, $result) {
        $this->cache[$this->getProcKey($pairs)] = $result;
        $data = serialize($result);
        return $this->mcache->set($this->getMCacheKey($pairs), $data, $this->expires, true);
    }

    private function invalidate($pairs) {
        unset($this->cache[$this->getProcKey($pairs)]);
        return $this->mcache->delete($this->getMCacheKey($pairs));
    }

    private function getProcKey($pairs) {
        $key = $pairs[$this->kind->getSplitField()];
        foreach ($this->uniqueFields as $field) {
            $key .= "_".$pairs[$field];
        }
        return $key;
    }

    private function getMCacheKey($pairs) {
        $key = "kalm_".$this->kind->getName()."_".$pairs[$this->kind->getSplitField()];
        foreach ($this->uniqueFields as $field) {
            $key .= "_".$pairs[$field];
        }
        return $key;
    }
}
