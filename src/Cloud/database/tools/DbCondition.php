<?php


namespace zyblog\wxMpCloudHttpApi\database\tools;


class DbCondition
{
    /**
     * where条件组合
     * @param array $where where条件
     * @return string 解析后的where条件
     */
    public function Where($where = [])
    {
        $whereObj = [];
        foreach ($where as $w) {
            list($k, $w) = $w;
            if (!$k || !$w) {
                continue;
            }
            // 拆解字段值
            list($field, $operator) = explode(' ', $k);
            $operator = $operator ?: '[=]';
            $value = $w;

            if (is_array($value) && !in_array($operator, ['[nin]', '[in]'])) {
                $whereObj[] = $this->CompositeWhere($field, $value);
            } else {
                $whereObj[] = $this->Operator($field, $value, $operator);
            }
        }
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
        $whereObjs = [];
        foreach ($w as $v) {
            list($k, $v) = $v;
            // 拆解字段值
            list($field, $operator) = explode(' ', $k);
            $operator = $operator ?: '[=]';
            $value = $v;

            if (is_array($value) && !in_array($operator, ['[nin]', '[in]'])) {
                $whereObjs[] = $this->CompositeWhere($field, $value) ;
            } else {
                $whereObjs[] = $this->Operator($field, $value, $operator) ;
            }
        }
        $whereString .= implode(',', $whereObjs) . '}';
        return $whereString;
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
        $wString = '' . $field . ':';
        if(is_string($value)){
            $value = addslashes($value);
        }
        if(is_array($value)){
            array_map(function(&$v){
                if(is_string($v)){
                    $v = addslashes($v);
                }
            }, $value);
        }
        switch ($operator) {
            case '[eq]':
            case '[=]':
                $wString .= '"' . $value . '"';
                break;
            case '[gt]':
            case '[>]':
                $wString .= "_.gt(" . $value . ")";
                break;
            case '[gte]':
            case '[>=]':
                $wString .= "_.gte(" . $value . ")";
                break;
            case '[lt]':
            case '[<]':
                $wString .= "_.lt(" . $value . ")";
                break;
            case '[lte]':
            case '[<=]':
                $wString .= "_.lte(" . $value . ")";
                break;
            case '[neq]':
            case '[<>]':
            case '[!=]':
                $wString .= "_.neq(" . $value . ")";
                break;
            case '[in]':
                $wString .= "_.in(" . json_encode((array)$value, JSON_UNESCAPED_UNICODE) . ")";
                break;
            case '[not in]':
            case '[nin]':
                $wString .= "_.nin(" . json_encode((array)$value, JSON_UNESCAPED_UNICODE) . ")";
                break;
            case '[like]':
                $wString .= "/" . $value . "/";
                break;
            case '[not like]':
                $wString .= "/^" . $value . "/";
                break;
        }

        return $wString;
    }
}