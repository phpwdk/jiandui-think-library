<?php
declare (strict_types=1);

namespace think\simple\model;

use think\simple\Model;

/**
 * 系统日志模型
 * Class SystemOplog
 * @package think\simple\model
 */
class SystemOplog extends Model
{
    /**
     * 格式化创建时间
     *
     * @param string $value
     *
     * @return string
     */
    public function getCreateAtAttr(string $value): string
    {
        return format_datetime($value);
    }
}
