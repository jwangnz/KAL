<?php

require_once dirname(__FILE__)."/xsprintf.php";
require_once dirname(__FILE__)."/qsprintf.php";

class KAL_QueryBuilder {
    private static $queryEscape;

    public static function buildSQL($pattern, array $values) {
        if (!self::$queryEscape) {
            self::$queryEscape = new KAL_QueryEscape();
        }
        array_unshift($values, $pattern);
        array_unshift($values, self::$queryEscape);
        return call_user_func_array("qsprintf", $values);
    }

    public static function buildSelect($kind_name, array $columns, $pattern, array $values) {
        $column = "*";
        if (! empty($columns)) {
            $column = "%LC";
        }
        $pattern = "SELECT ".$column." FROM %T WHERE ".$pattern;
        array_unshift($values, $kind_name);
        if (!empty($columns)) {
            array_unshift($values, $columns);
        }

        return self::buildSQL($pattern, $values);
    }

    public static function buildUpdate($kind_name, array $update, array $change, $pattern, array $values) {
        $set = array();
        foreach ($update as $field => $value)
        {
            $set[] = self::buildSQL("%C = %ns", array($field, $value));
        }

        foreach ($change as $field => $value)
        {
            $set[] = self::buildSQL($value > 0 ? "%C = %C + %nd" : "%C = %C - %nd",
                array($field, $field, abs($value)));
        }

        $set = implode(', ', $set);
        $pattern = "UPDATE %T SET %Q WHERE ".$pattern;

        array_unshift($values, $set);
        array_unshift($values, $kind_name);
        return self::buildSQL($pattern, $values);
    }

    public static function buildDelete($kind_name, $pattern, array $values) {
        $pattern = "DELETE FROM %T WHERE ".$pattern;

        array_unshift($values, $kind_name);
        return self::buildSQL($pattern, $values);
    }

    public static function buildInsert($kind_name, array $pairs) {
        $fields = array_keys($pairs);
        $values = array();
        foreach ($pairs as $field => $value) {
            $pairs[$field] = self::buildSQL("%ns", array($value));
        }
        $values = array(
            $kind_name,
            $fields,
            implode(', ', $pairs),
        );
        $pattern = "INSERT INTO %T (%LC) VALUES (%Q)";
        return self::buildSQL($pattern, $values);
    }

    public function buildCondition(array $pairs) {
        $values = array();
        $conditions = array();
        $values = array();
        foreach ($pairs as $key => $value) {
            $conditions[] = "%C = %s";
            $values[] = $key;
            $values[] = $value;
        }
        return self::buildSQL(implode(" AND ", $conditions), $values);
    }
}
