<?php


namespace zyblog\wxMpCloudHttpApi\database\tools;

/**
 * 查询条件类
 * Class DbCondition
 * @package zyblog\wxMpCloudHttpApi\database\tools
 */
class DbCondition extends DbToolsBase
{

    private $extrinsicOperator = ['[nin]', '[in]', '[reg]', '[geoNear]', '[geoWithin]', '[geoIntersects]', '[_eq]', '[all]', '[elemMatch]', '[and]', '[or]', '[nor]'];

    /**
     * where条件组合
     * @param array $where where条件
     * @return string 解析后的where条件
     */
    public function where($where = [])
    {
        $whereObj = $this->loop($where, $this->extrinsicOperator);
        return implode(',', $whereObj);
    }

    /**
     * 解析组合递归
     * @param $f 字段名
     * @param $w 内容值
     * @return string 组合后的内容
     */
    protected function composite($f, $w)
    {
        if(is_numeric($f)){
            $whereString = '{' . implode(',',$this->loop($w, $this->extrinsicOperator)) . '}';
        }else{
            $whereString = $f . ':{' . implode(',',$this->loop($w, $this->extrinsicOperator)) . '}';
        }
        return $whereString;
    }

//    /**
//     * where条件参数组合
//     * @param $wheres
//     * @return array
//     */
//    private function loopWhere($wheres)
//    {
//        $whereObjs = [];
//        foreach ($wheres as $k => $v) {
//            // 拆解字段值
//            list($field, $operator) = explode(' ', $k);
//            $operator = $operator ?: '[=]';
//            $value = $v;
//            if (is_array($value) && !in_array($operator, ['[nin]', '[in]', '[reg]', '[geoNear]', '[geoWithin]', '[geoIntersects]', '[_eq]', '[all]', '[elemMatch]', '[and]', '[or]', '[nor]'])) {
//                $whereObjs[] = $this->CompositeWhere($field, $value);
//            } else {
//                $whereObjs[] = $this->Operator($field, $value, $operator);
//            }
//        }
//        return $whereObjs;
//    }

    /**
     * 操作符解析
     * @param $field 字段名
     * @param $value 值
     * @param $operator 操作符
     * @return string 解析后的内容
     */
    protected function operator($field, $value, $operator)
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
        $opt = ':';
        switch ($operator) {
            case '[eq]':
            case '[=]':
                $value = gettype($value) != 'string' ? $value : '"' . $value . '"';
                break;
            case '[_eq]':
                $value = '_.eq(' . (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . ')';
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
                $value = "/" . $value . "/im";
                break;
            case '[not like]':
                $value = "/^" . $value . "/im";
                break;
            case '[exists]':
                $value = "_.exists(" . ($value ? 'true' : 'false') . ")";
                break;
            case '[size]':
                $value = "_.size(" . $value . ")";
                break;
            case '[mod]':
                $value = "_.mod(" . $value . ")";
                break;
            case '[reg]':
                if (isset($value['regexp'])) {
                    $opt = $value['options'] ?: 'im';
                    $value = "db.RegExp({regexp:\"" . $value['regexp'] . "\", options: \"" . $opt . "\"})";
                }
                break;
            case '[and]':
                if (is_array($value) && count($value) > 0) {
                    $value = '_.and(' . $this->where($value) . ')';
                }
                break;
            case '[or]':
                if (is_array($value) && count($value) > 0) {
                    $value = '_.or(' . $this->where($value) . ')';
                }
                break;
            case '[nor]':
                if (is_array($value) && count($value) > 0) {
                    $value = '_.nor([' . $this->where($value) . '])';
                }
                break;
            case '[elemMatch]':
                if (is_array($value) && count($value) > 0) {
                    $value = '_.elemMatch({' . $this->where($value) . '})';
                }
                break;
            case '[all]':
                if (is_array($value) && count($value) > 0) {
                    $resValue = '_.all([';
                    $subs = [];
                    foreach ($value as $v) {
                        if (is_array($v)) {
                            $subs[] = '_.elemMatch({' . $this->where($v) . '})';
                        } else {
                            $subs[] = '"' . $v . '"';
                        }
                    }
                    $value = $resValue . implode(',', $subs) . '])';
                }
                break;
            case '[geoNear]':
                if (isset($value['geometry'])) {
                    $resValue = 'db.command.geoNear({';
                    $subValue[] = 'geometry:db.Geo.Point(' . $value['geometry'] . ')';
                    if (isset($value['minDistance'])) {
                        $subValue[] = 'minDistance: ' . (int)$value['minDistance'];
                    }
                    if (isset($value['maxDistance'])) {
                        $subValue[] = 'maxDistance: ' . (int)$value['maxDistance'];
                    }
                    $resValue .= implode(',', $subValue) . '})';
                    $value = $resValue;
                }
                break;
            case '[geoWithin]':
            case '[geoIntersects]':
                if (isset($value['geometry'])) {
                    $value = 'db.command.geoWithin({geometry:' . $value['geometry'] . '})';
                }
                if (isset($value['centerSphere'])) {
                    $value = 'db.command.geoWithin({centerSphere:' . $value['centerSphere'] . '})';
                }
                break;
        }
        return $this->CompositeField($field, $value, $opt);
    }
}