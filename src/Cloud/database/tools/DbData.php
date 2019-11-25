<?php


namespace zyblog\wxMpCloudHttpApi\database\tools;


class DbData extends DbToolsBase
{
    private $extrinsicOperator = ['[set]', '[push]', '[unshift]', '[addToSet]', '[pullAll]', '[pull]'];

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
        echo $dataString, PHP_EOL;
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
            case '[min]': // cannot get property 'min'
                $value = '_.min(' . $value . ')';
                break;
            case '[max]': // cannot get property 'max'
                $value = '_.max(' . $value . ')';
                break;
            case '[rename]': // cannot get property 'rename'
                $value = '_.rename(\"' . $value . '\")';
                break;
            case '[push]':
                $resValue = '_.push(';
                if (isset($value['each'])) {
                    $resValue .= '{';
                    $subValus[] = 'each:' . json_encode($value['each'], JSON_UNESCAPED_UNICODE);
                    if (isset($value['position'])) {
                        $subValus[] = 'position:' . $value['position'];
                    }
                    if (isset($value['sort'])) {
                        $subValus[] = 'sort:' . (is_array($value['sort']) ? json_encode($value['sort'], JSON_UNESCAPED_UNICODE) : $value['sort']);
                    }
                    if (isset($value['slice'])) {
                        $subValus[] = 'slice:' . $value['slice'];
                    }
                    $resValue .= implode(',', $subValus) . '}';
                } else {
                    $resValue .= json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $value = $resValue . ')';
                break;
            case '[pop]':
                $value = '_.pop()';
                break;
            case '[shift]':
                $value = '_.shift()';
                break;
            case '[unshift]':
                $value = '_.unshift(' . json_encode($value, JSON_UNESCAPED_UNICODE) . ')';
                break;
            case '[addToSet]': // cannot get property 'addToSet'
                if (is_array($value)) {
                    if (isset($value['each'])) {
                        $value = $value['each'];
                    }
                    $value = '_.addToSet(' . json_encode($value, JSON_UNESCAPED_UNICODE) . ')';
                } else {
                    $value = '_.addToSet(' . (gettype($value) == 'string' ? '"' . $value . '"' : $value) . ')';
                }
                break;
            case '[pullAll]':  // cannot get property 'pullAll'
                $value = '_.pullAll(' . json_encode($value, JSON_UNESCAPED_UNICODE) . ')';
                break;
            case '[pull]': // cannot get property 'pull'
                if (is_array($value)) {
                    $con = new DbCondition();
                    $value = '_.pull(' . $con->where($value) . ')';
                } else {
                    $value = '_.pull(' . (gettype($value) == 'string' ? '"' . $value . '"' : $value) . ')';
                }
                break;
        }
        return $this->CompositeField($field, $value, $opt);
    }

}