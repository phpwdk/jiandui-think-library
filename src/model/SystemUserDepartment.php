<?php
declare (strict_types=1);

namespace think\simple\model;

use think\simple\Model;

/**
 * 用户部门模型
 * Class SystemUserDepartmentGroup
 *
 * @package think\simple\model
 */
class SystemUserDepartment extends Model
{
    /**
     * 绑定模型名称
     *
     * @var string
     */
    protected $name = 'SystemUserDepartment';

    /**
     * 判断部门是否绑定用户
     *
     * @param int $department_id
     *
     * @return bool
     */
    public function isUser(int $department_id): bool
    {
        $map = ['department_id' => $department_id];
        return $this->where($map)->count() > 0;
    }

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
