<?php


namespace zyblog\wxMpCloudHttpApi\database\tools;


class DbData extends DbToolsBase
{
    private $extrinsicOperator = ['[set]'];

    public function data($data)
    {
        $dataObj = $this->loop($data, $this->extrinsicOperator);
        return implode(',', $dataObj);
    }

    protected function composite($f, $w)
    {
        if (is_numeric($f)) {
            $dataString = '{' . implode(',', $this->loop($w, $this->extrinsicOperator)) . '}';
        } else {
            $dataString = $f . ':{' . implode(',', $this->loop($w, $this->extrinsicOperator)) . '}';
        }
        return $dataString;
    }

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
            case '[=]':
                $value = is_numeric($value) ? $value : '"' . $value . '"';
                break;
            case '[set]':
                $value = '_.set(' . json_encode($value, JSON_UNESCAPED_UNICODE) . ')';
                break;
            case '[remove]':
                $value = '_.remove()';
                break;
            case '[inc]':
                $value = '_.inc(' . $value . ')';
                break;
            case '[mul]':
                $value = '_.mul(' . $value . ')';
                break;
            case '[min]':
                $value = '_.min(' . $value . ')';
                break;
            case '[max]':
                $value = '_.max(' . $value . ')';
                break;
            case '[rename]':
                $value = '_.rename(\"' . $value . '\")';
                break;

        }
        return $this->CompositeField($field, $value, $opt);
    }

}