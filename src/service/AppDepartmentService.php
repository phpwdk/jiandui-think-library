<?php
/**
 * 代码维护 ( 汪登科 )
 * 联系微信 ( wk9653992 )
 * 启动日期 ( 2022/4/11 15:46 )
 */

declare (strict_types=1);

namespace think\simple\service;

use think\simple\model\SystemDepartment;
use think\simple\Service;

/**
 * 系统部门管理服务
 * Class AppDepartmentService
 *
 * @package think\simple\service
 */
class AppDepartmentService extends Service
{
    /**
     * 读取全部下级部门
     *
     * @return array
     * @throws \Exception
     */
    public function getAllNext(): array
    {
        // 用户所在部门编号
        $department_ids = AdminService::instance()->getAppUserDepartment();
        // 所有下级部门
        $new_department_ids = [];
        $this->getDepartment($department_ids, $new_department_ids);
        return $new_department_ids;
    }

    /**
     * 读取全部上级部门
     *
     * @return array
     * @throws \Exception
     */
    public function getAllUpper(): array
    {
        // 用户所在部门编号
        $department_ids = AdminService::instance()->getAppUserDepartment();
        // 所有上级部门
        $this->getDepartment($department_ids, $department_ids, 'upper');
        return $department_ids;
    }

    /**
     * 读取下级或上级部门
     *
     * @param int|array $ids  部门编号 (支持数组)
     * @param array     $data 数据
     * @param string    $type 'next'下级,'upper'上级
     *
     * @return void
     */
    protected function getDepartment($ids, array &$data, string $type = 'next'): void
    {
        $field = 'id';
        if ($type === 'next') {
            $key       = 'id';
            $condition = ['pid' => $ids, 'status' => 1, 'deleted' => 0];
            $result    = SystemDepartment::mk()->where($condition)->column($field, $key);
        }
        if ($type === 'upper') {
            $key       = 'pid';
            $condition = ['id' => $ids, 'status' => 1, 'deleted' => 0];
            $result    = SystemDepartment::mk()->where($condition)->column($field, $key);
        }
        if (!empty($result)) {
            $ids  = array_keys($result);
            $data = array_unique(array_merge($data, $ids));
            $this->getDepartment($ids, $data, $type);
        }
    }
}
