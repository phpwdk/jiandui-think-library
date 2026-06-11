<?php
declare (strict_types=1);

namespace think\simple\model;

use think\simple\Model;

/**
 * 系统菜单模型
 * Class SystemMenu
 * @package think\simple\model
 */
class SystemMenu extends Model
{
    /**
     * 日志名称
     * @var string
     */
    protected $oplogName = '系统菜单';

    /**
     * 日志类型
     * @var string
     */
    protected $oplogType = '系统菜单管理';

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
