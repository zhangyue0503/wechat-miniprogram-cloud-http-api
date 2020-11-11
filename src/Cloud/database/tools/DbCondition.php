<?php


namespace zyblog\wxMpCloudHttpApi\database\tools;

/**
 * 查询条件类
 * Class DbCondition
 * @package zyblog\wxMpCloudHttpApi\database\tools
 */
class DbCondition extends DbToolsBase
{

    private $extrinsicOperator = ['[nin]', '[not in]', '[in]', '[reg]', '[geoNear]', '[geoWithin]', '[geoIntersects]', '[_eq]', '[all]', '[elemMatch]', '[and]', '[or]', '[nor]', '[not]'];

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
    protected function composite($f, $w, $isObj)
    {
        if (is_numeric($f)) {
            $whereString = '{' . implode(',', $this->loop($w, $this->extrinsicOperator)) . '}';
        } else {
            if ($isObj) {
                $whereString = $f . ':{' . implode(',', $this->loop($w, $this->extrinsicOperator)) . '}';
            } else {
                $whereString = $f . ':[' . implode(',', $this->loop($w, $this->extrinsicOperator)) . ']';
            }
        }
        return $whereString;
    }

    /**
     * 操作符解析
     * @param $field 字段名
     * @param $value 值
     * @param $operator 操作符
     * @return string 解析后的内容
     */
    protected function operator($field, $value, $operator)
    {
        $value = $this->getValueTypeString($value);
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
                $value = "/" . trim($value, '"') . "/im";
                break;
            case '[not like]':
                $value = "/^" . trim($value, '"') . "/im";
                break;
            case '[exists]':
                $value = "_.exists(" . ($value ? 'true' : 'false') . ")";
                break;
            case '[size]':
                $value = "_.size(" . $value . ")";
                break;
            case '[mod]':
                $value = "_.mod(" . trim($value, '"') . ")";
                break;
            case '[reg]':
                if (isset($value['regexp'])) {
                    $options = $value['options'] ?: 'im';
                    $value = "db.RegExp({regexp:\"" . trim($value['regexp'], '"') . "\", options: \"" . $options . "\"})";
                } else {
                    $value = "/" . trim($value, '"') . "/im";
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
            case '[nor]':  // cannot get property 'nor'
                if (is_array($value) && count($value) > 0) {
                    $value = '_.nor([' . $this->where($value) . '])';
                }
                break;
            case '[not]': // cannot get property 'not'
                if (is_array($value) && count($value) > 0) {
                    $value = '_.not(' . $this->where($value) . ')';
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
                            $subs[] = $this->getValueTypeString($v);
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
            case '[json]':
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                break;
        }
        return $this->CompositeField($field, $value, $opt);
    }

    private function getValueTypeString($value)
    {
        if (gettype($value) == 'string') {
            $value = '"' . addslashes($value) . '"';
        }
        return $value;
    }

}