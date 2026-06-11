<?php
declare (strict_types=1);

namespace think\simple\service;

use think\simple\extend\DataExtend;
use think\simple\model\SystemAuth;
use think\simple\model\SystemDepartment;
use think\simple\model\SystemNode;
use think\simple\model\SystemUser;
use think\simple\Service;

/**
 * 系统权限管理服务
 * Class AdminService
 *
 * @package think\simple\service
 */
class AdminService extends Service
{

    /**
     * 是否已经登录
     *
     * @return boolean
     */
    public function isLogin(): bool
    {
        return $this->getUserId() > 0;
    }

    /**
     * 是否已经登录
     *
     * @param string $type
     *
     * @return boolean
     */
    public function isAppLogin(string $type = 'MANAGE'): bool
    {
        return $this->getAppUserId($type) > 0;
    }

    /**
     * 是否已经登录后台
     *
     * @return boolean
     */
    public function isManageLogin(): bool
    {
        return $this->getAppUserId('MANAGE') > 0;
    }

    /**
     * 是否已经登录APP商户端
     *
     * @return boolean
     */
    public function isMerchantLogin(): bool
    {
        return $this->getAppUserId('MERCHANT') > 0;
    }

    /**
     * Token是否有效
     *
     * @param string $token
     *
     * @return boolean
     */
    public function isToken(string $token): bool
    {
        return $this->getToken() === $token;
    }

    /**
     * Token是否有效
     *
     * @param string $token
     *
     * @return boolean
     */
    public function isAppToken(string $token): bool
    {
        return $this->getAppToken() === $token;
    }

    /**
     * 是否为超级用户
     *
     * @return boolean
     */
    public function isSuper(): bool
    {
        if ($this->isManageLogin()) $user_name = $this->getAppUserName('MANAGE');
        else $user_name = $this->getUserName();
        return $user_name === $this->getSuperName();
    }

    /**
     * 获取超级用户账号
     *
     * @return string
     */
    public function getSuperName(): string
    {
        return $this->app->config->get('app.super_user', 'admin');
    }

    /**
     * 获取后台用户ID
     *
     * @return integer
     */
    public function getUserId(): int
    {
        return intval($this->app->session->get('user.id', 0));
    }

    /**
     * 获取APP端用户ID
     *
     * @param string $type
     *
     * @return integer
     */
    public function getAppUserId(string $type = 'USER'): int
    {
        $user = $this->getAppUser($type);
        return intval($user['id'] ?? 0);
    }

    /**
     * 获取APP端用户信息
     *
     * @param string $type
     *
     * @return mixed
     */
    public function getAppUser(string $type = 'USER')
    {
        if (empty($mToken = $this->getAppToken())) return [];
        return $this->app->cache->get($type . '.' . $mToken);
    }

    /**
     * 获取登录Token
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->app->session->get('user.token', '');
    }

    /**
     * 获取登录Token
     *
     * @return string
     */
    public function getAppToken(): string
    {
        return $this->app->request->header('Access-Token', '') ?: $this->app->request->header('Api-Token', '');
    }

    /**
     * 获取后台用户名称
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->app->session->get('user.username', '');
    }

    /**
     * 获取登录账号
     *
     * @param string $type
     *
     * @return string
     */
    public function getAppUserName(string $type = 'MERCHANT'): string
    {
        $user = $this->getAppUser($type);
        return !empty($user['username']) ? $user['username'] : '';
    }

    /**
     * 获取用户扩展数据
     *
     * @param null|string $field
     * @param null|mixed  $default
     *
     * @return array|mixed
     */
    public function getUserData(?string $field = null, $default = null)
    {
        $keys = "UserData_{$this->getUserId()}";
        $data = SystemService::instance()->getData($keys, []);
        return is_null($field) ? $data : ($data[$field] ?? $default);
    }

    /**
     * 设置用户扩展数据
     *
     * @param array   $data
     * @param boolean $replace
     *
     * @return boolean|integer
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setUserData(array $data, bool $replace = false)
    {
        $keys = "UserData_{$this->getUserId()}";
        $data = $replace ? $data : array_merge($this->getUserData(), $data);
        return SystemService::instance()->setData($keys, $data);
    }

    /**
     * 获取用户主题名称
     *
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserTheme(): string
    {
        $default = sysconf('base.site_theme') ?: 'default';
        return $this->getUserData('site_theme', $default);
    }

    /**
     * 设置用户主题名称
     *
     * @param string $theme 主题名称
     *
     * @return boolean|integer
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setUserTheme(string $theme)
    {
        return $this->setUserData(['site_theme' => $theme]);
    }

    /**
     * 获取用户部门数据
     *
     * @return array
     * @throws \Exception
     */
    public function getUserDepartment(): array
    {
        $id          = $this->getUserId();
        $field       = 'id,department,status';
        $system_user = SystemUser::mk()->field($field)->where('id', $id)->findOrEmpty();
        if (!$system_user->isEmpty() && $system_user['status'] === 1) return str2arr($system_user['department']);
        throw new \Exception('未绑定部门或部门不存在');
    }

    /**
     * 获取用户部门数据
     *
     * @return array
     * @throws \Exception
     */
    public function getAppUserDepartment(): array
    {
        $id          = $this->getAppUserId('MANAGE');
        $field       = 'id,department,status';
        $system_user = SystemUser::mk()->field($field)->where('id', $id)->findOrEmpty();
        if (!$system_user->isEmpty() && $system_user['status'] === 1) return str2arr($system_user['department']);
        throw new \Exception('未绑定部门或部门不存在');
    }

    /**
     * 检查指定节点授权
     * --- 需要读取缓存或扫描所有节点
     *
     * @param null|string $node
     *
     * @return boolean
     * @throws \ReflectionException
     */
    public function check(?string $node = ''): bool
    {
        $service = NodeService::instance();
        $methods = $service->getMethods();
        // 兼容 windows 控制器不区分大小写的验证问题
        foreach ($methods as $key => $rule) {
            if (preg_match('#.*?/.*?_.*?#', $key)) {
                $attr                      = explode('/', $key);
                $attr[1]                   = strtr($attr[1], ['_' => '']);
                $methods[join('/', $attr)] = $rule;
            }
        }
        $current = $service->fullnode($node);
        if (function_exists('admin_check_filter')) {
            return admin_check_filter($current, $methods, $this->app->session->get('user.nodes', []), $this);
        } elseif ($this->isSuper()) {
            return true;
        } elseif (!empty($methods[$current]['isuser'])) {
            return $this->isAppLogin('USER');
        } elseif (!empty($methods[$current]['ismerchant'])) {
            return $this->isAppLogin('MERCHANT');
        } elseif (empty($methods[$current]['isauth'])) {
            $islogin = $methods[$current]['islogin'] ?? false;
            return empty($islogin) || (!empty($islogin) && ($this->isLogin() || $this->isAppLogin()));
        } else {
            if (!empty($user = $this->getAppUser('MANAGE'))) return !empty($user['nodes']) ? in_array($current, $user['nodes']) : false;
            else return in_array($current, $this->app->session->get('user.nodes', []));
        }
    }

    /**
     * 获取授权节点列表
     *
     * @param array $checkeds
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getTree(array $checkeds = []): array
    {
        [$nodes, $pnodes, $methods] = [[], [], array_reverse(NodeService::instance()->getMethods())];
        foreach ($methods as $node => $method) {
            [$count, $pnode] = [substr_count($node, '/'), substr($node, 0, strripos($node, '/'))];
            if ($count === 2 && !empty($method['isauth'])) {
                in_array($pnode, $pnodes) or array_push($pnodes, $pnode);
                $nodes[$node] = ['node' => $node, 'title' => $method['title'], 'pnode' => $pnode, 'checked' => in_array($node, $checkeds)];
            } elseif ($count === 1 && in_array($pnode, $pnodes)) {
                $nodes[$node] = ['node' => $node, 'title' => $method['title'], 'pnode' => $pnode, 'checked' => in_array($node, $checkeds)];
            }
        }
        foreach (array_keys($nodes) as $key) foreach ($methods as $node => $method) if (stripos($key, $node . '/') !== false) {
            $pnode         = substr($node, 0, strripos($node, '/'));
            $nodes[$node]  = ['node' => $node, 'title' => $method['title'], 'pnode' => $pnode, 'checked' => in_array($node, $checkeds)];
            $nodes[$pnode] = ['node' => $pnode, 'title' => ucfirst($pnode), 'pnode' => '', 'checked' => in_array($pnode, $checkeds)];
        }
        return DataExtend::arr2tree(array_reverse($nodes), 'node', 'pnode', '_sub_');
    }

    /**
     * 初始化用户权限
     *
     * @param boolean $force 强刷权限
     *
     * @return $this
     */
    public function apply(bool $force = false): AdminService
    {
        if ($force) $this->clearCache();
        if (($uid = $this->getUserId() ?: $this->getAppUserId('MANAGE')) <= 0) return $this;
        $user = SystemUser::mk()->where(['id' => $uid])->findOrEmpty()->toArray();
        if (!$this->isSuper() && count($aids = str2arr($user['department'])) > 0) {
            $authorizes = SystemDepartment::mk()->whereIn('id', $aids)->column('authorize');
            if (!empty($authorizes)) {
                $aids = [];
                foreach ($authorizes as $authorize) $aids = array_unique(array_merge($aids, str2arr($authorize)));
                $aids = SystemAuth::mk()->where(['status' => 1])->whereIn('id', $aids)->column('id');
                if (!empty($aids)) $nodes = SystemNode::mk()->distinct(true)->whereIn('auth', $aids)->column('node');
            }
        }
        $user['nodes'] = $nodes ?? [];
        if ($this->isManageLogin()) $this->app->cache->set('MANAGE.' . $this->getAppToken(), $user);
        else $this->app->session->set('user', $user);
        return $this;
    }

    /**
     * 清理节点缓存
     *
     * @return $this
     */
    public function clearCache(): AdminService
    {
        TokenService::instance()->clearCache();
        $this->app->cache->delete('SystemAuthNode');
        return $this;
    }

}
