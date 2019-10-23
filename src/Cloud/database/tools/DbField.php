<?php


namespace zyblog\wxMpCloudHttpApi\database\tools;

/**
 * 字段条件类
 * Class DbField
 * @package zyblog\wxMpCloudHttpApi\database\tools
 */
class DbField extends DbToolsBase
{
    /**
     * field条件组合
     * @param array $fields 字段
     * @return string
     */
    public function Field($fields = [])
    {
        $fieldStrings = $this->loop($fields);
        return implode(',', $fieldStrings);
    }

    /**
     * field条件参数组合
     * @param array $fields
     * @return array
     */
    private function loop($fields)
    {
        $fieldStrings = [];
        foreach ($fields as $v) {
            if (!$v) {
                continue;
            }
            if (is_array($v)) {
                // ["content",['mem.foo.baz'=>[1,2]],……]
                // 暂无法使用，报project无法找到异常
                // 文档：https://developers.weixin.qq.com/miniprogram/dev/wxcloud/reference-server-api/database/collection.field.html
                foreach ($v as $pKey => $pValue) {
                    if (is_array($pValue) && in_array(count($pValue), [1, 2])) {
                        $slice = implode(',', array_map(function ($n) {
                            return (int)$n;
                        }, $pValue));
                    } else {
                        $slice = (int)$pValue;
                    }
                    $fieldStrings[] = $this->CompositeField($pKey, 'db.command.project.slice(' . $slice . ')');
                }
            } else if (is_string($v)) {
                // ["content",……]
                $fieldStrings[] = $this->CompositeField($v, 'true');
            }
        }
        return $fieldStrings;
    }


}