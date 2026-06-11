<?php
declare (strict_types=1);

namespace think\simple\model;

use think\facade\Log;
use think\simple\Model;

/**
 * 部门模型
 * Class SystemDepartmentGroup
 *
 * @package think\simple\model
 */
class SystemDepartment extends Model
{
    /**
     * 日志名称
     *
     * @var string
     */
    protected $oplogName = '系统部门';

    /**
     * 日志类型
     *
     * @var string
     */
    protected $oplogType = '系统部门管理';

    /**
     * 获取全部部门数据
     *
     * @return ?array
     */
    public function items(): ?array
    {
        $field = 'id,pid,name';
        $map   = ['status' => 1, 'deleted' => 0];
        return $this->where($map)->order('sort desc,id asc')->column($field) ?: null;
    }

    /**
     * 获取部门数据
     *
     * @param int|array $ids   部门编号(支持数组)
     * @param string    $field 返回的内容字段
     * @param string    $key   多个用户时指定索引字段
     *
     * @return ?array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function item($ids, string $field = '*', string $key = 'id'): ?array
    {
        $map   = ['id' => $ids, 'status' => 1, 'deleted' => 0];
        $query = $this->where($map);
        if (!is_array($ids)) {
            $result = $query->field($field)->find();
            return $result ? $result->toArray() : [];
        }
        return $query->order('sort desc,id asc')->column($field, $key) ?: null;
    }

    /**
     * 判断同级名称是否存在
     *
     * @param string $name
     * @param int    $pid
     * @param int    $id
     *
     * @return bool
     */
    public function isName(string $name, int $pid = 0, int $id = 0): bool
    {
        $map   = ['name' => $name, 'pid' => $pid, 'deleted' => 0];
        $query = $this->where($map);
        ($id > 0) && $query->where('id', '<>', $id);
        return $query->count() > 0;
    }

    /**
     * 判断权限是否绑定部门
     *
     * @param int $id
     *
     * @return bool
     */
    public function isAuthorize(int $id): bool
    {
        $map   = ['status' => 1, 'deleted' => 0];
        $query = $this->where($map)->whereLike('authorize', "%,{$id},%");
        return $query->count() > 0;
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
