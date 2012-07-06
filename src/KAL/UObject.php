<?php

/**
 * 不想用 DKXI_UO:
 *   DKXI_UO 会操作数据库
 *
 * 所以直接从DKXI_Unite扩展
 *
 */
class KAL_UObject extends DKXI_Unite {
    public function __construct($kind, $fields, $xfield, $idname = '') {
        parent::__construct($kind, $fields, $xfield, $idname);
    }

    public function setUOGroup($grp) {
        $this->_group = $grp;
    }

    public function invalidate($xid, $id = 0) {
        if (! $this->_useId($id)) {
            $this->invalidateSimple($xid);
        } else {
            $this->invalidateComplex(array($xid, $id));
        }
    }
}
