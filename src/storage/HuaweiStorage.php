<?php
declare (strict_types=1);

namespace think\simple\storage;

use think\facade\Log;
use think\simple\Storage;

/**
 * 华为云OBS存储支持
 * Class HuaweiStorage
 *
 * @package think\simple\storage
 */
class HuaweiStorage extends Storage
{

    /**
     * 初始化入口
     *
     * @throws \think\simple\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function initialize()
    {
    }

    /**
     * 获取当前实例对象
     *
     * @param null|string $name
     *
     * @return static
     * @throws \think\simple\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function instance(?string $name = null)
    {
        return parent::instance('huawei');
    }

    /**
     * 上传文件内容
     *
     * @param string      $name    文件名称
     * @param string      $file    文件内容
     * @param boolean     $safe    安全模式
     * @param null|string $attname 下载名称
     *
     * @return array
     */
    public function set(string $name, string $file, bool $safe = false, ?string $attname = null): array
    {
        Log::info(['set', $name, $file, $safe, $attname]);
    }

    /**
     * 删除存储的文件
     *
     * @param string  $name 文件名称
     * @param boolean $safe 安全模式
     *
     * @return boolean
     */
    public function del(string $name, bool $safe = false): bool
    {
    }

    /**
     * 获取文件存储信息
     *
     * @param string      $name    文件名称
     * @param boolean     $safe    安全模式
     * @param null|string $attname 下载名称
     *
     * @return array
     */
    public function info(string $name, bool $safe = false, ?string $attname = null): array
    {
        Log::info(['info', $name, $safe, $attname]);
        return [];
    }
}
