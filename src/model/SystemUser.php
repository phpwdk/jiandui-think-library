<?php
declare (strict_types=1);

namespace think\simple\model;

use think\simple\Model;
use think\model\relation\HasOne;

/**
 * 系统用户模型
 * Class SystemUser
 *
 * @package think\simple\model
 */
class SystemUser extends Model
{
    /**
     * 日志名称
     *
     * @var string
     */
    protected $oplogName = '系统用户';

    /**
     * 日志类型
     *
     * @var string
     */
    protected $oplogType = '系统用户管理';

    /**
     * 获取用户数据
     *
     * @param mixed  $map    数据查询规则
     * @param array  $data   用户数据集合
     * @param string $field  原外连字段
     * @param string $target 关联目标字段
     * @param string $fields 关联数据字段
     *
     * @return array
     */
    public function items($map, array &$data = [], string $field = 'uuid', string $target = 'user_info', string $fields = 'username,nickname,headimg,status,deleted'): array
    {
        $query = $this->where($map)->order('sort desc,id desc');
        if (count($data) > 0) {
            $users = $query->whereIn('id', array_unique(array_column($data, $field)))->column($fields);
            foreach ($data as &$vo) $vo[$target] = $users[$vo[$field]] ?? null;
            return $users;
        } else {
            return $query->column($fields);
        }
    }

    /**
     * 获取用户数据
     *
     * @param int|array $ids   用户编号(支持数组)
     * @param string    $field 返回的内容字段
     * @param string    $key   多个用户时指定索引字段
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function item($ids, string $field = '*', string $key = 'id'): array
    {
        $map   = ['id' => $ids, 'deleted' => 0];
        $query = $this->where($map);
        if (!is_array($ids)) {
            $result = $query->field($field)->find();
            $result = $result ? $result->toArray() : [];
        } else $result = $query->order('sort desc,id asc')->column($field, $key);
        return $result;
    }

    /**
     * 关联身份权限
     *
     * @return HasOne
     */
    public function userinfo(): HasOne
    {
        return $this->hasOne(SystemBase::class, 'code', 'usertype')->where([
            'type' => '身份权限', 'status' => 1, 'deleted' => 0,
        ]);
    }

    /**
     * 默认头像处理
     *
     * @param mixed $value
     *
     * @return string
     */
    public function getHeadimgAttr($value): string
    {
        if (empty($value)) try {
            $host = sysconf('base.site_host') ?: 'https://v3.02347.net';
            return "{$host}/static/theme/img/headimg.png";
        } catch (\Exception $exception) {
            return "https://v3.02347.net/static/theme/img/headimg.png";
        } else {
            return $value;
        }
    }

    /**
     * 格式化登录时间
     *
     * @param string $value
     *
     * @return string
     */
    public function getLoginAtAttr(string $value): string
    {
        return format_datetime($value);
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
