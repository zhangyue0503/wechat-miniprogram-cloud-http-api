<?php


namespace zyblog\wxMpCloudHttpApi\database\tools;


abstract class DbToolsBase
{
    protected abstract function composite($field, $value);
    protected abstract function operator($field, $value, $operator);

    /**
     * 组合字段为json字符串
     * 支持.式key值调用，如a.b.c，组合结果为{a:{b:{c:...}}}
     * @param string $k 字段key
     * @param string $v 字段值
     * @param string $operator 操作符
     * @return string
     */
    protected function CompositeField($k, $v, $operator = ':')
    {
        if (!$k) {
            $operator = '';
        }
        if (strpos(trim($k, '.'), '.')) {
            $keys = explode('.', $k);
            $compositeKey = '';
            $countKeys = count($keys);
            if ($countKeys > 0) {
                foreach ($keys as $kk => $vv) {
                    if ($kk < $countKeys - 1) {
                        $compositeKey .= $vv . $operator . '{';
                    } else {
                        $compositeKey .= $vv;
                    }
                }
                $fieldString = $compositeKey . $operator . $v . str_repeat('}', $countKeys - 1);
            } else {
                $fieldString = $k . $operator . $v;
            }
        } else {
            $fieldString = (string)$k . $operator . $v;
        }
        return $fieldString;
    }



    protected function loop($wheres, $extrinsicOperator)
    {
        $whereObjs = [];
        foreach ($wheres as $k => $v) {
            // 拆解字段值
            list($field, $operator) = explode(' ', $k, 2);
            if(preg_match("/\[(.*)?\]/", $field)){
                $operator = $field;
                $field = '';
            }else{
                $operator = $operator ?: '[=]';
            }
            $value = $v;
            if (is_array($value) && !in_array($operator, $extrinsicOperator)) {
                $whereObjs[] = $this->composite($field, $value);
            } else {
                $whereObjs[] = $this->operator($field, $value, $operator);
            }
        }
        return $whereObjs;
    }
}