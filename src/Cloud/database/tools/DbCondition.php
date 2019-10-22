<?php


namespace zyblog\wxMpCloudHttpApi\database\tools;

/**
 * 查询条件类
 * Class DbCondition
 * @package zyblog\wxMpCloudHttpApi\database\tools
 */
class DbCondition extends DbToolsBase
{
    /**
     * where条件组合
     * @param array $where where条件
     * @return string 解析后的where条件
     */
    public function Where($where = [])
    {
        $whereObj = $this->loopWhere($where);
        return implode(',', $whereObj);
    }

    /**
     * 解析组合递归
     * @param $f 字段名
     * @param $w 内容值
     * @return string 组合后的内容
     */
    private function CompositeWhere($f, $w)
    {
        $whereString = '' . $f . ':{';
        $whereObjs = $this->loopWhere($w);
        $whereString .= implode(',', $whereObjs) . '}';
        return $whereString;
    }

    /**
     * where条件参数组合
     * @param $wheres
     * @return array
     */
    private function loopWhere($wheres)
    {
        $whereObjs = [];
        foreach ($wheres as $k => $v) {
            if (!$v) {
                continue;
            }
            // 拆解字段值
            list($field, $operator) = explode(' ', $k);
            $operator = $operator ?: '[=]';
            $value = $v;

            if (is_array($value) && !in_array($operator, ['[nin]', '[in]'])) {
                $whereObjs[] = $this->CompositeWhere($field, $value);
            } else {
                $whereObjs[] = $this->Operator($field, $value, $operator);
            }
        }
        return $whereObjs;
    }

    /**
     * 操作符解析
     * @param $field 字段名
     * @param $value 值
     * @param $operator 操作符
     * @return string 解析后的内容
     */
    private function Operator($field, $value, $operator)
    {
        if (is_string($value)) {
            $value = addslashes($value);
        }
        if (is_array($value)) {
            array_map(function (&$v) {
                if (is_string($v)) {
                    $v = addslashes($v);
                }
            }, $value);
        }

        switch ($operator) {
            case '[eq]':
            case '[=]':
                $value = '"' . $value . '"';
                break;
            case '[gt]':
            case '[>]':
                $value = "_.gt(" . $value . ")";
                break;
            case '[gte]':
            case '[>=]':
                $value = "_.gte(" . $value . ")";
                break;
            case '[lt]':
            case '[<]':
                $value = "_.lt(" . $value . ")";
                break;
            case '[lte]':
            case '[<=]':
                $value = "_.lte(" . $value . ")";
                break;
            case '[neq]':
            case '[<>]':
            case '[!=]':
                $value = "_.neq(" . $value . ")";
                break;
            case '[in]':
                $value = "_.in(" . json_encode((array)$value, JSON_UNESCAPED_UNICODE) . ")";
                break;
            case '[not in]':
            case '[nin]':
                $value = "_.nin(" . json_encode((array)$value, JSON_UNESCAPED_UNICODE) . ")";
                break;
            case '[like]':
                $value = "/" . $value . "/";
                break;
            case '[not like]':
                $value = "/^" . $value . "/";
                break;
        }
        return $this->CompositeField($field, $value);
    }
}